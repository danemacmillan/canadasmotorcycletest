<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <title>Canada's Motorcycle Test Cart</title>
        <link rel="stylesheet" type="text/css" href="app.css" />
        <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans:400,800,800italic">
        <link rel="stylesheet" type="text/css" href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css">
        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <script type="text/javascript" src="app.js"></script>
        <link rel="shortcut icon" href="//www.canadasmotorcycle.ca/skin/frontend/creation/default/favicon.ico">
    </head>

    <body>
        <header>
            <h1>
                <a class="logo" href="?cart">Canada's Motorcycle</a>
                <span class="logo-subtext">Test Cart</span>
            </h1>
        </header>

        <main class="cart">
            <section class="cart-bag">
                <h2 class="cart-bag-head"><i class="fa fa-shopping-cart"></i> Shopping cart</h2>
                <div class="cart-bag-content">
                    {{cart_bag}}
                </div>
            </section>

            <section class="cart-wallet">
                <div id="follow-bar">
                    <div class="follow-bar-padding">
                        <h2 class="cart-wallet-head">
                            <span id="cart-count">{{cart_count}}</span> item(s) selected
                        </h2>
                        <ul class="cart-wallet-content">
                            <li>
                                Subtotal: <strong><i class="fa fa-usd"></i> <span id="cart-subtotal">{{cart_subtotal}}</span></strong>
                            </li>
                            <li>
                                GST: <strong><i class="fa fa-usd"></i> <span id="cart-gst">{{cart_gst}}</span></strong>
                            </li>
                            <li>
                                QST: <strong><i class="fa fa-usd"></i> <span id="cart-qst">{{cart_qst}}</span></strong>
                            </li>
                            <li>
                                <hr />
                            </li>
                            <li class="cart-total-big">
                                Total: <strong><i class="fa fa-usd"></i> <span id="cart-total">{{cart_total}}</span></strong>
                            </li>
                        </ul>

                        <button id="button-checkout" class="button">Checkout</button>
                    </div>
                </div>
            </section>
            <div class="clear"></div>
        </main>

        <footer>
            <p>Test cart by <a href="https://danemacmillan.com">Dane MacMillan</a> &copy; 2014</p>
            <p><a href="https://bitbucket.org/danemacmillan/canadasmotorcycletest">View git repo on Bitbucket</a></p>
            <p>&middot;</p>
            <p class="error-note">
                <strong>Note</strong>: for the purposes of this demonstration, you can trigger
                an error if you clear the quantity for an item in the cart, or
                type in something other than a number. In addition, there
                is a quantity ceiling of 99, so any number beyond that will
                also trigger an error. On a live asset, I would handle this
                by defaulting to 0, but I wanted to show how errors are
                handled. <strong>Also, don't forget to checkout ;)</strong>
            </p>
        </footer>

        <!-- Container for asynchronous feedback messages. -->
        <div id="feedback"></div>
    </body>
</html>
