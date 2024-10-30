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
    window.cCustomers = {
        el: {
            window: $(window),
            document: $(document),
            dataTables: null,
            dataTablesSelector: null,

        },
        fn: {
            customers: function () {
                var self = $("#comm-customers");
                var datas = self.data('table');
                var arr = $.map( datas, function( a,i ) {
                    return i+"="+a;
                }).join("&");

                var args = {};

                args = {
                    ajax: {
                        url: ajaxurl + '?action=comm_customers&'+arr
                    },
                    "searching": true,
                    "paging": true,
                    "serverSide": true,
                    "info": true,
                    "lengthChange":true,
                    'processing': true,
                    "autoWidth": true,
                    order: [],
                    "fnInitComplete": function() {
                        cDashMain.fn.setPopoverTooltips();
                        this.fnAdjustColumnSizing(true);
                    },
                    scrollX: true,
                    fixedHeader: {
                        header: true,
                        footer: true
                    },
                };

                if (typeof data !== 'undefined' && typeof data === 'object' && data !== null) {
                    for (var prop in data) {
                        if (data.hasOwnProperty(prop)) {
                            args[prop] = data[prop];
                        }
                    }
                }
                cCustomers.el.dataTables = self.DataTable(args);
                cCustomers.el.dataTablesSelector = $(this).find('table');
                $('<div class="clear"></div>').insertBefore($(this).find('table'));
                $('<div class="clear"></div>').appendTo($(this).find(' > *:first-child'));
            },
            delete: function (url,id) {
                $.ajax({
                    url: url,
                    method: "POST",
                    data: {
                        id: id
                    },
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', cApiSettings.nonce);
                        cForm.fn.loading("show");
                    },
                }).done(function (response) {
                    setTimeout(function(){$("#comm-customers").DataTable().ajax.reload();}, 1000);
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
                });
            },
            filterProductSearchAction: function (selector, columnId) {
                var fv = selector.data("status");
                if (fv == "any") {
                    fv = null;
                }
                
                if ((fv == '') || (fv == null)) {
                    $("#comm-recent-orders").DataTable().column(columnId).search('').draw();
                } else {
                    $("#comm-recent-orders").DataTable().column(columnId).search(fv).draw();
                }
            },
            filterCustomerSearchAction: function (value, columnId) {
                $("#comm-customers").DataTable().column(columnId).search(value).draw();
            }
        },
        run: function () {
            //WINDOW LOAD
            cCustomers.el.window.on("load", function () {
                var url = new URL(window.location.href);
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
                        title: "sukses"
                    });
                    let new_url = window.location.href.replace('&msg=1','');
                    window.history.pushState({urlPath:new_url},"",new_url)
                }
            });

            cCustomers.el.document.on('click', '.comm-filter', function (e) {
                e.preventDefault();
                $(".comm-filter").removeClass("active");
                $(this).addClass("active");
                cCustomers.fn.filterProductSearchAction($(this), 4);
            });

            cCustomers.el.document.on('keyup', '.c-customer-search', function (e) {
                cCustomers.fn.filterCustomerSearchAction(this.value, 1);
            });
            cCustomers.el.document.on('click', '.c-delete', function (e) {
                e.preventDefault();
                var id = $(this).data("id");
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
                        cCustomers.fn.delete(cApiSettings.root + 'commercioo/v1/comm_delete_customer/',id);
                    }
                });
            });

            cCustomers.el.document.on('click', '.delete-selected', function (e) {
                e.preventDefault();
                var id = [];
                $("input:checkbox[name=customer_id]:checked").each(function(){
                    id.push($(this).val());
                });
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
                        cCustomers.fn.delete(cApiSettings.root + 'commercioo/v1/comm_delete_customer/',id);
                    }
                });
            });
            //DOCUMENT READY
            cCustomers.el.document.ready(function () {
                cCustomers.fn.customers();
                $('#select-all').click(function(event) {   
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
            });

            //sync
            cCustomers.el.document.on("click", '#sync_customer', function () {
                $('#sync_customer').html('Syncing customer...')
                $.ajax({
                    url: cApiSettings.root + 'commercioo/v1/sync_customer',
                    method: "GET",
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', cApiSettings.nonce);
                    },
                }).done(function (response) {
                    setTimeout(function(){$("#comm-customers").DataTable().ajax.reload();}, 1000);
                    //cCustomers.fn.customers();
                    $('#sync_customer').remove();  
                })
            });
            
            cCustomers.el.document.on("click", '.c-show-detail-customer', function () {
                $('#modaldetailcustomer').attr("data-id", $(this).attr("data-id"));
            });

            $('#modaldetailcustomer').on('shown.bs.modal', function (event) {
                $(".comm-detail-customer-content").html('');
                var user = $(event.relatedTarget).attr('data-id');
                var customer = $(event.relatedTarget).attr('data-customer');
                if (user || customer) {
                    user = (user) ? user :0;
                    $.ajax({
                        url: cApiSettings.root + 'commercioo/v1/comm_detail_customer/' + user +'?customer_id='+customer,
                        method: "GET",
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', cApiSettings.nonce);
                        },
                    }).done(function (response) {
                        cForm.fn.loading("hide");
                        if (response.success) {
                            $(".comm-detail-customer-header").html(response.data.status_label);
                            $(".comm-detail-customer-content").html(response.data.detail_order_content);
                            var args = {
                                "searching": true,
                                "pageLength": 5,
                                "paging": true,
                                "serverSide": false,
                                "info": false,
                                "lengthChange":false,
                                'processing': true,
                                "autoWidth": true,
                                order: [],
                                "fnInitComplete": function() {
                                    cDashMain.fn.setPopoverTooltips();
                                    this.fnAdjustColumnSizing(true);
                                }
                            };
                            cCustomers.el.dataTables =  $('#comm-recent-orders').DataTable(args);
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
            
            $('.comm-table-search').on('keyup', function () {
                cCustomers.el.dataTables.search($(this).val()).draw() ;
            })
    }
    };
    cCustomers.run();
})(jQuery);