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
    window.cStatsPage = {
        el: {
            window: $(window),
            document: $(document),
            dateRangeEl: '',
            dateRangeStatus: null,
            dataTables: null,
            dateRangeStart: ($(".c-dr").length == 1) ? moment() : '',
            dateRangeEnd: ($(".c-dr").length == 1) ? moment() : '',
            chartSnapshot: null,
            chartCustomer: null,
            chartRatio: null,
            chartOrder: null,
            chartResponse: null,

        },
        fn: {
            checkLifetime: function (start, end, ranges) {
                if (!start) {
                    start = moment();
                    end = moment();
                }
                cStatsPage.el.dateRangeStart = start.format('MMM D, YYYY');
                cStatsPage.el.dateRangeEnd = end.format('MMM D, YYYY');

                if (cStatsPage.el.dateRangeStatus == null) {
                    cStatsPage.el.dateRangeStatus = "Last 30 Days";
                    $(cStatsPage.el.dateRangeEl + ' span').html( cStatsPage.el.dateRangeStatus + ": " + cStatsPage.el.dateRangeStart + ' - ' + cStatsPage.el.dateRangeEnd);
                } else {
                    cStatsPage.el.dateRangeStatus = ranges;
                    if (cStatsPage.el.dateRangeStatus == "LifeTime") {
                        $(".daterangepicker .ranges").find("ul li").each(function () {
                            $(this).removeClass("active");
                            if ($(this).attr('data-range-key') == cStatsPage.el.dateRangeStatus) {
                                cStatsPage.el.dateRangeStatus = $(this).addClass("active").attr('data-range-key');
                            }
                        });
                        $(cStatsPage.el.dateRangeEl + ' span').html("LifeTime");
                    } else if (cStatsPage.el.dateRangeStatus == "Today") {
                        $(".daterangepicker .ranges").find("ul li").each(function () {
                            $(this).removeClass("active");
                            if ($(this).attr('data-range-key') == cStatsPage.el.dateRangeStatus) {
                                cStatsPage.el.dateRangeStatus = $(this).addClass("active").attr('data-range-key');
                            }
                        });
                        $(cStatsPage.el.dateRangeEl + ' span').html(cStatsPage.el.dateRangeStatus + ": " + cStatsPage.el.dateRangeStart);
                        cStatsPage.el.dateRangeStatus = "Today";
                    } else if (cStatsPage.el.dateRangeStatus == "Yesterday") {
                        $(".daterangepicker .ranges").find("ul li").each(function () {
                            $(this).removeClass("active");
                            if ($(this).attr('data-range-key') == cStatsPage.el.dateRangeStatus) {
                                cStatsPage.el.dateRangeStatus = $(this).addClass("active").attr('data-range-key');
                            }
                        });
                        $(cStatsPage.el.dateRangeEl + ' span').html(cStatsPage.el.dateRangeStatus + ": " + cStatsPage.el.dateRangeStart);
                    } else {
                        $(cStatsPage.el.dateRangeEl + ' span').html(cStatsPage.el.dateRangeStatus + ": " + cStatsPage.el.dateRangeStart + ' - ' + cStatsPage.el.dateRangeEnd);
                    }
                }
            },           
            generateDataRange: function (el) {
                cStatsPage.el.dateRangeEl = el;
                var start = moment().subtract(29, 'days');
                var end = moment();
                $(el).daterangepicker({
                    startDate: start,
                    endDate: end,
                    dayNamesMin: ["Sun", "Mon", "Tue", "Wedn", "Thu", "Fri", "Sat"],
                    showDropdowns: true,
                    showWeekNumbers: true,
                    alwaysShowCalendars: true,
                    opens: "center",
                    locale: {
                        format: "MMMM D, YYYY",
                        applyLabel: "Update",
                        cancelLabel: "Cancel",
                    },
                    ranges: {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 14 Days': [moment().subtract(13, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Week': [moment().startOf('weeks'), moment().endOf('weeks')],
                        'Last Week': [moment().subtract(1, 'weeks').startOf('weeks'), moment().subtract(1, 'weeks').endOf('weeks')],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                    }
                }, cStatsPage.fn.checkLifetime);
                cStatsPage.fn.checkLifetime(start, end, "Last 30 Days");
            },
            update_statistic: function (product_id = '0', startdate = '', enddate = '') {
                // getChart
                cStatsPage.fn.getChart(startdate, enddate, product_id);
                
                // getPageStatisticsData
                cStatsPage.fn.getPageStatisticsData(startdate, enddate, product_id);

                // trigger 3rd party plugin to get data
                cStatsPage.el.document.trigger( 'comm_statistic_reload', [ startdate, enddate, product_id ] );
            },
            getChart: function (startdate = '', enddate = '', product_id = null) {
                $.ajax({
                    url: cApiSettings.root + 'commercioo/v1/get_chart_by_date/',
                    method: "POST",
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', cApiSettings.nonce);
                    },
                    data: {
                        start_date: startdate,
                        end_date: enddate,
                        product_id: product_id,
                    }
                }).done(function (response) {
                    cStatsPage.el.chartResponse = response;
                    cStatsPage.fn.getPieCustomer(response);
                    cStatsPage.fn.getPieOrder(response);
                    cStatsPage.fn.getPieRatio(response,'sales', product_id);
                    cStatsPage.fn.generateSnapshot(response.sales_snapshot);
                }).fail(function (jqXHR, textStatus, error) {
                    
                });
            },
            getPageStatisticsData: function (startdate = '', enddate = '', product_id = null) {
                // set loading
                $('#statistics_loading').show();

                $.ajax({
                    url: cApiSettings.root + 'commercioo/v1/get_page_statistics_data/',
                    method: "POST",
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', cApiSettings.nonce);
                    },
                    data: {
                        start_date: startdate,
                        end_date: enddate,
                        product_id: product_id,
                    }
                }).done(function (response) {
                    // display the responses
                    $('#statistics_data_checkout_views').html(response.checkout_views);
                    $('#statistics_data_orders_number').html(response.orders_number);
                    $('#statistics_data_sales_number').html(response.sales_number);
                    $('#statistics_data_orders_percentage').html(response.orders_percentage);
                    $('#statistics_data_sales_percentage').html(response.sales_percentage);
                    $('#statistics_data_closing_rate').html(response.closing_rate);
                    $('#statistics_data_customers_number').html(response.customers_number);
                    $('#statistics_data_aspu').html(response.aspu);
                    $('#statistics_data_revenue').html(response.revenue);
                    $('#statistics_data_arpu').html(response.arpu);
                    $('#statistics_data_refund_number').html(response.refund_number);
                    $('#statistics_data_refund_number_percentage').html(response.refund_number_percentage);
                    $('#statistics_data_refund_amount').html(response.refund_amount);
                    $('#statistics_data_refund_amount_percentage').html(response.refund_amount_percentage);
                    $('#statistics_data_product_rank').html(response.product_rank_by_sales);
                    $('#statistics_data_product_rank_label').html(response.product_rank_label);
                                        
                    // hide loading
                    $('#statistics_loading').hide();
                }).fail(function (jqXHR, textStatus, error) {
                    console.log(error);
                });
            },
            generateSnapshot: function (data) {
                if(cStatsPage.el.chartSnapshot){
                    cStatsPage.el.chartSnapshot.destroy();
                }

                let snapshot = [];
                let labels = [];
                data.forEach(el => {
                    labels.push(new Date(el.t).toISOString().replace(/T.*/,'').split('-').reverse().join('-'));
                    snapshot.push(el.y);
                });

                var canvas = document.getElementById("snapshot"); 
                var ctx = canvas.getContext("2d");
                canvas.height = 350;
                var gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(0, 188, 212,0.5)');   
                gradient.addColorStop(1, 'rgba(0, 188, 212,0)');
                cStatsPage.el.chartSnapshot = new Chart(canvas, {
                  data: {
                    labels: labels,
                    datasets: [{
                        label: 'Sales',
                        data: snapshot,
                        type: 'line',
                        borderColor: '#00bcd4',
                        borderWidth: 2,
                        backgroundColor: gradient,
                        strokeColor: "rgba(151,205,187,1)",
                        pointBackgroundColor: "#fff",
                        pointBorderColor: "#00bcd4",
                        pointBorderWidth : 2,
                        lineTension: 0,
                    }]
                  },
                  options: {
                      maintainAspectRatio: false,
                      legend: {
                        display: false
                     },
                    scales: {
                        y: [{
                            gridLines: {
                                borderDash: [8, 4],
                                color: "#8888",
                                drawBorder: false,
                            },
                            ticks: {
                                beginAtZero: true,
                                fontColor: "#888",
                            }
                        }],
                        x: [{
                            type: 'time',
                            distribution: 'series',
                            offset: true,
                            gridLines: {
                                display: false,
                                drawBorder: false //<- set this
                            },
                            ticks: {
                                fontColor: "#888",
                                major: {
                                    enabled: true,
                                    fontStyle: 'bold'
                                },
                                source: 'data',
                                autoSkip: true,
                                autoSkipPadding: 50,
                                maxRotation: 0,
                                sampleSize: 100
                            },
                            time: {
                                tooltipFormat:'DD/MM/YYYY',
                                parser: 'YYYY-MM-DD',
                                displayFormats: {
                                    quarter: 'MMM YYYY'
                                },
                                unit: 'day'
                            }
                        }],
                    },
                    tooltips: {
                        intersect: true,
                        mode: 'index',
                        callbacks: {
                            label: function(tooltipItem, myData) {
                                var label = myData.datasets[tooltipItem.datasetIndex].label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += parseInt(tooltipItem.value);
                                return label;
                            }
                        }
                    }
                  }
                });
            },
            getPieCustomer: function(req){
                if(cStatsPage.el.chartCustomer){
                    cStatsPage.el.chartCustomer.destroy();
                }
                cStatsPage.el.chartCustomer = new Chart('pie-customer', {
                    type: 'pie',
                     data: {
                        labels: [
                            "NEW",
                            "REPEAT"
                        ],
                        datasets: [
                            {
                                data: [req.customer-req.returning_customer, req.returning_customer],
                                borderWidth: 0,
                                backgroundColor: [
                                    "#00bcd4",
                                    "#673ab7",
                                ]
                            }
                        ]
                    },
                    options: {
                          legend: {
                            position: 'bottom',
                            labels: {
                              usePointStyle: true,
                              boxWidth: 10
                            }
                         },
                         tooltips: {
                            intersect: true,
                            mode: 'index',
                            callbacks: {
                                label: function(tooltipItem, data) {
                                  //get the concerned dataset
                                  var dataset = data.datasets[tooltipItem.datasetIndex];
                                  //calculate the total of this data set
                                  var total = dataset.data.reduce(function(previousValue, currentValue, currentIndex, array) {
                                    return previousValue + currentValue;
                                  });
                                  //get the current items value
                                  var currentValue = dataset.data[tooltipItem.index];
                                  //calculate the precentage based on the total and current item, also this does a rough rounding to give a whole number
                                  var percentage = Math.floor(((currentValue/total) * 100)+0.5);

                                  return data.labels[tooltipItem.index] + ': '+ currentValue + ' ('+ percentage + "%)";
                                }
                            }
                        }
                      }
                });
            },
            getPieRatio: function(req, type, id = null){
                $('.ratio-type').html(type.toUpperCase());
                let labels,data,colors;
                req.sales = (req.sales == 0) ? -1 : req.sales;
                if (id && id != 0) {
                    labels = ["THIS PRODUCT","ALL PRODUCTS"];
                    data = (type == 'sales') ? [req.product_sales, req.sales] : [req.product_revenue, req.revenue];
                    colors = ["#00bcd4","#673ab7"];
                }else{
                    labels = ["ALL PRODUCTS"];
                    data = (type == 'sales') ? [req.sales] : [req.revenue];
                    colors = ["#673ab7"];
                }
                if(cStatsPage.el.chartRatio){
                    cStatsPage.el.chartRatio.destroy();
                }
                cStatsPage.el.chartRatio = new Chart('pie-ratio', {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                data: data,
                                borderWidth: 0,
                                backgroundColor: colors
                            }
                        ]
                    },
                    options: {
                          legend: {
                            position: 'bottom',
                            labels: {
                              usePointStyle: true,
                              boxWidth: 10
                            }
                         },
                         tooltips: {
                            intersect: true,
                            mode: 'index',
                            callbacks: {
                                label: function(tooltipItem, data) {
                                  //get the concerned dataset
                                  var dataset = data.datasets[tooltipItem.datasetIndex];
                                  //calculate the total of this data set
                                  var total = dataset.data.reduce(function(previousValue, currentValue, currentIndex, array) {
                                    return previousValue + currentValue;
                                  });
                                  //get the current items value
                                  var currentValue = dataset.data[tooltipItem.index];
                                  //calculate the precentage based on the total and current item, also this does a rough rounding to give a whole number
                                  var percentage = Math.floor(((currentValue/total) * 100)+0.5);

                                  return (req.sales == -1) ? data.labels[tooltipItem.index] + ': '+ 0 + ' ('+ 0 + "%)" :  data.labels[tooltipItem.index] + ': '+ currentValue + ' ('+ percentage + "%)";
                                }
                            }
                        }
                      }
                });
            },
            getPieOrder: function(req){
                if(cStatsPage.el.chartOrder){
                    cStatsPage.el.chartOrder.destroy();
                }
                Chart.defaults.font.size = 10;
                cStatsPage.el.chartOrder  = new Chart('pie-order', {
                    type: 'pie',
                     data: {
                        labels: [
                            "PENDING",
                            "PROCESSING",
                            "COMPLETED",
                            "REFUNDED"
                        ],
                        datasets: [
                            {
                                data: [req.pending, req.processing, req.completed, req.refund],
                                borderWidth: 0,
                                backgroundColor: [
                                    "#ff9800",
                                    "#2296f3",
                                    "#1cb43f",
                                    "#f44335"
                                ]
                            }
                        ]
                    },
                    options: {
                          legend: {
                            position: 'left',
                            labels: {
                              usePointStyle: true,
                              boxWidth: 10
                            }
                         },
                         tooltips: {
                            intersect: true,
                            mode: 'index',
                            callbacks: {
                                label: function(tooltipItem, data) {
                                  //get the concerned dataset
                                  var dataset = data.datasets[tooltipItem.datasetIndex];
                                  //calculate the total of this data set
                                  var total = dataset.data.reduce(function(previousValue, currentValue, currentIndex, array) {
                                    return previousValue + currentValue;
                                  });
                                  //get the current items value
                                  var currentValue = dataset.data[tooltipItem.index];
                                  //calculate the precentage based on the total and current item, also this does a rough rounding to give a whole number
                                  var percentage = Math.floor(((currentValue/total) * 100)+0.5);

                                  return data.labels[tooltipItem.index] + ': '+ currentValue + ' ('+ percentage + "%)";
                                }
                            }
                        }
                      }
                });
            },
            getContextRatio: function(){
                $.contextMenu({
                    selector: '#context-ratio', 
                    trigger: 'left',
                    callback: function(key, options) {
                        cStatsPage.fn.getPieRatio(cStatsPage.el.chartResponse, key, product_id)
                    },
                    items: {
                        "revenue": {name: "Revenue"},
                        "sales": {name: "Sales"},
                    }
                });
            },
            getContextRank: function(){
                $.contextMenu({
                    selector: '#statistics_data_product_rank_selector', 
                    trigger: 'left',
                    callback: function(key, options) {
                        // set loading
                        $('#statistics_loading').show();

                        // variables
                        var product_id = $('#product_id').val(); 
                        var startdate = cStatsPage.el.dateRangeStart;
                        var enddate = cStatsPage.el.dateRangeEnd;

                        $.ajax({
                            url: cApiSettings.root + 'commercioo/v1/get_statistics_product_rank/',
                            method: "POST",
                            beforeSend: function (xhr) {
                                xhr.setRequestHeader('X-WP-Nonce', cApiSettings.nonce);
                            },
                            data: {
                                start_date: startdate,
                                end_date: enddate,
                                product_id: product_id,
                                type: key,
                            }
                        }).done(function (response) {
                            // display the responses
                            $('#statistics_data_product_rank').html(response.value);
                            $('#statistics_data_product_rank_label').html(response.label);

                            // hide loading
                            $('#statistics_loading').hide();
                        }).fail(function (jqXHR, textStatus, error) {
                            console.log(error);
                        });
                    },
                    items: {
                        "sales": {name: "Sales"},
                        "orders": {name: "Orders"},
                        "customers": {name: "Customers"},
                        "revenue": {name: "Revenue"},
                    }
                });
            },
            getContextSales: function(){
                $.contextMenu({
                    selector: '#context-sales', 
                    trigger: 'left',
                    callback: function(key, options) {
                        var m = "clicked: " + key;
                        window.console && console.log(m) || alert(m); 
                    },
                    items: {
                        "order": {name: "Orders"},
                        "sales": {name: "Sales"},
                        "customer": {name: "Customers"},
                        "revenue": {name: "Revenue"},
                    }
                });
            }
        },
        run: function () {
            //WINDOW LOAD
            cStatsPage.el.window.on("load", function () {
                // silent
            });
            //DOCUMENT READY
            cStatsPage.el.document.ready(function () {
                if ($(".c-dr").length) {
                    cStatsPage.fn.generateDataRange(".c-date-range-statistics");
                }

                cStatsPage.fn.getChart(cStatsPage.el.dateRangeStart,cStatsPage.el.dateRangeEnd);

                // context menu
                cStatsPage.fn.getContextRatio();
                cStatsPage.fn.getContextRank();
                cStatsPage.fn.getContextSales();
                $('.c-statistic-link-product-details').on("click", function () {
                    cStatsPage.fn.getStatisticsProductDetails();
                });

                $(".c-date-range-statistics").on('apply.daterangepicker', function (ev, picker) {
                    if (cStatsPage.el.dateRangeStatus == "Yesterday" || cStatsPage.el.dateRangeStatus == "Today") {
                        cStatsPage.el.dateRangeEnd = cStatsPage.el.dateRangeStart;
                    } else if (cStatsPage.el.dateRangeStatus == "LifeTime") {
                        cStatsPage.el.dateRangeStart = '';
                        cStatsPage.el.dateRangeEnd = '';
                    } else {
                        cStatsPage.el.dateRangeStart = picker.startDate.format('MMM D, YYYY');
                        cStatsPage.el.dateRangeEnd = picker.endDate.format('MMM D, YYYY');
                    }
                });
                
                $('#product_id').select2({
                    width: '230px'
                });

                $('#get_statistics_data').on('click', function () {
                    cStatsPage.fn.update_statistic($('#product_id').val(), cStatsPage.el.dateRangeStart, cStatsPage.el.dateRangeEnd);
                });
            });
        }
    };
    cStatsPage.run();
})(jQuery);