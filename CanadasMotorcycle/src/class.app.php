<?php

namespace CanadasMotorcycle;

/**
 * Class App.
 *
 * To save from over-engineering this basic PHP test, this will more or less
 * act as the controller, but it's not a controller. This is to make the setup
 * easier, and avoid wasting time reinventing MVC.
 *
 * @author Dane MacMillan <work@danemacmillan.com>
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @package CanadasMotorcycle
 */
class App
{
    // Properties //


    /**
     * @var array $dispatcherWhitelist Whitelist of available dispatcher endpoints.
     */
    private $dispatcherWhitelist;

    /**
     * @var bool $isXhr Determine if requests is an XMLHttpRequest (asynchronous).
     */
    private $isXhr;

    /**
     * @var Model $model The Model object.
     */
    private $model;

    /**
     * @var string $requestMethod Method of the current request.
     */
    private $requestMethod;

    /**
     * @var int $userID User ID of example user's cart.
     */
    public static $userID;

    /**
     * @var View $view The View object.
     */
    private $view;


    // Methods //


    /**
     * Get things rolling.
     */
    public function __construct()
    {
        // Set whitelist for GET and POST requests.
        $this->dispatcherWhitelist = array(
            'get' => array(
                'cart'
            ),
            'post' => array(
                'update'
            )
        );

        // There is only one user for this test. Developing an authentication
        // layer is way beyond scope. So, for the sake of ease, everyone who
        // accesses the demo, will be user 1.
        self::$userID = 1;

        // Instantiate model and view.
        $this->model = new Model();
        $this->view = new View();
    }

    /**
     * Dispatcher to determine where and how requests should be processed.
     */
    private function dispatcher()
    {
        $this->requestMethod = strtolower($_SERVER['REQUEST_METHOD']);
        $this->isXhr = (
            (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']))
            || (isset($_SERVER['X_REQUESTED_WITH']) && strtolower($_SERVER['X_REQUESTED_WITH']) == 'xmlhttprequest')
        );

        $endpoints = $this->dispatcherWhitelist[$this->requestMethod];
        $totalEndpoints = count($endpoints);
        if ($totalEndpoints > 1) {
            trigger_error('Only one endpoint can be specified per request.', E_USER_NOTICE);
        }

        // Only allow one endpoint per request. Obviously this is very
        // simplified.
        $endpoint = reset($endpoints);

        // Set default endpoint, if none provided.
        if (!$endpoint) {
            $endpoint = 'cart';
        }

        if (in_array($endpoint, $this->dispatcherWhitelist[$this->requestMethod])) {
            switch ($endpoint) {
                // This will only accept requests over GET.
                case 'cart':
                    $this->view->render($endpoint);
                    break;
                // This will only accept requests over POST.
                case 'update':
                    if ($this->handleUpdateCartQuantityPost()) {
                        if ($this->isXhr) {
                            $this->sendJson($this->view->provisionView(false));
                        } else {
                            $this->redirect('?cart');
                        }
                    } else {
                        // Not going to bother handling regular synchronous
                        // errors, other than redirecting back to the page.
                        // That sort of exhaustiveness is beyond the scope
                        // of this test. Nevertheless, I did handle
                        // asynchronous errors, because everyone loves
                        // eye candy.
                        $this->redirect('?error');
                    }
                    break;
            }
        }
    }

    /**
     * Process and validate the post form values coming in for the cart update.
     *
     * @return bool
     */
    private function handleUpdateCartQuantityPost()
    {
        $postData = $this->request('post');
        $validationStore = $this->validationStore();

        // Walk through array and validate its data.
        array_walk($postData, function (&$value, $key) use ($validationStore) {
            if (!preg_match($validationStore[$key], $value)) {
                $value = '';
            }
        });

        // Clear out any invalid data, but keep int 0. Without a callback, this
        // would clear out the empty strings set in the previous check AND
        // int 0.
        $postData = array_filter($postData, function ($value) {
            return ($value !== '');
        });

        // If all is well, send the cleaned up data to the model. The minus one
        // is for the submit button that is never wanted.
        $updated = false;
        if (count($postData) == count($validationStore) - 1) {
            $updated = $this->model->updateProductQuantityInCart($postData);
        }

        return $updated;
    }

    /**
     * Helper method to save from typing awkward location calls.
     *
     * @param string $location Location to redirect to.
     */
    public function redirect($location)
    {
        header("Location: $location");
        exit;
    }

    /**
     * Wrapper for retrieving the request data from superglobals.
     *
     * @param string $requestMethod Is either `get` or `post`.
     *
     * @return array
     */
    private function request($requestMethod)
    {
        $requestData = array();

        switch (strtolower($requestMethod)) {
            case 'get':
                $requestData = $_GET;
                break;
            case 'post':
                $requestData = $_POST;
                break;
        }

        return $requestData;
    }

    /**
     * Helper to send JSON-encoded data to client.
     *
     * @param array $jsonDump Array of data to encode and send as JSON.
     */
    private function sendJson($jsonDump)
    {
        header('Content-Type: application/json');
        echo json_encode($jsonDump);
        exit;
    }

    /**
     * Fire up the app.
     */
    public function start()
    {
        $this->dispatcher();
    }

    /**
     * Only these values are allowed in a cart POST quantity update.
     *
     * Each value is named, with their corresponding filter task. Essentially
     * is a regex store to validate all the incoming values against.
     *
     * @return array
     */
    private function validationStore()
    {
        return array(
            'cart_id' => '/^[0-9]{1,11}$/',
            'product_id' => '/^[0-9]{1,11}$/',
            'quantity' => '/^[0-9]{1,2}$/',
            'submit_quantity' => '/^$/' // Effectively removing it.
        );
    }
}
