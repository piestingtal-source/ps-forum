<?php

/**
 * PSForum Admin Actions
 *
 * @package PSForum
 * @subpackage Admin
 *
 * This file contains the actions that are used through-out PSForum Admin. They
 * are consolidated here to make searching for them easier, and to help developers
 * understand at a glance the order in which things occur.
 *
 * There are a few common places that additional actions can currently be found
 *
 *  - PSForum: In {@link PSForum::setup_actions()} in psforum.php
 *  - Admin: More in {@link PSF_Admin::setup_actions()} in admin.php
 *
 * @see psf-core-actions.php
 * @see psf-core-filters.php
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Attach PSForum to WordPress
 *
 * PSForum uses its own internal actions to help aid in third-party plugin
 * development, and to limit the amount of potential future code changes when
 * updates to WordPress core occur.
 *
 * These actions exist to create the concept of 'plugin dependencies'. They
 * provide a safe way for plugins to execute code *only* when PSForum is
 * installed and activated, without needing to do complicated guesswork.
 *
 * For more information on how this works, see the 'Plugin Dependency' section
 * near the bottom of this file.
 *
 *           v--WordPress Actions       v--PSForum Sub-actions
 */
add_action( 'admin_menu',              'psf_admin_menu'                    );
add_action( 'admin_init',              'psf_admin_init'                    );
add_action( 'admin_head',              'psf_admin_head'                    );
add_action( 'admin_notices',           'psf_admin_notices'                 );
add_action( 'custom_menu_order',       'psf_admin_custom_menu_order'       );
add_action( 'menu_order',              'psf_admin_menu_order'              );
add_action( 'wpmu_new_blog',           'psf_new_site',               10, 6 );

// Hook on to admin_init
add_action( 'psf_admin_init', 'psf_admin_forums'                );
add_action( 'psf_admin_init', 'psf_admin_topics'                );
add_action( 'psf_admin_init', 'psf_admin_replies'               );
add_action( 'psf_admin_init', 'psf_setup_updater',          999 );
add_action( 'psf_admin_init', 'psf_register_importers'          );
add_action( 'psf_admin_init', 'psf_register_admin_style'        );
add_action( 'psf_admin_init', 'psf_register_admin_settings'     );
add_action( 'psf_admin_init', 'psf_do_activation_redirect', 1   );

// Initialize the admin area
add_action( 'psf_init', 'psf_admin' );

// Reset the menu order
add_action( 'psf_admin_menu', 'psf_admin_separator' );

// Activation
add_action( 'psf_activation', 'psf_delete_rewrite_rules'        );
add_action( 'psf_activation', 'psf_make_current_user_keymaster' );

// Deactivation
add_action( 'psf_deactivation', 'psf_remove_caps'          );
add_action( 'psf_deactivation', 'psf_delete_rewrite_rules' );

// New Site
add_action( 'psf_new_site', 'psf_create_initial_content', 8 );

// Contextual Helpers
add_action( 'load-settings_page_psforum', 'psf_admin_settings_help' );

// Handle submission of Tools pages
add_action( 'load-tools_page_psf-repair', 'psf_admin_repair_handler' );
add_action( 'load-tools_page_psf-reset',  'psf_admin_reset_handler'  );

// Add sample permalink filter
add_filter( 'post_type_link', 'psf_filter_sample_permalink', 10, 4 );

/**
 * When a new site is created in a multisite installation, run the activation
 * routine on that site
 *
 * @since PSForum (r3283)
 *
 * @param int $blog_id
 * @param int $user_id
 * @param string $domain
 * @param string $path
 * @param int $site_id
 * @param array() $meta
 */
function psf_new_site( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

	// Bail if plugin is not network activated
	if ( ! is_plugin_active_for_network( psforum()->basename ) )
		return;

	// Switch to the new blog
	switch_to_blog( $blog_id );

	// Do the PSForum activation routine
	do_action( 'psf_new_site', $blog_id, $user_id, $domain, $path, $site_id, $meta );

	// restore original blog
	restore_current_blog();
}

/** Sub-Actions ***************************************************************/

/**
 * Piggy back admin_init action
 *
 * @since PSForum (r3766)
 * @uses do_action() Calls 'psf_admin_init'
 */
function psf_admin_init() {
	do_action( 'psf_admin_init' );
}

/**
 * Piggy back admin_menu action
 *
 * @since PSForum (r3766)
 * @uses do_action() Calls 'psf_admin_menu'
 */
function psf_admin_menu() {
	do_action( 'psf_admin_menu' );
}

/**
 * Piggy back admin_head action
 *
 * @since PSForum (r3766)
 * @uses do_action() Calls 'psf_admin_head'
 */
function psf_admin_head() {
	do_action( 'psf_admin_head' );
}

/**
 * Piggy back admin_notices action
 *
 * @since PSForum (r3766)
 * @uses do_action() Calls 'psf_admin_notices'
 */
function psf_admin_notices() {
	do_action( 'psf_admin_notices' );
}

/**
 * Dedicated action to register PSForum importers
 *
 * @since PSForum (r3766)
 * @uses do_action() Calls 'psf_admin_notices'
 */
function psf_register_importers() {
	do_action( 'psf_register_importers' );
}

/**
 * Dedicated action to register admin styles
 *
 * @since PSForum (r3766)
 * @uses do_action() Calls 'psf_admin_notices'
 */
function psf_register_admin_style() {
	do_action( 'psf_register_admin_style' );
}

/**
 * Dedicated action to register admin settings
 *
 * @since PSForum (r3766)
 * @uses do_action() Calls 'psf_register_admin_settings'
 */
function psf_register_admin_settings() {
	do_action( 'psf_register_admin_settings' );
}
