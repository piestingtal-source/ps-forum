<?php

/**
 * Template Name: PSForum - Topic Tags
 *
 * @package PSForum
 * @subpackage Theme
 */

get_header(); ?>

	<?php do_action( 'psf_before_main_content' ); ?>

	<?php do_action( 'psf_template_notices' ); ?>

	<?php while ( have_posts() ) : the_post(); ?>

		<div id="psf-topic-tags" class="psf-topic-tags">
			<h1 class="entry-title"><?php the_title(); ?></h1>
			<div class="entry-content">

				<?php get_the_content() ? the_content() : _e( '<p>Dies ist eine Sammlung von Tags, die derzeit in unseren Foren beliebt sind.</p>', 'psforum' ); ?>

				<div id="psforum-forums">

					<?php psf_breadcrumb(); ?>

					<div id="psf-topic-hot-tags">

						<?php wp_tag_cloud( array( 'smallest' => 9, 'largest' => 38, 'number' => 80, 'taxonomy' => psf_get_topic_tag_tax_id() ) ); ?>

					</div>
				</div>
			</div>
		</div><!-- #psf-topic-tags -->

	<?php endwhile; ?>

	<?php do_action( 'psf_after_main_content' ); ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
