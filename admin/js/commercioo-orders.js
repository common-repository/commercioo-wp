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
    window.cOrders = {
        el: {
            window: $(window),
            document: $(document),
            dateRangeEl: '',
            dateRangeStatus: null,
            maxFieldAddProduct: 10,
            totalProducts: 0,
            dateRangeStart: ($(".c-dr").length == 1) ? moment() : '',
            dateRangeEnd: ($(".c-dr").length == 1) ? moment() : '',
        },
        fn: {
            checkLifetime: function (start, end, ranges) {
                if (!start) {
                    start = moment();
                    end = moment();
                }
                cOrders.el.dateRangeStart = start.format('MMM D, YYYY');
                cOrders.el.dateRangeEnd = end.format('MMM D, YYYY');

                if (cOrders.el.dateRangeStatus == null) {
                    cOrders.el.dateRangeStatus = "Last 30 Days";
                    $(cOrders.el.dateRangeEl + ' span').html( cOrders.el.dateRangeStatus + ": " + cOrders.el.dateRangeStart + ' - ' + cOrders.el.dateRangeEnd);
                    cForm.fn.reloadDataTables(cOrders.el.dateRangeStart, cOrders.el.dateRangeEnd);
                } else {
                    cOrders.el.dateRangeStatus = ranges;
                    if (cOrders.el.dateRangeStatus == "LifeTime") {
                        $(".daterangepicker .ranges").find("ul li").each(function () {
                            $(this).removeClass("active");
                            if ($(this).attr('data-range-key') == cOrders.el.dateRangeStatus) {
                                cOrders.el.dateRangeStatus = $(this).addClass("active").attr('data-range-key');
                            }
                        });
                        $(cOrders.el.dateRangeEl + ' span').html("LifeTime");
                        cForm.fn.reloadDataTables();
                    } else if (cOrders.el.dateRangeStatus == "Today") {
                        $(".daterangepicker .ranges").find("ul li").each(function () {
                            $(this).removeClass("active");
                            if ($(this).attr('data-range-key') == cOrders.el.dateRangeStatus) {
                                cOrders.el.dateRangeStatus = $(this).addClass("active").attr('data-range-key');
                            }
                        });
                        $(cOrders.el.dateRangeEl + ' span').html(cOrders.el.dateRangeStatus + ": " + cOrders.el.dateRangeStart);
                        cOrders.el.dateRangeStatus = "Today";
                        cForm.fn.reloadDataTables(cOrders.el.dateRangeStart);
                    } else if (cOrders.el.dateRangeStatus == "Yesterday") {
                        $(".daterangepicker .ranges").find("ul li").each(function () {
                            $(this).removeClass("active");
                            if ($(this).attr('data-range-key') == cOrders.el.dateRangeStatus) {
                                cOrders.el.dateRangeStatus = $(this).addClass("active").attr('data-range-key');
                            }
                        });
                        $(cOrders.el.dateRangeEl + ' span').html(cOrders.el.dateRangeStatus + ": " + cOrders.el.dateRangeStart);
                        cForm.fn.reloadDataTables(cOrders.el.dateRangeStart);
                    } else {
                        cForm.fn.reloadDataTables(cOrders.el.dateRangeStart, cOrders.el.dateRangeEnd);
                        $(cOrders.el.dateRangeEl + ' span').html(cOrders.el.dateRangeStatus + ": " + cOrders.el.dateRangeStart + ' - ' + cOrders.el.dateRangeEnd);
                    }
                }
            },
            generateDataRange: function (el) {
                cOrders.el.dateRangeEl = el;
                var start = moment().subtract(29, 'days');
                var end = moment();
                $(el).daterangepicker({
                    startDate: start,
                    endDate: end,
                    showDropdowns: true,
                    showWeekNumbers: false,
                    alwaysShowCalendars: true,
                    opens: "center",
                    locale: {
                        format: "MMMM D, YYYY",
                        applyLabel: "Update",
                        cancelLabel: "Cancel",
                    },
                    ranges: {
                        'Today': [moment(), moment()],
                        'LifeTime': [moment().subtract(0, 'year'), moment().subtract(1, 'year')],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 14 Days': [moment().subtract(13, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Week': [moment().startOf('weeks'), moment().endOf('weeks')],
                        'Last Week': [moment().subtract(1, 'weeks').startOf('weeks'), moment().subtract(1, 'weeks').endOf('weeks')],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                    }
                }, cOrders.fn.checkLifetime);
                cOrders.fn.checkLifetime(start, end, "Last 30 Days");
            },
            totalProducts: function () {
                return $(".c-products-form").length;
            },
            addProducts: function () {
                // cOrders.el.document.on("click", '.add-orders, .add-orders-mobile',function() {
                //     // $('.add-orders').closest('.form-main').find('.product-form-pqp').first().clone().appendTo('.results');
                //     var newel =  $(this).closest('.c-products-form').clone();
                //     $(newel).insertAfter($(this).closest('.c-products-form'));
                //     $(newel).find(".comm-select2").select2("destroy");
                //     // $(".comm-select2").select2("destroy");
                //     $(newel).find("select").select2();
                // });

                // $('.form-main-mobile').on('click', '.add-orders-mobile', function() {
                //     $('.add-orders-mobile').closest('.form-main-mobile').find('.product-form-pqp-mobile').first().clone().appendTo('.results-mobile');
                // });
            },
            removeProducts: function () {
                cOrders.el.document.on("click", '.remove-orders, .remove-orders-mobile', function () {
                    cOrders.el.totalProducts = $(this).closest('.c-products-clone').children().length;
                    if (cOrders.el.totalProducts > 1) {
                        $(this).closest('.c-products-form').remove();
                    }
                    // jQuery('.remove-orders').closest('.form-main').find('.product-form-pqp').not(':first').last().remove();
                });

                // $('.form-main-mobile').on('click', '.remove-orders-mobile', function() {
                //     $('.remove-orders-mobile').closest('.form-main-mobile').find('.product-form-pqp-mobile').not(':first').last().remove();
                // });
            },
            copyFormatOrder: function () {
                $('.c-copy-to-clip').on('click', function () {
                    var copyText = document.getElementById("c-textarea-copy-keyboard");
                    copyText.select();
                    copyText.setSelectionRange(0, 99999);
                    document.execCommand("copy");
                })
            },
            getProductPrice: function (target) {
                if (target.find('.order-product').val() !== null) {

                    var product_id = [];
                    var product_price = [];
                    var product_qty = [];

                    var price = target.find('.order-product').select2().find(":selected").data("price");
                    var qty = target.find('.item_order_qty').val();
                    var obj = {};
                    var obj2 = {};
                    var obj3 = {};
                    var is_processing = true;

                    if (target) {
                        target.find(".item-price").val(parseInt(price));
                    }


                    $(".c-products-clone .c-products-form").each(function () {
                        if ($(this).find('.order-product').val() !== null) {
                            obj = $(this).find('.order-product').val();
                            obj2 = $(this).find('.item-price').val();
                            obj3 = $(this).find('.item_order_qty').val();
                            product_id.push(obj);
                            product_price.push(obj2);
                            product_qty.push(obj3);
                        } else {
                            is_processing = false;
                        }
                    });

                    if (is_processing) {
                        var url_string = window.location.href;
                        var url = new URL(url_string);
                        var ID = url.searchParams.get("id");
                        $.ajax({
                            url: cApiSettings.root + 'commercioo/v1/comm_get_price/',
                            method: "GET",
                            beforeSend: function (xhr) {
                                xhr.setRequestHeader('X-WP-Nonce', cApiSettings.nonce);
                                cForm.fn.loading("show");
                            },
                            data: {
                                orderID: ID,
                                prodID: product_id,
                                prodQty: product_qty,
                                prodTotalPrice: product_price,
                            },
                        }).done(function (response) {
                            cForm.fn.loading("hide");
                            if (response.success) {
                                // $(".comm-detail-item-order").html(response.data.result_html);
                                $('.comm-select2:not(:first)').select2({
                                    "width": "100%",
                                    "containerCssClass": "c-select2-select",
                                    "dropdownCssClass": "c-select2-option"
                                });
                            } else {
                                alert(response.data[0].message);
                            }
                        }).fail(function (jqXHR, textStatus, error) {
                            cForm.fn.loading("hide");
                            if (typeof jqXHR.responseJSON != "undefined") {

                            }
                        });
                    }
                }
            },
            refreshField: function (status) {
                if (status == "show") {
                    $('.c-list-orders').show();
                    $('.c-add-orders').hide();
                    $('.c-parsing-orders').hide();
                    // $(".comm-order-label").hide();
                } else {
                    $('.c-list-orders').hide();
                    $('.c-add-orders').show();
                    $('.c-parsing-orders').hide();
                    $(".c-title-text-orders").html(commlang.orders.listTitle);
                }
            },
            resetField: function () {
                $("form")[0].reset();
                $('.order-product').val(null).trigger('change');
                $(".item_order_qty").val(0);
                $(".order-price").val(0);
                $('input[name="user_id"]').val(0);
                // $(".comm-order-label").hide();
                $("select[name='billing_email']").val(null).trigger('change');
                if (cOrders.fn.totalProducts() > 0) {
                    $(".c-products-clone").each(function () {
                        $(this).find(".c-products-form").not(':first').remove();
                    });
                }
                $("form").removeClass("was-validated");
            },
            recalculate_order_total: function () {
                var product_id = [];
                var product_price = [];
                var product_qty = [];
                var obj = {};
                var obj2 = {};
                var obj3 = {};


                $(".c-products-clone .c-products-form").each(function () {
                    obj = $(this).find('.order-product').val();
                    obj2 = $(this).find('.item-price').val();
                    obj3 = $(this).find('.item_order_qty').val();
                    product_id.push(obj);
                    product_price.push(obj2);
                    product_qty.push(obj3);

                });

                var url_string = window.location.href;
                var url = new URL(url_string);
                var ID = url.searchParams.get("id");

                $.ajax({
                    url: cApiSettings.root + 'commercioo/v1/comm_get_price/',
                    method: "GET",
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', cApiSettings.nonce);
                        cForm.fn.loading("show");
                    },
                    data: {
                        orderID: ID,
                        prodID: product_id,
                        prodQty: product_qty,
                        prodTotalPrice: product_price,
                    },
                }).done(function (response) {
                    cForm.fn.loading("hide");
                    if (response.success) {
                        $(".comm-detail-item-order").html(response.data.result_html);
                        $('.comm-select2').not('.c-products-cloneit select').select2({
                            "width": "100%",
                            "containerCssClass": "c-select2-select",
                            "dropdownCssClass": "c-select2-option"
                        });
                    } else {
                        alert(response.data[0].message);
                    }
                }).fail(function (jqXHR, textStatus, error) {
                    cForm.fn.loading("hide");
                    if (typeof jqXHR.responseJSON != "undefined") {

                    }
                });
            },
            post: function (url, dataStore, forms = '') {
                if (cForm.el.selectorID) {
                    url = url + cForm.el.selectorID;
                }

                $.ajax({
                    url: url,
                    method: "POST",
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', cApiSettings.nonce);
                    },
                    data: dataStore,
                }).done(function (response) {
                    cForm.el.selectorID = null;
                    var msg_store = commlang.generic.saveData;
                    if (cForm.el.dataFormEvent == "update") {
                        msg_store = commlang.generic.updateData;
                    }
                    cForm.fn.loading("hide");
                    cForm.el.dataFormEvent = null;
                    if (response.id) {
                        if (cOrders.el.dateRangeStatus == "LifeTime") {
                            cOrders.el.dateRangeStart = '';
                            cOrders.el.dateRangeEnd = '';
                        }
                        window.location.href = cApiSettings.site_url + '/wp-admin/admin.php?page=comm_order&msg=1';
                    } else {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });

                        Toast.fire({
                            type: 'error',
                            title: (response.data[0]) ? response.data[0].message : commlang.products.error
                        });

                        // forms.removeClass("was-validated");
                        forms.find(".c-save-products").removeAttr("disabled");
                        cOrders.fn.refreshField("hide");
                    }
                }).fail(function (jqXHR, textStatus, error) {
                    if (typeof jqXHR.responseJSON.message !== "undefined") {
                        cForm.fn.loading("hide");
                        if (cOrders.el.dateRangeStatus == "LifeTime") {
                            cOrders.el.dateRangeStart = '';
                            cOrders.el.dateRangeEnd = '';
                        }
                        cForm.fn.reloadDataTables(cOrders.el.dateRangeStart, cOrders.el.dateRangeEnd);
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });

                        Toast.fire({
                            type: 'error',
                            title: jqXHR.responseJSON.message
                        });

                        forms.find(".c-save-products").removeAttr("disabled");
                        cOrders.fn.refreshField("hide");
                    }
                });
            },
            validationForm: function () {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function (dataform) {
                    dataform.addEventListener('submit', function (event) {
                        if (dataform.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                            $('html, body').animate({
                                scrollTop: $('body').offset().top
                            }, 200);
                        } else {
                            event.preventDefault();
                            event.stopPropagation();
                            cForm.fn.loading("show");
                            var url_string = window.location.href;
                            var url = new URL(url_string);
                            cForm.el.selectorID = url.searchParams.get("id");
                            if(cForm.el.selectorID){
                                cForm.el.dataFormEvent = "update";
                            }
                            $(dataform).find(".c-save-products").attr("disabled", "disabled");
                            cOrders.fn.post(cApiSettings.root + 'wp/v2/comm_order/', $(dataform).serializeArray(), $(dataform));
                        }
                        dataform.classList.add('was-validated');
                    }, false);
                });
            },
            filterProductSearchAction: function (selector, columnId) {
                var fv = selector.data("status");
                if (fv == "any") {
                    fv = null;
                }

                if (fv == "trash") {
                    $(".dataTables_wrapper .c-empty-trash").show();
                } else {
                    $(".dataTables_wrapper .c-empty-trash").hide();
                }


                if ((fv == '') || (fv == null)) {
                    cForm.el.dataTables.column(columnId).search('', true, false).draw();
                } else {
                    cForm.el.dataTables.column(columnId).search(fv, true, false).draw();
                }
            },
            refreshFilterCount: function (status) {
                cForm.el.dataTables.column(8).search('', true, false).draw();
                var filteredData = cForm.el.dataTables
                    .column(8, {search: 'applied'})
                    .data()
                    .filter(function (value, index) {
                        if (status == null) {
                            return true;
                        } else {
                            status = status.charAt(0).toUpperCase() + status.slice(1);
                            return $(value).html() == status ? true : false;
                        }

                    });

                return "(" + filteredData.count() + ")";
            },
            setFilterCount: function () {
                $(".comm_count_all").html(cOrders.fn.refreshFilterCount(null));
                $(".comm_count_pending").html(cOrders.fn.refreshFilterCount("pending"));
                $(".comm_count_processing").html(cOrders.fn.refreshFilterCount("processing"));
                $(".comm_count_completed").html(cOrders.fn.refreshFilterCount("completed"));
                $(".comm_count_refunded").html(cOrders.fn.refreshFilterCount("refunded"));
                $(".comm_count_abandoned").html(cOrders.fn.refreshFilterCount("abandoned"));
                $(".comm_count_failed").html(cOrders.fn.refreshFilterCount("failed"));
                $(".comm_count_trash").html(cOrders.fn.refreshFilterCount("trash"));

                $(".comm-filter").removeClass("active");
                $(".comm-filter[data-status=any]").addClass("active");

            },
            comm_change_status: function (url, id, status) {
                var swal_msg;
                if (status == 'comm_processing') {
                    swal_msg = commlang.orders.msg_status_mark_as_processing;
                } else if (status == 'comm_completed') {
                    swal_msg = commlang.orders.msg_status_mark_as_complete;
                } else if (status == 'comm_pending') {
                    swal_msg = commlang.orders.msg_status_mark_as_pending;
                }else if (status == 'comm_refunded') {
                    swal_msg = commlang.orders.msg_status_mark_as_refunded;
                }else if (status == 'comm_abandoned') {
                    swal_msg = commlang.orders.msg_status_mark_as_abandoned;
                }else if (status == 'comm_failed') {
                    swal_msg = commlang.orders.msg_status_mark_as_failed;
                } else {
                    swal_msg = commlang.orders.trash;
                }

                $.ajax({
                    url: cApiSettings.root + 'commercioo/v1/comm_change_status/',
                    method: "POST",
                    data: {
                        id: id,
                        status: status,
                    },
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', cApiSettings.nonce);
                        cForm.fn.loading("show");
                    },
                }).done(function (response) {
                    if (cOrders.el.dateRangeStatus == "LifeTime") {
                        cOrders.el.dateRangeStart = '';
                        cOrders.el.dateRangeEnd = '';
                    }
                    cForm.fn.reloadDataTables(cOrders.el.dateRangeStart, cOrders.el.dateRangeEnd);
                    cForm.fn.loading("hide");
                    if (response.success) {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });

                        Toast.fire({
                            type: 'success',
                            title: swal_msg
                        });
                    } else {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });

                        Toast.fire({
                            type: 'info',
                            title: commlang.orders.failed_update_status
                        });
                    }
                }).fail(function (jqXHR, textStatus, error) {
                    if (typeof jqXHR.responseJSON != "undefined") {
                        if (typeof jqXHR.responseJSON.message != "undefined") {
                            if (cOrders.el.dateRangeStatus == "LifeTime") {
                                cOrders.el.dateRangeStart = '';
                                cOrders.el.dateRangeEnd = '';
                            }
                            cForm.fn.reloadDataTables(cOrders.el.dateRangeStart, cOrders.el.dateRangeEnd);
                            cForm.fn.loading("hide");
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 5000
                            });

                            Toast.fire({
                                type: 'error',
                                title: jqXHR.responseJSON.message
                            });
                        }
                    }
                });
            },
            delete: function (url, target) {
                $.ajax({
                    url: url,
                    method: "DELETE",
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', cApiSettings.nonce);
                        cForm.fn.loading("show");
                    },
                    data: {
                        force: true
                    },
                }).done(function (response) {
                    cForm.el.selectorID = null;
                    target.remove();
                    if (cOrders.el.dateRangeStatus == "LifeTime") {
                        cOrders.el.dateRangeStart = '';
                        cOrders.el.dateRangeEnd = '';
                    }
                    cForm.fn.reloadDataTables(cOrders.el.dateRangeStart, cOrders.el.dateRangeEnd);
                    cForm.fn.loading("hide");

                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });

                    Toast.fire({
                        type: 'success',
                        title: commlang.generic.deleteData
                    });
                }).fail(function (jqXHR, textStatus, error) {
                    if (typeof jqXHR.responseJSON.message !== "undefined") {
                        if (cOrders.el.dateRangeStatus == "LifeTime") {
                            cOrders.el.dateRangeStart = '';
                            cOrders.el.dateRangeEnd = '';
                        }
                        cForm.fn.reloadDataTables(cOrders.el.dateRangeStart, cOrders.el.dateRangeEnd);
                        cForm.fn.loading("hide");
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });

                        Toast.fire({
                            type: 'error',
                            title: jqXHR.responseJSON.message
                        });
                        if (cOrders.el.dateRangeStatus == "LifeTime") {
                            cOrders.el.dateRangeStart = '';
                            cOrders.el.dateRangeEnd = '';
                        }
                        cForm.fn.reloadDataTables(cOrders.el.dateRangeStart, cOrders.el.dateRangeEnd);
                        cForm.fn.loading("hide");
                    }
                });
            },
            get: function (url, dataStore, forms = '') {
                $.ajax({
                    url: url,
                    method: "GET",
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', cApiSettings.nonce);
                        cForm.fn.loading("show");
                    },
                    data: dataStore,
                }).done(function (response) {
                    cForm.fn.loading("hide");
                    if (response.status == "trash") {
                        cOrders.fn.refreshField("hide");
                        $(".c-back").trigger("click");
                    } else {
                        cOrders.fn.refreshField("hide");
                        cOrders.fn.resetField();

                        $("form.needs-validation").attr('novalidate', 'novalidate');

                        // billing_address
                        $('input[name="billing_address[billing_first_name]"]').val(response.billing_address.billing_first_name);
                        $('input[name="billing_address[billing_last_name]"]').val(response.billing_address.billing_last_name);
                        $('input[name="billing_address[billing_email]"]').val(response.billing_address.billing_email);
                        $('input[name="billing_address[billing_phone]"]').val(response.billing_address.billing_phone);
                        $('input[name="billing_address[billing_company]"]').val(response.billing_address.billing_company);
                        $('select[name="billing_address[billing_country]"]').val(response.billing_address.billing_country);
                        $('input[name="billing_address[billing_street_address]"]').val(response.billing_address.billing_street_address);
                        $('input[name="billing_address[billing_city]"]').val(response.billing_address.billing_city);
                        $('input[name="billing_address[billing_state]"]').val(response.billing_address.billing_state);
                        $('input[name="billing_address[billing_zip]"]').val(response.billing_address.billing_zip);

                        // shipping_address

                        $('input[name="shipping_address[shipping_first_name]"]').val(response.shipping_address.shipping_first_name);
                        $('input[name="shipping_address[shipping_last_name]"]').val(response.shipping_address.shipping_last_name);
                        $('input[name="shipping_address[shipping_email]"]').val(response.shipping_address.shipping_email);
                        $('input[name="shipping_address[shipping_phone]"]').val(response.shipping_address.shipping_phone);
                        $('input[name="shipping_address[shipping_company]"]').val(response.shipping_address.shipping_company);
                        $('select[name="shipping_address[shipping_country]"]').val(response.shipping_address.shipping_country);
                        $('input[name="shipping_address[shipping_street_address]"]').val(response.shipping_address.shipping_street_address);
                        $('input[name="shipping_address[shipping_city]"]').val(response.shipping_address.shipping_city);
                        $('input[name="shipping_address[shipping_state]"]').val(response.shipping_address.shipping_state);
                        $('input[name="shipping_address[shipping_zip]"]').val(response.shipping_address.shipping_zip);

                        $('textarea[name="order_notes"]').val(response.order_notes);

                        $('select[name="payment_method"]').val(response.payment_method);
                        $('select[name="status"]').val(response.status);
                        $('select[name="cs_id"]').val(response.cs_id);
                        $('select[name="staff_label"]').val(response.staff_label);
                        $('textarea[name="staff_label_note"]').val(response.staff_label_note);

                        var order_item = response.order_items;
                        order_item.reverse();
                        var clonedSheep = JSON.parse(JSON.stringify(order_item));
                        var self = $('.c-products-cloneit').children();
                        $('.c-products-clone').html('');
                        $.each(clonedSheep, function (key, item) {
                            var $newel = self.clone();
                            $($newel).find("select").attr("name", "order_items[" + key + "][product_id]");
                            $($newel).find("select").val(this.variation_id || this.product_id);
                            $($newel).find(".item_order_qty").attr("name", "order_items[" + key + "][item_order_qty]");
                            $($newel).find(".item_order_qty").val(this.item_order_qty);
                            $($newel).find(".item-price").attr("name", "order_items[" + key + "][custom_price]");
                            $($newel).find(".item-price").val(this.item_price);
                            // $($newel).find(".comm-select2").select2();
                            $($newel).find("select").select2({
                                "width": "100%",
                                "containerCssClass": "c-select2-select",
                                "dropdownCssClass": "c-select2-option"
                            });
                            $('.c-products-clone').append($newel);
                        });
                        cOrders.fn.recalculate_order_total();
                    }
                }).fail(function (jqXHR, textStatus, error) {
                    if (typeof jqXHR.responseJSON != "undefined") {
                        if (typeof jqXHR.responseJSON.message != "undefined") {
                            if (cOrders.el.dateRangeStatus == "LifeTime") {
                                cOrders.el.dateRangeStart = '';
                                cOrders.el.dateRangeEnd = '';
                            }
                            cForm.fn.reloadDataTables(cOrders.el.dateRangeStart, cOrders.el.dateRangeEnd);
                            cForm.fn.loading("hide");
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });

                            Toast.fire({
                                type: 'error',
                                title: jqXHR.responseJSON.message
                            });
                        }
                    }
                    if (cOrders.el.dateRangeStatus == "LifeTime") {
                        cOrders.el.dateRangeStart = '';
                        cOrders.el.dateRangeEnd = '';
                    }
                    cForm.fn.reloadDataTables(cOrders.el.dateRangeStart, cOrders.el.dateRangeEnd);
                    cForm.fn.loading("hide");
                    $(".c-back").trigger("click");
                });
            },
            viewSingleOrder: function (parameterName) {
                var result = null,
                tmp = [];
                location.search
                    .substr(1)
                    .split("&")
                    .forEach(function (item) {
                      tmp = item.split("=");
                      if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
                    });
                return result;
            },
            action: function (url, dataStore) {
                if (dataStore.action != 'delete') {
                    var actionText = commlang.orders.set + dataStore.action.replace("comm_","")
                } else {
                    var actionText = commlang.generic.deleteData
                }

                $.ajax({
                    url: url,
                    method: "PUT",
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', cApiSettings.nonce);
                        cForm.fn.loading("show");
                    },
                    data: dataStore,
                }).done(function (response) {
                    cForm.el.selectorID = null;
                    cForm.fn.reloadDataTables();
                    cForm.fn.loading("hide");
                    if (response.status != "error") {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });

                        Toast.fire({
                            type: 'success',
                            title: actionText
                        });
                        $('input[name=select-all]').prop('checked', false);

                        setTimeout(function () {
                            cOrders.fn.setFilterCount()
                        }, 500)
                    } else {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });

                        Toast.fire({
                            type: 'success',
                            title: commlang.orders.errorTrash
                        });
                    }
                }).fail(function (jqXHR, textStatus, error) {
                    if (typeof jqXHR.responseJSON.message !== "undefined") {
                        cForm.fn.loading("hide");
                        cForm.fn.reloadDataTables();
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });

                        Toast.fire({
                            type: 'error',
                            title: jqXHR.responseJSON.message
                        });
                    }
                });
            },
        },
        run: function () {
            //WINDOW LOAD

            cOrders.el.window.on("load", function () {
                cOrders.fn.validationForm();
                var url = new URL(window.location.href);
                var msg_store = commlang.generic.updateData;
                var msg = url.searchParams.get("msg");
                if(msg == 1){
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });

                    Toast.fire({
                        type: 'success',
                        title: msg_store
                    });
                    let new_url = window.location.href.replace('&msg=1','');
                    window.history.pushState({urlPath:new_url},"",new_url)
                }
            });
            //DOCUMENT READY
            cOrders.el.document.ready(function () {
                // GENERATE TABLE
                cForm.fn.generateTable(commlang.orders.searchPlaceholderTable);

                $('.comm-select2:not(:first)').select2({
                    "width": "100%",
                    "containerCssClass": "c-select2-select",
                    "dropdownCssClass": "c-select2-option"
                });
                cForm.fn.generateSelect2($("select[name='billing_email']"), true, []);
                cOrders.el.totalProducts = cOrders.fn.totalProducts();
                if ($(".c-dr").length) {
                    cOrders.fn.generateDataRange(".c-date-range-orders");
                }
                $('.c-add-orders').hide();
                $('.c-parsing-orders').hide();

                // cOrders.fn.addProducts();
                cOrders.fn.removeProducts();
                cOrders.fn.copyFormatOrder();

                // var self = $('.c-products-cloneit').children();
                // var $newel = self.clone();
                // $('.c-products-clone').append($newel);

                $(".c-date-range-orders").on('apply.daterangepicker', function (ev, picker) {
                    if (cOrders.el.dateRangeStatus == "Yesterday" || cOrders.el.dateRangeStatus == "Today") {
                        cOrders.el.dateRangeEnd = cOrders.el.dateRangeStart;
                    } else if (cOrders.el.dateRangeStatus == "LifeTime") {
                        cOrders.el.dateRangeStart = '';
                        cOrders.el.dateRangeEnd = '';
                    } else {
                        cOrders.el.dateRangeStart = picker.startDate.format('MMM D, YYYY');
                        cOrders.el.dateRangeEnd = picker.endDate.format('MMM D, YYYY');
                    }
                });

                if (cOrders.fn.viewSingleOrder('id') !== null) {
                    cForm.el.selectorID = cOrders.fn.viewSingleOrder('id');
                    cForm.el.dataFormEvent = "update";
                    
                    $('.c-editable').show();
                    $(".c-title-text-orders").html(commlang.orders.editTitle + ": #" + cForm.el.selectorID);
                    // cOrders.fn.get(cApiSettings.root + 'wp/v2/comm_order/' + cForm.el.selectorID, '', '');
                }

                // EDIT PRODUCT
                cOrders.el.document.on('click', '.comm_edit_order', function (e) {
                    e.preventDefault();
                    cForm.el.selectorID = $(this).data("id");

                    cForm.el.dataFormEvent = "update";
                    $('.c-editable').show();
                    $(".c-title-text-orders").html(commlang.orders.editTitle + ": #" + cForm.el.selectorID);
                    cOrders.fn.get(cApiSettings.root + 'wp/v2/comm_order/' + cForm.el.selectorID, '', '');

                });
                // ADD PARSING ORDER EVENT
                cOrders.el.document.on('click', '.c-btn-parsing-orders', function (e) {
                    e.preventDefault();
                    $('.c-list-orders').hide();
                    $('.c-parsing-orders').show();
                });

                // CONTENT FOLLOWUP MESSAGE

                cOrders.el.document.on('click', '.c-send-followup', function (e) {
                    e.preventDefault();
                    $(this).parent().find('.c-show-textarea-message').toggle();
                });

                cOrders.el.document.on('change, focusout', '.item_order_qty', function (e) {
                    // cOrders.fn.getProductPrice($(this).closest('.c-products-form'));
                });
                cOrders.el.document.on('change', '.comm-select2', function (e) {
                    cOrders.fn.getProductPrice($(this).closest('.c-products-form'));
                });

                cOrders.el.document.on("click", '.comm-copy-billing-email', function (e) {
                    var copyText = $(this).html();
                    document.addEventListener('copy', function (e) {
                        e.clipboardData.setData('text/plain', copyText);
                        e.preventDefault();
                    }, true);
                    document.execCommand('copy');
                    alert('copied text: ' + copyText);
                });


                cOrders.el.document.on("click", '.c-recalculate', function () {
                    cOrders.fn.recalculate_order_total();
                });
                cOrders.el.document.on("click", '.add-orders, .add-orders-mobile', function () {

                    var selfProduct = $('.c-products-cloneit').children();
                    var newel = selfProduct.clone(true);
                    var index = 0;
                    if (cOrders.fn.totalProducts() == 1) {
                        index = cOrders.fn.totalProducts();
                    } else {
                        index = cOrders.fn.totalProducts() - 1;
                        index = index + 1;
                    }

                    $(newel).find("select").attr("name", "order_items[" + index + "][product_id]");
                    $(newel).find(".item_order_qty").attr("name", "order_items[" + index + "][item_order_qty]");
                    $(newel).find(".item_order_qty").val(1);
                    $(newel).find(".item-price").attr("name", "order_items[" + index + "][custom_price]");
                    $(newel).find(".item-price").val(0);
                    $(newel).find("select").val(null).select2({
                        "width": "100%",
                        "containerCssClass": "c-select2-select",
                        "dropdownCssClass": "c-select2-option"
                    }).trigger("change");

                    $(newel).appendTo('.c-products-clone');
                });
                cOrders.el.document.on("click", '.c-show-detail-order', function () {
                    $('#modaldetailorder').attr("data-id", $(this).attr("data-id"));
                    $(".bs-popover-end").remove();
                    $(".feather-info").removeAttr("aria-describedby");
                });
                $('#modaldetailorder').on('hidden.bs.modal', function (event) {
                    cDashMain.fn.setPopoverTooltips();
                });
                $('#modaldetailorder').on('shown.bs.modal', function (event) {
                    $(".comm-detail-order-content").html('');
                    var order_id = $(event.relatedTarget).attr('data-id');
                    if (order_id) {
                        $.ajax({
                            url: cApiSettings.root + 'commercioo/v1/comm_detail_order/' + order_id,
                            method: "GET",
                            beforeSend: function (xhr) {
                                xhr.setRequestHeader('X-WP-Nonce', cApiSettings.nonce);
                            },
                        }).done(function (response) {
                            cForm.fn.loading("hide");
                            if (response.success) {
                                $(".comm-detail-order-header").html(response.data.status_label);
                                $(".comm-detail-order-content").html(response.data.detail_order_content);
                                $('input[name="shipping_number"]').val(response.data.shipping_number);
                            }
                        }).fail(function (jqXHR, textStatus, error) {
                            if (typeof jqXHR.responseJSON != "undefined") {
                                if (typeof jqXHR.responseJSON.message != "undefined") {
                                    if (typeof jqXHR.responseJSON.data.params.id != "undefined") {
                                        $('#modaldetailorder').modal('hide');
                                        if (cOrders.el.dateRangeStatus == "LifeTime") {
                                            cOrders.el.dateRangeStart = '';
                                            cOrders.el.dateRangeEnd = '';
                                        }
                                        cForm.fn.reloadDataTables(cOrders.el.dateRangeStart, cOrders.el.dateRangeEnd);
                                        cForm.fn.loading("hide");
                                        const Toast = Swal.mixin({
                                            toast: true,
                                            position: 'top-end',
                                            showConfirmButton: false,
                                            timer: 5000
                                        });

                                        Toast.fire({
                                            type: 'error',
                                            title: jqXHR.responseJSON.data.params.id
                                        });
                                    } else {
                                        $('#modaldetailorder').modal('hide');
                                        if (cOrders.el.dateRangeStatus == "LifeTime") {
                                            cOrders.el.dateRangeStart = '';
                                            cOrders.el.dateRangeEnd = '';
                                        }
                                        cForm.fn.reloadDataTables(cOrders.el.dateRangeStart, cOrders.el.dateRangeEnd);
                                        cForm.fn.loading("hide");
                                        const Toast = Swal.mixin({
                                            toast: true,
                                            position: 'top-end',
                                            showConfirmButton: false,
                                            timer: 5000
                                        });

                                        Toast.fire({
                                            type: 'error',
                                            title: jqXHR.responseJSON.message
                                        });
                                    }
                                }
                            }
                        });
                    }
                });

                cOrders.el.document.on('click', '.c-update-shipping', function (e) {
                    $('#modalupdateshipping').modal('show');
                    $('#modalupdateshipping').attr("data-id",$(this).attr("data-id"));
                });
                cOrders.el.document.on('click', '.c-cancel-shipping', function (e) {
                    $('#modalupdateshipping').modal('hide');
                });
                $('#modalupdateshipping').on('shown.bs.modal', function (event) {
                    $('#modaldetailorder').css('z-index',0);
                });
                $('#modalupdateshipping').on('hidden.bs.modal', function () {
                    $('#modaldetailorder').css('z-index',1050);
                });
                // FILTER
                cOrders.el.document.on('click', '.comm-filter', function (e) {
                    e.preventDefault();
                    $(".comm-filter").removeClass("active");
                    var selectedFilter = $(this).data("status");

                    if (selectedFilter != 'trash') {
                        $('.c-table-list-orders-head a.delete-trash').parents('li').show()
                        $('.c-table-list-orders-head a.delete-permanent').parents('li').hide()
                    } else {
                        $('.c-table-list-orders-head a.delete-trash').parents('li').hide()
                        $('.c-table-list-orders-head a.delete-permanent').parents('li').show()
                    }

                    $(".comm-filter[data-status="+selectedFilter+"]").addClass("active");
                    cOrders.fn.filterProductSearchAction($(this), 8);
                });

                cOrders.el.document.on('click', '.c-delete', function (e) {
                    e.preventDefault();
                    var id = $(this).data("id"), target = $(this).closest('tr');
                    Swal.fire({
                        title: commlang.generic.caution,
                        text: commlang.generic.deleteConfirm,
                        type: 'info',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: commlang.generic.yes,
                        cancelButtonText: commlang.generic.no
                    }).then((result) => {
                        if (result.value) {
                            cOrders.fn.delete(cApiSettings.root + 'wp/v2/comm_order/' + id, target);
                        }
                    });
                });
                cOrders.el.document.on('click', '.comm-mark-as', function (e) {
                    e.preventDefault();
                    var id = $(this).data("id"),
                    status = '',
                    swal_msg;

                    if ($(this).data('action') != 'trash') {
                        if ( $(this).data('action') == 'restore' ) {
                            status = 'comm_pending';
                        } else {
                            status = 'comm_' + $(this).data("action")
                        }
                    } else {
                        status = $(this).data("action")
                    }

                    if ($(this).hasClass("c-btn-processing") || $(this).data('action') == 'processing') {
                        // status = "comm_processing";
                        swal_msg = commlang.orders.updateStatusProcessing;
                    } else if ($(this).hasClass("c-btn-success") || $(this).data('action') == 'complete') {
                        // status = "comm_completed";
                        swal_msg = commlang.orders.updateStatusComplete;
                    } else if ($(this).hasClass("c-btn-pending") || $(this).data('action') == 'pending') {
                        // status = "comm_pending";
                        swal_msg = commlang.orders.updateStatusPending;
                    } else if ($(this).hasClass("c-btn-refunded") || $(this).data('action') == 'refunded') {
                        // status = "comm_refunded";
                        swal_msg = commlang.orders.updateStatusRefunded;
                    } else if ($(this).hasClass("c-btn-abandoned") || $(this).data('action') == 'abandoned') {
                        // status = "comm_abandoned";
                        swal_msg = commlang.orders.updateStatusAbandoned;
                    } else if ($(this).hasClass("c-btn-failed") || $(this).data('action') == 'failed') {
                        // status = "comm_abandoned";
                        swal_msg = commlang.orders.updateStatusFailed;
                    } else if ($(this).hasClass("c-trash") || $(this).data('action') == 'trash') {
                        // status = "trash";
                        swal_msg = commlang.generic.trashConfirm;
                    } else {
                        // status = "comm_pending";
                        swal_msg = commlang.orders.restoreConfirm;
                    }

                    Swal.fire({
                        title: commlang.generic.caution,
                        text: swal_msg,
                        type: 'info',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: commlang.generic.yes,
                        cancelButtonText: commlang.generic.no
                    }).then((result) => {
                        if (result.value) {
                            cOrders.fn.comm_change_status(cApiSettings.root + 'commercioo/v1/comm_change_status/', id, status);
                        }
                    });
                });

                $('.comm-table-search').on('keyup', function () {
                    cForm.el.dataTables.search($(this).val()).draw() ;
                })

                // when header checkbox checked
                $('input[name=select-all]').click(function(event) {   
                    if(this.checked) {
                        // Iterate each checkbox
                        $(':checkbox').each(function() {
                            this.checked = true;                        
                        });
                    } else {
                        $(':checkbox').each(function() {
                            this.checked = false;                       
                        });
                    }
                });

                // Table Action
                cOrders.el.document.on('click', '.c-bulk-edit', function (e) {
                    if ($(this).hasClass('edit')) {
                        return;
                    }
                    e.preventDefault();
                    var id = [], 
                        type   = $(this).data('type'),
                        action = $(this).data('action'),
                    parent= $(this).closest(".table-option"),
                    parent_chekbox = parent.find("input[type='checkbox']").attr("name");
                    // get checked data
                    if (type == 'bulk') {
                        $("input:checkbox[name=order_id]:checked").each(function(idx, val){
                            id.push($(val).val());
                        });
                        if(id.length==0 && parent_chekbox=="select-all") {
                            Swal.fire({
                                icon: 'error',
                                title: commlang.generic.caution,
                                text: commlang.orders.error_no_count_action,
                            })
                            return false;
                        }
                    } else {
                        id = $(this).data('id');
                    }
                    var swal_msg;
                    if ($(this).data('action') == 'comm_processing') {
                        // status = "comm_processing";
                        swal_msg = commlang.orders.updateStatusProcessing;
                    } else if ($(this).data('action') == 'comm_complete') {
                        // status = "comm_completed";
                        swal_msg = commlang.orders.updateStatusComplete;
                    } else if ($(this).data('action') == 'comm_pending') {
                        // status = "comm_pending";
                        swal_msg = commlang.orders.updateStatusPending;
                    } else if ($(this).data('action') == 'comm_refunded') {
                        // status = "comm_refunded";
                        swal_msg = commlang.orders.updateStatusRefunded;
                    } else if ($(this).data('action') == 'comm_abandoned') {
                        // status = "comm_abandoned";
                        swal_msg = commlang.orders.updateStatusAbandoned;
                    } else if ($(this).data('action') == 'comm_failed') {
                        // status = "comm_abandoned";
                        swal_msg = commlang.orders.updateStatusFailed;
                    } else if ($(this).data('action') == 'trash') {
                        // status = "trash";
                        swal_msg = commlang.generic.trashConfirm;
                    } else {
                        // status = "comm_pending";
                        swal_msg = commlang.orders.restoreConfirm;
                    }

                    Swal.fire({
                        title: commlang.generic.caution,
                        text: swal_msg,
                        type: 'info',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: commlang.generic.yes,
                        cancelButtonText: commlang.generic.no
                    }).then((result) => {
                        if (result.value) {
                            cOrders.fn.action(cApiSettings.root + 'commercioo/v2/comm_order_action/', {
                                id: id,
                                tbl: "comm_order",
                                action: action,
                                type: type,
                                force: true
                            });
                        }
                    });
                })
            });
        }
    };
    cOrders.run();
})(jQuery);