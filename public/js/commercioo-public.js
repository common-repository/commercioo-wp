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
    window.cPublic = {
        el: {
            window: $(window),
            document: $(document),
            status_type: null,
            order_button: $('.btn-place-order'),
            order_summary: $('.commercioo-checkout-order-summary'),
            owl_view_image: $(".owl-1"),
            owl_view_image_thumb: $(".owl-2"),
            slidesPerPage: 4,
            number: 0,
            ajax_in_process: true,
            checkout_form_checkout: $('.commercioo-checkout-form'),
            ajax_product_items: true,
            ajax_payment_method: true,
            ajax_shipping_method: true,
        },
        fn: {
            setPopoverTooltips:function(){
                var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
                var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                    return new bootstrap.Popover(popoverTriggerEl)
                });
            },
            loading_blockUI:function(target){
                target.block({
                    message: "<div class='loading-img'></div>",
                    css: {
                        border: '0px solid #a00',
                        backgroundColor:'none',
                    },
                    overlayCSS: {
                        backgroundColor: '#FFF',
                    }
                });
            },
            placeholder_ui:function (){
              var html ='<div class="ui placeholder">';
              html +='<div class="line"></div>';
              html +="</div>";
              return html;
            },
            loading_unblockUI:function (target){
                target.unblock();
            },
            get_cookie: function(){
                let name = 'commercioo-cart';
                var cart ='';
                var cookie = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
                if(cookie !==null){
                    cart =cookie[2];
                }
                return cart;
            },
            loading: function (event, txt_msg, icon, type = '', duration = 0, responseStatus = false) {
                if (event == "show") {
                    $.toast({
                        text: txt_msg,
                        icon: icon,
                        position: 'mid-center',
                        textAlign: 'center',
                        loader: true,
                        stack: false,
                        hideAfter: false,
                        allowToastClose: false
                    });
                } else {
                    window.setTimeout(function () {
                        $(".jq-toast-wrap").fadeOut("slow").remove();
                        if (type == "buy_now" && responseStatus) {
                            // window.location.href = cApiSettingsPublic.cart_url;
                            window.location.href = cApiSettingsPublic.checkout_url;
                        }
                    }, duration);
                }
            },
            keranjang_belanja: function (url, dataStore = {}, type, method, target = '') {
                $.ajax({
                    url: url,
                    method: method,
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', cApiSettingsPublic.nonce);
                        if (type != "fetch") {
                            $.toast({
                                text: comm_lang_Public.item_to_cart.please_wait,
                                icon: 'info',
                                position: 'mid-center',
                                textAlign: 'center',
                                loader: true,
                                stack: false,
                                hideAfter: false,
                                allowToastClose: false
                            });
                        }
                    },
                    data: dataStore,
                }).done(function (response) {
                    $(".jq-toast-wrap").fadeOut("slow").remove();
                    var txt_msg = '';
                    switch (type) {
                        case "add":
                            txt_msg = comm_lang_Public.item_to_cart.sukses_tambah_keranjang;
                            if (target != '') {
                                target.removeClass("no-click");
                                cPublic.fn.keranjang_belanja(cApiSettingsPublic.admin_ajax_url + '?action=comm_view_add_to_item_cart', {post_id: cApiSettingsPublic.post_id,is_elementor:($("input[name='is_elementor']").length>0)?$("input[name='is_elementor']").val():false}, "fetch", "GET", '');
                            }
                            break;
                        case "buy_now":
                            txt_msg = comm_lang_Public.item_to_cart.sukses_tambah_keranjang;
                            break;
                        case "remove":
                            txt_msg = comm_lang_Public.item_to_cart.sukses_remove_keranjang;
                            if (target != '') {
                                target.closest("tr").remove();
                            }
                            break;
                        case "update":
                            txt_msg = comm_lang_Public.item_to_cart.update_keranjang;
                            if (target != '') {
                                target.removeClass("no-click");
                                cPublic.fn.keranjang_belanja(cApiSettingsPublic.admin_ajax_url + '?action=comm_view_add_to_item_cart', {post_id: cApiSettingsPublic.post_id,is_elementor:($("input[name='is_elementor']").length>0)?$("input[name='is_elementor']").val():false}, "fetch", "GET", '');
                            }
                            break;
                        default:
                            if (response.data.total_item_cart > 0) {
                                $(".show-cart .detail-show-cart").html(response.data.result_html);
                                $(response.data.total_item_html).appendTo($(".show-cart").children("a:first-child"));
                                $(".show-cart .count-cart .comm-item-cart-tooltips").html(response.data.total_item_cart);
                            } else {
                                $(".show-cart .count-cart").remove();
                                $(".show-cart .detail-show-cart").html(response.data.result_html);
                            }
                            if ($(".comm-section-cart-page").length) {
                                $(".comm-section-cart-page").html(response.data.content_cart_page);
                            }
                            if (typeof feather !== "undefined") {
                                feather.replace();
                            }
                            break;
                    }

                    if (type != "fetch") {
                        if (response.success) {
                            cPublic.fn.loading("show", txt_msg, "success", type);
                            cPublic.fn.loading("hide", txt_msg, '', type, 1500, response.success);
                        } else {
                            cPublic.fn.loading("show", response.data.message, 'error', '', 5000);
                            cPublic.fn.loading("hide", txt_msg, '', type, 1500);
                        }
                        if (target != '') {
                            target.removeClass("no-click");
                        }
                    }
                }).fail(function (jqXHR, textStatus, error) {
                    cPublic.fn.loading("show", comm_lang_Public.generic.errorMsg, 'error', '');
                    cPublic.fn.loading("hide", '', '', '', 2500);
                });
            },
            submitted: function (event) {
                cPublic.el.order_button.prop('disabled', true);
            },
            check_payment_method:function() {
                if (!cApiSettingsPublic.is_available_payment_method) {
                    if ($("#checkout-shipping-options").find(".comm-cost-option").length == 0) {
                        cPublic.el.order_button.prop('disabled', false);
                    }
                }
            },
            check_purchase_order_btn: function (status=true) {
                if(cPublic.el.ajax_in_process && status){
                    cPublic.fn.loading_unblockUI(cPublic.el.checkout_form_checkout);
                    cPublic.el.order_button.prop('disabled', false);
                }else{
                    cPublic.fn.loading_blockUI(cPublic.el.checkout_form_checkout);
                }
                if(!status){
                    cPublic.fn.loading_blockUI(cPublic.el.checkout_form_checkout);
                    cPublic.el.order_button.prop('disabled', true);
                }
                if($("#checkout-shipping-options").length > 0){
                    if ($("#checkout-shipping-options").find("input[name='shipping_cost']").length > 0) {
                        if (0 === jQuery('[name=shipping_cost]:checked').length) {
                            cPublic.el.order_button.prop('disabled', true);
                        }else{
                            cPublic.fn.loading_unblockUI(cPublic.el.checkout_form_checkout);
                            cPublic.el.order_button.prop('disabled', false);
                        }
                    }
                }
                if(cApiSettingsPublic.is_customize_preview) {
                    cPublic.fn.loading_unblockUI(cPublic.el.checkout_form_checkout);
                    cPublic.el.order_button.prop('disabled', false);
                }
                if(typeof stock_status !=="undefined"){
                    if(stock_status){
                        cPublic.el.order_button.prop('disabled', true);
                    }
                }
                if($(".product-label").length===0){
                    cPublic.el.order_button.prop('disabled', true);
                }
            },
            set_shipping: function(type="all") {
                if(type==="all"){
                    type = "billing";
                }
                cPublic.el.ajax_in_process = true;
                cPublic.fn.check_purchase_order_btn();
                if($(".checkbox-shipping:checkbox").prop("checked")===true && type==="billing"){
                    return;
                }

                var cost = $('[name=shipping_cost]:checked').val();
                if ( cost ) {
                    $(".produk-grandtotal-wrapper").removeClass("total");
                    $(".produk-grandtotal .total-price label").html(cPublic.fn.placeholder_ui());
                    cPublic.el.ajax_in_process = false;
                    cPublic.fn.check_purchase_order_btn(false);
                    var values = $('#commercioo-checkout-form').serialize();
                    $.ajax({
                        url: cApiSettingsPublic.root + 'commercioo/v1/set_shipping/',
                        method: 'post',
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', cApiSettingsPublic.nonce);
                        },
                        data: {
                            shipping: cost,
                            checkout_data: values,
                            post_id: cApiSettingsPublic.post_id,
                            shipping_price:($('[name=shipping_cost]:checked').length>0)?$('[name=shipping_cost]:checked').attr("data-price"):0,
                            is_elementor:($("input[name='is_elementor']").length>0)?$("input[name='is_elementor']").val():false
                        },
                    }).done(function (response) {
                        if(response.data.shipping !=="") {
                            if (response.data.grand_total_plain) {
                                jQuery("input[name='product_total']").val(response.data.grand_total_plain);
                            }
                            if (typeof response.data.grandtotal !== 'undefined') {
                                $(".produk-grandtotal-wrapper").addClass("total");
                                $(".produk-grandtotal .total-price label").html(response.data.grandtotal);
                            }
                        }
                        cPublic.el.ajax_in_process = true;
                        cPublic.fn.check_purchase_order_btn();
                    }).fail(function (jqXHR, textStatus, error) {
                        cPublic.el.ajax_in_process = true;
                        cPublic.fn.check_purchase_order_btn();
                    });
                }
            },
            syncPosition: function (el) {
                cPublic.el.owl_view_image_thumb.find(".owl-item").each(function () {
                    $(this).removeClass("current active")
                });
                cPublic.el.owl_view_image_thumb.find(".owl-item").eq(el.item.index).removeClass("current active").addClass("current active");
            },
            initializeScrolltabs: function () {
                if ($('.nav-tabs-img-product').length > 0) {
                    $('.nav-tabs-img-product').scrollingTabs({
                        enableSwiping: true,
                        disableScrollArrowsOnFullyScrolled: true,
                        scrollToTabEdge: true,
                        widthMultiplier: 0.98,
                        forceActiveTab: true,
                        tabClickHandler: function (e) {
                            e.preventDefault();
                            $(".nav-link").removeClass("active");
                            $(".tab-content").find(".tab-pane.active").removeClass("active");
                            var clickedTabElement = this;
                            $(clickedTabElement).addClass("active");
                            cPublic.fn.initializeViewGallery(clickedTabElement);
                        }
                    }).on('ready.scrtabs', function () {
                        var clickedTabElement = this;
                        cPublic.fn.initializeViewGallery(clickedTabElement);
                    });
                } else {
                    cPublic.fn.initializeViewGallery();
                    cPublic.fn.initializeGalleryThumb();
                }

            },
            initializeViewGallery: function (clickedTabElement) {
                var owl_selector_id = $(clickedTabElement).attr("data-id");
                if(typeof owl_selector_id == "undefined"){
                    if($(".nav-tabs-img-product").length>0){
                        owl_selector_id = $(".nav-tabs-img-product").find("a:first-child").attr("data-id");
                    }
                }

                // reference for main items
                cPublic.el.owl_view_image = $('.owl-1');
                // reference for thumbnail items
                cPublic.el.owl_view_image_thumb = $('.owl-2');
                if (typeof owl_selector_id != "undefined") {
                    // reference for main items
                    cPublic.el.owl_view_image = $('.owl-1-' + owl_selector_id);
                    // reference for thumbnail items
                    cPublic.el.owl_view_image_thumb = $('.owl-2-' + owl_selector_id);
                }

                $("#tab" + owl_selector_id).addClass("active");
                //transition time in ms
                var duration = 250;

                // carousel function for main slider
                cPublic.el.owl_view_image.owlCarousel({
                    loop: false,
                    nav: true,
                    autoplay: false,
                    dots: false,
                    navigation: true,
                    navText: [
                        "<i class='fa fa-chevron-left c-slide-next-prev slider-prev'></i>",
                        "<i class='fa fa-chevron-right c-slide-next-prev slider-next'></i>"
                    ],
                    items: 1
                }).on('changed.owl.carousel', cPublic.fn.syncPosition);

                // carousel function for thumbnail slider
                cPublic.el.owl_view_image_thumb.owlCarousel({
                    loop: false,
                    // center:true, //to display the thumbnail item in center
                    nav: false,
                    responsive: {
                        0: {
                            items: 3
                        },
                        600: {
                            items: 4
                        },
                        1000: {
                            items: 6
                        }
                    }
                }).on('click', '.owl-item', function (e) {
                    e.preventDefault();
                    $('.owl-2 .owl-item').removeClass('active');
                    $(this).addClass("current active");

                    cPublic.el.owl_view_image.trigger('to.owl.carousel', [$(this).index(), duration, true]);

                }).on('changed.owl.carousel', function (e) {
                    // On change of thumbnail item to trigger main item
                    cPublic.el.owl_view_image.trigger('to.owl.carousel', [e.item.index, duration, true]);
                });
                cPublic.el.owl_view_image.hover(function () {
                        if (typeof owl_selector_id != "undefined") {
                            cPublic.el.owl_view_image.find('.owl-nav').addClass("owl-nav-multi-product");
                        }
                        cPublic.el.owl_view_image.find('.owl-nav').show();
                    },
                    function () {
                        if (typeof owl_selector_id != "undefined") {
                            cPublic.el.owl_view_image.find('.owl-nav').addClass("owl-nav-multi-product");
                        }
                        cPublic.el.owl_view_image.find('.owl-nav').hide();
                    });
            },
            initializeGalleryThumb: function () {
                cPublic.el.owl_view_image_thumb.owlCarousel({
                    items: 4,
                    dots: false,
                    nav: false,
                    smartSpeed: 200,
                    slideSpeed: 500,
                    margin: 10,
                    responsiveClass: true
                });
                cPublic.el.owl_view_image_thumb.find(".owl-item:first-child").addClass("current");
            },
            owl_carousel_click: function () {
                cPublic.el.owl_view_image_thumb.on("click", ".owl-item", function (e) {
                    e.preventDefault();
                    cPublic.el.number = $(this).index();

                    cPublic.el.owl_view_image_thumb.find(".owl-item").each(function () {
                        $(this).removeClass("current active")
                    });

                    cPublic.el.owl_view_image_thumb.find(".owl-item").eq(cPublic.el.number).addClass("current active");
                    cPublic.el.owl_view_image.data('owl.carousel').to(cPublic.el.number, 300, true);
                });
            },
        },
        run: function () {
            //WINDOW LOAD
            cPublic.el.window.on("load", function () {
                if (typeof feather !== "undefined") {
                    feather.replace();
                }

                if (!wp.customize) {
                    cPublic.fn.keranjang_belanja(cApiSettingsPublic.admin_ajax_url + '?action=comm_view_add_to_item_cart', {post_id: cApiSettingsPublic.post_id,is_elementor:($("input[name='is_elementor']").length>0)?$("input[name='is_elementor']").val():false}, "fetch", "GET", '');                    
                }
            });

            cPublic.el.document.ready(function () {
                cPublic.el.document.on('click', '.wishlist-product', function (e) {
                    e.preventDefault();
                    alert("Coming Soon");
                });

                cPublic.el.document.on('change, focusout', '.comm-cart-qty-input', function (e) {
                    var qty = $(this);
                    var qty_cart = 1;
                    if (qty.val() <= 0) {
                        qty.val(1);
                    }
                });

                jQuery(document).ajaxComplete(function (event, xhr, settings) {
                    if (settings.url === cApiSettingsPublic.root + 'commercioo/v1/comm_add_to_cart_del_item/') {
                        cPublic.fn.keranjang_belanja(cApiSettingsPublic.admin_ajax_url + '?action=comm_view_add_to_item_cart', {post_id: cApiSettingsPublic.post_id,is_elementor:($("input[name='is_elementor']").length>0)?$("input[name='is_elementor']").val():false}, "fetch", "GET", '');
                    }
                    var data = settings.data;
                    if(typeof data !=="undefined"){
                        if(data.indexOf("get_shipping_checkout")>0) {
                            var response = xhr.responseJSON;
                            if (response.grand_total_plain) {
                                jQuery("input[name='product_total']").val(response.grand_total_plain);
                            }
                            if (response.grandtotal) {
                                jQuery(".commercioo-checkout-container .grand_total").html(response.grandtotal);
                            }
                            if(jQuery(".commercioo-checkout-container .produk-shipping").length>0){
                                jQuery(".commercioo-checkout-container .produk-shipping").html(response.shipping_options_available);
                            }

                            if(response.shipping_options_available){
                                jQuery("#checkout-shipping-options").attr("data-type",response.type);
                                cPublic.el.ajax_in_process = true;
                                cPublic.fn.check_purchase_order_btn();
                                cPublic.fn.set_shipping(response.type);
                            }

                            if ( 0 === jQuery('[name=shipping_cost]:checked').length ) {
                                jQuery("[name=shipping_cost]:first").prop('checked', true);
                                cPublic.el.ajax_in_process = true;
                                cPublic.fn.check_purchase_order_btn();
                                cPublic.fn.set_shipping(response.type);
                            }
                            // $(".produk-grandtotal-wrapper").addClass("total");
                        }
                    }
                    // var data = settings.data;
                    // if(typeof data !=="undefined"){
                    //     if(data.indexOf("comm_ongkir_get_city")>0) {
                    //         cPublic.el.ajax_in_process = false;
                    //         cPublic.fn.check_purchase_order_btn(false);
                    //         // success get data city
                    //         cOngkirPublic.fn.load_district_list( cOngkirPublic.el.billing_district_id, cOngkirPublic.el.billing_city_id.val());
                    //     }
                    //     if(data.indexOf("comm_ongkir_get_district")>0) {
                    //         cPublic.el.ajax_in_process = true;
                    //         cCheckoutStandalone.fn.get_shipping_checkout();
                    //     }
                    // }


                    // if(JSON.stringify(settings.data).indexOf("comm_ongkir_get_city") > -1){
                    //     success get data city
                    // console.log(settings.data);
                    // }
                });
                var form = document.querySelector("#commercioo-checkout-form");
                if (form) {
                    form.onsubmit = cPublic.fn.submitted.bind(form);
                }
            });
        }
    };
    cPublic.run();
})(jQuery);