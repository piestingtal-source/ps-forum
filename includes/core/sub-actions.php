<?php

/**
 * Plugin Dependency
 *
 * The purpose of the following hooks is to mimic the behavior of something
 * called 'plugin dependency' which enables a plugin to have plugins of their
 * own in a safe and reliable way.
 *
 * We do this in PSForum by mirroring existing WordPress hookss in many places
 * allowing dependant plugins to hook into the PSForum specific ones, thus
 * guaranteeing proper code execution only when PSForum is active.
 *
 * The following functions are wrappers for hookss, allowing them to be
 * manually called and/or piggy-backed on top of other hooks if needed.
 *
 * @todo use anonymous functions when PHP minimun requirement allows (5.3)
 */

/** Activation Actions ********************************************************/

/**
 * Runs on PSForum activation
 *
 * @since PSForum (r2509)
 * @uses register_uninstall_hook() To register our own uninstall hook
 * @uses do_action() Calls 'psf_activation' hook
 */
function psf_activation() {
	do_action( 'psf_activation' );
}

/**
 * Runs on PSForum deactivation
 *
 * @since PSForum (r2509)
 * @uses do_action() Calls 'psf_deactivation' hook
 */
function psf_deactivation() {
	do_action( 'psf_deactivation' );
}

/**
 * Runs when uninstalling PSForum
 *
 * @since PSForum (r2509)
 * @uses do_action() Calls 'psf_uninstall' hook
 */
function psf_uninstall() {
	do_action( 'psf_uninstall' );
}

/** Main Actions **************************************************************/

/**
 * Main action responsible for constants, globals, and includes
 *
 * @since PSForum (r2599)
 * @uses do_action() Calls 'psf_loaded'
 */
function psf_loaded() {
	do_action( 'psf_loaded' );
}

/**
 * Setup constants
 *
 * @since PSForum (r2599)
 * @uses do_action() Calls 'psf_constants'
 */
function psf_constants() {
	do_action( 'psf_constants' );
}

/**
 * Setup globals BEFORE includes
 *
 * @since PSForum (r2599)
 * @uses do_action() Calls 'psf_boot_strap_globals'
 */
function psf_boot_strap_globals() {
	do_action( 'psf_boot_strap_globals' );
}

/**
 * Include files
 *
 * @since PSForum (r2599)
 * @uses do_action() Calls 'psf_includes'
 */
function psf_includes() {
	do_action( 'psf_includes' );
}

/**
 * Setup globals AFTER includes
 *
 * @since PSForum (r2599)
 * @uses do_action() Calls 'psf_setup_globals'
 */
function psf_setup_globals() {
	do_action( 'psf_setup_globals' );
}

/**
 * Register any objects before anything is initialized
 *
 * @since PSForum (r4180)
 * @uses do_action() Calls 'psf_register'
 */
function psf_register() {
	do_action( 'psf_register' );
}

/**
 * Initialize any code after everything has been loaded
 *
 * @since PSForum (r2599)
 * @uses do_action() Calls 'psf_init'
 */
function psf_init() {
	do_action( 'psf_init' );
}

/**
 * Initialize widgets
 *
 * @since PSForum (r3389)
 * @uses do_action() Calls 'psf_widgets_init'
 */
function psf_widgets_init() {
	do_action( 'psf_widgets_init' );
}

/**
 * Initialize roles
 *
 * @since PSForum (r6147)
 *
 * @param WP_Roles $wp_roles The main WordPress roles global
 *
 * @uses do_action() Calls 'psf_roles_init'
 */
function psf_roles_init( $wp_roles = null ) {
	do_action( 'psf_roles_init', $wp_roles );
}

/**
 * Setup the currently logged-in user
 *
 * @since PSForum (r2695)
 * @uses do_action() Calls 'psf_setup_current_user'
 */
function psf_setup_current_user() {
	do_action( 'psf_setup_current_user' );
}

/** Supplemental Actions ******************************************************/

/**
 * Load translations for current language
 *
 * @since PSForum (r2599)
 * @uses do_action() Calls 'psf_load_textdomain'
 */
function psf_load_textdomain() {
	do_action( 'psf_load_textdomain' );
}

/**
 * Setup the post types
 *
 * @since PSForum (r2464)
 * @uses do_action() Calls 'psf_register_post_type'
 */
function psf_register_post_types() {
	do_action( 'psf_register_post_types' );
}

/**
 * Setup the post statuses
 *
 * @since PSForum (r2727)
 * @uses do_action() Calls 'psf_register_post_statuses'
 */
function psf_register_post_statuses() {
	do_action( 'psf_register_post_statuses' );
}

/**
 * Register the built in PSForum taxonomies
 *
 * @since PSForum (r2464)
 * @uses do_action() Calls 'psf_register_taxonomies'
 */
function psf_register_taxonomies() {
	do_action( 'psf_register_taxonomies' );
}

/**
 * Register the default PSForum views
 *
 * @since PSForum (r2789)
 * @uses do_action() Calls 'psf_register_views'
 */
function psf_register_views() {
	do_action( 'psf_register_views' );
}

/**
 * Register the default PSForum shortcodes
 *
 * @since PSForum (r4211)
 * @uses do_action() Calls 'psf_register_shortcodes'
 */
function psf_register_shortcodes() {
	do_action( 'psf_register_shortcodes' );
}

/**
 * Enqueue PSForum specific CSS and JS
 *
 * @since PSForum (r3373)
 * @uses do_action() Calls 'psf_enqueue_scripts'
 */
function psf_enqueue_scripts() {
	do_action( 'psf_enqueue_scripts' );
}

/**
 * Add the PSForum-specific rewrite tags
 *
 * @since PSForum (r2753)
 * @uses do_action() Calls 'psf_add_rewrite_tags'
 */
function psf_add_rewrite_tags() {
	do_action( 'psf_add_rewrite_tags' );
}

/**
 * Add the PSForum-specific rewrite rules
 *
 * @since PSForum (r4918)
 * @uses do_action() Calls 'psf_add_rewrite_rules'
 */
function psf_add_rewrite_rules() {
	do_action( 'psf_add_rewrite_rules' );
}

/**
 * Add the PSForum-specific permalink structures
 *
 * @since PSForum (r4918)
 * @uses do_action() Calls 'psf_add_permastructs'
 */
function psf_add_permastructs() {
	do_action( 'psf_add_permastructs' );
}

/**
 * Add the PSForum-specific login forum action
 *
 * @since PSForum (r2753)
 * @uses do_action() Calls 'psf_login_form_login'
 */
function psf_login_form_login() {
	do_action( 'psf_login_form_login' );
}

/** User Actions **************************************************************/

/**
 * The main action for hooking into when a user account is updated
 *
 * @since PSForum (r4304)
 *
 * @param int $user_id ID of user being edited
 * @param array $old_user_data The old, unmodified user data
 * @uses do_action() Calls 'psf_profile_update'
 */
function psf_profile_update( $user_id = 0, $old_user_data = array() ) {
	do_action( 'psf_profile_update', $user_id, $old_user_data );
}

/**
 * The main action for hooking into a user being registered
 *
 * @since PSForum (r4304)
 * @param int $user_id ID of user being edited
 * @uses do_action() Calls 'psf_user_register'
 */
function psf_user_register( $user_id = 0 ) {
	do_action( 'psf_user_register', $user_id );
}

/** Final Action **************************************************************/

/**
 * PSForum has loaded and initialized everything, and is okay to go
 *
 * @since PSForum (r2618)
 * @uses do_action() Calls 'psf_ready'
 */
function psf_ready() {
	do_action( 'psf_ready' );
}

/** Theme Permissions *********************************************************/

/**
 * The main action used for redirecting PSForum theme actions that are not
 * permitted by the current_user
 *
 * @since PSForum (r3605)
 * @uses do_action()
 */
function psf_template_redirect() {
	do_action( 'psf_template_redirect' );
}

/** Theme Helpers *************************************************************/

/**
 * The main action used for executing code before the theme has been setup
 *
 * @since PSForum (r3829)
 * @uses do_action()
 */
function psf_register_theme_packages() {
	do_action( 'psf_register_theme_packages' );
}

/**
 * The main action used for executing code before the theme has been setup
 *
 * @since PSForum (r3732)
 * @uses do_action()
 */
function psf_setup_theme() {
	do_action( 'psf_setup_theme' );
}

/**
 * The main action used for executing code after the theme has been setup
 *
 * @since PSForum (r3732)
 * @uses do_action()
 */
function psf_after_setup_theme() {
	do_action( 'psf_after_setup_theme' );
}

/**
 * The main action used for handling theme-side POST requests
 *
 * @since PSForum (r4550)
 * @uses do_action()
 */
function psf_post_request() {

	// Bail if not a POST action
	if ( ! psf_is_post_request() )
		return;

	// Bail if no action
	if ( empty( $_POST['action'] ) )
		return;

	// This dynamic action is probably the one you want to use. It narrows down
	// the scope of the 'action' without needing to check it in your function.
	do_action( 'psf_post_request_' . $_POST['action'] );

	// Use this static action if you don't mind checking the 'action' yourself.
	do_action( 'psf_post_request',   $_POST['action'] );
}

/**
 * The main action used for handling theme-side GET requests
 *
 * @since PSForum (r4550)
 * @uses do_action()
 */
function psf_get_request() {

	// Bail if not a POST action
	if ( ! psf_is_get_request() )
		return;

	// Bail if no action
	if ( empty( $_GET['action'] ) )
		return;

	// This dynamic action is probably the one you want to use. It narrows down
	// the scope of the 'action' without needing to check it in your function.
	do_action( 'psf_get_request_' . $_GET['action'] );

	// Use this static action if you don't mind checking the 'action' yourself.
	do_action( 'psf_get_request',   $_GET['action'] );
}

/** Filters *******************************************************************/

/**
 * Filter the plugin locale and domain.
 *
 * @since PSForum (r4213)
 *
 * @param string $locale
 * @param string $domain
 */
function psf_plugin_locale( $locale = '', $domain = '' ) {
	return apply_filters( 'psf_plugin_locale', $locale, $domain );
}

/**
 * Piggy back filter for WordPress's 'request' filter
 *
 * @since PSForum (r3758)
 * @param array $query_vars
 * @return array
 */
function psf_request( $query_vars = array() ) {
	return apply_filters( 'psf_request', $query_vars );
}

/**
 * The main filter used for theme compatibility and displaying custom PSForum
 * theme files.
 *
 * @since PSForum (r3311)
 * @uses apply_filters()
 * @param string $template
 * @return string Template file to use
 */
function psf_template_include( $template = '' ) {
	return apply_filters( 'psf_template_include', $template );
}

/**
 * Generate PSForum-specific rewrite rules
 *
 * @since PSForum (r2688)
 * @deprecated since PSForum (r4918)
 * @param WP_Rewrite $wp_rewrite
 * @uses do_action() Calls 'psf_generate_rewrite_rules' with {@link WP_Rewrite}
 */
function psf_generate_rewrite_rules( $wp_rewrite ) {
	do_action_ref_array( 'psf_generate_rewrite_rules', array( &$wp_rewrite ) );
}

/**
 * Filter the allowed themes list for PSForum specific themes
 *
 * @since PSForum (r2944)
 * @uses apply_filters() Calls 'psf_allowed_themes' with the allowed themes list
 */
function psf_allowed_themes( $themes ) {
	return apply_filters( 'psf_allowed_themes', $themes );
}

/**
 * Maps forum/topic/reply caps to built in WordPress caps
 *
 * @since PSForum (r2593)
 *
 * @param array $caps Capabilities for meta capability
 * @param string $cap Capability name
 * @param int $user_id User id
 * @param mixed $args Arguments
 */
function psf_map_meta_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {
	return apply_filters( 'psf_map_meta_caps', $caps, $cap, $user_id, $args );
}
