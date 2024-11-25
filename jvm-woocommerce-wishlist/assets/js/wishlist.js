(function ($) {
    'use strict';

    var cix_wishlist = {


        add_to_wishlist: function (is_wishlist_page = false) {
            $(".jvm_add_to_wishlist, .wishlist-undo").on("click", function (e) {

                // get data-product-id from the button
                var product_id = $(this).data('product-id'),
                    remove_product = $(this).data('remove');

                e.preventDefault();
                var wishlist_btn = $(this);
                wishlist_btn.addClass('loading');

                $.ajax({
                    url: cix_wishlist_args.ajax_url,
                    type: 'post',
                    dataType: 'json',
                    data: {
                        action: 'cix_update_wishlist',
                        product_id: product_id,
                        nonce: cix_wishlist_args.nonce,
                        remove_product: remove_product,

                    },
                    success: function (res) {

                        if (is_wishlist_page) {

                            $(document).find('#cixwishlist-notice').empty().removeClass('wishlist-info ');
                            $(document).find('.jvm-woocommerce-wishlist-table tbody').append(res.data.loop_item);

                            $.event.trigger({
                                type: "undo_wishlist.cix_wishlist",
                                id: product_id,
                            });
                            return;
                        }
                        wishlist_btn.removeClass('loading');
                        wishlist_btn.addClass('in_wishlist');

                        if (!wishlist_btn.hasClass('in_wishlist')) {
                            wishlist_btn.addClass('wishlist_added');
                        }

                        // Redirect to the wishlist page
                        if (res.data.redirect && !res.data.removed && !res.data.already_in_wishlist) {
                            window.location.href = res.data.redirect_url;
                        }

                        if (res.data.removed) {
                            wishlist_btn.removeClass('in_wishlist');
                        }

                        if (res.data.popup) {
                            $('#wishlist-modal').html(res.data.template);

                            $('#wishlist-modal').modal({
                                fadeDuration: 200

                            });
                        }


                        $.event.trigger({
                            type: "add_to_wishlist.cix_wishlist",
                            id: product_id,
                        });





                    },
                    error: function () {
                        console.log('Ajax Error: cix_update_wishlist ');
                    }
                });

            });


        },
        add_to_cart: function () {
            $(".cixww-add-to-cart,.cixww-wishlist-all-cart").on("click", function (e) {
                console.log('clicked');
                var product_id = $(this).val(),
                    cart_all = $(this).data('cart-all');

                e.preventDefault();
                var wishlist_btn = $(this);
                wishlist_btn.addClass('loading');

                $.ajax({
                    url: cix_wishlist_args.ajax_url,
                    type: 'post',
                    dataType: 'json',
                    data: {
                        action: 'cix_wishlist_add_to_cart',
                        product_id: product_id,
                        nonce: cix_wishlist_args.nonce,
                        cart_all: cart_all
                    },
                    success: function (res) {
                        wishlist_btn.removeClass('loading');

                        if (res.data.cart_url) {
                            window.location.href = res.data.cart_url;
                        } {
                            $(document).find('#cixwishlist-notice').empty();
                            $(document).find('#cixwishlist-notice').append(res.data.add_to_cart_notice);
                        }
                        if (res.data.removed) {
                            // find closet .jvm-woocommerce-wishlist-product and remove it
                            wishlist_btn.closest('.jvm-woocommerce-wishlist-product').fadeOut();
                        }
                        if (res.data.loop_item && res.data.removed) {

                            $(document).find('.jvm-woocommerce-wishlist-table tbody').empty();
                            $(document).find('.jvm-woocommerce-wishlist-table tbody').append(res.data.loop_item);
                        }
                        $(document).find('#cixwishlist-notice').empty().removeClass('cixwishlist-notice');
                        $(document).find('#cixwishlist-notice').append(res.data.add_to_cart_notice);

                        $.event.trigger({
                            type: "add_to_cart.cix_wishlist",
                            id: product_id,
                        });

                    },
                    error: function () {
                        console.log('Ajax Error: cix_wishlist_add_to_cart ');
                    }
                });

            });


        },
        remove_product: function () {
            $(".www-remove").on("click", function (e) {
                e.preventDefault();
                console.log('clicked');
                // get data-product-id from the button
                var product_id = $(this).data('product-id');

                console.log(product_id);

                var rm_btn = $(this);

                $.ajax({
                    url: cix_wishlist_args.ajax_url,
                    type: 'post',
                    dataType: 'json',
                    data: {
                        action: 'cix_remove_product',
                        product_id: product_id,
                        nonce: cix_wishlist_args.nonce,
                    },
                    success: function (res) {


                        if (res.data.remove_notice) {
                            rm_btn.closest('.jvm-woocommerce-wishlist-product').fadeOut();
                            $(document).find('#cixwishlist-notice').empty().removeClass('cixwishlist-notice wishlist-info');
                            $(document).find('#cixwishlist-notice').append(res.data.remove_notice).addClass('cixwishlist-notice wishlist-info');
                        }

                        cix_wishlist.add_to_wishlist(true);

                        $.event.trigger({
                            type: "remove_product.cix_wishlist",
                            id: product_id,
                        });


                    },
                    error: function () {
                        console.log('Ajax Error: remove_product ');
                    }
                });

            });


        },

        misc: function () {
            if (!cix_wishlist_args.logged_in && cix_wishlist_args.wishlist_count > 0) {
                $(document).find('#cixwishlist-guest-notice').empty();
                $(document).find('#cixwishlist-guest-notice').append('<div class="cixwishlist-notice">' + cix_wishlist_args.guest_notice + '</div>').addClass('wishlist-info');
            }

            //  Cookies.set('cix_wc_wishlist_temp', 'value');
            var cix_cookie = Math.random().toString(36).substring(2, 9);
            console.log(Cookies.get('cix_wc_wishlist_temp'));
            if (!Cookies.get('cix_wc_wishlist_temp')) {
                Cookies.set('cix_wc_wishlist_temp', 'cookieID-' + cix_cookie, { expires: 30 });
            }

        },

    };

    window.cix_wishlist_init = function () {
        jQuery(".jvm_add_to_wishlist, .wishlist-undo").off('click');

        cix_wishlist.misc();
        cix_wishlist.add_to_wishlist();
        cix_wishlist.add_to_cart();
        cix_wishlist.remove_product();
        console.log('cix wishlist initialized');
    }


    $(document).ready(function () {
        cix_wishlist_init();
    });

    $(document).on('undo_wishlist.cix_wishlist', function () {
        cix_wishlist.add_to_cart();
        cix_wishlist.remove_product();
    });




})(jQuery);

// Other code using $ as an alias to the other library