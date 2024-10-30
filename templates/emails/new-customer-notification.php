<?php
/**
 * New customer notification email body.
 *
 * @author Commercioo Team
 * @package Commercioo
 */

if ( ! defined( 'WPINC' ) ) {
	exit;
}

echo wp_kses_post(sprintf( '<p>Dear %s,</p>', $user_display_name )); ?>
<p>Congratulations and welcome to <?php bloginfo( 'name' ); ?>, your new account has been created'. Please use the
    following info to login:</p>
<p>Site URL: <?php bloginfo( 'url' ); ?><br/>
	<?php echo wp_kses_post(sprintf( 'Username: <strong>%s</strong><br/>', $user_login )); ?>
    Password: <i>Please follow link below to update set your password</i><br/>
	<?php echo network_site_url( "wp-login.php?action=rp&key={$user_key}&login={$user_login}", 'login' ); ?>
</p>
<p>Best Regards,</p>
<p><?php bloginfo( 'name' ) ?> Team</p>
