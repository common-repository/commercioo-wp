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
    window.cProductArchive = {
        el: {
            window: $(window),
            document: $(document),
        },
        fn: {},
        run: function () {
            //WINDOW LOAD
            cProductArchive.el.window.on("load", function () {
            });

            cProductArchive.el.document.ready(function () {
                // SHOW MODAL ORDER FORM
                cProductArchive.el.document.on('click', '.list-product-quick', function (e) {
                    $('.comm-quick-view-product').modal('show');
                    $('.comm-quick-view-product').attr("data-id",$(this).attr("data-id"));
                    $('.comm-quick-view-product').appendTo("body");
                });

                $('.comm-quick-view-product').on('shown.bs.modal', function (e) {
                    $(".comm-modal-quick-product").html("");
                    var prod_id= $(this).attr("data-id");
                    $.ajax({
                        url: cApiSettingsPublic.root + 'commercioo/v1/comm_quick_view_get_product/',
                        method: "POST",
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', cApiSettingsPublic.nonce);
                            cPublic.fn.loading("show",comm_lang_Public.item_to_cart.please_wait,"info",'');
                        },
                        data: {
                            prod_id:prod_id,
                            reqest_type: 'archive'
                        },
                    }).done(function (response) {
                        cPublic.fn.loading("hide",'','','',500);
                        $(".comm-modal-quick-product").html(response.data.result_html);

                        cGalerry.fn.initializeViewGallery();
                        cGalerry.fn.initializeGalleryThumb();
                        cGalerry.fn.owl_carousel_click();
                        feather.replace();
                        cProductArchive.el.document.trigger('product-modal-loaded');
                    }).fail(function (jqXHR, textStatus, error) {
                    });
                });

                cProductArchive.el.document.on('click', '.view-close', function (e) {
                    $('.comm-quick-view-product').modal('hide');
                })

                cProductArchive.el.document.on('click', '.comm-add-to-cart-product-archive', function (e) {
                    e.preventDefault();
                    if($(this).hasClass("no-click")){
                        return false;
                    }
                    $(this).addClass("no-click");
                    var dataStore = {
                        'prod_id': $(this).attr("data-id"),
                        'cart_qty': $(this).attr("data-qty"),
                    };
                    cPublic.fn.keranjang_belanja(cApiSettingsPublic.root + 'commercioo/v1/comm_add_to_cart_set_item/',dataStore,"add","POST",$(this));
                });

                $('li.li-dropdown>ul.ul-dropdown').prepend('<span class="click-submenu-dropdown badge float-right"><i class="fa fa-angle-down"></i></span>');
                $('.click-submenu-dropdown').siblings().hide();
                $('.click-submenu-dropdown').on('click', function() {
                    $(this).siblings().toggle();
                });

            });
        }
    };
    cProductArchive.run();
})(jQuery);