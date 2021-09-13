<?php

/**
 * Template Name: PSForum - Topics (No Replies)
 *
 * @package PSForum
 * @subpackage Theme
 */

get_header(); ?>

	<?php do_action( 'psf_before_main_content' ); ?>

	<?php do_action( 'psf_template_notices' ); ?>

	<?php while ( have_posts() ) : the_post(); ?>

		<div id="topics-front" class="psf-topics-front">
			<h1 class="entry-title"><?php the_title(); ?></h1>
			<div class="entry-content">

				<?php the_content(); ?>

				<div id="psforum-forums">

					<?php psf_breadcrumb(); ?>

					<?php psf_set_query_name( 'psf_no_replies' ); ?>

					<?php if ( psf_has_topics( array( 'meta_key' => '_psf_reply_count', 'meta_value' => '1', 'meta_compare' => '<', 'orderby' => 'date', 'show_stickies' => false ) ) ) : ?>

						<?php psf_get_template_part( 'pagination', 'topics'    ); ?>

						<?php psf_get_template_part( 'loop',       'topics'    ); ?>

						<?php psf_get_template_part( 'pagination', 'topics'    ); ?>

					<?php else : ?>

						<?php psf_get_template_part( 'feedback',   'no-topics' ); ?>

					<?php endif; ?>

					<?php psf_reset_query_name(); ?>

				</div>
			</div>
		</div><!-- #topics-front -->

	<?php endwhile; ?>

	<?php do_action( 'psf_after_main_content' ); ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
