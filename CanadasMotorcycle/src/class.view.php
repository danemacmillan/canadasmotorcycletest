<?php

namespace CanadasMotorcycle;

/**
 * Class View.
 *
 * Abstract away the view.
 *
 * @package CanadasMotorcycle
 */
class View
{
    /**
     * @var array $viewData Contains array of view data to be inserted.
     */
    private $viewData;

    public function __construct()
    {
        $this->viewData = array();

        // Get and set view data.
        $this->provisionView();
    }

    /**
     * Pass this a list of products, and it will build the HTML.
     *
     * @param array $productList List of products.
     *
     * @return string Html.
     */
    private function buildHtmlProductList($cartData)
    {
        $htmlProductList = '<ul class="cart-product-list">';

        foreach ($cartData as $cartItem) {
            $htmlProductList .= '
                <li id="product-id-' . $this->cleanOutput($cartItem['product_id']) . '">
                    <form method="post" action="?update" id="cart-id-' . $this->cleanOutput($cartItem['cart_id']) . '">
                        <div class="cart-product-info">
                            <img src="' . $this->cleanOutput($cartItem['image_url']) . '" alt="' . $this->cleanOutput($cartItem['name']) . '" />
                            <h3 class="cart-product-name">
                                <a href="' . $this->cleanOutput($cartItem['image_url']) . '">' . $this->cleanOutput($cartItem['name']) . '</a>
                            </h3>
                            <div class="cart-product-description">' . $this->cleanOutput($cartItem['description']) . '</div>
                            <div class="cart-product-price">$' . $this->cleanOutput($cartItem['price']) . '</div>
                            <label class="cart-product-quantity">
                                Quantity: <input name="quantity" type="number" min="0" max="99" value="' . $this->cleanOutput($cartItem['quantity']) . '" />
                            </label>
                            <input type="hidden" name="cart_id" value="' . $this->cleanOutput($cartItem['cart_id']) . '" />
                            <input type="hidden" name="product_id" value="' . $this->cleanOutput($cartItem['cart_id']) . '" />
                            <input type="submit" name="submit_quantity" value="Update quantity" class="button button-inline no-js" />
                        </div>
                    </form>
                </li>';
        }

        $htmlProductList .= '</ul>';

        return $htmlProductList;
    }

    private function cleanOutput($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Replaces all handlebars-type placeholders in the view with live data.
     *
     * @param string $viewContents Raw HTML.
     */
    private function injectViewData($viewContents)
    {
        $viewData = $this->viewData;

        $viewKeys = array_map(function ($key) {
            return '{{' . $key . '}}';
        }, array_keys($viewData)) ;

        $viewValues = array_values($viewData);

        $viewContents = str_ireplace($viewKeys, $viewValues, $viewContents);

        return $viewContents;
    }

    /**
     * Get and set cart data for the view.
     *
     * @param bool $noBag If true, 'cart_bag' will be removed from return.
     *
     * @return array
     */
    public function provisionView($noBag = false)
    {
        $model = new Model();
        $cartData = $model->getCartData(App::$userID);

        // Get and set basic view data.
        $viewData = array(
            'cart_count' => $model->getCartQuantity($cartData),
            'cart_subtotal' => $model->getCartSubtotal($cartData),
            'cart_bag' => $this->buildHtmlProductList($cartData)
        );
        $this->setViewData($viewData);

        // Get and set calculation view data.
        $cartFinalPrices = $model->getFinalPrices($this->viewData['cart_subtotal']);
        $this->setViewData($cartFinalPrices);

        if ($noBag) {
            unset($this->viewData['cart_bag']);
        }

        return $this->viewData;
    }

    /**
     * Grab contents of a view file.
     *
     * @param string $view Name of view to render.
     */
    public function render($view)
    {
        // Capture all outbut from buffer.
        ob_start();

        // Bring in the template.
        include('view.' . $view . '.tpl');

        // Assign and parse view.
        $viewContents = ob_get_clean();
        if (strlen($viewContents)) {
            $viewContents = $this->injectViewData($viewContents);
        }

        // Render out contents to browser.
        echo $viewContents;
    }

    /**
     * Pass a key/value pair array to the method.
     *
     * The values will be cleaned, and the array will be merged with the
     * $this->viewData property, which is used for render.
     *
     * @param array $viewData View data from model.
     */
    public function setViewData($viewData)
    {
        if (is_array($viewData)) {
            $this->viewData = array_merge($this->viewData, $viewData);
        }
    }
}
