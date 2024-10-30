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
	window.cThankyouStandalone = {
		el: {
			window: $(window),
			document: $(document),
		},
		fn: {
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
			cThankyouStandalone.el.document.ready(function () {
				$(".sub-menu").removeClass("d-none");
				$(".utility-nav").removeClass("d-none");
				var color = $(".commercioo-checkout-container").attr("data-color");
				if(color){
					$(".c-commercioo-color, .cc-set-color-red").css('color',color);
					$(".c-btn-payment-confirmation").css('background-color',color);
				}
				cPublic.fn.setPopoverTooltips();
				cThankyouStandalone.el.document.on("click", '.c-copy-to-clip-total', function (e) {
					var copyText = $(this).closest("div").find('.commercioo_total').html();
					document.addEventListener('copy', function (e) {
						e.clipboardData.setData('text/plain', copyText);
						e.preventDefault();
					}, true);
					document.execCommand('copy');
					cThankyouStandalone.fn.loading("show",'Copied',"success");
					cThankyouStandalone.fn.loading("hide",'',"hide",1500);
				});
				cThankyouStandalone.el.document.on("click", '.c-copy-bank-account', function (e) {
					var copyText = $(this).closest("div").find('.commercioo-copy-bank-account').html();
					document.addEventListener('copy', function (e) {
						e.clipboardData.setData('text/plain', copyText);
						e.preventDefault();
					}, true);
					document.execCommand('copy');
					cThankyouStandalone.fn.loading("show",'Copied',"success");
					cThankyouStandalone.fn.loading("hide",'',"hide",1500);
				});
				// move the content to the end of the body
			});
		}
	};
	cThankyouStandalone.run();
})(jQuery);