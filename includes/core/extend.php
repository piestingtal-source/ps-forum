<?php

/**
 * PSForum Extentions
 *
 * There's a world of really cool plugins out there, and PSForum comes with
 * support for some of the most popular ones.
 *
 * @package PSForum
 * @subpackage Extend
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Loads Akismet inside the PSForum global class
 *
 * @since PSForum (r3277)
 *
 * @return If PSForum is not active
 */
function psf_setup_akismet() {

	// Bail if no akismet
	if ( !defined( 'AKISMET_VERSION' ) ) return;

	// Bail if Akismet is turned off
	if ( !psf_is_akismet_active() ) return;

	// Include the Akismet Component
	require( psforum()->includes_dir . 'extend/akismet.php' );

	// Instantiate Akismet for PSForum
	psforum()->extend->akismet = new PSF_Akismet();
}

/**
 * Requires and creates the BuddyPress extension, and adds component creation
 * action to bp_init hook. @see psf_setup_buddypress_component()
 *
 * @since PSForum (r3395)
 * @return If BuddyPress is not active
 */
function psf_setup_buddypress() {

	if ( ! function_exists( 'buddypress' ) ) {

		/**
		 * Helper for BuddyPress 1.6 and earlier
		 *
		 * @since PSForum (r4395)
		 * @return BuddyPress
		 */
		function buddypress() {
			return isset( $GLOBALS['bp'] ) ? $GLOBALS['bp'] : false;
		}
	}

	// Bail if in maintenance mode
	if ( ! buddypress() || buddypress()->maintenance_mode )
		return;

	// Include the BuddyPress Component
	require( psforum()->includes_dir . 'extend/buddypress/loader.php' );

	// Instantiate BuddyPress for PSForum
	psforum()->extend->buddypress = new PSF_Forums_Component();
}
