<?php

/**
 * PSForum Reply Template Tags
 *
 * @package PSForum
 * @subpackage TemplateTags
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/** Post Type *****************************************************************/

/**
 * Return the unique id of the custom post type for replies
 *
 * @since PSForum (r2857)
 *
 * @uses psf_get_reply_post_type() To get the reply post type
 */
function psf_reply_post_type() {
	echo psf_get_reply_post_type();
}
	/**
	 * Return the unique id of the custom post type for replies
	 *
	 * @since PSForum (r2857)
	 *
	 * @uses apply_filters() Calls 'psf_get_forum_post_type' with the forum
	 *                        post type id
	 * @return string The unique reply post type id
	 */
	function psf_get_reply_post_type() {
		return apply_filters( 'psf_get_reply_post_type', psforum()->reply_post_type );
	}

/**
 * Return array of labels used by the reply post type
 *
 * @since PSForum (r5129)
 *
 * @return array
 */
function psf_get_reply_post_type_labels() {
	return apply_filters( 'psf_get_reply_post_type_labels', array(
		'name'               => __( 'Antworten',                   'psforum' ),
		'menu_name'          => __( 'Antworten',                   'psforum' ),
		'singular_name'      => __( 'Antwort',                     'psforum' ),
		'all_items'          => __( 'Alle Antworten',               'psforum' ),
		'add_new'            => __( 'Neue Antwort',                 'psforum' ),
		'add_new_item'       => __( 'Neue Antwort erstellen',          'psforum' ),
		'edit'               => __( 'Bearbeiten',                      'psforum' ),
		'edit_item'          => __( 'Antwort bearbeiten',                'psforum' ),
		'new_item'           => __( 'Neue Antwort',                 'psforum' ),
		'view'               => __( 'Antwort anzeigen',                'psforum' ),
		'view_item'          => __( 'Antwort anzeigen',                'psforum' ),
		'search_items'       => __( 'Antworten suchen',            'psforum' ),
		'not_found'          => __( 'Keine Antworten gefunden',          'psforum' ),
		'not_found_in_trash' => __( 'Keine Antworten in Papierkorb gefunden', 'psforum' ),
		'parent_item_colon'  => __( 'Thema:',                    'psforum' )
	) );
}

/**
 * Return array of reply post type rewrite settings
 *
 * @since PSForum (r5129)
 *
 * @return array
 */
function psf_get_reply_post_type_rewrite() {
	return apply_filters( 'psf_get_reply_post_type_rewrite', array(
		'slug'       => psf_get_reply_slug(),
		'with_front' => false
	) );
}

/**
 * Return array of features the reply post type supports
 *
 * @since PSForum (rx5129)
 *
 * @return array
 */
function psf_get_reply_post_type_supports() {
	return apply_filters( 'psf_get_reply_post_type_supports', array(
		'title',
		'editor',
		'revisions'
	) );
}

/** Reply Loop Functions ******************************************************/

/**
 * The main reply loop. WordPress makes this easy for us
 *
 * @since PSForum (r2553)
 *
 * @param mixed $args All the arguments supported by {@link WP_Query}
 * @uses psf_show_lead_topic() Are we showing the topic as a lead?
 * @uses psf_get_topic_id() To get the topic id
 * @uses psf_get_reply_post_type() To get the reply post type
 * @uses psf_get_topic_post_type() To get the topic post type
 * @uses get_option() To get the replies per page option
 * @uses psf_get_paged() To get the current page value
 * @uses current_user_can() To check if the current user is capable of editing
 *                           others' replies
 * @uses WP_Query To make query and get the replies
 * @uses WP_Rewrite::using_permalinks() To check if the blog is using permalinks
 * @uses get_permalink() To get the permalink
 * @uses add_query_arg() To add custom args to the url
 * @uses apply_filters() Calls 'psf_replies_pagination' with the pagination args
 * @uses paginate_links() To paginate the links
 * @uses apply_filters() Calls 'psf_has_replies' with
 *                        PSForum::reply_query::have_posts()
 *                        and PSForum::reply_query
 * @return object Multidimensional array of reply information
 */
function psf_has_replies( $args = '' ) {
	global $wp_rewrite;

	/** Defaults **************************************************************/

	// Other defaults
	$default_reply_search   = !empty( $_REQUEST['rs'] ) ? $_REQUEST['rs']    : false;
	$default_post_parent    = ( psf_is_single_topic() ) ? psf_get_topic_id() : 'any';
	$default_post_type      = ( psf_is_single_topic() && psf_show_lead_topic() ) ? psf_get_reply_post_type() : array( psf_get_topic_post_type(), psf_get_reply_post_type() );
	$default_thread_replies = (bool) ( psf_is_single_topic() && psf_thread_replies() );

	// Default query args
	$default = array(
		'post_type'           => $default_post_type,         // Only replies
		'post_parent'         => $default_post_parent,       // Of this topic
		'posts_per_page'      => psf_get_replies_per_page(), // This many
		'paged'               => psf_get_paged(),            // On this page
		'orderby'             => 'date',                     // Sorted by date
		'order'               => 'ASC',                      // Oldest to newest
		'hierarchical'        => $default_thread_replies,    // Hierarchical replies
		'ignore_sticky_posts' => true,                       // Stickies not supported
		's'                   => $default_reply_search,      // Maybe search
	);

	// What are the default allowed statuses (based on user caps)
	if ( psf_get_view_all() ) {

		// Default view=all statuses
		$post_statuses = array(
			psf_get_public_status_id(),
			psf_get_closed_status_id(),
			psf_get_spam_status_id(),
			psf_get_trash_status_id()
		);

		// Add support for private status
		if ( current_user_can( 'read_private_replies' ) ) {
			$post_statuses[] = psf_get_private_status_id();
		}

		// Join post statuses together
		$default['post_status'] = implode( ',', $post_statuses );

	// Lean on the 'perm' query var value of 'readable' to provide statuses
	} else {
		$default['perm'] = 'readable';
	}

	/** Setup *****************************************************************/

	// Parse arguments against default values
	$r = psf_parse_args( $args, $default, 'has_replies' );

	// Set posts_per_page value if replies are threaded
	$replies_per_page = $r['posts_per_page'];
	if ( true === $r['hierarchical'] ) {
		$r['posts_per_page'] = -1;
	}

	// Get PSForum
	$psf = psforum();

	// Call the query
	$psf->reply_query = new WP_Query( $r );

	// Add pagination values to query object
	$psf->reply_query->posts_per_page = $replies_per_page;
	$psf->reply_query->paged          = $r['paged'];

	// Never home, regardless of what parse_query says
	$psf->reply_query->is_home        = false;

	// Reset is_single if single topic
	if ( psf_is_single_topic() ) {
		$psf->reply_query->is_single = true;
	}

	// Only add reply to if query returned results
	if ( (int) $psf->reply_query->found_posts ) {

		// Get reply to for each reply
		foreach ( $psf->reply_query->posts as &$post ) {

			// Check for reply post type
			if ( psf_get_reply_post_type() === $post->post_type ) {
				$reply_to = psf_get_reply_to( $post->ID );

				// Make sure it's a reply to a reply
				if ( empty( $reply_to ) || ( psf_get_reply_topic_id( $post->ID ) === $reply_to ) ) {
					$reply_to = 0;
				}

				// Add reply_to to the post object so we can walk it later
				$post->reply_to = $reply_to;
			}
		}
	}

	// Only add pagination if query returned results
	if ( (int) $psf->reply_query->found_posts && (int) $psf->reply_query->posts_per_page ) {

		// If pretty permalinks are enabled, make our pagination pretty
		if ( $wp_rewrite->using_permalinks() ) {

			// User's replies
			if ( psf_is_single_user_replies() ) {
				$base = psf_get_user_replies_created_url( psf_get_displayed_user_id() );

			// Root profile page
			} elseif ( psf_is_single_user() ) {
				$base = psf_get_user_profile_url( psf_get_displayed_user_id() );

			// Page or single post
			} elseif ( is_page() || is_single() ) {
				$base = get_permalink();

			// Single topic
			} else {
				$base = get_permalink( psf_get_topic_id() );
			}

			$base = trailingslashit( $base ) . user_trailingslashit( $wp_rewrite->pagination_base . '/%#%/' );

		// Unpretty permalinks
		} else {
			$base = add_query_arg( 'paged', '%#%' );
		}

		// Figure out total pages
		if ( true === $r['hierarchical'] ) {
			$walker      = new PSF_Walker_Reply;
			$total_pages = ceil( (int) $walker->get_number_of_root_elements( $psf->reply_query->posts ) / (int) $replies_per_page );
		} else {
			$total_pages = ceil( (int) $psf->reply_query->found_posts / (int) $replies_per_page );

			// Add pagination to query object
			$psf->reply_query->pagination_links = paginate_links( apply_filters( 'psf_replies_pagination', array(
				'base'      => $base,
				'format'    => '',
				'total'     => $total_pages,
				'current'   => (int) $psf->reply_query->paged,
				'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
				'next_text' => is_rtl() ? '&larr;' : '&rarr;',
				'mid_size'  => 1,
				'add_args'  => ( psf_get_view_all() ) ? array( 'view' => 'all' ) : false
			) ) );

			// Remove first page from pagination
			if ( $wp_rewrite->using_permalinks() ) {
				$psf->reply_query->pagination_links = str_replace( $wp_rewrite->pagination_base . '/1/', '', $psf->reply_query->pagination_links );
			} else {
				$psf->reply_query->pagination_links = str_replace( '&#038;paged=1', '', $psf->reply_query->pagination_links );
			}
		}
	}

	// Return object
	return apply_filters( 'psf_has_replies', $psf->reply_query->have_posts(), $psf->reply_query );
}

/**
 * Whether there are more replies available in the loop
 *
 * @since PSForum (r2553)
 *
 * @uses WP_Query PSForum::reply_query::have_posts() To check if there are more
 *                                                    replies available
 * @return object Replies information
 */
function psf_replies() {

	// Put into variable to check against next
	$have_posts = psforum()->reply_query->have_posts();

	// Reset the post data when finished
	if ( empty( $have_posts ) )
		wp_reset_postdata();

	return $have_posts;
}

/**
 * Loads up the current reply in the loop
 *
 * @since PSForum (r2553)
 *
 * @uses WP_Query PSForum::reply_query::the_post() To get the current reply
 * @return object Reply information
 */
function psf_the_reply() {
	return psforum()->reply_query->the_post();
}

/**
 * Output reply id
 *
 * @since PSForum (r2553)
 *
 * @param $reply_id Optional. Used to check emptiness
 * @uses psf_get_reply_id() To get the reply id
 */
function psf_reply_id( $reply_id = 0 ) {
	echo psf_get_reply_id( $reply_id );
}
	/**
	 * Return the id of the reply in a replies loop
	 *
	 * @since PSForum (r2553)
	 *
	 * @param $reply_id Optional. Used to check emptiness
	 * @uses PSForum::reply_query::post::ID To get the reply id
	 * @uses psf_is_reply() To check if the search result is a reply
	 * @uses psf_is_single_reply() To check if it's a reply page
	 * @uses psf_is_reply_edit() To check if it's a reply edit page
	 * @uses get_post_field() To get the post's post type
	 * @uses WP_Query::post::ID To get the reply id
	 * @uses psf_get_reply_post_type() To get the reply post type
	 * @uses apply_filters() Calls 'psf_get_reply_id' with the reply id and
	 *                        supplied reply id
	 * @return int The reply id
	 */
	function psf_get_reply_id( $reply_id = 0 ) {
		global $wp_query;

		$psf = psforum();

		// Easy empty checking
		if ( !empty( $reply_id ) && is_numeric( $reply_id ) ) {
			$psf_reply_id = $reply_id;

		// Currently inside a replies loop
		} elseif ( !empty( $psf->reply_query->in_the_loop ) && isset( $psf->reply_query->post->ID ) ) {
			$psf_reply_id = $psf->reply_query->post->ID;

		// Currently inside a search loop
		} elseif ( !empty( $psf->search_query->in_the_loop ) && isset( $psf->search_query->post->ID ) && psf_is_reply( $psf->search_query->post->ID ) ) {
			$psf_reply_id = $psf->search_query->post->ID;

		// Currently viewing a forum
		} elseif ( ( psf_is_single_reply() || psf_is_reply_edit() ) && !empty( $psf->current_reply_id ) ) {
			$psf_reply_id = $psf->current_reply_id;

		// Currently viewing a reply
		} elseif ( ( psf_is_single_reply() || psf_is_reply_edit() ) && isset( $wp_query->post->ID ) ) {
			$psf_reply_id = $wp_query->post->ID;

		// Fallback
		} else {
			$psf_reply_id = 0;
		}

		return (int) apply_filters( 'psf_get_reply_id', $psf_reply_id, $reply_id );
	}

/**
 * Gets a reply
 *
 * @since PSForum (r2787)
 *
 * @param int|object $reply reply id or reply object
 * @param string $output Optional. OBJECT, ARRAY_A, or ARRAY_N. Default = OBJECT
 * @param string $filter Optional Sanitation filter. See {@link sanitize_post()}
 * @uses get_post() To get the reply
 * @uses psf_get_reply_post_type() To get the reply post type
 * @uses apply_filters() Calls 'psf_get_reply' with the reply, output type and
 *                        sanitation filter
 * @return mixed Null if error or reply (in specified form) if success
 */
function psf_get_reply( $reply, $output = OBJECT, $filter = 'raw' ) {
	if ( empty( $reply ) || is_numeric( $reply ) )
		$reply = psf_get_reply_id( $reply );

	$reply = get_post( $reply, OBJECT, $filter );
	if ( empty( $reply ) )
		return $reply;

	if ( $reply->post_type !== psf_get_reply_post_type() )
		return null;

	if ( $output === OBJECT ) {
		return $reply;

	} elseif ( $output === ARRAY_A ) {
		$_reply = get_object_vars( $reply );
		return $_reply;

	} elseif ( $output === ARRAY_N ) {
		$_reply = array_values( get_object_vars( $reply ) );
		return $_reply;

	}

	return apply_filters( 'psf_get_reply', $reply, $output, $filter );
}

/**
 * Output the link to the reply in the reply loop
 *
 * @since PSForum (r2553)
 *
 * @param int $reply_id Optional. Reply id
 * @uses psf_get_reply_permalink() To get the reply permalink
 */
function psf_reply_permalink( $reply_id = 0 ) {
	echo esc_url( psf_get_reply_permalink( $reply_id ) );
}
	/**
	 * Return the link to the reply
	 *
	 * @since PSForum (r2553)
	 *
	 * @param int $reply_id Optional. Reply id
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses get_permalink() To get the permalink of the reply
	 * @uses apply_filters() Calls 'psf_get_reply_permalink' with the link
	 *                        and reply id
	 * @return string Permanent link to reply
	 */
	function psf_get_reply_permalink( $reply_id = 0 ) {
		$reply_id = psf_get_reply_id( $reply_id );

		return apply_filters( 'psf_get_reply_permalink', get_permalink( $reply_id ), $reply_id );
	}
/**
 * Output the paginated url to the reply in the reply loop
 *
 * @since PSForum (r2679)
 *
 * @param int $reply_id Optional. Reply id
 * @uses psf_get_reply_url() To get the reply url
 */
function psf_reply_url( $reply_id = 0 ) {
	echo esc_url( psf_get_reply_url( $reply_id ) );
}
	/**
	 * Return the paginated url to the reply in the reply loop
	 *
	 * @since PSForum (r2679)
	 *
	 * @param int $reply_id Optional. Reply id
	 * @param string $redirect_to Optional. Pass a redirect value for use with
	 *                              shortcodes and other fun things.
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses psf_get_reply_topic_id() To get the reply topic id
	 * @uses psf_get_topic_permalink() To get the topic permalink
	 * @uses psf_get_reply_position() To get the reply position
	 * @uses get_option() To get the replies per page option
	 * @uses WP_Rewrite::using_permalinks() To check if the blog uses
	 *                                       permalinks
	 * @uses add_query_arg() To add custom args to the url
	 * @uses apply_filters() Calls 'psf_get_reply_url' with the reply url,
	 *                        reply id and bool count hidden
	 * @return string Link to reply relative to paginated topic
	 */
	function psf_get_reply_url( $reply_id = 0, $redirect_to = '' ) {

		// Set needed variables
		$reply_id   = psf_get_reply_id      ( $reply_id );
		$topic_id   = psf_get_reply_topic_id( $reply_id );

		// Hierarchical reply page
		if ( psf_thread_replies() ) {
			$reply_page = 1;

		// Standard reply page
		} else {
			$reply_page = ceil( (int) psf_get_reply_position( $reply_id, $topic_id ) / (int) psf_get_replies_per_page() );
		}

		$reply_hash = '#post-' . $reply_id;
		$topic_link = psf_get_topic_permalink( $topic_id, $redirect_to );
		$topic_url  = remove_query_arg( 'view', $topic_link );

		// Don't include pagination if on first page
		if ( 1 >= $reply_page ) {
			$url = trailingslashit( $topic_url ) . $reply_hash;

		// Include pagination
		} else {
			global $wp_rewrite;

			// Pretty permalinks
			if ( $wp_rewrite->using_permalinks() ) {
				$url = trailingslashit( $topic_url ) . trailingslashit( $wp_rewrite->pagination_base ) . trailingslashit( $reply_page ) . $reply_hash;

			// Yucky links
			} else {
				$url = add_query_arg( 'paged', $reply_page, $topic_url ) . $reply_hash;
			}
		}

		// Add topic view query arg back to end if it is set
		if ( psf_get_view_all() )
			$url = psf_add_view_all( $url );

		return apply_filters( 'psf_get_reply_url', $url, $reply_id, $redirect_to );
	}

/**
 * Output the title of the reply
 *
 * @since PSForum (r2553)
 *
 * @param int $reply_id Optional. Reply id
 * @uses psf_get_reply_title() To get the reply title
 */
function psf_reply_title( $reply_id = 0 ) {
	echo psf_get_reply_title( $reply_id );
}

	/**
	 * Return the title of the reply
	 *
	 * @since PSForum (r2553)
	 *
	 * @param int $reply_id Optional. Reply id
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses get_the_title() To get the reply title
	 * @uses apply_filters() Calls 'psf_get_reply_title' with the title and
	 *                        reply id
	 * @return string Title of reply
	 */
	function psf_get_reply_title( $reply_id = 0 ) {
		$reply_id = psf_get_reply_id( $reply_id );

		return apply_filters( 'psf_get_reply_title', get_the_title( $reply_id ), $reply_id );
	}

	/**
	 * Get empty reply title fallback.
	 *
	 * @since PSForum (r5177)
	 *
	 * @param string $reply_title Required. Reply Title
	 * @param int $reply_id Required. Reply ID
	 * @uses psf_get_reply_topic_title() To get the reply topic title
	 * @uses apply_filters() Calls 'psf_get_reply_title_fallback' with the title and reply ID
	 * @return string Title of reply
	 */
	function psf_get_reply_title_fallback( $post_title = '', $post_id = 0 ) {

		// Bail if title not empty, or post is not a reply
		if ( ! empty( $post_title ) || ! psf_is_reply( $post_id ) ) {
			return $post_title;
		}

		// Get reply topic title.
		$topic_title = psf_get_reply_topic_title( $post_id );

		// Get empty reply title fallback.
		$reply_title = sprintf( __( 'Antwort an: %s', 'psforum' ), $topic_title );

		return apply_filters( 'psf_get_reply_title_fallback', $reply_title, $post_id, $topic_title );
	}

/**
 * Output the content of the reply
 *
 * @since PSForum (r2553)
 *
 * @param int $reply_id Optional. reply id
 * @uses psf_get_reply_content() To get the reply content
 */
function psf_reply_content( $reply_id = 0 ) {
	echo psf_get_reply_content( $reply_id );
}
	/**
	 * Return the content of the reply
	 *
	 * @since PSForum (r2780)
	 *
	 * @param int $reply_id Optional. reply id
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses post_password_required() To check if the reply requires pass
	 * @uses get_the_password_form() To get the password form
	 * @uses get_post_field() To get the content post field
	 * @uses apply_filters() Calls 'psf_get_reply_content' with the content
	 *                        and reply id
	 * @return string Content of the reply
	 */
	function psf_get_reply_content( $reply_id = 0 ) {
		$reply_id = psf_get_reply_id( $reply_id );

		// Check if password is required
		if ( post_password_required( $reply_id ) )
			return get_the_password_form();

		$content = get_post_field( 'post_content', $reply_id );

		return apply_filters( 'psf_get_reply_content', $content, $reply_id );
	}

/**
 * Output the excerpt of the reply
 *
 * @since PSForum (r2751)
 *
 * @param int $reply_id Optional. Reply id
 * @param int $length Optional. Length of the excerpt. Defaults to 100 letters
 * @uses psf_get_reply_excerpt() To get the reply excerpt
 */
function psf_reply_excerpt( $reply_id = 0, $length = 100 ) {
	echo psf_get_reply_excerpt( $reply_id, $length );
}
	/**
	 * Return the excerpt of the reply
	 *
	 * @since PSForum (r2751)
	 *
	 * @param int $reply_id Optional. Reply id
	 * @param int $length Optional. Length of the excerpt. Defaults to 100
	 *                     letters
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses get_post_field() To get the excerpt
	 * @uses psf_get_reply_content() To get the reply content
	 * @uses apply_filters() Calls 'psf_get_reply_excerpt' with the excerpt,
	 *                        reply id and length
	 * @return string Reply Excerpt
	 */
	function psf_get_reply_excerpt( $reply_id = 0, $length = 100 ) {
		$reply_id = psf_get_reply_id( $reply_id );
		$length   = (int) $length;
		$excerpt  = get_post_field( 'post_excerpt', $reply_id );

		if ( empty( $excerpt ) ) {
			$excerpt = psf_get_reply_content( $reply_id );
		}

		$excerpt = trim ( strip_tags( $excerpt ) );

		// Multibyte support
		if ( function_exists( 'mb_strlen' ) ) {
			$excerpt_length = mb_strlen( $excerpt );
		} else {
			$excerpt_length = strlen( $excerpt );
		}

		if ( !empty( $length ) && ( $excerpt_length > $length ) ) {
			$excerpt  = substr( $excerpt, 0, $length - 1 );
			$excerpt .= '&hellip;';
		}

		return apply_filters( 'psf_get_reply_excerpt', $excerpt, $reply_id, $length );
	}

/**
 * Output the post date and time of a reply
 *
 * @since PSForum (r4155)
 *
 * @param int $reply_id Optional. Reply id.
 * @param bool $humanize Optional. Humanize output using time_since
 * @param bool $gmt Optional. Use GMT
 * @uses psf_get_reply_post_date() to get the output
 */
function psf_reply_post_date( $reply_id = 0, $humanize = false, $gmt = false ) {
	echo psf_get_reply_post_date( $reply_id, $humanize, $gmt );
}
	/**
	 * Return the post date and time of a reply
	 *
	 * @since PSForum (r4155)
	 *
	 * @param int $reply_id Optional. Reply id.
	 * @param bool $humanize Optional. Humanize output using time_since
	 * @param bool $gmt Optional. Use GMT
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses get_post_time() to get the reply post time
	 * @uses psf_get_time_since() to maybe humanize the reply post time
	 * @return string
	 */
	function psf_get_reply_post_date( $reply_id = 0, $humanize = false, $gmt = false ) {
		$reply_id = psf_get_reply_id( $reply_id );

		// 4 days, 4 hours ago
		if ( !empty( $humanize ) ) {
			$gmt_s  = !empty( $gmt ) ? 'G' : 'U';
			$date   = get_post_time( $gmt_s, $gmt, $reply_id );
			$time   = false; // For filter below
			$result = psf_get_time_since( $date );

		// August 4, 2012 at 2:37 pm
		} else {
			$date   = get_post_time( get_option( 'date_format' ), $gmt, $reply_id, true );
			$time   = get_post_time( get_option( 'time_format' ), $gmt, $reply_id, true );
			$result = sprintf( _x( '%1$s um %2$s', 'Datum zur Zeit', 'psforum' ), $date, $time );
		}

		return apply_filters( 'psf_get_reply_post_date', $result, $reply_id, $humanize, $gmt, $date, $time );
	}

/**
 * Append revisions to the reply content
 *
 * @since PSForum (r2782)
 *
 * @param string $content Optional. Content to which we need to append the revisions to
 * @param int $reply_id Optional. Reply id
 * @uses psf_get_reply_revision_log() To get the reply revision log
 * @uses apply_filters() Calls 'psf_reply_append_revisions' with the processed
 *                        content, original content and reply id
 * @return string Content with the revisions appended
 */
function psf_reply_content_append_revisions( $content = '', $reply_id = 0 ) {

	// Bail if in admin or feed
	if ( is_admin() || is_feed() )
		return $content;

	// Validate the ID
	$reply_id = psf_get_reply_id( $reply_id );

	return apply_filters( 'psf_reply_append_revisions', $content . psf_get_reply_revision_log( $reply_id ), $content, $reply_id );
}

/**
 * Output the revision log of the reply
 *
 * @since PSForum (r2782)
 *
 * @param int $reply_id Optional. Reply id
 * @uses psf_get_reply_revision_log() To get the reply revision log
 */
function psf_reply_revision_log( $reply_id = 0 ) {
	echo psf_get_reply_revision_log( $reply_id );
}
	/**
	 * Return the formatted revision log of the reply
	 *
	 * @since PSForum (r2782)
	 *
	 * @param int $reply_id Optional. Reply id
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses psf_get_reply_revisions() To get the reply revisions
	 * @uses psf_get_reply_raw_revision_log() To get the raw revision log
	 * @uses psf_get_reply_author_display_name() To get the reply author
	 * @uses psf_get_reply_author_link() To get the reply author link
	 * @uses psf_convert_date() To convert the date
	 * @uses psf_get_time_since() To get the time in since format
	 * @uses apply_filters() Calls 'psf_get_reply_revision_log' with the
	 *                        log and reply id
	 * @return string Revision log of the reply
	 */
	function psf_get_reply_revision_log( $reply_id = 0 ) {

		// Create necessary variables
		$reply_id = psf_get_reply_id( $reply_id );

		// Show the topic reply log if this is a topic in a reply loop
		if ( psf_is_topic( $reply_id ) ) {
			return psf_get_topic_revision_log( $reply_id );
		}

		// Get the reply revision log (out of post meta
		$revision_log = psf_get_reply_raw_revision_log( $reply_id );

		// Check reply and revision log exist
		if ( empty( $reply_id ) || empty( $revision_log ) || !is_array( $revision_log ) )
			return false;

		// Get the actual revisions
		$revisions = psf_get_reply_revisions( $reply_id );
		if ( empty( $revisions ) )
			return false;

		$r = "\n\n" . '<ul id="psf-reply-revision-log-' . esc_attr( $reply_id ) . '" class="psf-reply-revision-log">' . "\n\n";

		// Loop through revisions
		foreach ( (array) $revisions as $revision ) {

			if ( empty( $revision_log[$revision->ID] ) ) {
				$author_id = $revision->post_author;
				$reason    = '';
			} else {
				$author_id = $revision_log[$revision->ID]['author'];
				$reason    = $revision_log[$revision->ID]['reason'];
			}

			$author = psf_get_author_link( array( 'size' => 14, 'link_text' => psf_get_reply_author_display_name( $revision->ID ), 'post_id' => $revision->ID ) );
			$since  = psf_get_time_since( psf_convert_date( $revision->post_modified ) );

			$r .= "\t" . '<li id="psf-reply-revision-log-' . esc_attr( $reply_id ) . '-item-' . esc_attr( $revision->ID ) . '" class="psf-reply-revision-log-item">' . "\n";
			if ( !empty( $reason ) ) {
				$r .= "\t\t" . sprintf( esc_html__( 'Diese Antwort wurde %1$s von %2$s geändert. Grund: %3$s', 'psforum' ), esc_html( $since ), $author, esc_html( $reason ) ) . "\n";
			} else {
				$r .= "\t\t" . sprintf( esc_html__( 'Diese Antwort wurde %1$s von %2$s geändert.', 'psforum' ), esc_html( $since ), $author ) . "\n";
			}
			$r .= "\t" . '</li>' . "\n";

		}

		$r .= "\n" . '</ul>' . "\n\n";

		return apply_filters( 'psf_get_reply_revision_log', $r, $reply_id );
	}
		/**
		 * Return the raw revision log of the reply
		 *
		 * @since PSForum (r2782)
		 *
		 * @param int $reply_id Optional. Reply id
		 * @uses psf_get_reply_id() To get the reply id
		 * @uses get_post_meta() To get the revision log meta
		 * @uses apply_filters() Calls 'psf_get_reply_raw_revision_log'
		 *                        with the log and reply id
		 * @return string Raw revision log of the reply
		 */
		function psf_get_reply_raw_revision_log( $reply_id = 0 ) {
			$reply_id     = psf_get_reply_id( $reply_id );
			$revision_log = get_post_meta( $reply_id, '_psf_revision_log', true );
			$revision_log = empty( $revision_log ) ? array() : $revision_log;

			return apply_filters( 'psf_get_reply_raw_revision_log', $revision_log, $reply_id );
		}

/**
 * Return the revisions of the reply
 *
 * @since PSForum (r2782)
 *
 * @param int $reply_id Optional. Reply id
 * @uses psf_get_reply_id() To get the reply id
 * @uses wp_get_post_revisions() To get the reply revisions
 * @uses apply_filters() Calls 'psf_get_reply_revisions'
 *                        with the revisions and reply id
 * @return string reply revisions
 */
function psf_get_reply_revisions( $reply_id = 0 ) {
	$reply_id  = psf_get_reply_id( $reply_id );
	$revisions = wp_get_post_revisions( $reply_id, array( 'order' => 'ASC' ) );

	return apply_filters( 'psf_get_reply_revisions', $revisions, $reply_id );
}

/**
 * Return the revision count of the reply
 *
 * @since PSForum (r2782)
 *
 * @param int $reply_id Optional. Reply id
 * @param boolean $integer Optional. Whether or not to format the result
 * @uses psf_get_reply_revisions() To get the reply revisions
 * @uses apply_filters() Calls 'psf_get_reply_revision_count'
 *                        with the revision count and reply id
 * @return string reply revision count
 */
function psf_get_reply_revision_count( $reply_id = 0, $integer = false ) {
	$count  = (int) count( psf_get_reply_revisions( $reply_id ) );
	$filter = ( true === $integer ) ? 'psf_get_reply_revision_count_int' : 'psf_get_reply_revision_count';

	return apply_filters( $filter, $count, $reply_id );
}

/**
 * Output the status of the reply
 *
 * @since PSForum (r2667)
 *
 * @param int $reply_id Optional. Reply id
 * @uses psf_get_reply_status() To get the reply status
 */
function psf_reply_status( $reply_id = 0 ) {
	echo psf_get_reply_status( $reply_id );
}
	/**
	 * Return the status of the reply
	 *
	 * @since PSForum (r2667)
	 *
	 * @param int $reply_id Optional. Reply id
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses get_post_status() To get the reply status
	 * @uses apply_filters() Calls 'psf_get_reply_status' with the reply id
	 * @return string Status of reply
	 */
	function psf_get_reply_status( $reply_id = 0 ) {
		$reply_id = psf_get_reply_id( $reply_id );
		return apply_filters( 'psf_get_reply_status', get_post_status( $reply_id ), $reply_id );
	}

/**
 * Is the reply not spam or deleted?
 *
 * @since PSForum (r3496)
 *
 * @param int $reply_id Optional. Topic id
 * @uses psf_get_reply_id() To get the reply id
 * @uses psf_get_reply_status() To get the reply status
 * @return bool True if published, false if not.
 */
function psf_is_reply_published( $reply_id = 0 ) {
	$reply_status = psf_get_reply_status( psf_get_reply_id( $reply_id ) ) === psf_get_public_status_id();
	return (bool) apply_filters( 'psf_is_reply_published', (bool) $reply_status, $reply_id );
}

/**
 * Is the reply marked as spam?
 *
 * @since PSForum (r2740)
 *
 * @param int $reply_id Optional. Reply id
 * @uses psf_get_reply_id() To get the reply id
 * @uses psf_get_reply_status() To get the reply status
 * @return bool True if spam, false if not.
 */
function psf_is_reply_spam( $reply_id = 0 ) {
	$reply_status = psf_get_reply_status( psf_get_reply_id( $reply_id ) ) === psf_get_spam_status_id();
	return (bool) apply_filters( 'psf_is_reply_spam', (bool) $reply_status, $reply_id );
}

/**
 * Is the reply trashed?
 *
 * @since PSForum (r2884)
 *
 * @param int $reply_id Optional. Topic id
 * @uses psf_get_reply_id() To get the reply id
 * @uses psf_get_reply_status() To get the reply status
 * @return bool True if spam, false if not.
 */
function psf_is_reply_trash( $reply_id = 0 ) {
	$reply_status = psf_get_reply_status( psf_get_reply_id( $reply_id ) ) === psf_get_trash_status_id();
	return (bool) apply_filters( 'psf_is_reply_trash', (bool) $reply_status, $reply_id );
}

/**
 * Is the reply by an anonymous user?
 *
 * @since PSForum (r2753)
 *
 * @param int $reply_id Optional. Reply id
 * @uses psf_get_reply_id() To get the reply id
 * @uses psf_get_reply_author_id() To get the reply author id
 * @uses get_post_meta() To get the anonymous name and email metas
 * @return bool True if the post is by an anonymous user, false if not.
 */
function psf_is_reply_anonymous( $reply_id = 0 ) {
	$reply_id = psf_get_reply_id( $reply_id );
	$retval   = false;

	if ( !psf_get_reply_author_id( $reply_id ) )
		$retval = true;

	elseif ( get_post_meta( $reply_id, '_psf_anonymous_name', true ) )
		$retval = true;

	elseif ( get_post_meta( $reply_id, '_psf_anonymous_email', true ) )
		$retval = true;

	return (bool) apply_filters( 'psf_is_reply_anonymous', $retval, $reply_id );
}

/**
 * Deprecated. Use psf_reply_author_display_name() instead.
 *
 * Output the author of the reply
 *
 * @since PSForum (r2667)
 * @deprecated PSForum (r5119)
 *
 * @param int $reply_id Optional. Reply id
 * @uses psf_get_reply_author() To get the reply author
 */
function psf_reply_author( $reply_id = 0 ) {
	echo psf_get_reply_author( $reply_id );
}
	/**
	 * Deprecated. Use psf_get_reply_author_display_name() instead.
	 *
	 * Return the author of the reply
	 *
	 * @since PSForum (r2667)
	 * @deprecated PSForum (r5119)
	 *
	 * @param int $reply_id Optional. Reply id
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses psf_is_reply_anonymous() To check if the reply is by an
	 *                                 anonymous user
	 * @uses get_the_author_meta() To get the reply author display name
	 * @uses get_post_meta() To get the anonymous poster name
	 * @uses apply_filters() Calls 'psf_get_reply_author' with the reply
	 *                        author and reply id
	 * @return string Author of reply
	 */
	function psf_get_reply_author( $reply_id = 0 ) {
		$reply_id = psf_get_reply_id( $reply_id );

		if ( !psf_is_reply_anonymous( $reply_id ) ) {
			$author = get_the_author_meta( 'display_name', psf_get_reply_author_id( $reply_id ) );
		} else {
			$author = get_post_meta( $reply_id, '_psf_anonymous_name', true );
		}

		return apply_filters( 'psf_get_reply_author', $author, $reply_id );
	}

/**
 * Output the author ID of the reply
 *
 * @since PSForum (r2667)
 *
 * @param int $reply_id Optional. Reply id
 * @uses psf_get_reply_author_id() To get the reply author id
 */
function psf_reply_author_id( $reply_id = 0 ) {
	echo psf_get_reply_author_id( $reply_id );
}
	/**
	 * Return the author ID of the reply
	 *
	 * @since PSForum (r2667)
	 *
	 * @param int $reply_id Optional. Reply id
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses get_post_field() To get the reply author id
	 * @uses apply_filters() Calls 'psf_get_reply_author_id' with the author
	 *                        id and reply id
	 * @return string Author id of reply
	 */
	function psf_get_reply_author_id( $reply_id = 0 ) {
		$reply_id  = psf_get_reply_id( $reply_id );
		$author_id = get_post_field( 'post_author', $reply_id );

		return (int) apply_filters( 'psf_get_reply_author_id', $author_id, $reply_id );
	}

/**
 * Output the author display_name of the reply
 *
 * @since PSForum (r2667)
 *
 * @param int $reply_id Optional. Reply id
 * @uses psf_get_reply_author_display_name()
 */
function psf_reply_author_display_name( $reply_id = 0 ) {
	echo psf_get_reply_author_display_name( $reply_id );
}
	/**
	 * Return the author display_name of the reply
	 *
	 * @since PSForum (r2667)
	 *
	 * @param int $reply_id Optional. Reply id
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses psf_is_reply_anonymous() To check if the reply is by an
	 *                                 anonymous user
	 * @uses psf_get_reply_author_id() To get the reply author id
	 * @uses get_the_author_meta() To get the reply author's display name
	 * @uses get_post_meta() To get the anonymous poster's name
	 * @uses apply_filters() Calls 'psf_get_reply_author_display_name' with
	 *                        the author display name and reply id
	 * @return string Reply's author's display name
	 */
	function psf_get_reply_author_display_name( $reply_id = 0 ) {
		$reply_id = psf_get_reply_id( $reply_id );

		// User is not a guest
		if ( !psf_is_reply_anonymous( $reply_id ) ) {

			// Get the author ID
			$author_id = psf_get_reply_author_id( $reply_id );

			// Try to get a display name
			$author_name = get_the_author_meta( 'display_name', $author_id );

			// Fall back to user login
			if ( empty( $author_name ) ) {
				$author_name = get_the_author_meta( 'user_login', $author_id );
			}

		// User does not have an account
		} else {
			$author_name = get_post_meta( $reply_id, '_psf_anonymous_name', true );
		}

		// If nothing could be found anywhere, use Anonymous
		if ( empty( $author_name ) )
			$author_name = __( 'Anonym', 'psforum' );

		// Encode possible UTF8 display names
		if ( seems_utf8( $author_name ) === false )
			$author_name = utf8_encode( $author_name );

		return apply_filters( 'psf_get_reply_author_display_name', $author_name, $reply_id );
	}

/**
 * Output the author avatar of the reply
 *
 * @since PSForum (r2667)
 *
 * @param int $reply_id Optional. Reply id
 * @param int $size Optional. Size of the avatar. Defaults to 40
 * @uses psf_get_reply_author_avatar() To get the reply author id
 */
function psf_reply_author_avatar( $reply_id = 0, $size = 40 ) {
	echo psf_get_reply_author_avatar( $reply_id, $size );
}
	/**
	 * Return the author avatar of the reply
	 *
	 * @since PSForum (r2667)
	 *
	 * @param int $reply_id Optional. Reply id
	 * @param int $size Optional. Size of the avatar. Defaults to 40
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses psf_is_reply_anonymous() To check if the reply is by an
	 *                                 anonymous user
	 * @uses psf_get_reply_author_id() To get the reply author id
	 * @uses get_post_meta() To get the anonymous poster's email id
	 * @uses get_avatar() To get the avatar
	 * @uses apply_filters() Calls 'psf_get_reply_author_avatar' with the
	 *                        author avatar, reply id and size
	 * @return string Avatar of author of the reply
	 */
	function psf_get_reply_author_avatar( $reply_id = 0, $size = 40 ) {
		$reply_id = psf_get_reply_id( $reply_id );
		if ( !empty( $reply_id ) ) {
			// Check for anonymous user
			if ( !psf_is_reply_anonymous( $reply_id ) ) {
				$author_avatar = get_avatar( psf_get_reply_author_id( $reply_id ), $size );
			} else {
				$author_avatar = get_avatar( get_post_meta( $reply_id, '_psf_anonymous_email', true ), $size );
			}
		} else {
			$author_avatar = '';
		}

		return apply_filters( 'psf_get_reply_author_avatar', $author_avatar, $reply_id, $size );
	}

/**
 * Output the author link of the reply
 *
 * @since PSForum (r2717)
 *
 * @param mixed $args Optional. If it is an integer, it is used as reply id.
 * @uses psf_get_reply_author_link() To get the reply author link
 */
function psf_reply_author_link( $args = '' ) {
	echo psf_get_reply_author_link( $args );
}
	/**
	 * Return the author link of the reply
	 *
	 * @since PSForum (r2717)
	 *
	 * @param mixed $args Optional. If an integer, it is used as reply id.
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses psf_is_reply_anonymous() To check if the reply is by an
	 *                                 anonymous user
	 * @uses psf_get_reply_author_url() To get the reply author url
	 * @uses psf_get_reply_author_avatar() To get the reply author avatar
	 * @uses psf_get_reply_author_display_name() To get the reply author display
	 *                                      name
	 * @uses psf_get_user_display_role() To get the reply author display role
	 * @uses psf_get_reply_author_id() To get the reply author id
	 * @uses apply_filters() Calls 'psf_get_reply_author_link' with the
	 *                        author link and args
	 * @return string Author link of reply
	 */
	function psf_get_reply_author_link( $args = '' ) {

		// Parse arguments against default values
		$r = psf_parse_args( $args, array(
			'post_id'    => 0,
			'link_title' => '',
			'type'       => 'both',
			'size'       => 80,
			'sep'        => '&nbsp;',
			'show_role'  => false
		), 'get_reply_author_link' );

		// Used as reply_id
		if ( is_numeric( $args ) ) {
			$reply_id = psf_get_reply_id( $args );
		} else {
			$reply_id = psf_get_reply_id( $r['post_id'] );
		}

		// Reply ID is good
		if ( !empty( $reply_id ) ) {

			// Get some useful reply information
			$author_url = psf_get_reply_author_url( $reply_id );
			$anonymous  = psf_is_reply_anonymous( $reply_id );

			// Tweak link title if empty
			if ( empty( $r['link_title'] ) ) {
				$link_title = sprintf( empty( $anonymous ) ? __( 'Profil von %s anzeigen', 'psforum' ) : __( 'Besuche die Website von %s', 'psforum' ), psf_get_reply_author_display_name( $reply_id ) );

			// Use what was passed if not
			} else {
				$link_title = $r['link_title'];
			}

			// Setup title and author_links array
			$link_title   = !empty( $link_title ) ? ' title="' . esc_attr( $link_title ) . '"' : '';
			$author_links = array();

			// Get avatar
			if ( 'avatar' === $r['type'] || 'both' === $r['type'] ) {
				$author_links['avatar'] = psf_get_reply_author_avatar( $reply_id, $r['size'] );
			}

			// Get display name
			if ( 'name' === $r['type']   || 'both' === $r['type'] ) {
				$author_links['name'] = psf_get_reply_author_display_name( $reply_id );
			}

			// Link class
			$link_class = ' class="psf-author-' . esc_attr( $r['type'] ) . '"';

			// Add links if not anonymous and existing user
			if ( empty( $anonymous ) && psf_user_has_profile( psf_get_reply_author_id( $reply_id ) ) ) {

				$author_link = array();

				// Assemble the links
				foreach ( $author_links as $link => $link_text ) {
					$link_class = ' class="psf-author-' . $link . '"';
					$author_link[] = sprintf( '<a href="%1$s"%2$s%3$s>%4$s</a>', esc_url( $author_url ), $link_title, $link_class, $link_text );
				}

				if ( true === $r['show_role'] ) {
					$author_link[] = psf_get_reply_author_role( array( 'reply_id' => $reply_id ) );
				}

				$author_link = implode( $r['sep'], $author_link );

			// No links if anonymous
			} else {
				$author_link = implode( $r['sep'], $author_links );
			}

		// No replies so link is empty
		} else {
			$author_link = '';
		}

		return apply_filters( 'psf_get_reply_author_link', $author_link, $r );
	}

/**
 * Output the author url of the reply
 *
 * @since PSForum (r2667)
 *
 * @param int $reply_id Optional. Reply id
 * @uses psf_get_reply_author_url() To get the reply author url
 */
function psf_reply_author_url( $reply_id = 0 ) {
	echo esc_url( psf_get_reply_author_url( $reply_id ) );
}
	/**
	 * Return the author url of the reply
	 *
	 * @since PSForum (r2667)
	 *
	 * @param int $reply_id Optional. Reply id
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses psf_is_reply_anonymous() To check if the reply is by an anonymous
	 *                                 user
	 * @uses psf_user_has_profile() To check if the user has a profile
	 * @uses psf_get_reply_author_id() To get the reply author id
	 * @uses psf_get_user_profile_url() To get the user profile url
	 * @uses get_post_meta() To get the anonymous poster's website url
	 * @uses apply_filters() Calls psf_get_reply_author_url with the author
	 *                        url & reply id
	 * @return string Author URL of the reply
	 */
	function psf_get_reply_author_url( $reply_id = 0 ) {
		$reply_id = psf_get_reply_id( $reply_id );

		// Check for anonymous user or non-existant user
		if ( !psf_is_reply_anonymous( $reply_id ) && psf_user_has_profile( psf_get_reply_author_id( $reply_id ) ) ) {
			$author_url = psf_get_user_profile_url( psf_get_reply_author_id( $reply_id ) );
		} else {
			$author_url = get_post_meta( $reply_id, '_psf_anonymous_website', true );
			if ( empty( $author_url ) ) {
				$author_url = '';
			}
		}

		return apply_filters( 'psf_get_reply_author_url', $author_url, $reply_id );
	}

/**
 * Output the reply author email address
 *
 * @since PSForum (r3445)
 *
 * @param int $reply_id Optional. Reply id
 * @uses psf_get_reply_author_email() To get the reply author email
 */
function psf_reply_author_email( $reply_id = 0 ) {
	echo psf_get_reply_author_email( $reply_id );
}
	/**
	 * Return the reply author email address
	 *
	 * @since PSForum (r3445)
	 *
	 * @param int $reply_id Optional. Reply id
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses psf_is_reply_anonymous() To check if the reply is by an anonymous
	 *                                 user
	 * @uses psf_get_reply_author_id() To get the reply author id
	 * @uses get_userdata() To get the user data
	 * @uses get_post_meta() To get the anonymous poster's website email
	 * @uses apply_filters() Calls psf_get_reply_author_email with the author
	 *                        email & reply id
	 * @return string Reply author email address
	 */
	function psf_get_reply_author_email( $reply_id = 0 ) {
		$reply_id = psf_get_reply_id( $reply_id );

		// Not anonymous
		if ( !psf_is_reply_anonymous( $reply_id ) ) {

			// Use reply author email address
			$user_id      = psf_get_reply_author_id( $reply_id );
			$user         = get_userdata( $user_id );
			$author_email = !empty( $user->user_email ) ? $user->user_email : '';

		// Anonymous
		} else {

			// Get email from post meta
			$author_email = get_post_meta( $reply_id, '_psf_anonymous_email', true );

			// Sanity check for missing email address
			if ( empty( $author_email ) ) {
				$author_email = '';
			}
		}

		return apply_filters( 'psf_get_reply_author_email', $author_email, $reply_id );
	}

/**
 * Output the reply author role
 *
 * @since PSForum (r3860)
 *
 * @param array $args Optional.
 * @uses psf_get_reply_author_role() To get the reply author role
 */
function psf_reply_author_role( $args = array() ) {
	echo psf_get_reply_author_role( $args );
}
	/**
	 * Return the reply author role
	 *
	 * @since PSForum (r3860)
	 *
	 * @param array $args Optional.
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses psf_get_user_display_role() To get the user display role
	 * @uses psf_get_reply_author_id() To get the reply author id
	 * @uses apply_filters() Calls psf_get_reply_author_role with the author
	 *                        role & args
	 * @return string Reply author role
	 */
	function psf_get_reply_author_role( $args = array() ) {

		// Parse arguments against default values
		$r = psf_parse_args( $args, array(
			'reply_id' => 0,
			'class'    => 'psf-author-role',
			'before'   => '',
			'after'    => ''
		), 'get_reply_author_role' );

		$reply_id    = psf_get_reply_id( $r['reply_id'] );
		$role        = psf_get_user_display_role( psf_get_reply_author_id( $reply_id ) );
		$author_role = sprintf( '%1$s<div class="%2$s">%3$s</div>%4$s', $r['before'], esc_attr( $r['class'] ), esc_html( $role ), $r['after'] );

		return apply_filters( 'psf_get_reply_author_role', $author_role, $r );
	}

/**
 * Output the topic title a reply belongs to
 *
 * @since PSForum (r2553)
 *
 * @param int $reply_id Optional. Reply id
 * @uses psf_get_reply_topic_title() To get the reply topic title
 */
function psf_reply_topic_title( $reply_id = 0 ) {
	echo psf_get_reply_topic_title( $reply_id );
}
	/**
	 * Return the topic title a reply belongs to
	 *
	 * @since PSForum (r2553)
	 *
	 * @param int $reply_id Optional. Reply id
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses psf_get_reply_topic_id() To get the reply topic id
	 * @uses psf_get_topic_title() To get the reply topic title
	 * @uses apply_filters() Calls 'psf_get_reply_topic_title' with the
	 *                        topic title and reply id
	 * @return string Reply's topic's title
	 */
	function psf_get_reply_topic_title( $reply_id = 0 ) {
		$reply_id = psf_get_reply_id( $reply_id );
		$topic_id = psf_get_reply_topic_id( $reply_id );

		return apply_filters( 'psf_get_reply_topic_title', psf_get_topic_title( $topic_id ), $reply_id );
	}

/**
 * Output the topic id a reply belongs to
 *
 * @since PSForum (r2553)
 *
 * @param int $reply_id Optional. Reply id
 * @uses psf_get_reply_topic_id() To get the reply topic id
 */
function psf_reply_topic_id( $reply_id = 0 ) {
	echo psf_get_reply_topic_id( $reply_id );
}
	/**
	 * Return the topic id a reply belongs to
	 *
	 * @since PSForum (r2553)
	 *
	 * @param int $reply_id Optional. Reply id
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses get_post_meta() To get the reply topic id from meta
	 * @uses psf_get_topic_id() To get the topic id
	 * @uses apply_filters() Calls 'psf_get_reply_topic_id' with the topic
	 *                        id and reply id
	 * @return int Reply's topic id
	 */
	function psf_get_reply_topic_id( $reply_id = 0 ) {

		// Assume there is no topic id
		$topic_id = 0;

		// Check that reply_id is valid
		if ( $reply_id = psf_get_reply_id( $reply_id ) )

			// Get topic_id from reply
			if ( $topic_id = get_post_meta( $reply_id, '_psf_topic_id', true ) )

				// Validate the topic_id
				$topic_id = psf_get_topic_id( $topic_id );

		return (int) apply_filters( 'psf_get_reply_topic_id', $topic_id, $reply_id );
	}

/**
 * Output the forum id a reply belongs to
 *
 * @since PSForum (r2679)
 *
 * @param int $reply_id Optional. Reply id
 * @uses psf_get_reply_forum_id() To get the reply forum id
 */
function psf_reply_forum_id( $reply_id = 0 ) {
	echo psf_get_reply_forum_id( $reply_id );
}
	/**
	 * Return the forum id a reply belongs to
	 *
	 * @since PSForum (r2679)
	 *
	 * @param int $reply_id Optional. Reply id
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses get_post_meta() To get the reply forum id
	 * @uses apply_filters() Calls 'psf_get_reply_forum_id' with the forum
	 *                        id and reply id
	 * @return int Reply's forum id
	 */
	function psf_get_reply_forum_id( $reply_id = 0 ) {

		// Assume there is no forum
		$forum_id = 0;

		// Check that reply_id is valid
		if ( $reply_id = psf_get_reply_id( $reply_id ) )

			// Get forum_id from reply
			if ( $forum_id = get_post_meta( $reply_id, '_psf_forum_id', true ) )

				// Validate the forum_id
				$forum_id = psf_get_forum_id( $forum_id );

		return (int) apply_filters( 'psf_get_reply_forum_id', $forum_id, $reply_id );
	}

/**
 * Output the reply's ancestor reply id
 *
 * @since PSForum (r4944)
 *
 * @param int $reply_id Optional. Reply id
 * @uses psf_get_reply_ancestor_id() To get the reply's ancestor id
 */
function psf_reply_ancestor_id( $reply_id = 0 ) {
	echo psf_get_reply_ancestor_id( $reply_id );
}
	/**
	 * Return the reply's ancestor reply id
	 *
	 * @since PSForum (r4944)
	 *
	 * @param in $reply_id Reply id
	 * @uses psf_get_reply_id() To get the reply id
	 */
	function psf_get_reply_ancestor_id( $reply_id = 0 ) {

		// Validation
		$reply_id = psf_get_reply_id( $reply_id );
		if ( empty( $reply_id ) )
			return false;

		// Find highest reply ancestor
		$ancestor_id = $reply_id;
		while ( $parent_id = psf_get_reply_to( $ancestor_id ) ) {
			if ( empty( $parent_id ) || ( $parent_id === $ancestor_id ) || ( psf_get_reply_topic_id( $reply_id ) === $parent_id ) || ( $parent_id === $reply_id ) ) {
				break;
			}
			$ancestor_id = $parent_id;
		}

		return (int) $ancestor_id;
	}

/**
 * Output the reply to id of a reply
 *
 * @since PSForum (r4944)
 *
 * @param int $reply_id Optional. Reply id
 * @uses psf_get_reply_to() To get the reply to id
 */
function psf_reply_to( $reply_id = 0 ) {
	echo psf_get_reply_to( $reply_id );
}
	/**
	 * Return the reply to id of a reply
	 *
 	 * @since PSForum (r4944)
	 *
	 * @param int $reply_id Optional. Reply id
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses get_post_meta() To get the reply to id
	 * @uses apply_filters() Calls 'psf_get_reply_to' with the reply to id and
	 *                        reply id
	 * @return int Reply's reply to id
	 */
	function psf_get_reply_to( $reply_id = 0 ) {

		// Assume there is no reply_to set
		$reply_to = 0;

		// Check that reply_id is valid
		$reply_id = psf_get_reply_id( $reply_id );

		// Get reply_to value
		if ( !empty( $reply_id ) ) {
			$reply_to = (int) get_post_meta( $reply_id, '_psf_reply_to', true );
		}

		return (int) apply_filters( 'psf_get_reply_to', $reply_to, $reply_id );
	}

/**
 * Output the link for the reply to
 *
 * @since PSForum (r4944)
 *
 * @param array $args
 * @uses psf_get_reply_to_link() To get the reply to link
 */
function psf_reply_to_link( $args = array() ) {
	echo psf_get_reply_to_link( $args );
}

	/**
	 * Return the link for a reply to a reply
	 *
	 * @since PSForum (r4944)
	 *
	 * @param array $args Arguments
	 * @uses psf_current_user_can_access_create_reply_form() To check permissions
	 * @uses psf_get_reply_id() To validate the reply id
	 * @uses psf_get_reply() To get the reply
	 * @uses apply_filters() Calls 'psf_get_reply_to_link' with the formatted link,
	 *                        the arguments array, and the reply
	 * @return string Link for a reply to a reply
	 */
	function psf_get_reply_to_link( $args = array() ) {

		// Parse arguments against default values
		$r = psf_parse_args( $args, array(
			'id'           => 0,
			'link_before'  => '',
			'link_after'   => '',
			'reply_text'   => __( 'Antwort', 'psforum' ),
			'depth'        => 0,
			'add_below'    => 'post',
			'respond_id'   => 'new-reply-' . psf_get_topic_id(),
		), 'get_reply_to_link' );

		// Get the reply to use it's ID and post_parent
		$reply = psf_get_reply( psf_get_reply_id( (int) $r['id'] ) );

		// Bail if no reply or user cannot reply
		if ( empty( $reply ) || ! psf_current_user_can_access_create_reply_form() )
			return;

		// Build the URI and return value
		$uri = remove_query_arg( array( 'psf_reply_to' ) );
		$uri = add_query_arg( array( 'psf_reply_to' => $reply->ID ) );
		$uri = wp_nonce_url( $uri, 'respond_id_' . $reply->ID );
		$uri = $uri . '#new-post';

		// Only add onclick if replies are threaded
		if ( psf_thread_replies() ) {

			// Array of classes to pass to moveForm
			$move_form = array(
				$r['add_below'] . '-' . $reply->ID,
				$reply->ID,
				$r['respond_id'],
				$reply->post_parent
			);

			// Build the onclick
			$onclick  = ' onclick="return addReply.moveForm(\'' . implode( "','", $move_form ) . '\');"';

		// No onclick if replies are not threaded
		} else {
			$onclick  = '';
		}

		// Add $uri to the array, to be passed through the filter
		$r['uri'] = $uri;
		$retval   = $r['link_before'] . '<a href="' . esc_url( $r['uri'] ) . '" class="psf-reply-to-link"' . $onclick . '>' . esc_html( $r['reply_text'] ) . '</a>' . $r['link_after'];

		return apply_filters( 'psf_get_reply_to_link', $retval, $r, $args );
	}

/**
 * Output the reply to a reply cancellation link
 *
 * @since PSForum (r4944)
 *
 * @uses psf_get_cancel_reply_to_link() To get the reply cancellation link
 */
function psf_cancel_reply_to_link( $text = '' ) {
	echo psf_get_cancel_reply_to_link( $text );
}
	/**
	 * Return the cancellation link for a reply to a reply
	 *
	 * @since PSForum (r4944)
	 *
	 * @param string $text The cancel text
	 * @uses apply_filters() Calls 'psf_get_cancel_reply_to_link' with the cancellation
	 *                        link and the cancel text
	 * @return string The cancellation link
	 */
	function psf_get_cancel_reply_to_link( $text = '' ) {

		// Bail if not hierarchical or editing a reply
		if ( ! psf_thread_replies() || psf_is_reply_edit() ) {
			return;
		}

		// Set default text
		if ( empty( $text ) ) {
			$text = __( 'Abbrechen', 'psforum' );
		}

		$reply_to = isset( $_GET['psf_reply_to'] ) ? (int) $_GET['psf_reply_to'] : 0;

		// Set visibility
		$style  = !empty( $reply_to ) ? '' : ' style="display:none;"';
		$link   = remove_query_arg( array( 'psf_reply_to', '_wpnonce' ) ) . '#post-' . $reply_to;
		$retval = '<a rel="nofollow" id="psf-cancel-reply-to-link" href="' . esc_url( $link ) . '"' . $style . '>' . esc_html( $text ) . '</a>';

		return apply_filters( 'psf_get_cancel_reply_to_link', $retval, $link, $text );
	}

/**
 * Output the numeric position of a reply within a topic
 *
 * @since PSForum (r2984)
 *
 * @param int $reply_id Optional. Reply id
 * @param int $topic_id Optional. Topic id
 * @uses psf_get_reply_position() To get the reply position
 */
function psf_reply_position( $reply_id = 0, $topic_id = 0 ) {
	echo psf_get_reply_position( $reply_id, $topic_id );
}
	/**
	 * Return the numeric position of a reply within a topic
	 *
	 * @since PSForum (r2984)
	 *
	 * @param int $reply_id Optional. Reply id
	 * @param int $topic_id Optional. Topic id
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses psf_get_reply_topic_id() Get the topic id of the reply id
	 * @uses psf_get_topic_reply_count() To get the topic reply count
	 * @uses psf_get_reply_post_type() To get the reply post type
	 * @uses psf_get_reply_position_raw() To get calculate the reply position
	 * @uses psf_update_reply_position() To update the reply position
	 * @uses psf_show_lead_topic() Bump the count if lead topic is included
	 * @uses apply_filters() Calls 'psf_get_reply_position' with the reply
	 *                        position, reply id and topic id
	 * @return int Reply position
	 */
	function psf_get_reply_position( $reply_id = 0, $topic_id = 0 ) {

		// Get required data
		$reply_id       = psf_get_reply_id( $reply_id );
		$reply_position = get_post_field( 'menu_order', $reply_id );

		// Reply doesn't have a position so get the raw value
		if ( empty( $reply_position ) ) {
			$topic_id = !empty( $topic_id ) ? psf_get_topic_id( $topic_id ) : psf_get_reply_topic_id( $reply_id );

			// Post is not the topic
			if ( $reply_id !== $topic_id ) {
				$reply_position = psf_get_reply_position_raw( $reply_id, $topic_id );

				// Update the reply position in the posts table so we'll never have
				// to hit the DB again.
				if ( !empty( $reply_position ) ) {
					psf_update_reply_position( $reply_id, $reply_position );
				}

			// Topic's position is always 0
			} else {
				$reply_position = 0;
			}
		}

		// Bump the position by one if the lead topic is in the replies loop
		if ( ! psf_show_lead_topic() )
			$reply_position++;

		return (int) apply_filters( 'psf_get_reply_position', $reply_position, $reply_id, $topic_id );
	}

/** Reply Admin Links *********************************************************/

/**
 * Output admin links for reply
 *
 * @since PSForum (r2667)
 *
 * @param array $args See {@link psf_get_reply_admin_links()}
 * @uses psf_get_reply_admin_links() To get the reply admin links
 */
function psf_reply_admin_links( $args = array() ) {
	echo psf_get_reply_admin_links( $args );
}
	/**
	 * Return admin links for reply
	 *
	 * @since PSForum (r2667)
	 *
	 * @param array $args This function supports these arguments:
	 *  - id: Optional. Reply id
	 *  - before: HTML before the links. Defaults to
	 *             '<span class="psf-admin-links">'
	 *  - after: HTML after the links. Defaults to '</span>'
	 *  - sep: Separator. Defaults to ' | '
	 *  - links: Array of the links to display. By default, edit, trash,
	 *            spam, reply move, and topic split links are displayed
	 * @uses psf_is_topic() To check if it's the topic page
	 * @uses psf_is_reply() To check if it's the reply page
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses psf_get_reply_edit_link() To get the reply edit link
	 * @uses psf_get_reply_trash_link() To get the reply trash link
	 * @uses psf_get_reply_spam_link() To get the reply spam link
	 * @uses psf_get_reply_move_link() To get the reply move link
	 * @uses psf_get_topic_split_link() To get the topic split link
	 * @uses current_user_can() To check if the current user can edit or
	 *                           delete the reply
	 * @uses apply_filters() Calls 'psf_get_reply_admin_links' with the
	 *                        reply admin links and args
	 * @return string Reply admin links
	 */
	function psf_get_reply_admin_links( $args = array() ) {

		// Parse arguments against default values
		$r = psf_parse_args( $args, array(
			'id'     => 0,
			'before' => '<span class="psf-admin-links">',
			'after'  => '</span>',
			'sep'    => ' | ',
			'links'  => array()
		), 'get_reply_admin_links' );

		$r['id'] = psf_get_reply_id( (int) $r['id'] );

		// If post is a topic, return the topic admin links instead
		if ( psf_is_topic( $r['id'] ) ) {
			return psf_get_topic_admin_links( $args );
		}

		// If post is not a reply, return
		if ( !psf_is_reply( $r['id'] ) ) {
			return;
		}

		// If topic is trashed, do not show admin links
		if ( psf_is_topic_trash( psf_get_reply_topic_id( $r['id'] ) ) ) {
			return;
		}

		// If no links were passed, default to the standard
		if ( empty( $r['links'] ) ) {
			$r['links'] = apply_filters( 'psf_reply_admin_links', array(
				'edit'  => psf_get_reply_edit_link ( $r ),
				'move'  => psf_get_reply_move_link ( $r ),
				'split' => psf_get_topic_split_link( $r ),
				'trash' => psf_get_reply_trash_link( $r ),
				'spam'  => psf_get_reply_spam_link ( $r ),
				'reply' => psf_get_reply_to_link   ( $r )
			), $r['id'] );
		}

		// See if links need to be unset
		$reply_status = psf_get_reply_status( $r['id'] );
		if ( in_array( $reply_status, array( psf_get_spam_status_id(), psf_get_trash_status_id() ) ) ) {

			// Spam link shouldn't be visible on trashed topics
			if ( psf_get_trash_status_id() === $reply_status ) {
				unset( $r['links']['spam'] );

			// Trash link shouldn't be visible on spam topics
			} elseif ( psf_get_spam_status_id() === $reply_status ) {
				unset( $r['links']['trash'] );
			}
		}

		// Process the admin links
		$links  = implode( $r['sep'], array_filter( $r['links'] ) );
		$retval = $r['before'] . $links . $r['after'];

		return apply_filters( 'psf_get_reply_admin_links', $retval, $r, $args );
	}

/**
 * Output the edit link of the reply
 *
 * @since PSForum (r2740)
 *
 * @param mixed $args See {@link psf_get_reply_edit_link()}
 * @uses psf_get_reply_edit_link() To get the reply edit link
 */
function psf_reply_edit_link( $args = '' ) {
	echo psf_get_reply_edit_link( $args );
}

	/**
	 * Return the edit link of the reply
	 *
	 * @since PSForum (r2740)
	 *
	 * @param mixed $args This function supports these arguments:
	 *  - id: Reply id
	 *  - link_before: HTML before the link
	 *  - link_after: HTML after the link
	 *  - edit_text: Edit text. Defaults to 'Edit'
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses psf_get_reply() To get the reply
	 * @uses current_user_can() To check if the current user can edit the
	 *                           reply
	 * @uses psf_get_reply_edit_url() To get the reply edit url
	 * @uses apply_filters() Calls 'psf_get_reply_edit_link' with the reply
	 *                        edit link and args
	 * @return string Reply edit link
	 */
	function psf_get_reply_edit_link( $args = '' ) {

		// Parse arguments against default values
		$r = psf_parse_args( $args, array(
			'id'           => 0,
			'link_before'  => '',
			'link_after'   => '',
			'edit_text'    => esc_html__( 'Bearbeiten', 'psforum' )
		), 'get_reply_edit_link' );

		$reply = psf_get_reply( psf_get_reply_id( (int) $r['id'] ) );

		// Bypass check if user has caps
		if ( !current_user_can( 'edit_others_replies' ) ) {

			// User cannot edit or it is past the lock time
			if ( empty( $reply ) || !current_user_can( 'edit_reply', $reply->ID ) || psf_past_edit_lock( $reply->post_date_gmt ) ) {
				return;
			}
		}

		// Get uri
		$uri = psf_get_reply_edit_url( $r['id'] );

		// Bail if no uri
		if ( empty( $uri ) )
			return;

		$retval = $r['link_before'] . '<a href="' . esc_url( $uri ) . '" class="psf-reply-edit-link">' . $r['edit_text'] . '</a>' . $r['link_after'];

		return apply_filters( 'psf_get_reply_edit_link', $retval, $r );
	}

/**
 * Output URL to the reply edit page
 *
 * @since PSForum (r2753)
 *
 * @param int $reply_id Optional. Reply id
 * @uses psf_get_reply_edit_url() To get the reply edit url
 */
function psf_reply_edit_url( $reply_id = 0 ) {
	echo esc_url( psf_get_reply_edit_url( $reply_id ) );
}
	/**
	 * Return URL to the reply edit page
	 *
	 * @since PSForum (r2753)
	 *
	 * @param int $reply_id Optional. Reply id
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses psf_get_reply() To get the reply
	 * @uses psf_get_reply_post_type() To get the reply post type
	 * @uses add_query_arg() To add custom args to the url
	 * @uses apply_filters() Calls 'psf_get_reply_edit_url' with the edit
	 *                        url and reply id
	 * @return string Reply edit url
	 */
	function psf_get_reply_edit_url( $reply_id = 0 ) {
		global $wp_rewrite;

		$psf   = psforum();
		$reply = psf_get_reply( psf_get_reply_id( $reply_id ) );
		if ( empty( $reply ) )
			return;

		$reply_link = psf_remove_view_all( psf_get_reply_permalink( $reply_id ) );

		// Pretty permalinks
		if ( $wp_rewrite->using_permalinks() ) {
			$url = trailingslashit( $reply_link ) . $psf->edit_id;
			$url = trailingslashit( $url );

		// Unpretty permalinks
		} else {
			$url = add_query_arg( array( psf_get_reply_post_type() => $reply->post_name, $psf->edit_id => '1' ), $reply_link );
		}

		// Maybe add view all
		$url = psf_add_view_all( $url );

		return apply_filters( 'psf_get_reply_edit_url', $url, $reply_id );
	}

/**
 * Output the trash link of the reply
 *
 * @since PSForum (r2740)
 *
 * @param mixed $args See {@link psf_get_reply_trash_link()}
 * @uses psf_get_reply_trash_link() To get the reply trash link
 */
function psf_reply_trash_link( $args = '' ) {
	echo psf_get_reply_trash_link( $args );
}

	/**
	 * Return the trash link of the reply
	 *
	 * @since PSForum (r2740)
	 *
	 * @param mixed $args This function supports these arguments:
	 *  - id: Reply id
	 *  - link_before: HTML before the link
	 *  - link_after: HTML after the link
	 *  - sep: Separator
	 *  - trash_text: Trash text
	 *  - restore_text: Restore text
	 *  - delete_text: Delete text
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses psf_get_reply() To get the reply
	 * @uses current_user_can() To check if the current user can delete the
	 *                           reply
	 * @uses psf_is_reply_trash() To check if the reply is trashed
	 * @uses psf_get_reply_status() To get the reply status
	 * @uses add_query_arg() To add custom args to the url
	 * @uses wp_nonce_url() To nonce the url
	 * @uses esc_url() To escape the url
	 * @uses psf_get_reply_edit_url() To get the reply edit url
	 * @uses apply_filters() Calls 'psf_get_reply_trash_link' with the reply
	 *                        trash link and args
	 * @return string Reply trash link
	 */
	function psf_get_reply_trash_link( $args = '' ) {

		// Parse arguments against default values
		$r = psf_parse_args( $args, array(
			'id'           => 0,
			'link_before'  => '',
			'link_after'   => '',
			'sep'          => ' | ',
			'trash_text'   => esc_html__( 'Papierkorb',   'psforum' ),
			'restore_text' => esc_html__( 'Wiederherstellen', 'psforum' ),
			'delete_text'  => esc_html__( 'Löschen',  'psforum' )
		), 'get_reply_trash_link' );

		$actions = array();
		$reply   = psf_get_reply( psf_get_reply_id( (int) $r['id'] ) );

		if ( empty( $reply ) || !current_user_can( 'delete_reply', $reply->ID ) ) {
			return;
		}

		if ( psf_is_reply_trash( $reply->ID ) ) {
			$actions['untrash'] = '<a title="' . esc_attr__( 'Dieses Element aus dem Papierkorb wiederherstellen', 'psforum' ) . '" href="' . esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'psf_toggle_reply_trash', 'sub_action' => 'untrash', 'reply_id' => $reply->ID ) ), 'untrash-' . $reply->post_type . '_' . $reply->ID ) ) . '" class="psf-reply-restore-link">' . $r['restore_text'] . '</a>';
		} elseif ( EMPTY_TRASH_DAYS ) {
			$actions['trash']   = '<a title="' . esc_attr__( 'Dieses Element in den Papierkorb verschieben', 'psforum' ) . '" href="' . esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'psf_toggle_reply_trash', 'sub_action' => 'trash',   'reply_id' => $reply->ID ) ), 'trash-'   . $reply->post_type . '_' . $reply->ID ) ) . '" class="psf-reply-trash-link">'   . $r['trash_text']   . '</a>';
		}

		if ( psf_is_reply_trash( $reply->ID ) || !EMPTY_TRASH_DAYS ) {
			$actions['delete']  = '<a title="' . esc_attr__( 'Dieses Element endgültig löschen', 'psforum' ) . '" href="' . esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'psf_toggle_reply_trash', 'sub_action' => 'delete',  'reply_id' => $reply->ID ) ), 'delete-'  . $reply->post_type . '_' . $reply->ID ) ) . '" onclick="return confirm(\'' . esc_js( __( 'Möchtest Du das wirklich dauerhaft löschen?', 'psforum' ) ) . '\' );" class="psf-reply-delete-link">' . $r['delete_text'] . '</a>';
		}

		// Process the admin links
		$retval = $r['link_before'] . implode( $r['sep'], $actions ) . $r['link_after'];

		return apply_filters( 'psf_get_reply_trash_link', $retval, $r );
	}

/**
 * Output the spam link of the reply
 *
 * @since PSForum (r2740)
 *
 * @param mixed $args See {@link psf_get_reply_spam_link()}
 * @uses psf_get_reply_spam_link() To get the reply spam link
 */
function psf_reply_spam_link( $args = '' ) {
	echo psf_get_reply_spam_link( $args );
}

	/**
	 * Return the spam link of the reply
	 *
	 * @since PSForum (r2740)
	 *
	 * @param mixed $args This function supports these arguments:
	 *  - id: Reply id
	 *  - link_before: HTML before the link
	 *  - link_after: HTML after the link
	 *  - spam_text: Spam text
	 *  - unspam_text: Unspam text
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses psf_get_reply() To get the reply
	 * @uses current_user_can() To check if the current user can edit the
	 *                           reply
	 * @uses psf_is_reply_spam() To check if the reply is marked as spam
	 * @uses add_query_arg() To add custom args to the url
	 * @uses wp_nonce_url() To nonce the url
	 * @uses esc_url() To escape the url
	 * @uses psf_get_reply_edit_url() To get the reply edit url
	 * @uses apply_filters() Calls 'psf_get_reply_spam_link' with the reply
	 *                        spam link and args
	 * @return string Reply spam link
	 */
	function psf_get_reply_spam_link( $args = '' ) {

		// Parse arguments against default values
		$r = psf_parse_args( $args, array(
			'id'           => 0,
			'link_before'  => '',
			'link_after'   => '',
			'spam_text'    => esc_html__( 'Spam',   'psforum' ),
			'unspam_text'  => esc_html__( 'Kein Spam', 'psforum' )
		), 'get_reply_spam_link' );

		$reply = psf_get_reply( psf_get_reply_id( (int) $r['id'] ) );

		if ( empty( $reply ) || !current_user_can( 'moderate', $reply->ID ) )
			return;

		$display  = psf_is_reply_spam( $reply->ID ) ? $r['unspam_text'] : $r['spam_text'];
		$uri      = add_query_arg( array( 'action' => 'psf_toggle_reply_spam', 'reply_id' => $reply->ID ) );
		$uri      = wp_nonce_url( $uri, 'spam-reply_' . $reply->ID );
		$retval   = $r['link_before'] . '<a href="' . esc_url( $uri ) . '" class="psf-reply-spam-link">' . $display . '</a>' . $r['link_after'];

		return apply_filters( 'psf_get_reply_spam_link', $retval, $r );
	}

/**
 * Move reply link
 *
 * Output the move link of the reply
 *
 * @since PSForum (r4521)
 *
 * @param mixed $args See {@link psf_get_reply_move_link()}
 * @uses psf_get_reply_move_link() To get the reply move link
 */
function psf_reply_move_link( $args = '' ) {
	echo psf_get_reply_move_link( $args );
}

	/**
	 * Get move reply link
	 *
	 * Return the move link of the reply
	 *
	 * @since PSForum (r4521)
	 *
	 * @param mixed $args This function supports these arguments:
	 *  - id: Reply id
	 *  - link_before: HTML before the link
	 *  - link_after: HTML after the link
	 *  - move_text: Move text
	 *  - move_title: Move title attribute
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses psf_get_reply() To get the reply
	 * @uses current_user_can() To check if the current user can edit the
	 *                           topic
	 * @uses psf_get_reply_topic_id() To get the reply topic id
	 * @uses psf_get_reply_edit_url() To get the reply edit url
	 * @uses add_query_arg() To add custom args to the url
	 * @uses wp_nonce_url() To nonce the url
	 * @uses esc_url() To escape the url
	 * @uses apply_filters() Calls 'psf_get_reply_move_link' with the reply
	 *                        move link and args
	 * @return string Reply move link
	 */
	function psf_get_reply_move_link( $args = '' ) {

		// Parse arguments against default values
		$r = psf_parse_args( $args, array(
			'id'          => 0,
			'link_before' => '',
			'link_after'  => '',
			'split_text'  => esc_html__( 'Verschieben',            'psforum' ),
			'split_title' => esc_attr__( 'Diese Antwort verschieben', 'psforum' )
		), 'get_reply_move_link' );

		$reply_id = psf_get_reply_id( $r['id'] );
		$topic_id = psf_get_reply_topic_id( $reply_id );

		if ( empty( $reply_id ) || !current_user_can( 'moderate', $topic_id ) )
			return;

		$uri = add_query_arg( array(
			'action'   => 'move',
			'reply_id' => $reply_id
		), psf_get_reply_edit_url( $reply_id ) );

		$retval = $r['link_before'] . '<a href="' . esc_url( $uri ) . '" title="' . $r['split_title'] . '" class="psf-reply-move-link">' . $r['split_text'] . '</a>' . $r['link_after'];

		return apply_filters( 'psf_get_reply_move_link', $retval, $r );
	}

/**
 * Split topic link
 *
 * Output the split link of the topic (but is bundled with each reply)
 *
 * @since PSForum (r2756)
 *
 * @param mixed $args See {@link psf_get_topic_split_link()}
 * @uses psf_get_topic_split_link() To get the topic split link
 */
function psf_topic_split_link( $args = '' ) {
	echo psf_get_topic_split_link( $args );
}

	/**
	 * Get split topic link
	 *
	 * Return the split link of the topic (but is bundled with each reply)
	 *
	 * @since PSForum (r2756)
	 *
	 * @param mixed $args This function supports these arguments:
	 *  - id: Reply id
	 *  - link_before: HTML before the link
	 *  - link_after: HTML after the link
	 *  - split_text: Split text
	 *  - split_title: Split title attribute
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses psf_get_reply() To get the reply
	 * @uses current_user_can() To check if the current user can edit the
	 *                           topic
	 * @uses psf_get_reply_topic_id() To get the reply topic id
	 * @uses psf_get_topic_edit_url() To get the topic edit url
	 * @uses add_query_arg() To add custom args to the url
	 * @uses wp_nonce_url() To nonce the url
	 * @uses esc_url() To escape the url
	 * @uses apply_filters() Calls 'psf_get_topic_split_link' with the topic
	 *                        split link and args
	 * @return string Topic split link
	 */
	function psf_get_topic_split_link( $args = '' ) {

		// Parse arguments against default values
		$r = psf_parse_args( $args, array(
			'id'          => 0,
			'link_before' => '',
			'link_after'  => '',
			'split_text'  => esc_html__( 'Trennen', 'psforum' ),
			'split_title' => esc_attr__( 'Trenne das Thema von dieser Antwort', 'psforum' )
		), 'get_topic_split_link' );

		$reply_id = psf_get_reply_id( $r['id'] );
		$topic_id = psf_get_reply_topic_id( $reply_id );

		if ( empty( $reply_id ) || !current_user_can( 'moderate', $topic_id ) )
			return;

		$uri =  add_query_arg( array(
			'action'   => 'split',
			'reply_id' => $reply_id
		), psf_get_topic_edit_url( $topic_id ) );

		$retval = $r['link_before'] . '<a href="' . esc_url( $uri ) . '" title="' . $r['split_title'] . '" class="psf-topic-split-link">' . $r['split_text'] . '</a>' . $r['link_after'];

		return apply_filters( 'psf_get_topic_split_link', $retval, $r );
	}

/**
 * Output the row class of a reply
 *
 * @since PSForum (r2678)
 *
 * @param int $reply_id Optional. Reply ID
 * @param array Extra classes you can pass when calling this function
 * @uses psf_get_reply_class() To get the reply class
 */
function psf_reply_class( $reply_id = 0, $classes = array() ) {
	echo psf_get_reply_class( $reply_id, $classes );
}
	/**
	 * Return the row class of a reply
	 *
	 * @since PSForum (r2678)
	 *
	 * @param int $reply_id Optional. Reply ID
	 * @param array Extra classes you can pass when calling this function
	 * @uses psf_get_reply_id() To validate the reply id
	 * @uses psf_get_reply_forum_id() To get the reply's forum id
	 * @uses psf_get_reply_topic_id() To get the reply's topic id
	 * @uses get_post_class() To get all the classes including ours
	 * @uses apply_filters() Calls 'psf_get_reply_class' with the classes
	 * @return string Row class of the reply
	 */
	function psf_get_reply_class( $reply_id = 0, $classes = array() ) {
		$psf       = psforum();
		$reply_id  = psf_get_reply_id( $reply_id );
		$count     = isset( $psf->reply_query->current_post ) ? $psf->reply_query->current_post : 1;
		$classes   = (array) $classes;
		$classes[] = ( (int) $count % 2 ) ? 'even' : 'odd';
		$classes[] = 'psf-parent-forum-'   . psf_get_reply_forum_id( $reply_id );
		$classes[] = 'psf-parent-topic-'   . psf_get_reply_topic_id( $reply_id );
		$classes[] = 'psf-reply-position-' . psf_get_reply_position( $reply_id );
		$classes[] = 'user-id-' . psf_get_reply_author_id( $reply_id );
		$classes[] = ( psf_get_reply_author_id( $reply_id ) === psf_get_topic_author_id( psf_get_reply_topic_id( $reply_id ) ) ? 'topic-author' : '' );
		$classes   = array_filter( $classes );
		$classes   = get_post_class( $classes, $reply_id );
		$classes   = apply_filters( 'psf_get_reply_class', $classes, $reply_id );
		$retval    = 'class="' . implode( ' ', $classes ) . '"';

		return $retval;
	}

/**
 * Output the topic pagination count
 *
 * @since PSForum (r2519)
 *
 * @uses psf_get_topic_pagination_count() To get the topic pagination count
 */
function psf_topic_pagination_count() {
	echo psf_get_topic_pagination_count();
}
	/**
	 * Return the topic pagination count
	 *
	 * @since PSForum (r2519)
	 *
	 * @uses psf_number_format() To format the number value
	 * @uses psf_show_lead_topic() Are we showing the topic as a lead?
	 * @uses apply_filters() Calls 'psf_get_topic_pagination_count' with the
	 *                        pagination count
	 * @return string Topic pagination count
	 */
	function psf_get_topic_pagination_count() {
		$psf = psforum();

		// Define local variable(s)
		$retstr = '';

		// Set pagination values
		$start_num = intval( ( $psf->reply_query->paged - 1 ) * $psf->reply_query->posts_per_page ) + 1;
		$from_num  = psf_number_format( $start_num );
		$to_num    = psf_number_format( ( $start_num + ( $psf->reply_query->posts_per_page - 1 ) > $psf->reply_query->found_posts ) ? $psf->reply_query->found_posts : $start_num + ( $psf->reply_query->posts_per_page - 1 ) );
		$total_int = (int) $psf->reply_query->found_posts;
		$total     = psf_number_format( $total_int );

		// We are threading replies
		if ( psf_thread_replies() && psf_is_single_topic() ) {
			return;
			$walker  = new PSF_Walker_Reply;
			$threads = (int) $walker->get_number_of_root_elements( $psf->reply_query->posts );

			// Adjust for topic
			$threads--;
			$retstr  = sprintf( _n( '%1$s Antwort-Thread wird angezeigt', '%1$s Antwort-Threads werden angezeigt', $threads, 'bpsforum' ), psf_number_format( $threads ) );

		// We are not including the lead topic
		} elseif ( psf_show_lead_topic() ) {

			// Several replies in a topic with a single page
			if ( empty( $to_num ) ) {
				$retstr = sprintf( _n( '%1$s Antwort wird angezeigt', '%1$s Antworten werden angezeigt', $total_int, 'psforum' ), $total );

			// Several replies in a topic with several pages
			} else {
				$retstr = sprintf( _n( '%2$s Antworten werden angezeigt (von %4$s insgesamt)', '%1$s Antworten werden angezeigt - %2$s bis %3$s (von %4$s insgesamt)', $psf->reply_query->post_count, 'psforum' ), $psf->reply_query->post_count, $from_num, $to_num, $total );
			}

		// We are including the lead topic
		} else {

			// Several posts in a topic with a single page
			if ( empty( $to_num ) ) {
				$retstr = sprintf( _n( '%1$s Beitrag ansehen', '%1$s Beiträge ansehen', $total_int, 'psforum' ), $total );

			// Several posts in a topic with several pages
			} else {
				$retstr = sprintf( _n( 'Anzeige von %2$s Beitrag (von %4$s insgesamt)', '%1$s Beiträge ansehen - %2$s bis %3$s (von %4$s insgesamt)', $psf->reply_query->post_count, 'psforum' ), $psf->reply_query->post_count, $from_num, $to_num, $total );
			}
		}

		// Filter and return
		return apply_filters( 'psf_get_topic_pagination_count', esc_html( $retstr ) );
	}

/**
 * Output topic pagination links
 *
 * @since PSForum (r2519)
 *
 * @uses psf_get_topic_pagination_links() To get the topic pagination links
 */
function psf_topic_pagination_links() {
	echo psf_get_topic_pagination_links();
}
	/**
	 * Return topic pagination links
	 *
	 * @since PSForum (r2519)
	 *
	 * @uses apply_filters() Calls 'psf_get_topic_pagination_links' with the
	 *                        pagination links
	 * @return string Topic pagination links
	 */
	function psf_get_topic_pagination_links() {
		$psf = psforum();

		if ( !isset( $psf->reply_query->pagination_links ) || empty( $psf->reply_query->pagination_links ) )
			return false;

		return apply_filters( 'psf_get_topic_pagination_links', $psf->reply_query->pagination_links );
	}

/** Forms *********************************************************************/

/**
 * Output the value of reply content field
 *
 * @since PSForum (r31301)
 *
 * @uses psf_get_form_reply_content() To get value of reply content field
 */
function psf_form_reply_content() {
	echo psf_get_form_reply_content();
}
	/**
	 * Return the value of reply content field
	 *
	 * @since PSForum (r31301)
	 *
	 * @uses psf_is_reply_edit() To check if it's the reply edit page
	 * @uses apply_filters() Calls 'psf_get_form_reply_content' with the content
	 * @return string Value of reply content field
	 */
	function psf_get_form_reply_content() {

		// Get _POST data
		if ( psf_is_post_request() && isset( $_POST['psf_reply_content'] ) ) {
			$reply_content = stripslashes( $_POST['psf_reply_content'] );

		// Get edit data
		} elseif ( psf_is_reply_edit() ) {
			$reply_content = psf_get_global_post_field( 'post_content', 'raw' );

		// No data
		} else {
			$reply_content = '';
		}

		return apply_filters( 'psf_get_form_reply_content', $reply_content );
	}

/**
 * Output the value of the reply to field
 *
 * @since PSForum (r4944)
 *
 * @uses psf_get_form_reply_to() To get value of the reply to field
 */
function psf_form_reply_to() {
	echo psf_get_form_reply_to();
}

	/**
	 * Return the value of reply to field
	 *
	 * @since PSForum (r4944)
	 *
	 * @uses psf_get_reply_id() To validate the reply to
	 * @uses apply_filters() Calls 'psf_get_form_reply_to' with the reply to
	 * @return string Value of reply to field
	 */
	function psf_get_form_reply_to() {

		// Set initial value
		$reply_to = 0;

		// Get $_REQUEST data
		if ( isset( $_REQUEST['psf_reply_to'] ) ) {
			$reply_to = psf_validate_reply_to( $_REQUEST['psf_reply_to'] );
		}

		// If empty, get from meta
		if ( empty( $reply_to ) ) {
			$reply_to = psf_get_reply_to();
		}

		return (int) apply_filters( 'psf_get_form_reply_to', $reply_to );
	}

/**
 * Output checked value of reply log edit field
 *
 * @since PSForum (r31301)
 *
 * @uses psf_get_form_reply_log_edit() To get the reply log edit value
 */
function psf_form_reply_log_edit() {
	echo psf_get_form_reply_log_edit();
}
	/**
	 * Return checked value of reply log edit field
	 *
	 * @since PSForum (r31301)
	 *
	 * @uses apply_filters() Calls 'psf_get_form_reply_log_edit' with the
	 *                        log edit value
	 * @return string Reply log edit checked value
	 */
	function psf_get_form_reply_log_edit() {

		// Get _POST data
		if ( psf_is_post_request() && isset( $_POST['psf_log_reply_edit'] ) ) {
			$reply_revision = $_POST['psf_log_reply_edit'];

		// No data
		} else {
			$reply_revision = 1;
		}

		return apply_filters( 'psf_get_form_reply_log_edit', checked( $reply_revision, true, false ) );
	}

/**
 * Output the value of the reply edit reason
 *
 * @since PSForum (r31301)
 *
 * @uses psf_get_form_reply_edit_reason() To get the reply edit reason value
 */
function psf_form_reply_edit_reason() {
	echo psf_get_form_reply_edit_reason();
}
	/**
	 * Return the value of the reply edit reason
	 *
	 * @since PSForum (r31301)
	 *
	 * @uses apply_filters() Calls 'psf_get_form_reply_edit_reason' with the
	 *                        reply edit reason value
	 * @return string Reply edit reason value
	 */
	function psf_get_form_reply_edit_reason() {

		// Get _POST data
		if ( psf_is_post_request() && isset( $_POST['psf_reply_edit_reason'] ) ) {
			$reply_edit_reason = $_POST['psf_reply_edit_reason'];

		// No data
		} else {
			$reply_edit_reason = '';
		}

		return apply_filters( 'psf_get_form_reply_edit_reason', esc_attr( $reply_edit_reason ) );
	}
