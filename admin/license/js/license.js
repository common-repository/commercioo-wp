(function ($) {
    'use strict'
    window.app = {el: {}, fn: {}};
    app.el['window'] = $(window);
    app.el['document'] = $(document);
    app.el['html-body'] = $('html,body');

    app.el['document'].ready(function () {
        auto_check_license();
        function auto_check_license() {
            $("input.check_licenses:not(disable)").each(function (){
                // if($(this).closest("li").find('.ctp_keys').val().length==0){
                //     return false;
                // }
                var $this = $(this);
                $this.prop('disabled', true);
                $(".loading-img").remove();
                var img = '<img class="loading-img">', img_loading = $this.closest("li").find(".license-loading");
                img_loading.append($(img));
                var ctp_dt_form = {
                    item_id: $(this).closest("li").data("id"),
                    license: $(this).closest("li").find('.ctp_keys').val(),
                    url: commercioo_license_page_ajax_obj.site_url,
                };
                var plugin_slug = $this.closest("li").find(".commercioo_plugin_slug").val();
                var item_name = $this.closest("li").data("item-name");
                var license_info = $this.closest("li").find('.license-page-plugin-desc-license');
                var license_key = $this.closest("li").find('.ctp_keys').val();
                var html = '';
                $.ajax({
                    url: commercioo_license_page_ajax_obj.store_url + '/wp-json/comm-license/checking-license',
                    type: "POST",
                    data: ctp_dt_form,
                    dataType: "json",
                    cache: false,
                    success: function (data) {
                        var ctp_data_form = {
                            action: 'license_page_check_license',
                            data: data,
                            data_slug:plugin_slug,
                            license_key: license_key,
                            nonce_license_edd: commercioo_license_page_ajax_obj.nonce_license,
                        };
                        $.ajax({
                            url: ajaxurl,
                            type: "POST",
                            data: ctp_data_form,
                            dataType: "json",
                            cache: false,
                            success: function (data) {
                                if(data.is_valid){
                                    $this.prop('disabled', false);
                                    html = '<div class="comm-msg-license">Success' +
                                        ' checking license key Product: ' + item_name + '</div>';
                                    $(html).appendTo(".comm-msg-floating").delay(2000).queue(function() {
                                        license_info.html(data.msg);
                                        $(this).fadeOut().remove();
                                        img_loading.find(".loading-img").remove();
                                    });
                                }else{
                                    $this.prop('disabled', false);
                                    html = '<div class="comm-msg-license">'+ data.msg+
                                        ' - Product: ' + item_name + '</div>';
                                    $(html).appendTo(".comm-msg-floating").delay(2000).queue(function() {
                                        license_info.html(data.msg);
                                        $(this).fadeOut().remove();
                                        img_loading.find(".loading-img").remove();
                                    });
                                }
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                                var html = '<div class="comm-msg-license">internal server invalid</div>';
                                $(html).appendTo(".comm-msg-floating").delay(2000).queue(function() {
                                    $(this).fadeOut().remove();
                                });
                                img_loading.find(".loading-img").remove();
                                $this.prop('disabled', false);
                                $("#act_license").each(function () {
                                    $(this).find(".act_licenses").prop('disabled', false);
                                    $(this).find(".ctp_keys").prop('disabled', false);
                                });
                            }
                        });
                    }, error: function (jqXHR, textStatus, errorThrown) {
                        var html = '<div class="comm-msg-license">Invalid checking license</div>';
                        $(html).appendTo(".comm-msg-floating").delay(2000).queue(function() {
                            $(this).fadeOut().remove();
                        });
                        img_loading.find(".loading-img").remove();
                        $this.prop('disabled', false);
                    }
                });
            });
        }

        $("input.check_licenses:not(disable)").click(function () {
            if($(this).closest("li").find('.ctp_keys').val().length==0){
                $(this).closest("li").find('.ctp_keys').focus();
                alert("License key required");
                return false;
            }
            var $this = $(this);
            $this.prop('disabled', true);
            $(".loading-img").remove();
            var img = '<img class="loading-img">', img_loading = $(this).closest("li").find(".license-loading");
            img_loading.append($(img));
            var html = '';
            var ctp_dt_form = {
                item_id: $(this).closest("li").data("id"),
                license: $(this).closest("li").find('.ctp_keys').val(),
                url: commercioo_license_page_ajax_obj.site_url,
            };
            var plugin_slug = $(this).closest("li").find(".commercioo_plugin_slug").val();
            var item_name = $(this).closest("li").data("item-name");
            var license_info = $(this).closest("li").find('.license-page-plugin-desc-license');
            var license_key = $this.closest("li").find('.ctp_keys').val();
            $.ajax({
                url: commercioo_license_page_ajax_obj.store_url + '/wp-json/comm-license/checking-license',
                type: "POST",
                data: ctp_dt_form,
                dataType: "json",
                cache: false,
                success: function (data) {
                    var ctp_data_form = {
                        action: 'license_page_check_license',
                        data: data,
                        data_slug:plugin_slug,
                        license_key: license_key,
                        nonce_license_edd: commercioo_license_page_ajax_obj.nonce_license,
                    };
                    $.ajax({
                        url: ajaxurl,
                        type: "POST",
                        data: ctp_data_form,
                        dataType: "json",
                        cache: false,
                        success: function (data) {
                            if(data.is_valid){
                                $this.prop('disabled', false);
                                html = '<div class="comm-msg-license">Success' +
                                    ' checking license key Product: ' + item_name + '</div>';
                                $(html).appendTo(".comm-msg-floating").delay(2000).queue(function() {
                                    license_info.html(data.msg);
                                    $(this).fadeOut().remove();
                                    img_loading.find(".loading-img").remove();
                                });
                            }else{
                                $this.prop('disabled', false);
                                html = '<div class="comm-msg-license">'+ data.msg+
                                    ' - Product: ' + item_name + '</div>';
                                $(html).appendTo(".comm-msg-floating").delay(2000).queue(function() {
                                    license_info.html(data.msg);
                                    $(this).fadeOut().remove();
                                    img_loading.find(".loading-img").remove();
                                });
                            }

                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            var html = '<div class="comm-msg-license">internal server invalid</div>';
                            $(html).appendTo(".comm-msg-floating").delay(2000).queue(function() {
                                $(this).fadeOut().remove();
                            });
                            img_loading.find(".loading-img").remove();
                            $this.prop('disabled', false);
                            $("#act_license").each(function () {
                                $(this).find(".act_licenses").prop('disabled', false);
                                $(this).find(".ctp_keys").prop('disabled', false);
                            });
                        }
                    });
                }, error: function (jqXHR, textStatus, errorThrown) {
                    var html = '<div class="comm-msg-license">Invalid checking license</div>';
                    $(html).appendTo(".comm-msg-floating").delay(2000).queue(function() {
                        $(this).fadeOut().remove();
                    });
                    img_loading.find(".loading-img").remove();
                    $this.prop('disabled', false);
                }
            });
        });

        $("input.act_check_requirement:not(disable)").click(function () {
            var $this = $(this);
            $this.prop('disabled', true);
            $(".loading-img").remove();
            var img = '<img class="loading-img">', img_loading = $(this).closest("li").find(".license-loading");
            img_loading.append($(img));
            $("#act_license").each(function () {
                $(this).find(".act_check_requirement").prop('disabled', true);
                $(this).find(".cond_requirement_error").addClass('check_requirement_error');
            });
            var ctp_data_form = {
                action: 'ctp_action_check_requirement'
            };
            $.ajax({
                url: commercioo_license_page_ajax_obj.ajaxurl,
                type: "POST",
                data: ctp_data_form,
                dataType: "html",
                cache: false,
                success: function (data) {
                    $this.prop('disabled', false);
                    if (data) {
                        $this.closest("li").find(".check_requirement_error").removeClass('check_requirement_error');
                        $this.closest("li").find(".requirement_content").html(data);
                        $this.closest("li").find(".cond_requirement_error").show();
                        $this.closest("li").find(".requirement_content").show();
                        setTimeout(function () {
                            $this.closest("li").find(".requirement_content").html('');
                            $this.closest("li").find(".cond_requirement_error").hide();
                            $this.closest("li").find(".requirement_content").hide();
                        }, 5000);
                    }

                    img_loading.find(".loading-img").remove();
                    $("#act_license").each(function () {
                        $(this).find(".act_check_requirement").prop('disabled', false);
                    });
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $this.prop('disabled', false);
                    alert("internal server invalid");
                    img_loading.find(".loading-img").remove();
                    $("#act_license").each(function () {
                        $(this).find(".act_check_requirement").prop('disabled', false);
                    });
                }
            });
        });
        $("input.act_licenses_activate:not(disable), input.act_licenses_deactivate:not(disable)").click(function () {
            if($(this).closest("li").find('.ctp_keys').val().length==0){
                $(this).closest("li").find('.ctp_keys').focus();
                alert("License key required");
                return false;
            }
            var $this = $(this);
            $this.prop('disabled', true);
            $this.find(".ctp_keys").prop('disabled', true);

            $(".loading-img").remove();
            var img = '<img class="loading-img">';
            var event_ctp = $(this).closest("li").find("input[name='act_name']").val(),
                img_loading = $(this).closest("li").find(".license-loading");
            img_loading.append($(img));
            // $("#act_license").each(function () {
            //     $(this).find(".act_licenses").prop('disabled', true);
            //     $(this).find(".ctp_keys").prop('disabled', true);
            // });
            var plugin_slug = $(this).closest("li").find(".commercioo_plugin_slug").val();
            var item_name = $(this).closest("li").data("item-name");
            var license_info = $(this).closest("li").find('.license-page-plugin-desc-license');
            var license_key = $(this).closest("li").find('.ctp_keys').val();
            var ctp_dt_form = {
                item_id: $(this).closest("li").data("id"),
                license: license_key,
                url: commercioo_license_page_ajax_obj.site_url,
            };
            var url_action = (event_ctp == 'deactivated')? 'deactivate-license':'activate-license';
            $.ajax({
                url: commercioo_license_page_ajax_obj.store_url + '/wp-json/comm-license/'+url_action,
                type: "POST",
                data: ctp_dt_form,
                dataType: "json",
                cache: false,
                success: function (data) {
                    var ctp_data_form = {
                        action: 'license_page_do_license',
                        ctp_event: event_ctp,
                        data: data,
                        data_slug:plugin_slug,
                        ctp_license_key:license_key
                    };
                    $.ajax({
                        url: ajaxurl,
                        type: "POST",
                        data: ctp_data_form,
                        dataType: "json",
                        cache: false,
                        success: function (data) {
                            if(data.is_valid) {
                                if (event_ctp == 'deactivated') {
                                    var html = '<div class="comm-msg-license">Successful deactivated license key' +
                                        ' Product: ' + item_name + '</div>';
                                    if ($(".comm-msg-floating").length > 0) {
                                        $(html).appendTo(".comm-msg-floating").delay(2000).queue(function () {
                                            license_info.html(data.data_view_license);
                                            $(this).fadeOut().remove();
                                            $this.prop('disabled', false);
                                            $this.find(".ctp_keys").prop('disabled', false);
                                            window.location.href = data.links;
                                        });
                                    } else {
                                        setTimeout(function () {
                                            $this.prop('disabled', false);
                                            $this.find(".ctp_keys").prop('disabled', false);
                                            window.location.href = data.links;
                                        }, 2000);
                                    }

                                } else {
                                    var html = '<div class="comm-msg-license">Successful activated license key' +
                                        ' Product: ' + item_name + '</div>';
                                    if ($(".comm-msg-floating").length > 0) {
                                        $(html).appendTo(".comm-msg-floating").delay(2000).queue(function () {
                                            license_info.html(data.data_view_license);
                                            $(this).fadeOut().remove();
                                            $this.prop('disabled', false);
                                            $this.find(".ctp_keys").prop('disabled', false);
                                            window.location.href = data.links;
                                        });
                                    } else {
                                        setTimeout(function () {
                                            $this.prop('disabled', false);
                                            $this.find(".ctp_keys").prop('disabled', false);
                                            window.location.href = data.links;
                                        }, 2000);
                                    }

                                }
                            }else{
                                var html = '<div class="comm-msg-license">' + data.pesan +
                                    ' Product: ' + item_name + '</div>';
                                if ($(".comm-msg-floating").length > 0) {
                                    $(html).appendTo(".comm-msg-floating").delay(2000).queue(function () {
                                        license_info.html(data.pesan);
                                        $(this).fadeOut().remove();
                                        $this.prop('disabled', false);
                                        $this.find(".ctp_keys").prop('disabled', false);
                                        img_loading.find(".loading-img").remove();
                                    });
                                } else {
                                    setTimeout(function () {
                                        $this.prop('disabled', false);
                                        $this.find(".ctp_keys").prop('disabled', false);
                                        img_loading.find(".loading-img").remove();
                                    }, 2000);
                                }
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            var html = '<div class="comm-msg-license">internal server invalid</div>';
                            $(html).appendTo(".comm-msg-floating").delay(2000).queue(function() {
                                $(this).fadeOut().remove();
                            });
                            img_loading.find(".loading-img").remove();
                            $("#act_license").each(function () {
                                $(this).find(".act_licenses").prop('disabled', false);
                                $(this).find(".ctp_keys").prop('disabled', false);
                            });
                            $this.prop('disabled', false);
                            $this.find(".ctp_keys").prop('disabled', false);
                        }
                    });
                }, error: function (jqXHR, textStatus, errorThrown) {
                    $this.prop('disabled', false);
                    $this.find(".ctp_keys").prop('disabled', false);
                    var html = '<div class="comm-msg-license">Invalid activate or deactivate license</div>';
                    $(html).appendTo(".comm-msg-floating").delay(2000).queue(function() {
                        $(this).fadeOut().remove();
                    });
                    img_loading.find(".loading-img").remove();
                }
            });

        });
    });
})(jQuery);