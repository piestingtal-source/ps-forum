<?php

/**
 * Template Name: PSForum - User Lost Password
 *
 * @package PSForum
 * @subpackage Theme
 */

// No logged in users
psf_logged_in_redirect();

// Begin Template
get_header(); ?>

	<?php do_action( 'psf_before_main_content' ); ?>

	<?php do_action( 'psf_template_notices' ); ?>

	<?php while ( have_posts() ) : the_post(); ?>

		<div id="psf-lost-pass" class="psf-lost-pass">
			<h1 class="entry-title"><?php the_title(); ?></h1>
			<div class="entry-content">

				<?php the_content(); ?>

				<div id="psforum-forums">

					<?php psf_breadcrumb(); ?>

					<?php psf_get_template_part( 'form', 'user-lost-pass' ); ?>

				</div>
			</div>
		</div><!-- #psf-lost-pass -->

	<?php endwhile; ?>

	<?php do_action( 'psf_after_main_content' ); ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
