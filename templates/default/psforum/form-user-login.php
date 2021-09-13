<?php

/**
 * User Login Form
 *
 * @package PSForum
 * @subpackage Theme
 */

?>

<form method="post" action="<?php psf_wp_login_action( array( 'context' => 'login_post' ) ); ?>" class="psf-login-form">
	<fieldset class="psf-form">
		<legend><?php _e( 'Einloggen', 'psforum' ); ?></legend>

		<div class="psf-username">
			<label for="user_login"><?php _e( 'Benutzername', 'psforum' ); ?>: </label>
			<input type="text" name="log" value="<?php psf_sanitize_val( 'user_login', 'text' ); ?>" size="20" id="user_login" tabindex="<?php psf_tab_index(); ?>" />
		</div>

		<div class="psf-password">
			<label for="user_pass"><?php _e( 'Passwort', 'psforum' ); ?>: </label>
			<input type="password" name="pwd" value="<?php psf_sanitize_val( 'user_pass', 'password' ); ?>" size="20" id="user_pass" tabindex="<?php psf_tab_index(); ?>" />
		</div>

		<div class="psf-remember-me">
			<input type="checkbox" name="rememberme" value="forever" <?php checked( psf_get_sanitize_val( 'rememberme', 'checkbox' ) ); ?> id="rememberme" tabindex="<?php psf_tab_index(); ?>" />
			<label for="rememberme"><?php _e( 'Ich möchte angemeldet bleiben', 'psforum' ); ?></label>
		</div>

		<?php do_action( 'login_form' ); ?>

		<div class="psf-submit-wrapper">

			<button type="submit" tabindex="<?php psf_tab_index(); ?>" name="user-submit" class="button submit user-submit"><?php _e( 'Log In', 'psforum' ); ?></button>

			<?php psf_user_login_fields(); ?>

		</div>
	</fieldset>
</form>
