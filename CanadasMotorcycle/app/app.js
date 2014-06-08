/**
 * Run some stuff on document ready.
 *
 * @author Dane MacMillan <work@danemacmillan.com>
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @package CanadasMotorcycle
 */
$(document).ready(function($) {
    // 'Cause I'm a badass.
    'use strict';

    /**
     * Add `js` class to body, to progressively enhance page and to handle
     * graceful degradation. Essentially, the submit buttons will be hidden,
     * because they will be automatically submitted when JS is available.
     */
    var hasJs = (function()
    {
        $(document.body).addClass('js');
    }());

    /**
     * Handle clicks to the checkout button. Note: Easter egg.
     */
    var checkoutMessage = (function()
    {
        $('#button-checkout').on('click', function(e) {
            $('#feedback')
                .addClass('success')
                .html('<iframe width="420" height="315" src="//www.youtube.com/embed/kIfOjkB17BA?autoplay=1" frameborder="0" allowfullscreen></iframe>');

            setTimeout(function() {
                $('#feedback').removeClass('success');
            }, 16000);
        })
    }());

    /**
     * Submit cart quantity updates asynchronously with JS, when available.
     */
    var updateAsync = (function()
    {
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

                // Make the grand total pop, as the armchair design critics say.
                $('.cart-total-big').addClass('make-it-pop-like-its-95-wed-design');
            })
            .fail(function(data) {
                $('#feedback')
                    .addClass('error')
                    .html('<i class="fa fa-exclamation-triangle"></i> Oops, there was an error doing that!');
            })
            .always(function(data) {
                setTimeout(function() {
                    $('#feedback').removeClass('error');
                }, 5000);

                setTimeout(function() {
                    $('.cart-total-big').removeClass('make-it-pop-like-its-95-wed-design');
                }, 400);
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
