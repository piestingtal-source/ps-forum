<?php

/**
 * Edit Topic Tag
 *
 * @package PSForum
 * @subpackage Theme
 */

?>

<?php if ( current_user_can( 'edit_topic_tags' ) ) : ?>

	<div id="edit-topic-tag-<?php psf_topic_tag_id(); ?>" class="psf-topic-tag-form">

		<fieldset class="psf-form" id="psf-edit-topic-tag">

			<legend><?php printf( __( 'Tag verwalten: "%s"', 'psforum' ), psf_get_topic_tag_name() ); ?></legend>

			<fieldset class="psf-form" id="tag-rename">

				<legend><?php _e( 'Umbenennen', 'psforum' ); ?></legend>

				<div class="psf-template-notice info">
					<p><?php _e( 'Lasse den Slug leer, damit einer automatisch generiert wird.', 'psforum' ); ?></p>
				</div>

				<div class="psf-template-notice">
					<p><?php _e( 'Das Ändern des Slugs wirkt sich auf seinen Permalink aus. Alle Links zum alten Slug funktionieren nicht mehr.', 'psforum' ); ?></p>
				</div>

				<form id="rename_tag" name="rename_tag" method="post" action="<?php the_permalink(); ?>">

					<div>
						<label for="tag-name"><?php _e( 'Name:', 'psforum' ); ?></label>
						<input type="text" id="tag-name" name="tag-name" size="20" maxlength="40" tabindex="<?php psf_tab_index(); ?>" value="<?php echo esc_attr( psf_get_topic_tag_name() ); ?>" />
					</div>

					<div>
						<label for="tag-slug"><?php _e( 'Slug:', 'psforum' ); ?></label>
						<input type="text" id="tag-slug" name="tag-slug" size="20" maxlength="40" tabindex="<?php psf_tab_index(); ?>" value="<?php echo esc_attr( apply_filters( 'editable_slug', psf_get_topic_tag_slug() ) ); ?>" />
					</div>

					<div class="psf-submit-wrapper">
						<button type="submit" tabindex="<?php psf_tab_index(); ?>" class="button submit"><?php esc_attr_e( 'Aktualisieren', 'psforum' ); ?></button>

						<input type="hidden" name="tag-id" value="<?php psf_topic_tag_id(); ?>" />
						<input type="hidden" name="action" value="psf-update-topic-tag" />

						<?php wp_nonce_field( 'update-tag_' . psf_get_topic_tag_id() ); ?>

					</div>
				</form>

			</fieldset>

			<fieldset class="psf-form" id="tag-merge">

				<legend><?php _e( 'Zusammenlegen', 'psforum' ); ?></legend>

				<div class="psf-template-notice">
					<p><?php _e( 'Das Zusammenführen von Tags kann nicht rückgängig gemacht werden.', 'psforum' ); ?></p>
				</div>

				<form id="merge_tag" name="merge_tag" method="post" action="<?php the_permalink(); ?>">

					<div>
						<label for="tag-existing-name"><?php _e( 'Vorhandenes Tag:', 'psforum' ); ?></label>
						<input type="text" id="tag-existing-name" name="tag-existing-name" size="22" tabindex="<?php psf_tab_index(); ?>" maxlength="40" />
					</div>

					<div class="psf-submit-wrapper">
						<button type="submit" tabindex="<?php psf_tab_index(); ?>" class="button submit" onclick="return confirm('<?php echo esc_js( sprintf( __( 'Möchtest Du das %s-Tag wirklich mit dem von Dir angegebenen Tag zusammenführen?', 'psforum' ), psf_get_topic_tag_name() ) ); ?>');"><?php esc_attr_e( 'Verschmelzen', 'psforum' ); ?></button>

						<input type="hidden" name="tag-id" value="<?php psf_topic_tag_id(); ?>" />
						<input type="hidden" name="action" value="psf-merge-topic-tag" />

						<?php wp_nonce_field( 'merge-tag_' . psf_get_topic_tag_id() ); ?>
					</div>
				</form>

			</fieldset>

			<?php if ( current_user_can( 'delete_topic_tags' ) ) : ?>

				<fieldset class="psf-form" id="delete-tag">

					<legend><?php _e( 'Löschen', 'psforum' ); ?></legend>

					<div class="psf-template-notice info">
						<p><?php _e( 'Dadurch werden Deine Themen nicht gelöscht. Nur das Tag selbst wird gelöscht.', 'psforum' ); ?></p>
					</div>
					<div class="psf-template-notice">
						<p><?php _e( 'Das Löschen eines Tags kann nicht rückgängig gemacht werden.', 'psforum' ); ?></p>
						<p><?php _e( 'Alle Links zu diesem Tag funktionieren nicht mehr.', 'psforum' ); ?></p>
					</div>

					<form id="delete_tag" name="delete_tag" method="post" action="<?php the_permalink(); ?>">

						<div class="psf-submit-wrapper">
							<button type="submit" tabindex="<?php psf_tab_index(); ?>" class="button submit" onclick="return confirm('<?php echo esc_js( sprintf( __( 'Möchtest Du das %s-Tag wirklich löschen? Dies ist dauerhaft und kann nicht rückgängig gemacht werden.', 'psforum' ), psf_get_topic_tag_name() ) ); ?>');"><?php esc_attr_e( 'Löschen', 'psforum' ); ?></button>

							<input type="hidden" name="tag-id" value="<?php psf_topic_tag_id(); ?>" />
							<input type="hidden" name="action" value="psf-delete-topic-tag" />

							<?php wp_nonce_field( 'delete-tag_' . psf_get_topic_tag_id() ); ?>
						</div>
					</form>

				</fieldset>

			<?php endif; ?>

		</fieldset>
	</div>

<?php endif; ?>
