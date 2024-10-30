<div class='wrap commercioo' id='commercioo-license-page'>
    <h2 id='commerciooo-admin-title'><img src='<?php echo COMMERCIOO_URL . 'admin/img/logo.png' ?>'>
        <span>Commercioo</span></h2>
    <nav class="nav-tab-wrapper comm-nav-tab-wrapper">
        <?php
        $screen = get_current_screen();
        $page_id = $screen->id;
        $tab_menu = apply_filters('commercioo_admin_tabs', array(
            array(
                'url' => admin_url('admin.php?page=comm-system-status'),
                'label' => __('System Status', 'commercioo-agency'),
                'page_id' => 'toplevel_page_comm-system-status',
            ),
        ));

        // print the tabs
        foreach ($tab_menu as $menu) {
            $active_status = ($menu['page_id'] === $page_id) ? 'nav-tab-active' : '';
            printf('<a href="%s" class="nav-tab %s">%s</a>', esc_url($menu['url']), esc_attr($active_status), esc_html($menu['label']));
        }

        ?>
    </nav>

    <form method="post" action="<?php echo admin_url('admin.php'); ?>">
        <!-- key for admin_action -->
        <input type="hidden" name="action" value="comm_license_page_settings"/>
        <!-- nonce -->
        <?php wp_nonce_field('FkapDAyH75XY3'); ?>

        <div class="postbox ">
            <div class="commercioo-license-page">
                <div class="commercioo-license-page-content">
                    <div class="license-page-content">
                        <div class="license-page-content-container">
                            <h2>Enter your license key</h2>
                            <p>Enter the license key for each of the following products to get further updates on
                                your WordPress admin dashboard.</p>
                            <?php if (has_filter("commercioo/license/plugins/check-content")): ?>
                                <h3>Plugins:</h3>
                                <ul class="license-page-plugins">
                                    <?php do_action("commercioo/license/content"); ?>
                                </ul>
                            <?php endif; ?>
                            <?php do_action("commercioo/license/content-theme"); ?>
                        </div>
                    </div>
                </div>
                <div class="comm-msg-floating"></div>
            </div>
        </div>

    </form>

</div>