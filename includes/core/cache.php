<?php

/**
 * PSForum Cache Helpers
 *
 * Helper functions used to communicate with WordPress's various caches. Many
 * of these functions are used to work around specific WordPress nuances. They
 * are subject to changes, tweaking, and will need iteration as performance
 * improvements are made to WordPress core.
 *
 * @package PSForum
 * @subpackage Cache
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/** Helpers *******************************************************************/

/**
 * Skip invalidation of child post content when editing a parent.
 *
 * This prevents invalidating caches for topics and replies when editing a forum
 * or a topic. Without this in place, WordPress will attempt to invalidate all
 * child posts whenever a parent post is modified. This can cause thousands of
 * cache invalidations to occur on a single edit, which is no good for anyone.
 *
 * @since PSForum (r4011)
 *
 * @package PSForum
 * @subpackage Cache
 */
class PSF_Skip_Children {

	/**
	 * @var int Post ID being updated
	 */
	private $updating_post = 0;

	/**
	 * @var bool The original value of $_wp_suspend_cache_invalidation global
	 */
	private $original_cache_invalidation = false;

	/** Methods ***************************************************************/

	/**
	 * Hook into the 'pre_post_update' action.
	 *
	 * @since PSForum (r4011)
	 */
	public function __construct() {
		add_action( 'pre_post_update', array( $this, 'pre_post_update' ) );
	}

	/**
	 * Only clean post caches for main PSForum posts.
	 *
	 * Check that the post being updated is a PSForum post type, saves the
	 * post ID to be used later, and adds an action to 'clean_post_cache' that
	 * prevents child post caches from being cleared.
	 *
	 * @since PSForum (r4011)
	 *
	 * @param int $post_id The post ID being updated
	 * @return If invalid post data
	 */
	public function pre_post_update( $post_id = 0 ) {

		// Bail if post ID is not a PSForum post type
		if ( empty( $post_id ) || ! psf_is_custom_post_type( $post_id ) )
			return;

		// Store the $post_id
		$this->updating_post = $post_id;

		// Skip related post cache invalidation. This prevents invalidating the
		// caches of the child posts when there is no reason to do so.
		add_action( 'clean_post_cache', array( $this, 'skip_related_posts' ) );
	}

	/**
	 * Skip cache invalidation of related posts if the post ID being invalidated
	 * is not the one that was just updated.
	 *
	 * @since PSForum (r4011)
	 *
	 * @param int $post_id The post ID of the cache being invalidated
	 * @return If invalid post data
	 */
	public function skip_related_posts( $post_id = 0 ) {

		// Bail if this post is not the current PSForum post
		if ( empty( $post_id ) || ( $this->updating_post !== $post_id ) )
			return;

		// Stash the current cache invalidation value in a variable, so we can
		// restore back to it nicely in the future.
		global $_wp_suspend_cache_invalidation;

		$this->original_cache_invalidation = $_wp_suspend_cache_invalidation;

		// Turn off cache invalidation
		wp_suspend_cache_invalidation( true );

		// Restore cache invalidation
		add_action( 'wp_insert_post', array( $this, 'restore_cache_invalidation' ) );
	}

	/**
	 * Restore the cache invalidation to its previous value.
	 *
	 * @since PSForum (r4011)
	 * @uses wp_suspend_cache_invalidation()
	 */
	public function restore_cache_invalidation() {
		wp_suspend_cache_invalidation( $this->original_cache_invalidation );
	}
}
new PSF_Skip_Children();

/** General *******************************************************************/

/**
 * Will clean a post in the cache.
 *
 * Will call to clean the term object cache associated with the post ID.
 *
 * @since PSForum (r4040)
 *
 * @uses do_action() Calls 'psf_clean_post_cache' on $id
 * @param object|int $_post The post object or ID to remove from the cache
 */
function psf_clean_post_cache( $_post = '' ) {

	// Bail if no post
	$_post = get_post( $_post );
	if ( empty( $_post ) )
		return;

	wp_cache_delete( $_post->ID, 'posts'     );
	wp_cache_delete( $_post->ID, 'post_meta' );

	clean_object_term_cache( $_post->ID, $_post->post_type );

	do_action( 'psf_clean_post_cache', $_post->ID, $_post );

	// Child query types to clean
	$post_types = array(
		psf_get_topic_post_type(),
		psf_get_forum_post_type(),
		psf_get_reply_post_type()
	);

	// Loop through query types and clean caches
	foreach ( $post_types as $post_type ) {
		wp_cache_delete( 'psf_get_forum_'     . $_post->ID . '_reply_id',                              'psforum_posts' );
		wp_cache_delete( 'psf_parent_'        . $_post->ID . '_type_' . $post_type . '_child_last_id', 'psforum_posts' );
		wp_cache_delete( 'psf_parent_'        . $_post->ID . '_type_' . $post_type . '_child_count',   'psforum_posts' );
		wp_cache_delete( 'psf_parent_public_' . $_post->ID . '_type_' . $post_type . '_child_ids',     'psforum_posts' );
		wp_cache_delete( 'psf_parent_all_'    . $_post->ID . '_type_' . $post_type . '_child_ids',     'psforum_posts' );
	}

	// Invalidate parent caches
	if ( ! empty( $_post->post_parent ) ) {
		psf_clean_post_cache( $_post->post_parent );
	}
}
