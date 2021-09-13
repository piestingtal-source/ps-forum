<?php

/**
 * Main PSForum Admin Class
 *
 * @package PSForum
 * @subpackage Administration
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'PSF_Admin' ) ) :
/**
 * Loads PSForum plugin admin area
 *
 * @package PSForum
 * @subpackage Administration
 * @since PSForum (r2464)
 */
class PSF_Admin {

	/** Directory *************************************************************/

	/**
	 * @var string Path to the PSForum admin directory
	 */
	public $admin_dir = '';

	/** URLs ******************************************************************/

	/**
	 * @var string URL to the PSForum admin directory
	 */
	public $admin_url = '';

	/**
	 * @var string URL to the PSForum images directory
	 */
	public $images_url = '';

	/**
	 * @var string URL to the PSForum admin styles directory
	 */
	public $styles_url = '';

	/**
	 * @var string URL to the PSForum admin css directory
	 */
	public $css_url = '';

	/**
	 * @var string URL to the PSForum admin js directory
	 */
	public $js_url = '';

	/** Capability ************************************************************/

	/**
	 * @var bool Minimum capability to access Tools and Settings
	 */
	public $minimum_capability = 'keep_gate';

	/** Separator *************************************************************/

	/**
	 * @var bool Whether or not to add an extra top level menu separator
	 */
	public $show_separator = false;

	/** Functions *************************************************************/

	/**
	 * The main PSForum admin loader
	 *
	 * @since PSForum (r2515)
	 *
	 * @uses PSF_Admin::setup_globals() Setup the globals needed
	 * @uses PSF_Admin::includes() Include the required files
	 * @uses PSF_Admin::setup_actions() Setup the hooks and actions
	 */
	public function __construct() {
		$this->setup_globals();
		$this->includes();
		$this->setup_actions();
	}

	/**
	 * Admin globals
	 *
	 * @since PSForum (r2646)
	 * @access private
	 */
	private function setup_globals() {
		$psf = psforum();
		$this->admin_dir  = trailingslashit( $psf->includes_dir . 'admin'  ); // Admin path
		$this->admin_url  = trailingslashit( $psf->includes_url . 'admin'  ); // Admin url
		$this->images_url = trailingslashit( $this->admin_url   . 'images' ); // Admin images URL
		$this->styles_url = trailingslashit( $this->admin_url   . 'styles' ); // Admin styles URL
		$this->css_url    = trailingslashit( $this->admin_url   . 'css'    ); // Admin css URL
		$this->js_url     = trailingslashit( $this->admin_url   . 'js'     ); // Admin js URL
	}

	/**
	 * Include required files
	 *
	 * @since PSForum (r2646)
	 * @access private
	 */
	private function includes() {
		require( $this->admin_dir . 'tools.php'     );
		require( $this->admin_dir . 'converter.php' );
		require( $this->admin_dir . 'settings.php'  );
		require( $this->admin_dir . 'functions.php' );
		require( $this->admin_dir . 'metaboxes.php' );
		require( $this->admin_dir . 'forums.php'    );
		require( $this->admin_dir . 'topics.php'    );
		require( $this->admin_dir . 'replies.php'   );
		require( $this->admin_dir . 'users.php'     );
	}

	/**
	 * Setup the admin hooks, actions and filters
	 *
	 * @since PSForum (r2646)
	 * @access private
	 *
	 * @uses add_action() To add various actions
	 * @uses add_filter() To add various filters
	 */
	private function setup_actions() {

		// Bail to prevent interfering with the deactivation process
		if ( psf_is_deactivation() )
			return;

		/** General Actions ***************************************************/

		add_action( 'psf_admin_menu',              array( $this, 'admin_menus'                )     ); // Add menu item to settings menu
		add_action( 'psf_admin_head',              array( $this, 'admin_head'                 )     ); // Add some general styling to the admin area
		add_action( 'psf_admin_notices',           array( $this, 'activation_notice'          )     ); // Add notice if not using a PSForum theme
		add_action( 'psf_register_admin_style',    array( $this, 'register_admin_style'       )     ); // Add green admin style
		add_action( 'psf_register_admin_settings', array( $this, 'register_admin_settings'    )     ); // Add settings
		add_action( 'psf_activation',              array( $this, 'new_install'                )     ); // Add menu item to settings menu
		add_action( 'admin_enqueue_scripts',       array( $this, 'enqueue_styles'             )     ); // Add enqueued CSS
		add_action( 'admin_enqueue_scripts',       array( $this, 'enqueue_scripts'            )     ); // Add enqueued JS
		add_action( 'wp_dashboard_setup',          array( $this, 'dashboard_widget_right_now' )     ); // Forums 'Right now' Dashboard widget
		add_action( 'admin_bar_menu',              array( $this, 'admin_bar_about_link'       ), 15 ); // Add a link to PSForum about page to the admin bar

		/** Ajax **************************************************************/

		// No _nopriv_ equivalent - users must be logged in
		add_action( 'wp_ajax_psf_suggest_topic', array( $this, 'suggest_topic' ) );
		add_action( 'wp_ajax_psf_suggest_user',  array( $this, 'suggest_user'  ) );

		/** Filters ***********************************************************/

		// Modify PSForum's admin links
		add_filter( 'plugin_action_links', array( $this, 'modify_plugin_action_links' ), 10, 2 );

		// Map settings capabilities
		add_filter( 'psf_map_meta_caps',   array( $this, 'map_settings_meta_caps' ), 10, 4 );

		// Hide the theme compat package selection
		add_filter( 'psf_admin_get_settings_sections', array( $this, 'hide_theme_compat_packages' ) );

		// Allow keymasters to save forums settings
		add_filter( 'option_page_capability_psforum',  array( $this, 'option_page_capability_psforum' ) );

		/** Network Admin *****************************************************/

		// Add menu item to settings menu
		add_action( 'network_admin_menu',  array( $this, 'network_admin_menus' ) );

		/** Dependencies ******************************************************/

		// Allow plugins to modify these actions
		do_action_ref_array( 'psf_admin_loaded', array( &$this ) );
	}

	/**
	 * Add the admin menus
	 *
	 * @since PSForum (r2646)
	 *
	 * @uses add_management_page() To add the Recount page in Tools section
	 * @uses add_options_page() To add the Forums settings page in Settings
	 *                           section
	 */
	public function admin_menus() {

		$hooks = array();

		// These are later removed in admin_head
		if ( current_user_can( 'psf_tools_page' ) ) {
			if ( current_user_can( 'psf_tools_repair_page' ) ) {
				$hooks[] = add_management_page(
					__( 'Repariere Foren', 'psforum' ),
					__( 'Forum Reparatur',  'psforum' ),
					$this->minimum_capability,
					'psf-repair',
					'psf_admin_repair'
				);
			}

			if ( current_user_can( 'psf_tools_import_page' ) ) {
				$hooks[] = add_management_page(
					__( 'Foren importieren', 'psforum' ),
					__( 'Forenimport',  'psforum' ),
					$this->minimum_capability,
					'psf-converter',
					'psf_converter_settings'
				);
			}

			if ( current_user_can( 'psf_tools_reset_page' ) ) {
				$hooks[] = add_management_page(
					__( 'Foren zurücksetzen', 'psforum' ),
					__( 'Forum zurücksetzen',  'psforum' ),
					$this->minimum_capability,
					'psf-reset',
					'psf_admin_reset'
				);
			}

			// Fudge the highlighted subnav item when on a PSForum admin page
			foreach ( $hooks as $hook ) {
				add_action( "admin_head-$hook", 'psf_tools_modify_menu_highlight' );
			}

			// Forums Tools Root
			add_management_page(
				__( 'PS Forum Tools', 'psforum' ),
				__( 'PS Forum Tools', 'psforum' ),
				$this->minimum_capability,
				'psf-repair',
				'psf_admin_repair'
			);
		}

		// Are settings enabled?
		if ( ! psf_settings_integration() && current_user_can( 'psf_settings_page' ) ) {
			add_options_page(
				__( 'PS Forum',  'psforum' ),
				__( 'PS Forum',  'psforum' ),
				$this->minimum_capability,
				'psforum',
				'psf_admin_settings'
			);
		}

		// These are later removed in admin_head
		if ( current_user_can( 'psf_about_page' ) ) {

			// About
			add_dashboard_page(
				__( 'Willkommen bei PS Forum',  'psforum' ),
				__( 'Willkommen bei PS Forum',  'psforum' ),
				$this->minimum_capability,
				'psf-about',
				array( $this, 'about_screen' )
			);

			// Credits
			add_dashboard_page(
				__( 'Willkommen bei PS Forum',  'psforum' ),
				__( 'Willkommen bei PS Forum',  'psforum' ),
				$this->minimum_capability,
				'psf-credits',
				array( $this, 'credits_screen' )
			);
		}

		// Bail if plugin is not network activated
		if ( ! is_plugin_active_for_network( psforum()->basename ) )
			return;

		add_submenu_page(
			'index.php',
			__( 'Foren aktualisieren', 'psforum' ),
			__( 'Foren aktualisieren', 'psforum' ),
			'manage_network',
			'psf-update',
			array( $this, 'update_screen' )
		);
	}

	/**
	 * Add the network admin menus
	 *
	 * @since PSForum (r3689)
	 * @uses add_submenu_page() To add the Foren aktualisieren page in Updates
	 */
	public function network_admin_menus() {

		// Bail if plugin is not network activated
		if ( ! is_plugin_active_for_network( psforum()->basename ) )
			return;

		add_submenu_page(
			'upgrade.php',
			__( 'Foren aktualisieren', 'psforum' ),
			__( 'Foren aktualisieren', 'psforum' ),
			'manage_network',
			'psforum-update',
			array( $this, 'network_update_screen' )
		);
	}

	/**
	 * If this is a new installation, create some initial forum content.
	 *
	 * @since PSForum (r3767)
	 * @return type
	 */
	public static function new_install() {
		if ( !psf_is_install() )
			return;

		psf_create_initial_content();
	}

	/**
	 * Register the settings
	 *
	 * @since PSForum (r2737)
	 *
	 * @uses add_settings_section() To add our own settings section
	 * @uses add_settings_field() To add various settings fields
	 * @uses register_setting() To register various settings
	 * @todo Put fields into multidimensional array
	 */
	public static function register_admin_settings() {

		// Bail if no sections available
		$sections = psf_admin_get_settings_sections();
		if ( empty( $sections ) )
			return false;

		// Are we using settings integration?
		$settings_integration = psf_settings_integration();

		// Loop through sections
		foreach ( (array) $sections as $section_id => $section ) {

			// Only proceed if current user can see this section
			if ( ! current_user_can( $section_id ) )
				continue;

			// Only add section and fields if section has fields
			$fields = psf_admin_get_settings_fields_for_section( $section_id );
			if ( empty( $fields ) )
				continue;

			// Toggle the section if core integration is on
			if ( ( true === $settings_integration ) && !empty( $section['page'] ) ) {
				$page = $section['page'];
			} else {
				$page = 'psforum';
			}

			// Add the section
			add_settings_section( $section_id, $section['title'], $section['callback'], $page );

			// Loop through fields for this section
			foreach ( (array) $fields as $field_id => $field ) {

				// Add the field
				if ( ! empty( $field['callback'] ) && !empty( $field['title'] ) ) {
					add_settings_field( $field_id, $field['title'], $field['callback'], $page, $section_id, $field['args'] );
				}

				// Register the setting
				register_setting( $page, $field_id, $field['sanitize_callback'] );
			}
		}
	}

	/**
	 * Maps settings capabilities
	 *
	 * @since PSForum (r4242)
	 *
	 * @param array $caps Capabilities for meta capability
	 * @param string $cap Capability name
	 * @param int $user_id User id
	 * @param mixed $args Arguments
	 * @uses get_post() To get the post
	 * @uses apply_filters() Calls 'psf_map_meta_caps' with caps, cap, user id and
	 *                        args
	 * @return array Actual capabilities for meta capability
	 */
	public static function map_settings_meta_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {

		// What capability is being checked?
		switch ( $cap ) {

			// BuddyPress
			case 'psf_settings_buddypress' :
				if ( ( is_plugin_active( 'buddypress/bp-loader.php' ) && defined( 'BP_VERSION' ) && bp_is_root_blog() ) && is_super_admin() ) {
					$caps = array( psforum()->admin->minimum_capability );
				} else {
					$caps = array( 'do_not_allow' );
				}

				break;

			// Akismet
			case 'psf_settings_akismet' :
				if ( ( is_plugin_active( 'akismet/akismet.php' ) && defined( 'AKISMET_VERSION' ) ) && is_super_admin() ) {
					$caps = array( psforum()->admin->minimum_capability );
				} else {
					$caps = array( 'do_not_allow' );
				}

				break;

			// PSForum
			case 'psf_about_page'            : // About and Credits
			case 'psf_tools_page'            : // Tools Page
			case 'psf_tools_repair_page'     : // Tools - Repair Page
			case 'psf_tools_import_page'     : // Tools - Import Page
			case 'psf_tools_reset_page'      : // Tools - Reset Page
			case 'psf_settings_page'         : // Settings Page
			case 'psf_settings_users'        : // Settings - Users
			case 'psf_settings_features'     : // Settings - Features
			case 'psf_settings_theme_compat' : // Settings - Theme compat
			case 'psf_settings_root_slugs'   : // Settings - Root slugs
			case 'psf_settings_single_slugs' : // Settings - Single slugs
			case 'psf_settings_user_slugs'   : // Settings - User slugs
			case 'psf_settings_per_page'     : // Settings - Per page
			case 'psf_settings_per_rss_page' : // Settings - Per RSS page
				$caps = array( psforum()->admin->minimum_capability );
				break;
		}

		return apply_filters( 'psf_map_settings_meta_caps', $caps, $cap, $user_id, $args );
	}

	/**
	 * Register the importers
	 *
	 * @since PSForum (r2737)
	 *
	 * @uses apply_filters() Calls 'psf_importer_path' filter to allow plugins
	 *                        to customize the importer script locations.
	 */
	public function register_importers() {

		// Leave if we're not in the import section
		if ( !defined( 'WP_LOAD_IMPORTERS' ) )
			return;

		// Load Importer API
		require_once( ABSPATH . 'wp-admin/includes/import.php' );

		// Load our importers
		$importers = apply_filters( 'psf_importers', array( 'psforum' ) );

		// Loop through included importers
		foreach ( $importers as $importer ) {

			// Allow custom importer directory
			$import_dir  = apply_filters( 'psf_importer_path', $this->admin_dir . 'importers', $importer );

			// Compile the importer path
			$import_file = trailingslashit( $import_dir ) . $importer . '.php';

			// If the file exists, include it
			if ( file_exists( $import_file ) ) {
				require( $import_file );
			}
		}
	}

	/**
	 * Admin area activation notice
	 *
	 * Shows a nag message in admin area about the theme not supporting PSForum
	 *
	 * @since PSForum (r2743)
	 *
	 * @uses current_user_can() To check notice should be displayed.
	 */
	public function activation_notice() {
		// @todo - something fun
	}

	/**
	 * Add Settings link to plugins area
	 *
	 * @since PSForum (r2737)
	 *
	 * @param array $links Links array in which we would prepend our link
	 * @param string $file Current plugin basename
	 * @return array Processed links
	 */
	public static function modify_plugin_action_links( $links, $file ) {

		// Return normal links if not PSForum
		if ( plugin_basename( psforum()->file ) !== $file ) {
			return $links;
		}

		// New links to merge into existing links
		$new_links = array();

		// Settings page link
		if ( current_user_can( 'psf_settings_page' ) ) {
			$new_links['settings'] = '<a href="' . esc_url( add_query_arg( array( 'page' => 'psforum'   ), admin_url( 'options-general.php' ) ) ) . '">' . esc_html__( 'Einstellungen', 'psforum' ) . '</a>';
		}

		// About page link
		if ( current_user_can( 'psf_about_page' ) ) {
			$new_links['about']    = '<a href="' . esc_url( add_query_arg( array( 'page' => 'psf-about' ), admin_url( 'index.php'           ) ) ) . '">' . esc_html__( 'Über',    'psforum' ) . '</a>';
		}

		// Add a few links to the existing links array
		return array_merge( $links, $new_links );
	}

	/**
	 * Add the 'Right now in Forums' dashboard widget
	 *
	 * @since PSForum (r2770)
	 *
	 * @uses wp_add_dashboard_widget() To add the dashboard widget
	 */
	public static function dashboard_widget_right_now() {
		wp_add_dashboard_widget( 'psf-dashboard-right-now', __( 'Jetzt in Foren', 'psforum' ), 'psf_dashboard_widget_right_now' );
	}

	/**
	 * Add a link to PSForum about page to the admin bar
	 *
	 * @since PSForum (r5136)
	 *
	 * @param WP_Admin_Bar $wp_admin_bar
	 */
	public function admin_bar_about_link( $wp_admin_bar ) {
		if ( is_user_logged_in() ) {
			$wp_admin_bar->add_menu( array(
				'parent' => 'wp-logo',
				'id'     => 'psf-about',
				'title'  => esc_html__( 'Über PS Forum', 'psforum' ),
				'href'   => add_query_arg( array( 'page' => 'psf-about' ), admin_url( 'index.php' ) )
			) );
		}
	}

	/**
	 * Enqueue any admin scripts we might need
	 *
	 * @since PSForum (r4260)
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'suggest' );

		// Get the version to use for JS
		$version = psf_get_version();

		// Post type checker (only topics and replies)
		if ( 'post' === get_current_screen()->base ) {
			switch( get_current_screen()->post_type ) {
				case psf_get_reply_post_type() :
				case psf_get_topic_post_type() :

					// Enqueue the common JS
					wp_enqueue_script( 'psf-admin-common-js', $this->js_url . 'common.js', array( 'jquery' ), $version );

					// Topics admin
					if ( psf_get_topic_post_type() === get_current_screen()->post_type ) {
						wp_enqueue_script( 'psf-admin-topics-js', $this->js_url . 'topics.js', array( 'jquery' ), $version );

					// Replies admin
					} elseif ( psf_get_reply_post_type() === get_current_screen()->post_type ) {
						wp_enqueue_script( 'psf-admin-replies-js', $this->js_url . 'replies.js', array( 'jquery' ), $version );
					}

					break;
			}
		}
	}

	/**
	 * Enqueue any admin scripts we might need
	 *
	 * @since PSForum (r5224)
	 */
	public function enqueue_styles() {
		wp_enqueue_style( 'psf-admin-css', $this->css_url . 'admin.css', array( 'dashicons' ), psf_get_version() );
	}

	/**
	 * Remove the individual recount and converter menus.
	 * They are grouped together by h2 tabs
	 *
	 * @since PSForum (r2464)
	 *
	 * @uses remove_submenu_page() To remove menu items with alternat navigation
	 */
	public function admin_head() {
		remove_submenu_page( 'tools.php', 'psf-repair'    );
		remove_submenu_page( 'tools.php', 'psf-converter' );
		remove_submenu_page( 'tools.php', 'psf-reset'     );
		remove_submenu_page( 'index.php', 'psf-about'     );
		remove_submenu_page( 'index.php', 'psf-credits'   );
	}

	/**
	 * Registers the PSForum admin color scheme
	 *
	 * Because wp-content can exist outside of the WordPress root there is no
	 * way to be certain what the relative path of the admin images is.
	 * We are including the two most common configurations here, just in case.
	 *
	 * @since PSForum (r2521)
	 *
	 * @uses wp_admin_css_color() To register the color scheme
	 */
	public function register_admin_style () {

		// RTL and/or minified
		$suffix = is_rtl() ? '-rtl' : '';
		//$suffix .= defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Mint
		wp_admin_css_color(
			'psf-mint',
			esc_html_x( 'Mint',      'admin color scheme', 'psforum' ),
			$this->styles_url . 'mint' . $suffix . '.css',
			array( '#4f6d59', '#33834e', '#5FB37C', '#81c498' ),
			array( 'base' => '#f1f3f2', 'focus' => '#fff', 'current' => '#fff' )
		);

		// Evergreen
		wp_admin_css_color(
			'psf-evergreen',
			esc_html_x( 'Evergreen', 'admin color scheme', 'psforum' ),
			$this->styles_url . 'evergreen' . $suffix . '.css',
			array( '#324d3a', '#446950', '#56b274', '#324d3a' ),
			array( 'base' => '#f1f3f2', 'focus' => '#fff', 'current' => '#fff' )
		);

		// Bail if already using the fresh color scheme
		if ( 'fresh' === get_user_option( 'admin_color' ) ) {
			return;
		}

		// Force 'colors-fresh' dependency
		global $wp_styles;
		$wp_styles->registered[ 'colors' ]->deps[] = 'colors-fresh';
	}

	/**
	 * Hide theme compat package selection if only 1 package is registered
	 *
	 * @since PSForum (r4315)
	 *
	 * @param array $sections Forums settings sections
	 * @return array
	 */
	public function hide_theme_compat_packages( $sections = array() ) {
		if ( count( psforum()->theme_compat->packages ) <= 1 )
			unset( $sections['psf_settings_theme_compat'] );

		return $sections;
	}

	/**
	 * Allow keymaster role to save Forums settings
	 *
	 * @since PSForum (r4678)
	 *
	 * @param string $capability
	 * @return string Return 'keep_gate' capability
	 */
	public function option_page_capability_psforum( $capability = 'manage_options' ) {
		$capability = 'keep_gate';
		return $capability;
	}

	/** Ajax ******************************************************************/

	/**
	 * Ajax action for facilitating the forum auto-suggest
	 *
	 * @since PSForum (r4261)
	 *
	 * @uses get_posts()
	 * @uses psf_get_topic_post_type()
	 * @uses psf_get_topic_id()
	 * @uses psf_get_topic_title()
	 */
	public function suggest_topic() {
		global $wpdb;

		// Bail early if no request
		if ( empty( $_REQUEST['q'] ) ) {
			wp_die( '0' );
		}

		// Bail if user cannot moderate - only moderators can change hierarchy
		if ( ! current_user_can( 'moderate' ) ) {
			wp_die( '0' );
		}

		// Check the ajax nonce
		check_ajax_referer( 'psf_suggest_topic_nonce' );

		// Try to get some topics
		$topics = get_posts( array(
			's'         => $wpdb->esc_like( $_REQUEST['q'] ),
			'post_type' => psf_get_topic_post_type()
		) );

		// If we found some topics, loop through and display them
		if ( ! empty( $topics ) ) {
			foreach ( (array) $topics as $post ) {
				printf( esc_html__( '%s - %s', 'psforum' ), psf_get_topic_id( $post->ID ), psf_get_topic_title( $post->ID ) . "\n" );
			}
		}
		die();
	}

	/**
	 * Ajax action for facilitating the topic and reply author auto-suggest
	 *
	 * @since PSForum (r5014)
	 */
	public function suggest_user() {
		global $wpdb;

		// Bail early if no request
		if ( empty( $_REQUEST['q'] ) ) {
			wp_die( '0' );
		}

		// Bail if user cannot moderate - only moderators can change authorship
		if ( ! current_user_can( 'moderate' ) ) {
			wp_die( '0' );
		}

		// Check the ajax nonce
		check_ajax_referer( 'psf_suggest_user_nonce' );

		// Try to get some users
		$users_query = new WP_User_Query( array(
			'search'         => '*' . $wpdb->esc_like( $_REQUEST['q'] ) . '*',
			'fields'         => array( 'ID', 'user_nicename' ),
			'search_columns' => array( 'ID', 'user_nicename', 'user_email' ),
			'orderby'        => 'ID'
		) );

		// If we found some users, loop through and display them
		if ( ! empty( $users_query->results ) ) {
			foreach ( (array) $users_query->results as $user ) {
				printf( esc_html__( '%s - %s', 'psforum' ), psf_get_user_id( $user->ID ), psf_get_user_nicename( $user->ID, array( 'force' => $user->user_nicename ) ) . "\n" );
			}
		}
		die();
	}

	/** About *****************************************************************/

	/**
	 * Output the about screen
	 *
	 * @since PSForum (r4159)
	 */
	public function about_screen() {

		list( $display_version ) = explode( '-', psf_get_version() ); ?>

		<div class="wrap about-wrap">
			<h1><?php printf( esc_html__( 'Willkommen bei PS Forum %s', 'psforum' ), $display_version ); ?></h1>
			<div class="about-text"><?php printf( esc_html__( 'Vielen Dank für die Aktualisierung! PSForum %s ist bereit, den Sturm der Benutzer in Deiner Community zu überstehen!', 'psforum' ), $display_version ); ?></div>
			<div class="psf-badge"><?php printf( esc_html__( 'Version %s', 'psforum' ), $display_version ); ?></div>

			<h2 class="nav-tab-wrapper">
				<a class="nav-tab nav-tab-active" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'psf-about' ), 'index.php' ) ) ); ?>">
					<?php esc_html_e( 'What&#8217;s Neu', 'psforum' ); ?>
				</a><a class="nav-tab" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'psf-credits' ), 'index.php' ) ) ); ?>">
					<?php esc_html_e( 'Credits', 'psforum' ); ?>
				</a>
			</h2>

			<div class="changelog">
				<h3><?php esc_html_e( 'Foren-Abonnements', 'psforum' ); ?></h3>

				<div class="feature-section col two-col">
					<div class="last-feature">
						<h4><?php esc_html_e( 'Foren abonnieren', 'psforum' ); ?></h4>
						<p><?php esc_html_e( 'Jetzt können Deine Benutzer neue Themen in bestimmten Foren abonnieren.', 'psforum' ); ?></p>
					</div>

					<div>
						<h4><?php esc_html_e( 'Abonnements verwalten', 'psforum' ); ?></h4>
						<p><?php esc_html_e( 'Deine Benutzer können alle ihre Abonnements an einem bequemen Ort verwalten.', 'psforum' ); ?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php esc_html_e( 'Konverter', 'psforum' ); ?></h3>

				<div class="feature-section col one-col">
					<div class="last-feature">
						<p><?php esc_html_e( 'Wir sind alle in Aufregung über den Schwarm neuer Importeure, AEF, Drupal, FluxBB, Kunena Foren für Joomla, MyBB, Phorum, PHPFox, PHPWind, PunBB, SMF, Xenforo und XMB. Bestehende Importeure sind jetzt süßer als Honig mit verbessertem Importieren von Stickies, Themen-Tags, Forenkategorien und der Stachel ist jetzt weg, wenn Du importierte Benutzer entfernen musst.', 'psforum' ); ?></p>
					</div>
				</div>

				<div class="feature-section col three-col">
					<div>
						<h4><?php esc_html_e( 'Theme Kompatibilität', 'psforum' ); ?></h4>
						<p><?php esc_html_e( 'Bessere Handhabung von Stilen und Skripten im Vorlagenstapel.', 'psforum' ); ?></p>
					</div>

					<div>
						<h4><?php esc_html_e( 'Mehrsprachige Unterstützung', 'psforum' ); ?></h4>
						<p><?php esc_html_e( 'PS Forum unterstützt vollständig automatische Übersetzungsupdates.', 'psforum' ); ?></p>
					</div>

					<div class="last-feature">
						<h4><?php esc_html_e( 'Benutzerfunktionen', 'psforum' ); ?></h4>
						<p><?php esc_html_e( 'Rollen und Fähigkeiten wurden durchsucht, bereinigt und vereinfacht.', 'psforum' ); ?></p>
					</div>
				</div>
			</div>

			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'psforum' ), 'options-general.php' ) ) ); ?>"><?php esc_html_e( 'Gehe zu den Forumseinstellungen', 'psforum' ); ?></a>
			</div>

		</div>

		<?php
	}

	/**
	 * Output the credits screen
	 *
	 * Hardcoding this in here is pretty janky. It's fine for 2.2, but we'll
	 * want to leverage api.wordpress.org eventually.
	 *
	 * @since PSForum (r4159)
	 */
	public function credits_screen() {

		list( $display_version ) = explode( '-', psf_get_version() ); ?>

		<div class="wrap about-wrap">
			<h1><?php printf( esc_html__( 'Willkommen bei PS Forum %s', 'psforum' ), $display_version ); ?></h1>
			<div class="about-text"><?php printf( esc_html__( 'Vielen Dank für die Aktualisierung! PSForum %s ist gewachst, poliert und bereit für ein oder zwei Runden um den Block!', 'psforum' ), $display_version ); ?></div>
			<div class="psf-badge"><?php printf( esc_html__( 'Version %s', 'psforum' ), $display_version ); ?></div>

			<h2 class="nav-tab-wrapper">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'psf-about' ), 'index.php' ) ) ); ?>" class="nav-tab">
					<?php esc_html_e( 'Was ist Neu', 'psforum' ); ?>
				</a><a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'psf-credits' ), 'index.php' ) ) ); ?>" class="nav-tab nav-tab-active">
					<?php esc_html_e( 'Credits', 'psforum' ); ?>
				</a>
			</h2>

			<p class="about-description"><?php esc_html_e( 'PS Forum wird von einem weltweiten Schwarm fleißiger, fleißiger Bienen geschaffen.', 'psforum' ); ?></p>

			<h4 class="wp-people-group"><?php esc_html_e( 'Project Leaders', 'psforum' ); ?></h4>
			<ul class="wp-people-group " id="wp-people-group-project-leaders">
				<li class="wp-person" id="wp-person-matt">
					<a href="http://profiles.wordpress.org/matt"><img src="http://0.gravatar.com/avatar/767fc9c115a1b989744c755db47feb60?s=60" class="gravatar" alt="Matt Mullenweg" /></a>
					<a class="web" href="http://profiles.wordpress.org/matt">Matt Mullenweg</a>
					<span class="title"><?php esc_html_e( 'Founding Developer', 'psforum' ); ?></span>
				</li>
				<li class="wp-person" id="wp-person-johnjamesjacoby">
					<a href="http://profiles.wordpress.org/johnjamesjacoby"><img src="http://0.gravatar.com/avatar/81ec16063d89b162d55efe72165c105f?s=60" class="gravatar" alt="John James Jacoby" /></a>
					<a class="web" href="http://profiles.wordpress.org/johnjamesjacoby">John James Jacoby</a>
					<span class="title"><?php esc_html_e( 'Lead Developer', 'psforum' ); ?></span>
				</li>
				<li class="wp-person" id="wp-person-jmdodd">
					<a href="http://profiles.wordpress.org/jmdodd"><img src="http://0.gravatar.com/avatar/6a7c997edea340616bcc6d0fe03f65dd?s=60" class="gravatar" alt="Jennifer M. Dodd" /></a>
					<a class="web" href="http://profiles.wordpress.org/jmdodd">Jennifer M. Dodd</a>
					<span class="title"><?php esc_html_e( 'Feature Developer', 'psforum' ); ?></span>
				</li>
				<li class="wp-person" id="wp-person-netweb">
					<a href="http://profiles.wordpress.org/netweb"><img src="http://0.gravatar.com/avatar/97e1620b501da675315ba7cfb740e80f?s=60" class="gravatar" alt="Stephen Edgar" /></a>
					<a class="web" href="http://profiles.wordpress.org/netweb">Stephen Edgar</a>
					<span class="title"><?php esc_html_e( 'Converter Specialist', 'psforum' ); ?></span>
				</li>
			</ul>

			<h4 class="wp-people-group"><?php esc_html_e( 'Contributing Developers', 'psforum' ); ?></h4>
			<ul class="wp-people-group " id="wp-people-group-contributing-developers">
				<li class="wp-person" id="wp-person-jaredatch">
					<a href="http://profiles.wordpress.org/jaredatch"><img src="http://0.gravatar.com/avatar/e341eca9e1a85dcae7127044301b4363?s=60" class="gravatar" alt="Jared Atchison" /></a>
					<a class="web" href="http://profiles.wordpress.org/jaredatch">Jared Atchison</a>
					<span class="title"><?php esc_html_e( 'Bug Testing', 'psforum' ); ?></span>
				</li>
				<li class="wp-person" id="wp-person-gautamgupta">
					<a href="http://profiles.wordpress.org/gautamgupta"><img src="http://0.gravatar.com/avatar/b0810422cbe6e4eead4def5ae7a90b34?s=60" class="gravatar" alt="Gautam Gupta" /></a>
					<a class="web" href="http://profiles.wordpress.org/gautamgupta">Gautam Gupta</a>
					<span class="title"><?php esc_html_e( 'Feature Developer', 'psforum' ); ?></span>
				</li>
			</ul>

			<h4 class="wp-people-group"><?php esc_html_e( 'Core Contributors to PSForum 2.5', 'psforum' ); ?></h4>
			<p class="wp-credits-list">
				<a href="http://profiles.wordpress.org/alex-ye">alex-ye</a>,
				<a href="http://profiles.wordpress.org/alexvorn2">alexvorn2</a>,
				<a href="http://profiles.wordpress.org/aliso">aliso</a>,
				<a href="http://profiles.wordpress.org/boonebgorges">boonebgorges</a>,
				<a href="http://profiles.wordpress.org/daveshine">daveshine</a>,
				<a href="http://profiles.wordpress.org/DJPaul">DJPaul</a>,
				<a href="http://profiles.wordpress.org/ethitter">ethitter</a>,
				<a href="http://profiles.wordpress.org/fanquake">fanquake</a>,
				<a href="http://profiles.wordpress.org/GargajCNS">GargajCNS</a>,
				<a href="http://profiles.wordpress.org/GautamGupta">GautamGupta</a>,
				<a href="http://profiles.wordpress.org/imath">imath</a>,
				<a href="http://profiles.wordpress.org/jkudish">jkudish</a>,
				<a href="http://profiles.wordpress.org/kobenland">kobenland</a>,
				<a href="http://profiles.wordpress.org/lakrisgubben">lakrisgubben</a>,
				<a href="http://profiles.wordpress.org/loki_racer">loki_racer</a>,
				<a href="http://profiles.wordpress.org/mamaduka">mamaduka</a>,
				<a href="http://profiles.wordpress.org/Maty">Maty</a>,
				<a href="http://profiles.wordpress.org/mercime">mercime</a>,
				<a href="http://profiles.wordpress.org/mordauk">mordauk</a>,
				<a href="http://profiles.wordpress.org/mrcl">mrcl</a>,
				<a href="http://profiles.wordpress.org/MZAWeb">MZAWeb</a>,
				<a href="http://profiles.wordpress.org/r-a-y">r-a-y</a>,
				<a href="http://profiles.wordpress.org/strangerstudios">strangerstudios</a>,
				<a href="http://profiles.wordpress.org/thebrandonallen">thebrandonallen</a>,
				<a href="http://profiles.wordpress.org/tlovett1">tlovett1</a>,
				<a href="http://profiles.wordpress.org/wpdennis">wpdennis</a>,
			</p>

			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'psforum' ), 'options-general.php' ) ) ); ?>"><?php esc_html_e( 'Gehe zu den Forumseinstellungen', 'psforum' ); ?></a>
			</div>

		</div>

		<?php
	}

	/** Updaters **************************************************************/

	/**
	 * Update all PSForum forums across all sites
	 *
	 * @since PSForum (r3689)
	 *
	 * @global WPDB $wpdb
	 * @uses get_blog_option()
	 * @uses wp_remote_get()
	 */
	public static function update_screen() {

		// Get action
		$action = isset( $_GET['action'] ) ? $_GET['action'] : ''; ?>

		<div class="wrap">
			<div id="icon-edit" class="icon32 icon32-posts-topic"><br /></div>
			<h2><?php esc_html_e( 'Forum aktualisieren', 'psforum' ); ?></h2>

		<?php

		// Taking action
		switch ( $action ) {
			case 'psf-update' :

				// Run the full updater
				psf_version_updater(); ?>

				<p><?php esc_html_e( 'Alles erledigt!', 'psforum' ); ?></p>
				<a class="button" href="index.php?page=psf-update"><?php esc_html_e( 'Geh zurück', 'psforum' ); ?></a>

				<?php

				break;

			case 'show' :
			default : ?>

				<p><?php esc_html_e( 'Du kannst Dein Forum über diese Seite aktualisieren. Klicke auf den Link unten, um zu aktualisieren.', 'psforum' ); ?></p>
				<p><a class="button" href="index.php?page=psf-update&amp;action=psf-update"><?php esc_html_e( 'Forum aktualisieren', 'psforum' ); ?></a></p>

			<?php break;

		} ?>

		</div><?php
	}

	/**
	 * Update all PSForum forums across all sites
	 *
	 * @since PSForum (r3689)
	 *
	 * @global WPDB $wpdb
	 * @uses get_blog_option()
	 * @uses wp_remote_get()
	 */
	public static function network_update_screen() {
		global $wpdb;

		// Get action
		$action = isset( $_GET['action'] ) ? $_GET['action'] : ''; ?>

		<div class="wrap">
			<div id="icon-edit" class="icon32 icon32-posts-topic"><br /></div>
			<h2><?php esc_html_e( 'Foren aktualisieren', 'psforum' ); ?></h2>

		<?php

		// Taking action
		switch ( $action ) {
			case 'psforum-update' :

				// Site counter
				$n = isset( $_GET['n'] ) ? intval( $_GET['n'] ) : 0;

				// Get blogs 5 at a time
				$blogs = $wpdb->get_results( "SELECT * FROM {$wpdb->blogs} WHERE site_id = '{$wpdb->siteid}' AND spam = '0' AND deleted = '0' AND archived = '0' ORDER BY registered DESC LIMIT {$n}, 5", ARRAY_A );

				// No blogs so all done!
				if ( empty( $blogs ) ) : ?>

					<p><?php esc_html_e( 'Alles erledigt!', 'psforum' ); ?></p>
					<a class="button" href="update-core.php?page=psforum-update"><?php esc_html_e( 'Geh zurück', 'psforum' ); ?></a>

				<?php

				// Still have sites to loop through
				else : ?>

					<ul>

						<?php foreach ( (array) $blogs as $details ) :

							$siteurl = get_blog_option( $details['blog_id'], 'siteurl' ); ?>

							<li><?php echo $siteurl; ?></li>

							<?php

							// Get the response of the PSForum update on this site
							$response = wp_remote_get(
								trailingslashit( $siteurl ) . 'wp-admin/index.php?page=psf-update&action=psf-update',
								array( 'timeout' => 30, 'httpversion' => '1.1' )
							);

							// Site errored out, no response?
							if ( is_wp_error( $response ) )
								wp_die( sprintf( __( 'Warnung! Fehler beim Aktualisieren von %1$s. Dein Server kann möglicherweise keine Verbindung zu Webseiten herstellen, die darauf ausgeführt werden. Fehlermeldung: <em>%2$s</em>', 'psforum' ), $siteurl, $response->get_error_message() ) );

							// Switch to the new blog
							switch_to_blog( $details[ 'blog_id' ] );

							$basename = psforum()->basename;

							// Run the updater on this site
							if ( is_plugin_active_for_network( $basename ) || is_plugin_active( $basename ) ) {
								psf_version_updater();
							}

							// restore original blog
							restore_current_blog();

							// Do some actions to allow plugins to do things too
							do_action( 'after_psforum_upgrade', $response             );
							do_action( 'psf_upgrade_site',      $details[ 'blog_id' ] );

						endforeach; ?>

					</ul>

					<p>
						<?php esc_html_e( 'Wenn Dein Browser nicht automatisch mit dem Laden der nächsten Seite beginnt, klicke auf diesen Link:', 'psforum' ); ?>
						<a class="button" href="update-core.php?page=psforum-update&amp;action=psforum-update&amp;n=<?php echo ( $n + 5 ); ?>"><?php esc_html_e( 'Next Forums', 'psforum' ); ?></a>
					</p>
					<script type='text/javascript'>
						<!--
						function nextpage() {
							location.href = 'update-core.php?page=psforum-update&action=psforum-update&n=<?php echo ( $n + 5 ) ?>';
						}
						setTimeout( 'nextpage()', 250 );
						//-->
					</script><?php

				endif;

				break;

			case 'show' :
			default : ?>

				<p><?php esc_html_e( 'Du kannst alle Foren in Deinem Netzwerk über diese Seite aktualisieren. Es funktioniert, indem es das Update-Skript jeder Webseite automatisch aufruft. Klicke auf den Link unten, um zu aktualisieren.', 'psforum' ); ?></p>
				<p><a class="button" href="update-core.php?page=psforum-update&amp;action=psforum-update"><?php esc_html_e( 'Foren aktualisieren', 'psforum' ); ?></a></p>

			<?php break;

		} ?>

		</div><?php
	}
}
endif; // class_exists check

/**
 * Setup PSForum Admin
 *
 * @since PSForum (r2596)
 *
 * @uses PSF_Admin
 */
function psf_admin() {
	psforum()->admin = new PSF_Admin();

	psforum()->admin->converter = new PSF_Converter();
}
