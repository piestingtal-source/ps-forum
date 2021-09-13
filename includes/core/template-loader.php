<?php

/**
 * PSForum Template Loader
 *
 * @package PSForum
 * @subpackage TemplateLoader
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Possibly intercept the template being loaded
 *
 * Listens to the 'template_include' filter and waits for any PSForum specific
 * template condition to be met. If one is met and the template file exists,
 * it will be used; otherwise 
 *
 * Note that the _edit() checks are ahead of their counterparts, to prevent them
 * from being stomped on accident.
 *
 * @since PSForum (r3032)
 *
 * @param string $template
 *
 * @uses psf_is_single_user() To check if page is single user
 * @uses psf_get_single_user_template() To get user template
 * @uses psf_is_single_user_edit() To check if page is single user edit
 * @uses psf_get_single_user_edit_template() To get user edit template
 * @uses psf_is_single_view() To check if page is single view
 * @uses psf_get_single_view_template() To get view template
 * @uses psf_is_search() To check if page is search
 * @uses psf_get_search_template() To get search template
 * @uses psf_is_forum_edit() To check if page is forum edit
 * @uses psf_get_forum_edit_template() To get forum edit template
 * @uses psf_is_topic_merge() To check if page is topic merge
 * @uses psf_get_topic_merge_template() To get topic merge template
 * @uses psf_is_topic_split() To check if page is topic split
 * @uses psf_get_topic_split_template() To get topic split template
 * @uses psf_is_topic_edit() To check if page is topic edit
 * @uses psf_get_topic_edit_template() To get topic edit template
 * @uses psf_is_reply_move() To check if page is reply move
 * @uses psf_get_reply_move_template() To get reply move template
 * @uses psf_is_reply_edit() To check if page is reply edit
 * @uses psf_get_reply_edit_template() To get reply edit template
 * @uses psf_set_theme_compat_template() To set the global theme compat template
 *
 * @return string The path to the template file that is being used
 */
function psf_template_include_theme_supports( $template = '' ) {

	// Editing a user
	if     ( psf_is_single_user_edit() && ( $new_template = psf_get_single_user_edit_template() ) ) :

	// User favorites
	elseif ( psf_is_favorites()        && ( $new_template = psf_get_favorites_template()        ) ) :

	// User favorites
	elseif ( psf_is_subscriptions()    && ( $new_template = psf_get_subscriptions_template()    ) ) :

	// Viewing a user
	elseif ( psf_is_single_user()      && ( $new_template = psf_get_single_user_template()      ) ) :

	// Single View
	elseif ( psf_is_single_view()      && ( $new_template = psf_get_single_view_template()      ) ) :

	// Search
	elseif ( psf_is_search()           && ( $new_template = psf_get_search_template()           ) ) :

	// Forum edit
	elseif ( psf_is_forum_edit()       && ( $new_template = psf_get_forum_edit_template()       ) ) :

	// Single Forum
	elseif ( psf_is_single_forum()     && ( $new_template = psf_get_single_forum_template()     ) ) :

	// Forum Archive
	elseif ( psf_is_forum_archive()    && ( $new_template = psf_get_forum_archive_template()    ) ) :

	// Topic merge
	elseif ( psf_is_topic_merge()      && ( $new_template = psf_get_topic_merge_template()      ) ) :

	// Topic split
	elseif ( psf_is_topic_split()      && ( $new_template = psf_get_topic_split_template()      ) ) :

	// Topic edit
	elseif ( psf_is_topic_edit()       && ( $new_template = psf_get_topic_edit_template()       ) ) :

	// Single Topic
	elseif ( psf_is_single_topic()     && ( $new_template = psf_get_single_topic_template()     ) ) :

	// Topic Archive
	elseif ( psf_is_topic_archive()    && ( $new_template = psf_get_topic_archive_template()    ) ) :

	// Reply move
	elseif ( psf_is_reply_move()       && ( $new_template = psf_get_reply_move_template()       ) ) :

	// Editing a reply
	elseif ( psf_is_reply_edit()       && ( $new_template = psf_get_reply_edit_template()       ) ) :

	// Single Reply
	elseif ( psf_is_single_reply()     && ( $new_template = psf_get_single_reply_template()     ) ) :

	// Editing a topic tag
	elseif ( psf_is_topic_tag_edit()   && ( $new_template = psf_get_topic_tag_edit_template()   ) ) :

	// Viewing a topic tag
	elseif ( psf_is_topic_tag()        && ( $new_template = psf_get_topic_tag_template()        ) ) :
	endif;

	// A PSForum template file was located, so override the WordPress template
	// and use it to switch off PSForum's theme compatibility.
	if ( !empty( $new_template ) ) {
		$template = psf_set_template_included( $new_template );
	}

	return apply_filters( 'psf_template_include_theme_supports', $template );
}

/**
 * Set the included template
 *
 * @since PSForum (r4975)
 * @param mixed $template Default false
 * @return mixed False if empty. Template name if template included
 */
function psf_set_template_included( $template = false ) {
	psforum()->theme_compat->psforum_template = $template;

	return psforum()->theme_compat->psforum_template;
}

/**
 * Is a PSForum template being included?
 *
 * @since PSForum (r4975)
 * @return bool True if yes, false if no
 */
function psf_is_template_included() {
	return ! empty( psforum()->theme_compat->psforum_template );
}

/** Custom Functions **********************************************************/

/**
 * Attempt to load a custom PSForum functions file, similar to each themes
 * functions.php file.
 *
 * @since PSForum (r3732)
 *
 * @global string $pagenow
 * @uses psf_locate_template()
 */
function psf_load_theme_functions() {
	global $pagenow;

	// If PSForum is being deactivated, do not load any more files
	if ( psf_is_deactivation() )
		return;

	if ( ! defined( 'WP_INSTALLING' ) || ( !empty( $pagenow ) && ( 'wp-activate.php' !== $pagenow ) ) ) {
		psf_locate_template( 'psforum-functions.php', true );
	}
}

/** Individual Templates ******************************************************/

/**
 * Get the user profile template
 *
 * @since PSForum (r3311)
 *
 * @uses psf_get_displayed_user_id()
 * @uses psf_get_query_template()
 * @return string Path to template file
 */
function psf_get_single_user_template() {
	$nicename  = psf_get_displayed_user_field( 'user_nicename' );
	$user_id   = psf_get_displayed_user_id();
	$templates = array(
		'single-user-' . $nicename . '.php', // Single User nicename
		'single-user-' . $user_id  . '.php', // Single User ID
		'single-user.php',                   // Single User
		'user.php',                          // User
	);
	return psf_get_query_template( 'profile', $templates );
}

/**
 * Get the user profile edit template
 *
 * @since PSForum (r3311)
 *
 * @uses psf_get_displayed_user_id()
 * @uses psf_get_query_template()
 * @return string Path to template file
 */
function psf_get_single_user_edit_template() {
	$nicename  = psf_get_displayed_user_field( 'user_nicename' );
	$user_id   = psf_get_displayed_user_id();
	$templates = array(
		'single-user-edit-' . $nicename . '.php', // Single User Edit nicename
		'single-user-edit-' . $user_id  . '.php', // Single User Edit ID
		'single-user-edit.php',                   // Single User Edit
		'user-edit.php',                          // User Edit
		'user.php',                               // User
	);
	return psf_get_query_template( 'profile_edit', $templates );
}

/**
 * Get the user favorites template
 *
 * @since PSForum (r4225)
 *
 * @uses psf_get_displayed_user_id()
 * @uses psf_get_query_template()
 * @return string Path to template file
 */
function psf_get_favorites_template() {
	$nicename  = psf_get_displayed_user_field( 'user_nicename' );
	$user_id   = psf_get_displayed_user_id();
	$templates = array(
		'single-user-favorites-' . $nicename . '.php', // Single User Favs nicename
		'single-user-favorites-' . $user_id  . '.php', // Single User Favs ID
		'favorites-' . $nicename  . '.php',            // Favorites nicename
		'favorites-' . $user_id   . '.php',            // Favorites ID
		'favorites.php',                               // Favorites
		'user.php',                                    // User
	);
	return psf_get_query_template( 'favorites', $templates );
}

/**
 * Get the user subscriptions template
 *
 * @since PSForum (r4225)
 *
 * @uses psf_get_displayed_user_id()
 * @uses psf_get_query_template()
 * @return string Path to template file
 */
function psf_get_subscriptions_template() {
	$nicename  = psf_get_displayed_user_field( 'user_nicename' );
	$user_id   = psf_get_displayed_user_id();
	$templates = array(
		'single-user-subscriptions-' . $nicename . '.php', // Single User Subs nicename
		'single-user-subscriptions-' . $user_id  . '.php', // Single User Subs ID
		'subscriptions-' . $nicename  . '.php',            // Subscriptions nicename
		'subscriptions-' . $user_id   . '.php',            // Subscriptions ID
		'subscriptions.php',                               // Subscriptions
		'user.php',                                        // User
	);
	return psf_get_query_template( 'subscriptions', $templates );
}

/**
 * Get the view template
 *
 * @since PSForum (r3311)
 *
 * @uses psf_get_view_id()
 * @uses psf_get_query_template()
 * @return string Path to template file
 */
function psf_get_single_view_template() {
	$view_id   = psf_get_view_id();
	$templates = array(
		'single-view-' . $view_id . '.php', // Single View ID
		'view-'        . $view_id . '.php', // View ID
		'single-view.php',                  // Single View
		'view.php',                         // View
	);
	return psf_get_query_template( 'single_view', $templates );
}

/**
 * Get the search template
 *
 * @since PSForum (r4579)
 *
 * @uses psf_get_query_template()
 * @return string Path to template file
 */
function psf_get_search_template() {
	$templates = array(
		'page-forum-search.php', // Single Search
		'forum-search.php',      // Search
	);
	return psf_get_query_template( 'single_search', $templates );
}

/**
 * Get the single forum template
 *
 * @since PSForum (r3922)
 *
 * @uses psf_get_forum_post_type()
 * @uses psf_get_query_template()
 * @return string Path to template file
 */
function psf_get_single_forum_template() {
	$templates = array(
		'single-' . psf_get_forum_post_type() . '.php' // Single Forum
	);
	return psf_get_query_template( 'single_forum', $templates );
}

/**
 * Get the forum archive template
 *
 * @since PSForum (r3922)
 *
 * @uses psf_get_forum_post_type()
 * @uses psf_get_query_template()
 * @return string Path to template file
 */
function psf_get_forum_archive_template() {
	$templates = array(
		'archive-' . psf_get_forum_post_type() . '.php' // Forum Archive
	);
	return psf_get_query_template( 'forum_archive', $templates );
}

/**
 * Get the forum edit template
 *
 * @since PSForum (r3566)
 *
 * @uses psf_get_topic_post_type()
 * @uses psf_get_query_template()
 * @return string Path to template file
 */
function psf_get_forum_edit_template() {
	$templates = array(
		'single-' . psf_get_forum_post_type() . '-edit.php' // Single Forum Edit
	);
	return psf_get_query_template( 'forum_edit', $templates );
}

/**
 * Get the single topic template
 *
 * @since PSForum (r3922)
 *
 * @uses psf_get_topic_post_type()
 * @uses psf_get_query_template()
 * @return string Path to template file
 */
function psf_get_single_topic_template() {
	$templates = array(
		'single-' . psf_get_topic_post_type() . '.php'
	);
	return psf_get_query_template( 'single_topic', $templates );
}

/**
 * Get the topic archive template
 *
 * @since PSForum (r3922)
 *
 * @uses psf_get_topic_post_type()
 * @uses psf_get_query_template()
 * @return string Path to template file
 */
function psf_get_topic_archive_template() {
	$templates = array(
		'archive-' . psf_get_topic_post_type() . '.php' // Topic Archive
	);
	return psf_get_query_template( 'topic_archive', $templates );
}

/**
 * Get the topic edit template
 *
 * @since PSForum (r3311)
 *
 * @uses psf_get_topic_post_type()
 * @uses psf_get_query_template()
 * @return string Path to template file
 */
function psf_get_topic_edit_template() {
	$templates = array(
		'single-' . psf_get_topic_post_type() . '-edit.php' // Single Topic Edit
	);
	return psf_get_query_template( 'topic_edit', $templates );
}

/**
 * Get the topic split template
 *
 * @since PSForum (r3311)
 *
 * @uses psf_get_topic_post_type()
 * @uses psf_get_query_template()
 * @return string Path to template file
 */
function psf_get_topic_split_template() {
	$templates = array(
		'single-' . psf_get_topic_post_type() . '-split.php', // Topic Split
	);
	return psf_get_query_template( 'topic_split', $templates );
}

/**
 * Get the topic merge template
 *
 * @since PSForum (r3311)
 *
 * @uses psf_get_topic_post_type()
 * @uses psf_get_query_template()
 * @return string Path to template file
 */
function psf_get_topic_merge_template() {
	$templates = array(
		'single-' . psf_get_topic_post_type() . '-merge.php', // Topic Merge
	);
	return psf_get_query_template( 'topic_merge', $templates );
}

/**
 * Get the single reply template
 *
 * @since PSForum (r3922)
 *
 * @uses psf_get_reply_post_type()
 * @uses psf_get_query_template()
 * @return string Path to template file
 */
function psf_get_single_reply_template() {
	$templates = array(
		'single-' . psf_get_reply_post_type() . '.php'
	);
	return psf_get_query_template( 'single_reply', $templates );
}

/**
 * Get the reply edit template
 *
 * @since PSForum (r3311)
 *
 * @uses psf_get_reply_post_type()
 * @uses psf_get_query_template()
* @return string Path to template file
 */
function psf_get_reply_edit_template() {
	$templates = array(
		'single-' . psf_get_reply_post_type() . '-edit.php' // Single Reply Edit
	);
	return psf_get_query_template( 'reply_edit', $templates );
}

/**
 * Get the reply move template
 *
 * @since PSForum (r4521)
 *
 * @uses psf_get_reply_post_type()
 * @uses psf_get_query_template()
 * @return string Path to template file
 */
function psf_get_reply_move_template() {
	$templates = array(
		'single-' . psf_get_reply_post_type() . '-move.php', // Reply move
	);
	return psf_get_query_template( 'reply_move', $templates );
}

/**
 * Get the topic template
 *
 * @since PSForum (r3311)
 *
 * @uses psf_get_topic_tag_tax_id()
 * @uses psf_get_query_template()
 * @return string Path to template file
 */
function psf_get_topic_tag_template() {
	$tt_slug   = psf_get_topic_tag_slug();
	$tt_id     = psf_get_topic_tag_tax_id();
	$templates = array(
		'taxonomy-' . $tt_slug . '.php', // Single Topic Tag slug
		'taxonomy-' . $tt_id   . '.php', // Single Topic Tag ID
	);
	return psf_get_query_template( 'topic_tag', $templates );
}

/**
 * Get the topic edit template
 *
 * @since PSForum (r3311)
 *
 * @uses psf_get_topic_tag_tax_id()
 * @uses psf_get_query_template()
 * @return string Path to template file
 */
function psf_get_topic_tag_edit_template() {
	$tt_slug   = psf_get_topic_tag_slug();
	$tt_id     = psf_get_topic_tag_tax_id();
	$templates = array(
		'taxonomy-' . $tt_slug . '-edit.php', // Single Topic Tag Edit slug
		'taxonomy-' . $tt_id   . '-edit.php'  // Single Topic Tag Edit ID
	);
	return psf_get_query_template( 'topic_tag_edit', $templates );
}

/**
 * Get the templates to use as the endpoint for PSForum template parts
 *
 * @since PSForum (r3311)
 *
 * @uses apply_filters()
 * @uses psf_set_theme_compat_templates()
 * @uses psf_get_query_template()
 * @return string Path to template file
 */
function psf_get_theme_compat_templates() {
	$templates = array(
		'plugin-psforum.php',
		'psforum.php',
		'forums.php',
		'forum.php',
		'generic.php',
		'page.php',
		'single.php',
		'index.php'
	);
	return psf_get_query_template( 'psforum', $templates );
}
