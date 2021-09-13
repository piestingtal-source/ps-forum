<?php

/**
 * PSForum Filters
 *
 * @package PSForum
 * @subpackage Core
 *
 * This file contains the filters that are used through-out PSForum. They are
 * consolidated here to make searching for them easier, and to help developers
 * understand at a glance the order in which things occur.
 *
 * There are a few common places that additional filters can currently be found
 *
 *  - PSForum: In {@link PSForum::setup_actions()} in psforum.php
 *  - Admin: More in {@link PSF_Admin::setup_actions()} in admin.php
 *
 * @see /core/actions.php
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
add_filter( 'request',                 'psf_request',            10    );
add_filter( 'template_include',        'psf_template_include',   10    );
add_filter( 'wp_title',                'psf_title',              10, 3 );
add_filter( 'body_class',              'psf_body_class',         10, 2 );
add_filter( 'map_meta_cap',            'psf_map_meta_caps',      10, 4 );
add_filter( 'allowed_themes',          'psf_allowed_themes',     10    );
add_filter( 'redirect_canonical',      'psf_redirect_canonical', 10    );
add_filter( 'login_redirect',          'psf_redirect_login',     2,  3 );
add_filter( 'logout_url',              'psf_logout_url',         2,  2 );
add_filter( 'plugin_locale',           'psf_plugin_locale',      10, 2 );

// Fix post author id for anonymous posts (set it back to 0) when the post status is changed
add_filter( 'wp_insert_post_data', 'psf_fix_post_author', 30, 2 );

// Force comments_status on PSForum post types
add_filter( 'comments_open', 'psf_force_comment_status' );

// Remove forums roles from list of all roles
add_filter( 'editable_roles', 'psf_filter_blog_editable_roles' );

// Reply title fallback
add_filter( 'the_title', 'psf_get_reply_title_fallback', 2, 2 );

/**
 * Feeds
 *
 * PSForum comes with a number of custom RSS2 feeds that get handled outside
 * the normal scope of feeds that WordPress would normally serve. To do this,
 * we filter every page request, listen for a feed request, and trap it.
 */
add_filter( 'psf_request', 'psf_request_feed_trap' );

/**
 * Template Compatibility
 *
 * If you want to completely bypass this and manage your own custom PSForum
 * template hierarchy, start here by removing this filter, then look at how
 * psf_template_include() works and do something similar. :)
 */
add_filter( 'psf_template_include',   'psf_template_include_theme_supports', 2, 1 );
add_filter( 'psf_template_include',   'psf_template_include_theme_compat',   4, 2 );

// Filter PSForum template locations
add_filter( 'psf_get_template_stack', 'psf_add_template_stack_locations'          );

// Links
add_filter( 'paginate_links',          'psf_add_view_all' );
add_filter( 'psf_get_topic_permalink', 'psf_add_view_all' );
add_filter( 'psf_get_reply_permalink', 'psf_add_view_all' );
add_filter( 'psf_get_forum_permalink', 'psf_add_view_all' );

// wp_filter_kses on new/edit topic/reply title
add_filter( 'psf_new_reply_pre_title',  'wp_filter_kses'  );
add_filter( 'psf_new_topic_pre_title',  'wp_filter_kses'  );
add_filter( 'psf_edit_reply_pre_title', 'wp_filter_kses'  );
add_filter( 'psf_edit_topic_pre_title', 'wp_filter_kses'  );

// Prevent posting malicious or malformed content on new/edit topic/reply
add_filter( 'psf_new_reply_pre_content',  'psf_encode_bad',  10 );
add_filter( 'psf_new_reply_pre_content',  'psf_code_trick',  20 );
add_filter( 'psf_new_reply_pre_content',  'psf_filter_kses', 30 );
add_filter( 'psf_new_reply_pre_content',  'balanceTags',     40 );
add_filter( 'psf_new_topic_pre_content',  'psf_encode_bad',  10 );
add_filter( 'psf_new_topic_pre_content',  'psf_code_trick',  20 );
add_filter( 'psf_new_topic_pre_content',  'psf_filter_kses', 30 );
add_filter( 'psf_new_topic_pre_content',  'balanceTags',     40 );
add_filter( 'psf_new_forum_pre_content',  'psf_encode_bad',  10 );
add_filter( 'psf_new_forum_pre_content',  'psf_code_trick',  20 );
add_filter( 'psf_new_forum_pre_content',  'psf_filter_kses', 30 );
add_filter( 'psf_new_forum_pre_content',  'balanceTags',     40 );
add_filter( 'psf_edit_reply_pre_content', 'psf_encode_bad',  10 );
add_filter( 'psf_edit_reply_pre_content', 'psf_code_trick',  20 );
add_filter( 'psf_edit_reply_pre_content', 'psf_filter_kses', 30 );
add_filter( 'psf_edit_reply_pre_content', 'balanceTags',     40 );
add_filter( 'psf_edit_topic_pre_content', 'psf_encode_bad',  10 );
add_filter( 'psf_edit_topic_pre_content', 'psf_code_trick',  20 );
add_filter( 'psf_edit_topic_pre_content', 'psf_filter_kses', 30 );
add_filter( 'psf_edit_topic_pre_content', 'balanceTags',     40 );
add_filter( 'psf_edit_forum_pre_content', 'psf_encode_bad',  10 );
add_filter( 'psf_edit_forum_pre_content', 'psf_code_trick',  20 );
add_filter( 'psf_edit_forum_pre_content', 'psf_filter_kses', 30 );
add_filter( 'psf_edit_forum_pre_content', 'balanceTags',     40 );

// No follow and stripslashes on user profile links
add_filter( 'psf_get_reply_author_link',      'psf_rel_nofollow' );
add_filter( 'psf_get_reply_author_link',      'stripslashes'     );
add_filter( 'psf_get_topic_author_link',      'psf_rel_nofollow' );
add_filter( 'psf_get_topic_author_link',      'stripslashes'     );
add_filter( 'psf_get_user_favorites_link',    'psf_rel_nofollow' );
add_filter( 'psf_get_user_favorites_link',    'stripslashes'     );
add_filter( 'psf_get_user_subscribe_link',    'psf_rel_nofollow' );
add_filter( 'psf_get_user_subscribe_link',    'stripslashes'     );
add_filter( 'psf_get_user_profile_link',      'psf_rel_nofollow' );
add_filter( 'psf_get_user_profile_link',      'stripslashes'     );
add_filter( 'psf_get_user_profile_edit_link', 'psf_rel_nofollow' );
add_filter( 'psf_get_user_profile_edit_link', 'stripslashes'     );

// Run filters on reply content
add_filter( 'psf_get_reply_content', 'psf_make_clickable', 4    );
add_filter( 'psf_get_reply_content', 'wptexturize',        6    );
add_filter( 'psf_get_reply_content', 'convert_chars',      8    );
add_filter( 'psf_get_reply_content', 'capital_P_dangit',   10   );
add_filter( 'psf_get_reply_content', 'convert_smilies',    20   );
add_filter( 'psf_get_reply_content', 'force_balance_tags', 30   );
add_filter( 'psf_get_reply_content', 'wpautop',            40   );
add_filter( 'psf_get_reply_content', 'psf_rel_nofollow',   50   );

// Run filters on topic content
add_filter( 'psf_get_topic_content', 'psf_make_clickable', 4    );
add_filter( 'psf_get_topic_content', 'wptexturize',        6    );
add_filter( 'psf_get_topic_content', 'convert_chars',      8    );
add_filter( 'psf_get_topic_content', 'capital_P_dangit',   10   );
add_filter( 'psf_get_topic_content', 'convert_smilies',    20   );
add_filter( 'psf_get_topic_content', 'force_balance_tags', 30   );
add_filter( 'psf_get_topic_content', 'wpautop',            40   );
add_filter( 'psf_get_topic_content', 'psf_rel_nofollow',   50   );

// Form textarea output - undo the code-trick done pre-save, and sanitize
add_filter( 'psf_get_form_forum_content', 'psf_code_trick_reverse' );
add_filter( 'psf_get_form_forum_content', 'esc_textarea'           );
add_filter( 'psf_get_form_forum_content', 'trim'                   );
add_filter( 'psf_get_form_topic_content', 'psf_code_trick_reverse' );
add_filter( 'psf_get_form_topic_content', 'esc_textarea'           );
add_filter( 'psf_get_form_topic_content', 'trim'                   );
add_filter( 'psf_get_form_reply_content', 'psf_code_trick_reverse' );
add_filter( 'psf_get_form_reply_content', 'esc_textarea'           );
add_filter( 'psf_get_form_reply_content', 'trim'                   );

// Add number format filter to functions requiring numeric output
add_filter( 'psf_get_user_topic_count',     'psf_number_format', 10 );
add_filter( 'psf_get_user_reply_count',     'psf_number_format', 10 );
add_filter( 'psf_get_user_post_count',      'psf_number_format', 10 );
add_filter( 'psf_get_forum_subforum_count', 'psf_number_format', 10 );
add_filter( 'psf_get_forum_topic_count',    'psf_number_format', 10 );
add_filter( 'psf_get_forum_reply_count',    'psf_number_format', 10 );
add_filter( 'psf_get_forum_post_count',     'psf_number_format', 10 );
add_filter( 'psf_get_topic_voice_count',    'psf_number_format', 10 );
add_filter( 'psf_get_topic_reply_count',    'psf_number_format', 10 );
add_filter( 'psf_get_topic_post_count',     'psf_number_format', 10 );

// Sanitize displayed user data
add_filter( 'psf_get_displayed_user_field', 'psf_sanitize_displayed_user_field', 10, 3 );

// Run wp_kses_data on topic/reply content in admin section
if ( is_admin() ) {
	add_filter( 'psf_get_reply_content', 'psf_kses_data' );
	add_filter( 'psf_get_topic_content', 'psf_kses_data' );

// Revisions (only when not in admin)
} else {
	add_filter( 'psf_get_reply_content', 'psf_reply_content_append_revisions',  99,  2 );
	add_filter( 'psf_get_topic_content', 'psf_topic_content_append_revisions',  99,  2 );
}

// Suppress private forum details
add_filter( 'psf_get_forum_topic_count',    'psf_suppress_private_forum_meta',  10, 2 );
add_filter( 'psf_get_forum_reply_count',    'psf_suppress_private_forum_meta',  10, 2 );
add_filter( 'psf_get_forum_post_count',     'psf_suppress_private_forum_meta',  10, 2 );
add_filter( 'psf_get_forum_freshness_link', 'psf_suppress_private_forum_meta',  10, 2 );
add_filter( 'psf_get_author_link',          'psf_suppress_private_author_link', 10, 2 );
add_filter( 'psf_get_topic_author_link',    'psf_suppress_private_author_link', 10, 2 );
add_filter( 'psf_get_reply_author_link',    'psf_suppress_private_author_link', 10, 2 );

// Topic and reply author display names
add_filter( 'psf_get_topic_author_display_name', 'wptexturize'   );
add_filter( 'psf_get_topic_author_display_name', 'convert_chars' );
add_filter( 'psf_get_topic_author_display_name', 'esc_html'      );
add_filter( 'psf_get_reply_author_display_name', 'wptexturize'   );
add_filter( 'psf_get_reply_author_display_name', 'convert_chars' );
add_filter( 'psf_get_reply_author_display_name', 'esc_html'      );

/**
 * Add filters to anonymous post author data
 */
// Post author name
add_filter( 'psf_pre_anonymous_post_author_name',    'trim',                10 );
add_filter( 'psf_pre_anonymous_post_author_name',    'sanitize_text_field', 10 );
add_filter( 'psf_pre_anonymous_post_author_name',    'wp_filter_kses',      10 );
add_filter( 'psf_pre_anonymous_post_author_name',    '_wp_specialchars',    30 );

// Save email
add_filter( 'psf_pre_anonymous_post_author_email',   'trim',                10 );
add_filter( 'psf_pre_anonymous_post_author_email',   'sanitize_email',      10 );
add_filter( 'psf_pre_anonymous_post_author_email',   'wp_filter_kses',      10 );

// Save URL
add_filter( 'psf_pre_anonymous_post_author_website', 'trim',                10 );
add_filter( 'psf_pre_anonymous_post_author_website', 'wp_strip_all_tags',   10 );
add_filter( 'psf_pre_anonymous_post_author_website', 'esc_url_raw',         10 );
add_filter( 'psf_pre_anonymous_post_author_website', 'wp_filter_kses',      10 );

// Queries
add_filter( 'posts_request', '_psf_has_replies_where', 10, 2 );

// Capabilities
add_filter( 'psf_map_meta_caps', 'psf_map_primary_meta_caps',   10, 4 ); // Primary caps
add_filter( 'psf_map_meta_caps', 'psf_map_forum_meta_caps',     10, 4 ); // Forums
add_filter( 'psf_map_meta_caps', 'psf_map_topic_meta_caps',     10, 4 ); // Topics
add_filter( 'psf_map_meta_caps', 'psf_map_reply_meta_caps',     10, 4 ); // Replies
add_filter( 'psf_map_meta_caps', 'psf_map_topic_tag_meta_caps', 10, 4 ); // Topic tags

// Clickables
add_filter( 'psf_make_clickable', 'psf_make_urls_clickable',      2 ); // https://psforum.org
add_filter( 'psf_make_clickable', 'psf_make_ftps_clickable',      4 ); // ftps://psforum.org
add_filter( 'psf_make_clickable', 'psf_make_emails_clickable',    6 ); // jjj@psforum.org
add_filter( 'psf_make_clickable', 'psf_make_mentions_clickable',  8 ); // @jjj

/** Deprecated ****************************************************************/

/**
 * The following filters are deprecated.
 *
 * These filters were most likely replaced by psf_parse_args(), which includes
 * both passive and aggressive filters anywhere parse_args is used to compare
 * default arguments to passed arguments, without needing to litter the
 * codebase with _before_ and _after_ filters everywhere.
 */

/**
 * Deprecated locale filter
 *
 * @since PSForum (r4213)
 *
 * @param string $locale
 * @return string  $domain
 */
function _psf_filter_locale( $locale = '', $domain = '' ) {

	// Only apply to the PSForum text-domain
	if ( psforum()->domain !== $domain ) {
		return $locale;
	}

	return apply_filters( 'psforum_locale', $locale, $domain );
}
add_filter( 'psf_plugin_locale', '_psf_filter_locale', 10, 1 );

/**
 * Deprecated forums query filter
 *
 * @since PSForum (r3961)
 * @param array $args
 * @return array
 */
function _psf_has_forums_query( $args = array() ) {
	return apply_filters( 'psf_has_forums_query', $args );
}
add_filter( 'psf_after_has_forums_parse_args', '_psf_has_forums_query' );

/**
 * Deprecated topics query filter
 *
 * @since PSForum (r3961)
 * @param array $args
 * @return array
 */
function _psf_has_topics_query( $args = array() ) {
	return apply_filters( 'psf_has_topics_query', $args );
}
add_filter( 'psf_after_has_topics_parse_args', '_psf_has_topics_query' );

/**
 * Deprecated replies query filter
 *
 * @since PSForum (r3961)
 * @param array $args
 * @return array
 */
function _psf_has_replies_query( $args = array() ) {
	return apply_filters( 'psf_has_replies_query', $args );
}
add_filter( 'psf_after_has_replies_parse_args', '_psf_has_replies_query' );
