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
	window.cCheckoutStandalone = {
		el: {
			window: $(window),
			document: $(document),
			order_summary: $('.commercioo-checkout-order-summary'),
			order_button: $('.btn-place-order')
		},
		fn: {
			get_shipping_checkout: function(type="all",status=true) {
				if(!cPublic.el.ajax_in_process){
					return;
				}
				if(type==="all"){
					type = "billing";
				}
				if($(".checkbox-shipping:checkbox").prop("checked")===true && type==="billing"){
					return;
				}
				cPublic.el.ajax_in_process = false;
				cPublic.fn.check_purchase_order_btn(false);

				// if($(".produk-grandtotal").length>0) {
				$(".commercioo-checkout-container .grand_total").html(cPublic.fn.placeholder_ui());
				// }

				// var template_load = $.templates("#placeholder-order-summary-template");
				// var htmlOutput_load = template_load.render();
				if($(".produk-shipping").length>0) {
					$(".produk-shipping").html(cPublic.fn.placeholder_ui());
				}

				var values = $('#commercioo-checkout-form').serialize();
				var ctp_data_form = {
					action: 'get_shipping_checkout',
					commercioo_nonce: cApiSettingsPublic.nonce,
					type_shipping: type,
					ship_to_different_address: ($(".checkbox-shipping:checkbox").prop("checked")===true)?'on':'off',
					checkout_data: values,
					post_id: cApiSettingsPublic.post_id,
					is_elementor:($("input[name='is_elementor']").length>0)?$("input[name='is_elementor']").val():false
				};
				$.ajax({
					url: cApiSettingsPublic.admin_ajax_url,
					method: 'post',
					beforeSend: function (xhr) {
						xhr.setRequestHeader('X-WP-Nonce', cApiSettingsPublic.nonce);
					},
					data: ctp_data_form,
					dataType: "json",
					cache: false,
				}).done(function (response) {
				}).fail(function (jqXHR, textStatus, error) {
					cPublic.fn.loading_unblockUI(cPublic.el.checkout_form_checkout);
					cPublic.el.ajax_in_process = true;
					cPublic.fn.check_purchase_order_btn();
				});
			},
		},
		run: function () {
			//WINDOW LOAD
			cCheckoutStandalone.el.window.on("load", function () {
				$("[data-bs-toggle=popover]").popover();
			});

			cCheckoutStandalone.el.document.ready(function () {

				if($('.radio-payment[type=radio]:first').length > 0){
					$('.radio-payment[type=radio]:first').prop('checked', true);
				}
				// $('.radio-payment[type=radio]:first').prop('checked', true);
				// cCheckoutStandalone.fn.get_payment_method_checkout();
				/*
				function ship_to_different_address_toggle() {				
					// toggle hide-show
					cCheckoutStandalone.el.document.on('change', '.checkbox-shipping:checkbox', function () {
						$('.show-form-ship-different').toggle();

						// save checked state
						localStorage.setItem('order-checkbox-shipping-address', $(this).is(":checked"));
					});

					// is checked
					if ( localStorage.getItem('order-checkbox-shipping-address') == 'true' ) {
						$('.checkbox-shipping').prop('checked', true);
						$('.show-form-ship-different').show();
					}
					else {
						$('.show-form-ship-different').hide();
					}
				}
				*/
				var form = document.querySelector("#commercioo-checkout-form");
				if ( form ) {
					form.onsubmit = submitted.bind(form);
				}

				function submitted(event) {
					cCheckoutStandalone.el.order_button.prop('disabled', true);
				}

				cCheckoutStandalone.el.document.on( 'change', '[name=shipping_cost]', function() {
					cPublic.el.ajax_in_process = false;
					cPublic.fn.check_purchase_order_btn();
					var type = $(this).closest("#checkout-shipping-options").data("type");
					cPublic.fn.set_shipping(type);
				});

				function payment_methods_toggle() {
					$(".show-paypal").hide();
					$(".show-credit-card").hide();

					cCheckoutStandalone.el.document.on('change', '.radio-show-direct-bank', function () {
						$(".show-direct-bank").show();
						$(".show-paypal").hide();
						$(".show-credit-card").hide();
					});

					cCheckoutStandalone.el.document.on('change', '.radio-show-paypal', function () {
						$(".show-direct-bank").hide();
						$(".show-paypal").show();
						$(".show-credit-card").hide();
					});

					cCheckoutStandalone.el.document.on('change', '.radio-show-credit-card', function () {
						$(".show-direct-bank").hide();
						$(".show-paypal").hide();
						$(".show-credit-card").show();
					});
				}

				/**
				 * We not yet need this, because currently we only support a `direct-back-transfer` method
				 * -----------
				 * payment_methods_toggle();
				 */

				// ship_to_different_address checkbox toggle
				// ship_to_different_address_toggle();

				// move the content to the end of the body
				if(!cApiSettingsPublic.is_2step){
					$('#commercioo-checkout-standalone').appendTo(document.body);
				}

				if(cApiSettingsPublic.is_checkout_page){
					$('#commercioo-checkout-standalone').appendTo(document.body);
				}
				/**
				 * this wont be a simple hide-show toggle
				 * the field must be excluded from the form if the user wanted to
				 * so we need to move the fields outside the form tag
				 */
				$('.show-form-ship-different').hide();
				$(".checkbox-shipping:checkbox").change(function () {
					var contents = '#the-content-of-show-form-ship-different';
					var container = '#show-form-ship-different';
					if (this.checked) {
						$(contents).contents().appendTo(container);
						$(container).show();
					}
					else {
						$(container).hide();
						$(container).contents().appendTo(contents);
					}
				})

			});
		}
	};
	cCheckoutStandalone.run();
})(jQuery);