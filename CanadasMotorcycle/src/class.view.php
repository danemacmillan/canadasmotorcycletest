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

        $this->getViewData();

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
                <li id="cart-id-' . $cartItem['cart_id'] . '">
                    <img src="' . $cartItem['image_url'] . '" alt="' . $cartItem['name'] . '" />
                    <div class="cart-product-info" id="product-id-' . $cartItem['product_id'] . '">
                        <h3 class="cart-product-name"><a href="' . $cartItem['image_url'] . '">' . $cartItem['name'] . '</a></h3>
                        <div class="cart-product-description">' . $cartItem['description'] . '</div>
                        <div class="cart-product-price">$' . $cartItem['price'] . '</div>
                        <label class="cart-product-quantity">Quantity: <input type="number" min="0" max="99" value="' . $cartItem['quantity'] . '" /></label>
                    </div>
                </li>';
        }

        $htmlProductList .= '</ul>';

        return $htmlProductList;
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
     * @return string
     */
    public function render()
    {
        return '<h1>HEYEYEHE</h1>';
    }

    /**
     * Grab contents of a view file.
     *
     * @param $view
     */
    public function fetchView($view)
    {
        ob_start();
        include('view.' . $view . '.tpl.php');
        $viewContents = ob_get_clean();


        $viewContents = $this->injectViewData($viewContents);


        return $viewContents;
    }

    public function getViewData()
    {
        $model = new Model();
        $cartData = $model->getCartData(1);

        $cartCount = 0;
        $cartSubTotal = 0;
        foreach ($cartData as $cartItem) {
            $cartCount += $cartItem['quantity'];
            $cartSubTotal += $cartItem['price'];
        }

        $this->setViewData(array(
            'cart_count' => $cartCount,
            'cart_subtotal' => $cartSubTotal,
            'cart_bag' => $this->buildHtmlProductList($cartData)
        ));
    }

    /**
     * Pass a key/value pair array to the method.
     *
     * The values will be cleaned, and the array will be merged with the
     * $this->viewData property, which is used for render.
     *
     * @param $viewData
     */
    public function setViewData($viewData)
    {
        if (is_array($viewData)) {
            $this->viewData = array_merge($this->viewData, $viewData);
        }
    }


}
