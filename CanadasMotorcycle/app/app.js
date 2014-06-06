/**
 * Run stuff on load.
 */
$(document).ready(function () {
    // Because I'm a badass.
    'use strict';

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