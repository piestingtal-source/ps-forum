<?php

/**
 * Single Forum
 *
 * @package PSForum
 * @subpackage Theme
 */

get_header(); ?>

	<?php do_action( 'psf_before_main_content' ); ?>

	<?php do_action( 'psf_template_notices' ); ?>

	<?php while ( have_posts() ) : the_post(); ?>

		<?php if ( psf_user_can_view_forum() ) : ?>

			<div id="forum-<?php psf_forum_id(); ?>" class="psf-forum-content">
				<h1 class="entry-title"><?php psf_forum_title(); ?></h1>
				<div class="entry-content">

					<?php psf_get_template_part( 'content', 'single-forum' ); ?>

				</div>
			</div><!-- #forum-<?php psf_forum_id(); ?> -->

		<?php else : // Forum exists, user no access ?>

			<?php psf_get_template_part( 'feedback', 'no-access' ); ?>

		<?php endif; ?>

	<?php endwhile; ?>

	<?php do_action( 'psf_after_main_content' ); ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
