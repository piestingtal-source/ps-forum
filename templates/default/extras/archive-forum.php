<?php

/**
 * PSForum - Forum Archive
 *
 * @package PSForum
 * @subpackage Theme
 */

get_header(); ?>

	<?php do_action( 'psf_before_main_content' ); ?>

	<?php do_action( 'psf_template_notices' ); ?>

	<div id="forum-front" class="psf-forum-front">
		<h1 class="entry-title"><?php psf_forum_archive_title(); ?></h1>
		<div class="entry-content">

			<?php psf_get_template_part( 'content', 'archive-forum' ); ?>

		</div>
	</div><!-- #forum-front -->

	<?php do_action( 'psf_after_main_content' ); ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
