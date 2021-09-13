<?php

/**
 * Template Name: PSForum - Statistics
 *
 * @package PSForum
 * @subpackage Theme
 */

get_header(); ?>

	<?php do_action( 'psf_before_main_content' ); ?>

	<?php do_action( 'psf_template_notices' ); ?>

	<?php while ( have_posts() ) : the_post(); ?>

		<div id="psf-statistics" class="psf-statistics">
			<h1 class="entry-title"><?php the_title(); ?></h1>
			<div class="entry-content">

				<?php get_the_content() ? the_content() : _e( '<p>Hier sind die Statistiken und beliebte Themen unserer Foren.</p>', 'psforum' ); ?>

				<div id="psforum-forums">

					<?php psf_get_template_part( 'content', 'statistics' ); ?>

					<?php do_action( 'psf_before_popular_topics' ); ?>

					<?php psf_set_query_name( 'psf_popular_topics' ); ?>

					<?php if ( psf_view_query( 'popular' ) ) : ?>

						<h2 class="entry-title"><?php _e( 'Beliebte Themen', 'psforum' ); ?></h2>

						<?php psf_get_template_part( 'pagination', 'topics' ); ?>

						<?php psf_get_template_part( 'loop',       'topics' ); ?>

						<?php psf_get_template_part( 'pagination', 'topics' ); ?>

					<?php endif; ?>

					<?php psf_reset_query_name(); ?>

					<?php do_action( 'psf_after_popular_topics' ); ?>

				</div>
			</div>
		</div><!-- #psf-statistics -->

	<?php endwhile; ?>

	<?php do_action( 'psf_after_main_content' ); ?>

<?php get_sidebar(); ?>

<?php get_footer();