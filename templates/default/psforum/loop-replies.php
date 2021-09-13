<?php

/**
 * Replies Loop
 *
 * @package PSForum
 * @subpackage Theme
 */

?>

<?php do_action( 'psf_template_before_replies_loop' ); ?>

<ul id="topic-<?php psf_topic_id(); ?>-replies" class="forums psf-replies">

	<li class="psf-header">

		<div class="psf-reply-author"><?php  _e( 'Autor',  'psforum' ); ?></div><!-- .psf-reply-author -->

		<div class="psf-reply-content">

			<?php if ( !psf_show_lead_topic() ) : ?>

				<?php _e( 'Beiträge', 'psforum' ); ?>

				<?php psf_topic_subscription_link(); ?>

				<?php psf_user_favorites_link(); ?>

			<?php else : ?>

				<?php _e( 'Antworten', 'psforum' ); ?>

			<?php endif; ?>

		</div><!-- .psf-reply-content -->

	</li><!-- .psf-header -->

	<li class="psf-body">

		<?php if ( psf_thread_replies() ) : ?>

			<?php psf_list_replies(); ?>

		<?php else : ?>

			<?php while ( psf_replies() ) : psf_the_reply(); ?>

				<?php psf_get_template_part( 'loop', 'single-reply' ); ?>

			<?php endwhile; ?>

		<?php endif; ?>

	</li><!-- .psf-body -->

	<li class="psf-footer">

		<div class="psf-reply-author"><?php  _e( 'Autor',  'psforum' ); ?></div>

		<div class="psf-reply-content">

			<?php if ( !psf_show_lead_topic() ) : ?>

				<?php _e( 'Beiträge', 'psforum' ); ?>

			<?php else : ?>

				<?php _e( 'Antworten', 'psforum' ); ?>

			<?php endif; ?>

		</div><!-- .psf-reply-content -->

	</li><!-- .psf-footer -->

</ul><!-- #topic-<?php psf_topic_id(); ?>-replies -->

<?php do_action( 'psf_template_after_replies_loop' ); ?>
