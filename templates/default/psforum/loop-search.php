<?php

/**
 * Search Loop
 *
 * @package PSForum
 * @subpackage Theme
*/

?>

<?php do_action( 'psf_template_before_search_results_loop' ); ?>

<ul id="psf-search-results" class="forums psf-search-results">

	<li class="psf-header">

		<div class="psf-search-author"><?php  _e( 'Autor',  'psforum' ); ?></div><!-- .psf-reply-author -->

		<div class="psf-search-content">

			<?php _e( 'Suchergebnisse', 'psforum' ); ?>

		</div><!-- .psf-search-content -->

	</li><!-- .psf-header -->

	<li class="psf-body">

		<?php while ( psf_search_results() ) : psf_the_search_result(); ?>

			<?php psf_get_template_part( 'loop', 'search-' . get_post_type() ); ?>

		<?php endwhile; ?>

	</li><!-- .psf-body -->

	<li class="psf-footer">

		<div class="psf-search-author"><?php  _e( 'Autor',  'psforum' ); ?></div>

		<div class="psf-search-content">

			<?php _e( 'Suchergebnisse', 'psforum' ); ?>

		</div><!-- .psf-search-content -->

	</li><!-- .psf-footer -->

</ul><!-- #psf-search-results -->

<?php do_action( 'psf_template_after_search_results_loop' ); ?>