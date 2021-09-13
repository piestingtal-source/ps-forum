<?php

/**
 * Merge Topic
 *
 * @package PSForum
 * @subpackage Theme
 */

?>

<div id="psforum-forums">

	<?php psf_breadcrumb(); ?>

	<?php if ( is_user_logged_in() && current_user_can( 'edit_topic', psf_get_topic_id() ) ) : ?>

		<div id="merge-topic-<?php psf_topic_id(); ?>" class="psf-topic-merge">

			<form id="merge_topic" name="merge_topic" method="post" action="<?php the_permalink(); ?>">

				<fieldset class="psf-form">

					<legend><?php printf( __( 'Thema "%s" zusammenführen', 'psforum' ), psf_get_topic_title() ); ?></legend>

					<div>

						<div class="psf-template-notice info">
							<p><?php _e( 'Wähle das Thema aus, mit dem dieses zusammengeführt werden soll. Das Zielthema bleibt das Leitthema, und dieses wird in eine Antwort umgewandelt.', 'psforum' ); ?></p>
							<p><?php _e( 'Um dieses Thema als Lead zu behalten, gehe zum anderen Thema und verwende stattdessen das Zusammenführungstool von dort.', 'psforum' ); ?></p>
						</div>

						<div class="psf-template-notice">
							<p><?php _e( 'Alle Antworten innerhalb beider Themen werden chronologisch zusammengeführt. Die Reihenfolge der zusammengeführten Antworten basiert auf der Uhrzeit und dem Datum, an dem sie veröffentlicht wurden. Wenn das Zielthema nach diesem erstellt wurde, wird das Veröffentlichungsdatum auf die Sekunde vor diesem aktualisiert.', 'psforum' ); ?></p>
						</div>

						<fieldset class="psf-form">
							<legend><?php _e( 'Ziel', 'psforum' ); ?></legend>
							<div>
								<?php if ( psf_has_topics( array( 'show_stickies' => false, 'post_parent' => psf_get_topic_forum_id( psf_get_topic_id() ), 'post__not_in' => array( psf_get_topic_id() ) ) ) ) : ?>

									<label for="psf_destination_topic"><?php _e( 'Mit diesem Thema zusammenführen:', 'psforum' ); ?></label>

									<?php
										psf_dropdown( array(
											'post_type'   => psf_get_topic_post_type(),
											'post_parent' => psf_get_topic_forum_id( psf_get_topic_id() ),
											'selected'    => -1,
											'exclude'     => psf_get_topic_id(),
											'select_id'   => 'psf_destination_topic'
										) );
									?>

								<?php else : ?>

									<label><?php _e( 'Es gibt keine anderen Themen in diesem Forum, mit denen man zusammenführen könnte.', 'psforum' ); ?></label>

								<?php endif; ?>

							</div>
						</fieldset>

						<fieldset class="psf-form">
							<legend><?php _e( 'Themen-Extras', 'psforum' ); ?></legend>

							<div>

								<?php if ( psf_is_subscriptions_active() ) : ?>

									<input name="psf_topic_subscribers" id="psf_topic_subscribers" type="checkbox" value="1" checked="checked" tabindex="<?php psf_tab_index(); ?>" />
									<label for="psf_topic_subscribers"><?php _e( 'Themenabonnenten zusammenführen', 'psforum' ); ?></label><br />

								<?php endif; ?>

								<input name="psf_topic_favoriters" id="psf_topic_favoriters" type="checkbox" value="1" checked="checked" tabindex="<?php psf_tab_index(); ?>" />
								<label for="psf_topic_favoriters"><?php _e( 'Themenfavoriten zusammenführen', 'psforum' ); ?></label><br />

								<?php if ( psf_allow_topic_tags() ) : ?>

									<input name="psf_topic_tags" id="psf_topic_tags" type="checkbox" value="1" checked="checked" tabindex="<?php psf_tab_index(); ?>" />
									<label for="psf_topic_tags"><?php _e( 'Themen-Tags zusammenführen', 'psforum' ); ?></label><br />

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

					<?php psf_merge_topic_form_fields(); ?>

				</fieldset>
			</form>
		</div>

	<?php else : ?>

		<div id="no-topic-<?php psf_topic_id(); ?>" class="psf-no-topic">
			<div class="entry-content"><?php is_user_logged_in() ? _e( 'Du bist nicht berechtigt, dieses Thema zu bearbeiten!', 'psforum' ) : _e( 'Du kannst dieses Thema nicht bearbeiten.', 'psforum' ); ?></div>
		</div>

	<?php endif; ?>

</div>
