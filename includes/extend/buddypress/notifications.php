<?php

/**
 * Filter registered notifications components, and add 'forums' to the queried
 * 'component_name' array.
 *
 * @since PSForum (r5232)
 *
 * @see BP_Notifications_Notification::get()
 * @param array $component_names
 * @return array
 */
function psf_filter_notifications_get_registered_components( $component_names = array() ) {

	// Force $component_names to be an array
	if ( ! is_array( $component_names ) ) {
		$component_names = array();
	}

	// Add 'forums' component to registered components array
	array_push( $component_names, psf_get_component_name() );

	// Return component's with 'forums' appended
	return $component_names;
}
add_filter( 'bp_notifications_get_registered_components', 'psf_filter_notifications_get_registered_components', 10 );

/**
 * Format the BuddyBar/Toolbar notifications
 *
 * @since PSForum (r5155)
 *
 * @package PSForum
 *
 * @param string $action The kind of notification being rendered
 * @param int $item_id The primary item id
 * @param int $secondary_item_id The secondary item id
 * @param int $total_items The total number of messaging-related notifications waiting for the user
 * @param string $format 'string' for BuddyBar-compatible notifications; 'array' for WP Toolbar
 */
function psf_format_buddypress_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {

	// New reply notifications
	if ( 'psf_new_reply' === $action ) {
		$topic_id    = psf_get_reply_topic_id( $item_id );
		$topic_title = psf_get_topic_title( $topic_id );
		$topic_link  = wp_nonce_url( add_query_arg( array( 'action' => 'psf_mark_read', 'topic_id' => $topic_id ), psf_get_reply_url( $item_id ) ), 'psf_mark_topic_' . $topic_id );
		$title_attr  = __( 'Thema Antworten', 'psforum' );

		if ( (int) $total_items > 1 ) {
			$text   = sprintf( __( 'Du hast %d neue Antworten', 'psforum' ), (int) $total_items );
			$filter = 'psf_multiple_new_subscription_notification';
		} else {
			if ( !empty( $secondary_item_id ) ) {
				$text = sprintf( __( 'Du hast %d neue Antwort(en) auf %2$s von %3$s', 'psforum' ), (int) $total_items, $topic_title, bp_core_get_user_displayname( $secondary_item_id ) );
			} else {
				$text = sprintf( __( 'Du hast %d neue Antwort auf %s', 'psforum' ), (int) $total_items, $topic_title );
			}
			$filter = 'psf_single_new_subscription_notification';
		}

		// WordPress Toolbar
		if ( 'string' === $format ) {
			$return = apply_filters( $filter, '<a href="' . esc_url( $topic_link ) . '" title="' . esc_attr( $title_attr ) . '">' . esc_html( $text ) . '</a>', (int) $total_items, $text, $topic_link );

		// Deprecated BuddyBar
		} else {
			$return = apply_filters( $filter, array(
				'text' => $text,
				'link' => $topic_link
			), $topic_link, (int) $total_items, $text, $topic_title );
		}

		do_action( 'psf_format_buddypress_notifications', $action, $item_id, $secondary_item_id, $total_items );

		return $return;
	}
}
add_filter( 'bp_notifications_get_notifications_for_user', 'psf_format_buddypress_notifications', 10, 5 );

/**
 * Hooked into the new reply function, this notification action is responsible
 * for notifying topic and hierarchical reply authors of topic replies.
 *
 * @since PSForum (r5156)
 *
 * @param int $reply_id
 * @param int $topic_id
 * @param int $forum_id (not used)
 * @param array $anonymous_data (not used)
 * @param int $author_id
 * @param bool $is_edit Used to bail if this gets hooked to an edit action
 * @param int $reply_to
 */
function psf_buddypress_add_notification( $reply_id = 0, $topic_id = 0, $forum_id = 0, $anonymous_data = false, $author_id = 0, $is_edit = false, $reply_to = 0 ) {

	// Bail if somehow this is hooked to an edit action
	if ( !empty( $is_edit ) ) {
		return;
	}

	// Get autohr information
	$topic_author_id   = psf_get_topic_author_id( $topic_id );
	$secondary_item_id = $author_id;

	// Hierarchical replies
	if ( !empty( $reply_to ) ) {
		$reply_to_item_id = psf_get_topic_author_id( $reply_to );
	}

	// Get some reply information
	$args = array(
		'user_id'          => $topic_author_id,
		'item_id'          => $topic_id,
		'component_name'   => psf_get_component_name(),
		'component_action' => 'psf_new_reply',
		'date_notified'    => get_post( $reply_id )->post_date,
	);

 	// Notify the topic author if not the current reply author
 	if ( $author_id !== $topic_author_id ) {
		$args['secondary_item_id'] = $secondary_item_id ;

		bp_notifications_add_notification( $args );
 	}
 
 	// Notify the immediate reply author if not the current reply author
 	if ( !empty( $reply_to ) && ( $author_id !== $reply_to_item_id ) ) {
		$args['secondary_item_id'] = $reply_to_item_id ;

		bp_notifications_add_notification( $args );
 	}
}
add_action( 'psf_new_reply', 'psf_buddypress_add_notification', 10, 7 );

/**
 * Mark notifications as read when reading a topic
 *
 * @since PSForum (r5155)
 *
 * @return If not trying to mark a notification as read
 */
function psf_buddypress_mark_notifications( $action = '' ) {

	// Bail if no topic ID is passed
	if ( empty( $_GET['topic_id'] ) ) {
		return;
	}

	// Bail if action is not for this function
	if ( 'psf_mark_read' !== $action ) {
		return;
	}

	// Get required data
	$user_id  = bp_loggedin_user_id();
	$topic_id = intval( $_GET['topic_id'] );

	// Check nonce
	if ( ! psf_verify_nonce_request( 'psf_mark_topic_' . $topic_id ) ) {
		psf_add_error( 'psf_notification_topic_id', __( '<strong>FEHLER</strong>: Willst Du das wirklich?', 'psforum' ) );

	// Check current user's ability to edit the user
	} elseif ( !current_user_can( 'edit_user', $user_id ) ) {
		psf_add_error( 'psf_notification_permissions', __( '<strong>FEHLER</strong>: Du bist nicht berechtigt, Benachrichtigungen für diesen Nutzer zu markieren.', 'psforum' ) );
	}

	// Bail if we have errors
	if ( ! psf_has_errors() ) {

		// Attempt to clear notifications for the current user from this topic
		$success = bp_notifications_mark_notifications_by_item_id( $user_id, $topic_id, psf_get_component_name(), 'psf_new_reply' );

		// Do additional subscriptions actions
		do_action( 'psf_notifications_handler', $success, $user_id, $topic_id, $action );
	}

	// Redirect to the topic
	$redirect = psf_get_reply_url( $topic_id );

	// Redirect
	wp_safe_redirect( $redirect );

	// For good measure
	exit();
}
add_action( 'psf_get_request', 'psf_buddypress_mark_notifications', 1 );
