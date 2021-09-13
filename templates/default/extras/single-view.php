<?php

/**
 * Single View
 *
 * @package PSForum
 * @subpackage Theme
 */

get_header(); ?>

	<?php do_action( 'psf_before_main_content' ); ?>

	<?php do_action( 'psf_template_notices' ); ?>

	<div id="psf-view-<?php psf_view_id(); ?>" class="psf-view">
		<h1 class="entry-title"><?php psf_view_title(); ?></h1>
		<div class="entry-content">

			<?php psf_get_template_part( 'content', 'single-view' ); ?>

		</div>
	</div><!-- #psf-view-<?php psf_view_id(); ?> -->

	<?php do_action( 'psf_after_main_content' ); ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
