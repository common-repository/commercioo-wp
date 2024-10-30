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
    window.cAdmin = {
        el: {
            window: $(window),
            document: $(document),

        },
        fn: {
            setCookie:function(name,value,days) {
                var expires = "", path = "; path=/";
                if (days) {
                    var date = new Date();
                    date.setTime(date.getTime() + (days*24*60*60*1000));
                    expires = "; expires=" + date.toUTCString();
                    path = "; path=/";
                }
                document.cookie = name + "=" + (value+","+cAdmin.fn.getPathCookies() || "")  + expires + path;
            },
            getPathCookies:function(){
                return window.location.pathname+window.location.search;
            },
            checkCookies:function(name){
                var data = cAdmin.fn.getCookie(name);
                if(data !==null) {
                    var arr_data = data.split(",");
                    if (cAdmin.fn.getPathCookies() != arr_data[1]) {
                        cAdmin.fn.eraseCookie('comm-id');
                        return false;
                    }
                    return true;
                }else{
                    return false;
                }
            },
            getCookie:function(name) {
                var nameEQ = name + "=";
                var ca = document.cookie.split(';');
                for(var i=0;i < ca.length;i++) {
                    var c = ca[i];
                    while (c.charAt(0)==' ') c = c.substring(1,c.length);
                    if (c.indexOf(nameEQ) == 0){
                        return c.substring(nameEQ.length,c.length);
                    }
                }
                return null;
            },
            getCookieValue:function(name){
                var nameEQ = name + "=";
                var ca = document.cookie.split(';');
                for(var i=0;i < ca.length;i++) {
                    var c = ca[i];
                    while (c.charAt(0)==' ') c = c.substring(1,c.length);
                    if (c.indexOf(nameEQ) == 0){
                        var data = c.substring(nameEQ.length,c.length);
                        var id = data.split(",");
                        return id[0];
                    }
                }
                return null;
            },
            eraseCookie:function(name) {
                document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
            }
        },
        run: function () {
            //WINDOW LOAD
            cAdmin.el.window.on("load",function () {
                cAdmin.fn.checkCookies('comm-id');
            });

            cAdmin.el.document.ready(function () {
                cAdmin.el.document.on( 'click', '.commercioo_update_btn:not(".disabled")', function(e){
                    $(this).text("Please wait.. Update is still running");
                    $(".db-update-license-loading").removeClass("d-none");
                    window.location.href = $(this).attr("href");
                    $(this).addClass("disabled");
                    $(this).prop("disabled",true);
                    $(this).off("click").attr("href","javascript: void(0);");

                });
                $( document.body ).on( 'click', 'button.comm-js_copy-billing', function(e){
                    e.preventDefault();

                    $( '#fieldset-billing' ).find( 'input, select' ).each( function( i, el ) {
                        // The address keys match up, except for the prefix
                        var shipName = el.name.replace( '_billing_', '_shipping_');
                        // Swap prefix, then check if there are any elements
                        var shipEl = $( '[name="' + shipName + '"]' );

                        // No corresponding shipping field, skip this item
                        if ( ! shipEl.length ) {
                            return;
                        }

                        // Found a matching shipping element, update the value
                        $(shipEl).val( el.value ).trigger( 'change' );
                    } );
                });
            });
        }
    };
    cAdmin.run();
})( jQuery );
