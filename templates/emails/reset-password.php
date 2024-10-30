<?php
/**
 * Reset password notification email body.
 *
 * @author Commercioo Team
 * @package Commercioo
 */

if ( ! defined( 'WPINC' ) ) {
	exit;
}

echo wp_kses_post(sprintf( '<p>Dear %s,</p>', $user_display_name )); ?>
<p>Click the link below to reset your account password:<br/>
	<?php echo network_site_url( "wp-login.php?action=rp&key={$user_key}&login={$user_login}", 'login' ) ?>
</p>
<p>If you didn't request a new password, you can safely delete or ignore this email.</p>
<p>Best Regards,</p>
<p><?php bloginfo( 'name' ) ?> Team</p>
