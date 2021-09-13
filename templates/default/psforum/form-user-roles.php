<?php

/**
 * User Roles Profile Edit Part
 *
 * @package PSForum
 * @subpackage Theme
 */

?>

<div>
	<label for="role"><?php _e( 'Blog-Rolle', 'psforum' ) ?></label>

	<?php psf_edit_user_blog_role(); ?>

</div>

<div>
	<label for="forum-role"><?php _e( 'Forumsrolle', 'psforum' ) ?></label>

	<?php psf_edit_user_forums_role(); ?>

</div>
