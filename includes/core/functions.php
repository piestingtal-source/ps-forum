<?php

/**
 * PSForum Core Functions
 *
 * @package PSForum
 * @subpackage Functions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/** Versions ******************************************************************/

/**
 * Output the PSForum version
 *
 * @since PSForum (r3468)
 * @uses psf_get_version() To get the PSForum version
 */
function psf_version() {
	echo psf_get_version();
}
	/**
	 * Return the PSForum version
	 *
	 * @since PSForum (r3468)
	 * @retrun string The PSForum version
	 */
	function psf_get_version() {
		return psforum()->version;
	}

/**
 * Output the PSForum database version
 *
 * @since PSForum (r3468)
 * @uses psf_get_version() To get the PSForum version
 */
function psf_db_version() {
	echo psf_get_db_version();
}
	/**
	 * Return the PSForum database version
	 *
	 * @since PSForum (r3468)
	 * @retrun string The PSForum version
	 */
	function psf_get_db_version() {
		return psforum()->db_version;
	}

/**
 * Output the PSForum database version directly from the database
 *
 * @since PSForum (r3468)
 * @uses psf_get_version() To get the current PSForum version
 */
function psf_db_version_raw() {
	echo psf_get_db_version_raw();
}
	/**
	 * Return the PSForum database version directly from the database
	 *
	 * @since PSForum (r3468)
	 * @retrun string The current PSForum version
	 */
	function psf_get_db_version_raw() {
		return get_option( '_psf_db_version', '' );
	}

/** Post Meta *****************************************************************/

/**
 * Update a posts forum meta ID
 *
 * @since PSForum (r3181)
 *
 * @param int $post_id The post to update
 * @param int $forum_id The forum
 */
function psf_update_forum_id( $post_id, $forum_id ) {

	// Allow the forum ID to be updated 'just in time' before save
	$forum_id = apply_filters( 'psf_update_forum_id', $forum_id, $post_id );

	// Update the post meta forum ID
	update_post_meta( $post_id, '_psf_forum_id', (int) $forum_id );
}

/**
 * Update a posts topic meta ID
 *
 * @since PSForum (r3181)
 *
 * @param int $post_id The post to update
 * @param int $forum_id The forum
 */
function psf_update_topic_id( $post_id, $topic_id ) {

	// Allow the topic ID to be updated 'just in time' before save
	$topic_id = apply_filters( 'psf_update_topic_id', $topic_id, $post_id );

	// Update the post meta topic ID
	update_post_meta( $post_id, '_psf_topic_id', (int) $topic_id );
}

/**
 * Update a posts reply meta ID
 *
 * @since PSForum (r3181)
 *
 * @param int $post_id The post to update
 * @param int $forum_id The forum
 */
function psf_update_reply_id( $post_id, $reply_id ) {

	// Allow the reply ID to be updated 'just in time' before save
	$reply_id = apply_filters( 'psf_update_reply_id', $reply_id, $post_id );

	// Update the post meta reply ID
	update_post_meta( $post_id, '_psf_reply_id',(int) $reply_id );
}

/** Views *********************************************************************/

/**
 * Get the registered views
 *
 * Does nothing much other than return the {@link $psf->views} variable
 *
 * @since PSForum (r2789)
 *
 * @return array Views
 */
function psf_get_views() {
	return psforum()->views;
}

/**
 * Register a PSForum view
 *
 * @todo Implement feeds - See {@link http://trac.psforum.org/ticket/1422}
 *
 * @since PSForum (r2789)
 *
 * @param string $view View name
 * @param string $title View title
 * @param mixed $query_args {@link psf_has_topics()} arguments.
 * @param bool $feed Have a feed for the view? Defaults to true. NOT IMPLEMENTED
 * @param string $capability Capability that the current user must have
 * @uses sanitize_title() To sanitize the view name
 * @uses esc_html() To sanitize the view title
 * @return array The just registered (but processed) view
 */
function psf_register_view( $view, $title, $query_args = '', $feed = true, $capability = '' ) {

	// Bail if user does not have capability
	if ( ! empty( $capability ) && ! current_user_can( $capability ) )
		return false;

	$psf   = psforum();
	$view  = sanitize_title( $view );
	$title = esc_html( $title );

	if ( empty( $view ) || empty( $title ) )
		return false;

	$query_args = psf_parse_args( $query_args, '', 'register_view' );

	// Set show_stickies to false if it wasn't supplied
	if ( !isset( $query_args['show_stickies'] ) )
		$query_args['show_stickies'] = false;

	$psf->views[$view] = array(
		'title'  => $title,
		'query'  => $query_args,
		'feed'   => $feed
	);

	return $psf->views[$view];
}

/**
 * Deregister a PSForum view
 *
 * @since PSForum (r2789)
 *
 * @param string $view View name
 * @uses sanitize_title() To sanitize the view name
 * @return bool False if the view doesn't exist, true on success
 */
function psf_deregister_view( $view ) {
	$psf  = psforum();
	$view = sanitize_title( $view );

	if ( !isset( $psf->views[$view] ) )
		return false;

	unset( $psf->views[$view] );

	return true;
}

/**
 * Run the view's query
 *
 * @since PSForum (r2789)
 *
 * @param string $view Optional. View id
 * @param mixed $new_args New arguments. See {@link psf_has_topics()}
 * @uses psf_get_view_id() To get the view id
 * @uses psf_get_view_query_args() To get the view query args
 * @uses sanitize_title() To sanitize the view name
 * @uses psf_has_topics() To make the topics query
 * @return bool False if the view doesn't exist, otherwise if topics are there
 */
function psf_view_query( $view = '', $new_args = '' ) {

	$view = psf_get_view_id( $view );
	if ( empty( $view ) )
		return false;

	$query_args = psf_get_view_query_args( $view );

	if ( !empty( $new_args ) ) {
		$new_args   = psf_parse_args( $new_args, '', 'view_query' );
		$query_args = array_merge( $query_args, $new_args );
	}

	return psf_has_topics( $query_args );
}

/**
 * Return the view's query arguments
 *
 * @since PSForum (r2789)
 *
 * @param string $view View name
 * @uses psf_get_view_id() To get the view id
 * @return array Query arguments
 */
function psf_get_view_query_args( $view ) {
	$view   = psf_get_view_id( $view );
	$retval = !empty( $view ) ? psforum()->views[$view]['query'] : false;

	return apply_filters( 'psf_get_view_query_args', $retval, $view );
}

/** Errors ********************************************************************/

/**
 * Adds an error message to later be output in the theme
 *
 * @since PSForum (r3381)
 *
 * @see WP_Error()
 * @uses WP_Error::add();
 *
 * @param string $code Unique code for the error message
 * @param string $message Translated error message
 * @param string $data Any additional data passed with the error message
 */
function psf_add_error( $code = '', $message = '', $data = '' ) {
	psforum()->errors->add( $code, $message, $data );
}

/**
 * Check if error messages exist in queue
 *
 * @since PSForum (r3381)
 *
 * @see WP_Error()
 *
 * @uses is_wp_error()
 * @usese WP_Error::get_error_codes()
 */
function psf_has_errors() {
	$has_errors = psforum()->errors->get_error_codes() ? true : false;

	return apply_filters( 'psf_has_errors', $has_errors, psforum()->errors );
}

/** Mentions ******************************************************************/

/**
 * Set the pattern used for matching usernames for mentions.
 *
 * Moved into its own function to allow filtering of the regex pattern
 * anywhere mentions might be used.
 *
 * @since PSForum (r4997)
 * @deprecated 2.6.0 psf_make_clickable()
 *
 * @return string Pattern to match usernames with
 */
function psf_find_mentions_pattern() {
	return apply_filters( 'psf_find_mentions_pattern', '/[@]+([A-Za-z0-9-_\.@]+)\b/' );
}

/**
 * Searches through the content to locate usernames, designated by an @ sign.
 *
 * @since PSForum (r4323)
 * @deprecated 2.6.0 psf_make_clickable()
 *
 * @param string $content The content
 * @return bool|array $usernames Existing usernames. False if no matches.
 */
function psf_find_mentions( $content = '' ) {
	$pattern   = psf_find_mentions_pattern();
	preg_match_all( $pattern, $content, $usernames );
	$usernames = array_unique( array_filter( $usernames[1] ) );

	// Bail if no usernames
	if ( empty( $usernames ) ) {
		$usernames = false;
	}

	return apply_filters( 'psf_find_mentions', $usernames, $pattern, $content );
}

/**
 * Finds and links @-mentioned users in the content
 *
 * @since PSForum (r4323)
 * @deprecated 2.6.0 psf_make_clickable()
 *
 * @uses psf_find_mentions() To get usernames in content areas
 * @return string $content Content filtered for mentions
 */
function psf_mention_filter( $content = '' ) {

	// Get Usernames and bail if none exist
	$usernames = psf_find_mentions( $content );
	if ( empty( $usernames ) )
		return $content;

	// Loop through usernames and link to profiles
	foreach ( (array) $usernames as $username ) {

		// Skip if username does not exist or user is not active
		$user = get_user_by( 'slug', $username );
		if ( empty( $user->ID ) || psf_is_user_inactive( $user->ID ) )
			continue;

		// Replace name in content
		$content = preg_replace( '/(@' . $username . '\b)/', sprintf( '<a href="%1$s" rel="nofollow">@%2$s</a>', psf_get_user_profile_url( $user->ID ), $username ), $content );
	}

	// Return modified content
	return $content;
}

/** Post Statuses *************************************************************/

/**
 * Return the public post status ID
 *
 * @since PSForum (r3504)
 *
 * @return string
 */
function psf_get_public_status_id() {
	return psforum()->public_status_id;
}

/**
 * Return the pending post status ID
 *
 * @since PSForum (r3581)
 *
 * @return string
 */
function psf_get_pending_status_id() {
	return psforum()->pending_status_id;
}

/**
 * Return the private post status ID
 *
 * @since PSForum (r3504)
 *
 * @return string
 */
function psf_get_private_status_id() {
	return psforum()->private_status_id;
}

/**
 * Return the hidden post status ID
 *
 * @since PSForum (r3504)
 *
 * @return string
 */
function psf_get_hidden_status_id() {
	return psforum()->hidden_status_id;
}

/**
 * Return the closed post status ID
 *
 * @since PSForum (r3504)
 *
 * @return string
 */
function psf_get_closed_status_id() {
	return psforum()->closed_status_id;
}

/**
 * Return the spam post status ID
 *
 * @since PSForum (r3504)
 *
 * @return string
 */
function psf_get_spam_status_id() {
	return psforum()->spam_status_id;
}

/**
 * Return the trash post status ID
 *
 * @since PSForum (r3504)
 *
 * @return string
 */
function psf_get_trash_status_id() {
	return psforum()->trash_status_id;
}

/**
 * Return the orphan post status ID
 *
 * @since PSForum (r3504)
 *
 * @return string
 */
function psf_get_orphan_status_id() {
	return psforum()->orphan_status_id;
}

/** Rewrite IDs ***************************************************************/

/**
 * Return the unique ID for user profile rewrite rules
 *
 * @since PSForum (r3762)
 * @return string
 */
function psf_get_user_rewrite_id() {
	return psforum()->user_id;
}

/**
 * Return the unique ID for all edit rewrite rules (forum|topic|reply|tag|user)
 *
 * @since PSForum (r3762)
 * @return string
 */
function psf_get_edit_rewrite_id() {
	return psforum()->edit_id;
}

/**
 * Return the unique ID for all search rewrite rules
 *
 * @since PSForum (r4579)
 *
 * @return string
 */
function psf_get_search_rewrite_id() {
	return psforum()->search_id;
}

/**
 * Return the unique ID for user topics rewrite rules
 *
 * @since PSForum (r4321)
 * @return string
 */
function psf_get_user_topics_rewrite_id() {
	return psforum()->tops_id;
}

/**
 * Return the unique ID for user replies rewrite rules
 *
 * @since PSForum (r4321)
 * @return string
 */
function psf_get_user_replies_rewrite_id() {
	return psforum()->reps_id;
}

/**
 * Return the unique ID for user caps rewrite rules
 *
 * @since PSForum (r4181)
 * @return string
 */
function psf_get_user_favorites_rewrite_id() {
	return psforum()->favs_id;
}

/**
 * Return the unique ID for user caps rewrite rules
 *
 * @since PSForum (r4181)
 * @return string
 */
function psf_get_user_subscriptions_rewrite_id() {
	return psforum()->subs_id;
}

/**
 * Return the unique ID for topic view rewrite rules
 *
 * @since PSForum (r3762)
 * @return string
 */
function psf_get_view_rewrite_id() {
	return psforum()->view_id;
}

/** Rewrite Extras ************************************************************/

/**
 * Get the id used for paginated requests
 *
 * @since PSForum (r4926)
 * @return string
 */
function psf_get_paged_rewrite_id() {
	return psforum()->paged_id;
}

/**
 * Get the slug used for paginated requests
 *
 * @since PSForum (r4926)
 * @global object $wp_rewrite The WP_Rewrite object
 * @return string
 */
function psf_get_paged_slug() {
	global $wp_rewrite;
	return $wp_rewrite->pagination_base;
}

/**
 * Delete a blogs rewrite rules, so that they are automatically rebuilt on
 * the subsequent page load.
 *
 * @since PSForum (r4198)
 */
function psf_delete_rewrite_rules() {
	delete_option( 'rewrite_rules' );
}

/** Requests ******************************************************************/

/**
 * Return true|false if this is a POST request
 *
 * @since PSForum (r4790)
 * @return bool
 */
function psf_is_post_request() {
	return (bool) ( 'POST' === strtoupper( $_SERVER['REQUEST_METHOD'] ) );
}

/**
 * Return true|false if this is a GET request
 *
 * @since PSForum (r4790)
 * @return bool
 */
function psf_is_get_request() {
	return (bool) ( 'GET' === strtoupper( $_SERVER['REQUEST_METHOD'] ) );
}

