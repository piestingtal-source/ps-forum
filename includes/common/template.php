<?php

/**
 * PSForum Common Template Tags
 *
 * Common template tags are ones that are used by more than one component, like
 * forums, topics, replies, users, topic tags, etc...
 *
 * @package PSForum
 * @subpackage TemplateTags
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/** URLs **********************************************************************/

/**
 * Ouput the forum URL
 *
 * @since PSForum (r3979)
 *
 * @uses psf_get_forums_url() To get the forums URL
 * @param string $path Additional path with leading slash
 */
function psf_forums_url( $path = '/' ) {
	echo esc_url( psf_get_forums_url( $path ) );
}
	/**
	 * Return the forum URL
	 *
	 * @since PSForum (r3979)
	 *
	 * @uses home_url() To get the home URL
	 * @uses psf_get_root_slug() To get the forum root location
	 * @param string $path Additional path with leading slash
	 */
	function psf_get_forums_url( $path = '/' ) {
		return home_url( psf_get_root_slug() . $path );
	}

/**
 * Ouput the forum URL
 *
 * @since PSForum (r3979)
 *
 * @uses psf_get_topics_url() To get the topics URL
 * @param string $path Additional path with leading slash
 */
function psf_topics_url( $path = '/' ) {
	echo esc_url( psf_get_topics_url( $path ) );
}
	/**
	 * Return the forum URL
	 *
	 * @since PSForum (r3979)
	 *
	 * @uses home_url() To get the home URL
	 * @uses psf_get_topic_archive_slug() To get the topics archive location
	 * @param string $path Additional path with leading slash
	 * @return The URL to the topics archive
	 */
	function psf_get_topics_url( $path = '/' ) {
		return home_url( psf_get_topic_archive_slug() . $path );
	}

/** Add-on Actions ************************************************************/

/**
 * Add our custom head action to wp_head
 *
 * @since PSForum (r2464)
 *
 * @uses do_action() Calls 'psf_head'
*/
function psf_head() {
	do_action( 'psf_head' );
}

/**
 * Add our custom head action to wp_head
 *
 * @since PSForum (r2464)
 *
 * @uses do_action() Calls 'psf_footer'
 */
function psf_footer() {
	do_action( 'psf_footer' );
}

/** is_ ***********************************************************************/

/**
 * Check if current site is public
 *
 * @since PSForum (r3398)
 *
 * @param int $site_id
 * @uses get_current_blog_id()
 * @uses get_blog_option()
 * @uses apply_filters()
 * @return bool True if site is public, false if private
 */
function psf_is_site_public( $site_id = 0 ) {

	// Get the current site ID
	if ( empty( $site_id ) )
		$site_id = get_current_blog_id();

	// Get the site visibility setting
	$public = get_blog_option( $site_id, 'blog_public', 1 );

	return (bool) apply_filters( 'psf_is_site_public', $public, $site_id );
}

/**
 * Check if current page is a PSForum forum
 *
 * @since PSForum (r2549)
 *
 * @param int $post_id Possible post_id to check
 * @uses psf_get_forum_post_type() To get the forum post type
 * @return bool True if it's a forum page, false if not
 */
function psf_is_forum( $post_id = 0 ) {

	// Assume false
	$retval = false;

	// Supplied ID is a forum
	if ( !empty( $post_id ) && ( psf_get_forum_post_type() === get_post_type( $post_id ) ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_forum', $retval, $post_id );
}

/**
 * Check if we are viewing a forum archive.
 *
 * @since PSForum (r3251)
 *
 * @uses is_post_type_archive() To check if we are looking at the forum archive
 * @uses psf_get_forum_post_type() To get the forum post type ID
 *
 * @return bool
 */
function psf_is_forum_archive() {
	global $wp_query;

	// Default to false
	$retval = false;

	// In forum archive
	if ( is_post_type_archive( psf_get_forum_post_type() ) || psf_is_query_name( 'psf_forum_archive' ) || !empty( $wp_query->psf_show_topics_on_root ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_forum_archive', $retval );
}

/**
 * Viewing a single forum
 *
 * @since PSForum (r3338)
 *
 * @uses is_single()
 * @uses psf_get_forum_post_type()
 * @uses get_post_type()
 * @uses apply_filters()
 *
 * @return bool
 */
function psf_is_single_forum() {

	// Assume false
	$retval = false;

	// Edit is not a single forum
	if ( psf_is_forum_edit() )
		return false;

	// Single and a match
	if ( is_singular( psf_get_forum_post_type() ) || psf_is_query_name( 'psf_single_forum' ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_single_forum', $retval );
}

/**
 * Check if current page is a forum edit page
 *
 * @since PSForum (r3553)
 *
 * @uses WP_Query Checks if WP_Query::psf_is_forum_edit is true
 * @return bool True if it's the forum edit page, false if not
 */
function psf_is_forum_edit() {
	global $wp_query, $pagenow;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->psf_is_forum_edit ) && ( $wp_query->psf_is_forum_edit === true ) )
		$retval = true;

	// Editing in admin
	elseif ( is_admin() && ( 'post.php' === $pagenow ) && ( get_post_type() === psf_get_forum_post_type() ) && ( !empty( $_GET['action'] ) && ( 'edit' === $_GET['action'] ) ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_forum_edit', $retval );
}

/**
 * Check if current page is a PSForum topic
 *
 * @since PSForum (r2549)
 *
 * @param int $post_id Possible post_id to check
 * @uses psf_get_topic_post_type() To get the topic post type
 * @uses get_post_type() To get the post type of the post id
 * @return bool True if it's a topic page, false if not
 */
function psf_is_topic( $post_id = 0 ) {

	// Assume false
	$retval = false;

	// Supplied ID is a topic
	if ( !empty( $post_id ) && ( psf_get_topic_post_type() === get_post_type( $post_id ) ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_topic', $retval, $post_id );
}

/**
 * Viewing a single topic
 *
 * @since PSForum (r3338)
 *
 * @uses is_single()
 * @uses psf_get_topic_post_type()
 * @uses get_post_type()
 * @uses apply_filters()
 *
 * @return bool
 */
function psf_is_single_topic() {

	// Assume false
	$retval = false;

	// Edit is not a single topic
	if ( psf_is_topic_edit() )
		return false;

	// Single and a match
	if ( is_singular( psf_get_topic_post_type() ) || psf_is_query_name( 'psf_single_topic' ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_single_topic', $retval );
}

/**
 * Check if we are viewing a topic archive.
 *
 * @since PSForum (r3251)
 *
 * @uses is_post_type_archive() To check if we are looking at the topic archive
 * @uses psf_get_topic_post_type() To get the topic post type ID
 *
 * @return bool
 */
function psf_is_topic_archive() {

	// Default to false
	$retval = false;

	// In topic archive
	if ( is_post_type_archive( psf_get_topic_post_type() ) || psf_is_query_name( 'psf_topic_archive' ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_topic_archive', $retval );
}

/**
 * Check if current page is a topic edit page
 *
 * @since PSForum (r2753)
 *
 * @uses WP_Query Checks if WP_Query::psf_is_topic_edit is true
 * @return bool True if it's the topic edit page, false if not
 */
function psf_is_topic_edit() {
	global $wp_query, $pagenow;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->psf_is_topic_edit ) && ( $wp_query->psf_is_topic_edit === true ) )
		$retval = true;

	// Editing in admin
	elseif ( is_admin() && ( 'post.php' === $pagenow ) && ( get_post_type() === psf_get_topic_post_type() ) && ( !empty( $_GET['action'] ) && ( 'edit' === $_GET['action'] ) ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_topic_edit', $retval );
}

/**
 * Check if current page is a topic merge page
 *
 * @since PSForum (r2756)
 *
 * @uses psf_is_topic_edit() To check if it's a topic edit page
 * @return bool True if it's the topic merge page, false if not
 */
function psf_is_topic_merge() {

	// Assume false
	$retval = false;

	// Check topic edit and GET params
	if ( psf_is_topic_edit() && !empty( $_GET['action'] ) && ( 'merge' === $_GET['action'] ) )
		return true;

	return (bool) apply_filters( 'psf_is_topic_merge', $retval );
}

/**
 * Check if current page is a topic split page
 *
 * @since PSForum (r2756)
 *
 * @uses psf_is_topic_edit() To check if it's a topic edit page
 * @return bool True if it's the topic split page, false if not
 */
function psf_is_topic_split() {

	// Assume false
	$retval = false;

	// Check topic edit and GET params
	if ( psf_is_topic_edit() && !empty( $_GET['action'] ) && ( 'split' === $_GET['action'] ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_topic_split', $retval );
}

/**
 * Check if the current page is a topic tag
 *
 * @since PSForum (r3311)
 *
 * @return bool True if it's a topic tag, false if not
 */
function psf_is_topic_tag() {

	// Bail if topic-tags are off
	if ( ! psf_allow_topic_tags() )
		return false;

	// Return false if editing a topic tag
	if ( psf_is_topic_tag_edit() )
		return false;

	// Assume false
	$retval = false;

	// Check tax and query vars
	if ( is_tax( psf_get_topic_tag_tax_id() ) || !empty( psforum()->topic_query->is_tax ) || get_query_var( 'psf_topic_tag' ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_topic_tag', $retval );
}

/**
 * Check if the current page is editing a topic tag
 *
 * @since PSForum (r3346)
 *
 * @uses WP_Query Checks if WP_Query::psf_is_topic_tag_edit is true
 * @return bool True if editing a topic tag, false if not
 */
function psf_is_topic_tag_edit() {
	global $wp_query, $pagenow, $taxnow;

	// Bail if topic-tags are off
	if ( ! psf_allow_topic_tags() )
		return false;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->psf_is_topic_tag_edit ) && ( true === $wp_query->psf_is_topic_tag_edit ) )
		$retval = true;

	// Editing in admin
	elseif ( is_admin() && ( 'edit-tags.php' === $pagenow ) && ( psf_get_topic_tag_tax_id() === $taxnow ) && ( !empty( $_GET['action'] ) && ( 'edit' === $_GET['action'] ) ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_topic_tag_edit', $retval );
}

/**
 * Check if the current post type is one of PSForum's
 *
 * @since PSForum (r3311)
 *
 * @param mixed $the_post Optional. Post object or post ID.
 * @uses get_post_type()
 * @uses psf_get_forum_post_type()
 * @uses psf_get_topic_post_type()
 * @uses psf_get_reply_post_type()
 *
 * @return bool
 */
function psf_is_custom_post_type( $the_post = false ) {

	// Assume false
	$retval = false;

	// Viewing one of the PSForum post types
	if ( in_array( get_post_type( $the_post ), array(
		psf_get_forum_post_type(),
		psf_get_topic_post_type(),
		psf_get_reply_post_type()
	) ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_custom_post_type', $retval, $the_post );
}

/**
 * Check if current page is a PSForum reply
 *
 * @since PSForum (r2549)
 *
 * @param int $post_id Possible post_id to check
 * @uses psf_get_reply_post_type() To get the reply post type
 * @uses get_post_type() To get the post type of the post id
 * @return bool True if it's a reply page, false if not
 */
function psf_is_reply( $post_id = 0 ) {

	// Assume false
	$retval = false;

	// Supplied ID is a reply
	if ( !empty( $post_id ) && ( psf_get_reply_post_type() === get_post_type( $post_id ) ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_reply', $retval, $post_id );
}

/**
 * Check if current page is a reply edit page
 *
 * @since PSForum (r2753)
 *
 * @uses WP_Query Checks if WP_Query::psf_is_reply_edit is true
 * @return bool True if it's the reply edit page, false if not
 */
function psf_is_reply_edit() {
	global $wp_query, $pagenow;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->psf_is_reply_edit ) && ( true === $wp_query->psf_is_reply_edit ) )
		$retval = true;

	// Editing in admin
	elseif ( is_admin() && ( 'post.php' === $pagenow ) && ( get_post_type() === psf_get_reply_post_type() ) && ( !empty( $_GET['action'] ) && ( 'edit' === $_GET['action'] ) ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_reply_edit', $retval );
}

/**
 * Check if current page is a reply move page
 *
 * @uses psf_is_reply_move() To check if it's a reply move page
 * @return bool True if it's the reply move page, false if not
 */
function psf_is_reply_move() {

	// Assume false
	$retval = false;

	// Check reply edit and GET params
	if ( psf_is_reply_edit() && !empty( $_GET['action'] ) && ( 'move' === $_GET['action'] ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_reply_move', $retval );
}

/**
 * Viewing a single reply
 *
 * @since PSForum (r3344)
 *
 * @uses is_single()
 * @uses psf_get_reply_post_type()
 * @uses get_post_type()
 * @uses apply_filters()
 *
 * @return bool
 */
function psf_is_single_reply() {

	// Assume false
	$retval = false;

	// Edit is not a single reply
	if ( psf_is_reply_edit() )
		return false;

	// Single and a match
	if ( is_singular( psf_get_reply_post_type() ) || ( psf_is_query_name( 'psf_single_reply' ) ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_single_reply', $retval );
}

/**
 * Check if current page is a PSForum user's favorites page (profile page)
 *
 * @since PSForum (r2652)
 *
 * @return bool True if it's the favorites page, false if not
 */
function psf_is_favorites() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->psf_is_single_user_favs ) && ( true === $wp_query->psf_is_single_user_favs ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_favorites', $retval );
}

/**
 * Check if current page is a PSForum user's subscriptions page (profile page)
 *
 * @since PSForum (r2652)
 *
 * @return bool True if it's the subscriptions page, false if not
 */
function psf_is_subscriptions() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->psf_is_single_user_subs ) && ( true === $wp_query->psf_is_single_user_subs ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_subscriptions', $retval );
}

/**
 * Check if current page shows the topics created by a PSForum user (profile
 * page)
 *
 * @since PSForum (r2688)
 *
 * @return bool True if it's the topics created page, false if not
 */
function psf_is_topics_created() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->psf_is_single_user_topics ) && ( true === $wp_query->psf_is_single_user_topics ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_topics_created', $retval );
}

/**
 * Check if current page shows the topics created by a PSForum user (profile
 * page)
 *
 * @since PSForum (r4225)
 *
 * @return bool True if it's the topics created page, false if not
 */
function psf_is_replies_created() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->psf_is_single_user_replies ) && ( true === $wp_query->psf_is_single_user_replies ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_replies_created', $retval );
}

/**
 * Check if current page is the currently logged in users author page
 *
 * @since PSForum (r2688)
 * @uses psf_is_single_user() Check query variable
 * @uses is_user_logged_in() Must be logged in to be home
 * @uses psf_get_displayed_user_id()
 * @uses psf_get_current_user_id()
 * @return bool True if it's the user's home, false if not
 */
function psf_is_user_home() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->psf_is_single_user_home ) && ( true === $wp_query->psf_is_single_user_home ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_user_home', $retval );
}

/**
 * Check if current page is the currently logged in users author edit page
 *
 * @since PSForum (r3918)
 * @uses psf_is_single_user_edit() Check query variable
 * @uses is_user_logged_in() Must be logged in to be home
 * @uses psf_get_displayed_user_id()
 * @uses psf_get_current_user_id()
 * @return bool True if it's the user's home, false if not
 */
function psf_is_user_home_edit() {

	// Assume false
	$retval = false;

	if ( psf_is_user_home() && psf_is_single_user_edit() )
		$retval = true;

	return (bool) apply_filters( 'psf_is_user_home_edit', $retval );
}

/**
 * Check if current page is a user profile page
 *
 * @since PSForum (r2688)
 *
 * @uses WP_Query Checks if WP_Query::psf_is_single_user is set to true
 * @return bool True if it's a user's profile page, false if not
 */
function psf_is_single_user() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->psf_is_single_user ) && ( true === $wp_query->psf_is_single_user ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_single_user', $retval );
}

/**
 * Check if current page is a user profile edit page
 *
 * @since PSForum (r2688)
 *
 * @uses WP_Query Checks if WP_Query::psf_is_single_user_edit is set to true
 * @return bool True if it's a user's profile edit page, false if not
 */
function psf_is_single_user_edit() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->psf_is_single_user_edit ) && ( true === $wp_query->psf_is_single_user_edit ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_single_user_edit', $retval );
}

/**
 * Check if current page is a user profile page
 *
 * @since PSForum (r4225)
 *
 * @uses WP_Query Checks if WP_Query::psf_is_single_user_profile is set to true
 * @return bool True if it's a user's profile page, false if not
 */
function psf_is_single_user_profile() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->psf_is_single_user_profile ) && ( true === $wp_query->psf_is_single_user_profile ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_single_user_profile', $retval );
}

/**
 * Check if current page is a user topics created page
 *
 * @since PSForum (r4225)
 *
 * @uses WP_Query Checks if WP_Query::psf_is_single_user_topics is set to true
 * @return bool True if it's a user's topics page, false if not
 */
function psf_is_single_user_topics() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->psf_is_single_user_topics ) && ( true === $wp_query->psf_is_single_user_topics ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_single_user_topics', $retval );
}

/**
 * Check if current page is a user replies created page
 *
 * @since PSForum (r4225)
 *
 * @uses WP_Query Checks if WP_Query::psf_is_single_user_replies is set to true
 * @return bool True if it's a user's replies page, false if not
 */
function psf_is_single_user_replies() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->psf_is_single_user_replies ) && ( true === $wp_query->psf_is_single_user_replies ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_single_user_replies', $retval );
}

/**
 * Check if current page is a view page
 *
 * @since PSForum (r2789)
 *
 * @global WP_Query $wp_query To check if WP_Query::psf_is_view is true
 * @uses psf_is_query_name() To get the query name
 * @return bool Is it a view page?
 */
function psf_is_single_view() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->psf_is_view ) && ( true === $wp_query->psf_is_view ) )
		$retval = true;

	// Check query name
	if ( empty( $retval ) && psf_is_query_name( 'psf_single_view' ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_single_view', $retval );
}

/**
 * Check if current page is a search page
 *
 * @since PSForum (r4579)
 *
 * @global WP_Query $wp_query To check if WP_Query::psf_is_search is true
 * @uses psf_is_query_name() To get the query name
 * @return bool Is it a search page?
 */
function psf_is_search() {
	global $wp_query;

	// Bail if search is disabled
	if ( ! psf_allow_search() )
		return false;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->psf_is_search ) && ( true === $wp_query->psf_is_search ) )
		$retval = true;

	// Check query name
	if ( empty( $retval ) && psf_is_query_name( psf_get_search_rewrite_id() ) )
		$retval = true;

	// Check $_GET
	if ( empty( $retval ) && isset( $_REQUEST[ psf_get_search_rewrite_id() ] ) && empty( $_REQUEST[ psf_get_search_rewrite_id() ] ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_search', $retval );
}

/**
 * Check if current page is a search results page
 *
 * @since PSForum (r4919)
 *
 * @global WP_Query $wp_query To check if WP_Query::psf_is_search is true
 * @uses psf_is_query_name() To get the query name
 * @return bool Is it a search page?
 */
function psf_is_search_results() {
	global $wp_query;

	// Bail if search is disabled
	if ( ! psf_allow_search() )
		return false;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->psf_search_terms ) )
		$retval = true;

	// Check query name
	if ( empty( $retval ) && psf_is_query_name( 'psf_search_results' ) )
		$retval = true;

	// Check $_REQUEST
	if ( empty( $retval ) && !empty( $_REQUEST[ psf_get_search_rewrite_id() ] ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_search_results', $retval );
}

/**
 * Check if current page is an edit page
 *
 * @since PSForum (r3585)
 *
 * @uses WP_Query Checks if WP_Query::psf_is_edit is true
 * @return bool True if it's the edit page, false if not
 */
function psf_is_edit() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->psf_is_edit ) && ( $wp_query->psf_is_edit === true ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_edit', $retval );
}

/**
 * Use the above is_() functions to output a body class for each scenario
 *
 * @since PSForum (r2926)
 *
 * @param array $wp_classes
 * @param array $custom_classes
 * @uses psf_is_single_forum()
 * @uses psf_is_single_topic()
 * @uses psf_is_topic_edit()
 * @uses psf_is_topic_merge()
 * @uses psf_is_topic_split()
 * @uses psf_is_single_reply()
 * @uses psf_is_reply_edit()
 * @uses psf_is_reply_move()
 * @uses psf_is_single_view()
 * @uses psf_is_single_user_edit()
 * @uses psf_is_single_user()
 * @uses psf_is_user_home()
 * @uses psf_is_subscriptions()
 * @uses psf_is_favorites()
 * @uses psf_is_topics_created()
 * @uses psf_is_forum_archive()
 * @uses psf_is_topic_archive()
 * @uses psf_is_topic_tag()
 * @uses psf_is_topic_tag_edit()
 * @uses psf_get_topic_tag_tax_id()
 * @uses psf_get_topic_tag_slug()
 * @uses psf_get_topic_tag_id()
 * @return array Body Classes
 */
function psf_body_class( $wp_classes, $custom_classes = false ) {

	$psf_classes = array();

	/** Archives **************************************************************/

	if ( psf_is_forum_archive() ) {
		$psf_classes[] = psf_get_forum_post_type() . '-archive';

	} elseif ( psf_is_topic_archive() ) {
		$psf_classes[] = psf_get_topic_post_type() . '-archive';

	/** Topic Tags ************************************************************/

	} elseif ( psf_is_topic_tag() ) {
		$psf_classes[] = psf_get_topic_tag_tax_id();
		$psf_classes[] = psf_get_topic_tag_tax_id() . '-' . psf_get_topic_tag_slug();
		$psf_classes[] = psf_get_topic_tag_tax_id() . '-' . psf_get_topic_tag_id();
	} elseif ( psf_is_topic_tag_edit() ) {
		$psf_classes[] = psf_get_topic_tag_tax_id() . '-edit';
		$psf_classes[] = psf_get_topic_tag_tax_id() . '-' . psf_get_topic_tag_slug() . '-edit';
		$psf_classes[] = psf_get_topic_tag_tax_id() . '-' . psf_get_topic_tag_id()   . '-edit';

	/** Components ************************************************************/

	} elseif ( psf_is_single_forum() ) {
		$psf_classes[] = psf_get_forum_post_type();

	} elseif ( psf_is_single_topic() ) {
		$psf_classes[] = psf_get_topic_post_type();

	} elseif ( psf_is_single_reply() ) {
		$psf_classes[] = psf_get_reply_post_type();

	} elseif ( psf_is_topic_edit() ) {
		$psf_classes[] = psf_get_topic_post_type() . '-edit';

	} elseif ( psf_is_topic_merge() ) {
		$psf_classes[] = psf_get_topic_post_type() . '-merge';

	} elseif ( psf_is_topic_split() ) {
		$psf_classes[] = psf_get_topic_post_type() . '-split';

	} elseif ( psf_is_reply_edit() ) {
		$psf_classes[] = psf_get_reply_post_type() . '-edit';

	} elseif ( psf_is_reply_move() ) {
		$psf_classes[] = psf_get_reply_post_type() . '-move';

	} elseif ( psf_is_single_view() ) {
		$psf_classes[] = 'psf-view';

	/** User ******************************************************************/

	} elseif ( psf_is_single_user_edit() ) {
		$psf_classes[] = 'psf-user-edit';
		$psf_classes[] = 'single';
		$psf_classes[] = 'singular';

	} elseif ( psf_is_single_user() ) {
		$psf_classes[] = 'psf-user-page';
		$psf_classes[] = 'single';
		$psf_classes[] = 'singular';

	} elseif ( psf_is_user_home() ) {
		$psf_classes[] = 'psf-user-home';
		$psf_classes[] = 'single';
		$psf_classes[] = 'singular';

	} elseif ( psf_is_user_home_edit() ) {
		$psf_classes[] = 'psf-user-home-edit';
		$psf_classes[] = 'single';
		$psf_classes[] = 'singular';

	} elseif ( psf_is_topics_created() ) {
		$psf_classes[] = 'psf-topics-created';
		$psf_classes[] = 'single';
		$psf_classes[] = 'singular';

	} elseif ( psf_is_favorites() ) {
		$psf_classes[] = 'psf-favorites';
		$psf_classes[] = 'single';
		$psf_classes[] = 'singular';

	} elseif ( psf_is_subscriptions() ) {
		$psf_classes[] = 'psf-subscriptions';
		$psf_classes[] = 'single';
		$psf_classes[] = 'singular';

	/** Search ****************************************************************/

	} elseif ( psf_is_search() ) {
		$psf_classes[] = 'psf-search';
		$psf_classes[] = 'forum-search';

	} elseif ( psf_is_search_results() ) {
		$psf_classes[] = 'psf-search-results';
		$psf_classes[] = 'forum-search-results';
	}

	/** Clean up **************************************************************/

	// Add PSForum class if we are within a PSForum page
	if ( !empty( $psf_classes ) ) {
		$psf_classes[] = 'psforum';
	}

	// Merge WP classes with PSForum classes and remove any duplicates
	$classes = array_unique( array_merge( (array) $psf_classes, (array) $wp_classes ) );

	// Deprecated filter (do not use)
	$classes = apply_filters( 'psf_get_the_body_class', $classes, $psf_classes, $wp_classes, $custom_classes );

	return apply_filters( 'psf_body_class', $classes, $psf_classes, $wp_classes, $custom_classes );
}

/**
 * Use the above is_() functions to return if in any PSForum page
 *
 * @since PSForum (r3344)
 *
 * @uses psf_is_single_forum()
 * @uses psf_is_single_topic()
 * @uses psf_is_topic_edit()
 * @uses psf_is_topic_merge()
 * @uses psf_is_topic_split()
 * @uses psf_is_single_reply()
 * @uses psf_is_reply_edit()
 * @uses psf_is_reply_move()
 * @uses psf_is_single_view()
 * @uses psf_is_single_user_edit()
 * @uses psf_is_single_user()
 * @uses psf_is_user_home()
 * @uses psf_is_subscriptions()
 * @uses psf_is_favorites()
 * @uses psf_is_topics_created()
 * @return bool In a PSForum page
 */
function is_psforum() {

	// Defalt to false
	$retval = false;

	/** Archives **************************************************************/

	if ( psf_is_forum_archive() ) {
		$retval = true;

	} elseif ( psf_is_topic_archive() ) {
		$retval = true;

	/** Topic Tags ************************************************************/

	} elseif ( psf_is_topic_tag() ) {
		$retval = true;

	} elseif ( psf_is_topic_tag_edit() ) {
		$retval = true;

	/** Components ************************************************************/

	} elseif ( psf_is_single_forum() ) {
		$retval = true;

	} elseif ( psf_is_single_topic() ) {
		$retval = true;

	} elseif ( psf_is_single_reply() ) {
		$retval = true;

	} elseif ( psf_is_topic_edit() ) {
		$retval = true;

	} elseif ( psf_is_topic_merge() ) {
		$retval = true;

	} elseif ( psf_is_topic_split() ) {
		$retval = true;

	} elseif ( psf_is_reply_edit() ) {
		$retval = true;

	} elseif ( psf_is_reply_move() ) {
		$retval = true;

	} elseif ( psf_is_single_view() ) {
		$retval = true;

	/** User ******************************************************************/

	} elseif ( psf_is_single_user_edit() ) {
		$retval = true;

	} elseif ( psf_is_single_user() ) {
		$retval = true;

	} elseif ( psf_is_user_home() ) {
		$retval = true;

	} elseif ( psf_is_user_home_edit() ) {
		$retval = true;

	} elseif ( psf_is_topics_created() ) {
		$retval = true;

	} elseif ( psf_is_favorites() ) {
		$retval = true;

	} elseif ( psf_is_subscriptions() ) {
		$retval = true;

	/** Search ****************************************************************/

	} elseif ( psf_is_search() ) {
		$retval = true;

	} elseif ( psf_is_search_results() ) {
		$retval = true;
	}

	/** Done ******************************************************************/

	return (bool) apply_filters( 'is_psforum', $retval );
}

/** Forms *********************************************************************/

/**
 * Output the login form action url
 *
 * @since PSForum (r2815)
 *
 * @param string $args Pass a URL to redirect to
 * @uses add_query_arg() To add a arg to the url
 * @uses site_url() Toget the site url
 * @uses apply_filters() Calls 'psf_wp_login_action' with the url and args
 */
function psf_wp_login_action( $args = '' ) {
	echo esc_url( psf_get_wp_login_action( $args ) );
}

	/**
	 * Return the login form action url
	 *
	 * @since PSForum (r5691)
	 *
	 * @param string $args Pass a URL to redirect to
	 * @uses add_query_arg() To add a arg to the url
	 * @uses site_url() Toget the site url
	 * @uses apply_filters() Calls 'psf_wp_login_action' with the url and args
	 */
	function psf_get_wp_login_action( $args = '' ) {

		// Parse arguments against default values
		$r = psf_parse_args( $args, array(
			'action'  => '',
			'context' => '',
			'url'     => 'wp-login.php'
		), 'login_action' );

		// Add action as query arg
		if ( !empty( $r['action'] ) ) {
			$login_url = add_query_arg( array( 'action' => $r['action'] ), $r['url'] );

		// No query arg
		} else {
			$login_url = $r['url'];
		}

		$login_url = site_url( $login_url, $r['context'] );

		return apply_filters( 'psf_get_wp_login_action', $login_url, $r, $args );
	}

/**
 * Output hidden request URI field for user forms.
 *
 * The referer link is the current Request URI from the server super global. To
 * check the field manually, use psf_get_redirect_to().
 *
 * @since PSForum (r2815)
 *
 * @param string $redirect_to Pass a URL to redirect to
 *
 * @uses wp_get_referer() To get the referer
 * @uses remove_query_arg() To remove the `loggedout` argument
 * @uses esc_url() To escape the url
 * @uses apply_filters() Calls 'psf_redirect_to_field', passes field and to
 */
function psf_redirect_to_field( $redirect_to = '' ) {

	// Make sure we are directing somewhere
	if ( empty( $redirect_to ) ) {
		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$redirect_to = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		} else {
			$redirect_to = wp_get_referer();
		}
	}

	// Remove loggedout query arg if it's there
	$redirect_to    = remove_query_arg( 'loggedout', $redirect_to );
	$redirect_field = '<input type="hidden" id="psf_redirect_to" name="redirect_to" value="' . esc_url( $redirect_to ) . '" />';

	echo apply_filters( 'psf_redirect_to_field', $redirect_field, $redirect_to );
}

/**
 * Echo sanitized $_REQUEST value.
 *
 * Use the $input_type parameter to properly process the value. This
 * ensures correct sanitization of the value for the receiving input.
 *
 * @since PSForum (r2815)
 *
 * @param string $request Name of $_REQUEST to look for
 * @param string $input_type Type of input. Default: text. Accepts:
 *                            textarea|password|select|radio|checkbox
 * @uses psf_get_sanitize_val() To sanitize the value.
 */
function psf_sanitize_val( $request = '', $input_type = 'text' ) {
	echo psf_get_sanitize_val( $request, $input_type );
}
	/**
	 * Return sanitized $_REQUEST value.
	 *
	 * Use the $input_type parameter to properly process the value. This
	 * ensures correct sanitization of the value for the receiving input.
	 *
	 * @since PSForum (r2815)
	 *
	 * @param string $request Name of $_REQUEST to look for
	 * @param string $input_type Type of input. Default: text. Accepts:
	 *                            textarea|password|select|radio|checkbox
	 * @uses esc_attr() To escape the string
	 * @uses apply_filters() Calls 'psf_get_sanitize_val' with the sanitized
	 *                        value, request and input type
	 * @return string Sanitized value ready for screen display
	 */
	function psf_get_sanitize_val( $request = '', $input_type = 'text' ) {

		// Check that requested
		if ( empty( $_REQUEST[$request] ) )
			return false;

		// Set request varaible
		$pre_ret_val = $_REQUEST[$request];

		// Treat different kinds of fields in different ways
		switch ( $input_type ) {
			case 'text'     :
			case 'textarea' :
				$retval = esc_attr( stripslashes( $pre_ret_val ) );
				break;

			case 'password' :
			case 'select'   :
			case 'radio'    :
			case 'checkbox' :
			default :
				$retval = esc_attr( $pre_ret_val );
				break;
		}

		return apply_filters( 'psf_get_sanitize_val', $retval, $request, $input_type );
	}

/**
 * Output the current tab index of a given form
 *
 * Use this function to handle the tab indexing of user facing forms within a
 * template file. Calling this function will automatically increment the global
 * tab index by default.
 *
 * @since PSForum (r2810)
 *
 * @param int $auto_increment Optional. Default true. Set to false to prevent
 *                             increment
 */
function psf_tab_index( $auto_increment = true ) {
	echo psf_get_tab_index( $auto_increment );
}

	/**
	 * Output the current tab index of a given form
	 *
	 * Use this function to handle the tab indexing of user facing forms
	 * within a template file. Calling this function will automatically
	 * increment the global tab index by default.
	 *
	 * @since PSForum (r2810)
	 *
	 * @uses apply_filters Allows return value to be filtered
	 * @param int $auto_increment Optional. Default true. Set to false to
	 *                             prevent the increment
	 * @return int $psf->tab_index The global tab index
	 */
	function psf_get_tab_index( $auto_increment = true ) {
		$psf = psforum();

		if ( true === $auto_increment )
			++$psf->tab_index;

		return apply_filters( 'psf_get_tab_index', (int) $psf->tab_index );
	}

/**
 * Output a select box allowing to pick which forum/topic a new topic/reply
 * belongs in.
 *
 * Can be used for any post type, but is mostly used for topics and forums.
 *
 * @since PSForum (r2746)
 *
 * @param mixed $args See {@link psf_get_dropdown()} for arguments
 */
function psf_dropdown( $args = '' ) {
	echo psf_get_dropdown( $args );
}
	/**
	 * Output a select box allowing to pick which forum/topic a new
	 * topic/reply belongs in.
	 *
	 * @since PSForum (r2746)
	 *
	 * @param mixed $args The function supports these args:
	 *  - post_type: Post type, defaults to psf_get_forum_post_type() (psf_forum)
	 *  - selected: Selected ID, to not have any value as selected, pass
	 *               anything smaller than 0 (due to the nature of select
	 *               box, the first value would of course be selected -
	 *               though you can have that as none (pass 'show_none' arg))
	 *  - orderby: Defaults to 'menu_order title'
	 *  - post_parent: Post parent. Defaults to 0
	 *  - post_status: Which all post_statuses to find in? Can be an array
	 *                  or CSV of publish, category, closed, private, spam,
	 *                  trash (based on post type) - if not set, these are
	 *                  automatically determined based on the post_type
	 *  - posts_per_page: Retrieve all forums/topics. Defaults to -1 to get
	 *                     all posts
	 *  - walker: Which walker to use? Defaults to
	 *             {@link PSF_Walker_Dropdown}
	 *  - select_id: ID of the select box. Defaults to 'psf_forum_id'
	 *  - tab: Tabindex value. False or integer
	 *  - options_only: Show only <options>? No <select>?
	 *  - show_none: Boolean or String __( '(No Forum)', 'psforum' )
	 *  - disable_categories: Disable forum categories and closed forums?
	 *                         Defaults to true. Only for forums and when
	 *                         the category option is displayed.
	 * @uses PSF_Walker_Dropdown() As the default walker to generate the
	 *                              dropdown
	 * @uses current_user_can() To check if the current user can read
	 *                           private forums
	 * @uses psf_get_forum_post_type() To get the forum post type
	 * @uses psf_get_topic_post_type() To get the topic post type
	 * @uses walk_page_dropdown_tree() To generate the dropdown using the
	 *                                  walker
	 * @uses apply_filters() Calls 'psf_get_dropdown' with the dropdown
	 *                        and args
	 * @return string The dropdown
	 */
	function psf_get_dropdown( $args = '' ) {

		/** Arguments *********************************************************/

		// Parse arguments against default values
		$r = psf_parse_args( $args, array(
			'post_type'          => psf_get_forum_post_type(),
			'post_parent'        => null,
			'post_status'        => null,
			'selected'           => 0,
			'exclude'            => array(),
			'numberposts'        => -1,
			'orderby'            => 'menu_order title',
			'order'              => 'ASC',
			'walker'             => '',

			// Output-related
			'select_id'          => 'psf_forum_id',
			'tab'                => psf_get_tab_index(),
			'options_only'       => false,
			'show_none'          => false,
			'disable_categories' => true,
			'disabled'           => ''
		), 'get_dropdown' );

		if ( empty( $r['walker'] ) ) {
			$r['walker']            = new PSF_Walker_Dropdown();
			$r['walker']->tree_type = $r['post_type'];
		}

		// Force 0
		if ( is_numeric( $r['selected'] ) && $r['selected'] < 0 ) {
			$r['selected'] = 0;
		}

		// Force array
		if ( !empty( $r['exclude'] ) && !is_array( $r['exclude'] ) ) {
			$r['exclude'] = explode( ',', $r['exclude'] );
		}

		/** Setup variables ***************************************************/

		$retval = '';
		$posts  = get_posts( array(
			'post_type'          => $r['post_type'],
			'post_status'        => $r['post_status'],
			'exclude'            => $r['exclude'],
			'post_parent'        => $r['post_parent'],
			'numberposts'        => $r['numberposts'],
			'orderby'            => $r['orderby'],
			'order'              => $r['order'],
			'walker'             => $r['walker'],
			'disable_categories' => $r['disable_categories']
		) );

		/** Drop Down *********************************************************/

		// Build the opening tag for the select element
		if ( empty( $r['options_only'] ) ) {

			// Should this select appear disabled?
			$disabled  = disabled( isset( psforum()->options[ $r['disabled'] ] ), true, false );

			// Setup the tab index attribute
			$tab       = !empty( $r['tab'] ) ? ' tabindex="' . intval( $r['tab'] ) . '"' : '';

			// Open the select tag
			$retval   .= '<select name="' . esc_attr( $r['select_id'] ) . '" id="' . esc_attr( $r['select_id'] ) . '"' . $disabled . $tab . '>' . "\n";
		}

		// Display a leading 'no-value' option, with or without custom text
		if ( !empty( $r['show_none'] ) || !empty( $r['none_found'] ) ) {

			// Open the 'no-value' option tag
			$retval .= "\t<option value=\"\" class=\"level-0\">";

			// Use deprecated 'none_found' first for backpat
			if ( ! empty( $r['none_found'] ) && is_string( $r['none_found'] ) ) {
				$retval .= esc_html( $r['none_found'] );

			// Use 'show_none' second
			} elseif ( ! empty( $r['show_none'] ) && is_string( $r['show_none'] ) ) {
				$retval .= esc_html( $r['show_none'] );

			// Otherwise, make some educated guesses
			} else {

				// Switch the response based on post type
				switch ( $r['post_type'] ) {

					// Topics
					case psf_get_topic_post_type() :
						$retval .= esc_html__( 'Keine Themen verfügbar', 'psforum' );
						break;

					// Forums
					case psf_get_forum_post_type() :
						$retval .= esc_html__( 'Keine Foren verfügbar', 'psforum' );
						break;

					// Any other
					default :
						$retval .= esc_html__( 'Keine verfügbar', 'psforum' );
						break;
				}
			}

			// Close the 'no-value' option tag
			$retval .= '</option>';
		}

		// Items found so walk the tree
		if ( !empty( $posts ) ) {
			$retval .= walk_page_dropdown_tree( $posts, 0, $r );
		}

		// Close the selecet tag
		if ( empty( $r['options_only'] ) ) {
			$retval .= '</select>';
		}

		return apply_filters( 'psf_get_dropdown', $retval, $r );
	}

/**
 * Output the required hidden fields when creating/editing a forum
 *
 * @since PSForum (r3553)
 *
 * @uses psf_is_forum_edit() To check if it's the forum edit page
 * @uses wp_nonce_field() To generate hidden nonce fields
 * @uses psf_forum_id() To output the forum id
 * @uses psf_is_single_forum() To check if it's a forum page
 * @uses psf_forum_id() To output the forum id
 */
function psf_forum_form_fields() {

	if ( psf_is_forum_edit() ) : ?>

		<input type="hidden" name="action"       id="psf_post_action" value="psf-edit-forum" />
		<input type="hidden" name="psf_forum_id" id="psf_forum_id"    value="<?php psf_forum_id(); ?>" />

		<?php

		if ( current_user_can( 'unfiltered_html' ) )
			wp_nonce_field( 'psf-unfiltered-html-forum_' . psf_get_forum_id(), '_psf_unfiltered_html_forum', false );

		?>

		<?php wp_nonce_field( 'psf-edit-forum_' . psf_get_forum_id() );

	else :

		if ( psf_is_single_forum() ) : ?>

			<input type="hidden" name="psf_forum_parent_id" id="psf_forum_parent_id" value="<?php psf_forum_parent_id(); ?>" />

		<?php endif; ?>

		<input type="hidden" name="action" id="psf_post_action" value="psf-new-forum" />

		<?php

		if ( current_user_can( 'unfiltered_html' ) )
			wp_nonce_field( 'psf-unfiltered-html-forum_new', '_psf_unfiltered_html_forum', false );

		?>

		<?php wp_nonce_field( 'psf-new-forum' );

	endif;
}

/**
 * Output the required hidden fields when creating/editing a topic
 *
 * @since PSForum (r2753)
 *
 * @uses psf_is_topic_edit() To check if it's the topic edit page
 * @uses wp_nonce_field() To generate hidden nonce fields
 * @uses psf_topic_id() To output the topic id
 * @uses psf_is_single_forum() To check if it's a forum page
 * @uses psf_forum_id() To output the forum id
 */
function psf_topic_form_fields() {

	if ( psf_is_topic_edit() ) : ?>

		<input type="hidden" name="action"       id="psf_post_action" value="psf-edit-topic" />
		<input type="hidden" name="psf_topic_id" id="psf_topic_id"    value="<?php psf_topic_id(); ?>" />

		<?php

		if ( current_user_can( 'unfiltered_html' ) )
			wp_nonce_field( 'psf-unfiltered-html-topic_' . psf_get_topic_id(), '_psf_unfiltered_html_topic', false );

		?>

		<?php wp_nonce_field( 'psf-edit-topic_' . psf_get_topic_id() );

	else :

		if ( psf_is_single_forum() ) : ?>

			<input type="hidden" name="psf_forum_id" id="psf_forum_id" value="<?php psf_forum_id(); ?>" />

		<?php endif; ?>

		<input type="hidden" name="action" id="psf_post_action" value="psf-new-topic" />

		<?php if ( current_user_can( 'unfiltered_html' ) )
			wp_nonce_field( 'psf-unfiltered-html-topic_new', '_psf_unfiltered_html_topic', false ); ?>

		<?php wp_nonce_field( 'psf-new-topic' );

	endif;
}

/**
 * Output the required hidden fields when creating/editing a reply
 *
 * @since PSForum (r2753)
 *
 * @uses psf_is_reply_edit() To check if it's the reply edit page
 * @uses wp_nonce_field() To generate hidden nonce fields
 * @uses psf_reply_id() To output the reply id
 * @uses psf_topic_id() To output the topic id
 * @uses psf_forum_id() To output the forum id
 */
function psf_reply_form_fields() {

	if ( psf_is_reply_edit() ) : ?>

		<input type="hidden" name="psf_reply_id"    id="psf_reply_id"    value="<?php psf_reply_id(); ?>" />
		<input type="hidden" name="psf_reply_to"    id="psf_reply_to"    value="<?php psf_form_reply_to(); ?>" />
		<input type="hidden" name="action"          id="psf_post_action" value="psf-edit-reply" />

		<?php if ( current_user_can( 'unfiltered_html' ) )
			wp_nonce_field( 'psf-unfiltered-html-reply_' . psf_get_reply_id(), '_psf_unfiltered_html_reply', false ); ?>

		<?php wp_nonce_field( 'psf-edit-reply_' . psf_get_reply_id() );

	else : ?>

		<input type="hidden" name="psf_topic_id"    id="psf_topic_id"    value="<?php psf_topic_id(); ?>" />
		<input type="hidden" name="psf_reply_to"    id="psf_reply_to"    value="<?php psf_form_reply_to(); ?>" />
		<input type="hidden" name="action"          id="psf_post_action" value="psf-new-reply" />

		<?php if ( current_user_can( 'unfiltered_html' ) )
			wp_nonce_field( 'psf-unfiltered-html-reply_' . psf_get_topic_id(), '_psf_unfiltered_html_reply', false ); ?>

		<?php wp_nonce_field( 'psf-new-reply' );

		// Show redirect field if not viewing a specific topic
		if ( psf_is_query_name( 'psf_single_topic' ) ) :
			psf_redirect_to_field( get_permalink() );

		endif;
	endif;
}

/**
 * Output the required hidden fields when editing a user
 *
 * @since PSForum (r2690)
 *
 * @uses psf_displayed_user_id() To output the displayed user id
 * @uses wp_nonce_field() To generate a hidden referer field
 */
function psf_edit_user_form_fields() {
?>

	<input type="hidden" name="action"  id="psf_post_action" value="psf-update-user" />
	<input type="hidden" name="user_id" id="user_id"         value="<?php psf_displayed_user_id(); ?>" />

	<?php wp_nonce_field( 'update-user_' . psf_get_displayed_user_id() );
}

/**
 * Merge topic form fields
 *
 * Output the required hidden fields when merging a topic
 *
 * @since PSForum (r2756)
 *
 * @uses wp_nonce_field() To generate a hidden nonce field
 * @uses psf_topic_id() To output the topic id
 */
function psf_merge_topic_form_fields() {
?>

	<input type="hidden" name="action"       id="psf_post_action" value="psf-merge-topic" />
	<input type="hidden" name="psf_topic_id" id="psf_topic_id"    value="<?php psf_topic_id(); ?>" />

	<?php wp_nonce_field( 'psf-merge-topic_' . psf_get_topic_id() );
}

/**
 * Split topic form fields
 *
 * Output the required hidden fields when splitting a topic
 *
 * @since PSForum (r2756)
 *
 * @uses wp_nonce_field() To generate a hidden nonce field
 */
function psf_split_topic_form_fields() {
?>

	<input type="hidden" name="action"       id="psf_post_action" value="psf-split-topic" />
	<input type="hidden" name="psf_reply_id" id="psf_reply_id"    value="<?php echo absint( $_GET['reply_id'] ); ?>" />

	<?php wp_nonce_field( 'psf-split-topic_' . psf_get_topic_id() );
}

/**
 * Move reply form fields
 *
 * Output the required hidden fields when moving a reply
 *
 * @uses wp_nonce_field() To generate a hidden nonce field
 */
function psf_move_reply_form_fields() {
?>

	<input type="hidden" name="action"       id="psf_post_action" value="psf-move-reply" />
	<input type="hidden" name="psf_reply_id" id="psf_reply_id"    value="<?php echo absint( $_GET['reply_id'] ); ?>" />

	<?php wp_nonce_field( 'psf-move-reply_' . psf_get_reply_id() );
}

/**
 * Output a textarea or TinyMCE if enabled
 *
 * @since PSForum (r3586)
 *
 * @param array $args
 * @uses psf_get_the_content() To return the content to output
 */
function psf_the_content( $args = array() ) {
	echo psf_get_the_content( $args );
}
	/**
	 * Return a textarea or TinyMCE if enabled
	 *
	 * @since PSForum (r3586)
	 *
	 * @param array $args
	 *
	 * @uses apply_filter() To filter args and output
	 * @uses wp_parse_pargs() To compare args
	 * @uses psf_use_wp_editor() To see if WP editor is in use
	 * @uses psf_is_edit() To see if we are editing something
	 * @uses wp_editor() To output the WordPress editor
	 *
	 * @return string HTML from output buffer
	 */
	function psf_get_the_content( $args = array() ) {

		// Parse arguments against default values
		$r = psf_parse_args( $args, array(
			'context'           => 'topic',
			'before'            => '<div class="psf-the-content-wrapper">',
			'after'             => '</div>',
			'wpautop'           => true,
			'media_buttons'     => false,
			'textarea_rows'     => '12',
			'tabindex'          => psf_get_tab_index(),
			'tabfocus_elements' => 'psf_topic_title,psf_topic_tags',
			'editor_class'      => 'psf-the-content',
			'tinymce'           => false,
			'teeny'             => true,
			'quicktags'         => true,
			'dfw'               => false
		), 'get_the_content' );

		// If using tinymce, remove our escaping and trust tinymce
		if ( psf_use_wp_editor() && ( false !== $r['tinymce'] ) ) {
			remove_filter( 'psf_get_form_forum_content', 'esc_textarea' );
			remove_filter( 'psf_get_form_topic_content', 'esc_textarea' );
			remove_filter( 'psf_get_form_reply_content', 'esc_textarea' );
		}

		// Assume we are not editing
		$post_content = call_user_func( 'psf_get_form_' . $r['context'] . '_content' );

		// Start an output buffor
		ob_start();

		// Output something before the editor
		if ( !empty( $r['before'] ) ) {
			echo $r['before'];
		}

		// Use TinyMCE if available
		if ( psf_use_wp_editor() ) :

			// Enable additional TinyMCE plugins before outputting the editor
			add_filter( 'tiny_mce_plugins',   'psf_get_tiny_mce_plugins'   );
			add_filter( 'teeny_mce_plugins',  'psf_get_tiny_mce_plugins'   );
			add_filter( 'teeny_mce_buttons',  'psf_get_teeny_mce_buttons'  );
			add_filter( 'quicktags_settings', 'psf_get_quicktags_settings' );

			// Output the editor
			wp_editor( $post_content, 'psf_' . $r['context'] . '_content', array(
				'wpautop'           => $r['wpautop'],
				'media_buttons'     => $r['media_buttons'],
				'textarea_rows'     => $r['textarea_rows'],
				'tabindex'          => $r['tabindex'],
				'tabfocus_elements' => $r['tabfocus_elements'],
				'editor_class'      => $r['editor_class'],
				'tinymce'           => $r['tinymce'],
				'teeny'             => $r['teeny'],
				'quicktags'         => $r['quicktags'],
				'dfw'               => $r['dfw'],
			) );

			// Remove additional TinyMCE plugins after outputting the editor
			remove_filter( 'tiny_mce_plugins',   'psf_get_tiny_mce_plugins'   );
			remove_filter( 'teeny_mce_plugins',  'psf_get_tiny_mce_plugins'   );
			remove_filter( 'teeny_mce_buttons',  'psf_get_teeny_mce_buttons'  );
			remove_filter( 'quicktags_settings', 'psf_get_quicktags_settings' );

		/**
		 * Fallback to normal textarea.
		 *
		 * Note that we do not use esc_textarea() here to prevent double
		 * escaping the editable output, mucking up existing content.
		 */
		else : ?>

			<textarea id="psf_<?php echo esc_attr( $r['context'] ); ?>_content" class="<?php echo esc_attr( $r['editor_class'] ); ?>" name="psf_<?php echo esc_attr( $r['context'] ); ?>_content" cols="60" rows="<?php echo esc_attr( $r['textarea_rows'] ); ?>" tabindex="<?php echo esc_attr( $r['tabindex'] ); ?>"><?php echo $post_content; ?></textarea>

		<?php endif;

		// Output something after the editor
		if ( !empty( $r['after'] ) ) {
			echo $r['after'];
		}

		// Put the output into a usable variable
		$output = ob_get_clean();

		return apply_filters( 'psf_get_the_content', $output, $args, $post_content );
	}

/**
 * Edit TinyMCE plugins to match core behaviour
 *
 * @since PSForum (r4574)
 *
 * @param array $plugins
 * @see tiny_mce_plugins, teeny_mce_plugins
 * @return array
 */
function psf_get_tiny_mce_plugins( $plugins = array() ) {

	$plugins = (array) $plugins;

	// Unset fullscreen
	foreach ( $plugins as $key => $value ) {
		if ( 'fullscreen' === $value ) {
			unset( $plugins[$key] );
			break;
		}
	}

	// Add the tabfocus plugin
	$plugins[] = 'tabfocus';

	return apply_filters( 'psf_get_tiny_mce_plugins', $plugins );
}

/**
 * Edit TeenyMCE buttons to match allowedtags
 *
 * @since PSForum (r4605)
 *
 * @param array $buttons
 * @see teeny_mce_buttons
 * @return array
 */
function psf_get_teeny_mce_buttons( $buttons = array() ) {

	// Remove some buttons from TeenyMCE
	$buttons = array_diff( $buttons, array(
		'underline',
		'justifyleft',
		'justifycenter',
		'justifyright'
	) );

	// Images
	array_push( $buttons, 'image' );

	return apply_filters( 'psf_get_teeny_mce_buttons', $buttons );
}

/**
 * Edit TinyMCE quicktags buttons to match allowedtags
 *
 * @since PSForum (r4606)
 *
 * @param array $buttons
 * @see quicktags_settings
 * @return array Quicktags settings
 */
function psf_get_quicktags_settings( $settings = array() ) {

	// Get buttons out of settings
	$buttons_array = explode( ',', $settings['buttons'] );

	// Diff the ones we don't want out
	$buttons = array_diff( $buttons_array, array(
		'ins',
		'more',
		'spell'
	) );

	// Put them back into a string in the $settings array
	$settings['buttons'] = implode( ',', $buttons );

	return apply_filters( 'psf_get_quicktags_settings', $settings );
}

/** Views *********************************************************************/

/**
 * Output the view id
 *
 * @since PSForum (r2789)
 *
 * @param string $view Optional. View id
 * @uses psf_get_view_id() To get the view id
 */
function psf_view_id( $view = '' ) {
	echo psf_get_view_id( $view );
}

	/**
	 * Get the view id
	 *
	 * Use view id if supplied, otherwise psf_get_view_rewrite_id() query var.
	 *
	 * @since PSForum (r2789)
	 *
	 * @param string $view Optional. View id.
	 * @uses sanitize_title() To sanitize the view id
	 * @uses get_query_var() To get the view id query variable
	 * @uses psf_get_view_rewrite_id() To get the view rewrite ID
	 * @return bool|string ID on success, false on failure
	 */
	function psf_get_view_id( $view = '' ) {
		$psf = psforum();

		if ( !empty( $view ) ) {
			$view = sanitize_title( $view );
		} elseif ( ! empty( $psf->current_view_id ) ) {
			$view = $psf->current_view_id;
		} else {
			$view = get_query_var( psf_get_view_rewrite_id() );
		}

		if ( array_key_exists( $view, $psf->views ) ) {
			return $view;
		}

		return false;
	}

/**
 * Output the view name aka title
 *
 * @since PSForum (r2789)
 *
 * @param string $view Optional. View id
 * @uses psf_get_view_title() To get the view title
 */
function psf_view_title( $view = '' ) {
	echo psf_get_view_title( $view );
}

	/**
	 * Get the view name aka title
	 *
	 * If a view id is supplied, that is used. Otherwise the psf_view
	 * query var is checked for.
	 *
	 * @since PSForum (r2789)
	 *
	 * @param string $view Optional. View id
	 * @uses psf_get_view_id() To get the view id
	 * @return bool|string Title on success, false on failure
	 */
	function psf_get_view_title( $view = '' ) {
		$psf = psforum();

		$view = psf_get_view_id( $view );
		if ( empty( $view ) )
			return false;

		return $psf->views[$view]['title'];
	}

/**
 * Output the view url
 *
 * @since PSForum (r2789)
 *
 * @param string $view Optional. View id
 * @uses psf_get_view_url() To get the view url
 */
function psf_view_url( $view = false ) {
	echo esc_url( psf_get_view_url( $view ) );
}
	/**
	 * Return the view url
	 *
	 * @since PSForum (r2789)
	 *
	 * @param string $view Optional. View id
	 * @uses sanitize_title() To sanitize the view id
	 * @uses home_url() To get blog home url
	 * @uses add_query_arg() To add custom args to the url
	 * @uses apply_filters() Calls 'psf_get_view_url' with the view url,
	 *                        used view id
	 * @return string View url (or home url if the view was not found)
	 */
	function psf_get_view_url( $view = false ) {
		global $wp_rewrite;

		$view = psf_get_view_id( $view );
		if ( empty( $view ) )
			return home_url();

		// Pretty permalinks
		if ( $wp_rewrite->using_permalinks() ) {
			$url = $wp_rewrite->root . psf_get_view_slug() . '/' . $view;
			$url = home_url( user_trailingslashit( $url ) );

		// Unpretty permalinks
		} else {
			$url = add_query_arg( array( psf_get_view_rewrite_id() => $view ), home_url( '/' ) );
		}

		return apply_filters( 'psf_get_view_link', $url, $view );
	}

/** Query *********************************************************************/

/**
 * Check the passed parameter against the current _psf_query_name
 *
 * @since PSForum (r2980)
 *
 * @uses psf_get_query_name() Get the query var '_psf_query_name'
 * @return bool True if match, false if not
 */
function psf_is_query_name( $name = '' )  {
	return (bool) ( psf_get_query_name() === $name );
}

/**
 * Get the '_psf_query_name' setting
 *
 * @since PSForum (r2695)
 *
 * @uses get_query_var() To get the query var '_psf_query_name'
 * @return string To return the query var value
 */
function psf_get_query_name()  {
	return get_query_var( '_psf_query_name' );
}

/**
 * Set the '_psf_query_name' setting to $name
 *
 * @since PSForum (r2692)
 *
 * @param string $name What to set the query var to
 * @uses set_query_var() To set the query var '_psf_query_name'
 */
function psf_set_query_name( $name = '' )  {
	set_query_var( '_psf_query_name', $name );
}

/**
 * Used to clear the '_psf_query_name' setting
 *
 * @since PSForum (r2692)
 *
 * @uses psf_set_query_name() To set the query var '_psf_query_name' value to ''
 */
function psf_reset_query_name() {
	psf_set_query_name();
}

/** Breadcrumbs ***************************************************************/

/**
 * Output the page title as a breadcrumb
 *
 * @since PSForum (r2589)
 *
 * @param string $sep Separator. Defaults to '&larr;'
 * @param bool $current_page Include the current item
 * @param bool $root Include the root page if one exists
 * @uses psf_get_breadcrumb() To get the breadcrumb
 */
function psf_title_breadcrumb( $args = array() ) {
	echo psf_get_breadcrumb( $args );
}

/**
 * Output a breadcrumb
 *
 * @since PSForum (r2589)
 *
 * @param string $sep Separator. Defaults to '&larr;'
 * @param bool $current_page Include the current item
 * @param bool $root Include the root page if one exists
 * @uses psf_get_breadcrumb() To get the breadcrumb
 */
function psf_breadcrumb( $args = array() ) {
	echo psf_get_breadcrumb( $args );
}
	/**
	 * Return a breadcrumb ( forum -> topic -> reply )
	 *
	 * @since PSForum (r2589)
	 *
	 * @param string $sep Separator. Defaults to '&larr;'
	 * @param bool $current_page Include the current item
	 * @param bool $root Include the root page if one exists
	 *
	 * @uses get_post() To get the post
	 * @uses psf_get_forum_permalink() To get the forum link
	 * @uses psf_get_topic_permalink() To get the topic link
	 * @uses psf_get_reply_permalink() To get the reply link
	 * @uses get_permalink() To get the permalink
	 * @uses psf_get_forum_post_type() To get the forum post type
	 * @uses psf_get_topic_post_type() To get the topic post type
	 * @uses psf_get_reply_post_type() To get the reply post type
	 * @uses psf_get_forum_title() To get the forum title
	 * @uses psf_get_topic_title() To get the topic title
	 * @uses psf_get_reply_title() To get the reply title
	 * @uses get_the_title() To get the title
	 * @uses apply_filters() Calls 'psf_get_breadcrumb' with the crumbs
	 * @return string Breadcrumbs
	 */
	function psf_get_breadcrumb( $args = array() ) {

		// Turn off breadcrumbs
		if ( apply_filters( 'psf_no_breadcrumb', is_front_page() ) )
			return;

		// Define variables
		$front_id         = $root_id                                 = 0;
		$ancestors        = $crumbs           = $tag_data            = array();
		$pre_root_text    = $pre_front_text   = $pre_current_text    = '';
		$pre_include_root = $pre_include_home = $pre_include_current = true;

		/** Home Text *********************************************************/

		// No custom home text
		if ( empty( $args['home_text'] ) ) {

			$front_id = get_option( 'page_on_front' );

			// Set home text to page title
			if ( !empty( $front_id ) ) {
				$pre_front_text = get_the_title( $front_id );

			// Default to 'Home'
			} else {
				$pre_front_text = __( 'Home', 'psforum' );
			}
		}

		/** Root Text *********************************************************/

		// No custom root text
		if ( empty( $args['root_text'] ) ) {
			$page = psf_get_page_by_path( psf_get_root_slug() );
			if ( !empty( $page ) ) {
				$root_id = $page->ID;
			}
			$pre_root_text = psf_get_forum_archive_title();
		}

		/** Includes **********************************************************/

		// Root slug is also the front page
		if ( !empty( $front_id ) && ( $front_id === $root_id ) ) {
			$pre_include_root = false;
		}

		// Don't show root if viewing forum archive
		if ( psf_is_forum_archive() ) {
			$pre_include_root = false;
		}

		// Don't show root if viewing page in place of forum archive
		if ( !empty( $root_id ) && ( ( is_single() || is_page() ) && ( $root_id === get_the_ID() ) ) ) {
			$pre_include_root = false;
		}

		/** Current Text ******************************************************/

		// Search page
		if ( psf_is_search() ) {
			$pre_current_text = psf_get_search_title();

		// Forum archive
		} elseif ( psf_is_forum_archive() ) {
			$pre_current_text = psf_get_forum_archive_title();

		// Topic archive
		} elseif ( psf_is_topic_archive() ) {
			$pre_current_text = psf_get_topic_archive_title();

		// View
		} elseif ( psf_is_single_view() ) {
			$pre_current_text = psf_get_view_title();

		// Single Forum
		} elseif ( psf_is_single_forum() ) {
			$pre_current_text = psf_get_forum_title();

		// Single Topic
		} elseif ( psf_is_single_topic() ) {
			$pre_current_text = psf_get_topic_title();

		// Single Topic
		} elseif ( psf_is_single_reply() ) {
			$pre_current_text = psf_get_reply_title();

		// Topic Tag (or theme compat topic tag)
		} elseif ( psf_is_topic_tag() || ( get_query_var( 'psf_topic_tag' ) && !psf_is_topic_tag_edit() ) ) {

			// Always include the tag name
			$tag_data[] = psf_get_topic_tag_name();

			// If capable, include a link to edit the tag
			if ( current_user_can( 'manage_topic_tags' ) ) {
				$tag_data[] = '<a href="' . esc_url( psf_get_topic_tag_edit_link() ) . '" class="psf-edit-topic-tag-link">' . esc_html__( '(Bearbeiten)', 'psforum' ) . '</a>';
			}

			// Implode the results of the tag data
			$pre_current_text = sprintf( __( 'Themen-Tag: %s', 'psforum' ), implode( ' ', $tag_data ) );

		// Edit Topic Tag
		} elseif ( psf_is_topic_tag_edit() ) {
			$pre_current_text = __( 'Bearbeiten', 'psforum' );

		// Single
		} else {
			$pre_current_text = get_the_title();
		}

		/** Parse Args ********************************************************/

		// Parse args
		$r = psf_parse_args( $args, array(

			// HTML
			'before'          => '<div class="psf-breadcrumb"><p>',
			'after'           => '</p></div>',

			// Separator
			'sep'             => is_rtl() ? __( '&lsaquo;', 'psforum' ) : __( '&rsaquo;', 'psforum' ),
			'pad_sep'         => 1,
			'sep_before'      => '<span class="psf-breadcrumb-sep">',
			'sep_after'       => '</span>',

			// Crumbs
			'crumb_before'    => '',
			'crumb_after'     => '',

			// Home
			'include_home'    => $pre_include_home,
			'home_text'       => $pre_front_text,

			// Forum root
			'include_root'    => $pre_include_root,
			'root_text'       => $pre_root_text,

			// Current
			'include_current' => $pre_include_current,
			'current_text'    => $pre_current_text,
			'current_before'  => '<span class="psf-breadcrumb-current">',
			'current_after'   => '</span>',
		), 'get_breadcrumb' );

		/** Ancestors *********************************************************/

		// Get post ancestors
		if ( is_singular() || psf_is_forum_edit() || psf_is_topic_edit() || psf_is_reply_edit() ) {
			$ancestors = array_reverse( (array) get_post_ancestors( get_the_ID() ) );
		}

		// Do we want to include a link to home?
		if ( !empty( $r['include_home'] ) || empty( $r['home_text'] ) ) {
			$crumbs[] = '<a href="' . trailingslashit( home_url() ) . '" class="psf-breadcrumb-home">' . $r['home_text'] . '</a>';
		}

		// Do we want to include a link to the forum root?
		if ( !empty( $r['include_root'] ) || empty( $r['root_text'] ) ) {

			// Page exists at root slug path, so use its permalink
			$page = psf_get_page_by_path( psf_get_root_slug() );
			if ( !empty( $page ) ) {
				$root_url = get_permalink( $page->ID );

			// Use the root slug
			} else {
				$root_url = get_post_type_archive_link( psf_get_forum_post_type() );
			}

			// Add the breadcrumb
			$crumbs[] = '<a href="' . esc_url( $root_url ) . '" class="psf-breadcrumb-root">' . $r['root_text'] . '</a>';
		}

		// Ancestors exist
		if ( !empty( $ancestors ) ) {

			// Loop through parents
			foreach ( (array) $ancestors as $parent_id ) {

				// Parents
				$parent = get_post( $parent_id );

				// Skip parent if empty or error
				if ( empty( $parent ) || is_wp_error( $parent ) )
					continue;

				// Switch through post_type to ensure correct filters are applied
				switch ( $parent->post_type ) {

					// Forum
					case psf_get_forum_post_type() :
						$crumbs[] = '<a href="' . esc_url( psf_get_forum_permalink( $parent->ID ) ) . '" class="psf-breadcrumb-forum">' . psf_get_forum_title( $parent->ID ) . '</a>';
						break;

					// Topic
					case psf_get_topic_post_type() :
						$crumbs[] = '<a href="' . esc_url( psf_get_topic_permalink( $parent->ID ) ) . '" class="psf-breadcrumb-topic">' . psf_get_topic_title( $parent->ID ) . '</a>';
						break;

					// Reply (Note: not in most themes)
					case psf_get_reply_post_type() :
						$crumbs[] = '<a href="' . esc_url( psf_get_reply_permalink( $parent->ID ) ) . '" class="psf-breadcrumb-reply">' . psf_get_reply_title( $parent->ID ) . '</a>';
						break;

					// WordPress Post/Page/Other
					default :
						$crumbs[] = '<a href="' . esc_url( get_permalink( $parent->ID ) ) . '" class="psf-breadcrumb-item">' . get_the_title( $parent->ID ) . '</a>';
						break;
				}
			}

		// Edit topic tag
		} elseif ( psf_is_topic_tag_edit() ) {
			$crumbs[] = '<a href="' . esc_url( get_term_link( psf_get_topic_tag_id(), psf_get_topic_tag_tax_id() ) ) . '" class="psf-breadcrumb-topic-tag">' . sprintf( __( 'Themen-Tag: %s', 'psforum' ), psf_get_topic_tag_name() ) . '</a>';

		// Search
		} elseif ( psf_is_search() && psf_get_search_terms() ) {
			$crumbs[] = '<a href="' . esc_url( psf_get_search_url() ) . '" class="psf-breadcrumb-search">' . esc_html__( 'Suche', 'psforum' ) . '</a>';
		}

		/** Current ***********************************************************/

		// Add current page to breadcrumb
		if ( !empty( $r['include_current'] ) || empty( $r['current_text'] ) ) {
			$crumbs[] = $r['current_before'] . $r['current_text'] . $r['current_after'];
		}

		/** Separator *********************************************************/

		// Wrap the separator in before/after before padding and filter
		if ( ! empty( $r['sep'] ) ) {
			$sep = $r['sep_before'] . $r['sep'] . $r['sep_after'];
		}

		// Pad the separator
		if ( !empty( $r['pad_sep'] ) ) {
			if ( function_exists( 'mb_strlen' ) ) {
				$sep = str_pad( $sep, mb_strlen( $sep ) + ( (int) $r['pad_sep'] * 2 ), ' ', STR_PAD_BOTH );
			} else {
				$sep = str_pad( $sep, strlen( $sep ) + ( (int) $r['pad_sep'] * 2 ), ' ', STR_PAD_BOTH );
			}
		}

		/** Finish Up *********************************************************/

		// Filter the separator and breadcrumb
		$sep    = apply_filters( 'psf_breadcrumb_separator', $sep    );
		$crumbs = apply_filters( 'psf_breadcrumbs',          $crumbs );

		// Build the trail
		$trail  = !empty( $crumbs ) ? ( $r['before'] . $r['crumb_before'] . implode( $sep . $r['crumb_after'] . $r['crumb_before'] , $crumbs ) . $r['crumb_after'] . $r['after'] ) : '';

		return apply_filters( 'psf_get_breadcrumb', $trail, $crumbs, $r );
	}

/** Topic Tags ***************************************************************/

/**
 * Output all of the allowed tags in HTML format with attributes.
 *
 * This is useful for displaying in the post area, which elements and
 * attributes are supported. As well as any plugins which want to display it.
 *
 * @since PSForum (r2780)
 *
 * @uses psf_get_allowed_tags()
 */
function psf_allowed_tags() {
	echo psf_get_allowed_tags();
}
	/**
	 * Display all of the allowed tags in HTML format with attributes.
	 *
	 * This is useful for displaying in the post area, which elements and
	 * attributes are supported. As well as any plugins which want to display it.
	 *
	 * @since PSForum (r2780)
	 *
	 * @uses psf_kses_allowed_tags() To get the allowed tags
	 * @uses apply_filters() Calls 'psf_allowed_tags' with the tags
	 * @return string HTML allowed tags entity encoded.
	 */
	function psf_get_allowed_tags() {

		$allowed = '';

		foreach ( (array) psf_kses_allowed_tags() as $tag => $attributes ) {
			$allowed .= '<' . $tag;
			if ( 0 < count( $attributes ) ) {
				foreach ( array_keys( $attributes ) as $attribute ) {
					$allowed .= ' ' . $attribute . '=""';
				}
			}
			$allowed .= '> ';
		}

		return apply_filters( 'psf_get_allowed_tags', htmlentities( $allowed ) );
	}

/** Errors & Messages *********************************************************/

/**
 * Display possible errors & messages inside a template file
 *
 * @since PSForum (r2688)
 *
 * @uses WP_Error PSForum::errors::get_error_codes() To get the error codes
 * @uses WP_Error PSForum::errors::get_error_data() To get the error data
 * @uses WP_Error PSForum::errors::get_error_messages() To get the error
 *                                                       messages
 * @uses is_wp_error() To check if it's a {@link WP_Error}
 */
function psf_template_notices() {

	// Bail if no notices or errors
	if ( !psf_has_errors() )
		return;

	// Define local variable(s)
	$errors = $messages = array();

	// Get PSForum
	$psf = psforum();

	// Loop through notices
	foreach ( $psf->errors->get_error_codes() as $code ) {

		// Get notice severity
		$severity = $psf->errors->get_error_data( $code );

		// Loop through notices and separate errors from messages
		foreach ( $psf->errors->get_error_messages( $code ) as $error ) {
			if ( 'message' === $severity ) {
				$messages[] = $error;
			} else {
				$errors[]   = $error;
			}
		}
	}

	// Display errors first...
	if ( !empty( $errors ) ) : ?>

		<div class="psf-template-notice error">
			<p>
				<?php echo implode( "</p>\n<p>", $errors ); ?>
			</p>
		</div>

	<?php endif;

	// ...and messages last
	if ( !empty( $messages ) ) : ?>

		<div class="psf-template-notice">
			<p>
				<?php echo implode( "</p>\n<p>", $messages ); ?>
			</p>
		</div>

	<?php endif;
}

/** Login/logout/register/lost pass *******************************************/

/**
 * Output the logout link
 *
 * @since PSForum (r2827)
 *
 * @param string $redirect_to Redirect to url
 * @uses psf_get_logout_link() To get the logout link
 */
function psf_logout_link( $redirect_to = '' ) {
	echo psf_get_logout_link( $redirect_to );
}
	/**
	 * Return the logout link
	 *
	 * @since PSForum (r2827)
	 *
	 * @param string $redirect_to Redirect to url
	 * @uses wp_logout_url() To get the logout url
	 * @uses apply_filters() Calls 'psf_get_logout_link' with the logout link and
	 *                        redirect to url
	 * @return string The logout link
	 */
	function psf_get_logout_link( $redirect_to = '' ) {
		return apply_filters( 'psf_get_logout_link', '<a href="' . wp_logout_url( $redirect_to ) . '" class="button logout-link">' . esc_html__( 'Ausloggen', 'psforum' ) . '</a>', $redirect_to );
	}

/** Title *********************************************************************/

/**
 * Custom page title for PSForum pages
 *
 * @since PSForum (r2788)
 *
 * @param string $title Optional. The title (not used).
 * @param string $sep Optional, default is '&raquo;'. How to separate the
 *                     various items within the page title.
 * @param string $seplocation Optional. Direction to display title, 'right'.
 * @uses psf_is_single_user() To check if it's a user profile page
 * @uses psf_is_single_user_edit() To check if it's a user profile edit page
 * @uses psf_is_user_home() To check if the profile page is of the current user
 * @uses get_query_var() To get the user id
 * @uses get_userdata() To get the user data
 * @uses psf_is_single_forum() To check if it's a forum
 * @uses psf_get_forum_title() To get the forum title
 * @uses psf_is_single_topic() To check if it's a topic
 * @uses psf_get_topic_title() To get the topic title
 * @uses psf_is_single_reply() To check if it's a reply
 * @uses psf_get_reply_title() To get the reply title
 * @uses is_tax() To check if it's the tag page
 * @uses get_queried_object() To get the queried object
 * @uses psf_is_single_view() To check if it's a view
 * @uses psf_get_view_title() To get the view title
 * @uses apply_filters() Calls 'psf_raw_title' with the title
 * @uses apply_filters() Calls 'psf_profile_page_wp_title' with the title,
 *                        separator and separator location
 * @return string The tite
 */
function psf_title( $title = '', $sep = '&raquo;', $seplocation = '' ) {

	// Title array
	$new_title = array();

	/** Archives **************************************************************/

	// Forum Archive
	if ( psf_is_forum_archive() ) {
		$new_title['text'] = psf_get_forum_archive_title();

	// Topic Archive
	} elseif ( psf_is_topic_archive() ) {
		$new_title['text'] = psf_get_topic_archive_title();

	/** Edit ******************************************************************/

	// Forum edit page
	} elseif ( psf_is_forum_edit() ) {
		$new_title['text']   = psf_get_forum_title();
		$new_title['format'] = esc_attr__( 'Forum Bearbeiten: %s', 'psforum' );

	// Topic edit page
	} elseif ( psf_is_topic_edit() ) {
		$new_title['text']   = psf_get_topic_title();
		$new_title['format'] = esc_attr__( 'Thema Bearbeiten: %s', 'psforum' );

	// Reply edit page
	} elseif ( psf_is_reply_edit() ) {
		$new_title['text']   = psf_get_reply_title();
		$new_title['format'] = esc_attr__( 'Antwort Bearbeiten: %s', 'psforum' );

	// Topic tag edit page
	} elseif ( psf_is_topic_tag_edit() ) {
		$new_title['text']   = psf_get_topic_tag_name();
		$new_title['format'] = esc_attr__( 'Themen-Tag Bearbeiten: %s', 'psforum' );

	/** Singles ***************************************************************/

	// Forum page
	} elseif ( psf_is_single_forum() ) {
		$new_title['text']   = psf_get_forum_title();
		$new_title['format'] = esc_attr__( 'Forum: %s', 'psforum' );

	// Topic page
	} elseif ( psf_is_single_topic() ) {
		$new_title['text']   = psf_get_topic_title();
		$new_title['format'] = esc_attr__( 'Thema: %s', 'psforum' );

	// Replies
	} elseif ( psf_is_single_reply() ) {
		$new_title['text']   = psf_get_reply_title();

	// Topic tag page
	} elseif ( psf_is_topic_tag() || get_query_var( 'psf_topic_tag' ) ) {
		$new_title['text']   = psf_get_topic_tag_name();
		$new_title['format'] = esc_attr__( 'Themen-Tag: %s', 'psforum' );

	/** Users *****************************************************************/

	// Profile page
	} elseif ( psf_is_single_user() ) {

		// User is viewing their own profile
		if ( psf_is_user_home() ) {
			$new_title['text'] = esc_attr_x( 'Dein', 'Benutzer, der sein/ihr eigenes Profil anzeigt', 'psforum' );

		// User is viewing someone else's profile (so use their display name)
		} else {
			$new_title['text'] = sprintf( esc_attr_x( "%s's", 'Benutzer, der das Profil eines anderen Benutzers anzeigt', 'psforum' ), get_userdata( psf_get_user_id() )->display_name );
		}

		// User topics created
		if ( psf_is_single_user_topics() ) {
			$new_title['format'] = esc_attr__( "%s Themen", 'psforum' );

		// User rueplies created
		} elseif ( psf_is_single_user_replies() ) {
			$new_title['format'] = esc_attr__( "%s Antworten", 'psforum' );

		// User favorites
		} elseif ( psf_is_favorites() ) {
			$new_title['format'] = esc_attr__( "%s Favoriten", 'psforum' );

		// User subscriptions
		} elseif ( psf_is_subscriptions() ) {
			$new_title['format'] = esc_attr__( "%s Abonnements", 'psforum' );

		// User "home"
		} else {
			$new_title['format'] = esc_attr__( "%s Profil",       'psforum' );
		}

	// Profile edit page
	} elseif ( psf_is_single_user_edit() ) {

		// Current user
		if ( psf_is_user_home_edit() ) {
			$new_title['text']   = esc_attr__( 'Bearbeite dein Profil', 'psforum' );

		// Other user
		} else {
			$new_title['text']   = get_userdata( psf_get_user_id() )->display_name;
			$new_title['format'] = esc_attr__( "Profil von %s bearbeiten", 'psforum' );
		}

	/** Views *****************************************************************/

	// Views
	} elseif ( psf_is_single_view() ) {
		$new_title['text']   = psf_get_view_title();
		$new_title['format'] = esc_attr__( 'Ansehen: %s', 'psforum' );

	/** Search ****************************************************************/

	// Search
	} elseif ( psf_is_search() ) {
		$new_title['text'] = psf_get_search_title();
	}

	// This filter is deprecated. Use 'psf_before_title_parse_args' instead.
	$new_title = apply_filters( 'psf_raw_title_array', $new_title );

	// Set title array defaults
	$new_title = psf_parse_args( $new_title, array(
		'text'   => $title,
		'format' => '%s'
	), 'title' );

	// Get the formatted raw title
	$new_title = sprintf( $new_title['format'], $new_title['text'] );

	// Filter the raw title
	$new_title = apply_filters( 'psf_raw_title', $new_title, $sep, $seplocation );

	// Compare new title with original title
	if ( $new_title === $title )
		return $title;

	// Temporary separator, for accurate flipping, if necessary
	$t_sep  = '%WP_TITILE_SEP%';
	$prefix = '';

	if ( !empty( $new_title ) )
		$prefix = " $sep ";

	// sep on right, so reverse the order
	if ( 'right' === $seplocation ) {
		$new_title_array = array_reverse( explode( $t_sep, $new_title ) );
		$new_title       = implode( " $sep ", $new_title_array ) . $prefix;

	// sep on left, do not reverse
	} else {
		$new_title_array = explode( $t_sep, $new_title );
		$new_title       = $prefix . implode( " $sep ", $new_title_array );
	}

	// Filter and return
	return apply_filters( 'psf_title', $new_title, $sep, $seplocation );
}
