<?php

/**
 * PSForum Admin Settings
 *
 * @package PSForum
 * @subpackage Administration
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/** Sections ******************************************************************/

/**
 * Get the Forums settings sections.
 *
 * @since PSForum (r4001)
 * @return array
 */
function psf_admin_get_settings_sections() {
	return (array) apply_filters( 'psf_admin_get_settings_sections', array(
		'psf_settings_users' => array(
			'title'    => __( 'Forum Benutzereinstellungen', 'psforum' ),
			'callback' => 'psf_admin_setting_callback_user_section',
			'page'     => 'discussion'
		),
		'psf_settings_features' => array(
			'title'    => __( 'Forumsfunktionen', 'psforum' ),
			'callback' => 'psf_admin_setting_callback_features_section',
			'page'     => 'discussion'
		),
		'psf_settings_theme_compat' => array(
			'title'    => __( 'Forum-Themenpakete', 'psforum' ),
			'callback' => 'psf_admin_setting_callback_subtheme_section',
			'page'     => 'general'
		),
		'psf_settings_per_page' => array(
			'title'    => __( 'Themen und Antworten pro Seite', 'psforum' ),
			'callback' => 'psf_admin_setting_callback_per_page_section',
			'page'     => 'reading'
		),
		'psf_settings_per_rss_page' => array(
			'title'    => __( 'Themen und Antworten pro RSS-Seite', 'psforum' ),
			'callback' => 'psf_admin_setting_callback_per_rss_page_section',
			'page'     => 'reading',
		),
		'psf_settings_root_slugs' => array(
			'title'    => __( 'Forum Wurzel Slug', 'psforum' ),
			'callback' => 'psf_admin_setting_callback_root_slug_section',
			'page'     => 'permalink'
		),
		'psf_settings_single_slugs' => array(
			'title'    => __( 'Einzelne Forum Slugs', 'psforum' ),
			'callback' => 'psf_admin_setting_callback_single_slug_section',
			'page'     => 'permalink',
		),
		'psf_settings_user_slugs' => array(
			'title'    => __( 'Forum Benutzer Slugs', 'psforum' ),
			'callback' => 'psf_admin_setting_callback_user_slug_section',
			'page'     => 'permalink',
		),
		'psf_settings_buddypress' => array(
			'title'    => __( 'BuddyPress Integration', 'psforum' ),
			'callback' => 'psf_admin_setting_callback_buddypress_section',
			'page'     => 'buddypress',
		),
		'psf_settings_akismet' => array(
			'title'    => __( 'Akismet Integration', 'psforum' ),
			'callback' => 'psf_admin_setting_callback_akismet_section',
			'page'     => 'discussion'
		)
	) );
}

/**
 * Get all of the settings fields.
 *
 * @since PSForum (r4001)
 * @return type
 */
function psf_admin_get_settings_fields() {
	return (array) apply_filters( 'psf_admin_get_settings_fields', array(

		/** User Section ******************************************************/

		'psf_settings_users' => array(

			// Edit lock setting
			'_psf_edit_lock' => array(
				'title'             => __( 'Bearbeiten verbieten nach', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_editlock',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Throttle setting
			'_psf_throttle_time' => array(
				'title'             => __( 'Drossle Posten alle', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_throttle',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Allow anonymous posting setting
			'_psf_allow_anonymous' => array(
				'title'             => __( 'Anonymes Posten', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_anonymous',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Allow global access (on multisite)
			'_psf_allow_global_access' => array(
				'title'             => __( 'Automatische Rolle', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_global_access',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Allow global access (on multisite)
			'_psf_default_role' => array(
				'sanitize_callback' => 'sanitize_text_field',
				'args'              => array()
			)
		),

		/** Features Section **************************************************/

		'psf_settings_features' => array(

			// Allow topic and reply revisions
			'_psf_allow_revisions' => array(
				'title'             => __( 'Überarbeitungen', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_revisions',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Allow favorites setting
			'_psf_enable_favorites' => array(
				'title'             => __( 'Favoriten', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_favorites',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Allow subscriptions setting
			'_psf_enable_subscriptions' => array(
				'title'             => __( 'Abonnements', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_subscriptions',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Allow topic tags
			'_psf_allow_topic_tags' => array(
				'title'             => __( 'Themen Tags', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_topic_tags',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Allow topic tags
			'_psf_allow_search' => array(
				'title'             => __( 'Suche', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_search',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Allow fancy editor setting
			'_psf_use_wp_editor' => array(
				'title'             => __( 'Post Formatierung', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_use_wp_editor',
				'args'              => array(),
				'sanitize_callback' => 'intval'
			),

			// Allow auto embedding setting
			'_psf_use_autoembed' => array(
				'title'             => __( 'Links automatisch einbetten', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_use_autoembed',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Set reply threading level
			'_psf_thread_replies_depth' => array(
				'title'             => __( 'Antworten Verschachteln', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_thread_replies_depth',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Allow threaded replies
			'_psf_allow_threaded_replies' => array(
				'sanitize_callback' => 'intval',
				'args'              => array()
			)
		),

		/** Theme Packages ****************************************************/

		'psf_settings_theme_compat' => array(

			// Theme package setting
			'_psf_theme_package_id' => array(
				'title'             => __( 'Aktuelles Paket', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_subtheme_id',
				'sanitize_callback' => 'esc_sql',
				'args'              => array()
			)
		),

		/** Per Page Section **************************************************/

		'psf_settings_per_page' => array(

			// Replies per page setting
			'_psf_topics_per_page' => array(
				'title'             => __( 'Themen', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_topics_per_page',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Replies per page setting
			'_psf_replies_per_page' => array(
				'title'             => __( 'Antworten', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_replies_per_page',
				'sanitize_callback' => 'intval',
				'args'              => array()
			)
		),

		/** Per RSS Page Section **********************************************/

		'psf_settings_per_rss_page' => array(

			// Replies per page setting
			'_psf_topics_per_rss_page' => array(
				'title'             => __( 'Themen', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_topics_per_rss_page',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Replies per page setting
			'_psf_replies_per_rss_page' => array(
				'title'             => __( 'Antworten', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_replies_per_rss_page',
				'sanitize_callback' => 'intval',
				'args'              => array()
			)
		),

		/** Front Slugs *******************************************************/

		'psf_settings_root_slugs' => array(

			// Root slug setting
			'_psf_root_slug' => array(
				'title'             => __( 'Forumsstamm', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_root_slug',
				'sanitize_callback' => 'psf_sanitize_slug',
				'args'              => array()
			),

			// Include root setting
			'_psf_include_root' => array(
				'title'             => __( 'Forumspräfix', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_include_root',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// What to show on Forum Root
			'_psf_show_on_root' => array(
				'title'             => __( 'Forumsstamm sollte angezeigt werden', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_show_on_root',
				'sanitize_callback' => 'sanitize_text_field',
				'args'              => array()
			),
		),

		/** Single Slugs ******************************************************/

		'psf_settings_single_slugs' => array(

			// Forum slug setting
			'_psf_forum_slug' => array(
				'title'             => __( 'Forum', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_forum_slug',
				'sanitize_callback' => 'psf_sanitize_slug',
				'args'              => array()
			),

			// Topic slug setting
			'_psf_topic_slug' => array(
				'title'             => __( 'Thema', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_topic_slug',
				'sanitize_callback' => 'psf_sanitize_slug',
				'args'              => array()
			),

			// Topic tag slug setting
			'_psf_topic_tag_slug' => array(
				'title'             => __( 'Themen Tag', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_topic_tag_slug',
				'sanitize_callback' => 'psf_sanitize_slug',
				'args'              => array()
			),

			// View slug setting
			'_psf_view_slug' => array(
				'title'             => __( 'Themenansicht', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_view_slug',
				'sanitize_callback' => 'psf_sanitize_slug',
				'args'              => array()
			),

			// Reply slug setting
			'_psf_reply_slug' => array(
				'title'             => __( 'Antwort', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_reply_slug',
				'sanitize_callback' => 'psf_sanitize_slug',
				'args'              => array()
			),

			// Search slug setting
			'_psf_search_slug' => array(
				'title'             => __( 'Suche', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_search_slug',
				'sanitize_callback' => 'psf_sanitize_slug',
				'args'              => array()
			)
		),

		/** User Slugs ********************************************************/

		'psf_settings_user_slugs' => array(

			// User slug setting
			'_psf_user_slug' => array(
				'title'             => __( 'Nutzerbasis', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_user_slug',
				'sanitize_callback' => 'psf_sanitize_slug',
				'args'              => array()
			),

			// Topics slug setting
			'_psf_topic_archive_slug' => array(
				'title'             => __( 'Themen gestartet', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_topic_archive_slug',
				'sanitize_callback' => 'psf_sanitize_slug',
				'args'              => array()
			),

			// Replies slug setting
			'_psf_reply_archive_slug' => array(
				'title'             => __( 'Antworten erstellt', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_reply_archive_slug',
				'sanitize_callback' => 'psf_sanitize_slug',
				'args'              => array()
			),

			// Favorites slug setting
			'_psf_user_favs_slug' => array(
				'title'             => __( 'Lieblingsthemen', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_user_favs_slug',
				'sanitize_callback' => 'psf_sanitize_slug',
				'args'              => array()
			),

			// Subscriptions slug setting
			'_psf_user_subs_slug' => array(
				'title'             => __( 'Themen Abonnements', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_user_subs_slug',
				'sanitize_callback' => 'psf_sanitize_slug',
				'args'              => array()
			)
		),

		/** BuddyPress ********************************************************/

		'psf_settings_buddypress' => array(

			// Are group forums enabled?
			'_psf_enable_group_forums' => array(
				'title'             => __( 'Gruppenforen aktivieren', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_group_forums',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Group forums parent forum ID
			'_psf_group_forums_root_id' => array(
				'title'             => __( 'Gruppenforen Eltern', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_group_forums_root_id',
				'sanitize_callback' => 'intval',
				'args'              => array()
			)
		),

		/** Akismet ***********************************************************/

		'psf_settings_akismet' => array(

			// Should we use Akismet
			'_psf_enable_akismet' => array(
				'title'             => __( 'Akismet verwenden', 'psforum' ),
				'callback'          => 'psf_admin_setting_callback_akismet',
				'sanitize_callback' => 'intval',
				'args'              => array()
			)
		)
	) );
}

/**
 * Get settings fields by section.
 *
 * @since PSForum (r4001)
 * @param string $section_id
 * @return mixed False if section is invalid, array of fields otherwise.
 */
function psf_admin_get_settings_fields_for_section( $section_id = '' ) {

	// Bail if section is empty
	if ( empty( $section_id ) )
		return false;

	$fields = psf_admin_get_settings_fields();
	$retval = isset( $fields[$section_id] ) ? $fields[$section_id] : false;

	return (array) apply_filters( 'psf_admin_get_settings_fields_for_section', $retval, $section_id );
}

/** User Section **************************************************************/

/**
 * User settings section description for the settings page
 *
 * @since PSForum (r2786)
 */
function psf_admin_setting_callback_user_section() {
?>

	<p><?php esc_html_e( 'Festlegen von Zeitlimits und anderen Funktionen zum Posten von Benutzern', 'psforum' ); ?></p>

<?php
}


/**
 * Edit lock setting field
 *
 * @since PSForum (r2737)
 *
 * @uses psf_form_option() To output the option value
 */
function psf_admin_setting_callback_editlock() {
?>

	<input name="_psf_edit_lock" id="_psf_edit_lock" type="number" min="0" step="1" value="<?php psf_form_option( '_psf_edit_lock', '5' ); ?>" class="small-text"<?php psf_maybe_admin_setting_disabled( '_psf_edit_lock' ); ?> />
	<label for="_psf_edit_lock"><?php esc_html_e( 'Minuten', 'psforum' ); ?></label>

<?php
}

/**
 * Throttle setting field
 *
 * @since PSForum (r2737)
 *
 * @uses psf_form_option() To output the option value
 */
function psf_admin_setting_callback_throttle() {
?>

	<input name="_psf_throttle_time" id="_psf_throttle_time" type="number" min="0" step="1" value="<?php psf_form_option( '_psf_throttle_time', '10' ); ?>" class="small-text"<?php psf_maybe_admin_setting_disabled( '_psf_throttle_time' ); ?> />
	<label for="_psf_throttle_time"><?php esc_html_e( 'Sekunden', 'psforum' ); ?></label>

<?php
}

/**
 * Allow anonymous posting setting field
 *
 * @since PSForum (r2737)
 *
 * @uses checked() To display the checked attribute
 */
function psf_admin_setting_callback_anonymous() {
?>

	<input name="_psf_allow_anonymous" id="_psf_allow_anonymous" type="checkbox" value="1" <?php checked( psf_allow_anonymous( false ) ); psf_maybe_admin_setting_disabled( '_psf_allow_anonymous' ); ?> />
	<label for="_psf_allow_anonymous"><?php esc_html_e( 'Erlaube Gastbenutzern ohne Konten, Themen und Antworten zu erstellen', 'psforum' ); ?></label>

<?php
}

/**
 * Allow global access setting field
 *
 * @since PSForum (r3378)
 *
 * @uses checked() To display the checked attribute
 */
function psf_admin_setting_callback_global_access() {

	// Get the default role once rather than loop repeatedly below
	$default_role = psf_get_default_role();

	// Start the output buffer for the select dropdown
	ob_start(); ?>

	</label>
	<label for="_psf_default_role">
		<select name="_psf_default_role" id="_psf_default_role" <?php psf_maybe_admin_setting_disabled( '_psf_default_role' ); ?>>
		<?php foreach ( psf_get_dynamic_roles() as $role => $details ) : ?>

			<option <?php selected( $default_role, $role ); ?> value="<?php echo esc_attr( $role ); ?>"><?php echo psf_translate_user_role( $details['name'] ); ?></option>

		<?php endforeach; ?>
		</select>

	<?php $select = ob_get_clean(); ?>

	<label for="_psf_allow_global_access">
		<input name="_psf_allow_global_access" id="_psf_allow_global_access" type="checkbox" value="1" <?php checked( psf_allow_global_access( true ) ); psf_maybe_admin_setting_disabled( '_psf_allow_global_access' ); ?> />
		<?php printf( esc_html__( 'Gib registrierten Besuchern automatisch die Forumsrolle %s', 'psforum' ), $select ); ?>
	</label>

<?php
}

/** Features Section **********************************************************/

/**
 * Features settings section description for the settings page
 *
 * @since PSForum (r2786)
 */
function psf_admin_setting_callback_features_section() {
?>

	<p><?php esc_html_e( 'Forenfunktionen, die ein- und ausgeschaltet werden können', 'psforum' ); ?></p>

<?php
}

/**
 * Allow favorites setting field
 *
 * @since PSForum (r2786)
 *
 * @uses checked() To display the checked attribute
 */
function psf_admin_setting_callback_favorites() {
?>

	<input name="_psf_enable_favorites" id="_psf_enable_favorites" type="checkbox" value="1" <?php checked( psf_is_favorites_active( true ) ); psf_maybe_admin_setting_disabled( '_psf_enable_favorites' ); ?> />
	<label for="_psf_enable_favorites"><?php esc_html_e( 'Nutzern erlauben, Themen als Favoriten zu markieren', 'psforum' ); ?></label>

<?php
}

/**
 * Allow subscriptions setting field
 *
 * @since PSForum (r2737)
 *
 * @uses checked() To display the checked attribute
 */
function psf_admin_setting_callback_subscriptions() {
?>

	<input name="_psf_enable_subscriptions" id="_psf_enable_subscriptions" type="checkbox" value="1" <?php checked( psf_is_subscriptions_active( true ) ); psf_maybe_admin_setting_disabled( '_psf_enable_subscriptions' ); ?> />
	<label for="_psf_enable_subscriptions"><?php esc_html_e( 'Nutzern erlauben, Foren und Themen zu abonnieren', 'psforum' ); ?></label>

<?php
}

/**
 * Allow topic tags setting field
 *
 * @since PSForum (r4944)
 *
 * @uses checked() To display the checked attribute
 */
function psf_admin_setting_callback_topic_tags() {
?>

	<input name="_psf_allow_topic_tags" id="_psf_allow_topic_tags" type="checkbox" value="1" <?php checked( psf_allow_topic_tags( true ) ); psf_maybe_admin_setting_disabled( '_psf_allow_topic_tags' ); ?> />
	<label for="_psf_allow_topic_tags"><?php esc_html_e( 'Themen mit Tags zulassen', 'psforum' ); ?></label>

<?php
}

/**
 * Allow forum wide search
 *
 * @since PSForum (r4970)
 *
 * @uses checked() To display the checked attribute
 */
function psf_admin_setting_callback_search() {
?>

	<input name="_psf_allow_search" id="_psf_allow_search" type="checkbox" value="1" <?php checked( psf_allow_search( true ) ); psf_maybe_admin_setting_disabled( '_psf_allow_search' ); ?> />
	<label for="_psf_allow_search"><?php esc_html_e( 'Forenweite Suche zulassen', 'psforum' ); ?></label>

<?php
}

/**
 * Hierarchical reply maximum depth level setting field
 *
 * Replies will be threaded if depth is 2 or greater
 *
 * @since PSForum (r4944)
 *
 * @uses apply_filters() Calls 'psf_thread_replies_depth_max' to set a
 *                        maximum displayed level
 * @uses selected() To display the selected attribute
 */
function psf_admin_setting_callback_thread_replies_depth() {

	// Set maximum depth for dropdown
	$max_depth     = (int) apply_filters( 'psf_thread_replies_depth_max', 10 );
	$current_depth = psf_thread_replies_depth();

	// Start an output buffer for the select dropdown
	ob_start(); ?>

	</label>
	<label for="_psf_thread_replies_depth">
		<select name="_psf_thread_replies_depth" id="_psf_thread_replies_depth" <?php psf_maybe_admin_setting_disabled( '_psf_thread_replies_depth' ); ?>>
		<?php for ( $i = 2; $i <= $max_depth; $i++ ) : ?>

			<option value="<?php echo esc_attr( $i ); ?>" <?php selected( $i, $current_depth ); ?>><?php echo esc_html( $i ); ?></option>

		<?php endfor; ?>
		</select>

	<?php $select = ob_get_clean(); ?>

	<label for="_psf_allow_threaded_replies">
		<input name="_psf_allow_threaded_replies" id="_psf_allow_threaded_replies" type="checkbox" value="1" <?php checked( '1', psf_allow_threaded_replies( false ) ); psf_maybe_admin_setting_disabled( '_psf_allow_threaded_replies' ); ?> />
		<?php printf( esc_html__( 'Verschachtelte Antworten aktivieren %s Ebenen tief', 'psforum' ), $select ); ?>
	</label>

<?php
}

/**
 * Allow topic and reply revisions
 *
 * @since PSForum (r3412)
 *
 * @uses checked() To display the checked attribute
 */
function psf_admin_setting_callback_revisions() {
?>

	<input name="_psf_allow_revisions" id="_psf_allow_revisions" type="checkbox" value="1" <?php checked( psf_allow_revisions( true ) ); psf_maybe_admin_setting_disabled( '_psf_allow_revisions' ); ?> />
	<label for="_psf_allow_revisions"><?php esc_html_e( 'Protokollierung von Themen- und Antwortrevisionen zulassen', 'psforum' ); ?></label>

<?php
}

/**
 * Use the WordPress editor setting field
 *
 * @since PSForum (r3586)
 *
 * @uses checked() To display the checked attribute
 */
function psf_admin_setting_callback_use_wp_editor() {
?>

	<input name="_psf_use_wp_editor" id="_psf_use_wp_editor" type="checkbox" value="1" <?php checked( psf_use_wp_editor( true ) ); psf_maybe_admin_setting_disabled( '_psf_use_wp_editor' ); ?> />
	<label for="_psf_use_wp_editor"><?php esc_html_e( 'Füge Symbolleisten und Schaltflächen zu Textbereichen hinzu, um bei der HTML-Formatierung zu helfen', 'psforum' ); ?></label>

<?php
}

/**
 * Main subtheme section
 *
 * @since PSForum (r2786)
 */
function psf_admin_setting_callback_subtheme_section() {
?>

	<p><?php esc_html_e( 'Wie Dein Forumsinhalt in Deinem bestehenden Theme angezeigt wird.', 'psforum' ); ?></p>

<?php
}

/**
 * Use the WordPress editor setting field
 *
 * @since PSForum (r3586)
 *
 * @uses checked() To display the checked attribute
 */
function psf_admin_setting_callback_subtheme_id() {

	// Declare locale variable
	$theme_options   = '';
	$current_package = psf_get_theme_package_id( 'default' );

	// Note: This should never be empty. /templates/ is the
	// canonical backup if no other packages exist. If there's an error here,
	// something else is wrong.
	//
	// @see PSForum::register_theme_packages()
	foreach ( (array) psforum()->theme_compat->packages as $id => $theme ) {
		$theme_options .= '<option value="' . esc_attr( $id ) . '"' . selected( $theme->id, $current_package, false ) . '>' . sprintf( esc_html__( '%1$s - %2$s', 'psforum' ), esc_html( $theme->name ), esc_html( str_replace( WP_CONTENT_DIR, '', $theme->dir ) ) )  . '</option>';
	}

	if ( !empty( $theme_options ) ) : ?>

		<select name="_psf_theme_package_id" id="_psf_theme_package_id" <?php psf_maybe_admin_setting_disabled( '_psf_theme_package_id' ); ?>><?php echo $theme_options ?></select>
		<label for="_psf_theme_package_id"><?php esc_html_e( 'wird alle PS Forum-Vorlagen bereitstellen', 'psforum' ); ?></label>

	<?php else : ?>

		<p><?php esc_html_e( 'Keine Vorlagenpakete verfügbar.', 'psforum' ); ?></p>

	<?php endif;
}

/**
 * Allow oEmbed in replies
 *
 * @since PSForum (r3752)
 *
 * @uses checked() To display the checked attribute
 */
function psf_admin_setting_callback_use_autoembed() {
?>

	<input name="_psf_use_autoembed" id="_psf_use_autoembed" type="checkbox" value="1" <?php checked( psf_use_autoembed( true ) ); psf_maybe_admin_setting_disabled( '_psf_use_autoembed' ); ?> />
	<label for="_psf_use_autoembed"><?php esc_html_e( 'Bette Medien (YouTube, Twitter, Flickr, etc...) direkt in Themen und Antworten ein', 'psforum' ); ?></label>

<?php
}

/** Per Page Section **********************************************************/

/**
 * Per page settings section description for the settings page
 *
 * @since PSForum (r2786)
 */
function psf_admin_setting_callback_per_page_section() {
?>

	<p><?php esc_html_e( 'Wie viele Themen und Antworten pro Seite angezeigt werden sollen', 'psforum' ); ?></p>

<?php
}

/**
 * Topics per page setting field
 *
 * @since PSForum (r2786)
 *
 * @uses psf_form_option() To output the option value
 */
function psf_admin_setting_callback_topics_per_page() {
?>

	<input name="_psf_topics_per_page" id="_psf_topics_per_page" type="number" min="1" step="1" value="<?php psf_form_option( '_psf_topics_per_page', '15' ); ?>" class="small-text"<?php psf_maybe_admin_setting_disabled( '_psf_topics_per_page' ); ?> />
	<label for="_psf_topics_per_page"><?php esc_html_e( 'pro Seite', 'psforum' ); ?></label>

<?php
}

/**
 * Replies per page setting field
 *
 * @since PSForum (r2786)
 *
 * @uses psf_form_option() To output the option value
 */
function psf_admin_setting_callback_replies_per_page() {
?>

	<input name="_psf_replies_per_page" id="_psf_replies_per_page" type="number" min="1" step="1" value="<?php psf_form_option( '_psf_replies_per_page', '15' ); ?>" class="small-text"<?php psf_maybe_admin_setting_disabled( '_psf_replies_per_page' ); ?> />
	<label for="_psf_replies_per_page"><?php esc_html_e( 'pro Seite', 'psforum' ); ?></label>

<?php
}

/** Per RSS Page Section ******************************************************/

/**
 * Per page settings section description for the settings page
 *
 * @since PSForum (r2786)
 */
function psf_admin_setting_callback_per_rss_page_section() {
?>

	<p><?php esc_html_e( 'Wie viele Themen und Antworten pro RSS-Seite angezeigt werden sollen', 'psforum' ); ?></p>

<?php
}

/**
 * Topics per RSS page setting field
 *
 * @since PSForum (r2786)
 *
 * @uses psf_form_option() To output the option value
 */
function psf_admin_setting_callback_topics_per_rss_page() {
?>

	<input name="_psf_topics_per_rss_page" id="_psf_topics_per_rss_page" type="number" min="1" step="1" value="<?php psf_form_option( '_psf_topics_per_rss_page', '25' ); ?>" class="small-text"<?php psf_maybe_admin_setting_disabled( '_psf_topics_per_rss_page' ); ?> />
	<label for="_psf_topics_per_rss_page"><?php esc_html_e( 'pro Seite', 'psforum' ); ?></label>

<?php
}

/**
 * Replies per RSS page setting field
 *
 * @since PSForum (r2786)
 *
 * @uses psf_form_option() To output the option value
 */
function psf_admin_setting_callback_replies_per_rss_page() {
?>

	<input name="_psf_replies_per_rss_page" id="_psf_replies_per_rss_page" type="number" min="1" step="1" value="<?php psf_form_option( '_psf_replies_per_rss_page', '25' ); ?>" class="small-text"<?php psf_maybe_admin_setting_disabled( '_psf_replies_per_rss_page' ); ?> />
	<label for="_psf_replies_per_rss_page"><?php esc_html_e( 'pro Seite', 'psforum' ); ?></label>

<?php
}

/** Slug Section **************************************************************/

/**
 * Slugs settings section description for the settings page
 *
 * @since PSForum (r2786)
 */
function psf_admin_setting_callback_root_slug_section() {

	// Flush rewrite rules when this section is saved
	if ( isset( $_GET['settings-updated'] ) && isset( $_GET['page'] ) )
		flush_rewrite_rules(); ?>

	<p><?php esc_html_e( 'Passe Deinen Forenstamm an. Arbeite mit einer WordPress-Seite zusammen und verwende Shortcodes für mehr Flexibilität.', 'psforum' ); ?></p>

<?php
}

/**
 * Root slug setting field
 *
 * @since PSForum (r2786)
 *
 * @uses psf_form_option() To output the option value
 */
function psf_admin_setting_callback_root_slug() {
?>

        <input name="_psf_root_slug" id="_psf_root_slug" type="text" class="regular-text code" value="<?php psf_form_option( '_psf_root_slug', 'forums', true ); ?>"<?php psf_maybe_admin_setting_disabled( '_psf_root_slug' ); ?> />

<?php
	// Slug Check
	psf_form_slug_conflict_check( '_psf_root_slug', 'forums' );
}

/**
 * Include root slug setting field
 *
 * @since PSForum (r2786)
 *
 * @uses checked() To display the checked attribute
 */
function psf_admin_setting_callback_include_root() {
?>

	<input name="_psf_include_root" id="_psf_include_root" type="checkbox" value="1" <?php checked( psf_include_root_slug() ); psf_maybe_admin_setting_disabled( '_psf_include_root' ); ?> />
	<label for="_psf_include_root"><?php esc_html_e( 'Setze allen Foreninhalten den Forum-Root-Slug voran (empfohlen)', 'psforum' ); ?></label>

<?php
}

/**
 * Include root slug setting field
 *
 * @since PSForum (r2786)
 *
 * @uses checked() To display the checked attribute
 */
function psf_admin_setting_callback_show_on_root() {

	// Current setting
	$show_on_root = psf_show_on_root();

	// Options for forum root output
	$root_options = array(
		'forums' => array(
			'name' => __( 'Forum Index', 'psforum' )
		),
		'topics' => array(
			'name' => __( 'Themen nach Frische', 'psforum' )
		)
	); ?>

	<select name="_psf_show_on_root" id="_psf_show_on_root" <?php psf_maybe_admin_setting_disabled( '_psf_show_on_root' ); ?>>

		<?php foreach ( $root_options as $option_id => $details ) : ?>

			<option <?php selected( $show_on_root, $option_id ); ?> value="<?php echo esc_attr( $option_id ); ?>"><?php echo esc_html( $details['name'] ); ?></option>

		<?php endforeach; ?>

	</select>

<?php
}

/** User Slug Section *********************************************************/

/**
 * Slugs settings section description for the settings page
 *
 * @since PSForum (r2786)
 */
function psf_admin_setting_callback_user_slug_section() {
?>

	<p><?php esc_html_e( 'Passe Deine Benutzerprofil-Slugs an.', 'psforum' ); ?></p>

<?php
}

/**
 * User slug setting field
 *
 * @since PSForum (r2786)
 *
 * @uses psf_form_option() To output the option value
 */
function psf_admin_setting_callback_user_slug() {
?>

	<input name="_psf_user_slug" id="_psf_user_slug" type="text" class="regular-text code" value="<?php psf_form_option( '_psf_user_slug', 'users', true ); ?>"<?php psf_maybe_admin_setting_disabled( '_psf_user_slug' ); ?> />

<?php
	// Slug Check
	psf_form_slug_conflict_check( '_psf_user_slug', 'users' );
}

/**
 * Topic archive slug setting field
 *
 * @since PSForum (r2786)
 *
 * @uses psf_form_option() To output the option value
 */
function psf_admin_setting_callback_topic_archive_slug() {
?>

	<input name="_psf_topic_archive_slug" id="_psf_topic_archive_slug" type="text" class="regular-text code" value="<?php psf_form_option( '_psf_topic_archive_slug', 'topics', true ); ?>"<?php psf_maybe_admin_setting_disabled( '_psf_topic_archive_slug' ); ?> />

<?php
	// Slug Check
	psf_form_slug_conflict_check( '_psf_topic_archive_slug', 'topics' );
}

/**
 * Reply archive slug setting field
 *
 * @since PSForum (r4932)
 *
 * @uses psf_form_option() To output the option value
 */
function psf_admin_setting_callback_reply_archive_slug() {
?>

	<input name="_psf_reply_archive_slug" id="_psf_reply_archive_slug" type="text" class="regular-text code" value="<?php psf_form_option( '_psf_reply_archive_slug', 'replies', true ); ?>"<?php psf_maybe_admin_setting_disabled( '_psf_reply_archive_slug' ); ?> />

<?php
	// Slug Check
	psf_form_slug_conflict_check( '_psf_reply_archive_slug', 'replies' );
}

/**
 * Favorites slug setting field
 *
 * @since PSForum (r4932)
 *
 * @uses psf_form_option() To output the option value
 */
function psf_admin_setting_callback_user_favs_slug() {
?>

	<input name="_psf_user_favs_slug" id="_psf_user_favs_slug" type="text" class="regular-text code" value="<?php psf_form_option( '_psf_user_favs_slug', 'favorites', true ); ?>"<?php psf_maybe_admin_setting_disabled( '_psf_user_favs_slug' ); ?> />

<?php
	// Slug Check
	psf_form_slug_conflict_check( '_psf_reply_archive_slug', 'favorites' );
}

/**
 * Favorites slug setting field
 *
 * @since PSForum (r4932)
 *
 * @uses psf_form_option() To output the option value
 */
function psf_admin_setting_callback_user_subs_slug() {
?>

	<input name="_psf_user_subs_slug" id="_psf_user_subs_slug" type="text" class="regular-text code" value="<?php psf_form_option( '_psf_user_subs_slug', 'subscriptions', true ); ?>"<?php psf_maybe_admin_setting_disabled( '_psf_user_subs_slug' ); ?> />

<?php
	// Slug Check
	psf_form_slug_conflict_check( '_psf_user_subs_slug', 'subscriptions' );
}

/** Single Slugs **************************************************************/

/**
 * Slugs settings section description for the settings page
 *
 * @since PSForum (r2786)
 */
function psf_admin_setting_callback_single_slug_section() {
?>

	<p><?php printf( esc_html__( 'Benutzerdefinierte Slugs für einzelne Foren, topics, replies, tags, views, and search.', 'psforum' ), get_admin_url( null, 'options-permalink.php' ) ); ?></p>

<?php
}

/**
 * Forum slug setting field
 *
 * @since PSForum (r2786)
 *
 * @uses psf_form_option() To output the option value
 */
function psf_admin_setting_callback_forum_slug() {
?>

	<input name="_psf_forum_slug" id="_psf_forum_slug" type="text" class="regular-text code" value="<?php psf_form_option( '_psf_forum_slug', 'forum', true ); ?>"<?php psf_maybe_admin_setting_disabled( '_psf_forum_slug' ); ?> />

<?php
	// Slug Check
	psf_form_slug_conflict_check( '_psf_forum_slug', 'forum' );
}

/**
 * Topic slug setting field
 *
 * @since PSForum (r2786)
 *
 * @uses psf_form_option() To output the option value
 */
function psf_admin_setting_callback_topic_slug() {
?>

	<input name="_psf_topic_slug" id="_psf_topic_slug" type="text" class="regular-text code" value="<?php psf_form_option( '_psf_topic_slug', 'topic', true ); ?>"<?php psf_maybe_admin_setting_disabled( '_psf_topic_slug' ); ?> />

<?php
	// Slug Check
	psf_form_slug_conflict_check( '_psf_topic_slug', 'topic' );
}

/**
 * Reply slug setting field
 *
 * @since PSForum (r2786)
 *
 * @uses psf_form_option() To output the option value
 */
function psf_admin_setting_callback_reply_slug() {
?>

	<input name="_psf_reply_slug" id="_psf_reply_slug" type="text" class="regular-text code" value="<?php psf_form_option( '_psf_reply_slug', 'reply', true ); ?>"<?php psf_maybe_admin_setting_disabled( '_psf_reply_slug' ); ?> />

<?php
	// Slug Check
	psf_form_slug_conflict_check( '_psf_reply_slug', 'reply' );
}

/**
 * Topic tag slug setting field
 *
 * @since PSForum (r2786)
 *
 * @uses psf_form_option() To output the option value
 */
function psf_admin_setting_callback_topic_tag_slug() {
?>

	<input name="_psf_topic_tag_slug" id="_psf_topic_tag_slug" type="text" class="regular-text code" value="<?php psf_form_option( '_psf_topic_tag_slug', 'topic-tag', true ); ?>"<?php psf_maybe_admin_setting_disabled( '_psf_topic_tag_slug' ); ?> />

<?php

	// Slug Check
	psf_form_slug_conflict_check( '_psf_topic_tag_slug', 'topic-tag' );
}

/**
 * View slug setting field
 *
 * @since PSForum (r2789)
 *
 * @uses psf_form_option() To output the option value
 */
function psf_admin_setting_callback_view_slug() {
?>

	<input name="_psf_view_slug" id="_psf_view_slug" type="text" class="regular-text code" value="<?php psf_form_option( '_psf_view_slug', 'view', true ); ?>"<?php psf_maybe_admin_setting_disabled( '_psf_view_slug' ); ?> />

<?php
	// Slug Check
	psf_form_slug_conflict_check( '_psf_view_slug', 'view' );
}

/**
 * Search slug setting field
 *
 * @since PSForum (r4579)
 *
 * @uses psf_form_option() To output the option value
 */
function psf_admin_setting_callback_search_slug() {
?>

	<input name="_psf_search_slug" id="_psf_search_slug" type="text" class="regular-text code" value="<?php psf_form_option( '_psf_search_slug', 'search', true ); ?>"<?php psf_maybe_admin_setting_disabled( '_psf_search_slug' ); ?> />

<?php
	// Slug Check
	psf_form_slug_conflict_check( '_psf_search_slug', 'search' );
}

/** BuddyPress ****************************************************************/

/**
 * Extension settings section description for the settings page
 *
 * @since PSForum (r3575)
 */
function psf_admin_setting_callback_buddypress_section() {
?>

	<p><?php esc_html_e( 'Foreneinstellungen für BuddyPress', 'psforum' ); ?></p>

<?php
}

/**
 * Allow BuddyPress group forums setting field
 *
 * @since PSForum (r3575)
 *
 * @uses checked() To display the checked attribute
 */
function psf_admin_setting_callback_group_forums() {
?>

	<input name="_psf_enable_group_forums" id="_psf_enable_group_forums" type="checkbox" value="1" <?php checked( psf_is_group_forums_active( true ) );  psf_maybe_admin_setting_disabled( '_psf_enable_group_forums' ); ?> />
	<label for="_psf_enable_group_forums"><?php esc_html_e( 'Erlaube BuddyPress-Gruppen, ihre eigenen Foren zu haben', 'psforum' ); ?></label>

<?php
}

/**
 * Replies per page setting field
 *
 * @since PSForum (r3575)
 *
 * @uses psf_form_option() To output the option value
 */
function psf_admin_setting_callback_group_forums_root_id() {

	// Output the dropdown for all forums
	psf_dropdown( array(
		'selected'           => psf_get_group_forums_root_id(),
		'show_none'          => __( '&mdash; Forumsstamm &mdash;', 'psforum' ),
		'orderby'            => 'title',
		'order'              => 'ASC',
		'select_id'          => '_psf_group_forums_root_id',
		'disable_categories' => false,
		'disabled'           => '_psf_group_forums_root_id'
	) ); ?>

	<label for="_psf_group_forums_root_id"><?php esc_html_e( 'ist das übergeordnete Element für alle Gruppenforen', 'psforum' ); ?></label>
	<p class="description"><?php esc_html_e( 'Die Verwendung des Forumstamms wird nicht empfohlen. Wenn Du dies änderst, werden bestehende Foren nicht verschoben.', 'psforum' ); ?></p>

<?php
}

/** Akismet *******************************************************************/

/**
 * Extension settings section description for the settings page
 *
 * @since PSForum (r3575)
 */
function psf_admin_setting_callback_akismet_section() {
?>

	<p><?php esc_html_e( 'Foreneinstellungen für Akismet', 'psforum' ); ?></p>

<?php
}


/**
 * Allow Akismet setting field
 *
 * @since PSForum (r3575)
 *
 * @uses checked() To display the checked attribute
 */
function psf_admin_setting_callback_akismet() {
?>

	<input name="_psf_enable_akismet" id="_psf_enable_akismet" type="checkbox" value="1" <?php checked( psf_is_akismet_active( true ) );  psf_maybe_admin_setting_disabled( '_psf_enable_akismet' ); ?> />
	<label for="_psf_enable_akismet"><?php esc_html_e( 'Akismet erlauben, Forum-Spam aktiv zu verhindern.', 'psforum' ); ?></label>

<?php
}

/** Settings Page *************************************************************/

/**
 * The main settings page
 *
 * @since PSForum (r2643)
 *
 * @uses settings_fields() To output the hidden fields for the form
 * @uses do_settings_sections() To output the settings sections
 */
function psf_admin_settings() {
?>

	<div class="wrap">

		<h2><?php esc_html_e( 'Foreneinstellungen', 'psforum' ) ?></h2>

		<form action="options.php" method="post">

			<?php settings_fields( 'psforum' ); ?>

			<?php do_settings_sections( 'psforum' ); ?>

			<p class="submit">
				<input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e( 'Änderungen speichern', 'psforum' ); ?>" />
			</p>
		</form>
	</div>

<?php
}


/** Converter Section *********************************************************/

/**
 * Main settings section description for the settings page
 *
 * @since PSForum (r3813)
 */
function psf_converter_setting_callback_main_section() {
?>

	<p><?php _e( 'Informationen über Deine vorherige Forendatenbank, damit diese konvertiert werden kann. <strong>Sichere deine Datenbank, bevor Du fortfährst.</strong>', 'psforum' ); ?></p>

<?php
}

/**
 * Edit Platform setting field
 *
 * @since PSForum (r3813)
 */
function psf_converter_setting_callback_platform() {

	$platform_options = '';
	$curdir           = opendir( psforum()->admin->admin_dir . 'converters/' );

	// Bail if no directory was found (how did this happen?)
	if ( empty( $curdir ) )
		return;

	// Loop through files in the converters folder and assemble some options
	while ( $file = readdir( $curdir ) ) {
		if ( ( stristr( $file, '.php' ) ) && ( stristr( $file, 'index' ) === false ) ) {
			$file              = preg_replace( '/.php/', '', $file );
			$platform_options .= '<option value="' . $file . '">' . esc_html( $file ) . '</option>';
		}
	}

	closedir( $curdir ); ?>

	<select name="_psf_converter_platform" id="_psf_converter_platform" /><?php echo $platform_options ?></select>
	<label for="_psf_converter_platform"><?php esc_html_e( 'ist die vorherige Forensoftware', 'psforum' ); ?></label>

<?php
}

/**
 * Edit Database Server setting field
 *
 * @since PSForum (r3813)
 */
function psf_converter_setting_callback_dbserver() {
?>

	<input name="_psf_converter_db_server" id="_psf_converter_db_server" type="text" value="<?php psf_form_option( '_psf_converter_db_server', 'localhost' ); ?>" class="medium-text" />
	<label for="_psf_converter_db_server"><?php esc_html_e( 'IP oder Hostname', 'psforum' ); ?></label>

<?php
}

/**
 * Edit Database Server Port setting field
 *
 * @since PSForum (r3813)
 */
function psf_converter_setting_callback_dbport() {
?>

	<input name="_psf_converter_db_port" id="_psf_converter_db_port" type="text" value="<?php psf_form_option( '_psf_converter_db_port', '3306' ); ?>" class="small-text" />
	<label for="_psf_converter_db_port"><?php esc_html_e( 'Verwende die Standardeinstellung 3306, wenn Du Dir nicht sicher bist', 'psforum' ); ?></label>

<?php
}

/**
 * Edit Database User setting field
 *
 * @since PSForum (r3813)
 */
function psf_converter_setting_callback_dbuser() {
?>

	<input name="_psf_converter_db_user" id="_psf_converter_db_user" type="text" value="<?php psf_form_option( '_psf_converter_db_user' ); ?>" class="medium-text" />
	<label for="_psf_converter_db_user"><?php esc_html_e( 'Benutzer für Deine Datenbankverbindung', 'psforum' ); ?></label>

<?php
}

/**
 * Edit Database Pass setting field
 *
 * @since PSForum (r3813)
 */
function psf_converter_setting_callback_dbpass() {
?>

	<input name="_psf_converter_db_pass" id="_psf_converter_db_pass" type="password" value="<?php psf_form_option( '_psf_converter_db_pass' ); ?>" class="medium-text" />
	<label for="_psf_converter_db_pass"><?php esc_html_e( 'Passwort für den Zugriff auf die Datenbank', 'psforum' ); ?></label>

<?php
}

/**
 * Edit Database Name setting field
 *
 * @since PSForum (r3813)
 */
function psf_converter_setting_callback_dbname() {
?>

	<input name="_psf_converter_db_name" id="_psf_converter_db_name" type="text" value="<?php psf_form_option( '_psf_converter_db_name' ); ?>" class="medium-text" />
	<label for="_psf_converter_db_name"><?php esc_html_e( 'Name der Datenbank mit Deinen alten Forumsdaten', 'psforum' ); ?></label>

<?php
}

/**
 * Main settings section description for the settings page
 *
 * @since PSForum (r3813)
 */
function psf_converter_setting_callback_options_section() {
?>

	<p><?php esc_html_e( 'Einige optionale Parameter zur Optimierung des Konvertierungsprozesses.', 'psforum' ); ?></p>

<?php
}

/**
 * Edit Table Prefix setting field
 *
 * @since PSForum (r3813)
 */
function psf_converter_setting_callback_dbprefix() {
?>

	<input name="_psf_converter_db_prefix" id="_psf_converter_db_prefix" type="text" value="<?php psf_form_option( '_psf_converter_db_prefix' ); ?>" class="medium-text" />
	<label for="_psf_converter_db_prefix"><?php esc_html_e( '(Wenn Du aus BuddyPress-Foren konvertierst, verwende "wp_bb_" oder Dein benutzerdefiniertes Präfix)', 'psforum' ); ?></label>

<?php
}

/**
 * Edit Rows Limit setting field
 *
 * @since PSForum (r3813)
 */
function psf_converter_setting_callback_rows() {
?>

	<input name="_psf_converter_rows" id="_psf_converter_rows" type="text" value="<?php psf_form_option( '_psf_converter_rows', '100' ); ?>" class="small-text" />
	<label for="_psf_converter_rows"><?php esc_html_e( 'Zeilen gleichzeitig zu verarbeiten', 'psforum' ); ?></label>
	<p class="description"><?php esc_html_e( 'Halte diesen Wert niedrig, wenn Du Probleme mit unzureichendem Speicher hast.', 'psforum' ); ?></p>

<?php
}

/**
 * Edit Delay Time setting field
 *
 * @since PSForum (r3813)
 */
function psf_converter_setting_callback_delay_time() {
?>

	<input name="_psf_converter_delay_time" id="_psf_converter_delay_time" type="text" value="<?php psf_form_option( '_psf_converter_delay_time', '1' ); ?>" class="small-text" />
	<label for="_psf_converter_delay_time"><?php esc_html_e( 'Sekunde(n) Verzögerung zwischen jeder Gruppe von Zeilen', 'psforum' ); ?></label>
	<p class="description"><?php esc_html_e( 'Halte diesen Wert hoch, um Probleme mit zu vielen Verbindungen zu vermeiden.', 'psforum' ); ?></p>

<?php
}

/**
 * Edit Restart setting field
 *
 * @since PSForum (r3813)
 */
function psf_converter_setting_callback_restart() {
?>

	<input name="_psf_converter_restart" id="_psf_converter_restart" type="checkbox" value="1" <?php checked( get_option( '_psf_converter_restart', false ) ); ?> />
	<label for="_psf_converter_restart"><?php esc_html_e( 'Starte eine neue Konvertierung von Anfang an', 'psforum' ); ?></label>
	<p class="description"><?php esc_html_e( 'Du solltest alte Konvertierungsinformationen bereinigen, bevor Du neu beginnst.', 'psforum' ); ?></p>

<?php
}

/**
 * Edit Clean setting field
 *
 * @since PSForum (r3813)
 */
function psf_converter_setting_callback_clean() {
?>

	<input name="_psf_converter_clean" id="_psf_converter_clean" type="checkbox" value="1" <?php checked( get_option( '_psf_converter_clean', false ) ); ?> />
	<label for="_psf_converter_clean"><?php esc_html_e( 'Alle Informationen aus einem zuvor versuchten Import entfernen', 'psforum' ); ?></label>
	<p class="description"><?php esc_html_e( 'Verwende dies, wenn ein Import fehlgeschlagen ist und Du diese unvollständigen Daten entfernen möchtest.', 'psforum' ); ?></p>

<?php
}

/**
 * Edit Convert Users setting field
 *
 * @since PSForum (r3813)
 */
function psf_converter_setting_callback_convert_users() {
?>

	<input name="_psf_converter_convert_users" id="_psf_converter_convert_users" type="checkbox" value="1" <?php checked( get_option( '_psf_converter_convert_users', false ) ); ?> />
	<label for="_psf_converter_convert_users"><?php esc_html_e( 'Versuch, Benutzerkonten aus früheren Foren zu importieren', 'psforum' ); ?></label>
	<p class="description"><?php esc_html_e( 'Nicht-PS Forum-Passwörter können nicht automatisch konvertiert werden. Sie werden konvertiert, wenn sich jeder Benutzer anmeldet.', 'psforum' ); ?></p>

<?php
}

/** Converter Page ************************************************************/

/**
 * The main settings page
 *
 * @uses settings_fields() To output the hidden fields for the form
 * @uses do_settings_sections() To output the settings sections
 */
function psf_converter_settings() {
?>

	<div class="wrap">

		<h2 class="nav-tab-wrapper"><?php psf_tools_admin_tabs( esc_html__( 'Foren importieren', 'psforum' ) ); ?></h2>

		<form action="#" method="post" id="psf-converter-settings">

			<?php settings_fields( 'psforum_converter' ); ?>

			<?php do_settings_sections( 'psforum_converter' ); ?>

			<p class="submit">
				<input type="button" name="submit" class="button-primary" id="psf-converter-start" value="<?php esc_attr_e( 'Start', 'psforum' ); ?>" onclick="bbconverter_start();" />
				<input type="button" name="submit" class="button-primary" id="psf-converter-stop" value="<?php esc_attr_e( 'Stop', 'psforum' ); ?>" onclick="bbconverter_stop();" />
				<img id="psf-converter-progress" src="">
			</p>

			<div class="psf-converter-updated" id="psf-converter-message"></div>
		</form>
	</div>

<?php
}

/** Helpers *******************************************************************/

/**
 * Contextual help for Forums settings page
 *
 * @since PSForum (r3119)
 * @uses get_current_screen()
 */
function psf_admin_settings_help() {

	$current_screen = get_current_screen();

	// Bail if current screen could not be found
	if ( empty( $current_screen ) )
		return;

	// Overview
	$current_screen->add_help_tab( array(
		'id'      => 'overview',
		'title'   => __( 'Überblick', 'psforum' ),
		'content' => '<p>' . __( 'Dieser Bildschirm bietet Zugriff auf alle Foreneinstellungen.', 'psforum' ) . '</p>' .
					 '<p>' . __( 'Weitere Informationen zu den einzelnen Abschnitten findest Du in den zusätzlichen Hilfe-Registerkarten.', 'psforum' ) . '</p>'
	) );

	// Main Settings
	$current_screen->add_help_tab( array(
		'id'      => 'main_settings',
		'title'   => __( 'Haupteinstellungen', 'psforum' ),
		'content' => '<p>' . __( 'In den Haupteinstellungen hast Du eine Reihe von Optionen:', 'psforum' ) . '</p>' .
					 '<p>' .
						'<ul>' .
							'<li>' . __( 'You can choose to lock a post after a certain number of minutes. "Locking post editing" will prevent the author from editing some amount of time after saving a post.',              'psforum' ) . '</li>' .
							'<li>' . __( '"Throttle time" is the amount of time required between posts from a single author. The higher the throttle time, the longer a user will need to wait between posting to the forum.', 'psforum' ) . '</li>' .
							'<li>' . __( 'Favorites are a way for users to save and later return to topics they favor. This is enabled by default.',                                                                           'psforum' ) . '</li>' .
							'<li>' . __( 'Subscriptions allow users to subscribe for notifications to topics that interest them. This is enabled by default.',                                                                 'psforum' ) . '</li>' .
							'<li>' . __( 'Topic-Tags allow users to filter topics between forums. This is enabled by default.',                                                                                                'psforum' ) . '</li>' .
							'<li>' . __( '"Anonymous Posting" allows guest users who do not have accounts on your site to both create topics as well as replies.',                                                             'psforum' ) . '</li>' .
							'<li>' . __( 'The Fancy Editor brings the luxury of the Visual editor and HTML editor from the traditional WordPress dashboard into your theme.',                                                  'psforum' ) . '</li>' .
							'<li>' . __( 'Auto-embed will embed the media content from a URL directly into the replies. For example: links to Flickr and YouTube.',                                                            'psforum' ) . '</li>' .
						'</ul>' .
					'</p>' .
					'<p>' . __( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.', 'psforum' ) . '</p>'
	) );

	// Per Page
	$current_screen->add_help_tab( array(
		'id'      => 'per_page',
		'title'   => __( 'Per Page', 'psforum' ),
		'content' => '<p>' . __( 'Per Page settings allow you to control the number of topics and replies appear on each page.',                                                    'psforum' ) . '</p>' .
					 '<p>' . __( 'This is comparable to the WordPress "Reading Settings" page, where you can set the number of posts that should show on blog pages and in feeds.', 'psforum' ) . '</p>' .
					 '<p>' . __( 'These are broken up into two separate groups: one for what appears in your theme, another for RSS feeds.',                                        'psforum' ) . '</p>'
	) );

	// Slugs
	$current_screen->add_help_tab( array(
		'id'      => 'slus',
		'title'   => __( 'Slugs', 'psforum' ),
		'content' => '<p>' . __( 'The Slugs section allows you to control the permalink structure for your forums.',                                                                                                            'psforum' ) . '</p>' .
					 '<p>' . __( '"Archive Slugs" are used as the "root" for your forums and topics. If you combine these values with existing page slugs, PSForum will attempt to output the most correct title and content.', 'psforum' ) . '</p>' .
					 '<p>' . __( '"Single Slugs" are used as a prefix when viewing an individual forum, topic, reply, user, or view.',                                                                                          'psforum' ) . '</p>' .
					 '<p>' . __( 'In the event of a slug collision with WordPress or BuddyPress, a warning will appear next to the problem slug(s).', 'psforum' ) . '</p>'
	) );

	// Help Sidebar
	$current_screen->set_help_sidebar(
		'<p><strong>' . __( 'For more information:', 'psforum' ) . '</strong></p>' .
		'<p>' . __( '<a href="http://codex.psforum.org" target="_blank">PSForum Documentation</a>',    'psforum' ) . '</p>' .
		'<p>' . __( '<a href="http://psforum.org/forums/" target="_blank">PSForum Support Forums</a>', 'psforum' ) . '</p>'
	);
}

/**
 * Disable a settings field if the value is forcibly set in PSForum's global
 * options array.
 *
 * @since PSForum (r4347)
 *
 * @param string $option_key
 */
function psf_maybe_admin_setting_disabled( $option_key = '' ) {
	disabled( isset( psforum()->options[$option_key] ) );
}

/**
 * Output settings API option
 *
 * @since PSForum (r3203)
 *
 * @uses psf_get_psf_form_option()
 *
 * @param string $option
 * @param string $default
 * @param bool $slug
 */
function psf_form_option( $option, $default = '' , $slug = false ) {
	echo psf_get_form_option( $option, $default, $slug );
}
	/**
	 * Return settings API option
	 *
	 * @since PSForum (r3203)
	 *
	 * @uses get_option()
	 * @uses esc_attr()
	 * @uses apply_filters()
	 *
	 * @param string $option
	 * @param string $default
	 * @param bool $slug
	 */
	function psf_get_form_option( $option, $default = '', $slug = false ) {

		// Get the option and sanitize it
		$value = get_option( $option, $default );

		// Slug?
		if ( true === $slug ) {
			$value = esc_attr( apply_filters( 'editable_slug', $value ) );

		// Not a slug
		} else {
			$value = esc_attr( $value );
		}

		// Fallback to default
		if ( empty( $value ) )
			$value = $default;

		// Allow plugins to further filter the output
		return apply_filters( 'psf_get_form_option', $value, $option );
	}

/**
 * Used to check if a PSForum slug conflicts with an existing known slug.
 *
 * @since PSForum (r3306)
 *
 * @param string $slug
 * @param string $default
 *
 * @uses psf_get_form_option() To get a sanitized slug string
 */
function psf_form_slug_conflict_check( $slug, $default ) {

	// Only set the slugs once ver page load
	static $the_core_slugs = array();

	// Get the form value
	$this_slug = psf_get_form_option( $slug, $default, true );

	if ( empty( $the_core_slugs ) ) {

		// Slugs to check
		$core_slugs = apply_filters( 'psf_slug_conflict_check', array(

			/** WordPress Core ****************************************************/

			// Core Post Types
			'post_base'       => array( 'name' => __( 'Beiträge','psforum' ), 'default' => 'post', 'context' => 'WordPress' ),
			'page_base'       => array( 'name' => __( 'Seiten', 'psforum' ), 'default' => 'page', 'context' => 'WordPress' ),
			'revision_base'   => array( 'name' => __( 'Überarbeitungen', 'psforum' ), 'default' => 'revision', 'context' => 'WordPress' ),
			'attachment_base' => array( 'name' => __( 'Anhänge', 'psforum' ), 'default' => 'attachment', 'context' => 'WordPress' ),
			'nav_menu_base'   => array( 'name' => __( 'Menüs', 'psforum' ), 'default' => 'nav_menu_item', 'context' => 'WordPress' ),

			// Post Tags
			'tag_base'        => array( 'name' => __( 'Tag-Basis', 'psforum' ), 'default' => 'tag', 'context' => 'WordPress' ),

			// Post Categories
			'category_base'   => array( 'name' => __( 'Kategoriebasis', 'psforum' ), 'default' => 'category', 'context' => 'WordPress' ),

			/** PSForum Core ******************************************************/

			// Forum archive slug
			'_psf_root_slug'          => array( 'name' => __( 'Forenbasis', 'psforum' ), 'default' => 'forums', 'context' => 'PSForum' ),

			// Topic archive slug
			'_psf_topic_archive_slug' => array( 'name' => __( 'Themenbasis', 'psforum' ), 'default' => 'topics', 'context' => 'PSForum' ),

			// Forum slug
			'_psf_forum_slug'         => array( 'name' => __( 'Forum Slug',  'psforum' ), 'default' => 'forum',  'context' => 'PSForum' ),

			// Topic slug
			'_psf_topic_slug'         => array( 'name' => __( 'Thema Slug',  'psforum' ), 'default' => 'topic',  'context' => 'PSForum' ),

			// Reply slug
			'_psf_reply_slug'         => array( 'name' => __( 'Antwort Slug',  'psforum' ), 'default' => 'reply',  'context' => 'PSForum' ),

			// User profile slug
			'_psf_user_slug'          => array( 'name' => __( 'Nutzerbasis',   'psforum' ), 'default' => 'users',  'context' => 'PSForum' ),

			// View slug
			'_psf_view_slug'          => array( 'name' => __( 'Basis anzeigen',   'psforum' ), 'default' => 'view',   'context' => 'PSForum' ),

			// Topic tag slug
			'_psf_topic_tag_slug'     => array( 'name' => __( 'Themen-Tag Slug', 'psforum' ), 'default' => 'topic-tag', 'context' => 'PSForum' ),
		) );

		/** BuddyPress Core *******************************************************/

		if ( defined( 'BP_VERSION' ) ) {
			$bp = buddypress();

			// Loop through root slugs and check for conflict
			if ( !empty( $bp->pages ) ) {
				foreach ( $bp->pages as $page => $page_data ) {
					$page_base    = $page . '_base';
					$page_title   = sprintf( __( '%s Seite', 'psforum' ), $page_data->title );
					$core_slugs[$page_base] = array( 'name' => $page_title, 'default' => $page_data->slug, 'context' => 'BuddyPress' );
				}
			}
		}

		// Set the static
		$the_core_slugs = apply_filters( 'psf_slug_conflict', $core_slugs );
	}

	// Loop through slugs to check
	foreach ( $the_core_slugs as $key => $value ) {

		// Get the slug
		$slug_check = psf_get_form_option( $key, $value['default'], true );

		// Compare
		if ( ( $slug !== $key ) && ( $slug_check === $this_slug ) ) : ?>

			<span class="attention"><?php printf( esc_html__( 'Möglicher %1$s Konflikt: %2$s', 'psforum' ), $value['context'], '<strong>' . $value['name'] . '</strong>' ); ?></span>

		<?php endif;
	}
}
