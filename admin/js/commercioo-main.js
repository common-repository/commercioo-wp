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
    window.cDashMain = {
        el: {
            window: $(window),
            document: $(document),
            last_nav_menu_settings:'general-settings'
        },
        fn: {
            commercioMainCSOnLoad:function() {

                $('#responsive-menu').click(function () {
                    $("#menu").slideToggle(300);
                });

                $('.submenu').click(function (e) {
                    e.preventDefault();
                    $("#media-sub").slideToggle(300);
                    $('.submenu').toggleClass('downarrow');
                });

                feather.replace()

            },
            setPopoverTooltips:function(){
                var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
                var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                    return new bootstrap.Popover(popoverTriggerEl)
                });
            },
            loading: function (event,txt_msg,icon,duration=0) {
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
                    }, duration);
                }
            },
        },
        run: function () {
            //WINDOW LOAD
            cDashMain.el.window.on("load",function () {
                $('#wpfooter,#adminmenumain,#wpadminbar,#wp-auth-check-wrap, .notice.is-dismissible, #wpbody-content > *:not(.c-wrapper):not(.clear)').remove();
               $('.c-page-loading').fadeOut();
            });
            //DOCUMENT READY
            cDashMain.el.document.ready(function () {
                cDashMain.fn.commercioMainCSOnLoad();
                cDashMain.fn.setPopoverTooltips();
                $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                    var target = $(e.target).attr("data-page-menu"); // activated tab
                    cDashMain.el.last_nav_menu_settings=target;
                    // if(cDashMain.el.last_nav_menu_settings!="auto-responder"){
                    //     $('.c-add-auto-responder').hide();
                    //     $('.c-list-auto-responder').show();
                    //     $('.c-btn-add-autoresponder').show();
                    //     $("#auto-responder form")[0].reset();
                    //     $("#auto-responder .comm-hidden").hide();
                    // }
                });
            });
        }
    };
    cDashMain.run();
})( jQuery );



