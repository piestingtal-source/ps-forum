<?php

/**
 * Single Topic
 *
 * @package PSForum
 * @subpackage Theme
 */

get_header(); ?>

	<?php do_action( 'psf_before_main_content' ); ?>

	<?php do_action( 'psf_template_notices' ); ?>

	<?php if ( psf_user_can_view_forum( array( 'forum_id' => psf_get_topic_forum_id() ) ) ) : ?>

		<?php while ( have_posts() ) : the_post(); ?>

			<div id="psf-topic-wrapper-<?php psf_topic_id(); ?>" class="psf-topic-wrapper">
				<h1 class="entry-title"><?php psf_topic_title(); ?></h1>
				<div class="entry-content">

					<?php psf_get_template_part( 'content', 'single-topic' ); ?>

				</div>
			</div><!-- #psf-topic-wrapper-<?php psf_topic_id(); ?> -->

		<?php endwhile; ?>

	<?php elseif ( psf_is_forum_private( psf_get_topic_forum_id(), false ) ) : ?>

		<?php psf_get_template_part( 'feedback', 'no-access' ); ?>

	<?php endif; ?>

	<?php do_action( 'psf_after_main_content' ); ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
