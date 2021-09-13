<?php

/**
 * Archive Topic Content Part
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

	<?php if ( psf_is_topic_tag() ) psf_topic_tag_description(); ?>

	<?php do_action( 'psf_template_before_topics_index' ); ?>

	<?php if ( psf_has_topics() ) : ?>

		<?php psf_get_template_part( 'pagination', 'topics'    ); ?>

		<?php psf_get_template_part( 'loop',       'topics'    ); ?>

		<?php psf_get_template_part( 'pagination', 'topics'    ); ?>

	<?php else : ?>

		<?php psf_get_template_part( 'feedback',   'no-topics' ); ?>

	<?php endif; ?>

	<?php do_action( 'psf_template_after_topics_index' ); ?>

</div>
