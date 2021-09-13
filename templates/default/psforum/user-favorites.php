<?php

/**
 * User Favorites
 *
 * @package PSForum
 * @subpackage Theme
 */

?>

	<?php do_action( 'psf_template_before_user_favorites' ); ?>

	<div id="psf-user-favorites" class="psf-user-favorites">
		<h2 class="entry-title"><?php _e( 'Lieblingsforumthemen', 'psforum' ); ?></h2>
		<div class="psf-user-section">

			<?php if ( psf_get_user_favorites() ) : ?>

				<?php psf_get_template_part( 'pagination', 'topics' ); ?>

				<?php psf_get_template_part( 'loop',       'topics' ); ?>

				<?php psf_get_template_part( 'pagination', 'topics' ); ?>

			<?php else : ?>

				<p><?php psf_is_user_home() ? _e( 'Du hast derzeit keine Lieblingsthemen.', 'psforum' ) : _e( 'Dieser Benutzer hat keine Lieblingsthemen.', 'psforum' ); ?></p>

			<?php endif; ?>

		</div>
	</div><!-- #psf-user-favorites -->

	<?php do_action( 'psf_template_after_user_favorites' ); ?>
