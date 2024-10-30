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
	window.cThankyouConfirmationPaymentStandalone = {
		el: {
			window: $(window),
			document: $(document),
			confirmation_payment_button: $('.btn-place-confirmation-payment')
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
			generateDataRange: function (el) {
				var start = moment();
				var end = moment();
				$(el).daterangepicker({
					startDate: start,
					"singleDatePicker": true,
					"showDropdowns": true,
					"autoApply": true,
					locale: {
						format: "MMMM D, YYYY",
					},
				});
			},
		},
		run: function () {
			cThankyouConfirmationPaymentStandalone.el.document.ready(function () {
				cPublic.fn.setPopoverTooltips();
				cThankyouConfirmationPaymentStandalone.fn.generateDataRange("#transfer_date");
				var form = document.querySelector("#commercioo-confirmation-payment-form");
				if(form) {
					form.onsubmit = submitted.bind(form);
				}
				function submitted(event) {
					cThankyouConfirmationPaymentStandalone.el.confirmation_payment_button.prop('disabled', true);
				}
				cThankyouStandalone.el.document.on("change", '#transfer_file', function (e) {
					var fileName = e.target.files[0].name;
					var _size = e.target.files[0].size;
					var output = fileName.substr(0, fileName.lastIndexOf('.')) || fileName;
					var file_ext = fileName.substring(fileName.lastIndexOf('.')+1);
					if (fileName.length > 45){
						fileName = output.substring(0,45)+'...'+file_ext;
					}
					$(".commercioo_confirmation_payment_file_size").val(_size);
					$("#bukti_transfer_file").val(fileName);
				});
			});
		}
	};
	cThankyouConfirmationPaymentStandalone.run();
})(jQuery);