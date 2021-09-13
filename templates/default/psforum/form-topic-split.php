<?php

/**
 * Split Topic
 *
 * @package PSForum
 * @subpackage Theme
 */

?>

<div id="psforum-forums">

	<?php psf_breadcrumb(); ?>

	<?php if ( is_user_logged_in() && current_user_can( 'edit_topic', psf_get_topic_id() ) ) : ?>

		<div id="split-topic-<?php psf_topic_id(); ?>" class="psf-topic-split">

			<form id="split_topic" name="split_topic" method="post" action="<?php the_permalink(); ?>">

				<fieldset class="psf-form">

					<legend><?php printf( __( 'Thema "%s" teilen', 'psforum' ), psf_get_topic_title() ); ?></legend>

					<div>

						<div class="psf-template-notice info">
							<p><?php _e( 'Wenn Du ein Thema teilst, teile es beginnend mit der gerade ausgewählten Antwort in zwei Hälften. Wähle diese Antwort als neues Thema mit einem neuen Titel oder füge diese Antworten in ein vorhandenes Thema ein.', 'psforum' ); ?></p>
						</div>

						<div class="psf-template-notice">
							<p><?php _e( 'Wenn Du die vorhandene Themenoption verwendest, werden Antworten innerhalb beider Themen chronologisch zusammengeführt. Die Reihenfolge der zusammengeführten Antworten basiert auf der Zeit und dem Datum, an dem sie veröffentlicht wurden.', 'psforum' ); ?></p>
						</div>

						<fieldset class="psf-form">
							<legend><?php _e( 'Split-Methode', 'psforum' ); ?></legend>

							<div>
								<input name="psf_topic_split_option" id="psf_topic_split_option_reply" type="radio" checked="checked" value="reply" tabindex="<?php psf_tab_index(); ?>" />
								<label for="psf_topic_split_option_reply"><?php printf( __( 'Neues Thema in <strong>%s</strong> mit dem Titel:', 'psforum' ), psf_get_forum_title( psf_get_topic_forum_id( psf_get_topic_id() ) ) ); ?></label>
								<input type="text" id="psf_topic_split_destination_title" value="<?php printf( __( 'Split: %s', 'psforum' ), psf_get_topic_title() ); ?>" tabindex="<?php psf_tab_index(); ?>" size="35" name="psf_topic_split_destination_title" />
							</div>

							<?php if ( psf_has_topics( array( 'show_stickies' => false, 'post_parent' => psf_get_topic_forum_id( psf_get_topic_id() ), 'post__not_in' => array( psf_get_topic_id() ) ) ) ) : ?>

								<div>
									<input name="psf_topic_split_option" id="psf_topic_split_option_existing" type="radio" value="existing" tabindex="<?php psf_tab_index(); ?>" />
									<label for="psf_topic_split_option_existing"><?php _e( 'Verwende ein bestehendes Thema in diesem Forum:', 'psforum' ); ?></label>

									<?php
										psf_dropdown( array(
											'post_type'   => psf_get_topic_post_type(),
											'post_parent' => psf_get_topic_forum_id( psf_get_topic_id() ),
											'selected'    => -1,
											'exclude'     => psf_get_topic_id(),
											'select_id'   => 'psf_destination_topic'
										) );
									?>

								</div>

							<?php endif; ?>

						</fieldset>

						<fieldset class="psf-form">
							<legend><?php _e( 'Themen-Extras', 'psforum' ); ?></legend>

							<div>

								<?php if ( psf_is_subscriptions_active() ) : ?>

									<input name="psf_topic_subscribers" id="psf_topic_subscribers" type="checkbox" value="1" checked="checked" tabindex="<?php psf_tab_index(); ?>" />
									<label for="psf_topic_subscribers"><?php _e( 'Abonnenten in das neue Thema kopieren', 'psforum' ); ?></label><br />

								<?php endif; ?>

								<input name="psf_topic_favoriters" id="psf_topic_favoriters" type="checkbox" value="1" checked="checked" tabindex="<?php psf_tab_index(); ?>" />
								<label for="psf_topic_favoriters"><?php _e( 'Favoriten in das neue Thema kopieren', 'psforum' ); ?></label><br />

								<?php if ( psf_allow_topic_tags() ) : ?>

									<input name="psf_topic_tags" id="psf_topic_tags" type="checkbox" value="1" checked="checked" tabindex="<?php psf_tab_index(); ?>" />
									<label for="psf_topic_tags"><?php _e( 'Themen-Tags in das neue Thema kopieren', 'psforum' ); ?></label><br />

								<?php endif; ?>

							</div>
						</fieldset>

						<div class="psf-template-notice error">
							<p><?php _e( '<strong>WARNUNG:</strong> Dieser Vorgang kann nicht rückgängig gemacht werden.', 'psforum' ); ?></p>
						</div>

						<div class="psf-submit-wrapper">
							<button type="submit" tabindex="<?php psf_tab_index(); ?>" id="psf_merge_topic_submit" name="psf_merge_topic_submit" class="button submit"><?php _e( 'Einreichen', 'psforum' ); ?></button>
						</div>
					</div>

					<?php psf_split_topic_form_fields(); ?>

				</fieldset>
			</form>
		</div>

	<?php else : ?>

		<div id="no-topic-<?php psf_topic_id(); ?>" class="psf-no-topic">
			<div class="entry-content"><?php is_user_logged_in() ? _e( 'Du bist nicht berechtigt, dieses Thema zu bearbeiten!', 'psforum' ) : _e( 'Du kannst dieses Thema nicht bearbeiten.', 'psforum' ); ?></div>
		</div>

	<?php endif; ?>

</div>
