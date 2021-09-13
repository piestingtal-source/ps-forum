<?php

/**
 * Topic Tag Edit Content Part
 *
 * @package PSForum
 * @subpackage Theme
 */

?>

<div id="psforum-forums">

	<?php psf_breadcrumb(); ?>

	<?php psf_topic_tag_description(); ?>

	<?php do_action( 'psf_template_before_topic_tag_edit' ); ?>

	<?php psf_get_template_part( 'form', 'topic-tag' ); ?>

	<?php do_action( 'psf_template_after_topic_tag_edit' ); ?>

</div>
