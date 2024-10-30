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
    window.cRecentOrders = {
        el: {
            window: $(window),
            document: $(document),
            dataTables: null,
            dataTablesSelector: null,

        },
        fn: {
            recent_orders: function () {
                var self = $("#comm-recent-orders");
                var datas = self.data('table');
                var arr = $.map( datas, function( a,i ) {
                    return i+"="+a;
                }).join("&");

                var args = {};

                args = {
                    ajax: {
                        url: ajaxurl + '?action=comm_recent_orders&'+arr
                    },
                    "searching": false,
                    "paging": false,
                    "serverSide": true,
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

                if (typeof data !== 'undefined' && typeof data === 'object' && data !== null) {
                    for (var prop in data) {
                        if (data.hasOwnProperty(prop)) {
                            args[prop] = data[prop];
                        }
                    }
                }
                cRecentOrders.el.dataTables = self.DataTable(args);
                cRecentOrders.el.dataTablesSelector = $(this).find('table');
                $('<div class="clear"></div>').insertBefore($(this).find('table'));
                $('<div class="clear"></div>').appendTo($(this).find(' > *:first-child'));
            },
        },
        run: function () {
            //WINDOW LOAD
            cRecentOrders.el.window.on("load", function () {
            });
            //DOCUMENT READY
            cRecentOrders.el.document.ready(function () {
                cRecentOrders.fn.recent_orders();
            });
        }
    };
    cRecentOrders.run();
})(jQuery);