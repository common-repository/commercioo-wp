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
    window.cStats = {
        el: {
            window: $(window),
            document: $(document),
            dateRangeEl: '',
            dateRangeStatus: null,
            dataTables: null,
            dateRangeStart: ($(".c-dr").length == 1) ? moment() : moment().startOf('month').format('MMM D, YYYY'),
            dateRangeEnd: ($(".c-dr").length == 1) ? moment() : moment().endOf('month').format('MMM D, YYYY'),
            commOrder : 0,
            commSales : 0,
            commCustomer : 0,
            commRevenue : 0,
            badgeOrder : 0,
            badgeSales : 0,
            badgeCustomer : 0,
            badgeRevenue : 0,
        },
        fn: {
            getStatistic: function (startdate = '', enddate = '') {
                return new Promise((resolve, reject) => {
                    $.ajax({
                        url: cApiSettings.root + 'commercioo/v1/get_timespan_by_date/',
                        method: "POST",
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', cApiSettings.nonce);
                            // cForm.fn.loading("show");
                        },
                        data: {
                            start_date: startdate,
                            end_date: enddate,
                        },
                        success: resolve,
                        error: reject
                    })
                });
            },
            numberCurrency: function (number){
                return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            },
            getTimeStatistic : function(arg) {
                $('#timespan_loading').show();
                if(arg == 'week'){
                    cStats.el.startdate = moment().subtract(7, "days").format('MMM D, YYYY');
                }else if(arg == 'month'){
                    cStats.el.startdate = moment().subtract(30, "days").format('MMM D, YYYY');
                }else if(arg == 'quarter'){
                    cStats.el.startdate = moment().subtract(90, "days").format('MMM D, YYYY');
                }else if(arg == 'year'){
                    cStats.el.startdate = moment().subtract(365, "days").format('MMM D, YYYY');
                }else{
                    cStats.el.startdate = moment().subtract(30, "days").format('MMM D, YYYY');
                }
                cStats.fn.getStatistic(cStats.el.startdate, moment().format('MMM D, YYYY')).then(result => {
                    cStats.el.commRevenue = result.revenue;
                    cStats.el.commSales = result.sales;
                    cStats.el.commCustomer = result.customer;
                    cStats.el.commOrder = result.total_order;

                    cStats.el.dateRangeEnd = cStats.el.startdate;
                    if(arg == 'week'){
                        cStats.el.startdate = moment().subtract(14, "days").format('MMM D, YYYY');
                    }else if(arg == 'month'){
                        cStats.el.startdate = moment().subtract(60, "days").format('MMM D, YYYY');
                    }else if(arg == 'quarter'){
                        cStats.el.startdate = moment().subtract(180, "days").format('MMM D, YYYY');
                    }else if(arg == 'year'){
                        cStats.el.startdate = moment().subtract(730, "days").format('MMM D, YYYY');
                    }else{
                        cStats.el.startdate = moment().subtract(60, "days").format('MMM D, YYYY');
                    }
                    cStats.fn.getStatistic(cStats.el.startdate, cStats.el.dateRangeEnd).then(result => {
                        $(".comm-data-revenue").html(cStats.fn.numberCurrency(cStats.el.commRevenue));
                        $(".comm-sales").html(cStats.el.commSales);
                        $(".comm-customer").html(cStats.el.commCustomer);
                        $(".comm-total-order").html(cStats.el.commOrder);

                        cStats.el.badgeOrder = ((cStats.el.commOrder - result.total_order)/ result.total_order) * 100;
                        cStats.el.badgeSales = ((cStats.el.commSales - result.sales)/result.sales) * 100;
                        cStats.el.badgeCustomer = ((cStats.el.commCustomer - result.customer)/result.customer) * 100;
                        cStats.el.badgeRevenue = ((cStats.el.commRevenue - result.revenue)/result.revenue) * 100;

                        $('.box-order > span').html(cStats.el.badgeOrder < 0 ? cStats.el.badgeOrder.toFixed(2)+"%" : (cStats.el.badgeOrder == 'Infinity') ? '+100%' : '+'+cStats.el.badgeOrder.toFixed(2)+"%").removeClass().addClass('badge').addClass(cStats.el.badgeOrder < 0 ? "c-badge-danger" : cStats.el.badgeOrder == 0 ? 'c-badge-secondary' : 'c-badge-success');
                        $('.box-sales > span').html(cStats.el.badgeSales < 0 ? cStats.el.badgeSales.toFixed(2)+"%" : (cStats.el.badgeSales == 'Infinity') ? '+100%' :'+'+cStats.el.badgeSales.toFixed(2)+"%").removeClass().addClass('badge').addClass(cStats.el.badgeSales < 0 ? "c-badge-danger" : cStats.el.badgeSales == 0 ? 'c-badge-secondary' :'c-badge-success');
                        $('.box-customer > span').html(cStats.el.badgeCustomer < 0 ? cStats.el.badgeCustomer.toFixed(2)+"%" : (cStats.el.badgeCustomer == 'Infinity') ? '+100%' :'+'+cStats.el.badgeCustomer.toFixed(2)+"%").removeClass().addClass('badge').addClass(cStats.el.badgeCustomer < 0 ? "c-badge-danger" : cStats.el.badgeCustomer == 0 ? 'c-badge-secondary' :'c-badge-success');
                        $('.box-revenue > span').html(cStats.el.badgeRevenue < 0 ? cStats.el.badgeRevenue.toFixed(2)+"%" : (cStats.el.badgeRevenue == 'Infinity') ? '+100%' :'+'+cStats.el.badgeRevenue.toFixed(2)+"%").removeClass().addClass('badge').addClass(cStats.el.badgeRevenue < 0 ? "c-badge-danger" : cStats.el.badgeRevenue == 0 ? 'c-badge-secondary' :'c-badge-success');
                        $('#timespan_loading').hide();
                    })
                    .catch(err => {
                        //or err
                    });
                })
                .catch(err => {
                    //or err
                });
            },
            getContextMenu: function(){
                $.contextMenu({
                    selector: '.context-menu', 
                    trigger: 'left',
                    callback: function(key, options) {
                        cStats.fn.getTimeStatistic(key);
                        let title = key.toUpperCase()+'LY';
                        $('.comm-time-title').html(title);
                    },
                    items: {
                        "week": {name: "Weekly"},
                        "month": {name: "Montly"},
                        "quarter": {name: "Quarterly"},
                        "year": {name: "Yearly"},
                    }
                });
            }
        },
        run: function () {
            //WINDOW LOAD
            cStats.el.window.on("load", function () {
            });
            //DOCUMENT READY
            cStats.el.document.ready(function () {
                
                cStats.fn.getTimeStatistic('month');
                cStats.fn.getContextMenu();
            });
        }
    };
    cStats.run();
})(jQuery);