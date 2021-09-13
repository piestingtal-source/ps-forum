<?php

/**
 * New/Edit Topic
 *
 * @package PSForum
 * @subpackage Theme
 */

?>

<?php if ( !psf_is_single_forum() ) : ?>

<div id="psforum-forums">

	<?php psf_breadcrumb(); ?>

<?php endif; ?>

<?php if ( psf_is_topic_edit() ) : ?>

	<?php psf_topic_tag_list( psf_get_topic_id() ); ?>

	<?php psf_single_topic_description( array( 'topic_id' => psf_get_topic_id() ) ); ?>

<?php endif; ?>

<?php if ( psf_current_user_can_access_create_topic_form() ) : ?>

	<div id="new-topic-<?php psf_topic_id(); ?>" class="psf-topic-form">

		<form id="new-post" name="new-post" method="post" action="<?php the_permalink(); ?>">

			<?php do_action( 'psf_theme_before_topic_form' ); ?>

			<fieldset class="psf-form">
				<legend>

					<?php
						if ( psf_is_topic_edit() )
							printf( __( 'Jetzt bearbeiten &ldquo;%s&rdquo;', 'psforum' ), psf_get_topic_title() );
						else
							psf_is_single_forum() ? printf( __( 'Neues Thema erstellen in &ldquo;%s&rdquo;', 'psforum' ), psf_get_forum_title() ) : _e( 'Neues Thema erstellen', 'psforum' );
					?>

				</legend>

				<?php do_action( 'psf_theme_before_topic_form_notices' ); ?>

				<?php if ( !psf_is_topic_edit() && psf_is_forum_closed() ) : ?>

					<div class="psf-template-notice">
						<p><?php _e( 'Dieses Forum ist als geschlossen für neue Themen markiert, Deine Posting-Möglichkeiten erlauben Dir dies jedoch weiterhin.', 'psforum' ); ?></p>
					</div>

				<?php endif; ?>

				<?php if ( current_user_can( 'unfiltered_html' ) ) : ?>

					<div class="psf-template-notice">
						<p><?php _e( 'Dein Konto hat die Möglichkeit, uneingeschränkte HTML-Inhalte zu posten.', 'psforum' ); ?></p>
					</div>

				<?php endif; ?>

				<?php do_action( 'psf_template_notices' ); ?>

				<div>

					<?php psf_get_template_part( 'form', 'anonymous' ); ?>

					<?php do_action( 'psf_theme_before_topic_form_title' ); ?>

					<p>
						<label for="psf_topic_title"><?php printf( __( 'Thementitel (Maximale Länge: %d):', 'psforum' ), psf_get_title_max_length() ); ?></label><br />
						<input type="text" id="psf_topic_title" value="<?php psf_form_topic_title(); ?>" tabindex="<?php psf_tab_index(); ?>" size="40" name="psf_topic_title" maxlength="<?php psf_title_max_length(); ?>" />
					</p>

					<?php do_action( 'psf_theme_after_topic_form_title' ); ?>

					<?php do_action( 'psf_theme_before_topic_form_content' ); ?>

					<?php psf_the_content( array( 'context' => 'topic' ) ); ?>

					<?php do_action( 'psf_theme_after_topic_form_content' ); ?>

					<?php if ( ! ( psf_use_wp_editor() || current_user_can( 'unfiltered_html' ) ) ) : ?>

						<p class="form-allowed-tags">
							<label><?php _e( 'Du kannst diese <abbr title="HyperText Markup Language">HTML</abbr>-Tags und -Attribute verwenden:','psforum' ); ?></label><br />
							<code><?php psf_allowed_tags(); ?></code>
						</p>

					<?php endif; ?>

					<?php if ( psf_allow_topic_tags() && current_user_can( 'assign_topic_tags' ) ) : ?>

						<?php do_action( 'psf_theme_before_topic_form_tags' ); ?>

						<p>
							<label for="psf_topic_tags"><?php _e( 'Topic Tags:', 'psforum' ); ?></label><br />
							<input type="text" value="<?php psf_form_topic_tags(); ?>" tabindex="<?php psf_tab_index(); ?>" size="40" name="psf_topic_tags" id="psf_topic_tags" <?php disabled( psf_is_topic_spam() ); ?> />
						</p>

						<?php do_action( 'psf_theme_after_topic_form_tags' ); ?>

					<?php endif; ?>

					<?php if ( !psf_is_single_forum() ) : ?>

						<?php do_action( 'psf_theme_before_topic_form_forum' ); ?>

						<p>
							<label for="psf_forum_id"><?php _e( 'Forum:', 'psforum' ); ?></label><br />
							<?php
								psf_dropdown( array(
									'show_none' => __( '(Kein Forum)', 'psforum' ),
									'selected'  => psf_get_form_topic_forum()
								) );
							?>
						</p>

						<?php do_action( 'psf_theme_after_topic_form_forum' ); ?>

					<?php endif; ?>

					<?php if ( current_user_can( 'moderate' ) ) : ?>

						<?php do_action( 'psf_theme_before_topic_form_type' ); ?>

						<p>

							<label for="psf_stick_topic"><?php _e( 'Thementyp:', 'psforum' ); ?></label><br />

							<?php psf_form_topic_type_dropdown(); ?>

						</p>

						<?php do_action( 'psf_theme_after_topic_form_type' ); ?>

						<?php do_action( 'psf_theme_before_topic_form_status' ); ?>

						<p>

							<label for="psf_topic_status"><?php _e( 'Themenstatus:', 'psforum' ); ?></label><br />

							<?php psf_form_topic_status_dropdown(); ?>

						</p>

						<?php do_action( 'psf_theme_after_topic_form_status' ); ?>

					<?php endif; ?>

					<?php if ( psf_is_subscriptions_active() && !psf_is_anonymous() && ( !psf_is_topic_edit() || ( psf_is_topic_edit() && !psf_is_topic_anonymous() ) ) ) : ?>

						<?php do_action( 'psf_theme_before_topic_form_subscriptions' ); ?>

						<p>
							<input name="psf_topic_subscription" id="psf_topic_subscription" type="checkbox" value="psf_subscribe" <?php psf_form_topic_subscribed(); ?> tabindex="<?php psf_tab_index(); ?>" />

							<?php if ( psf_is_topic_edit() && ( psf_get_topic_author_id() !== psf_get_current_user_id() ) ) : ?>

								<label for="psf_topic_subscription"><?php _e( 'Benachrichtige den Autor von Folgeantworten per E-Mail', 'psforum' ); ?></label>

							<?php else : ?>

								<label for="psf_topic_subscription"><?php _e( 'Benachrichtige mich über Folgeantworten per E-Mail', 'psforum' ); ?></label>

							<?php endif; ?>
						</p>

						<?php do_action( 'psf_theme_after_topic_form_subscriptions' ); ?>

					<?php endif; ?>

					<?php if ( psf_allow_revisions() && psf_is_topic_edit() ) : ?>

						<?php do_action( 'psf_theme_before_topic_form_revisions' ); ?>

						<fieldset class="psf-form">
							<legend>
								<input name="psf_log_topic_edit" id="psf_log_topic_edit" type="checkbox" value="1" <?php psf_form_topic_log_edit(); ?> tabindex="<?php psf_tab_index(); ?>" />
								<label for="psf_log_topic_edit"><?php _e( 'Führe ein Protokoll dieser Änderung:', 'psforum' ); ?></label><br />
							</legend>

							<div>
								<label for="psf_topic_edit_reason"><?php printf( __( 'Optionaler Bearbeitungsgrund:', 'psforum' ), psf_get_current_user_name() ); ?></label><br />
								<input type="text" value="<?php psf_form_topic_edit_reason(); ?>" tabindex="<?php psf_tab_index(); ?>" size="40" name="psf_topic_edit_reason" id="psf_topic_edit_reason" />
							</div>
						</fieldset>

						<?php do_action( 'psf_theme_after_topic_form_revisions' ); ?>

					<?php endif; ?>

					<?php do_action( 'psf_theme_before_topic_form_submit_wrapper' ); ?>

					<div class="psf-submit-wrapper">

						<?php do_action( 'psf_theme_before_topic_form_submit_button' ); ?>

						<button type="submit" tabindex="<?php psf_tab_index(); ?>" id="psf_topic_submit" name="psf_topic_submit" class="button submit"><?php _e( 'Einreichen', 'psforum' ); ?></button>

						<?php do_action( 'psf_theme_after_topic_form_submit_button' ); ?>

					</div>

					<?php do_action( 'psf_theme_after_topic_form_submit_wrapper' ); ?>

				</div>

				<?php psf_topic_form_fields(); ?>

			</fieldset>

			<?php do_action( 'psf_theme_after_topic_form' ); ?>

		</form>
	</div>

<?php elseif ( psf_is_forum_closed() ) : ?>

	<div id="no-topic-<?php psf_topic_id(); ?>" class="psf-no-topic">
		<div class="psf-template-notice">
			<p><?php printf( __( 'Das Forum &#8216;%s&#8217; ist für neue Themen und Antworten geschlossen.', 'psforum' ), psf_get_forum_title() ); ?></p>
		</div>
	</div>

<?php else : ?>

	<div id="no-topic-<?php psf_topic_id(); ?>" class="psf-no-topic">
		<div class="psf-template-notice">
			<p><?php is_user_logged_in() ? _e( 'Du kannst keine neuen Themen erstellen.', 'psforum' ) : _e( 'Du musst angemeldet sein, um neue Themen zu erstellen.', 'psforum' ); ?></p>
		</div>
	</div>

<?php endif; ?>

<?php if ( !psf_is_single_forum() ) : ?>

</div>

<?php endif; ?>
