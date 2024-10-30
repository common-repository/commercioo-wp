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
    window.cProduct = {
        el: {
            window: $(window),
            document: $(document),
            frameFeatured: null,
            frameGallery: null,
            dataForm: null,
            validationForm: null,
            editorContent: null,
        },
        fn: {
            uuidv4: function () {
                return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                    var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
                    return v.toString(16);
                });
            },
            post: function (url, dataStore, forms = '') {
                if (cForm.el.selectorID) {
                    dataStore.push({name: 'id', value: cForm.el.selectorID}, {
                        name: 'content',
                        value: tinymce.editors.content.getContent()
                    }, {
                        name: 'additional_description',
                        value: tinymce.editors.additional_description.getContent()
                    }, {
                        name: 'is_featured',
                        value: $('input[name="is_featured"]').prop('checked'),
                    },{
                        name: 'overwrite_thank_you_redirect',
                        value: $('input[name="overwrite_thank_you_redirect"]').prop('checked'),
                    }, {
                        name: 'free_shipping',
                        value: $('input[name="free_shipping"]').prop('checked'),
                    });
                    url = url+cForm.el.selectorID;
                } else {
                    dataStore.push({
                        name: 'content',
                        value: tinymce.editors.content.getContent()
                    }, {
                        name: 'additional_description',
                        value: tinymce.editors.additional_description.getContent()
                    },{
                        name: 'is_featured',
                        value: $('input[name="is_featured"]').prop('checked'),
                    },{
                        name: 'overwrite_thank_you_redirect',
                        value: $('input[name="overwrite_thank_you_redirect"]').prop('checked'),
                    }, {
                        name: 'free_shipping',
                        value: $('input[name="free_shipping"]').prop('checked'),
                    });
                }

                $.ajax({
                    url: url,
                    method: "POST",
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', cApiSettings.nonce);
                        // if (cForm.el.dataFormEvent == "add" || cForm.el.dataFormEvent == "update") {
                        //     cForm.fn.loading("show");
                        // }
                    },
                    data: dataStore,
                }).done(function (response) {
                    cForm.el.selectorID = null;
                    cAdmin.fn.eraseCookie('comm-id');

                    var msg_store = commlang.generic.saveData;
                    if (cForm.el.dataFormEvent == "update") {
                        msg_store = commlang.generic.updateData;
                    }
                    cForm.fn.loading("hide");
                    cForm.el.dataFormEvent = null;
                    $('.comm-order-2-step-product').modal('hide');
                    if (response.id) {
                        cForm.fn.reloadDataTables();
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });

                        Toast.fire({
                            type: 'success',
                            title: msg_store
                        });

                        forms.removeClass("was-validated");
                        forms.find(".c-save-products").removeAttr("disabled");
                        cProduct.fn.refreshField("show");
                    } else {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });

                        Toast.fire({
                            type: 'error',
                            title: (response.data[0]) ? response.data[0].message : commlang.products.error
                        });

                        forms.removeClass("was-validated");
                        forms.find(".c-save-products").removeAttr("disabled");
                        cProduct.fn.refreshField("hide");
                    }
                    cProduct.fn.resetField();
                }).fail(function (jqXHR, textStatus, error) {
                    if (typeof jqXHR.responseJSON.message !== "undefined") {
                        cForm.fn.loading("hide");
                        cForm.fn.reloadDataTables();
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
                        forms.removeClass("was-validated");
                        forms.find(".c-save-products").removeAttr("disabled");
                        cProduct.fn.refreshField("hide");
                    }
                });
            },
            trash: function (url, id, status) {
                $.ajax({
                    url: url,
                    method: "DELETE",
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', cApiSettings.nonce);
                        cForm.fn.loading("show");
                    },
                    data: {
                        id: id,
                        tbl: "product",
                        status: status,
                        force: true
                    },
                }).done(function (response) {
                    cForm.el.selectorID = null;
                    cForm.fn.reloadDataTables();
                    cForm.fn.loading("hide");
                    if (response.status != "error") {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });

                        Toast.fire({
                            type: 'success',
                            title: commlang.products.trash
                        });
                    } else {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });

                        Toast.fire({
                            type: 'success',
                            title: commlang.products.errorTrash
                        });
                    }
                }).fail(function (jqXHR, textStatus, error) {
                    if (typeof jqXHR.responseJSON.message !== "undefined") {
                        cForm.fn.loading("hide");
                        cForm.fn.reloadDataTables();
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
                });
            },
            clone: function (url, id) {
                $.ajax({
                    url: url,
                    method: "POST",
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', cApiSettings.nonce);
                        cForm.fn.loading("show");
                    },
                    data: {
                        id: id,
                        force: true
                    },
                }).done(function (response) {
                    cForm.el.selectorID = null;
                    cForm.fn.reloadDataTables();
                    if (response.id) {
                        cForm.el.selectorID = response.id;
                        cForm.el.dataFormEvent = "clone";
                        $(".c-title-text-products").html(commlang.products.editTitle + ": " + cForm.el.selectorID);
                        cProduct.fn.get(cApiSettings.root + 'wp/v2/comm_product/' + cForm.el.selectorID, '', '');
                    } else {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });

                        Toast.fire({
                            type: 'success',
                            title: commlang.products.errorDuplicate
                        });
                    }
                }).fail(function (jqXHR, textStatus, error) {
                    if (typeof jqXHR.responseJSON.message !== "undefined") {
                        cForm.fn.loading("hide");
                        cForm.fn.reloadDataTables();
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
                });
            },
            delete: function (url, target) {
                $.ajax({
                    url: url,
                    method: "DELETE",
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', cApiSettings.nonce);
                        cForm.fn.loading("show");
                    },
                    data: {
                        force: true
                    },
                }).done(function (response) {
                    cForm.el.selectorID = null;
                    target.remove();
                    cForm.fn.reloadDataTables();
                    cForm.fn.loading("hide");

                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });

                    Toast.fire({
                        type: 'success',
                        title: commlang.generic.deleteData
                    });
                }).fail(function (jqXHR, textStatus, error) {
                    if (typeof jqXHR.responseJSON.message !== "undefined") {
                        cForm.fn.reloadDataTables();
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
                        cForm.fn.reloadDataTables();
                        cForm.fn.loading("hide");
                    }
                });
            },
            restore: function (url, id) {
                $.ajax({
                    url: url,
                    method: "POST",
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', cApiSettings.nonce);
                        cForm.fn.loading("show");
                    },
                    data: {
                        id: id,
                    },
                }).done(function (response) {
                    cForm.el.selectorID = null;
                    cForm.fn.reloadDataTables();
                    cForm.fn.loading("hide");

                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });

                    Toast.fire({
                        type: 'success',
                        title: commlang.products.restore + response.id
                    });
                }).fail(function (jqXHR, textStatus, error) {
                    if (typeof jqXHR.responseJSON.message !== "undefined") {
                        cForm.fn.reloadDataTables();
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
                        cForm.fn.reloadDataTables();
                        cForm.fn.loading("hide");
                    }
                });
            },
            emptyTrash: function (url) {
                $.ajax({
                    url: url,
                    method: "POST",
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', cApiSettings.nonce);
                        cForm.fn.loading("show");
                    },
                    data: {},
                }).done(function (response) {

                    cForm.el.selectorID = null;
                    cForm.fn.reloadDataTables();
                    cForm.fn.loading("hide");
                    if (response.message == "success") {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });

                        Toast.fire({
                            type: 'success',
                            title: commlang.products.successEmptyTrash
                        });
                    } else {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });

                        Toast.fire({
                            type: 'info',
                            title: commlang.products.noDataEmptyTrash
                        });
                    }

                }).fail(function (jqXHR, textStatus, error) {
                    if (typeof jqXHR.responseJSON.message !== "undefined") {
                        cForm.fn.reloadDataTables();
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
                        cForm.fn.reloadDataTables();
                        cForm.fn.loading("hide");
                    }
                });
            },
            get: function (url, dataStore, forms = '') {
                $.ajax({
                    url: url,
                    method: "GET",
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', cApiSettings.nonce);
                        cForm.fn.loading("show");
                    },
                    data: dataStore,
                }).done(function (response) {
                    cForm.fn.loading("hide");
                    if(response.status=="trash") {
                        cProduct.fn.refreshField("hide");
                        $(".c-back").trigger("click");
                    }else{
                        cProduct.fn.refreshField("edit");
                        cProduct.fn.resetField();
                        if (cForm.el.dataFormEvent == "clone") {
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });

                            Toast.fire({
                                type: 'success',
                                title: commlang.products.clone + response.id
                            });
                            cForm.el.dataFormEvent = "update";
                        }

                        $("form.needs-validation").attr('novalidate', 'novalidate');
                        $('span#id_product').text(response.id);
                        $('input[name="title"]').val(response.title.rendered);
                        $('input[name="slug"]').val(response.slug);

                        tinymce.get('content').setContent(response.content.rendered);
                        tinymce.get('additional_description').setContent(response.additional_description);

                        $(".comm-order-form-url").val(cApiSettings.checkout_url+"?comm_action=add_to_cart&comm_prod_id="+parseInt(cForm.el.selectorID));
                        $(".comm-order-checkout-shortcode").val('[comm_checkout prod_id="'+ parseInt(cForm.el.selectorID) + '"]');
                        $(".comm-order-checkout-embed").val('<script type="text/javascript" src="'+cApiSettings.checkout_url+ '?comm_action=checkout_embed&comm_prod_id'+parseInt(cForm.el.selectorID)+'"></script><div id="product-'+response.slug+'"></div>');
                        $('input[name="sku"]').val(response.sku);
                        $('select[name="stock_status"]').val(response.stock_status);
                        $('input[name="regular_price"]').val(response.regular_price);
                        $('select[name="status"]').val(response.status);
                        $('input[name="weight"]').val(response.weight);

                        if(cApiSettings.is_comm_ar){
                            // var ar_form_integration = parseInt(response.ar_form_integration);
                            // $(".ar_form_integration").val(ar_form_integration);
                            $('select.ar_form_integration').val(response.ar_form_integration).trigger("change");
                        }

                        if(cApiSettings.is_comm_wa){
                            $(".wa_form_integration").val(response.whatsapp_order_msg);
                        }

                        if (response.is_featured) {
                            $('input[name="is_featured"]').prop('checked', true);
                        } else {
                            $('input[name="is_featured"]').prop('checked', false);
                        }
                        if (response.overwrite_thank_you_redirect) {
                            $('input[name="overwrite_thank_you_redirect"]').prop('checked', true);
                            $('.c-overwrite_thank_you_redirect').show();
                        } else {
                            $('input[name="overwrite_thank_you_redirect"]').prop('checked', false);
                            $('.c-overwrite_thank_you_redirect').hide();
                        }

                        if ( response.free_shipping ) {
                            $('input[name="free_shipping"]').prop( 'checked', true );
                        } else {
                            $('input[name="free_shipping"]').prop( 'checked', false );
                        }

                        // product_featured
                        wp.media.attachment(response.product_featured).fetch().then(function (data) {
                            // preloading finished
                            // after this you can use your attachment normally

                            $('select[name="comm_product_cat[]"]').val(response.comm_product_cat).trigger("change");
                            $('select[name="comm_product_tag[]"]').val(response.comm_product_tag).trigger("change");

                            $(".c-list-preview-image").append('<div class="c-preview-image c-feature-image"><img' +
                                ' class="preview c-img-thumbnail" id="preview" src="' + wp.media.attachment(response.product_featured).get('url') + '" alt=""' +
                                ' style="max-width:100%;"/><div class="c-featured-wrap text-center"><span' +
                                ' class="c-featured-text">FEATURED</span></div> <div class="c-close-icon-wrap' +
                                ' c-feature-image-text"><i class="feather-16 c-close-wrap"' +
                                ' data-feather="x"></i></div></div>');
                            feather.replace();
                        });
                        // product_gallery
                        var ids = response.product_gallery.split(',');
                        ids.forEach(function (id) {
                            wp.media.attachment(parseInt(id)).fetch().then(function (data) {
                                if (parseInt(id)) {
                                    $(".set-gallery-image").append('<div class="c-preview-image c-feature-image"' +
                                        ' data-attachment="' + parseInt(id) + '"><img' +
                                        ' class="preview c-img-thumbnail" id="preview" src="' + wp.media.attachment(parseInt(id)).get('url') + '" alt=""' +
                                        ' style="max-width:100%;"/><div class="c-featured-wrap text-center"><span' +
                                        ' class="c-featured-text">GALLERY</span></div> <div class="c-close-icon-wrap' +
                                        ' c-feature-image-text"><i class="feather-16 c-close-wrap"' +
                                        ' data-feather="x"></i></div></div>');
                                    feather.replace();
                                }
                            });
                        });

                        $('select[name="comm_product_cat[]"]').val(response.comm_product_cat).trigger("change");
                        $('select[name="comm_product_tag[]"]').val(response.comm_product_tag).trigger("change");
                        $(".c-image-featured").val(response.product_featured);
                        $(".c-image-gallery").val(response.product_gallery);

                        $('select[name="thank_you_redirect"]').val(response.thank_you_redirect);

                        // included items


                        if ($('.c-products-cloneit').length) {
                            var included_items = response.included_items;
                            var clonedSheep = JSON.parse(JSON.stringify(included_items));
                            var self = $('.c-products-cloneit').children();

                            // reset repeater
                            $('.c-products-clone').html('');

                            if (included_items.length) {
                                $.each(included_items, function (key, item) {
                                    var newel = self.clone();

                                    $(newel).find(".included_items").attr("name", "included_items[]");
                                    $(newel).find(".included_items").val(item);

                                    $('.c-products-clone').append(newel);
                                });
                            }
                            else {
                                var newel = self.clone();
                                $(newel).find(".included_items").attr("name", "included_items[]");
                                $('.c-products-clone').html($(newel));
                            }
                        }

                        // Digital product
                        if ($('.c-digital-products-cloneit').length) {
                            var digital_items = response.downloadable_products;
                            var clonedSheep = JSON.parse(JSON.stringify(digital_items));
                            var self = $('.c-digital-products-cloneit').children();

                            // reset repeater
                            $('.c-digital-products-clone').html('');

                            if (digital_items.length) {
                                let i = 0
                                $.each(digital_items, function (key, item) {
                                    var newel = self.clone();
                                    $(newel).find(".c-file-name").attr("name", "downloadable_products["+i+"]['name']");
                                    $(newel).find(".c-file-url").attr("name", "downloadable_products["+i+"]['url']");
                                    $(newel).find(".c-file-uuid").attr("name", "downloadable_products["+i+"]['uuid']");

                                    $(newel).find(".c-file-name").val(item["'name'"]);
                                    $(newel).find(".c-file-url").val(item["'url'"]);
                                    $(newel).find(".c-file-uuid").val(item["'uuid'"]);

                                    $('.c-digital-products-clone').append(newel);
                                    i++;
                                });
                            }
                            else {
                                var newel = self.clone();
                                $(newel).find(".c-file-name").attr("name", "downloadable_products[0]['name']");
                                $(newel).find(".c-file-url").attr("name", "downloadable_products[0]['url']");
                                $(newel).find(".c-file-uuid").attr("name", "downloadable_products[0]['uuid']");
                                $(newel).find(".c-file-uuid").val(cProduct.fn.uuidv4());

                                $('.c-digital-products-clone').append(newel);
                            }
                        }
                        cProduct.el.document.trigger( 'comm_edit_product_loaded', [ response ] );
                    }
                }).fail(function (jqXHR, textStatus, error) {
                    if (typeof jqXHR.responseJSON != "undefined") {
                        if (typeof jqXHR.responseJSON.message != "undefined") {
                            cForm.fn.reloadDataTables();
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
                    cForm.fn.reloadDataTables();
                    cForm.fn.loading("hide");
                    $(".c-back").trigger("click");
                });
            },
            validationForm: function () {
                var forms = document.querySelectorAll('.c-add-products-validation');
                // Loop over them and prevent submission
                var validation = Array.prototype.slice.call(forms)
                    .forEach(function (dataform) {
                        dataform.addEventListener('submit', function (event) {
                            if (dataform.checkValidity() === false) {
                                event.preventDefault();
                                event.stopPropagation();
                                $('html, body').animate({
                                    scrollTop: $('body').offset().top
                                }, 200);
                            } else {
                                event.preventDefault();
                                event.stopPropagation();
                                cForm.fn.loading("show");
                                $(dataform).find(".c-save-products").attr("disabled", "disabled");
                                cProduct.fn.post(cApiSettings.root + 'wp/v2/comm_product/', $(dataform).serializeArray(), $(dataform));
                            }

                            dataform.classList.add('was-validated')
                        }, false)
                    })
            },
            refreshField: function (status) {
                if (status == "show") {
                    $('.c-add-products').hide();
                    $('.c-list-products').show();
                    $('.c-add-products.product-title').hide();
                    $('.c-list-products.product-title').show();
                    $('.c-edit-products.product-title').hide();
                } else if (status == "edit") {
                    $('.c-add-products').show();
                    $('.c-list-products').hide();
                    $('.c-add-products.product-title').hide();
                    $('.c-list-products.product-title').hide();
                    $('.c-edit-products.product-title').show();
                } else {
                    $('.c-add-products').show();
                    $('.c-list-products').hide();
                    $('.c-add-products.product-title').show();
                    $('.c-list-products.product-title').hide();
                    $('.c-edit-products.product-title').hide();
                }
            },
            filterProductSearchAction: function (selector, columnId) {
                var fv = selector;
                if (typeof selector == "object") {
                    fv = selector.data("status");
                }
                if (fv == "any") {
                    fv = null;
                }

                if (fv == "trash") {
                    $(".dataTables_wrapper .c-empty-trash").show();
                } else {
                    $(".dataTables_wrapper .c-empty-trash").hide();
                }

                if ((fv == '') || (fv == null)) {
                    cForm.el.dataTables.column(columnId).search('publish|draft', true, false).draw();
                } else {
                    cForm.el.dataTables.column(columnId).search(fv, true, false).draw();
                }
            },
            refreshFilterCount: function (status) {
                cForm.el.dataTables.column(6).search('', true, false).draw();
                var filteredData = cForm.el.dataTables
                    .column(6, {search: 'applied'})
                    .data()
                    .filter(function (value, index) {
                        if (status == null) {
                            // status = status.charAt(0).toUpperCase() + status.slice(1);
                            // console.log(status);
                            return value != 'Trash' ? true : false;
                        } else {
                            status = status.charAt(0).toUpperCase() + status.slice(1);
                            return value == status ? true : false;
                        }

                    });

                return "(" + filteredData.count() + ")";
            },
            setFilterCount: function () {
                $(".comm_count_all").html(cProduct.fn.refreshFilterCount(null));
                $(".comm_count_publish").html(cProduct.fn.refreshFilterCount("publish"));
                $(".comm_count_draft").html(cProduct.fn.refreshFilterCount("draft"));
                $(".comm_count_trash").html(cProduct.fn.refreshFilterCount("trash"));
                cProduct.fn.filterProductSearchAction('any', 6);

                $(".comm-filter").removeClass("active");
                $(".comm-filter").eq(0).addClass("active");

            },
            resetField: function () {
                $("form").trigger("reset");

                $('select[name="comm_product_cat[]"]').val(null).trigger('change');
                $('select[name="comm_product_tag[]"]').val(null).trigger('change');

                $(".c-list-preview-image").html("");
                $(".set-gallery-image").html("");
                $('input[name="product_featured"]').val("");
                $('input[name="product_gallery"]').val("");
                $("form").removeClass("was-validated");
            },
            initialWPEditor: function () {
                cProduct.el.editorContent = wp.editor;
                var editor = ['content', 'additional_description'];
                editor.forEach(function (id) {
                    wp.editor.initialize(id, cApiSettings.tinymce_config);
                });

                var html = '<a href="#" class="c-empty-trash c-btn-empty-trash">' + commlang.products.emptyTrash + '</a>';
                $(html).insertAfter($(".dataTables_length"));
            },
        },
        run: function () {
            //WINDOW LOAD
            cProduct.el.window.on("load", function () {
                cProduct.fn.validationForm();
                cProduct.fn.initialWPEditor();
                $('.c-overwrite_thank_you_redirect').hide();
            });
            //DOCUMENT READY
            cProduct.el.document.ready(function () {
                cProduct.fn.refreshField("show");

                // GENERATE TABLE
                cForm.fn.generateTable(commlang.products.searchPlaceholderTable);
                //cProduct.fn.filterProductSearchAction('any', 6);
                // SETUP TOOLBAR WP EDITOR
                cProduct.el.document.on('tinymce-editor-setup', function (event, editor) {
                    editor.settings.toolbar1 = 'bold,italic,underline,blockquote,strikethrough,bullist,numlist,alignleft,aligncenter,alignright,undo,redo,link'; //Teeny -fullscreen
                });

                // FILTER PRODUCT
                cProduct.el.document.on('click', '.comm-filter', function (e) {
                    e.preventDefault();
                    $(".comm-filter").removeClass("active");
                    $(this).addClass("active");
                    cProduct.fn.filterProductSearchAction($(this), 6);
                });

                // SHOW PRODUCT FORM
                cProduct.el.document.on('click', '.c-btn-add-products', function (e) {
                    e.preventDefault();
                    $(".c-title-text-products").html(commlang.products.addTitle);
                    cForm.el.dataFormEvent = "add";
                    cProduct.fn.refreshField("hide");
                    $('.c-editable').hide();
                    cProduct.fn.resetField();
                    $('.c-overwrite_thank_you_redirect').hide();
                    if (typeof cAddonPro !== "undefined") {
                        cAddonPro.fn.setFieldManageStock();
                    }

                    // included items
                    if($('.c-products-cloneit').length) {
                        var selfProduct = $('.c-products-cloneit').children();
                        var newel = selfProduct.clone();
                        $(newel).find(".included_items").attr("name", "included_items[]");
                        $('.c-products-clone').html($(newel));
                    }

                    // Digital product
                    if ($('.c-digital-products-cloneit').length) {
                        $('.c-digital-products-clone').html('');
                        var selfDigital = $('.c-digital-products-cloneit').children();

                        var newel = selfDigital.clone();
                        $(newel).find(".c-file-name").attr("name", "downloadable_products[0]['name']");
                        $(newel).find(".c-file-url").attr("name", "downloadable_products[0]['url']");
                        $(newel).find(".c-file-uuid").attr("name", "downloadable_products[0]['uuid']");
                        $(newel).find(".c-file-uuid").val(cProduct.fn.uuidv4());

                        $('.c-digital-products-clone').append(newel);
                    }
                });

                // EDIT PRODUCT
                cProduct.el.document.on('click', '.c-edit', function (e) {
                    e.preventDefault();
                    cForm.el.selectorID = $(this).data("id");

                    cAdmin.fn.setCookie('comm-id', cForm.el.selectorID, "1");

                    cForm.el.dataFormEvent = "update";
                    $('.c-editable').show();
                    $(".c-title-text-products").html(commlang.products.editTitle + ": " + cForm.el.selectorID);
                    cProduct.fn.get(cApiSettings.root + 'wp/v2/comm_product/' + cForm.el.selectorID, '', '');
                });
                // CLONE PRODUCT
                cProduct.el.document.on('click', '.c-clone', function (e) {
                    e.preventDefault();
                    cForm.el.selectorID = $(this).data("id");
                    cProduct.fn.clone(cApiSettings.root + 'commercioo/v1/comm_product_clone/' + cForm.el.selectorID, cForm.el.selectorID);
                });
                // RESTORE PRODUCT
                cProduct.el.document.on('click', '.c-restore', function (e) {
                    e.preventDefault();
                    cForm.el.selectorID = $(this).data("id");
                    cProduct.fn.restore(cApiSettings.root + 'commercioo/v1/comm_product_restore/' + cForm.el.selectorID, cForm.el.selectorID);
                });
                // Empty Trash Product
                cProduct.el.document.on('click', '.c-empty-trash', function (e) {
                    e.preventDefault();

                    Swal.fire({
                        title: commlang.generic.caution,
                        text: commlang.generic.emptyTrashConfirm,
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: commlang.generic.yes
                    }).then((result) => {
                        if (result.value) {
                            cProduct.fn.emptyTrash(cApiSettings.root + 'commercioo/v1/comm_product_empty_trash/');
                        }
                    });
                });
                // DELETE PRODUCT
                cProduct.el.document.on('click', '.c-delete', function (e) {
                    e.preventDefault();
                    cForm.el.selectorID = $(this).data("id");
                    var self = $(this),
                        target = self.closest('tr'),
                        title = target.find('td:eq(2)').find("a").html();

                    if(typeof title =="undefined"){
                        title = target.find('td:eq(2)').html();
                    }
                    Swal.fire({
                        title: commlang.generic.caution,
                        text: commlang.generic.deleteConfirm + " " + title,
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: commlang.generic.yes
                    }).then((result) => {
                        if (result.value) {
                            cProduct.fn.delete(cApiSettings.root + 'wp/v2/comm_product/' + cForm.el.selectorID, target);
                        }
                    });
                });
                // TRASH PRODUCT
                cProduct.el.document.on('click', '.c-trash', function (e) {
                    e.preventDefault();
                    cForm.el.selectorID = $(this).data("id");
                    var status = $(this).data("status");
                    var self = $(this),
                        target = self.closest('tr'),
                        title = target.find('td:eq(2)').find("a").html();
                    if(typeof title =="undefined"){
                        title = target.find('td:eq(2)').html();
                    }
                    Swal.fire({
                        title: commlang.generic.caution,
                        text: commlang.generic.trashConfirm + " " + title,
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: commlang.generic.yes
                    }).then((result) => {
                        if (result.value) {
                            cProduct.fn.trash(cApiSettings.root + 'commercioo/v1/comm_product_trash/' + cForm.el.selectorID, cForm.el.selectorID, status);
                        }
                    });
                });

                // CANCEL PRODUCT FORM
                cProduct.el.document.on('click', '.c-back', function (e) {
                    e.preventDefault();
                    cProduct.fn.resetField();
                    $(".c-title-text-products").html(commlang.products.listTitle);
                    cForm.el.selectorID = null;
                    cAdmin.fn.eraseCookie('comm-id');
                    cProduct.fn.refreshField("show");
                });

                // SHOW MODAL ORDER FORM
                cProduct.el.document.on('click', '.c-show-order-form', function (e) {
                    $('.comm-order-form-product').modal('show');
                    $('.comm-order-form-product').attr("data-id",$(this).attr("data-id"));
                    $('.comm-order-form-product').attr("data-slug",$(this).attr("data-slug"));
                    if(cApiSettings.is_comm_wa){
                        $('.comm-order-form-product').attr("data-wa-url",$(this).attr("data-wa-url"));
                        $('.comm-order-form-product').attr("data-wa-info",$(this).attr("data-wa-info"));
                    }
                });

                $('.comm-order-form-product').on('shown.bs.modal', function (e) {
                    var prod_id= $(e.relatedTarget).attr("data-id");
                    $(".comm-order-form-url").val(cApiSettings.checkout_url+"?comm_action=add_to_cart&comm_prod_id="+parseInt(prod_id));
                    $(".comm-order-checkout-shortcode").val('[comm_checkout prod_id="'+ parseInt(prod_id) + '"]');

                    $(".comm-order-checkout-embed").val('<script type="text/javascript" src="'+ cApiSettings.checkout_url + '?comm_action=checkout_embed&comm_prod_id='+parseInt(prod_id)+'"></script><div id="product-embed-'+parseInt(prod_id)+'"></div>');

                    $(".comm-order-2-step-checkout-shortcode").val('[comm_2_step_checkout prod_id="'+ parseInt(prod_id) + '"]');
                });

                // Event execCommand Copy
                cProduct.el.document.on("click", '.c-copy-to-clip', function (e) {
                    var copyText = $(this).prev("input[type='text']").val();
                    document.addEventListener('copy', function (e) {
                        e.clipboardData.setData('text/plain', copyText);
                        e.preventDefault();
                    }, true);
                    document.execCommand('copy');
                    cDashMain.fn.loading("show",'Copied',"success");
                    cDashMain.fn.loading("hide",'',"hide",1500);
                });
                // REMOVE PRODUCT PHOTO
                cProduct.el.document.on('click', '.c-feature-image-text', function (e) {
                    e.preventDefault();
                    if ($(this).closest(".c-photo").hasClass("set-gallery-image")) {
                        var currentID = $(this).closest(".c-feature-image").data("attachment");
                        var galleryImage = [];
                        $(this).closest(".set-gallery-image").find(".c-feature-image").each(function (i, e) {
                            var Gid = $(this).data("attachment");
                            if (currentID != Gid) {
                                galleryImage.push(Gid);
                            }
                        });
                        $(this).closest(".c-feature-image").remove();
                        $(".c-image-gallery").val(galleryImage);
                    } else {
                        // FEATURED IMAGE
                        $(this).closest(".c-feature-image").remove();
                        $(".c-image-featured").val('');
                    }
                });

                // UPLOAD FEATURED IMAGE
                cProduct.el.document.on('click', '.browse', function (e) {
                    e.preventDefault();
                    if (cProduct.el.frameFeatured) {
                        cProduct.el.frameFeatured.open();
                        return;
                    }
                    // Create a new media frame
                    cProduct.el.frameFeatured = wp.media({
                        title: 'Select or Upload Media Of Your Chosen Persuasion',
                        button: {
                            text: 'Use this media'
                        },
                        multiple: false  // Set to true to allow multiple files to be selected
                    });

                    cProduct.el.frameFeatured.on('open', function () {
                        var selectedImageIDs = $(".c-image-featured").val();
                        if (selectedImageIDs != '') {

                            var selection = cProduct.el.frameFeatured.state().get('selection');
                            var ids = selectedImageIDs.split(',');
                            ids.forEach(function (id) {
                                var attachment = wp.media.attachment(id);
                                attachment.fetch();
                                selection.add(attachment ? [attachment] : []);
                            });
                        }
                    });

                    // When an image is selected in the media frame...
                    cProduct.el.frameFeatured.on('select', function () {

                        // Get media attachment details from the frame state
                        var attachment = cProduct.el.frameFeatured.state().get('selection').first().toJSON();

                        // Send the attachment URL to our custom image input field.
                        $(".c-list-preview-image").html("");
                        $(".c-list-preview-image").append('<div class="c-preview-image c-feature-image"><img' +
                            ' class="preview c-img-thumbnail" id="preview" src="' + attachment.url + '" alt=""' +
                            ' style="max-width:100%;"/><div class="c-featured-wrap text-center"><span' +
                            ' class="c-featured-text">FEATURED</span></div> <div class="c-close-icon-wrap' +
                            ' c-feature-image-text"><i class="feather-16 c-close-wrap"' +
                            ' data-feather="x"></i></div></div>');


                        $(".c-image-featured").val(attachment.id);
                        feather.replace();
                    });

                    // Finally, open the modal on click
                    cProduct.el.frameFeatured.open();
                });

                // UPLOAD GALLERY IMAGE

                cProduct.el.document.on('click', '.browse-gallery', function (e) {
                    e.preventDefault();

                    if (cProduct.el.frameGallery) {
                        cProduct.el.frameGallery.open();
                        return;
                    }

                    // Create a new media frame
                    cProduct.el.frameGallery = wp.media.frames.file_frame = wp.media({
                        title: 'Select or Upload Media Of Your Chosen Persuasion',
                        button: {
                            text: 'Use this media'
                        },
                        multiple: true  // Set to true to allow multiple files to be selected
                    });

                    cProduct.el.frameGallery.on('open', function () {
                        var selectedImageIDs = $(".c-image-gallery").val();
                        if (selectedImageIDs != '') {

                            var selection = cProduct.el.frameGallery.state().get('selection');
                            var ids = selectedImageIDs.split(',');
                            ids.forEach(function (id) {
                                var attachment = wp.media.attachment(id);
                                attachment.fetch();
                                selection.add(attachment ? [attachment] : []);
                            });
                        }
                    });

                    // When an image is selected in the media frame...
                    cProduct.el.frameGallery.on('select', function () {
                        // Get media attachment details from the frame state
                        // var attachment = cProduct.el.frameFeatured.state().get('selection').toJSON();
                        var attachments = cProduct.el.frameGallery.state().get('selection').map(
                            function (attachment) {
                                attachment.toJSON();
                                return attachment;

                            });
                        var selected = [];
                        // Send the attachment URL to our custom image input field.
                        $(".set-gallery-image").html("");

                        var i;
                        var gallery_index = 0;
                        for (i = 0; i < attachments.length; ++i) {
                            if (attachments[i].attributes.id) {
                                $(".set-gallery-image").append('<div class="c-preview-image c-feature-image"' +
                                    ' data-attachment="' + attachments[i].attributes.id + '"><img' +
                                    ' class="preview c-img-thumbnail" id="preview" src="' + attachments[i].attributes.url + '" alt=""' +
                                    ' style="max-width:100%;"/><div class="c-featured-wrap text-center"><span' +
                                    ' class="c-featured-text">GALLERY</span></div> <div class="c-close-icon-wrap' +
                                    ' c-feature-image-text"><i class="feather-16 c-close-wrap"' +
                                    ' data-feather="x"></i></div></div>');
                                feather.replace();

                                selected[gallery_index] = attachments[i].attributes.id;
                                gallery_index++;
                            }
                        }
                        // Send the attachment id to our hidden input
                        var ids = selected.join(",");
                        $(".c-image-gallery").val(ids);
                    });

                    // Finally, open the modal on click
                    cProduct.el.frameGallery.open();
                });

                // Add included items
                cProduct.el.document.on("click", '.add-orders, .add-orders-mobile', function () {
                    var selfProduct = $('.c-products-cloneit').children();
                    var newel = selfProduct.clone();
                    $(newel).find(".included_items").attr("name", "included_items[]");
                    $(newel).insertAfter($(this).closest('.c-products-form'));
                });

                // Remove included items
                cProduct.el.document.on("click", '.remove-orders, .remove-orders-mobile', function () {
                    cProduct.el.totalProducts = $(this).closest('.c-products-clone').children().length;
                    if (cProduct.el.totalProducts > 1) {
                        $(this).closest('.c-products-form').remove();
                    }
                });

                // Remove included items
                cProduct.el.document.on("change", '#_overwrite_thank_you_redirect', function () {
                    if(this.checked){
                        $('.c-overwrite_thank_you_redirect').show();
                    }else{
                        $('.c-overwrite_thank_you_redirect').hide();
                    }
                });

                $('.comm-table-search').on('keyup', function () {
                    cForm.el.dataTables.search($(this).val()).draw() ;
                })

            });
        }
    };
    cProduct.run();
})(jQuery);