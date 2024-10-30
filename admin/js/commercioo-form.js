(function( $ ) {
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
    window.cForm= {
        el: {
            window: $(window),
            document: $(document),
            selectorID:null,
            dataTables: null,
            dataTablesSelector: null,
            dataFormEvent: null,
        },
        fn: {
            generateSelect2: function (elSel,tags,tokenSeparators=[]) {
                if(tags){
                    elSel.select2({
                        tags: tags,
                        tokenSeparators: tokenSeparators,
                        createTag: function (params) {
                            var term = $.trim(params.term);

                            if (term === '') {
                                return null;
                            }

                            return {
                                id: term,
                                text: term,
                                newTag: true // add additional parameters
                            }
                        },
                        formatNoMatches: function() {
                            return '';
                        },
                        dropdownCssClass: 'c-select2-hidden'
                    })
                }else{
                    elSel.select2();
                }

            },
            loading:function(event){
                if(event=="show"){
                     $('.c-page-loading').fadeIn();
                }else{
                     $('.c-page-loading').fadeOut();
                }
            },
            generateTable:function(searchPlaceholder){
                $('.c-tbl').each(function () {
                    
                    var datas = $(this).data('table');
                    var arr = $.map( datas, function( a,i ) {
                        return i+"="+a;
                    }).join("&");
                    var args = {},apiURL;
                    // if (pagenow === "toplevel_page_ccioo_product" ) {
                    //     apiURL = cApiSettings.api_url + "get_data?status=" + status + "&tbl=" + tbl+"&post_type="+post_type;
                        apiURL = cApiSettings.get_list_data+arr;
                    // }
                    args = {
                        ajax: apiURL,
                        // dom: '<"top">rt<"bottom"flp><"clear">',
                        pagingType: 'full_numbers',
                        'processing': true,
                        language: {
                            paginate: {
                                last: '&#187;',
                                first: '&#171;',
                                next: '&#155;',
                                previous: '&#139;',
                            },
                            search: '<div class="c-f-right c-dt-icon-search"><i class="fa fa-search"></i></div>',
                            searchPlaceholder: searchPlaceholder,
                            // sEmptyTable: c_lang.generic.no_data_tables.label
                        },
                        fixedHeader: {
                            header: true,
                            footer: true
                        },
                        scrollX: true,
                        lengthMenu: [5, 10, 20, 40, 80, 160],
                        bInfo: false,
                        order: [],
                        
                        "fnDrawCallback": function (oSettings) {
                            //TOOLTIPS
                            cDashMain.fn.setPopoverTooltips();
                            feather.replace();
                           // $('.c-page-loading').fadeOut();
                        }
                    };

                    if (typeof data !== 'undefined' && typeof data === 'object' && data !== null) {
                        for (var prop in data) {
                            if (data.hasOwnProperty(prop)) {
                                args[prop] = data[prop];
                            }
                        }
                    }
                    cForm.el.dataTables = $(this).find('table').DataTable(args);
                    cForm.el.dataTablesSelector = $(this).find('table');
                    $('<div class="clear"></div>').insertBefore($(this).find('table'));
                    $('<div class="clear"></div>').appendTo($(this).find(' > *:first-child'));
                });
            },
            reloadDataTables: function(startdate='',enddate=''){
                var tbl = $(".c-tbl").data("tbl");
                var datas = $('.c-tbl').data('table'), rangeDate='';
                var arr = $.map( datas, function( a,i ) {
                    return i+"="+a;
                }).join("&");

                if(pagenow=="admin_page_comm_order"){
                    rangeDate="&startDate="+startdate+"&enddate="+enddate;
                }

                cForm.el.dataTables.ajax.url(cApiSettings.get_list_data+arr+rangeDate).load(function (res) {
                    cDashMain.fn.setPopoverTooltips();
                    feather.replace();
                    if(tbl=="product"){
                        cProduct.fn.setFilterCount();
                    }
                    if(tbl=="manage_cs"){
                        cManageCS.fn.setFilterCount();
                    }
                    if(tbl=="orders"){
                        cOrders.fn.setFilterCount();
                    }
                    if(tbl=="comm_wa_followup"){
                        cWAFollowup.fn.setFilterCount();
                    }
                    if(tbl=="comm_ar"){
                        cAutoresponder.fn.setFilterCount();
                    }
                });
            },
        },
        run: function () {
            //WINDOW LOAD
            cForm.el.window.on("load",function () {

            });
            //DOCUMENT READY
            cForm.el.document.ready(function () {
                cForm.el.selectorID ='';
                cForm.fn.generateSelect2($(".c-select2"),true,[',']);
            });
        }
    };
    cForm.run();
})( jQuery );



