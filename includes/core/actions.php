<?php

/**
 * PSForum Actions
 *
 * @package PSForum
 * @subpackage Core
 *
 * This file contains the actions that are used through-out PSForum. They are
 * consolidated here to make searching for them easier, and to help developers
 * understand at a glance the order in which things occur.
 *
 * There are a few common places that additional actions can currently be found
 *
 *  - PSForum: In {@link PSForum::setup_actions()} in psforum.php
 *  - Admin: More in {@link PSF_Admin::setup_actions()} in admin.php
 *
 * @see /core/filters.php
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
 *           v--WordPress Actions        v--PSForum Sub-actions
 */
add_action( 'plugins_loaded',           'psf_loaded',                 10    );
add_action( 'init',                     'psf_init',                   0     ); // Early for psf_register
add_action( 'parse_query',              'psf_parse_query',            2     ); // Early for overrides
add_action( 'widgets_init',             'psf_widgets_init',           10    );
add_action( 'generate_rewrite_rules',   'psf_generate_rewrite_rules', 10    );
add_action( 'wp_enqueue_scripts',       'psf_enqueue_scripts',        10    );
add_action( 'wp_head',                  'psf_head',                   10    );
add_action( 'wp_footer',                'psf_footer',                 10    );
add_action( 'wp_roles_init',            'psf_roles_init',             10    );
add_action( 'set_current_user',         'psf_setup_current_user',     10    );
add_action( 'setup_theme',              'psf_setup_theme',            10    );
add_action( 'after_setup_theme',        'psf_after_setup_theme',      10    );
add_action( 'template_redirect',        'psf_template_redirect',      8     ); // Before BuddyPress's 10 [BB2225]
add_action( 'login_form_login',         'psf_login_form_login',       10    );
add_action( 'profile_update',           'psf_profile_update',         10, 2 ); // user_id and old_user_data
add_action( 'user_register',            'psf_user_register',          10    );

/**
 * psf_loaded - Attached to 'plugins_loaded' above
 *
 * Attach various loader actions to the psf_loaded action.
 * The load order helps to execute code at the correct time.
 *                                                         v---Load order
 */
add_action( 'psf_loaded', 'psf_constants',                 2  );
add_action( 'psf_loaded', 'psf_boot_strap_globals',        4  );
add_action( 'psf_loaded', 'psf_includes',                  6  );
add_action( 'psf_loaded', 'psf_setup_globals',             8  );
add_action( 'psf_loaded', 'psf_setup_option_filters',      10 );
add_action( 'psf_loaded', 'psf_setup_user_option_filters', 12 );
add_action( 'psf_loaded', 'psf_register_theme_packages',   14 );
add_action( 'psf_loaded', 'psf_filter_user_roles_option',  16 );

/**
 * psf_init - Attached to 'init' above
 *
 * Attach various initialization actions to the init action.
 * The load order helps to execute code at the correct time.
 *                                               v---Load order
 */
add_action( 'psf_init', 'psf_load_textdomain',   0   );
add_action( 'psf_init', 'psf_register',          0   );
add_action( 'psf_init', 'psf_add_rewrite_tags',  20  );
add_action( 'psf_init', 'psf_add_rewrite_rules', 30  );
add_action( 'psf_init', 'psf_add_permastructs',  40  );
add_action( 'psf_init', 'psf_ready',             999 );

/**
 * psf_roles_init - Attached to 'wp_roles_init' above
 */
add_action( 'psf_roles_init', 'psf_add_forums_roles', 1 );

/**
 * When setting up the current user, make sure they have a role for the forums.
 *
 * This is multisite aware, thanks to psf_filter_user_roles_option(), hooked to
 * the 'psf_loaded' action above.
 */
add_action( 'psf_setup_current_user', 'psf_set_current_user_default_role' );

/**
 * psf_register - Attached to 'init' above on 0 priority
 *
 * Attach various initialization actions early to the init action.
 * The load order helps to execute code at the correct time.
 *                                                         v---Load order
 */
add_action( 'psf_register', 'psf_register_post_types',     2  );
add_action( 'psf_register', 'psf_register_post_statuses',  4  );
add_action( 'psf_register', 'psf_register_taxonomies',     6  );
add_action( 'psf_register', 'psf_register_views',          8  );
add_action( 'psf_register', 'psf_register_shortcodes',     10 );

// Autoembeds
add_action( 'psf_init', 'psf_reply_content_autoembed', 8 );
add_action( 'psf_init', 'psf_topic_content_autoembed', 8 );

/**
 * psf_ready - attached to end 'psf_init' above
 *
 * Attach actions to the ready action after PSForum has fully initialized.
 * The load order helps to execute code at the correct time.
 *                                                v---Load order
 */
add_action( 'psf_ready',  'psf_setup_akismet',    2  ); // Spam prevention for topics and replies
add_action( 'bp_include', 'psf_setup_buddypress', 10 ); // Social network integration

// Try to load the psforum-functions.php file from the active themes
add_action( 'psf_after_setup_theme', 'psf_load_theme_functions', 10 );

// Widgets
add_action( 'psf_widgets_init', array( 'PSF_Login_Widget',   'register_widget' ), 10 );
add_action( 'psf_widgets_init', array( 'PSF_Views_Widget',   'register_widget' ), 10 );
add_action( 'psf_widgets_init', array( 'PSF_Search_Widget',  'register_widget' ), 10 );
add_action( 'psf_widgets_init', array( 'PSF_Forums_Widget',  'register_widget' ), 10 );
add_action( 'psf_widgets_init', array( 'PSF_Topics_Widget',  'register_widget' ), 10 );
add_action( 'psf_widgets_init', array( 'PSF_Replies_Widget', 'register_widget' ), 10 );
add_action( 'psf_widgets_init', array( 'PSF_Stats_Widget',   'register_widget' ), 10 );

// Notices (loaded after psf_init for translations)
add_action( 'psf_head',             'psf_login_notices'    );
add_action( 'psf_head',             'psf_topic_notices'    );
add_action( 'psf_template_notices', 'psf_template_notices' );

// Always exclude private/hidden forums if needed
add_action( 'pre_get_posts', 'psf_pre_get_posts_normalize_forum_visibility', 4 );

// Profile Page Messages
add_action( 'psf_template_notices', 'psf_notice_edit_user_success'           );
add_action( 'psf_template_notices', 'psf_notice_edit_user_is_super_admin', 2 );

// Before Delete/Trash/Untrash Topic
add_action( 'wp_trash_post', 'psf_trash_forum'   );
add_action( 'trash_post',    'psf_trash_forum'   );
add_action( 'untrash_post',  'psf_untrash_forum' );
add_action( 'delete_post',   'psf_delete_forum'  );

// After Deleted/Trashed/Untrashed Topic
add_action( 'trashed_post',   'psf_trashed_forum'   );
add_action( 'untrashed_post', 'psf_untrashed_forum' );
add_action( 'deleted_post',   'psf_deleted_forum'   );

// Auto trash/untrash/delete a forums topics
add_action( 'psf_delete_forum',  'psf_delete_forum_topics',  10 );
add_action( 'psf_trash_forum',   'psf_trash_forum_topics',   10 );
add_action( 'psf_untrash_forum', 'psf_untrash_forum_topics', 10 );

// New/Edit Forum
add_action( 'psf_new_forum',  'psf_update_forum', 10 );
add_action( 'psf_edit_forum', 'psf_update_forum', 10 );

// Save forum extra metadata
add_action( 'psf_new_forum_post_extras',         'psf_save_forum_extras', 2 );
add_action( 'psf_edit_forum_post_extras',        'psf_save_forum_extras', 2 );
add_action( 'psf_forum_attributes_metabox_save', 'psf_save_forum_extras', 2 );

// New/Edit Reply
add_action( 'psf_new_reply',  'psf_update_reply', 10, 7 );
add_action( 'psf_edit_reply', 'psf_update_reply', 10, 7 );

// Before Delete/Trash/Untrash Reply
add_action( 'wp_trash_post', 'psf_trash_reply'   );
add_action( 'trash_post',    'psf_trash_reply'   );
add_action( 'untrash_post',  'psf_untrash_reply' );
add_action( 'delete_post',   'psf_delete_reply'  );

// After Deleted/Trashed/Untrashed Reply
add_action( 'trashed_post',   'psf_trashed_reply'   );
add_action( 'untrashed_post', 'psf_untrashed_reply' );
add_action( 'deleted_post',   'psf_deleted_reply'   );

// New/Edit Topic
add_action( 'psf_new_topic',  'psf_update_topic', 10, 5 );
add_action( 'psf_edit_topic', 'psf_update_topic', 10, 5 );

// Split/Merge Topic
add_action( 'psf_merged_topic',     'psf_merge_topic_count', 1, 3 );
add_action( 'psf_post_split_topic', 'psf_split_topic_count', 1, 3 );

// Move Reply
add_action( 'psf_post_move_reply', 'psf_move_reply_count', 1, 3 );

// Before Delete/Trash/Untrash Topic
add_action( 'wp_trash_post', 'psf_trash_topic'   );
add_action( 'trash_post',    'psf_trash_topic'   );
add_action( 'untrash_post',  'psf_untrash_topic' );
add_action( 'delete_post',   'psf_delete_topic'  );

// After Deleted/Trashed/Untrashed Topic
add_action( 'trashed_post',   'psf_trashed_topic'   );
add_action( 'untrashed_post', 'psf_untrashed_topic' );
add_action( 'deleted_post',   'psf_deleted_topic'   );

// Favorites
add_action( 'psf_trash_topic',  'psf_remove_topic_from_all_favorites' );
add_action( 'psf_delete_topic', 'psf_remove_topic_from_all_favorites' );

// Subscriptions
add_action( 'psf_trash_topic',  'psf_remove_topic_from_all_subscriptions'       );
add_action( 'psf_delete_topic', 'psf_remove_topic_from_all_subscriptions'       );
add_action( 'psf_trash_forum',  'psf_remove_forum_from_all_subscriptions'       );
add_action( 'psf_delete_forum', 'psf_remove_forum_from_all_subscriptions'       );
add_action( 'psf_new_reply',    'psf_notify_topic_subscribers',           11, 5 );
add_action( 'psf_new_topic',    'psf_notify_forum_subscribers',           11, 4 );

// Sticky
add_action( 'psf_trash_topic',  'psf_unstick_topic' );
add_action( 'psf_delete_topic', 'psf_unstick_topic' );

// Update topic branch
add_action( 'psf_trashed_topic',   'psf_update_topic_walker' );
add_action( 'psf_untrashed_topic', 'psf_update_topic_walker' );
add_action( 'psf_deleted_topic',   'psf_update_topic_walker' );
add_action( 'psf_spammed_topic',   'psf_update_topic_walker' );
add_action( 'psf_unspammed_topic', 'psf_update_topic_walker' );

// Update reply branch
add_action( 'psf_trashed_reply',   'psf_update_reply_walker' );
add_action( 'psf_untrashed_reply', 'psf_update_reply_walker' );
add_action( 'psf_deleted_reply',   'psf_update_reply_walker' );
add_action( 'psf_spammed_reply',   'psf_update_reply_walker' );
add_action( 'psf_unspammed_reply', 'psf_update_reply_walker' );

// User status
// @todo make these sub-actions
add_action( 'make_ham_user',  'psf_make_ham_user'  );
add_action( 'make_spam_user', 'psf_make_spam_user' );

// User role
add_action( 'psf_profile_update', 'psf_profile_update_role' );

// Hook WordPress admin actions to PSForum profiles on save
add_action( 'psf_user_edit_after', 'psf_user_edit_after' );

// Caches
add_action( 'psf_new_forum_pre_extras',  'psf_clean_post_cache' );
add_action( 'psf_new_forum_post_extras', 'psf_clean_post_cache' );
add_action( 'psf_new_topic_pre_extras',  'psf_clean_post_cache' );
add_action( 'psf_new_topic_post_extras', 'psf_clean_post_cache' );
add_action( 'psf_new_reply_pre_extras',  'psf_clean_post_cache' );
add_action( 'psf_new_reply_post_extras', 'psf_clean_post_cache' );

/**
 * PSForum needs to redirect the user around in a few different circumstances:
 *
 * 1. POST and GET requests
 * 2. Accessing private or hidden content (forums/topics/replies)
 * 3. Editing forums, topics, replies, users, and tags
 * 4. PSForum specific AJAX requests
 */
add_action( 'psf_template_redirect', 'psf_forum_enforce_blocked', 1  );
add_action( 'psf_template_redirect', 'psf_forum_enforce_hidden',  1  );
add_action( 'psf_template_redirect', 'psf_forum_enforce_private', 1  );
add_action( 'psf_template_redirect', 'psf_post_request',          10 );
add_action( 'psf_template_redirect', 'psf_get_request',           10 );
add_action( 'psf_template_redirect', 'psf_check_user_edit',       10 );
add_action( 'psf_template_redirect', 'psf_check_forum_edit',      10 );
add_action( 'psf_template_redirect', 'psf_check_topic_edit',      10 );
add_action( 'psf_template_redirect', 'psf_check_reply_edit',      10 );
add_action( 'psf_template_redirect', 'psf_check_topic_tag_edit',  10 );

// Theme-side POST requests
add_action( 'psf_post_request', 'psf_do_ajax',                1  );
add_action( 'psf_post_request', 'psf_edit_topic_tag_handler', 1  );
add_action( 'psf_post_request', 'psf_edit_user_handler',      1  );
add_action( 'psf_post_request', 'psf_edit_forum_handler',     1  );
add_action( 'psf_post_request', 'psf_edit_reply_handler',     1  );
add_action( 'psf_post_request', 'psf_edit_topic_handler',     1  );
add_action( 'psf_post_request', 'psf_merge_topic_handler',    1  );
add_action( 'psf_post_request', 'psf_split_topic_handler',    1  );
add_action( 'psf_post_request', 'psf_move_reply_handler',     1  );
add_action( 'psf_post_request', 'psf_new_forum_handler',      10 );
add_action( 'psf_post_request', 'psf_new_reply_handler',      10 );
add_action( 'psf_post_request', 'psf_new_topic_handler',      10 );

// Theme-side GET requests
add_action( 'psf_get_request', 'psf_toggle_topic_handler',        1  );
add_action( 'psf_get_request', 'psf_toggle_reply_handler',        1  );
add_action( 'psf_get_request', 'psf_favorites_handler',           1  );
add_action( 'psf_get_request', 'psf_subscriptions_handler',       1  );
add_action( 'psf_get_request', 'psf_forum_subscriptions_handler', 1  );
add_action( 'psf_get_request', 'psf_search_results_redirect',     10 );

// Maybe convert the users password
add_action( 'psf_login_form_login', 'psf_user_maybe_convert_pass' );

add_action( 'psf_activation', 'psf_add_activation_redirect' );