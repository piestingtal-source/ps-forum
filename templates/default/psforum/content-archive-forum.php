<?php

/**
 * Archive Forum Content Part
 *
 * @package PSForum
 * @subpackage Theme
 */

?>

<div id="psforum-forums">

	<?php if ( psf_allow_search() ) : ?>

		<div class="psf-search-form">

			<?php psf_get_template_part( 'form', 'search' ); ?>

		</div>

	<?php endif; ?>

	<?php psf_breadcrumb(); ?>

	<?php psf_forum_subscription_link(); ?>

	<?php do_action( 'psf_template_before_forums_index' ); ?>

	<?php if ( psf_has_forums() ) : ?>

		<?php psf_get_template_part( 'loop',     'forums'    ); ?>

	<?php else : ?>

		<?php psf_get_template_part( 'feedback', 'no-forums' ); ?>

	<?php endif; ?>

	<?php do_action( 'psf_template_after_forums_index' ); ?>

</div>
