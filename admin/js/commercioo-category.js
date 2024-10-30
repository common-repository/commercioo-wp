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
    window.cCategory = {
        el: {
            window: $(window),
            document: $(document),

        },
        fn: {
            post: function (url, dataStore, forms = '') {
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
                    cForm.el.selectorID = '';
                    var msg_store = commlang.generic.saveData;
                    if (cForm.el.dataFormEvent == "update") {
                        msg_store = commlang.generic.updateData;
                    }
                    cCategory.fn.refreshParent();
                    cForm.fn.loading("hide");
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

                    forms.removeAttr("novalidate");
                    forms.removeClass("was-validated");
                    forms.find(".c-save-category").removeAttr("disabled");
                    $('select[name="parent"]').find("option").removeAttr("style");
                    cCategory.fn.refreshField("show");
                }).fail(function(jqXHR, textStatus, error){
                    if(typeof jqXHR.responseJSON.message!=="undefined"){
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
                                title: (jqXHR.responseJSON.code == "category_term_exists") ? commlang.category.errorExist : jqXHR.responseJSON.message
                            });

                            // forms.removeAttr("novalidate");
                            // forms.removeClass("was-validated");
                            forms.find(".c-save-category").removeAttr("disabled");
                            cCategory.fn.refreshField("hide");
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
                    cForm.el.dataFormEvent = "update";
                    $("form")[0].reset();
                    $("form.needs-validation").attr('novalidate', 'novalidate');

                    $('span#id_category').text(response.id);
                    $.each(response, function (index, value) {
                        $('input[name="' + index + '"]').val(value);
                        if (index == "id") {
                            $('select[name="parent"]').find("option[value='" + value + "']").hide();
                        }
                        $('select[name="' + index + '"]').val(value);
                        $('textarea[name="' + index + '"]').val(value);
                    });
                    cCategory.fn.refreshField("edit");
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
                        force:true
                    },
                }).done(function (response) {
                    cForm.el.selectorID = '';
                    target.remove();
                    cCategory.fn.refreshParent();
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
                });
            },
            validationForm: function () {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function (form) {
                    form.addEventListener('submit', function (event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                            $('html, body').animate({
                                scrollTop: $('body').offset().top
                            }, 200);
                        } else {
                            event.preventDefault();
                            event.stopPropagation();
                            cForm.fn.loading("show");
                            $(form).find(".c-save-category").attr("disabled", "disabled");
                            cCategory.fn.post(cApiSettings.root + 'wp/v2/comm_product_cat/' + cForm.el.selectorID, $(form).serializeArray(), $(form));
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            },
            refreshParent: function(){
                $.ajax({
                    url: cApiSettings.root + 'commercioo/v1/comm_getParent/',
                    method: "GET",
                    dataType:'html',
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', cApiSettings.nonce);
                    },
                }).done(function (response) {
                    $('select[name="parent"]').html($(response).html());
                    $('select[name="parent"]').find("option[value='-1']").val('0');
                });
            },
            refreshField:function(status){
                if(status=="show"){
                    $('.c-add-category').hide();
                    $('.list-category').show();
                    $('.c-add-category.category-title').hide();
                    $('.c-list-category.category-title').show();
                    $('.c-edit-category.category-title').hide();
                }else if(status=="edit"){
                    $('.c-add-category').show();
                    $('.list-category').hide();
                    $('.c-add-category.category-title').hide();
                    $('.c-list-category.category-title').hide();
                    $('.c-edit-category.category-title').show();
                }else{
                    $('.c-add-category').show();
                    $('.list-category').hide();
                    $('.c-add-category.category-title').show();
                    $('.c-list-category.category-title').hide();
                    $('.c-edit-category.category-title').hide();
                }
            },
            action: function (url, dataStore) {
                $.ajax({
                    url: url,
                    method: "PATCH",
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', cApiSettings.nonce);
                        cForm.fn.loading("show");
                    },
                    data: dataStore,
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
                            title: commlang.generic.dataSaved
                        });
                        $('input[name=select-all]').prop('checked', false);
                    } else {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });

                        Toast.fire({
                            type: 'success',
                            title: commlang.autoresponder.errorTrash
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
            }
        },
        run: function () {
            //WINDOW LOAD
            cCategory.el.window.on("load", function () {
            });
            //DOCUMENT READY
            cCategory.el.document.ready(function () {
                cCategory.fn.refreshField("show");
                cCategory.fn.refreshParent();
                // GENERATE TABLE
                cForm.fn.generateTable(commlang.category.searchPlaceholderTable);

                cCategory.fn.validationForm();
                $('.c-btn-add-category').on('click', function () {
                    cForm.el.selectorID = '';
                    $(".c-form-title span").html(commlang.category.addTitle);
                    cCategory.fn.refreshField("hide");
                    cForm.el.dataFormEvent = "add";
                    $("form")[0].reset();
                    $("form.needs-validation").attr('novalidate', 'novalidate');

                });

                $('.c-back').on('click', function () {
                    cCategory.fn.refreshField("show");
                    $("form.needs-validation").removeClass("was-validated");
                    $('select[name="parent"]').find("option").removeAttr("style");
                });
                cCategory.el.document.on('click', '.c-edit', function (e) {
                    e.preventDefault();
                    $(".c-form-title span").html(commlang.category.editTitle);
                    cForm.el.selectorID = $(this).data("id");
                    cCategory.fn.get(cApiSettings.root + 'wp/v2/comm_product_cat/' + cForm.el.selectorID, '', '');
                });
                cCategory.el.document.on('click', '.c-delete', function (e) {
                    e.preventDefault();
                    cForm.el.selectorID = $(this).data("id");
                    var self = $(this),
                        target = self.closest('tr'),
                        title = target.find('td:eq(1)').text();

                    Swal.fire({
                        title: commlang.generic.caution,
                        text: commlang.generic.deleteConfirm + " "+title,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: commlang.generic.yes
                    }).then((result) => {
                        if (result.value) {
                            cCategory.fn.delete(cApiSettings.root + 'wp/v2/comm_product_cat/' + cForm.el.selectorID, target);
                        }
                    });
                });

                $('.comm-table-search').on('keyup', function () {
                    cForm.el.dataTables.search($(this).val()).draw() ;
                })

                // when header checkbox checked
                $('input[name=select-all]').click(function(event) {   
                    if(this.checked) {
                        // Iterate each checkbox
                        $(':checkbox').each(function() {
                            this.checked = true;                        
                        });
                    } else {
                        $(':checkbox').each(function() {
                            this.checked = false;                       
                        });
                    }
                });

                $('.c-option-edit').on('click', function (e) {
                    e.preventDefault();
                    var id = [], 
                        type   = $(this).data('type'),
                        action = $(this).data('action'),
                        parent_chekbox = $(this).closest(".table-option").find("input[type='checkbox']").attr("name");
                    // get checked data
                    if (type == 'bulk') {
                        $("input:checkbox[name=category_id]:checked").each(function(idx, val){
                            id.push($(val).val());
                        });
                        if(id.length==0 && parent_chekbox=="select-all") {
                            Swal.fire({
                                icon: 'error',
                                title: commlang.generic.caution,
                                text: commlang.category.noCountAction,
                            })
                            return false;
                        }
                    } else {
                        id = $(this).data('id');
                    }

                    Swal.fire({
                        title: commlang.generic.caution,
                        text: commlang.category[action + 'Confirm'],
                        type: 'info',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: commlang.generic.yes,
                        cancelButtonText: commlang.generic.no
                    }).then((result) => {
                        if (result.value) {
                            cCategory.fn.action(cApiSettings.root + 'commercioo/v1/comm_product_term_action', {
                                id: id,
                                tbl: "comm_product_cat",
                                action: action,
                                type: type,
                                force: true
                            });
                        }
                    });
                })
            });
        }
    };
    cCategory.run();
})(jQuery);