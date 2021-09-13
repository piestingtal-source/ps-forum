<?php

/**
 * PSForum User Options
 *
 * @package PSForum
 * @subpackage UserOptions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Get the default user options and their values
 *
 * @since PSForum (r3910)
 * @return array Filtered user option names and values
 */
function psf_get_default_user_options() {

	// Default options
	return apply_filters( 'psf_get_default_user_options', array(
		'_psf_last_posted'   => '0', // For checking flooding
		'_psf_topic_count'   => '0', // Total topics per site
		'_psf_reply_count'   => '0', // Total replies per site
		'_psf_favorites'     => '',  // Favorite topics per site
		'_psf_subscriptions' => ''   // Subscribed topics per site
	) );
}

/**
 * Add default user options
 *
 * This is destructive, so existing PSForum user options will be overridden.
 *
 * @since PSForum (r3910)
 * @uses psf_get_default_user_options() To get default options
 * @uses update_user_option() Adds default options
 * @uses do_action() Calls 'psf_add_user_options'
 */
function psf_add_user_options( $user_id = 0 ) {

	// Validate user id
	$user_id = psf_get_user_id( $user_id );
	if ( empty( $user_id ) )
		return;

	// Add default options
	foreach ( psf_get_default_user_options() as $key => $value )
		update_user_option( $user_id, $key, $value );

	// Allow previously activated plugins to append their own user options.
	do_action( 'psf_add_user_options', $user_id );
}

/**
 * Delete default user options
 *
 * Hooked to psf_uninstall, it is only called once when PSForum is uninstalled.
 * This is destructive, so existing PSForum user options will be destroyed.
 *
 * @since PSForum (r3910)
 * @uses psf_get_default_user_options() To get default options
 * @uses delete_user_option() Removes default options
 * @uses do_action() Calls 'psf_delete_options'
 */
function psf_delete_user_options( $user_id = 0 ) {

	// Validate user id
	$user_id = psf_get_user_id( $user_id );
	if ( empty( $user_id ) )
		return;

	// Add default options
	foreach ( array_keys( psf_get_default_user_options() ) as $key )
		delete_user_option( $user_id, $key );

	// Allow previously activated plugins to append their own options.
	do_action( 'psf_delete_user_options', $user_id );
}

/**
 * Add filters to each PSForum option and allow them to be overloaded from
 * inside the $psf->options array.
 *
 * @since PSForum (r3910)
 * @uses psf_get_default_user_options() To get default options
 * @uses add_filter() To add filters to 'pre_option_{$key}'
 * @uses do_action() Calls 'psf_add_option_filters'
 */
function psf_setup_user_option_filters() {

	// Add filters to each PSForum option
	foreach ( array_keys( psf_get_default_user_options() ) as $key )
		add_filter( 'get_user_option_' . $key, 'psf_filter_get_user_option', 10, 3 );

	// Allow previously activated plugins to append their own options.
	do_action( 'psf_setup_user_option_filters' );
}

/**
 * Filter default options and allow them to be overloaded from inside the
 * $psf->user_options array.
 *
 * @since PSForum (r3910)
 * @param bool $value Optional. Default value false
 * @return mixed false if not overloaded, mixed if set
 */
function psf_filter_get_user_option( $value = false, $option = '', $user = 0 ) {
	$psf = psforum();

	// Check the options global for preset value
	if ( isset( $user->ID ) && isset( $psf->user_options[$user->ID] ) && !empty( $psf->user_options[$user->ID][$option] ) )
		$value = $psf->user_options[$user->ID][$option];

	// Always return a value, even if false
	return $value;
}

/** Post Counts ***************************************************************/

/**
 * Output a users topic count
 *
 * @since PSForum (r3632)
 *
 * @param int $user_id
 * @param boolean $integer Optional. Whether or not to format the result
 * @uses psf_get_user_topic_count()
 * @return string
 */
function psf_user_topic_count( $user_id = 0, $integer = false ) {
	echo psf_get_user_topic_count( $user_id, $integer );
}
	/**
	 * Return a users reply count
	 *
	 * @since PSForum (r3632)
	 *
	 * @param int $user_id
	 * @param boolean $integer Optional. Whether or not to format the result
	 * @uses psf_get_user_id()
	 * @uses get_user_option()
	 * @uses apply_filters()
	 * @return string
	 */
	function psf_get_user_topic_count( $user_id = 0, $integer = false ) {

		// Validate user id
		$user_id = psf_get_user_id( $user_id );
		if ( empty( $user_id ) )
			return false;

		$count  = (int) get_user_option( '_psf_topic_count', $user_id );
		$filter = ( false === $integer ) ? 'psf_get_user_topic_count_int' : 'psf_get_user_topic_count';

		return apply_filters( $filter, $count, $user_id );
	}

/**
 * Output a users reply count
 *
 * @since PSForum (r3632)
 *
 * @param int $user_id
 * @param boolean $integer Optional. Whether or not to format the result
 * @uses psf_get_user_reply_count()
 * @return string
 */
function psf_user_reply_count( $user_id = 0, $integer = false ) {
	echo psf_get_user_reply_count( $user_id, $integer );
}
	/**
	 * Return a users reply count
	 *
	 * @since PSForum (r3632)
	 *
	 * @param int $user_id
	 * @param boolean $integer Optional. Whether or not to format the result
	 * @uses psf_get_user_id()
	 * @uses get_user_option()
	 * @uses apply_filters()
	 * @return string
	 */
	function psf_get_user_reply_count( $user_id = 0, $integer = false ) {

		// Validate user id
		$user_id = psf_get_user_id( $user_id );
		if ( empty( $user_id ) )
			return false;

		$count  = (int) get_user_option( '_psf_reply_count', $user_id );
		$filter = ( true === $integer ) ? 'psf_get_user_topic_count_int' : 'psf_get_user_topic_count';

		return apply_filters( $filter, $count, $user_id );
	}

/**
 * Output a users total post count
 *
 * @since PSForum (r3632)
 *
 * @param int $user_id
 * @param boolean $integer Optional. Whether or not to format the result
 * @uses psf_get_user_post_count()
 * @return string
 */
function psf_user_post_count( $user_id = 0, $integer = false ) {
	echo psf_get_user_post_count( $user_id, $integer );
}
	/**
	 * Return a users total post count
	 *
	 * @since PSForum (r3632)
	 *
	 * @param int $user_id
	 * @param boolean $integer Optional. Whether or not to format the result
	 * @uses psf_get_user_id()
	 * @uses get_user_option()
	 * @uses apply_filters()
	 * @return string
	 */
	function psf_get_user_post_count( $user_id = 0, $integer = false ) {

		// Validate user id
		$user_id = psf_get_user_id( $user_id );
		if ( empty( $user_id ) )
			return false;

		$topics  = psf_get_user_topic_count( $user_id, true );
		$replies = psf_get_user_reply_count( $user_id, true );
		$count   = (int) $topics + $replies;
		$filter  = ( true === $integer ) ? 'psf_get_user_post_count_int' : 'psf_get_user_post_count';

		return apply_filters( $filter, $count, $user_id );
	}

/** Last Posted ***************************************************************/

/**
 * Update a users last posted time, for use with post throttling
 *
 * @since PSForum (r3910)
 * @param int $user_id User ID to update
 * @param int $time Time in time() format
 * @return bool False if no user or failure, true if successful
 */
function psf_update_user_last_posted( $user_id = 0, $time = 0 ) {

	// Validate user id
	$user_id = psf_get_user_id( $user_id );
	if ( empty( $user_id ) )
		return false;

	// Set time to now if nothing is passed
	if ( empty( $time ) )
		$time = time();

	return update_user_option( $user_id, '_psf_last_posted', $time );
}

/**
 * Output the raw value of the last posted time.
 *
 * @since PSForum (r3910)
 * @param int $user_id User ID to retrieve value for
 * @uses psf_get_user_last_posted() To output the last posted time
 */
function psf_user_last_posted( $user_id = 0 ) {
	echo psf_get_user_last_posted( $user_id );
}

	/**
	 * Return the raw value of teh last posted time.
	 *
	 * @since PSForum (r3910)
	 * @param int $user_id User ID to retrieve value for
	 * @return mixed False if no user, time() format if exists
	 */
	function psf_get_user_last_posted( $user_id = 0 ) {

		// Validate user id
		$user_id = psf_get_user_id( $user_id );
		if ( empty( $user_id ) )
			return false;

		$time = get_user_option( '_psf_last_posted', $user_id );

		return apply_filters( 'psf_get_user_last_posted', $time, $user_id );
	}
