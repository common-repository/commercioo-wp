<!-- Start Title -->
<div class="col-md-12 c-col-container">
    <div class="d-flex align-items-center">
        <h2 class="page-title"><?php _e("General Settings", "Commercioo_title"); ?></h2>
    </div>
</div>
<!-- End Title -->

<div class="row c-title-wrap-top c-tab-setting-up">
    <div class="col-md-12 p-0">
        <!-- Nav tabs -->
        <ul class="nav nav-tabs c-tabs-settings c-tab-setting-up-nav" role="tablist">
            <li class="nav-item c-nav-item">
                <a class="nav-link c-nav-link active c-set-tab" data-bs-toggle="tab" href="#general-settings" role="tab"
                   data-page-menu="general-settings">General Settings</a>
            </li>
            <li class="nav-item c-nav-item">
                <a class="nav-link c-nav-link c-set-tab" data-bs-toggle="tab" href="#payments" role="tab"
                   data-page-menu="payment">Payments</a>
            </li>
            <li class="nav-item c-nav-item">
                <a
                        class="nav-link c-nav-link c-set-tab"
                        href="<?php
                        $checkout_page_id = get_option('commercioo_Checkout_page_id');
                        if (defined('ELEMENTOR_VERSION')) {
                            echo esc_attr('#checkout');
                            $attribute = 'data-bs-toggle="tab" role="tab" data-page-menu="checkout"';
                        } else {
                            $checkout_url = get_permalink($checkout_page_id);
                            $checkout_url = $checkout_url ? $checkout_url : get_site_url();
                            $comm_setting_url = admin_url('admin.php?page=comm_settings');
                            $attribute = '';

                            echo esc_url(admin_url('customize.php') . '?url=' . $checkout_url . '&autofocus[panel]=commercioo_customize_checkout_settings&return=' . $comm_setting_url);
                        }
                        ?>" <?php echo wp_kses_post($attribute) ?>
                ><?php esc_html_e('Checkout', 'commercioo') ?></a>
            </li>
            <?php if (is_comm_wa()): ?>
                <!-- <li class="nav-item c-nav-item">
                    <a class="nav-link c-nav-link c-set-tab" data-bs-toggle="tab" href="#followup-messages" role="tab"
                       data-page-menu="follow-up-msg"
                    >FOLLOWUP
                        MESSAGES</a>
                </li> -->
            <?php endif; ?>
            <li class="nav-item c-nav-item">
                <a class="nav-link c-nav-link c-set-tab" data-bs-toggle="tab" href="#emails-settings" role="tab"
                   data-page-menu="emails-settings">Emails</a>
            </li>
            <li class="nav-item c-nav-item">
                <a class="nav-link c-nav-link c-set-tab" data-bs-toggle="tab" href="#tracking" role="tab"
                   data-page-menu="emails-settings">Tracking</a>
            </li>
            <?php if (has_action('comm_shipping_menu')): ?>
                <li class="nav-item c-nav-item">
                    <a class="nav-link c-nav-link c-set-tab" data-bs-toggle="tab" href="#shipping" role="tab"
                       data-page-menu="shipping">Shipping</a>
                </li>
            <?php endif; ?>
            <li class="nav-item c-nav-item">
                <a class="nav-link c-nav-link c-set-tab" data-bs-toggle="tab" href="#login-register" role="tab"
                   data-page-menu="login-register">Login / Register</a>
            </li>
        </ul>
    </div>
</div>
<!-- Tab panes -->
<div class="tab-content c-general-settings-content c-setting-up">
    <?php
    $timezone_format = _x('Y-m-d H:i:s', 'timezone date format');
    $current_offset = get_option('gmt_offset');
    $tzstring = get_option('timezone_string');

    $check_zone_info = true;

    // Remove old Etc mappings. Fallback to gmt_offset.
    if (false !== strpos($tzstring, 'Etc/GMT')) {
        $tzstring = '';
    }

    if (empty($tzstring)) { // Create a UTC+- zone if no timezone string exists.
        $check_zone_info = false;
        if (0 == $current_offset) {
            $tzstring = 'UTC+0';
        } elseif ($current_offset < 0) {
            $tzstring = 'UTC' . $current_offset;
        } else {
            $tzstring = 'UTC+' . $current_offset;
        }
    }

    ?>
    <!-- Start General Settings -->
    <div class="tab-pane active" id="general-settings" role="tabpanel">
        <form class="needs-validation" novalidate>
            <div class="row">
                <div class="col-md-2">
                    <label class="c-label"><?php _e('Timezone', 'commercioo') ?></label>
                </div>
                <div class="col-md-4">
                    <select id="timezone_string" name="timezone_string" class="form-control c-setting-form-control"
                            aria-describedby="timezone-description">
                        <?php echo wp_timezone_choice($tzstring, get_user_locale()); ?>
                    </select>
                    <div class="c-form-orders-desc">
                        <div class="description" id="timezone-description"><?php
                            printf(
                            /* translators: %s: UTC abbreviation */
                                __('Choose either a city in the same timezone as you or a %s (Coordinated Universal Time) time offset.'),
                                '<abbr>UTC</abbr>'
                            );
                            ?>
                        </div>
                    </div>
                    <div class="c-form-orders-desc">
                        <div class="timezone-info">
                            <div id="utc-time">
                            <?php
                            printf(
                            /* translators: %s: UTC time. */
                                __('Universal time is %s'),
                                '<code>' . date_i18n($timezone_format, false, true) . '</code>'
                            );
                            ?>
                            </div>
                            <?php if (get_option('timezone_string') || !empty($current_offset)) : ?>
                                <div id="local-time">
                            <?php
                            printf(
                            /* translators: %s: Local time. */
                                __('Local time is %s'),
                                '<code>' . date_i18n($timezone_format) . '</code>'
                            );
                            ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="c-form-orders-desc">
                        <?php if ($check_zone_info && $tzstring) : ?>
                            <div class="timezone-info">
                                <span>
                                    <?php
                                    $now = new DateTime('now', new DateTimeZone($tzstring));
                                    $dst = (bool)$now->format('I');

                                    if ($dst) {
                                        _e('This timezone is currently in daylight saving time.');
                                    } else {
                                        _e('This timezone is currently in standard time.');
                                    }
                                    ?>
                                    <br/>
                                    <?php
                                    if (in_array($tzstring, timezone_identifiers_list(), true)) {
                                        $transitions = timezone_transitions_get(timezone_open($tzstring), time());

                                        // 0 index is the state at current time, 1 index is the next transition, if any.
                                        if (!empty($transitions[1])) {
                                            echo ' ';
                                            $message = $transitions[1]['isdst'] ?
                                                /* translators: %s: Date and time. */
                                                __('Daylight saving time begins on: %s.') :
                                                /* translators: %s: Date and time. */
                                                __('Standard time begins on: %s.');
                                            printf(
                                                $message,
                                                '<code>' . wp_date(__('F j, Y') . ' ' . __('g:i a'), $transitions[1]['ts']) . '</code>'
                                            );
                                        } else {
                                            _e('This timezone does not observe daylight saving time.');
                                        }
                                    }
                                    ?>
                                    </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <label class="c-label"><?php _e('Currency', 'commercioo') ?></label>
                </div>
                <div class="col-md-4">
                    <?php $currency = comm_controller()->comm_get_currency_list(); ?>
                    <select name="currency" class="form-control c-setting-form-control">
                        <?php if ($currency): ?>
                            <?php foreach ($currency as $code => $name):
                                ?>
                                <option value="<?php echo esc_attr($code); ?>" <?php selected(isset
                                ($comm_options['currency']) ? $comm_options['currency'] : '', $code); ?>>
                                    <?php $symbol = comm_controller()->comm_get_currency_symbol($code); ?>
                                    <?php
                                    /* translators: 1: currency name 2: currency symbol, 3: currency code */
                                    echo esc_html(sprintf(__('%1$s (%2$s)', 'commercioo_settings'), $name, comm_controller()->comm_get_currency_symbol($code)));
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2"><label class="c-label"><?php _e('Currency Position', 'commercioo') ?></label>
                </div>
                <div class="col-md-4">
                    <select name="currency_position" class="form-control c-setting-form-control">
                        <option value="suffix" <?php selected(isset
                        ($comm_options['currency_position']) ? $comm_options['currency_position'] : '', 'suffix'); ?>>
                            Right: 10$
                        </option>
                        <option value="prefix" <?php selected(isset
                        ($comm_options['currency_position']) ? $comm_options['currency_position'] : '', 'prefix'); ?>>
                            Left: $10
                        </option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2"><label class="c-label"><?php _e('Thousand Separator', 'commercioo') ?></label>
                </div>
                <div class="col-md-4">
                    <input type="text"
                           class="form-control c-setting-form-control currency c-input-form c-set-cursor-pointer"
                           name="currency_thousand"
                           value="<?php echo esc_html(isset
                           ($comm_options['currency_thousand']) ? $comm_options['currency_thousand'] : '.'); ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-2"><label class="c-label"><?php _e('Decimal Separator', 'commercioo') ?></label>
                </div>
                <div class="col-md-4">
                    <input type="text"
                           class="form-control c-setting-form-control currency c-input-form c-set-cursor-pointer"
                           name="currency_decimal"
                           value="<?php echo esc_html(isset
                           ($comm_options['currency_decimal']) ? $comm_options['currency_decimal'] : ','); ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-2"><label class="c-label"><?php _e('Number of decimals', 'commercioo') ?></label>
                </div>
                <div class="col-md-4">
                    <input type="number" value="<?php echo esc_html(isset
                    ($comm_options['currency_decimal_limit']) ? $comm_options['currency_decimal_limit'] : '0'); ?>"
                           min="0" step="1"
                           data-number-stepfactor="100"
                           class="form-control c-setting-form-control currency c-input-form c-set-cursor-pointer"
                           name="currency_decimal_limit">
                </div>
            </div>

            <div class="col-md-7 mt-4 mb-4 ml-3 c-line-dash-settings"></div>

            <div class="row">
                <div class="col-md-2">
                    <label class="c-label"><?php _e('Store Name', 'commercioo') ?></label>
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control c-setting-form-control c-input-form c-set-cursor-pointer"
                           placeholder="Jaya Abadi"
                           name="store_name" value="<?php echo esc_html(isset
                    ($comm_options['store_name']) ? $comm_options['store_name'] : ''); ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <label class="c-label"><?php esc_html_e('Store Logo', 'commercioo') ?></label>
                </div>
                <div class="col-md-4">
                    <input
                            type="hidden"
                            class="c-image-featured"
                            value="<?php echo esc_html(isset($comm_options['store_logo']) ? $comm_options['store_logo'] : ''); ?>"
                            name="store_logo"
                    >
                    <div class="input-group">
                        <div class="mb-2">
                            <a href="#" class="browse-store-logo c-btn-icon-right">
                                <button>
                                    <i class="fa fa-upload" aria-hidden="true"></i>
                                </button>
                                <span><?php esc_html_e('Upload', 'commercioo') ?></span>
                            </a>
                        </div>
                    </div>
                    <div class="comm-clear c-form-orders-desc">
                        <?php esc_html_e('Paste your store log URL or upload an image. Recommended image size minimum 300 x 65 pixel.', 'commercioo') ?>
                    </div>
                    <div class="row c-set-image">
                        <div class="c-photo store-logo-preview">
                            <?php
                            if (isset($comm_options['store_logo']) && intval($comm_options['store_logo'])) :
                                $thumb_id = intval($comm_options['store_logo']);
                                $thumb = wp_get_attachment_image_src($thumb_id, 'thumbnail');

                                if ($thumb) :
                                    $thumb_url = $thumb[0];
                                    ?>
                                    <div class="c-preview-image c-feature-image">
                                        <img class="preview c-img-thumbnail" id="preview"
                                             src="<?php echo esc_url($thumb_url) ?>" alt="">
                                        <div class="c-close-icon-wrap remove-store-logo">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                 stroke-linecap="round" stroke-linejoin="round"
                                                 class="feather feather-x feather-16 c-close-wrap">
                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                            </svg>
                                        </div>
                                    </div>
                                <?php
                                endif;
                            endif
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-7 mt-4 mb-4 ml-3 c-line-dash-settings"></div>

            <div class="row">
                <div class="col-md-2"><label class="c-label"><?php _e('Country', 'commercioo') ?></label></div>
                <div class="col-md-4">
                    <?php $country = comm_controller()->comm_get_country(); ?>
                    <select name="store_country" class="form-control c-setting-form-control">
                        <?php if ($country): ?>
                            <?php foreach ($country as $code => $name):
                                ?>
                                <option value="<?php echo esc_attr($code); ?>" <?php selected(isset
                                ($comm_options['store_country']) ? $comm_options['store_country'] : '', $code); ?>>
                                    <?php echo esc_html($name) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <label class="c-label"><?php _e('Street Address', 'commercioo') ?></label>
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control c-setting-form-control c-input-form c-set-cursor-pointer"
                           name="store_address" placeholder="Street Address" value="<?php echo esc_html(isset
                    ($comm_options['store_address']) ? $comm_options['store_address'] : ''); ?>">
                </div>
            </div>
            <div class="col-md-7 mt-4 mb-4 ml-3 c-line-dash-settings"></div>

            <div class="col-md-6 set-margin-bottom-20 ms-4">
                <button type="submit" class="btn btn-primary c-save-settings">Save</button>
                <input type="hidden" name="comm_key" value="general">
            </div>
        </form>
    </div>
    <!-- End General Settings -->

    <!-- Start Payments -->
    <div class="tab-pane" id="payments" role="tabpanel">
        <?php
        $settings_tab = \Commercioo\Helper::comm_registered_payment_sub_tab_menu_settings();
        ?>
        <div class="col-md-12 sub-tab">
            <ul class="nav nav-tabs" id="payment-submenu" role="tablist">
                <?php
                foreach ($settings_tab as $tab_setting) {
                    foreach ($tab_setting as $k => $va_setting) {
                        if ($va_setting['is_tab']) {
                            if ($va_setting['content']['content']) {
                                $url = "#" . $va_setting['target'];
                                ?>
                                <li>
                                    <a class="sub-tab-menu nav-item" data-bs-toggle="tab"
                                       data-bs-target="<?php echo esc_url($url); ?>"
                                       href="<?php echo esc_url($url); ?>" role="tab"
                                       aria-selected="true"><?php echo wp_kses_post($va_setting['icon']); ?><?php echo $va_setting['label']; ?></a>
                                </li>
                                <?php
                            }
                        }
                    }
                }
                ?>
            </ul>
        </div>
        <div class="col-md-12 mt-2 mb-4 ml-3 c-line-dash-settings-sub-tab"></div>
        <?php
        foreach ($settings_tab as $tab_setting) {
            foreach ($tab_setting as $k => $va_setting) {
                if ($va_setting['content']['content'] != '') {
                    echo $va_setting['content']['content'];
                }
            }
        }
        ?>
    </div>
    <!-- Start Payments -->

    <!-- Start Checkout -->
    <div class="tab-pane" id="checkout" role="tabpanel">
        <form class="needs-validation" novalidate>
            <?php if (defined('ELEMENTOR_VERSION') && !\Elementor\Plugin::$instance->documents->get($checkout_page_id)->is_built_with_elementor()) : ?>
                <div class="row">
                    <div class="col-md-2">
                        <label class="c-label"><?php _e('Checkout page settings', 'commercioo') ?></label>
                    </div>
                    <div class="col-md-5 d-flex align-items-center">
                        <input type="hidden" name="checkout_elementor_status" value="true">
                        <input type="hidden" name="checkout_elementor_url"
                               value="<?php echo admin_url('post.php?post=' . $checkout_page_id . '&action=elementor') ?>">
                        <a class="button button-primary button-large" id="commerciooCheckoutElementor" href="#">
                        <span class="elementor-switch-mode-off">
						    <i class="eicon-elementor-square" aria-hidden="true"></i>
						    Switch to Elementor
                        </span>
                        </a>
                        <span class="mx-3">or</span>
                        <?php
                        $checkout_url = get_permalink($checkout_page_id);
                        $checkout_url = $checkout_url ? $checkout_url : get_site_url();
                        $comm_setting_url = admin_url('admin.php?page=comm_settings');
                        ?>
                        <a href="<?php echo esc_url(admin_url('customize.php') . '?url=' . $checkout_url . '&autofocus[panel]=commercioo_customize_checkout_settings&return=' . $comm_setting_url); ?>">Go
                            to Checkout Settings</a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-9 mt-4 mb-4 ml-3 c-line-dash-settings"></div>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-md-2">
                        <label class="c-label"><?php _e('Checkout page settings', 'commercioo') ?></label>
                    </div>
                    <div class="col-md-5">
                        <input type="hidden" name="checkout_elementor_status" value="false">
                        <input type="hidden" name="checkout_elementor_url"
                               value="<?php echo admin_url('post.php?post=' . $checkout_page_id . '&action=edit') ?>">
                        <?php echo sprintf("Click %s if you want to using shortcode default general checkout page", '<a id="commercioo_restore_default_general" href="#">here</a>'); ?>
                        <div class="c-form-orders-desc">Note: Checkout page content will be replace with the default
                            shortcode general checkout page
                        </div>
                    </div>
                </div>
                <div class="col-md-9 mt-4 mb-4 ml-3 c-line-dash-settings"></div>
            <?php endif;
            if (defined('ELEMENTOR_VERSION') && \Elementor\Plugin::$instance->documents->get($checkout_page_id)->is_built_with_elementor()) : ?>
                <div class="row">
                    <div class="col-md-2"><label
                                class="c-label"><?php _e('Redirect type after submit', 'commercioo') ?></label>
                    </div>
                    <div class="col-md-4">
                        <select name="thank_you_redirect" class="form-control c-setting-form-control">
                            <?php $checkout_options = apply_filters(
                                'commercioo_customizer_checkout_redirect_choices',
                                array(
                                    'page' => 'Thank You Page'
                                ));

                            foreach ($checkout_options as $key => $value) {
                                ?>
                                <option value="<?php echo esc_html($key) ?>" <?php echo ($comm_options['thank_you_redirect'] == $key) ? 'selected' : '' ?>>
                                    <?php echo esc_html($value) ?>
                                </option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-9 mt-4 mb-4 ml-3 c-line-dash-settings"></div>
                <?php do_action('comm_checkout_setting', $comm_options) ?>
            <?php endif; ?>
            <?php if (defined('ELEMENTOR_VERSION') && \Elementor\Plugin::$instance->documents->get($checkout_page_id)->is_built_with_elementor()) : ?>
                <div class="row">
                    <div class="col-md-6 set-margin-bottom-20 ms-4">
                        <button type="submit" class="btn btn-primary c-save-settings">Save</button>
                    </div>
                </div>
            <?php endif; ?>
            <input type="hidden" name="comm_key" value="order_forms">
        </form>
    </div>
    <!-- End Checkout -->

    <!-- Start Followup Messages -->
    <?php if (is_comm_wa()): ?>
        <?php apply_filters("comm_followup_msg_page", ''); ?>
    <?php endif; ?>
    <!-- End Followup Messages -->

    <!-- Start Shipping Settings -->
    <?php if (is_comm_ongkir() || class_exists('Commercioo_Jne')): ?>
        <div class="tab-pane" id="shipping" role="tabpanel">
            <div class="col-md-8 sub-tab">
                <ul>
                    <?php do_action("comm_shipping_menu", $comm_options); ?>
                </ul>
            </div>
            <div class="col-md-12">
                <?php do_action("comm_ongkir_setting", $comm_options); ?>
            </div>

        </div>
    <?php endif; ?>
    <!-- End Shipping Settings -->

    <div class="tab-pane" id="tracking" role="tabpanel">
        <?php if (is_comm_pro()) : ?>
            <div class="col-md-8 sub-tab">
                <ul class="nav nav-tabs" role="tablist">
                    <li><a class="sub-tab-menu active" data-bs-toggle="tab" href="#header-footer" role="tab"
                           data-page-menu="header-footer">Header Footer</a></li>
                    <li><a class="sub-tab-menu" data-bs-toggle="tab" href="#facebook-pixel" role="tab"
                           data-page-menu="facebook-pixel">Facebook Pixel</a></li>
                </ul>
            </div>
        <?php endif; ?>
        <form class="needs-validation" novalidate>
            <div class="col-md-12 ps-4">
                <div id="header-footer" class="col-md-8 sub-tab-content active">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="c-label">Header Script</label>
                        </div>
                        <div class="col-md-9 mb-3">
                            <textarea class="form-control c-setting-form-control c-input-form c-set-cursor-pointer"
                                      rows="8"
                                      name="header_code"
                                      placeholder="Paste your script here"
                            ><?php echo isset
                                ($comm_options['header_code']) ? $comm_options['header_code'] : ''; ?></textarea>
                            <div class="c-form-orders-desc">
                                If you want to integrate custom script with Commercioo order form URL and thank you URL,
                                please
                                paste your script on the field above. Usually it's used for Google Tag Manager, Facebook
                                Pixel,
                                or Google Analytics script. The script is placed between <b>&#60;head&#62;&#60;/head&#62;
                                    tag.</b>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="c-label">Footer Script</label>
                        </div>
                        <div class="col-md-9 mb-3">
                            <textarea class="form-control c-setting-form-control c-input-form c-set-cursor-pointer"
                                      rows="8"
                                      name="footer_code"
                                      placeholder="Paste your script here"
                            ><?php echo isset
                                ($comm_options['footer_code']) ? $comm_options['footer_code'] : ''; ?></textarea>
                            <div class="c-form-orders-desc">
                                If you want to integrate custom script with Commercioo order form URL and thank you URL,
                                please
                                paste your script on the field above. Usually it's used for Google Tag Manager, Facebook
                                Pixel,
                                or Google Analytics script. The script is placed between <b>&#60;head&#62;&#60;/head&#62;
                                    tag.</b>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="facebook-pixel" class="col-md-18 sub-tab-content">
                    <?php apply_filters("commercioo_fb_pixel_event", $comm_options); ?>
                </div>
            </div>
            <div class="col-md-6 set-margin-bottom-20 ms-4">
                <button type="submit" class="btn btn-primary c-save-settings">Save</button>
                <input type="hidden" name="comm_key" value="misc">
            </div>
        </form>
    </div>

    <!-- Start Login Register Settings -->
    <div class="tab-pane login-register" id="login-register" role="tabpanel">
        <form class="needs-validation" novalidate>
            <div class="row">
                <div class="col-md-2">
                    <label><?php _e('Login Message', 'commercioo') ?></label>
                </div>
                <div class="col-md-6">
                    <div class="form-check">
                        <input name="login_message_enabled" class="form-check-input bg-white" type="checkbox" value="1"
                               id="login_message_enabled" <?php echo isset($comm_options['login_message_enabled']) && $comm_options['login_message_enabled'] == true ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="login_message_enabled">
                            Enable
                        </label>
                    </div>
                    <div class="form-group">
                        <textarea class="form-control c-setting-form-control c-input-form c-set-cursor-pointer" rows="8"
                                  name="login_message"
                                  id="message_login"
                        ><?php echo isset
                            ($comm_options['login_message']) ? $comm_options['login_message'] : ''; ?></textarea>
                    </div>
                </div>
            </div>
            <div class="col-md-9 mt-4 mb-4 ml-3 c-line-dashed-settings"></div>
            <div class="row">
                <div class="col-md-2">
                    <label><?php _e('Register Message', 'commercioo') ?></label>
                </div>
                <div class="col-md-6">
                    <div class="form-check">
                        <input name="register_message_enabled" class="form-check-input bg-white" type="checkbox"
                               value="1"
                               id="register_message_enabled" <?php echo isset($comm_options['register_message_enabled']) && $comm_options['register_message_enabled'] == true ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="register_message_enabled">
                            Enable
                        </label>
                    </div>
                    <div class="form-group">
                        <textarea class="form-control c-setting-form-control c-input-form c-set-cursor-pointer" rows="8"
                                  name="register_message"
                                  id="message_register"
                        ><?php echo isset
                            ($comm_options['register_message']) ? $comm_options['register_message'] : ''; ?></textarea>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-2">
                    <label><?php _e('Register Agreement', 'commercioo') ?></label>
                </div>
                <div class="col-md-6">
                    <div class="form-check">
                        <input name="agreement_message_enabled" class="form-check-input bg-white" type="checkbox"
                               value="1"
                               id="agreement_message_enabled" <?php echo isset($comm_options['agreement_message_enabled']) && $comm_options['agreement_message_enabled'] == true ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="agreement_message_enabled">
                            Enable
                        </label>
                    </div>
                    <div class="form-group">
                        <textarea class="form-control c-setting-form-control c-input-form c-set-cursor-pointer" rows="8"
                                  name="agreement_message"
                                  id="message_agreement"
                        ><?php echo isset
                            ($comm_options['agreement_message']) ? $comm_options['agreement_message'] : ''; ?></textarea>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-2">
                    <label><?php _e('Forgot Password Message', 'commercioo') ?></label>
                </div>
                <div class="col-md-6">
                    <div class="form-check">
                        <input name="forgot_message_enabled" class="form-check-input bg-white" type="checkbox" value="1"
                               id="forgot_message_enabled" <?php echo isset($comm_options['forgot_message_enabled']) && $comm_options['forgot_message_enabled'] == true ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="forgot_message_enabled">
                            Enable
                        </label>
                    </div>
                    <div class="form-group">
                        <textarea class="form-control c-setting-form-control c-input-form c-set-cursor-pointer" rows="8"
                                  name="forgot_message"
                                  id="message_forgot"
                        ><?php echo isset
                            ($comm_options['forgot_message']) ? $comm_options['forgot_message'] : ''; ?></textarea>
                    </div>
                </div>
            </div>
            <div class="col-md-6 set-margin-bottom-20 ms-4">
                <button type="submit" class="btn btn-primary c-save-settings">Save</button>
                <input type="hidden" name="comm_key" value="login_register">
            </div>
        </form>
    </div>
    <!-- End Login Register Settings -->

    <!-- Get Emails Settings -->
    <?php
    $default_emails = new \Commercioo\Emails\Default_Emails();
    $emails_settings = $default_emails->current_settings;
    ?>

    <!-- Start Emails Settings -->
    <div class="tab-pane" id="emails-settings" role="tabpanel">
        <form class="needs-validation" novalidate>
            <div class="row">
                <div class="col-md-7">
                    <table class="table table-borderless c-email-table">
                        <thead>
                        <tr>
                            <th></th>
                            <th>Email</th>
                            <th>Recipient(s)</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td><span class="badge c-badge-rounded green">Enable</span></td>
                            <td>
                                <a href="#" class="c-link" data-email="mail_pending_order" data-bs-toggle="modal"
                                   data-bs-target=".comm-email-form-settings">Order status: Pending</a>
                                <div class="d-none" data-email-desc="mail_pending_order">
                                    <div>Dynamic variables you can use</div>
                                    <div><code>{name}</code> - The customer's name</div>
                                    <div><code>{sitename}</code> - The website's name</div>
                                    <div><code>{bank}</code> - The users's bank account(s)
                                        <div>
                                            <div><code>{order_details}</code> - The customer'sorder details</div>
                                        </div>
                            </td>
                            <td>Customer</td>
                        </tr>
                        <tr>
                            <td><span class="badge c-badge-rounded green">Enable</span></td>
                            <td>
                                <a href="#" class="c-link" data-email="mail_processing_order" data-bs-toggle="modal"
                                   data-bs-target=".comm-email-form-settings">Order status: Processing</a>
                                <div class="d-none" data-email-desc="mail_processing_order">
                                    <div>Dynamic variables you can use</div>
                                    <div><code>{name}</code> - The customer's name</div>
                                    <div><code>{order_id}</code> - The customer's order ID</div>
                                    <div><code>{sitename}</code> - The website's name</div>
                                    <div><code>{order_details}</code> - The customer's order details</div>
                                    <div><code>{support_email}</code> - The website's support email address</div>
                                </div>
                            </td>
                            <td>Customer</td>
                        </tr>
                        <tr>
                            <td><span class="badge c-badge-rounded green">Enable</span></td>
                            <td>
                                <a href="#" class="c-link" data-email="mail_completed_order" data-bs-toggle="modal"
                                   data-bs-target=".comm-email-form-settings">Order status: Complete</a>
                                <div class="d-none" data-email-desc="mail_completed_order">
                                    <div>Dynamic variables you can use</div>
                                    <div><code>{name}</code> - The customer's name</div>
                                    <div><code>{order_id}</code> - The customer's order ID</div>
                                    <div><code>{sitename}</code> - The website's name</div>
                                    <div><code>{order_details}</code> - The customer's order details</div>
                                    <div><code>{user_address}</code> - The customer's address</div>
                                    <div><code>{support_email}</code> - The website's support email address</div>
                                </div>
                            </td>
                            <td>Customer</td>
                        </tr>
                        <tr>
                            <td><span class="badge c-badge-rounded green">Enable</span></td>
                            <td>
                                <a href="#" class="c-link" data-email="mail_refund_order" data-bs-toggle="modal"
                                   data-bs-target=".comm-email-form-settings">Order status: Refund</a>
                                <div class="d-none" data-email-desc="mail_refund_order">
                                    <div>Dynamic variables you can use</div>
                                    <div><code>{name}</code> - The customer's name</div>
                                    <div><code>{order_id}</code> - The customer's order ID</div>
                                    <div><code>{sitename}</code> - The website's name</div>
                                    <div><code>{subtotal}</code> - The order's total amount</div>
                                </div>
                            </td>
                            <td>Customer</td>
                        </tr>
                        <tr>
                            <td><span class="badge c-badge-rounded green">Enable</span></td>
                            <td>
                                <a href="#" class="c-link" data-email="mail_failed_order" data-bs-toggle="modal"
                                   data-bs-target=".comm-email-form-settings">Order status: Failed</a>
                                <div class="d-none" data-email-desc="mail_failed_order">
                                    <div>Dynamic variables you can use</div>
                                    <div><code>{name}</code> - The customer's name</div>
                                    <div><code>{order_id}</code> - The customer's order ID</div>
                                    <div><code>{sitename}</code> - The website's name</div>
                                    <div><code>{order_details}</code> - The customer's order details</div>
                                    <div><code>{complete_purchase_url}</code> - The website's url to complete the
                                        purchase
                                    </div>
                                </div>
                            </td>
                            <td>Customer</td>
                        </tr>
                        <tr>
                            <td><span class="badge c-badge-rounded green">Enable</span></td>
                            <td>
                                <a href="#" class="c-link" data-email="mail_cancelled_order" data-bs-toggle="modal"
                                   data-bs-target=".comm-email-form-settings">Order status: Cancelled</a>
                                <div class="d-none" data-email-desc="mail_cancelled_order">
                                    <div>Dynamic variables you can use</div>
                                    <div><code>{name}</code> - The customer's name</div>
                                    <div><code>{order_id}</code> - The customer's order ID</div>
                                    <div><code>{sitename}</code> - The website's name</div>
                                    <div><code>{order_details}</code> - The customer's order details</div>
                                </div>
                            </td>
                            <td>Customer</td>
                        </tr>
                        <tr>
                            <td><span class="badge c-badge-rounded green">Enable</span></td>
                            <td>
                                <a href="#" class="c-link" data-email="mail_new_account_customer" data-bs-toggle="modal"
                                   data-bs-target=".comm-email-form-settings">New Customer Account (Manual Register)</a>
                                <div class="d-none" data-email-desc="mail_new_account_customer">
                                    <div>Dynamic variables you can use</div>
                                    <div><code>{name}</code> - The customer's name</div>
                                    <div><code>{sitename}</code> - The website's name</div>
                                    <div><code>{login_url}</code> - The website's login url</div>
                                    <div><code>{username}</code> - The user's login username</div>
                                    <div><code>{password}</code> - The user's login password</div>
                                </div>
                            </td>
                            <td>Customer</td>
                        </tr>
                        <?php if (is_comm_wa()) : ?>
                            <tr>
                                <td><span class="badge c-badge-rounded green">Enable</span></td>
                                <td>
                                    <a href="#" class="c-link" data-email="mail_new_account_cs" data-bs-toggle="modal"
                                       data-bs-target=".comm-email-form-settings">New Staff Account</a>
                                    <div class="d-none" data-email-desc="mail_new_account_cs">
                                        <div>Dynamic variables you can use</div>
                                        <div><code>{sitename}</code> - The website's name</div>
                                        <div><code>{login_url}</code> - The website's login url</div>
                                        <div><code>{username}</code> - The user's login username</div>
                                        <div><code>{admin_name}</code> - The website's admin name</div>
                                        <div><code>{create_password_url}</code> - The website's url to create user's
                                            password
                                        </div>
                                    </div>
                                </td>
                                <td>Staff</td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td><span class="badge c-badge-rounded green">Enable</span></td>
                            <td>
                                <a href="#" class="c-link" data-email="mail_reset_password" data-bs-toggle="modal"
                                   data-bs-target=".comm-email-form-settings">Forgot / Reset Password</a>
                                <div class="d-none" data-email-desc="mail_reset_password">
                                    <div>Dynamic variables you can use</div>
                                    <div><code>{name}</code> - The customer's name</div>
                                    <div><code>{sitename}</code> - The website's name</div>
                                    <div><code>{reset_password_url}</code> - The website's reset password url</div>
                                    <div><code>{support_email}</code> - The website's support email address</div>
                                </div>
                            </td>
                            <td>Customer</td>
                        </tr>
                        <tr>
                            <td><span class="badge c-badge-rounded green">Enable</span></td>
                            <td>
                                <a href="#" class="c-link" data-email="mail_admin_new_order_notification"
                                   data-bs-toggle="modal" data-bs-target=".comm-email-form-settings">Order Notification
                                    to
                                    Admin</a>
                                <div class="d-none" data-email-desc="mail_admin_new_order_notification">
                                    <div>Dynamic variables you can use</div>
                                    <div><code>{name}</code> - The customer's name</div>
                                    <div><code>{order_id}</code> - The customer's order ID</div>
                                    <div><code>{sitename}</code> - The website's name</div>
                                    <div><code>{order_details}</code> - The customer's order details</div>
                                </div>
                            </td>
                            <td>Admin</td>
                        </tr>
                        <tr>
                            <td><span class="badge c-badge-rounded green">Enable</span></td>
                            <td>
                                <a href="#" class="c-link" data-email="mail_admin_new_customer_account_notification"
                                   data-bs-toggle="modal" data-bs-target=".comm-email-form-settings">New Account
                                    Notification
                                    to Admin</a>
                                <div class="d-none" data-email-desc="mail_admin_new_customer_account_notification">
                                    <div>Dynamic variables you can use</div>
                                    <div><code>{name}</code> - The customer's name</div>
                                    <div><code>{customer_email}</code> - The customer's email address</div>
                                    <div><code>{username}</code> - The customer's login username</div>
                                    <div><code>{sitename}</code> - The website's name</div>
                                </div>
                            </td>
                            <td>Admin</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-5">
                    <div class="c-form-orders-desc">
                        <?php esc_html_e('We recommed you to use custom SMTP service for better email delivery.', 'commercioo');
                        if (!is_plugin_active('post-smtp/postman-smtp.php')) : ?>
                            <a href="<?php _e(admin_url('plugins.php?page=tgmpa-install-plugins')) ?>">Click
                                here</a> <?php esc_html_e('to install the plugin we recommended', 'commercioo') ?>;
                        <?php endif ?>
                        <strong>Post SMTP Mailer/Email Log</strong>
                        <?php _e(is_plugin_active('post-smtp/postman-smtp.php') ? '<strong> (Already Installed)</strong>' : '') ?>
                    </div>
                </div>
            </div>
            <!-- <div class="col-md-6 set-margin-bottom-20">
                <button type="submit" class="btn btn-primary c-save-settings">Save Settings</button>
                <input type="hidden" name="comm_key" value="emails">
            </div> -->
        </form>
    </div>
    <!-- End Emails Settings -->
</div>
<!-- Modal -->
<div class="modal fade comm-email-form-settings" tabindex="-1" role="dialog" aria-labelledby="comm-email-form-settings"
     aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header set-pt-pb-15">
                <h2 class="modal-heading c-settings-modal-heading set-font-size-16"><?php _e("Edit Email - ", "commercioo"); ?>
                    <span></span></h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="modal-content">
                    <form>
                        <div class="row">
                            <!-- if enabled feature already registered -->

                            <!-- <div class="col-md-2"></div>
                            <div class="col-md-10 mb-4">
                                <div class="form-check">
                                    <input type="checkbox" class="c-set-cursor-pointer" id="mail_status">
                                    <label class="form-check-label c-label" for="mail_status">Enable</label>
                                </div>
                            </div> -->
                            <div class="col-md-2">
                                <label class="c-label"><?php _e('Email Subject', 'commercioo') ?></label>
                            </div>
                            <div class="col-md-10 mb-3">
                                <input type="text"
                                       class="form-control c-setting-form-control currency c-input-form c-set-cursor-pointer"
                                       name="email_subject" placeholder="Email Subject">
                            </div>
                            <div class="col-md-2">
                                <label class="c-label"><?php _e('Email Body', 'commercioo') ?></label>
                            </div>
                            <div class="col-md-10 mb-3">
                                <textarea
                                        class="form-control c-setting-form-control c-input-form c-set-cursor-pointer"
                                        name="email_body"
                                        id="mail_body" placeholder="Email Content">
                                </textarea>
                            </div>
                            <div class="col-md-2"></div>
                            <div class="col-md-10" data-description="description"></div>
                        </div>
                        <div class="col-md-6 set-margin-bottom-20 ms-4">
                            <input type="hidden" name="comm_settings_email_name">
                            <input type="hidden" name="comm_key" value="emails">
                            <button type="submit" class="btn btn-primary c-save-settings">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Modal -->