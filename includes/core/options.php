<?php

/**
 * PSForum Options
 *
 * @package PSForum
 * @subpackage Options
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Get the default site options and their values.
 * 
 * These option
 *
 * @since PSForum (r3421)
 * @return array Filtered option names and values
 */
function psf_get_default_options() {

	// Default options
	return apply_filters( 'psf_get_default_options', array(

		/** DB Version ********************************************************/

		'_psf_db_version'           => psforum()->db_version,

		/** Settings **********************************************************/

		'_psf_edit_lock'              => 5,                          // Lock post editing after 5 minutes
		'_psf_throttle_time'          => 10,                         // Throttle post time to 10 seconds
		'_psf_enable_favorites'       => 1,                          // Favorites
		'_psf_enable_subscriptions'   => 1,                          // Subscriptions
		'_psf_allow_anonymous'        => 0,                          // Allow anonymous posting
		'_psf_allow_global_access'    => 1,                          // Users from all sites can post
		'_psf_allow_revisions'        => 1,                          // Allow revisions
		'_psf_allow_topic_tags'       => 1,                          // Allow topic tagging
		'_psf_allow_threaded_replies' => 0,                          // Allow threaded replies
		'_psf_allow_search'           => 1,                          // Allow forum-wide search
		'_psf_thread_replies_depth'   => 2,                          // Thread replies depth
		'_psf_use_wp_editor'          => 1,                          // Use the WordPress editor if available
		'_psf_use_autoembed'          => 0,                          // Allow oEmbed in topics and replies
		'_psf_theme_package_id'       => 'default',                  // The ID for the current theme package
		'_psf_default_role'           => psf_get_participant_role(), // Default forums role
		'_psf_settings_integration'   => 0,                          // Put settings into existing admin pages

		/** Per Page **********************************************************/

		'_psf_topics_per_page'      => 15,          // Topics per page
		'_psf_replies_per_page'     => 15,          // Replies per page
		'_psf_forums_per_page'      => 50,          // Forums per page
		'_psf_topics_per_rss_page'  => 25,          // Topics per RSS page
		'_psf_replies_per_rss_page' => 25,          // Replies per RSS page

		/** Page For **********************************************************/

		'_psf_page_for_forums'      => 0,           // Page for forums
		'_psf_page_for_topics'      => 0,           // Page for forums
		'_psf_page_for_login'       => 0,           // Page for login
		'_psf_page_for_register'    => 0,           // Page for register
		'_psf_page_for_lost_pass'   => 0,           // Page for lost-pass

		/** Forum Root ********************************************************/

		'_psf_root_slug'            => 'forums',    // Forum archive slug
		'_psf_show_on_root'         => 'forums',    // What to show on root (forums|topics)
		'_psf_include_root'         => 1,           // Include forum-archive before single slugs

		/** Single Slugs ******************************************************/

		'_psf_forum_slug'           => 'forum',     // Forum slug
		'_psf_topic_slug'           => 'topic',     // Topic slug
		'_psf_reply_slug'           => 'reply',     // Reply slug
		'_psf_topic_tag_slug'       => 'topic-tag', // Topic tag slug

		/** User Slugs ********************************************************/

		'_psf_user_slug'            => 'users',         // User profile slug
		'_psf_user_favs_slug'       => 'favorites',     // User favorites slug
		'_psf_user_subs_slug'       => 'subscriptions', // User subscriptions slug
		'_psf_topic_archive_slug'   => 'topics',        // Topic archive slug
		'_psf_reply_archive_slug'   => 'replies',       // Reply archive slug

		/** Other Slugs *******************************************************/

		'_psf_view_slug'            => 'view',      // View slug
		'_psf_search_slug'          => 'search',    // Search slug

		/** Topics ************************************************************/

		'_psf_title_max_length'     => 80,          // Title Max Length
		'_psf_super_sticky_topics'  => '',          // Super stickies

		/** Forums ************************************************************/

		'_psf_private_forums'       => '',          // Private forums
		'_psf_hidden_forums'        => '',          // Hidden forums

		/** BuddyPress ********************************************************/

		'_psf_enable_group_forums'  => 1,           // Enable BuddyPress Group Extension
		'_psf_group_forums_root_id' => 0,           // Group Forums parent forum id

		/** Akismet ***********************************************************/

		'_psf_enable_akismet'       => 1            // Users from all sites can post

	) );
}

/**
 * Add default options
 *
 * Hooked to psf_activate, it is only called once when PSForum is activated.
 * This is non-destructive, so existing settings will not be overridden.
 *
 * @since PSForum (r3421)
 * @uses psf_get_default_options() To get default options
 * @uses add_option() Adds default options
 * @uses do_action() Calls 'psf_add_options'
 */
function psf_add_options() {

	// Add default options
	foreach ( psf_get_default_options() as $key => $value )
		add_option( $key, $value );

	// Allow previously activated plugins to append their own options.
	do_action( 'psf_add_options' );
}

/**
 * Delete default options
 *
 * Hooked to psf_uninstall, it is only called once when PSForum is uninstalled.
 * This is destructive, so existing settings will be destroyed.
 *
 * @since PSForum (r3421)
 * @uses psf_get_default_options() To get default options
 * @uses delete_option() Removes default options
 * @uses do_action() Calls 'psf_delete_options'
 */
function psf_delete_options() {

	// Add default options
	foreach ( array_keys( psf_get_default_options() ) as $key )
		delete_option( $key );

	// Allow previously activated plugins to append their own options.
	do_action( 'psf_delete_options' );
}

/**
 * Add filters to each PSForum option and allow them to be overloaded from
 * inside the $psf->options array.
 *
 * @since PSForum (r3451)
 * @uses psf_get_default_options() To get default options
 * @uses add_filter() To add filters to 'pre_option_{$key}'
 * @uses do_action() Calls 'psf_add_option_filters'
 */
function psf_setup_option_filters() {

	// Add filters to each PSForum option
	foreach ( array_keys( psf_get_default_options() ) as $key )
		add_filter( 'pre_option_' . $key, 'psf_pre_get_option' );

	// Allow previously activated plugins to append their own options.
	do_action( 'psf_setup_option_filters' );
}

/**
 * Filter default options and allow them to be overloaded from inside the
 * $psf->options array.
 *
 * @since PSForum (r3451)
 * @param bool $value Optional. Default value false
 * @return mixed false if not overloaded, mixed if set
 */
function psf_pre_get_option( $value = '' ) {

	// Remove the filter prefix
	$option = str_replace( 'pre_option_', '', current_filter() );

	// Check the options global for preset value
	if ( isset( psforum()->options[$option] ) )
		$value = psforum()->options[$option];

	// Always return a value, even if false
	return $value;
}

/** Active? *******************************************************************/

/**
 * Checks if favorites feature is enabled.
 *
 * @since PSForum (r2658)
 * @param $default bool Optional.Default value true
 * @uses get_option() To get the favorites option
 * @return bool Is favorites enabled or not
 */
function psf_is_favorites_active( $default = 1 ) {
	return (bool) apply_filters( 'psf_is_favorites_active', (bool) get_option( '_psf_enable_favorites', $default ) );
}

/**
 * Checks if subscription feature is enabled.
 *
 * @since PSForum (r2658)
 * @param $default bool Optional.Default value true
 * @uses get_option() To get the subscriptions option
 * @return bool Is subscription enabled or not
 */
function psf_is_subscriptions_active( $default = 1 ) {
	return (bool) apply_filters( 'psf_is_subscriptions_active', (bool) get_option( '_psf_enable_subscriptions', $default ) );
}

/**
 * Are topic tags allowed
 *
 * @since PSForum (r4097)
 * @param $default bool Optional. Default value true
 * @uses get_option() To get the allow tags
 * @return bool Are tags allowed?
 */
function psf_allow_topic_tags( $default = 1 ) {
	return (bool) apply_filters( 'psf_allow_topic_tags', (bool) get_option( '_psf_allow_topic_tags', $default ) );
}

/**
 * Is forum-wide searching allowed
 *
 * @since PSForum (r4970)
 * @param $default bool Optional. Default value true
 * @uses get_option() To get the forum-wide search setting
 * @return bool Is forum-wide searching allowed?
 */
function psf_allow_search( $default = 1 ) {
	return (bool) apply_filters( 'psf_allow_search', (bool) get_option( '_psf_allow_search', $default ) );
}

/**
 * Are replies threaded
 *
 * @since PSForum (r4944)
 *
 * @param bool $default Optional. Default value true
 * @uses apply_filters() Calls 'psf_thread_replies' with the calculated value and
 *                        the thread replies depth
 * @uses get_option() To get thread replies option
 * @return bool Are replies threaded?
 */
function psf_thread_replies() {
	$depth  = psf_thread_replies_depth();
	$allow  = psf_allow_threaded_replies();
	$retval = (bool) ( ( $depth >= 2 ) && ( true === $allow ) );

	return (bool) apply_filters( 'psf_thread_replies', $retval, $depth, $allow );
}

/**
 * Are threaded replies allowed
 *
 * @since PSForum (r4964)
 * @param $default bool Optional. Default value false
 * @uses get_option() To get the threaded replies setting
 * @return bool Are threaded replies allowed?
 */
function psf_allow_threaded_replies( $default = 0 ) {
	return (bool) apply_filters( '_psf_allow_threaded_replies', (bool) get_option( '_psf_allow_threaded_replies', $default ) );
}

/**
 * Maximum reply thread depth
 *
 * @since PSForum (r4944)
 *
 * @param int $default Thread replies depth
 * @uses apply_filters() Calls 'psf_thread_replies_depth' with the option value and
 *                       the default depth
 * @uses get_option() To get the thread replies depth
 * @return int Thread replies depth
 */
function psf_thread_replies_depth( $default = 2 ) {
	return (int) apply_filters( 'psf_thread_replies_depth', (int) get_option( '_psf_thread_replies_depth', $default ) );
}

/**
 * Are topic and reply revisions allowed
 *
 * @since PSForum (r3412)
 * @param $default bool Optional. Default value true
 * @uses get_option() To get the allow revisions
 * @return bool Are revisions allowed?
 */
function psf_allow_revisions( $default = 1 ) {
	return (bool) apply_filters( 'psf_allow_revisions', (bool) get_option( '_psf_allow_revisions', $default ) );
}

/**
 * Is the anonymous posting allowed?
 *
 * @since PSForum (r2659)
 * @param $default bool Optional. Default value
 * @uses get_option() To get the allow anonymous option
 * @return bool Is anonymous posting allowed?
 */
function psf_allow_anonymous( $default = 0 ) {
	return apply_filters( 'psf_allow_anonymous', (bool) get_option( '_psf_allow_anonymous', $default ) );
}

/**
 * Is this forum available to all users on all sites in this installation?
 *
 * @since PSForum (r3378)
 * @param $default bool Optional. Default value false
 * @uses get_option() To get the global access option
 * @return bool Is global access allowed?
 */
function psf_allow_global_access( $default = 1 ) {
	return (bool) apply_filters( 'psf_allow_global_access', (bool) get_option( '_psf_allow_global_access', $default ) );
}

/**
 * Is this forum available to all users on all sites in this installation?
 *
 * @since PSForum (r4294)
 * @param $default string Optional. Default value empty
 * @uses get_option() To get the default forums role option
 * @return string The default forums user role
 */
function psf_get_default_role( $default = 'psf_participant' ) {
	return apply_filters( 'psf_get_default_role', get_option( '_psf_default_role', $default ) );
}

/**
 * Use the WordPress editor if available
 *
 * @since PSForum (r3386)
 * @param $default bool Optional. Default value true
 * @uses get_option() To get the WP editor option
 * @return bool Use WP editor?
 */
function psf_use_wp_editor( $default = 1 ) {
	return (bool) apply_filters( 'psf_use_wp_editor', (bool) get_option( '_psf_use_wp_editor', $default ) );
}

/**
 * Use WordPress's oEmbed API
 *
 * @since PSForum (r3752)
 * @param $default bool Optional. Default value true
 * @uses get_option() To get the oEmbed option
 * @return bool Use oEmbed?
 */
function psf_use_autoembed( $default = 1 ) {
	return (bool) apply_filters( 'psf_use_autoembed', (bool) get_option( '_psf_use_autoembed', $default ) );
}

/**
 * Get the current theme package ID
 *
 * @since PSForum (r3829)
 * @param $default string Optional. Default value 'default'
 * @uses get_option() To get the subtheme option
 * @return string ID of the subtheme
 */
function psf_get_theme_package_id( $default = 'default' ) {
	return apply_filters( 'psf_get_theme_package_id', get_option( '_psf_theme_package_id', $default ) );
}

/**
 * Output the maximum length of a title
 *
 * @since PSForum (r3246)
 * @param $default bool Optional. Default value 80
 */
function psf_title_max_length( $default = 80 ) {
	echo psf_get_title_max_length( $default );
}
	/**
	 * Return the maximum length of a title
	 *
	 * @since PSForum (r3246)
	 * @param $default bool Optional. Default value 80
	 * @uses get_option() To get the maximum title length
	 * @return int Is anonymous posting allowed?
	 */
	function psf_get_title_max_length( $default = 80 ) {
		return (int) apply_filters( 'psf_get_title_max_length', (int) get_option( '_psf_title_max_length', $default ) );
	}

/**
 * Output the grop forums root parent forum id
 *
 * @since PSForum (r3575)
 * @param $default int Optional. Default value
 */
function psf_group_forums_root_id( $default = 0 ) {
	echo psf_get_group_forums_root_id( $default );
}
	/**
	 * Return the grop forums root parent forum id
	 *
	 * @since PSForum (r3575)
	 * @param $default bool Optional. Default value 0
	 * @uses get_option() To get the root group forum ID
	 * @return int The post ID for the root forum
	 */
	function psf_get_group_forums_root_id( $default = 0 ) {
		return (int) apply_filters( 'psf_get_group_forums_root_id', (int) get_option( '_psf_group_forums_root_id', $default ) );
	}

/**
 * Checks if BuddyPress Group Forums are enabled
 *
 * @since PSForum (r3575)
 * @param $default bool Optional. Default value true
 * @uses get_option() To get the group forums option
 * @return bool Is group forums enabled or not
 */
function psf_is_group_forums_active( $default = 1 ) {
	return (bool) apply_filters( 'psf_is_group_forums_active', (bool) get_option( '_psf_enable_group_forums', $default ) );
}

/**
 * Checks if Akismet is enabled
 *
 * @since PSForum (r3575)
 * @param $default bool Optional. Default value true
 * @uses get_option() To get the Akismet option
 * @return bool Is Akismet enabled or not
 */
function psf_is_akismet_active( $default = 1 ) {
	return (bool) apply_filters( 'psf_is_akismet_active', (bool) get_option( '_psf_enable_akismet', $default ) );
}

/**
 * Integrate settings into existing WordPress pages
 *
 * @since PSForum (r4932)
 * @param $default bool Optional. Default value false
 * @uses get_option() To get the admin integration setting
 * @return bool To deeply integrate settings, or not
 */
function psf_settings_integration( $default = 0 ) {
	return (bool) apply_filters( 'psf_settings_integration', (bool) get_option( '_psf_settings_integration', $default ) );
}

/** Slugs *********************************************************************/

/**
 * Return the root slug
 *
 * @since PSForum (r3759)
 * @return string
 */
function psf_get_root_slug( $default = 'forums' ) {
	return apply_filters( 'psf_get_root_slug', get_option( '_psf_root_slug', $default ) );
}

/**
 * Are we including the root slug in front of forum pages?
 *
 * @since PSForum (r3759)
 * @return bool
 */
function psf_include_root_slug( $default = 1 ) {
	return (bool) apply_filters( 'psf_include_root_slug', (bool) get_option( '_psf_include_root', $default ) );
}

/**
 * Return the search slug
 *
 * @since PSForum (r4932)
 *
 * @return string
 */
function psf_show_on_root( $default = 'forums' ) {
	return apply_filters( 'psf_show_on_root', get_option( '_psf_show_on_root', $default ) );
}

/**
 * Maybe return the root slug, based on whether or not it's included in the url
 *
 * @since PSForum (r3759)
 * @return string
 */
function psf_maybe_get_root_slug() {
	$retval = '';

	if ( psf_get_root_slug() && psf_include_root_slug() )
		$retval = trailingslashit( psf_get_root_slug() );

	return apply_filters( 'psf_maybe_get_root_slug', $retval );
}

/**
 * Return the single forum slug
 *
 * @since PSForum (r3759)
 * @return string
 */
function psf_get_forum_slug( $default = 'forum' ) {;
	return apply_filters( 'psf_get_root_slug', psf_maybe_get_root_slug() . get_option( '_psf_forum_slug', $default ) );
}

/**
 * Return the topic archive slug
 *
 * @since PSForum (r3759)
 * @return string
 */
function psf_get_topic_archive_slug( $default = 'topics' ) {
	return apply_filters( 'psf_get_topic_archive_slug', get_option( '_psf_topic_archive_slug', $default ) );
}

/**
 * Return the reply archive slug
 *
 * @since PSForum (r4925)
 * @return string
 */
function psf_get_reply_archive_slug( $default = 'replies' ) {
	return apply_filters( 'psf_get_reply_archive_slug', get_option( '_psf_reply_archive_slug', $default ) );
}

/**
 * Return the single topic slug
 *
 * @since PSForum (r3759)
 * @return string
 */
function psf_get_topic_slug( $default = 'topic' ) {
	return apply_filters( 'psf_get_topic_slug', psf_maybe_get_root_slug() . get_option( '_psf_topic_slug', $default ) );
}

/**
 * Return the topic-tag taxonomy slug
 *
 * @since PSForum (r3759)
 * @return string
 */
function psf_get_topic_tag_tax_slug( $default = 'topic-tag' ) {
	return apply_filters( 'psf_get_topic_tag_tax_slug', psf_maybe_get_root_slug() . get_option( '_psf_topic_tag_slug', $default ) );
}

/**
 * Return the single reply slug (used mostly for editing)
 *
 * @since PSForum (r3759)
 * @return string
 */
function psf_get_reply_slug( $default = 'reply' ) {
	return apply_filters( 'psf_get_reply_slug', psf_maybe_get_root_slug() . get_option( '_psf_reply_slug', $default ) );
}

/**
 * Return the single user slug
 *
 * @since PSForum (r3759)
 * @return string
 */
function psf_get_user_slug( $default = 'user' ) {
	return apply_filters( 'psf_get_user_slug', psf_maybe_get_root_slug() . get_option( '_psf_user_slug', $default ) );
}

/**
 * Return the single user favorites slug
 *
 * @since PSForum (r4187)
 * @return string
 */
function psf_get_user_favorites_slug( $default = 'favorites' ) {
	return apply_filters( 'psf_get_user_favorites_slug', get_option( '_psf_user_favs_slug', $default ) );
}

/**
 * Return the single user subscriptions slug
 *
 * @since PSForum (r4187)
 * @return string
 */
function psf_get_user_subscriptions_slug( $default = 'subscriptions' ) {
	return apply_filters( 'psf_get_user_subscriptions_slug', get_option( '_psf_user_subs_slug', $default ) );
}

/**
 * Return the topic view slug
 *
 * @since PSForum (r3759)
 * @return string
 */
function psf_get_view_slug( $default = 'view' ) {
	return apply_filters( 'psf_get_view_slug', psf_maybe_get_root_slug() . get_option( '_psf_view_slug', $default ) );
}

/**
 * Return the search slug
 *
 * @since PSForum (r4579)
 *
 * @return string
 */
function psf_get_search_slug( $default = 'search' ) {
	return apply_filters( 'psf_get_search_slug', psf_maybe_get_root_slug() . get_option( '_psf_search_slug', $default ) );
}

/** Legacy ********************************************************************/

/**
 * Checks if there is a previous BuddyPress Forum configuration
 *
 * @since PSForum (r3790)
 * @param $default string Optional. Default empty string
 * @uses get_option() To get the old bb-config.php location
 * @return string The location of the bb-config.php file, if any
 */
function psf_get_config_location( $default = '' ) {
	return apply_filters( 'psf_get_config_location', get_option( 'bb-config-location', $default ) );
}
