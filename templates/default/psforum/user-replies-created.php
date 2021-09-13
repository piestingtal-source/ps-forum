<?php

/**
 * User Replies Created
 *
 * @package PSForum
 * @subpackage Theme
 */

?>

	<?php do_action( 'psf_template_before_user_replies' ); ?>

	<div id="psf-user-replies-created" class="psf-user-replies-created">
		<h2 class="entry-title"><?php _e( 'Forum-Antworten erstellt', 'psforum' ); ?></h2>
		<div class="psf-user-section">

			<?php if ( psf_get_user_replies_created() ) : ?>

				<?php psf_get_template_part( 'pagination', 'replies' ); ?>

				<?php psf_get_template_part( 'loop',       'replies' ); ?>

				<?php psf_get_template_part( 'pagination', 'replies' ); ?>

			<?php else : ?>

				<p><?php psf_is_user_home() ? _e( 'Du hast auf keine Themen geantwortet.', 'psforum' ) : _e( 'Dieser Benutzer hat auf keine Themen geantwortet.', 'psforum' ); ?></p>

			<?php endif; ?>

		</div>
	</div><!-- #psf-user-replies-created -->

	<?php do_action( 'psf_template_after_user_replies' ); ?>
