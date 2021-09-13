<?php

/**
 * User Profile
 *
 * @package PSForum
 * @subpackage Theme
 */

?>

	<?php do_action( 'psf_template_before_user_profile' ); ?>

	<div id="psf-user-profile" class="psf-user-profile">
		<h2 class="entry-title"><?php _e( 'Profil', 'psforum' ); ?></h2>
		<div class="psf-user-section">

			<?php if ( psf_get_displayed_user_field( 'description' ) ) : ?>

				<p class="psf-user-description"><?php psf_displayed_user_field( 'description' ); ?></p>

			<?php endif; ?>

			<p class="psf-user-forum-role"><?php  printf( __( 'Forumsrolle: %s',      'psforum' ), psf_get_user_display_role()    ); ?></p>
			<p class="psf-user-topic-count"><?php printf( __( 'Themen gestartet: %s',  'psforum' ), psf_get_user_topic_count_raw() ); ?></p>
			<p class="psf-user-reply-count"><?php printf( __( 'Antworten erstellt: %s', 'psforum' ), psf_get_user_reply_count_raw() ); ?></p>
		</div>
	</div><!-- #psf-author-topics-started -->

	<?php do_action( 'psf_template_after_user_profile' ); ?>
