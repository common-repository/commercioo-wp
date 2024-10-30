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
    window.cSettings = {
        el: {
            window: $(window),
            document: $(document),
            dataTables: "",
        },
        fn: {
            addBankAccount: function () {
                cSettings.el.document.on('click', '.add-bank-account', function () {
                    var html = $('.c-bank-account').first().clone();
                    html.find('input').each(function() {
                        var $this = $(this);
                        $this.attr('id', $this.attr('id').replace(/_(\d+)_/, function($0, $1) {
                            return '_' + (+$1 + 1) + '_';
                        }));
                        $this.attr('name', $this.attr('name').replace(/\[(\d+)\]/, function($0, $1) {
                            return '[' + (+$1 + 1) + ']';
                        }));
                        $this.val('');
                    });
                    $(html).appendTo('.c-payments-bank-item');
                    $(html).find('.remove-bank-account').show();
                    cSettings.fn.relocateInputBank();
                    cSettings.fn.removeBankAccount();
                });
            },
            removeBankAccount: function () {
                $('.remove-bank-account').off();
                $('.remove-bank-account').click(function () {
                    $(this).closest('.form-bank-account').remove();
                    cSettings.fn.relocateInputBank();
                });
            },
            relocateInputBank: function(){
                $(".c-bank-account").each(function ($0, $1) {
                    var el_index = $0;
                    $(this).find('input').each(function () {
                        var $this = $(this);
                        $this.attr('id', $this.attr('id').replace(/_(\d+)_/, function ($0, $1) {
                            return '_' + (el_index) + '_';
                        }));
                        $this.attr('name', $this.attr('name').replace(/\[(\d+)\]/, function ($0, $1) {
                            return '[' + (el_index) + ']';
                        }));
                    });
                });
            },
            selectFilterProduct: function () {
                $('.c-select-filter-products').select2({
                    dropdownAutoWidth: true,
                    allowClear: true,
                    placeholder: "All Product",
                });
            },
            listFollowupMessage: function () {
                cSettings.el.dataTables = $('.c-table-followup-messages').dataTable({
                    "columnDefs": [{
                        "targets": 1,
                    }],
                });
            },
            filterProductSearchAction: function (selector, columnId) {
                var fv = selector.val();
                if ((fv == '') || (fv == null)) {
                    cSettings.el.dataTables.api().column(columnId).search('', true, false).draw();
                } else {
                    cSettings.el.dataTables.api().column(columnId).search(fv, true, false).draw();
                }
            },
            filterProductSearchInit: function () {
                $('.c-select-filter-products').change(function () {
                    cSettings.fn.filterProductSearchAction($(this), 1)
                });
            },
            resizeColorPicker: function ($picker_el) {
                var rem_int = parseInt(getComputedStyle(document.documentElement).fontSize),
                    width_int = $picker_el.parent().width() - ((rem_int * .75) * 2) - 2,
                    colorPicker_obj = $picker_el.colorpicker('colorpicker'),
                    slider_obj = colorPicker_obj.options.slidersHorz;

                slider_obj.alpha.maxLeft = width_int;
                slider_obj.alpha.maxTop = 0;

                slider_obj.hue.maxLeft = width_int;
                slider_obj.hue.maxTop = 0;

                slider_obj.saturation.maxLeft = width_int;
                slider_obj.saturation.maxTop = 150;

                colorPicker_obj.update();
            },
            initColorPicker: function (id_str, format_str = 'hex') {

                if (!id_str.startsWith('#')) {
                    id_str = '#' + id_str;
                }

                var $picker_el = $(id_str);

                $picker_el.colorpicker({
                    format: format_str,
                    horizontal: true,
                    popover: {
                        container: id_str + '-container'
                    },
                    template: '<div class="colorpicker">' +
                    '<div class="colorpicker-saturation"><i class="colorpicker-guide"></i></div>' +
                    '<div class="colorpicker-hue"><i class="colorpicker-guide"></i></div>' +
                    '<div class="colorpicker-alpha">' +
                    '    <div class="colorpicker-alpha-color"></div>' +
                    '    <i class="colorpicker-guide"></i>' +
                    '</div>' +
                    '<div class="colorpicker-bar">' +
                    '    <div class="input-group">' +
                    '        <input class="form-control input-block color-io" />' +
                    '    </div>' +
                    '</div>' +
                    '</div>'
                }).on('colorpickerCreate colorpickerUpdate', function (e) {
                    $picker_el.parent().find('.colorpicker-input-addon>i').css('background-color', e.value);
                }).on('colorpickerCreate', function (e) {
                    cSettings.fn.resizeColorPicker($picker_el);
                }).on('colorpickerShow', function (e) {
                    var cpInput_el = e.colorpicker.popupHandler.popoverTip.find('.color-io');

                    cpInput_el.val(e.color.string());

                    cpInput_el.on('change keyup', function () {
                        e.colorpicker.setValue(cpInput_el.val());
                    });
                }).on('colorpickerHide', function (e) {
                    var cpInput_el = e.colorpicker.popupHandler.popoverTip.find('.color-io');
                    cpInput_el.off('change keyup');
                }).on('colorpickerChange', function (e) {
                    var cpInput_el = e.colorpicker.popupHandler.popoverTip.find('.color-io');

                    if (e.value === cpInput_el.val() || !e.color || !e.color.isValid()) {
                        return;
                    }

                    cpInput_el.val(e.color.string());
                });

                $picker_el.parent().find('.colorpicker-input-addon>i').on('click', function (e) {
                    $picker_el.colorpicker('colorpicker').show();
                });

                $(window).resize(function (e) {
                    cSettings.fn.resizeColorPicker($picker_el);
                });
            },
            post: function (url, dataStore) {
                var comm_key = false;
                var comm_sub_key= false;
                var restore_to_shortcode = false;
                // get comm_key
                for (var k in dataStore) {
                    var o = dataStore[k];
                    if (o.name == 'comm_key') {
                        comm_key = o.value
                    }
                    if (o.name == 'comm_key_settings') {
                        comm_sub_key = o.value
                    }
                    if (o.name == 'restore_to_shortcode') {
                        restore_to_shortcode = o.value
                    }
                }

                // store bank info contents
                if(comm_key=="gateways"){
                    if(comm_sub_key=="gateways_bacs"){
                        dataStore.push({
                            name: 'bank_info_content_message',
                            value: tinymce.editors.bank_info_content_message.getContent()
                        });
                    }
                }

                // store emails contents
                if (comm_key == "emails") {
                    for (var k in dataStore) {
                        var o = dataStore[k];
                        switch (o.name) {
                            case 'mail_new_account_cs[content]':
                                // dataStore[k].value = tinymce.editors.mail_new_account_cs.getContent();
                                dataStore[k].value = tinymce.editors.mail_body.getContent();
                                break;

                            case 'mail_new_account_customer[content]':
                                // dataStore[k].value = tinymce.editors.mail_new_account_customer.getContent();
                                dataStore[k].value = tinymce.editors.mail_body.getContent();
                                break;

                            case 'mail_reset_password[content]':
                                // dataStore[k].value = tinymce.editors.mail_reset_password.getContent();
                                dataStore[k].value = tinymce.editors.mail_body.getContent();
                                break;

                            case 'mail_pending_order[content]':
                                // dataStore[k].value = tinymce.editors.mail_pending_order.getContent();
                                dataStore[k].value = tinymce.editors.mail_body.getContent();
                                break;

                            case 'mail_processing_order[content]':
                                // dataStore[k].value = tinymce.editors.mail_processing_order.getContent();
                                dataStore[k].value = tinymce.editors.mail_body.getContent();
                                break;

                            case 'mail_completed_order[content]':
                                // dataStore[k].value = tinymce.editors.mail_completed_order.getContent();
                                dataStore[k].value = tinymce.editors.mail_body.getContent();
                                break;

                            case 'mail_refund_order[content]':
                                // dataStore[k].value = tinymce.editors.mail_refund_order.getContent();
                                dataStore[k].value = tinymce.editors.mail_body.getContent();
                                break;

                            case 'mail_failed_order[content]':
                                // dataStore[k].value = tinymce.editors.mail_failed_order.getContent();
                                dataStore[k].value = tinymce.editors.mail_body.getContent();
                                break;

                            case 'mail_cancelled_order[content]':
                                // dataStore[k].value = tinymce.editors.mail_cancelled_order.getContent();
                                dataStore[k].value = tinymce.editors.mail_body.getContent();
                                break;

                            case 'mail_admin_new_order_notification[content]':
                                // dataStore[k].value = tinymce.editors.mail_admin_new_order_notification.getContent();
                                dataStore[k].value = tinymce.editors.mail_body.getContent();
                                break;

                            case 'mail_admin_new_customer_account_notification[content]':
                                // dataStore[k].value = tinymce.editors.mail_admin_new_customer_account_notification.getContent();
                                dataStore[k].value = tinymce.editors.mail_body.getContent();
                                break;
                        
                            default:
                                break;
                        }
                    }
                }

                if (comm_key == "login_register") {
                    for (var k in dataStore) {
                        var o = dataStore[k];

                        switch (o.name) {
                            case 'login_message':
                                dataStore[k].value = tinymce.editors.message_login.getContent();
                                break;
                            case 'register_message':
                                dataStore[k].value = tinymce.editors.message_register.getContent();
                                break;
                            case 'agreement_message':
                                dataStore[k].value = tinymce.editors.message_agreement.getContent();
                                break;
                            case 'forgot_message':
                                dataStore[k].value = tinymce.editors.message_forgot.getContent();
                                break;
                                
                            default:
                                break;
                        }
                    }
                }

                $.ajax({
                    url: url,
                    method: "POST",
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', cApiSettings.nonce);
                        cForm.fn.loading("show");
                    },
                    data: dataStore,
                }).done(function (response) {
                    cForm.fn.loading("hide");
                    if (comm_key == 'emails') {
                        $('.comm-email-form-settings').modal('hide');
                    }

                    if (comm_key == 'gateways') {
                        $(".check_status").prop("checked",false);
                        $(".sub-tab-menu").find("i.commerioo-icon-sbu-tabs").removeClass("active");
                        if(response.payment_option) {
                            Object.keys(response.payment_option).forEach(function (key) {
                                if ($("input[name='payment_option[" + key + "]']").length > 0) {
                                    $("input[name='payment_option[" + key + "]']").prop("checked", true);
                                }
                                if ($(".sub-tab-menu").find("i.commerioo-icon-sbu-tabs." + key).length > 0) {
                                    $(".sub-tab-menu").find("i.commerioo-icon-sbu-tabs." + key).addClass("active");
                                }
                            });
                        }
                    }
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });

                    Toast.fire({
                        type: 'success',
                        title: "sukses"
                    });
                    if (comm_key == 'order_forms') {
                        if ( $("input[name='checkout_elementor_url']").length ) {
                            if(restore_to_shortcode){
                                cForm.fn.loading("show");
                                window.location.href = $("input[name='checkout_elementor_url']").val();
                            }
                        }
                    }
                }).fail(function (jqXHR, textStatus, error) {
                    if (typeof jqXHR.responseJSON !== "undefined") {
                        if (typeof jqXHR.responseJSON.message !== "undefined") {
                            cForm.fn.loading("hide");
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });

                            Toast.fire({
                                type: 'error',
                                title: jqXHR.responseJSON.message
                            });
                            if (typeof jqXHR.responseJSON.data !== "undefined") {
                                if (typeof jqXHR.responseJSON.data.status !== "undefined") {
                                    if (jqXHR.responseJSON.data.status===403){
                                        window.location.reload(true);
                                    }
                                }
                            }
                        }
                    }
                });
            },
            get: function (url) {
                $.ajax({
                    url: url,
                    method: "GET",
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', cApiSettings.nonce);
                    },
                }).done(function (response) {
                    switch (response.name) {
                        case 'emails':
                            // clone and set value of emails
                            $('div[data-description=description]').empty();
                            var text = $('div[data-email-desc=' + response.email_name).clone().removeClass('d-none');
                            $('div[data-description=description]').html(text);
                            // set name form in modal
                            $('.comm-email-form-settings input[type=text]').attr('name', response.email_name + '[subject]').val(response.subject);
                            $('.comm-email-form-settings textarea').attr('name', response.email_name + '[content]');
                            // set value
                            $('.comm-email-form-settings input[name=comm_settings_email_name]').val(response.email_name)
                            tinymce.editors['mail_body'].setContent(response.content);

                            $('.comm-email-form-settings .modal-content form').show()
                            break;
                    
                        default:
                            break;
                    }
                }).fail(function (jqXHR, textStatus, error) {
                    if (typeof jqXHR.responseJSON !== "undefined") {
                        if (typeof jqXHR.responseJSON.message !== "undefined") {
                            cForm.fn.loading("hide");
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });

                            Toast.fire({
                                type: 'error',
                                title: jqXHR.responseJSON.message
                            });
                        }
                    }
                });
            }
        },
        run: function () {
            //WINDOW LOAD
            cSettings.el.window.on("load", function () {
                var editor = [
                    'def_typ_msg', 
                    'mail_new_account_cs', 
                    'mail_new_account_customer',
                    'mail_reset_password',
                    'mail_pending_order',
                    'mail_processing_order',
                    'mail_completed_order',
                    'mail_refund_order',
                    'mail_failed_order',
                    'mail_cancelled_order',
                    'mail_admin_new_order_notification',
                    'mail_admin_new_customer_account_notification',
                    'message_login',
                    'message_register',
                    'message_agreement',
                    'mail_body',
                    'message_forgot',
                    'bank_info_content_message',
                ];
                editor.forEach(function (id) {
                    wp.editor.initialize(id, cApiSettings.tinymce_config);
                });

                if($("#paypal_sandbox").prop("checked")){
                    $(".commercioo_paypal_is_mode_sandbox").removeClass("d-none");
                }
                // console.log(cApiSettings.comm_options.def_typ_msg);                
                // tinymce.get('def_typ_msg').setContent(cApiSettings.comm_options.def_typ_msg);
            });
            //DOCUMENT READY
            cSettings.el.document.ready(function () {
                $('.c-color-picker-bg').wpColorPicker();

                $('input:checkbox[name="billing_address[billing_company_show]"]').on("click", function () {
                    $('input[name="billing_address[billing_company]"]').toggle();
                });

                $('input:checkbox[name="order_note_show"]').on("click", function () {
                    $('input[name="order_note"]').toggle();
                });

                $('input:checkbox[name="billing_address[billing_email_hide]"]').on("click", function () {
                    $('input[name="billing_address[billing_email]"]').toggle();
                });

                $('.c-payments-paypal-item').hide();
                $('.c-payments-paypal-minus').hide();

                $('.c-payments-paypal-plus').on("click", function () {
                    $(".c-payments-paypal-item").slideToggle("fast");
                    $('.c-payments-paypal-minus').show();
                    $('.c-payments-paypal-plus').hide();
                });

                $('.c-payments-paypal-minus').on("click", function () {
                    $(".c-payments-paypal-item").slideToggle("fast");
                    $('.c-payments-paypal-plus').show();
                    $('.c-payments-paypal-minus').hide();
                });

                $('.remove-bank-account:first').hide();

                // $('.c-payments-bank-item').hide();
                // $('.c-payments-bank-minus').hide();

                $('.c-payments-bank-plus').on("click", function () {
                    $(".c-payments-bank-item").slideToggle("fast");
                    $('.c-payments-bank-minus').show();
                    $('.c-payments-bank-plus').hide();
                });

                $('.c-payments-bank-minus').on("click", function () {
                    $(".c-payments-bank-item").slideToggle("fast");
                    $('.c-payments-bank-plus').show();
                    $('.c-payments-bank-minus').hide();
                });
                cSettings.fn.addBankAccount();
                cSettings.fn.removeBankAccount();

                cSettings.fn.selectFilterProduct();
                cSettings.fn.listFollowupMessage();
                cSettings.fn.filterProductSearchInit();

                // cSettings.fn.resizeColorPicker();

                // Save Settings
                cSettings.el.document.on('click', '.c-save-settings', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    cSettings.fn.post(cApiSettings.root + 'commercioo/v1/settings/', $(this).closest("form").serializeArray());
                });

                cSettings.el.document.on('click', '#comm_order_form_shipping_status', function (e) {
                     $(".comm_order_form_shipping_address").toggle();
                });
                $('div#shipping .tab-pane:first-child').addClass('active');
                $('div#shipping .sub-tab li:first-child a').addClass('active');
            });
            
            // UPLOAD STORE LOGO
            cSettings.el.document.on('click', '.browse-store-logo', function (e) {
                e.preventDefault();
                if (cSettings.el.frameFeatured) {
                    cSettings.el.frameFeatured.open();
                    return;
                }
                // Create a new media frame
                cSettings.el.frameFeatured = wp.media({
                    title: 'Select or Upload Media Of Your Chosen Persuasion',
                    button: {
                        text: 'Use this media'
                    },
                    multiple: false  // Set to true to allow multiple files to be selected
                });

                cSettings.el.frameFeatured.on('open', function () {
                    var selectedImageIDs = $(".c-image-featured").val();
                    if (selectedImageIDs != '') {

                        var selection = cSettings.el.frameFeatured.state().get('selection');
                        var ids = selectedImageIDs.split(',');
                        ids.forEach(function (id) {
                            var attachment = wp.media.attachment(id);
                            attachment.fetch();
                            selection.add(attachment ? [attachment] : []);
                        });
                    }
                });

                // When an image is selected in the media frame...
                cSettings.el.frameFeatured.on('select', function () {
                    // Get media attachment details from the frame state
                    var attachment = cSettings.el.frameFeatured.state().get('selection').first().toJSON();

                    // Send the attachment URL to our custom image input field.
                    $(".store-logo-preview").html("");
                    $(".store-logo-preview").append('<div class="c-preview-image c-feature-image"><img' +
                        ' class="preview c-img-thumbnail" id="preview" src="' + attachment.url + '" alt=""' +
                        ' style="max-width:100%;"/><div class="c-close-icon-wrap' +
                        ' remove-store-logo"><i class="feather-16 c-close-wrap"' +
                        ' data-feather="x"></i></div></div>');

                    $("input[name=store_logo]").val(attachment.id);
                    feather.replace();
                });

                // Finally, open the modal on click
                cSettings.el.frameFeatured.open();
            });

            // REMOVE STORE LOGO
            cSettings.el.document.on('click', '.remove-store-logo', function (e) {
                e.preventDefault();
                $(this).closest(".c-feature-image").remove();
                $("input[name=store_logo]").val('');
            });
            
            // EMAIL MODAL
            cSettings.el.document.on('click', '.c-link', function (e) {
                e.preventDefault();
                $('.c-settings-modal-heading span').text( $(this).text() );
                var dataStore = jQuery.param({
                    name: 'emails',
                    email_name: $(this).data('email')
                })
                $('.comm-email-form-settings .modal-content form').hide()
                cSettings.fn.get(cApiSettings.root + 'commercioo/v1/settings/?' + dataStore);
            });

            // Switch to elementor button
            cSettings.el.document.on('click', '#commerciooCheckoutElementor', function (e) {
                e.preventDefault();
                var dataStore = $(this).closest("form").serializeArray();
                dataStore.push({
                    name: 'restore_to_shortcode',
                    value: "elementor"
                });
                cSettings.fn.post(cApiSettings.root + 'commercioo/v1/settings/', dataStore);
            });
            // Switch to shortcode default general checkout page
            cSettings.el.document.on('click', '#commercioo_restore_default_general', function (e) {
                e.preventDefault();
                Swal.fire({
                    title: commlang.generic.caution,
                    text: commlang.generic.restore_to_shortcode_default_general_checkout,
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: commlang.generic.yes
                }).then((result) => {
                    if (result.value) {
                        var dataStore = $(this).closest("form").serializeArray();
                        dataStore.push({
                            name: 'restore_to_shortcode',
                            value: "restore"
                        });
                        cSettings.fn.post(cApiSettings.root + 'commercioo/v1/settings/', dataStore);
                    }
                });
            })
            // Switch to elementor button
            cSettings.el.document.on('click', '#paypal_sandbox', function (e) {
                if($(this).prop("checked")){
                    $(".commercioo_paypal_is_mode_sandbox").removeClass("d-none");
                }else{
                    $(".commercioo_paypal_is_mode_sandbox").addClass("d-none");
                }
            });
        }
    };
    cSettings.run();
})(jQuery);