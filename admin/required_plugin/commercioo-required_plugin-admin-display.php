<div class='wrap commercioo commercioo-required-plugin' id='commercioo-license-page'>
    <h2 id='commerciooo-admin-title'><img src='<?php echo esc_url(COMMERCIOO_URL . 'admin/img/logo.png'); ?>'>
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
            wp_kses_post(printf('<a href="%s" class="nav-tab %s">%s</a>', esc_url($menu['url']), esc_attr($active_status), esc_html($menu['label'])));
        }

        ?>
    </nav>

    <div class="commercioo-required-plugin-main full">
        <?php
            $required_plugin = new \Commercioo\Admin\Required_Plugin_Page();
            $required_plugin->init_display();
        ?>
    </div>

</div>