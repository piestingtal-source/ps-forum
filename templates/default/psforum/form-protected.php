<?php

/**
 * Password Protected
 *
 * @package PSForum
 * @subpackage Theme
 */

?>

<div id="psforum-forums">
	<fieldset class="psf-form" id="psf-protected">
		<Legend><?php _e( 'Geschützt', 'psforum' ); ?></legend>

		<?php echo get_the_password_form(); ?>

	</fieldset>
</div>