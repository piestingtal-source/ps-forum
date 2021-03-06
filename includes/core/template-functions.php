<?php

/**
 * PSForum Template Functions
 *
 * This file contains functions necessary to mirror the WordPress core template
 * loading process. Many of those functions are not filterable, and even then
 * would not be robust enough to predict where PSForum templates might exist.
 *
 * @package PSForum
 * @subpackage TemplateFunctions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Adds PSForum theme support to any active WordPress theme
 *
 * @since PSForum (r3032)
 *
 * @param string $slug
 * @param string $name Optional. Default null
 * @uses psf_locate_template()
 * @uses load_template()
 * @uses get_template_part()
 */
function psf_get_template_part( $slug, $name = null ) {

	// Execute code for this part
	do_action( 'get_template_part_' . $slug, $slug, $name );

	// Setup possible parts
	$templates = array();
	if ( isset( $name ) )
		$templates[] = $slug . '-' . $name . '.php';
	$templates[] = $slug . '.php';

	// Allow template parst to be filtered
	$templates = apply_filters( 'psf_get_template_part', $templates, $slug, $name );

	// Return the part that is found
	return psf_locate_template( $templates, true, false );
}

/**
 * Retrieve the name of the highest priority template file that exists.
 *
 * Searches in the child theme before parent theme so that themes which
 * inherit from a parent theme can just overload one file. If the template is
 * not found in either of those, it looks in the theme-compat folder last.
 *
 * @since PSForum (r3618)
 *
 * @param string|array $template_names Template file(s) to search for, in order.
 * @param bool $load If true the template file will be loaded if it is found.
 * @param bool $require_once Whether to require_once or require. Default true.
 *                            Has no effect if $load is false.
 * @return string The template filename if one is located.
 */
function psf_locate_template( $template_names, $load = false, $require_once = true ) {

	// No file found yet
	$located            = false;
	$template_locations = psf_get_template_stack();

	// Try to find a template file
	foreach ( (array) $template_names as $template_name ) {

		// Continue if template is empty
		if ( empty( $template_name ) ) {
			continue;
		}

		// Trim off any slashes from the template name
		$template_name  = ltrim( $template_name, '/' );

		// Loop through template stack
		foreach ( (array) $template_locations as $template_location ) {

			// Continue if $template_location is empty
			if ( empty( $template_location ) ) {
				continue;
			}

			// Check child theme first
			if ( file_exists( trailingslashit( $template_location ) . $template_name ) ) {
				$located = trailingslashit( $template_location ) . $template_name;
				break 2;
			}
		}
	}

	/**
	 * This action exists only to follow the standard PSForum coding convention,
	 * and should not be used to short-circuit any part of the template locator.
	 *
	 * If you want to override a specific template part, please either filter
	 * 'psf_get_template_part' or add a new location to the template stack.
	 */
	do_action( 'psf_locate_template', $located, $template_name, $template_names, $template_locations, $load, $require_once );

	// Maybe load the template if one was located
	if ( ( true === $load ) && !empty( $located ) ) {
		load_template( $located, $require_once );
	}

	return $located;
}

/**
 * Enqueue a script from the highest priority location in the template stack.
 *
 * Registers the style if file provided (does NOT overwrite) and enqueues.
 *
 * @since PSForum (r5180)
 *
 * @param string      $handle Name of the stylesheet.
 * @param string|bool $file   Relative path to stylesheet. Example: '/css/mystyle.css'.
 * @param array       $deps   An array of registered style handles this stylesheet depends on. Default empty array.
 * @param string|bool $ver    String specifying the stylesheet version number, if it has one. This parameter is used
 *                            to ensure that the correct version is sent to the client regardless of caching, and so
 *                            should be included if a version number is available and makes sense for the stylesheet.
 * @param string      $media  Optional. The media for which this stylesheet has been defined.
 *                            Default 'all'. Accepts 'all', 'aural', 'braille', 'handheld', 'projection', 'print',
 *                            'screen', 'tty', or 'tv'.
 *
 * @return string The style filename if one is located.
 */
function psf_enqueue_style( $handle = '', $file = '', $dependencies = array(), $version = false, $media = 'all' ) {

	// No file found yet
	$located = false;

	// Trim off any slashes from the template name
	$file = ltrim( $file, '/' );

	// Make sure there is always a version
	if ( empty( $version ) ) {
		$version = psf_get_version();
	}

	// Loop through template stack
	foreach ( (array) psf_get_template_stack() as $template_location ) {

		// Continue if $template_location is empty
		if ( empty( $template_location ) ) {
			continue;
		}

		// Check child theme first
		if ( file_exists( trailingslashit( $template_location ) . $file ) ) {
			$located = trailingslashit( $template_location ) . $file;
			break;
		}
	}

	// Enqueue if located
	if ( !empty( $located ) ) {

		$content_dir = constant( 'WP_CONTENT_DIR' );

		// IIS (Windows) here
		// Replace back slashes with forward slash
		if ( strpos( $located, '\\' ) !== false ) {
			$located     = str_replace( '\\', '/', $located     );
			$content_dir = str_replace( '\\', '/', $content_dir );
		}

 		// Make path to file relative to site URL
		$located = str_replace( $content_dir, content_url(), $located );

		// Enqueue the style
		wp_enqueue_style( $handle, $located, $dependencies, $version, $media );
	}

	return $located;
}

/**
 * Enqueue a script from the highest priority location in the template stack.
 *
 * Registers the style if file provided (does NOT overwrite) and enqueues.
 *
 * @since PSForum (r5180)
 *
 * @param string      $handle    Name of the script.
 * @param string|bool $file      Relative path to the script. Example: '/js/myscript.js'.
 * @param array       $deps      An array of registered handles this script depends on. Default empty array.
 * @param string|bool $ver       Optional. String specifying the script version number, if it has one. This parameter
 *                               is used to ensure that the correct version is sent to the client regardless of caching,
 *                               and so should be included if a version number is available and makes sense for the script.
 * @param bool        $in_footer Optional. Whether to enqueue the script before </head> or before </body>.
 *                               Default 'false'. Accepts 'false' or 'true'.
 *
 * @return string The script filename if one is located.
 */
function psf_enqueue_script( $handle = '', $file = '', $dependencies = array(), $version = false, $in_footer = 'all' ) {

	// No file found yet
	$located = false;

	// Trim off any slashes from the template name
	$file = ltrim( $file, '/' );

	// Make sure there is always a version
	if ( empty( $version ) ) {
		$version = psf_get_version();
	}

	// Loop through template stack
	foreach ( (array) psf_get_template_stack() as $template_location ) {

		// Continue if $template_location is empty
		if ( empty( $template_location ) ) {
			continue;
		}

		// Check child theme first
		if ( file_exists( trailingslashit( $template_location ) . $file ) ) {
			$located = trailingslashit( $template_location ) . $file;
			break;
		}
	}

	// Enqueue if located
	if ( !empty( $located ) ) {

		$content_dir = constant( 'WP_CONTENT_DIR' );

		// IIS (Windows) here
		// Replace back slashes with forward slash
		if ( strpos( $located, '\\' ) !== false ) {
			$located     = str_replace( '\\', '/', $located     );
			$content_dir = str_replace( '\\', '/', $content_dir );
		}

 		// Make path to file relative to site URL
		$located = str_replace( $content_dir, content_url(), $located );

		// Enqueue the style
		wp_enqueue_script( $handle, $located, $dependencies, $version, $in_footer );
	}

	return $located;
}

/**
 * This is really cool. This function registers a new template stack location,
 * using WordPress's built in filters API.
 *
 * This allows for templates to live in places beyond just the parent/child
 * relationship, to allow for custom template locations. Used in conjunction
 * with psf_locate_template(), this allows for easy template overrides.
 *
 * @since PSForum (r4323)
 *
 * @param string $location Callback function that returns the
 * @param int $priority
 */
function psf_register_template_stack( $location_callback = '', $priority = 10 ) {

	// Bail if no location, or function does not exist
	if ( empty( $location_callback ) || ! function_exists( $location_callback ) )
		return false;

	// Add location callback to template stack
	return add_filter( 'psf_template_stack', $location_callback, (int) $priority );
}

/**
 * Deregisters a previously registered template stack location.
 *
 * @since PSForum (r4652)
 *
 * @param string $location Callback function that returns the
 * @param int $priority
 * @see psf_register_template_stack()
 */
function psf_deregister_template_stack( $location_callback = '', $priority = 10 ) {

	// Bail if no location, or function does not exist
	if ( empty( $location_callback ) || ! function_exists( $location_callback ) )
		return false;

	// Remove location callback to template stack
	return remove_filter( 'psf_template_stack', $location_callback, (int) $priority );
}

/**
 * Call the functions added to the 'psf_template_stack' filter hook, and return
 * an array of the template locations.
 *
 * @see psf_register_template_stack()
 *
 * @since PSForum (r4323)
 *
 * @global array $wp_filter Stores all of the filters
 * @global array $merged_filters Merges the filter hooks using this function.
 * @global array $wp_current_filter stores the list of current filters with the current one last
 *
 * @return array The filtered value after all hooked functions are applied to it.
 */
function psf_get_template_stack() {
	global $wp_filter, $merged_filters, $wp_current_filter;

	// Setup some default variables
	$tag  = 'psf_template_stack';
	$args = $stack = array();

	// Add 'psf_template_stack' to the current filter array.
	$wp_current_filter[] = $tag;

	// Sort.
	if ( class_exists( 'WP_Hook' ) ) {
		$filter = $wp_filter[ $tag ]->callbacks;
	} else {
		$filter = &$wp_filter[ $tag ];

		if ( ! isset( $merged_filters[ $tag ] ) ) {
			ksort( $filter );
			$merged_filters[ $tag ] = true;
		}
	}

	// Ensure we're always at the beginning of the filter array.
	reset( $filter );

	// Loop through 'psf_template_stack' filters, and call callback functions.
	do {
		foreach( (array) current( $filter ) as $the_ ) {
			if ( ! is_null( $the_['function'] ) ) {
				$args[1] = $stack;
				$stack[] = call_user_func_array( $the_['function'], array_slice( $args, 1, (int) $the_['accepted_args'] ) );
			}
		}
	} while ( next( $filter ) !== false );

	// Remove 'psf_template_stack' from the current filter array.
	array_pop( $wp_current_filter );

	// Remove empties and duplicates.
	$stack = array_unique( array_filter( $stack ) );

	return (array) apply_filters( 'psf_get_template_stack', $stack ) ;
}

/**
 * Get a template part in an output buffer, and return it
 *
 * @since PSForum (r5043)
 *
 * @param string $slug
 * @param string $name
 * @return string
 */
function psf_buffer_template_part( $slug, $name = null, $echo = true ) {
	ob_start();

	psf_get_template_part( $slug, $name );

	// Get the output buffer contents
	$output = ob_get_clean();

	// Echo or return the output buffer contents
	if ( true === $echo ) {
		echo $output;
	} else {
		return $output;
	}
}

/**
 * Retrieve path to a template
 *
 * Used to quickly retrieve the path of a template without including the file
 * extension. It will also check the parent theme and theme-compat theme with
 * the use of {@link psf_locate_template()}. Allows for more generic template
 * locations without the use of the other get_*_template() functions.
 *
 * @since PSForum (r3629)
 *
 * @param string $type Filename without extension.
 * @param array $templates An optional list of template candidates
 * @uses psf_set_theme_compat_templates()
 * @uses psf_locate_template()
 * @uses psf_set_theme_compat_template()
 * @return string Full path to file.
 */
function psf_get_query_template( $type, $templates = array() ) {
	$type = preg_replace( '|[^a-z0-9-]+|', '', $type );

	if ( empty( $templates ) )
		$templates = array( "{$type}.php" );

	// Filter possible templates, try to match one, and set any PSForum theme
	// compat properties so they can be cross-checked later.
	$templates = apply_filters( "psf_get_{$type}_template", $templates );
	$templates = psf_set_theme_compat_templates( $templates );
	$template  = psf_locate_template( $templates );
	$template  = psf_set_theme_compat_template( $template );

	return apply_filters( "psf_{$type}_template", $template );
}

/**
 * Get the possible subdirectories to check for templates in
 *
 * @since PSForum (r3738)
 * @param array $templates Templates we are looking for
 * @return array Possible subfolders to look in
 */
function psf_get_template_locations( $templates = array() ) {
	$locations = array(
		'psforum',
		'forums',
		''
	);
	return apply_filters( 'psf_get_template_locations', $locations, $templates );
}

/**
 * Add template locations to template files being searched for
 *
 * @since PSForum (r3738)
 *
 * @param array $templates
 * @return array()
 */
function psf_add_template_stack_locations( $stacks = array() ) {
	$retval = array();

	// Get alternate locations
	$locations = psf_get_template_locations();

	// Loop through locations and stacks and combine
	foreach ( (array) $stacks as $stack )
		foreach ( (array) $locations as $custom_location )
			$retval[] = untrailingslashit( trailingslashit( $stack ) . $custom_location );

	return apply_filters( 'psf_add_template_stack_locations', array_unique( $retval ), $stacks );
}

/**
 * Add checks for PSForum conditions to parse_query action
 *
 * If it's a user page, WP_Query::psf_is_single_user is set to true.
 * If it's a user edit page, WP_Query::psf_is_single_user_edit is set to true
 * and the the 'wp-admin/includes/user.php' file is included.
 * In addition, on user/user edit pages, WP_Query::home is set to false & query
 * vars 'psf_user_id' with the displayed user id and 'author_name' with the
 * displayed user's nicename are added.
 *
 * If it's a forum edit, WP_Query::psf_is_forum_edit is set to true
 * If it's a topic edit, WP_Query::psf_is_topic_edit is set to true
 * If it's a reply edit, WP_Query::psf_is_reply_edit is set to true.
 *
 * If it's a view page, WP_Query::psf_is_view is set to true
 * If it's a search page, WP_Query::psf_is_search is set to true
 *
 * @since PSForum (r2688)
 *
 * @param WP_Query $posts_query
 *
 * @uses get_query_var() To get {@link WP_Query} query var
 * @uses get_user_by() To try to get the user by id or nicename
 * @uses get_userdata() to get the user data
 * @uses current_user_can() To check if the current user can edit the user
 * @uses is_user_member_of_blog() To check if user profile page exists
 * @uses WP_Query::set_404() To set a 404 status
 * @uses apply_filters() Calls 'enable_edit_any_user_configuration' with true
 * @uses psf_get_view_query_args() To get the view query args
 * @uses psf_get_forum_post_type() To get the forum post type
 * @uses psf_get_topic_post_type() To get the topic post type
 * @uses psf_get_reply_post_type() To get the reply post type
 * @uses remove_action() To remove the auto save post revision action
 */
function psf_parse_query( $posts_query ) {

	// Bail if $posts_query is not the main loop
	if ( ! $posts_query->is_main_query() )
		return;

	// Bail if filters are suppressed on this query
	if ( true === $posts_query->get( 'suppress_filters' ) )
		return;

	// Bail if in admin
	if ( is_admin() )
		return;

	// Get query variables
	$psf_view = $posts_query->get( psf_get_view_rewrite_id() );
	$psf_user = $posts_query->get( psf_get_user_rewrite_id() );
	$is_edit  = $posts_query->get( psf_get_edit_rewrite_id() );

	// It is a user page - We'll also check if it is user edit
	if ( !empty( $psf_user ) ) {

		/** Find User *********************************************************/

		// Setup the default user variable
		$the_user = false;

		// If using pretty permalinks, always use slug
		if ( get_option( 'permalink_structure' ) ) {
			$the_user = get_user_by( 'slug', $psf_user );

		// If not using pretty permalinks, always use numeric ID
		} elseif ( is_numeric( $psf_user ) ) {
			$the_user = get_user_by( 'id', $psf_user );
		}

		// 404 and bail if user does not have a profile
		if ( empty( $the_user->ID ) || ! psf_user_has_profile( $the_user->ID ) ) {
			$posts_query->set_404();
			return;
		}

		/** User Exists *******************************************************/

		$is_favs    = $posts_query->get( psf_get_user_favorites_rewrite_id()     );
		$is_subs    = $posts_query->get( psf_get_user_subscriptions_rewrite_id() );
		$is_topics  = $posts_query->get( psf_get_user_topics_rewrite_id()        );
		$is_replies = $posts_query->get( psf_get_user_replies_rewrite_id()       );

		// View or edit?
		if ( !empty( $is_edit ) ) {

			// We are editing a profile
			$posts_query->psf_is_single_user_edit = true;

			// Load the core WordPress contact methods
			if ( !function_exists( '_wp_get_user_contactmethods' ) ) {
				include_once( ABSPATH . 'wp-includes/registration.php' );
			}

			// Load the edit_user functions
			if ( !function_exists( 'edit_user' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/user.php' );
			}

			// Load the grant/revoke super admin functions
			if ( is_multisite() && !function_exists( 'revoke_super_admin' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/ms.php' );
			}

			// Editing a user
			$posts_query->psf_is_edit = true;

		// User favorites
		} elseif ( ! empty( $is_favs ) ) {
			$posts_query->psf_is_single_user_favs = true;

		// User subscriptions
		} elseif ( ! empty( $is_subs ) ) {
			$posts_query->psf_is_single_user_subs = true;

		// User topics
		} elseif ( ! empty( $is_topics ) ) {
			$posts_query->psf_is_single_user_topics = true;

		// User topics
		} elseif ( ! empty( $is_replies ) ) {
			$posts_query->psf_is_single_user_replies = true;

		// User profile
		} else {
			$posts_query->psf_is_single_user_profile = true;
		}

		// Looking at a single user
		$posts_query->psf_is_single_user = true;

		// Make sure 404 is not set
		$posts_query->is_404  = false;

		// Correct is_home variable
		$posts_query->is_home = false;

		// User is looking at their own profile
		if ( get_current_user_id() === $the_user->ID ) {
			$posts_query->psf_is_single_user_home = true;
		}

		// Set psf_user_id for future reference
		$posts_query->set( 'psf_user_id', $the_user->ID );

		// Set author_name as current user's nicename to get correct posts
		$posts_query->set( 'author_name', $the_user->user_nicename );

		// Set the displayed user global to this user
		psforum()->displayed_user = $the_user;

	// View Page
	} elseif ( !empty( $psf_view ) ) {

		// Check if the view exists by checking if there are query args are set
		$view_args = psf_get_view_query_args( $psf_view );

		// Bail if view args is false (view isn't registered)
		if ( false === $view_args ) {
			$posts_query->set_404();
			return;
		}

		// Correct is_home variable
		$posts_query->is_home     = false;

		// We are in a custom topic view
		$posts_query->psf_is_view = true;

	// Search Page
	} elseif ( isset( $posts_query->query_vars[ psf_get_search_rewrite_id() ] ) ) {

		// Check if there are search query args set
		$search_terms = psf_get_search_terms();
		if ( !empty( $search_terms ) )
			$posts_query->psf_search_terms = $search_terms;

		// Correct is_home variable
		$posts_query->is_home = false;

		// We are in a search query
		$posts_query->psf_is_search = true;

	// Forum/Topic/Reply Edit Page
	} elseif ( !empty( $is_edit ) ) {

		// Get the post type from the main query loop
		$post_type = $posts_query->get( 'post_type' );

		// Check which post_type we are editing, if any
		if ( !empty( $post_type ) ) {
			switch( $post_type ) {

				// We are editing a forum
				case psf_get_forum_post_type() :
					$posts_query->psf_is_forum_edit = true;
					$posts_query->psf_is_edit       = true;
					break;

				// We are editing a topic
				case psf_get_topic_post_type() :
					$posts_query->psf_is_topic_edit = true;
					$posts_query->psf_is_edit       = true;
					break;

				// We are editing a reply
				case psf_get_reply_post_type() :
					$posts_query->psf_is_reply_edit = true;
					$posts_query->psf_is_edit       = true;
					break;
			}

		// We are editing a topic tag
		} elseif ( psf_is_topic_tag() ) {
			$posts_query->psf_is_topic_tag_edit = true;
			$posts_query->psf_is_edit           = true;
		}

		// We save post revisions on our own
		remove_action( 'pre_post_update', 'wp_save_post_revision' );

	// Topic tag page
	} elseif ( psf_is_topic_tag() ) {
		$posts_query->set( 'psf_topic_tag',  get_query_var( 'term' )   );
		$posts_query->set( 'post_type',      psf_get_topic_post_type() );
		$posts_query->set( 'posts_per_page', psf_get_topics_per_page() );

	// Do topics on forums root
	} elseif ( is_post_type_archive( array( psf_get_forum_post_type(), psf_get_topic_post_type() ) ) && ( 'topics' === psf_show_on_root() ) ) {
		$posts_query->psf_show_topics_on_root = true;
	}
}
