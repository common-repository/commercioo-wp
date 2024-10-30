(function ($) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
	 *
	 * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
	 *
	 * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */
    window.cCartPage = {
        el: {
            window: $(window),
            document: $(document),
        },
        fn: {},
        run: function () {
            //WINDOW LOAD
            cCartPage.el.window.on("load", function () {
            });

            cCartPage.el.document.ready(function () {
                cCartPage.el.document.on('change, focusout', '.comm-cart-qty-input', function (e) {
                    var qty = $(this);
                    var qty_cart = 1;
                    if (qty.val() <= 0) {
                        qty.val(1);
                    }
                });

                cCartPage.el.document.on('click', '.comm-remove-item-cart', function (e) {
                    e.preventDefault();
                    if($(this).hasClass("no-click")){
                        return false;
                    }
                    $(this).addClass("no-click");
                    var dataStore = {
                        'prod_id': $(this).attr("data-id"),
                    };
                    cPublic.fn.keranjang_belanja(cApiSettingsPublic.root + 'commercioo/v1/comm_add_to_cart_del_item/',dataStore,"remove","POST",$(this));
                });

                cCartPage.el.document.on('click', '.comm-plus-qty-cart', function (e) {
                    var qty = $(this).closest("tr").find(".comm-cart-qty-input");
                    if(qty.length==0){
                        qty=$(".comm-cart-qty-input");
                    }
                    var qty_cart = 1;

                    qty_cart = parseInt(qty.val()) + qty_cart;
                    qty.val(qty_cart);

                });

                cCartPage.el.document.on('click', '.comm-minus-qty-cart', function (e) {
                    var qty = $(this).closest("tr").find(".comm-cart-qty-input");
                    if(qty.length==0){
                        qty=$(".comm-cart-qty-input");
                    }
                    var qty_cart = 1;
                    if (qty.val() <= 1) {
                        qty.val(1);
                    } else {
                        qty_cart = parseInt(qty.val()) - qty_cart;
                        qty.val(qty_cart);
                    }
                });


                cCartPage.el.document.on('click', '.comm-shop-now', function (e) {
                    e.preventDefault();
                    window.location.href = cApiSettingsPublic.shopping_url;
                });
                cCartPage.el.document.on('click', '.comm-add-to-cart', function (e) {
                    e.preventDefault();
                    if ($(this).hasClass("no-click")) {
                        return false;
                    }
                    $(this).addClass("no-click");
                    var dataStore = {};
                    if($(".comm-parent-cart").length > 0) {
                        var product_id = [];
                        var product_qty = [];

                        $(".comm-parent-cart").each(function () {
                            var self = $(this);
                            var prod_id = self.attr("data-id");
                            var cart_qty = self.find('.comm-cart-qty-input').val();
                            product_id.push(prod_id);
                            product_qty.push(cart_qty);

                        });

                        dataStore = {
                            'prod_id': product_id,
                            'cart_qty': product_qty,
                        };
                        cPublic.fn.keranjang_belanja(cApiSettingsPublic.root + 'commercioo/v1/comm_add_to_cart_set_item/', dataStore, "update", "POST", $(this));
                    }else{
                        dataStore = {
                            'prod_id': $(this).attr("data-id"),
                            'cart_qty': $(".comm-cart-qty-input").val(),
                        };
                        cPublic.fn.keranjang_belanja(cApiSettingsPublic.root + 'commercioo/v1/comm_add_to_cart_set_item/',dataStore,"add","POST",$(this));
                    }

                });

                cCartPage.el.document.on('click', '.comm-checkout', function (e) {
                    e.preventDefault();
                    if ($(this).hasClass("no-click")) {
                        return false;
                    }
                    $(this).addClass("no-click");
                    if($(this).hasClass("btn-buy-now")) {
                        $(".comm-add-to-cart").trigger("click");
                        var dataStore = {
                            'prod_id': $(this).attr("data-id"),
                            'cart_qty': ($(".comm-cart-qty-input").val()) ? $(".comm-cart-qty-input").val() : 1,
                        };
                        cPublic.fn.keranjang_belanja(cApiSettingsPublic.root + 'commercioo/v1/comm_add_to_cart_set_item/', dataStore, "buy_now", "POST", $(this));
                    }else{
                        window.location.href = cApiSettingsPublic.checkout_url;
                    }
                });
            });
        }
    };
    cCartPage.run();
})(jQuery);