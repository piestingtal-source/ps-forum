<?php

/**
 * PSForum BuddyPress Members Class
 *
 * @package PSForum
 * @subpackage BuddyPress
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'PSF_Forums_Members' ) ) :
/**
 * Member profile modifications
 *
 * @since PSForum (r4395)
 *
 * @package PSForum
 * @subpackage BuddyPress
 */
class PSF_BuddyPress_Members {

	/**
	 * Main constructor for modifying PSForum profile links
	 *
	 * @since PSForum (r4395)
	 */
	public function __construct() {
		$this->setup_actions();
		$this->setup_filters();
	}

	/**
	 * Setup the actions
	 *
	 * @since PSForum (r4395)
	 *
	 * @access private
	 * @uses add_filter() To add various filters
	 * @uses add_action() To add various actions
	 */
	private function setup_actions() {

		// Allow unsubscribe/unfavorite links to work
		add_action( 'bp_template_redirect', array( $this, 'set_member_forum_query_vars' ) );

		/** Favorites *********************************************************/

		// Move handler to 'bp_actions' - BuddyPress bypasses template_loader
		remove_action( 'template_redirect', 'psf_favorites_handler', 1 );
		add_action(    'bp_actions',        'psf_favorites_handler', 1 );

		/** Subscriptions *****************************************************/

		// Move handler to 'bp_actions' - BuddyPress bypasses template_loader
		remove_action( 'template_redirect', 'psf_subscriptions_handler', 1 );
		add_action(    'bp_actions',        'psf_subscriptions_handler', 1 );
	}

	/**
	 * Setup the filters
	 *
	 * @since PSForum (r4395)
	 *
	 * @access private
	 * @uses add_filter() To add various filters
	 * @uses add_action() To add various actions
	 */
	private function setup_filters() {
		add_filter( 'psf_pre_get_user_profile_url',    array( $this, 'user_profile_url'            )        );
		add_filter( 'psf_get_favorites_permalink',     array( $this, 'get_favorites_permalink'     ), 10, 2 );
		add_filter( 'psf_get_subscriptions_permalink', array( $this, 'get_subscriptions_permalink' ), 10, 2 );
	}

	/** Filters ***************************************************************/

	/**
	 * Override PSForum profile URL with BuddyPress profile URL
	 *
	 * @since PSForum (r3401)
	 * @param string $url
	 * @param int $user_id
	 * @param string $user_nicename
	 * @return string
	 */
	public function user_profile_url( $user_id ) {

		// Define local variable(s)
		$profile_url    = '';
		$component_slug = psforum()->extend->buddypress->slug;

		// Special handling for forum component
		if ( bp_is_current_component( $component_slug ) ) {

			// Empty action or 'topics' action
			if ( !bp_current_action() || bp_is_current_action( psf_get_topic_archive_slug() ) ) {
				$profile_url = bp_core_get_user_domain( $user_id ) . $component_slug . '/' . psf_get_topic_archive_slug();

			// Empty action or 'topics' action
			} elseif ( bp_is_current_action( psf_get_reply_archive_slug() ) ) {
				$profile_url = bp_core_get_user_domain( $user_id ) . $component_slug . '/' . psf_get_reply_archive_slug();

			// 'favorites' action
			} elseif ( psf_is_favorites_active() && bp_is_current_action( psf_get_user_favorites_slug() ) ) {
				$profile_url = $this->get_favorites_permalink( '', $user_id );

			// 'subscriptions' action
			} elseif ( psf_is_subscriptions_active() && bp_is_current_action( psf_get_user_subscriptions_slug() ) ) {
				$profile_url = $this->get_subscriptions_permalink( '', $user_id );
			}

		// Not in users' forums area
		} else {
			$profile_url = bp_core_get_user_domain( $user_id );
		}

		return trailingslashit( $profile_url );
	}

	/**
	 * Override PSForum favorites URL with BuddyPress profile URL
	 *
	 * @since PSForum (r3721)
	 * @param string $url
	 * @param int $user_id
	 * @return string
	 */
	public function get_favorites_permalink( $url, $user_id ) {
		$component_slug = psforum()->extend->buddypress->slug;
		$url            = trailingslashit( bp_core_get_user_domain( $user_id ) . $component_slug . '/' . psf_get_user_favorites_slug() );
		return $url;
	}

	/**
	 * Override PSForum subscriptions URL with BuddyPress profile URL
	 *
	 * @since PSForum (r3721)
	 * @param string $url
	 * @param int $user_id
	 * @return string
	 */
	public function get_subscriptions_permalink( $url, $user_id ) {
		$component_slug = psforum()->extend->buddypress->slug;
		$url            = trailingslashit( bp_core_get_user_domain( $user_id ) . $component_slug . '/' . psf_get_user_subscriptions_slug() );
		return $url;
	}

	/**
	 * Set favorites and subscriptions query variables if viewing member profile
	 * pages.
	 *
	 * @since PSForum (r4615)
	 *
	 * @global WP_Query $wp_query
	 * @return If not viewing your own profile
	 */
	public function set_member_forum_query_vars() {

		// Special handling for forum component
		if ( ! bp_is_my_profile() )
			return;

		global $wp_query;

		// 'favorites' action
		if ( psf_is_favorites_active() && bp_is_current_action( psf_get_user_favorites_slug() ) ) {
			$wp_query->psf_is_single_user_favs = true;

		// 'subscriptions' action
		} elseif ( psf_is_subscriptions_active() && bp_is_current_action( psf_get_user_subscriptions_slug() ) ) {
			$wp_query->psf_is_single_user_subs = true;
		}
	}
}
endif;
