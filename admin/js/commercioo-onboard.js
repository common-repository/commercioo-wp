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
    window.cOnBoarding = {
        el: {
            window: $(window),
            document: $(document),
            comm_items: {},
            comm_items_main: {},
            comm_items_main_plugin: {},
            comm_items_cart: {},
            comm_install_progress: {},
            comm_install_processed: {},
            comm_install_total: {},
            comm_install_current: {},
            comm_install_ids: {},
            comm_activate_current: {},
            comm_activate_ids: {},
            sendData: '',
            install_ids: [],
            percentComplete: 0,
            timer_progress: null,
            begin_install: true,
            begin_activate: true,

        },
        fn: {
            comm_check_email: function () {
                $.ajax({
                    url: ajaxurl,
                    type: 'post',
                    data: {
                        action: 'comm_onboard_check_account',
                        comm_action: comm_onboard.nonce_set_email,
                    },
                    dataType: 'json',
                    success: function (res) {
                        if (res.status) {
                            $('.onboard-button[data-bs-target=license]').prop('disabled', false);
                        } else {
                            $('.onboard-button[data-bs-target=license]').prop('disabled', true);
                        }
                    }
                });
                // var email = $('#input-email').val();
                // if (email) {
                //     $('.onboard-button[data-bs-target=license]').prop('disabled', false);
                // } else {
                //     $('.onboard-button[data-bs-target=license]').prop('disabled', true);
                // }
            },
            makeRequest: function (method, url, data) {
                return new Promise(function (resolve, reject) {
                    var xhr = new XMLHttpRequest();
                    xhr.open(method, url, true);
                    xhr.onload = function () {
                        if (this.status >= 200 && this.status < 300) {
                            resolve(xhr.response);
                        } else {
                            reject({
                                status: this.status,
                                statusText: xhr.statusText
                            });
                        }
                    };
                    xhr.onerror = function () {
                        reject({
                            status: this.status,
                            statusText: xhr.statusText
                        });
                    };
                    if (method == "POST" && data) {
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                        xhr.send(data);
                    } else {
                        xhr.send();
                    }
                });
            },
            comm_set_step: function (step) {
                var steps = $('.commercioo-onboard-header .steps');
                if ('email' === step) {
                    steps.find('li').removeClass('active');
                    steps.find('li').removeClass('done');
                    steps.find('.step-email').addClass('active');
                } else if ('license' === step) {
                    steps.find('li').removeClass('active');
                    steps.find('li').removeClass('done');
                    steps.find('.step-email').addClass('done');
                    steps.find('.step-license').addClass('active');
                } else if ('install' === step) {
                    steps.find('li').removeClass('active');
                    steps.find('li').removeClass('done');
                    steps.find('.step-email').addClass('done');
                    steps.find('.step-license').addClass('done');
                    steps.find('.step-install').addClass('active');
                } else if ('activate' === step) {
                    steps.find('li').removeClass('active');
                    steps.find('li').removeClass('done');
                    steps.find('.step-email').addClass('done');
                    steps.find('.step-license').addClass('done');
                    steps.find('.step-install').addClass('active');
                }

                $('.onboard-content').each(function () {
                    $(this).removeClass('show');
                });
                $('#step-' + step).addClass('show');
            },
            comm_update_progress: function () {
                var percentage = Math.round((cOnBoarding.el.comm_install_processed / cOnBoarding.el.comm_install_total) * 100);
                $('.onboard-install-progress .percentage').html(percentage + '%');
                $('.onboard-install-progress .bar div').css('width', percentage + '%');

                if (100 == percentage) {
                    $('#step-install .onboard-button').prop('disabled', false);
                    $('#step-install .onboard-content-container > h2').html('Installation finished');
                    $('#step-install .onboard-content-container > p').html('Congratulations! Installation has just finished.');
                }
            },
            comm_set_install_status: function (item_id, status) {
                if ('installing' === status) {
                    $('#installing-' + item_id + ' .status').html('Installing');
                    $('#installing-' + item_id).addClass('installing');
                } else if ('installed' === status) {
                    $('#installing-' + item_id + ' .status').html('Installed');
                    $('#installing-' + item_id).removeClass('installing');
                } else if ('completed' === status) {
                    $('#installing-' + item_id + ' .status').html('Complete');
                    $('#installing-' + item_id).removeClass('installing');
                } else if ('failed' === status) {
                    $('#installing-' + item_id + ' .status').html('Failed');
                    $('#installing-' + item_id).removeClass('installing');
                }
            },
            comm_install_plugins: function (item_id, file_num, error) {
                cOnBoarding.fn.comm_set_install_status(item_id, 'installing');
                $.ajax({
                    url: ajaxurl,
                    type: 'post',
                    data: {
                        action: 'comm_onboard_install',
                        ids_plugin: item_id,
                        // type: comm_items[item_id].category,
                        // name: comm_items[item_id].files[file_num].name,
                        // file: comm_items[item_id].files[file_num].file,
                        // ids: item_id
                    },
                    dataType: 'json',
                    xhr: function () {
                        var xhr = new window.XMLHttpRequest();
                        //Upload progress
                        xhr.upload.addEventListener("progress", function (evt) {
                            if (evt.lengthComputable) {
                                cOnBoarding.el.timer_progress = setInterval(function () {
                                    cOnBoarding.el.percentComplete++;
                                    cOnBoarding.fn.progress_bar_process(item_id, cOnBoarding.el.percentComplete, cOnBoarding.el.timer_progress);
                                }, 100);
                                // cOnBoarding.el.percentComplete = evt.loaded / evt.total;
                                //Do something with upload progress
                            }
                        }, false);
                        //Download progress
                        xhr.addEventListener("progress", function (evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = evt.loaded / evt.total;
                                //Do something with download progress
                                $('#installing-' + item_id + ' .status').html(Math.round(percentComplete * 100) + "%");
                                cOnBoarding.fn.comm_set_install_status(item_id, 'installed');
                            }
                        }, false);
                        return xhr;
                    },
                    beforeSend: function () {
                        $('#installing-' + item_id + ' .status').html("0%");
                    },
                    success: function (res) {
                        clearInterval(cOnBoarding.el.timer_progress);
                        if (!res.status) {
                            if (item_id == res.item_id) {
                                cOnBoarding.fn.comm_set_install_status(res.item_id, 'failed');
                                cOnBoarding.el.begin_install = false;
                            }
                        } else {
                            if (item_id == res.item_id) {
                                cOnBoarding.fn.comm_set_install_status(res.item_id, 'completed');
                                cOnBoarding.el.comm_items[res.item_id].installed = true;
                            }
                        }
                        cOnBoarding.el.percentComplete = 0;
                        cOnBoarding.el.timer_progress = 0;
                        cOnBoarding.el.comm_install_current++;
                        if (cOnBoarding.el.comm_install_current == cOnBoarding.el.install_ids.length) {
                            cOnBoarding.el.begin_install = false;
                            cOnBoarding.el.comm_install_processed++;
                            cOnBoarding.fn.comm_update_progress();
                            $('.onboard-button').prop('disabled', false);
                        } else {
                            if (cOnBoarding.el.begin_install) {
                                if (cOnBoarding.el.comm_install_current <= cOnBoarding.el.install_ids.length) {
                                    cOnBoarding.el.comm_install_processed++;
                                    cOnBoarding.fn.comm_update_progress();
                                    item_id = cOnBoarding.el.install_ids[cOnBoarding.el.comm_install_current];
                                    cOnBoarding.fn.comm_install_plugins(item_id, cOnBoarding.el.comm_install_current, error);
                                }
                            }
                        }


                    },
                    error: function (err) {
                        console.log(err);
                        error.append('<p>Can not install ' + cOnBoarding.el.comm_items[item_id].name + '</p>');
                        error.addClass('show');
                        setTimeout(function () {
                            error.removeClass('show');
                            error.html('');
                        }, 5000);
                    }
                });
            },
            progress_bar_process: function (item_id, percentage, timer) {
                if (percentage > 100) {
                    clearInterval(cOnBoarding.el.timer_progress);
                    $('#installing-' + item_id + ' .status').html("100%");

                } else {
                    var progress = Math.round(percentage * 100) / 100;
                    $('#installing-' + item_id + ' .status').html(progress + "%");
                }
            },
            comm_set_activate_status(item_id, status) {
                if ('activating' === status) {
                    $('#activating-' + item_id).addClass('activating');
                } else if ('activated' === status) {
                    $('#activating-' + item_id).removeClass('activating');
                    $('#activating-' + item_id).addClass('activated');
                } else if ('failed' === status) {
                    $('#activating-' + item_id).removeClass('activating');
                    $('#activating-' + item_id).removeClass('activated');
                    $('#activating-' + item_id).addClass('failed');
                }
            },
            comm_activate_plugin(item_id, file_num, type = 'second') {
                cOnBoarding.fn.comm_set_activate_status(item_id, 'activating');
                var error = $('#step-activate .error-container');

                var dta = {
                    item_id: item_id,
                    license: (type == "main") ? cOnBoarding.el.comm_items_main.license : cOnBoarding.el.comm_items[item_id].license,
                    url: comm_onboard.site_url,
                    store_url: comm_onboard.store_url,
                    // type: comm_items[item_id].category,
                    // name: comm_items[item_id].files[file_num].name,
                    // file: comm_items[item_id].files[file_num].file,
                    // ids: item_id
                };
                $.ajax({
                    url: comm_onboard.store_url + '/wp-json/comm-license/activate-license',
                    type: 'POST',
                    data: dta,
                    dataType: 'json',
                    success: function (res) {
                        if (res.code) {
                            cOnBoarding.fn.comm_set_activate_status(item_id, 'failed');
                            $('#step-activate .onboard-button').removeClass('disabled');
                            error.append('<p>Error!! Can not activate license for product ' + cOnBoarding.el.comm_items[item_id].name + '</p>');
                            error.addClass('show');
                            setTimeout(function () {
                                error.removeClass('show');
                                error.html('');
                            }, 5000);
                        } else {
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'comm_onboard_activate',
                                    item_id: item_id,
                                    types: (type == "main") ? 'main' : 'second',
                                    response: res
                                },
                                dataType: 'json',
                                success: function (res) {
                                    cOnBoarding.el.comm_activate_current++;
                                    cOnBoarding.fn.comm_set_activate_status(item_id, 'activated');
                                    if (cOnBoarding.el.comm_activate_current == cOnBoarding.el.comm_activate_ids.length) {
                                        cOnBoarding.el.begin_activate = false;
                                    } else {
                                        if (cOnBoarding.el.begin_activate) {
                                            if (cOnBoarding.el.comm_activate_current <= cOnBoarding.el.install_ids.length) {
                                                item_id = cOnBoarding.el.install_ids[cOnBoarding.el.comm_activate_current];
                                                cOnBoarding.fn.comm_activate_plugin(item_id, cOnBoarding.el.comm_install_current);
                                            }
                                        }
                                    }

                                    if (type == "main") {
                                        cOnBoarding.el.begin_activate = false;
                                        $('#step-activate .onboard-content-container > h2').html('Activation Finished');
                                        $('#step-activate .onboard-content-container > p').html('Congratulations! Activation has just finished. Now you can start using the products.');
                                        $('#step-activate .onboard-button').removeClass('disabled');
                                    }
                                    if (!cOnBoarding.el.begin_activate && type != "main") {
                                        item_id = cOnBoarding.el.comm_items_main.id;
                                        if (item_id) {
                                            cOnBoarding.fn.comm_activate_plugin(item_id, 0, "main");
                                        } else {
                                            $('#step-activate .onboard-content-container > h2').html('Activation Finished');
                                            $('#step-activate .onboard-content-container > p').html('Congratulations! Activation has just finished. Now you can start using the products.');
                                            $('#step-activate .onboard-button').removeClass('disabled');
                                        }
                                    }
                                }, error: function (err) {
                                    console.log(err);
                                    cOnBoarding.fn.comm_set_activate_status(item_id, 'failed');
                                    $('#step-activate .onboard-button').removeClass('disabled');
                                    error.append('<p>Can not activate ' + cOnBoarding.el.comm_items[item_id].name + '</p>');
                                    error.addClass('show');
                                    setTimeout(function () {
                                        error.removeClass('show');
                                        error.html('');
                                    }, 5000);
                                }
                            });
                        }
                    }, error: function (err) {
                        console.log(err.responseJSON.message);
                        cOnBoarding.fn.comm_set_activate_status(item_id, 'failed');
                        $('#step-activate .onboard-button').removeClass('disabled');
                        error.append('<p>Error!! Can not activate license for product ' + cOnBoarding.el.comm_items[item_id].name + '</p>');
                        error.addClass('show');
                        setTimeout(function () {
                            error.removeClass('show');
                            error.html('');
                        }, 5000);
                    }
                });

            }
        },
        run: function () {
            //WINDOW LOAD
            cOnBoarding.el.window.on("load", function () {
                // cOnBoarding.fn.comm_check_email();
            });

            cOnBoarding.el.document.ready(function () {
                $('.switch-account').on('click', function () {
                    $('.email-container').addClass('hide');
                    $('.email-container.form').removeClass('hide');
                    $('#input-password').val('');
                });
                // $('#input-email').on('change keyup', function () {
                //     cOnBoarding.fn.comm_check_email();
                // });
                $('.onboard-button[data-bs-target=activate]').on('click', function () {
                    var block = $(this).parents('.onboard-inner').find('.block');
                    var error = $(this).parents('.onboard-content').find('.error-container');

                    cOnBoarding.el.comm_activate_ids = [];
                    cOnBoarding.el.comm_activate_current = 0;
                    cOnBoarding.el.comm_install_current = 0;

                    var html = '';
                    for (var i in cOnBoarding.el.install_ids) {
                        if (cOnBoarding.el.comm_items[cOnBoarding.el.install_ids[i]].installed) {
                            html += '<li id="activating-' + cOnBoarding.el.install_ids[i] + '"><span class="icon"></span> ' + cOnBoarding.el.comm_items[cOnBoarding.el.install_ids[i]].name + '</li>';
                            cOnBoarding.el.comm_activate_ids.push(cOnBoarding.el.install_ids[i]);
                        }
                    }

                    if (cOnBoarding.el.install_ids.length) {
                        $('#step-activate .onboard-activate').html(html);
                        $('#step-activate .onboard-content-container > h2').html('Activating Product');
                        $('#step-activate .onboard-content-container > p').html('Wait until the activation is complete.');
                        $('#step-activate .onboard-button').addClass('disabled');

                        var cur_item_ids = cOnBoarding.el.install_ids[cOnBoarding.el.comm_activate_current];
                        cOnBoarding.fn.comm_activate_plugin(cur_item_ids, cOnBoarding.el.comm_activate_current);
                    } else {
                        $('#step-activate .onboard-activate').html('');
                        $('#step-activate .onboard-content-container > h2').html('Nothing to activate');
                        $('#step-activate .onboard-content-container > p').html('There is no product to activate, you can skip this ste and got to dashboard.');
                        $('#step-activate .onboard-button').removeClass('disabled');
                    }
                    cOnBoarding.fn.comm_set_step('activate');
                });
                $('.onboard-button[data-bs-target=license]').on('click', function () {
                    var block = $(this).parents('.onboard-inner').find('.block');
                    var error = $(this).parents('.onboard-content').find('.error-container');
                    var password = '';
                    var method = '';
                    block.addClass('show');

                    var email = $('#input-email').val();

                    if ($('.email-container.filled').hasClass('hide')) {
                        password = $('#input-password').val();
                        method = 'password';
                    } else {
                        password = $('#input-password').val();
                        method = 'password';
                    }

                    if (password === "") {
                        password = $('#comm-onboarding-account-pass').val();
                    }
                    $.ajax({
                        url: comm_onboard.store_url + '/wp-json/comm-items/items',
                        type: 'post',
                        data: {
                            method: method,
                            url: comm_onboard.site_url,
                            email: email,
                            pass: password,
                            is_new_commercioo: true
                        },
                        dataType: 'json',
                        success: function (results) {
                            if (!results.status) {
                                error.append('<p>' + results.message + '</p>');
                                error.addClass('show');
                                setTimeout(function () {
                                    error.removeClass('show');
                                    error.html('');
                                }, 5000);
                                block.removeClass('show');
                            } else {
                                cOnBoarding.el.sendData = results;
                                cOnBoarding.el.comm_items_cart = results.data;
                                $.ajax({
                                    url: ajaxurl,
                                    type: 'post',
                                    data: {
                                        action: 'comm_onboard_email',
                                        comm_action: comm_onboard.nonce_set_email,
                                        email: email,
                                        password: password,
                                        sendData: cOnBoarding.el.sendData,
                                        sendData_plugin: results
                                    },
                                    dataType: 'json',
                                    success: function (res) {
                                        // console.log(res);
                                        if (!res.status) {
                                            error.append('<p>' + res.message + '</p>');
                                            error.addClass('show');
                                            setTimeout(function () {
                                                error.removeClass('show');
                                                error.html('');
                                            }, 5000);
                                        } else {
                                            $('.onboard-content .onboard-inner-header img').attr('src', res.avatar);
                                            $('.onboard-content .onboard-inner-header h4').html(res.email);
                                            var msg = "This addon cannot be install because your license key for this purchase is expired. Please contact us for <a href='https://commercioo.com/go/telegram' target='_blank'>Renew</a> License Key";
                                            if (res.data) {
                                                cOnBoarding.el.comm_items = res.data;
                                                cOnBoarding.el.comm_items_main = res.data_main;
                                                cOnBoarding.el.comm_items_main_plugin = res.data_main_plugin;
                                                var html = '';
                                                for (var i in cOnBoarding.el.comm_items) {
                                                    if (res.is_new_commercioo) {
                                                        if (typeof res.data[i].success === "undefined") {
                                                            if(res.data[i].license!==""){
                                                                html += '<li><div class="onboard-plugin-header"><input type="checkbox" checked class="comm_install" name="comm_install" value="' + i + '" id="item-' + i + '"><label class="comm_install" for="item-' + i + '">' + res.data[i].name + '</label></div><div class="onboard-plugin-content"><input type="text" name="license[' + i + ']" value="' + res.data[i].license + '"></div></li>';
                                                            }else{
                                                                html += '<li><div class="onboard-plugin-header"><label class="comm_not_install" for="item-' + i + '">' + res.data[i].name + '</label><div class="alert alert-danger" role="alert">' + msg + '</div></div></li>';
                                                            }
                                                        } else {
                                                            if (res.data[i].success !== "false") {
                                                                html += '<li><div class="onboard-plugin-header"><input type="checkbox" checked class="comm_install" name="comm_install" value="' + i + '" id="item-' + i + '"><label class="comm_install" for="item-' + i + '">' + res.data[i].name + '</label></div><div class="onboard-plugin-content"><input type="text" name="license[' + i + ']" value="' + res.data[i].license + '"></div></li>';
                                                            } else {
                                                                if (typeof res.data[i].message !== "undefined") {
                                                                    msg = res.data[i].message.replace(/\\/g, '');
                                                                }
                                                                html += '<li><div class="onboard-plugin-header"><label class="comm_not_install" for="item-' + i + '">' + res.data[i].name + '</label><div class="alert alert-danger" role="alert">' + msg + '</div></div></li>';
                                                            }
                                                        }
                                                    } else {
                                                        if (typeof res.data[i].success === "undefined") {
                                                            if(res.data[i].license!==""){
                                                                html += '<li><div class="onboard-plugin-header"><input type="checkbox" checked class="comm_install" name="comm_install" value="' + i + '" id="item-' + i + '"><label class="comm_install" for="item-' + i + '">' + res.data[i].name + '</label></div><div class="onboard-plugin-content"><input type="text" name="license[' + i + ']" value="' + res.data[i].license + '"></div></li>';
                                                            }else{
                                                                html += '<li><div class="onboard-plugin-header"><label class="comm_not_install" for="item-' + i + '">' + res.data[i].name + '</label><div class="alert alert-danger" role="alert">' + msg + '</div></div></li>';
                                                            }
                                                        } else {
                                                            if (res.data[i].success !== "false") {
                                                                html += '<li><div class="onboard-plugin-header"><input type="checkbox" checked class="comm_install" name="comm_install" value="' + i + '" id="item-' + i + '"><label class="comm_install" for="item-' + i + '">' + res.data[i].name + '</label></div><div class="onboard-plugin-content"><input type="text" name="license[' + i + ']" value="' + res.data[i].license + '"></div></li>';
                                                            } else {
                                                                if (typeof res.data[i].message !== "undefined") {
                                                                    msg = res.data[i].message.replace(/\\/g, '');
                                                                }
                                                                html += '<li><div class="onboard-plugin-header"><label class="comm_not_install" for="item-' + i + '">' + res.data[i].name + '</label><div class="alert alert-danger" role="alert">' + msg + '</div></div></li>';
                                                            }
                                                        }
                                                        // html += '<li><div class="onboard-plugin-header"><input type="checkbox" checked name="comm_install" value="' + i + '" id="item-' + i + '"><label for="item-' + i + '">' + res.data[i].name + '</label></div><div class="onboard-plugin-content"><input type="text" name="license[' + i + ']" value="' + res.data[i].license + '"></div></li>';
                                                    }

                                                }
                                                $('#step-license .onboard-plugins').html(html);
                                                cOnBoarding.fn.comm_set_step('license');
                                            } else {
                                                $('#step-activate .onboard-activate').html('');
                                                $('#step-activate .onboard-content-container > h2').html('Nothing to install');
                                                $('#step-activate .onboard-content-container > p').html('There is no product to install, you can skip this step and go to dashboard.');
                                                $('#step-activate .onboard-button').removeClass('disabled');
                                                cOnBoarding.fn.comm_set_step('activate');
                                            }
                                            // console.log(res);
                                        }
                                        block.removeClass('show');
                                    },
                                    error: function (err) {
                                        console.log(err);
                                        error.append('<p>Can not set email</p>');
                                        error.addClass('show');
                                        setTimeout(function () {
                                            error.removeClass('show');
                                            error.html('');
                                        }, 5000);
                                        block.removeClass('show');
                                    }
                                });
                            }
                        },
                        error: function (err) {
                            console.log(err);
                            error.append('<p>Can not set email</p>');
                            error.addClass('show');
                            setTimeout(function () {
                                error.removeClass('show');
                                error.html('');
                            }, 5000);
                            block.removeClass('show');
                        }
                    });
                });
                $('.onboard-button[data-bs-target=install]').on('click', function () {
                    var block = $(this).parents('.onboard-inner').find('.block');
                    var error = $(this).parents('.onboard-content').find('.error-container');
                    block.addClass('show');
                    cOnBoarding.el.comm_install_progress = 0;
                    cOnBoarding.el.comm_install_processed = 0;
                    cOnBoarding.el.comm_install_total = 0;
                    cOnBoarding.el.comm_install_current = 0;

                    $('.onboard-plugins [name=comm_install]:checked').each(function () {
                        cOnBoarding.el.install_ids.push($(this).val());
                    });

                    if (0 === cOnBoarding.el.install_ids.length) {
                        block.removeClass('show');
                        error.append('<p>Please select at least 1 plugin/theme to install</p>');
                        error.addClass('show');
                        setTimeout(function () {
                            error.removeClass('show');
                            error.html('');
                        }, 5000);
                        return;
                    }
                    var licenses = {};
                    for (var i = 0; i < cOnBoarding.el.install_ids.length; i++) {
                        var license = $('input[name="license[' + cOnBoarding.el.install_ids[i] + ']"]').val();
                        if ('' === license) {
                            block.removeClass('show');
                            error.append('<p>License key on ' + $('label[for=item-' + cOnBoarding.el.install_ids[i] + ']').text() + ' can not empty.</p>');
                            error.addClass('show');
                            setTimeout(function () {
                                error.removeClass('show');
                                error.html('');
                            }, 5000);
                            return;
                        }
                        licenses[cOnBoarding.el.install_ids[i]] = license;
                        cOnBoarding.el.comm_items[cOnBoarding.el.install_ids[i]].license = license;
                        cOnBoarding.el.comm_items[cOnBoarding.el.install_ids[i]].installed = false;
                        cOnBoarding.el.comm_install_total += cOnBoarding.el.comm_items[cOnBoarding.el.install_ids[i]].files.length;
                        cOnBoarding.fn.comm_update_progress();
                    }
                    var html = '';
                    $.each(cOnBoarding.el.install_ids, function (i, v) {
                        html += '<li id="installing-' + v + '">Installing ' + cOnBoarding.el.comm_items[v].name + ' (<span' +
                            ' class="status">Queued</span>)</li>';
                    });
                    $('#step-install .onboard-install').append(html);
                    $('.onboard-button').prop('disabled', true);
                    cOnBoarding.fn.comm_set_step('install');

                    var cur_item_ids = cOnBoarding.el.install_ids[cOnBoarding.el.comm_install_current];
                    $('#step-install .onboard-content-container > h2').html('Begin installation');
                    $('#step-install .onboard-content-container > p').html('You are almost done! Wait until the installation is complete.');
                    cOnBoarding.fn.comm_install_plugins(cur_item_ids, cOnBoarding.el.comm_install_current, error);

                    // for (var i = 0; i < cOnBoarding.el.install_ids.length; i++) {
                    //     var cur_item_ids = cOnBoarding.el.install_ids[i];
                    //     $('#step-install .onboard-content-container > h2').html('Begin installation');
                    //     $('#step-install .onboard-content-container > p').html('You are almost done! Wait until the installation is complete.');
                    //     cOnBoarding.el.comm_install_current=i;
                    //     cOnBoarding.fn.comm_install_plugins(cur_item_ids, cOnBoarding.el.comm_install_current);
                    // }
                });
            });
        }
    };
    cOnBoarding.run();
})(jQuery);