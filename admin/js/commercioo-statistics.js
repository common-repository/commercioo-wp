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
            checkLifetime: function (start, end, ranges) {
                if (!start) {
                    start = moment();
                    end = moment();
                }
                cStats.el.dateRangeStart = start.format('MMM D, YYYY');
                cStats.el.dateRangeEnd = end.format('MMM D, YYYY');

                if (cStats.el.dateRangeStatus == null) {
                    cStats.el.dateRangeStatus = "Last 30 Days";
                    $(cStats.el.dateRangeEl + ' span').html( cStats.el.dateRangeStatus + ": " + cStats.el.dateRangeStart + ' - ' + cStats.el.dateRangeEnd);
                } else {
                    cStats.el.dateRangeStatus = ranges;
                    if (cStats.el.dateRangeStatus == "LifeTime") {
                        $(".daterangepicker .ranges").find("ul li").each(function () {
                            $(this).removeClass("active");
                            if ($(this).attr('data-range-key') == cStats.el.dateRangeStatus) {
                                cStats.el.dateRangeStatus = $(this).addClass("active").attr('data-range-key');
                            }
                        });
                        $(cStats.el.dateRangeEl + ' span').html("LifeTime");
                    } else if (cStats.el.dateRangeStatus == "Today") {
                        $(".daterangepicker .ranges").find("ul li").each(function () {
                            $(this).removeClass("active");
                            if ($(this).attr('data-range-key') == cStats.el.dateRangeStatus) {
                                cStats.el.dateRangeStatus = $(this).addClass("active").attr('data-range-key');
                            }
                        });
                        $(cStats.el.dateRangeEl + ' span').html(cStats.el.dateRangeStatus + ": " + cStats.el.dateRangeStart);
                        cStats.el.dateRangeStatus = "Today";
                    } else if (cStats.el.dateRangeStatus == "Yesterday") {
                        $(".daterangepicker .ranges").find("ul li").each(function () {
                            $(this).removeClass("active");
                            if ($(this).attr('data-range-key') == cStats.el.dateRangeStatus) {
                                cStats.el.dateRangeStatus = $(this).addClass("active").attr('data-range-key');
                            }
                        });
                        $(cStats.el.dateRangeEl + ' span').html(cStats.el.dateRangeStatus + ": " + cStats.el.dateRangeStart);
                    } else {
                        $(cStats.el.dateRangeEl + ' span').html(cStats.el.dateRangeStatus + ": " + cStats.el.dateRangeStart + ' - ' + cStats.el.dateRangeEnd);
                    }
                }
            },
            getStatistic: function (startdate = '', enddate = '') {
                // if (cStats.el.dateRangeStatus == "Yesterday" || cStats.el.dateRangeStatus == "Today") {
                //     cStats.el.dateRangeEnd = cStats.el.dateRangeStart;
                // }else if (cStats.el.dateRangeStatus == "LifeTime"){
                //     cStats.el.dateRangeStart = '';
                //     cStats.el.dateRangeEnd = '';
                // }
                return new Promise((resolve, reject) => {
                    $.ajax({
                        url: cApiSettings.root + 'commercioo/v1/get_statistic_by_date/',
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
            getTimeStatistic : function(arg) {;
                cStats.fn.getStatistic(moment().startOf(arg).format('MMM D, YYYY'), moment().endOf(arg).format('MMM D, YYYY')).then(result => {
                    cStats.el.commRevenue = result.revenue;
                    cStats.el.commSales = result.sales;
                    cStats.el.commCustomer = result.customer;
                    cStats.el.commOrder = result.total_order;
                    cStats.fn.getStatistic(moment().subtract(1,arg+'s').startOf(arg).format('MMM D, YYYY'), moment().subtract(1,arg+'s').endOf(arg).format('MMM D, YYYY')).then(result => {
                        $(".c-price-revenue").html(cStats.el.commRevenue);
                        $(".comm-sales").html(cStats.el.commSales);
                        $(".comm-customer").html(cStats.el.commCustomer);
                        $(".comm-total-order").html(cStats.el.commOrder);
                        cStats.el.badgeOrder = (cStats.el.commOrder-result.total_order) * 100;
                        cStats.el.badgeSales = (cStats.el.commSales-result.sales) * 100;
                        cStats.el.badgeCustomer = (cStats.el.commCustomer-result.customer) * 100;
                        cStats.el.badgeRevenue = (cStats.el.commRevenue-result.revenue) * 100;
                        $('.box-order > span').html(cStats.el.badgeOrder < 0 ? cStats.el.badgeOrder+"%" : '+'+cStats.el.badgeOrder+"%").removeClass().addClass('badge').addClass(cStats.el.badgeOrder < 0 ? "c-badge-danger" : cStats.el.badgeOrder == 0 ? 'c-badge-secondary' : 'c-badge-success');
                        $('.box-sales > span').html(cStats.el.badgeSales < 0 ? cStats.el.badgeSales+"%" : '+'+cStats.el.badgeSales+"%").removeClass().addClass('badge').addClass(cStats.el.badgeSales < 0 ? "c-badge-danger" : cStats.el.badgeSales == 0 ? 'c-badge-secondary' :'c-badge-success');
                        $('.box-customer > span').html(cStats.el.badgeCustomer < 0 ? cStats.el.badgeCustomer+"%" : '+'+cStats.el.badgeCustomer+"%").removeClass().addClass('badge').addClass(cStats.el.badgeCustomer < 0 ? "c-badge-danger" : cStats.el.badgeCustomer == 0 ? 'c-badge-secondary' :'c-badge-success');
                        $('.box-revenue > span').html(cStats.el.badgeRevenue < 0 ? cStats.el.badgeRevenue+"%" : '+'+cStats.el.badgeRevenue+"%").removeClass().addClass('badge').addClass(cStats.el.badgeRevenue < 0 ? "c-badge-danger" : cStats.el.badgeRevenue == 0 ? 'c-badge-secondary' :'c-badge-success');
                    })
                    .catch(err => {
                        //or err
                    });
                })
                .catch(err => {
                    //or err
                });
            },
            generateDataRange: function (el) {
                cStats.el.dateRangeEl = el;
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
                        applyLabel: "Apply",
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
                }, cStats.fn.checkLifetime);
                cStats.fn.checkLifetime(start, end, "Last 30 Days");
            },
            getStatisticsProductDetails: function () {
                $('.c-hide-layouts').hide();
                $('.c-statistics-product-details').show();
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
                // if ($(".c-dr").length) {
                //     cStats.fn.generateDataRange(".c-date-range-statistics");
                // }
                
                cStats.fn.getTimeStatistic('month');
                cStats.fn.getContextMenu();

                $('.c-statistics-product-details').hide();

                $('.c-statistic-link-product-details').on("click", function () {
                    cStats.fn.getStatisticsProductDetails();
                });

                cStats.el.document.on('click', '.c-back-statistics-product-details', function (e) {
                    $('.c-hide-layouts').show();
                    $('.c-statistics-product-details').hide();
                });

                // $(".c-date-range-statistics").on('apply.daterangepicker', function (ev, picker) {
                //     if (cStats.el.dateRangeStatus == "Yesterday" || cStats.el.dateRangeStatus == "Today") {
                //         cStats.el.dateRangeEnd = cStats.el.dateRangeStart;
                //     } else if (cStats.el.dateRangeStatus == "LifeTime") {
                //         cStats.el.dateRangeStart = '';
                //         cStats.el.dateRangeEnd = '';
                //     } else {
                //         cStats.el.dateRangeStart = picker.startDate.format('MMM D, YYYY');
                //         cStats.el.dateRangeEnd = picker.endDate.format('MMM D, YYYY');
                //     }
                //     cStats.fn.update_statistic(cStats.el.dateRangeStart, cStats.el.dateRangeEnd);
                // });
            });
        }
    };
    cStats.run();
})(jQuery);