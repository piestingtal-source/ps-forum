<?php

/**
 * PSForum Search Functions
 *
 * @package PSForum
 * @subpackage Functions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/** Query *********************************************************************/

/**
 * Run the search query
 *
 * @since PSForum (r4579) 
 *
 * @param mixed $new_args New arguments
 * @uses psf_get_search_query_args() To get the search query args
 * @uses psf_parse_args() To parse the args
 * @uses psf_has_search_results() To make the search query
 * @return bool False if no results, otherwise if search results are there
 */
function psf_search_query( $new_args = array() ) {

	// Existing arguments 
	$query_args = psf_get_search_query_args();

	// Merge arguments
	if ( !empty( $new_args ) ) {
		$new_args   = psf_parse_args( $new_args, array(), 'search_query' );
		$query_args = array_merge( $query_args, $new_args );
	}

	return psf_has_search_results( $query_args );
}

/**
 * Return the search's query args
 *
 * @since PSForum (r4579)
 *
 * @uses psf_get_search_terms() To get the search terms
 * @return array Query arguments
 */
function psf_get_search_query_args() {

	// Get search terms
	$search_terms = psf_get_search_terms();
	$retval       = !empty( $search_terms ) ? array( 's' => $search_terms ) : array();

	return apply_filters( 'psf_get_search_query_args', $retval );
}

/**
 * Redirect to search results page if needed
 *
 * @since PSForum (r4928)
 * @return If a redirect is not needed
 */
function psf_search_results_redirect() {
	global $wp_rewrite;
	
	// Bail if not a search request action
	if ( empty( $_GET['action'] ) || ( 'psf-search-request' !== $_GET['action'] ) ) {
		return;
	}

	// Bail if not using pretty permalinks
	if ( ! $wp_rewrite->using_permalinks() ) {
		return;
	}

	// Get the redirect URL
	$redirect_to = psf_get_search_results_url();
	if ( empty( $redirect_to ) ) {
		return;
	}

	// Redirect and bail
	wp_safe_redirect( $redirect_to );
	exit();
}
