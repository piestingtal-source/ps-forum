<?php

/**
 * User Lost Password Form
 *
 * @package PSForum
 * @subpackage Theme
 */

?>

<form method="post" action="<?php psf_wp_login_action( array( 'action' => 'lostpassword', 'context' => 'login_post' ) ); ?>" class="psf-login-form">
	<fieldset class="psf-form">
		<legend><?php _e( 'Passwort verloren', 'psforum' ); ?></legend>

		<div class="psf-username">
			<p>
				<label for="user_login" class="hide"><?php _e( 'Benutzername oder E-Mail-Adresse', 'psforum' ); ?>: </label>
				<input type="text" name="user_login" value="" size="20" id="user_login" tabindex="<?php psf_tab_index(); ?>" />
			</p>
		</div>

		<?php do_action( 'login_form', 'resetpass' ); ?>

		<div class="psf-submit-wrapper">

			<button type="submit" tabindex="<?php psf_tab_index(); ?>" name="user-submit" class="button submit user-submit"><?php _e( 'Setze mein Passwort zurück', 'psforum' ); ?></button>

			<?php psf_user_lost_pass_fields(); ?>

		</div>
	</fieldset>
</form>
