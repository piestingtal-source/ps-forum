<?php

/**
 * PSForum User Profile Edit
 *
 * @package PSForum
 * @subpackage Theme
 */

get_header(); ?>

	<?php do_action( 'psf_before_main_content' ); ?>

	<div id="psf-user-<?php psf_current_user_id(); ?>" class="psf-single-user">
		<div class="entry-content">

			<?php psf_get_template_part( 'content', 'single-user' ); ?>

		</div><!-- .entry-content -->
	</div><!-- #psf-user-<?php psf_current_user_id(); ?> -->

	<?php do_action( 'psf_after_main_content' ); ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
