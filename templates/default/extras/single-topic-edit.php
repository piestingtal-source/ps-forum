<?php

/**
 * Edit handler for topics
 *
 * @package PSForum
 * @subpackage Theme
 */

get_header(); ?>

	<?php do_action( 'psf_before_main_content' ); ?>

	<?php while ( have_posts() ) : the_post(); ?>

		<div id="psf-edit-page" class="psf-edit-page">
			<h1 class="entry-title"><?php the_title(); ?></h1>
			<div class="entry-content">

				<?php psf_get_template_part( 'form', 'topic' ); ?>

			</div>
		</div><!-- #psf-edit-page -->

	<?php endwhile; ?>

	<?php do_action( 'psf_after_main_content' ); ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
