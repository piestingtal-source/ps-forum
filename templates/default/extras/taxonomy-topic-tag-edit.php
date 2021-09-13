<?php

/**
 * Topic Tag Edit
 *
 * @package PSForum
 * @subpackage Theme
 */

get_header(); ?>

	<?php do_action( 'psf_before_main_content' ); ?>

	<?php do_action( 'psf_template_notices' ); ?>

	<div id="topic-tag" class="psf-topic-tag">
		<h1 class="entry-title"><?php printf( __( 'Themen-Tag: %s', 'psforum' ), '<span>' . psf_get_topic_tag_name() . '</span>' ); ?></h1>

		<div class="entry-content">

			<?php psf_get_template_part( 'content', 'topic-tag-edit' ); ?>

		</div>
	</div><!-- #topic-tag -->

	<?php do_action( 'psf_after_main_content' ); ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
