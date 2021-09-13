<?php

/**
 * Single Reply
 *
 * @package PSForum
 * @subpackage Theme
 */

get_header(); ?>

	<?php do_action( 'psf_before_main_content' ); ?>

	<?php do_action( 'psf_template_notices' ); ?>

	<?php if ( psf_user_can_view_forum( array( 'forum_id' => psf_get_reply_forum_id() ) ) ) : ?>

		<?php while ( have_posts() ) : the_post(); ?>

			<div id="psf-reply-wrapper-<?php psf_reply_id(); ?>" class="psf-reply-wrapper">
				<h1 class="entry-title"><?php psf_reply_title(); ?></h1>
				<div class="entry-content">

					<?php psf_get_template_part( 'content', 'single-reply' ); ?>

				</div><!-- .entry-content -->
			</div><!-- #psf-reply-wrapper-<?php psf_reply_id(); ?> -->

		<?php endwhile; ?>

	<?php elseif ( psf_is_forum_private( psf_get_reply_forum_id(), false ) ) : ?>

		<?php psf_get_template_part( 'feedback', 'no-access' ); ?>

	<?php endif; ?>

	<?php do_action( 'psf_after_main_content' ); ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
