<?php
global $wp;
$current_url = comm_controller()->getCurrentMenuURL();
$the_id      = sanitize_text_field( isset( $_GET['id'] ) && $_GET['id'] != ''  ? $_GET['id'] : '');

// admin logo
$powered_by_label = new Commercioo_Powered_By_Label();
$admin_logo       = $powered_by_label->admin_logo_url;

?>

<div id="responsive-admin-menu">
    <div id="responsive-menu">
        <div class="menuicon">≡</div>
    </div>

    <div class="col-md-12 c-logo-wrap">
        <div class="row c-logo-nav">
            <a href="<?php echo esc_url(comm_controller()->comm_dash_page("comm_dashboard")) ?>" class="c-logo-link">
                <img src="<?php echo esc_url( $admin_logo )  ?>" class="img-fluid c-image-logo">
            </a>
        </div>
    </div>

    <!--Menu-->
    <div id="menu">
        <a href="<?php echo esc_url(comm_controller()->comm_dash_page("comm_dashboard"));?>" class="c-set-submenu <?php echo esc_attr(comm_controller()->is_comm_page(['admin_page_comm_dashboard', 'commercioo_page_comm_dashboard'])) ?'active':''?>">
            <img src="<?php echo esc_url(plugin_dir_url( __FILE__ ).'../img/icon-sidebar/icon-default/icon-dashboard.svg'); ?>" class="img-fluid set-margin-right-10 icon-dashboard"><span><?php _e("Dashboard","commercioo_menu");?></span></a>
        <a href="#" class="submenu c-set-submenu <?php echo esc_attr(comm_controller()->is_comm_page
        (['admin_page_comm_prod','admin_page_comm_category','admin_page_comm_tags','admin_page_comm_coupon']))
            ?'downarrow active':''?>" name="media-sub">
            <img src="<?php echo esc_url(plugin_dir_url( __FILE__ ).'../img/icon-sidebar/icon-default/icon-product.svg'); ?>" class="img-fluid set-margin-right-10 icon-dashboard"><span>Products</span></a>
        <!-- Media Sub Menu -->
        <div id="media-sub" class="sub-menu-icon" style="<?php echo (comm_controller()->is_comm_page(['admin_page_comm_prod','admin_page_comm_category','admin_page_comm_tags','admin_page_comm_coupon']))?'display: block;':'display: none;'?>">
            <a href="<?php echo esc_url(comm_controller()->comm_dash_page("comm_prod"));?>" class="c-set-submenu <?php echo esc_attr(comm_controller()->is_comm_page(['admin_page_comm_prod'])) ?'active':''?>"><span>Products</span></a>
            <a href="<?php echo esc_url(comm_controller()->comm_dash_page("comm_category"));?>" class="c-set-submenu <?php echo esc_attr(comm_controller()->is_comm_page
            (['admin_page_comm_category']))
                ?'active':''?>"><span>
                    Category</span></a>
            <a href="<?php echo esc_url(comm_controller()->comm_dash_page("comm_tags"));?>" class="c-set-submenu <?php echo esc_attr(comm_controller()->is_comm_page
            (['admin_page_comm_tags']))
                ?'active':''?>"><span>Tag</span></a>
        </div>
        <!-- Media Sub Menu -->
        <a href="<?php echo esc_url(comm_controller()->comm_dash_page("comm_order"));?>" class="c-set-submenu <?php echo esc_attr(comm_controller()->is_comm_page
        (['admin_page_comm_order']))
            ?'active':''?>">
            <img src="<?php echo esc_url(plugin_dir_url( __FILE__ ).'../img/icon-sidebar/icon-default/icon-order.svg'); ?>" class="img-fluid set-margin-right-10 icon-dashboard"><span>Orders</span></a>
        <a href="<?php echo esc_url(comm_controller()->comm_dash_page("comm_customers"));?>" class="c-set-submenu <?php echo esc_attr(comm_controller()->is_comm_page
        (['admin_page_comm_customers']))
            ?'active':''?>"><img src="<?php echo esc_url(plugin_dir_url( __FILE__ ).'../img/icon-sidebar/icon-default/icon-customer.svg'); ?>" class="img-fluid set-margin-right-10 icon-dashboard"><span> Customers</span></a>
        <a href="<?php echo esc_url(comm_controller()->comm_dash_page("comm_statistics"));?>" class="c-set-submenu <?php echo esc_attr(comm_controller()->is_comm_page
        (['admin_page_comm_statistics']))
            ?'active':''?>"><img src="<?php echo esc_url(plugin_dir_url( __FILE__ ).'../img/icon-sidebar/icon-default/icon-statistics.svg'); ?>" class="img-fluid set-margin-right-10 icon-dashboard"><span> Statistics</span></a>
        <?php if( is_comm_wa() ) :?>
            <a href="<?php echo esc_url(comm_controller()->comm_dash_page("comm_manage_cs")); ?>" class="c-set-submenu <?php echo esc_attr( comm_controller()->is_comm_page( ['admin_page_comm_manage_cs'] ) ) ? 'active' : '' ?>">
                <img src="<?php echo esc_url(plugin_dir_url( __FILE__ ).'../img/icon-sidebar/icon-default/icon-staffs.svg'); ?>" class="img-fluid set-margin-right-10 icon-dashboard"><span>Staffs</span></a>
            </a>
        <?php endif; ?>
        <?php if( is_comm_wa_followup() ) :?>
            <a href="<?php echo esc_url(comm_controller()->comm_dash_page("comm_wa_followup")); ?>" class="c-set-submenu <?php echo esc_attr( comm_controller()->is_comm_page( ['admin_page_comm_wa_followup'] ) ) ? 'active' : '' ?>">
                <img src="<?php echo esc_url(plugin_dir_url( __FILE__ ).'../img/icon-sidebar/icon-default/icon-followup-wa.svg'); ?>" class="img-fluid set-margin-right-10 icon-dashboard"><span>WA Followup</span></a>
            </a>
		<?php endif; ?>
        <?php if( is_comm_ar() ) :?>
            <a href="<?php echo esc_url(comm_controller()->comm_dash_page("comm_autoresponder")); ?>" class="c-set-submenu <?php echo esc_attr( comm_controller()->is_comm_page( ['admin_page_comm_autoresponder'] ) ) ? 'active' : '' ?>">
                <img src="<?php echo esc_url(plugin_dir_url( __FILE__ ).'../img/icon-sidebar/icon-default/icon-autoresponder.svg'); ?>" class="img-fluid set-margin-right-10 icon-dashboard"><span>Autoresponder</span></a>
            </a>
		<?php endif; ?>
        <?php do_action("commercioo_sidebar_menu");?>
        <a href="<?php echo esc_url(comm_controller()->comm_dash_page("comm_settings"));?>" class="c-set-submenu <?php echo esc_attr(comm_controller()->is_comm_page
        (['admin_page_comm_settings']))
            ?'active':''?>">
            <img src="<?php echo esc_url(plugin_dir_url( __FILE__ ).'../img/icon-sidebar/icon-default/icon-settings.svg'); ?>" class="img-fluid set-margin-right-10 icon-dashboard"><span>Settings</span></a>
        </a>
    </div>
    <!--Menu-->

    <div class="col-md-12 c-floating-icon-wrap">
    	<ul class="c-icon-floating">
			<li>
				<a class="c-set-floating c-gobal-bottom-icon" href="<?php echo esc_url(admin_url('admin.php?page=comm-system-status')); ?>">
					<img src="<?php echo esc_url(plugin_dir_url( __FILE__ ).'../img/bottom_1.svg'); ?>" data-bs-container="body"
					data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover" data-bs-content="<?php _e('Back to Wordpress Admin','commercioo');?>" alt="">
				</a>
			</li>
           <li>
               <a class="c-set-floating c-gobal-bottom-icon" href="<?php echo esc_url("https://commercioo.com/knowledge-base/");?>" target="_blank"><img src="<?php echo esc_url(plugin_dir_url( __FILE__ ).'../img/bottom_3.svg'); ?>" data-bs-container="body"
               data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover" data-bs-content="<?php _e('Tutorial','commercioo');?>" alt="">
               </a>
           </li>
           <li>
               <a class="c-set-floating c-gobal-bottom-icon" href="<?php echo esc_url("https://commercioo.com/community/");?>" target="_blank">
                   <img src="<?php echo esc_url(plugin_dir_url( __FILE__ ).'../img/bottom_5.svg'); ?>" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover" data-bs-content="<?php _e('Community','commercioo');?>" alt="">
               </a>
           </li>
        </ul>
    </div>
	
</div>