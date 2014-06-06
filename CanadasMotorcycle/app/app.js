/**
 * Run stuff on load.
 */
$(document).ready(function($) {
    // 'Cause I'm a badass.
    'use strict';

    /**
     * Add `js` class to body, to progressively enhance page and to handle
     * graceful degradation. Essentially, the submit buttons will be hidden,
     * because they will be automatically submitted when JS is available.
     */
    var hasJs = (function() {
        $(document.body).addClass('js');
    }());

    /**
     * Submit quantity updates asynchronously with JS, when available.
     */
    var updateAsync = (function() {

        $('input[name=quantity]').on('input', function(e) {

            // Grab relevant form vars.
            var $form = $(this).closest('form'),
                formAction = $form.attr('action').trim(),
                quantity = $(this).val().trim(),
                cartId = $form.find('[name="cart_id"]').val().trim(),
                productId = $form.find('[name="product_id"]').val().trim();

            // Get placeholders for updates
            var $cartCount = $('#cart-count'),
                $cartSubtotal = $('#cart-subtotal'),
                $cartGst = $('#cart-gst'),
                $cartQst = $('#cart-qst'),
                $cartTotal = $('#cart-total');

            // Handle the asynchronous requests using promises, because they're
            // immensely better than checking against raw responses.
            $.post(formAction, {
                quantity: quantity,
                cart_id: cartId,
                product_id: productId
            }, null, 'json')
            .done(function(data) {
                // T'was a success, so reflect the new data in the view.
                $cartCount.html(data.cart_count);
                $cartSubtotal.html(data.cart_subtotal);
                $cartGst.html(data.cart_gst);
                $cartQst.html(data.cart_qst);
                $cartTotal.html(data.cart_total);
            })
            .fail(function(data) {

            })
            .always(function(data) {

            });
        });
    }());


    /**
     * Make sure the cart's wallet on the right follows as you scroll.
     */
    var walletScroll = (function()
    {
        var $cartWallet = $('.cart-wallet'),
            $followBar = $('#follow-bar'),
            length = $cartWallet.height() - $followBar.height() + $cartWallet.offset().top,
            width = $cartWallet.width() + 'px';

        $(window)
            // Make sure width is always accurate.
            .resize(function() {
                width = $cartWallet.width() + 'px';
                $followBar.css({
                    'width': width
                });
            })
            // The magic.
            .on('scroll load', function() {
                var scroll = $(this).scrollTop();
                $followBar.removeClass('follow-bar-fixed');

                if (scroll < $cartWallet.offset().top) {
                    $followBar.css({
                        'position': 'absolute',
                        'top': '0',
                        'width': width
                    });
                } else if (scroll > length) {
                    $followBar.css({
                        'position': 'absolute',
                        'bottom': '0',
                        'top': 'auto',
                        'width': width
                    });
                } else {
                    $followBar
                        .addClass('follow-bar-fixed')
                        .css({
                            'position': 'fixed',
                            'top': '0',
                            'width': width
                        });
                }
            });
    }());
});