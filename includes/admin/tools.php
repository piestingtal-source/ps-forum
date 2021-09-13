<?php

/**
 * PSForum Admin Tools Page
 *
 * @package PSForum
 * @subpackage Administration
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/** Repair ********************************************************************/

/**
 * Admin repair page
 *
 * @since PSForum (r2613)
 *
 * @uses psf_admin_repair_list() To get the recount list
 * @uses check_admin_referer() To verify the nonce and the referer
 * @uses wp_cache_flush() To flush the cache
 * @uses do_action() Calls 'admin_notices' to display the notices
 * @uses wp_nonce_field() To add a hidden nonce field
 */
function psf_admin_repair() {
?>

	<div class="wrap">

		<h2 class="nav-tab-wrapper"><?php psf_tools_admin_tabs( __( 'Repariere Foren', 'psforum' ) ); ?></h2>

		<p><?php esc_html_e( 'PS Forum verfolgt die Beziehungen zwischen Foren, Themen, Antworten und Themen-Tags und Benutzern. Gelegentlich werden diese Beziehungen nicht synchron, meistens nach einem Import oder einer Migration. Verwende die folgenden Tools, um diese Beziehungen manuell neu zu berechnen.', 'psforum' ); ?></p>
		<p class="description"><?php esc_html_e( 'Einige dieser Tools verursachen einen erheblichen Datenbank-Overhead. Vermeide die gleichzeitige Ausführung von mehr als einem Reparaturauftrag.', 'psforum' ); ?></p>

		<form class="settings" method="post" action="">
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Beziehungen zu reparieren:', 'psforum' ) ?></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><span><?php esc_html_e( 'Reparatur', 'psforum' ) ?></span></legend>

								<?php foreach ( psf_admin_repair_list() as $item ) : ?>

									<label><input type="checkbox" class="checkbox" name="<?php echo esc_attr( $item[0] ) . '" id="' . esc_attr( str_replace( '_', '-', $item[0] ) ); ?>" value="1" /> <?php echo esc_html( $item[1] ); ?></label><br />

								<?php endforeach; ?>

							</fieldset>
						</td>
					</tr>
				</tbody>
			</table>

			<fieldset class="submit">
				<input class="button-primary" type="submit" name="submit" value="<?php esc_attr_e( 'Repariere Elemente', 'psforum' ); ?>" />
				<?php wp_nonce_field( 'psforum-do-counts' ); ?>
			</fieldset>
		</form>
	</div>

<?php
}

/**
 * Handle the processing and feedback of the admin tools page
 *
 * @since PSForum (r2613)
 *
 * @uses psf_admin_repair_list() To get the recount list
 * @uses check_admin_referer() To verify the nonce and the referer
 * @uses wp_cache_flush() To flush the cache
 * @uses do_action() Calls 'admin_notices' to display the notices
 */
function psf_admin_repair_handler() {

	if ( ! psf_is_post_request() )
		return;

	check_admin_referer( 'psforum-do-counts' );

	// Stores messages
	$messages = array();

	wp_cache_flush();

	foreach ( (array) psf_admin_repair_list() as $item ) {
		if ( isset( $item[2] ) && isset( $_POST[$item[0]] ) && 1 === absint( $_POST[$item[0]] ) && is_callable( $item[2] ) ) {
			$messages[] = call_user_func( $item[2] );
		}
	}

	if ( count( $messages ) ) {
		foreach ( $messages as $message ) {
			psf_admin_tools_feedback( $message[1] );
		}
	}
}

/**
 * Assemble the admin notices
 *
 * @since PSForum (r2613)
 *
 * @param string|WP_Error $message A message to be displayed or {@link WP_Error}
 * @param string $class Optional. A class to be added to the message div
 * @uses WP_Error::get_error_messages() To get the error messages of $message
 * @uses add_action() Adds the admin notice action with the message HTML
 * @return string The message HTML
 */
function psf_admin_tools_feedback( $message, $class = false ) {
	if ( is_string( $message ) ) {
		$message = '<p>' . $message . '</p>';
		$class = $class ? $class : 'updated';
	} elseif ( is_wp_error( $message ) ) {
		$errors = $message->get_error_messages();

		switch ( count( $errors ) ) {
			case 0:
				return false;
				break;

			case 1:
				$message = '<p>' . $errors[0] . '</p>';
				break;

			default:
				$message = '<ul>' . "\n\t" . '<li>' . implode( '</li>' . "\n\t" . '<li>', $errors ) . '</li>' . "\n" . '</ul>';
				break;
		}

		$class = $class ? $class : 'error';
	} else {
		return false;
	}

	$message = '<div id="message" class="' . esc_attr( $class ) . '">' . $message . '</div>';
	$message = str_replace( "'", "\'", $message );
	$lambda  = create_function( '', "echo '$message';" );

	add_action( 'admin_notices', $lambda );

	return $lambda;
}

/**
 * Get the array of the repair list
 *
 * @since PSForum (r2613)
 *
 * @uses apply_filters() Calls 'psf_repair_list' with the list array
 * @return array Repair list of options
 */
function psf_admin_repair_list() {
	$repair_list = array(
		0  => array( 'psf-sync-topic-meta',          __( 'Berechne das übergeordnete Thema für jeden Beitrag neu', 'psforum' ), 'psf_admin_repair_topic_meta'               ),
		5  => array( 'psf-sync-forum-meta',          __( 'Berechne das übergeordnete Forum für jeden Beitrag neu', 'psforum' ), 'psf_admin_repair_forum_meta'               ),
		10 => array( 'psf-sync-forum-visibility',    __( 'Berechne private und versteckte Foren neu', 'psforum' ), 'psf_admin_repair_forum_visibility'         ),
		15 => array( 'psf-sync-all-topics-forums',   __( 'Letzte Aktivität in jedem Thema und Forum neu berechnen', 'psforum' ), 'psf_admin_repair_freshness'                ),
		20 => array( 'psf-sync-all-topics-sticky',   __( 'Berechne die klebrige Beziehung jedes Themas neu', 'psforum' ), 'psf_admin_repair_sticky'                   ),
		25 => array( 'psf-sync-all-reply-positions', __( 'Berechne die Position jeder Antwort neu', 'psforum' ), 'psf_admin_repair_reply_menu_order'         ),
		30 => array( 'psf-group-forums',             __( 'Beziehungen zum BuddyPress Group Forum reparieren', 'psforum' ), 'psf_admin_repair_group_forum_relationship' ),
		35 => array( 'psf-forum-topics',             __( 'Zähle die Themen in jedem Forum', 'psforum' ), 'psf_admin_repair_forum_topic_count'        ),
		40 => array( 'psf-forum-replies',            __( 'Antworten in jedem Forum zählen', 'psforum' ), 'psf_admin_repair_forum_reply_count'        ),
		45 => array( 'psf-topic-replies',            __( 'Antworten in jedem Thema zählen', 'psforum' ), 'psf_admin_repair_topic_reply_count'        ),
		50 => array( 'psf-topic-voices',             __( 'Zähle die Stimmen in jedem Thema', 'psforum' ), 'psf_admin_repair_topic_voice_count'        ),
		55 => array( 'psf-topic-hidden-replies',     __( 'Zähle Spam- und Papierkorb-Antworten in jedem Thema', 'psforum' ), 'psf_admin_repair_topic_hidden_reply_count' ),
		60 => array( 'psf-user-topics',              __( 'Zähle Themen für jeden Benutzer', 'psforum' ), 'psf_admin_repair_user_topic_count'         ),
		65 => array( 'psf-user-replies',             __( 'Antworten für jeden Benutzer zählen', 'psforum' ), 'psf_admin_repair_user_reply_count'         ),
		70 => array( 'psf-user-favorites',           __( 'Entferne gelöschte Themen aus den Benutzerfavoriten', 'psforum' ), 'psf_admin_repair_user_favorites'           ),
		75 => array( 'psf-user-topic-subscriptions', __( 'Entferne gelöschte Themen aus Benutzerabonnements', 'psforum' ), 'psf_admin_repair_user_topic_subscriptions' ),
		80 => array( 'psf-user-forum-subscriptions', __( 'Entferne gelöschte Foren aus Benutzerabonnements', 'psforum' ), 'psf_admin_repair_user_forum_subscriptions' ),
		85 => array( 'psf-user-role-map',            __( 'Vorhandene Benutzer den Standard-Forenrollen neu zuordnen', 'psforum' ), 'psf_admin_repair_user_roles'               )
	);
	ksort( $repair_list );

	return (array) apply_filters( 'psf_repair_list', $repair_list );
}

/**
 * Recount topic replies
 *
 * @since PSForum (r2613)
 *
 * @uses psf_get_reply_post_type() To get the reply post type
 * @uses wpdb::query() To run our recount sql queries
 * @uses is_wp_error() To check if the executed query returned {@link WP_Error}
 * @return array An array of the status code and the message
 */
function psf_admin_repair_topic_reply_count() {
	global $wpdb;

	$statement = __( 'Zählen der Antworten in jedem Thema&hellip; %s', 'psforum' );
	$result    = __( 'Fehlgeschlagen!', 'psforum' );

	// Post types and status
	$tpt = psf_get_topic_post_type();
	$rpt = psf_get_reply_post_type();
	$pps = psf_get_public_status_id();
	$cps = psf_get_closed_status_id();

	// Delete the meta key _psf_reply_count for each topic
	$sql_delete = "DELETE `postmeta` FROM `{$wpdb->postmeta}` AS `postmeta`
						LEFT JOIN `{$wpdb->posts}` AS `posts` ON `posts`.`ID` = `postmeta`.`post_id`
						WHERE `posts`.`post_type` = '{$tpt}'
						AND `postmeta`.`meta_key` = '_psf_reply_count'";

	if ( is_wp_error( $wpdb->query( $sql_delete ) ) ) {
		return array( 1, sprintf( $statement, $result ) );
	}

	// Recalculate the meta key _psf_reply_count for each topic
	$sql = "INSERT INTO `{$wpdb->postmeta}` (`post_id`, `meta_key`, `meta_value`) (
			SELECT `topics`.`ID` AS `post_id`, '_psf_reply_count' AS `meta_key`, COUNT(`replies`.`ID`) As `meta_value`
				FROM `{$wpdb->posts}` AS `topics`
					LEFT JOIN `{$wpdb->posts}` as `replies`
						ON  `replies`.`post_parent` = `topics`.`ID`
						AND `replies`.`post_status` = '{$pps}'
						AND `replies`.`post_type`   = '{$rpt}'
				WHERE `topics`.`post_type` = '{$tpt}'
					AND `topics`.`post_status` IN ( '{$pps}', '{$cps}' )
				GROUP BY `topics`.`ID`);";

	if ( is_wp_error( $wpdb->query( $sql ) ) ) {
		return array( 2, sprintf( $statement, $result ) );
	}

	return array( 0, sprintf( $statement, __( 'Durchgeführt!', 'psforum' ) ) );
}

/**
 * Recount topic voices
 *
 * @since PSForum (r2613)
 *
 * @uses psf_get_reply_post_type() To get the reply post type
 * @uses wpdb::query() To run our recount sql queries
 * @uses is_wp_error() To check if the executed query returned {@link WP_Error}
 * @return array An array of the status code and the message
 */
function psf_admin_repair_topic_voice_count() {
	global $wpdb;

	$statement = __( 'Zählen der Stimmen in jedem Thema&hellip; %s', 'psforum' );
	$result    = __( 'Fehlgeschlagen!', 'psforum' );

	$sql_delete = "DELETE FROM `{$wpdb->postmeta}` WHERE `meta_key` = '_psf_voice_count';";
	if ( is_wp_error( $wpdb->query( $sql_delete ) ) )
		return array( 1, sprintf( $statement, $result ) );

	// Post types and status
	$tpt = psf_get_topic_post_type();
	$rpt = psf_get_reply_post_type();
	$pps = psf_get_public_status_id();
	$cps = psf_get_closed_status_id();

	$sql = "INSERT INTO `{$wpdb->postmeta}` (`post_id`, `meta_key`, `meta_value`) (
			SELECT `postmeta`.`meta_value`, '_psf_voice_count', COUNT(DISTINCT `post_author`) as `meta_value`
				FROM `{$wpdb->posts}` AS `posts`
				LEFT JOIN `{$wpdb->postmeta}` AS `postmeta`
					ON `posts`.`ID` = `postmeta`.`post_id`
					AND `postmeta`.`meta_key` = '_psf_topic_id'
				WHERE `posts`.`post_type` IN ( '{$tpt}', '{$rpt}' )
					AND `posts`.`post_status` IN ( '{$pps}', '{$cps}' )
					AND `posts`.`post_author` != '0'
				GROUP BY `postmeta`.`meta_value`);";

	if ( is_wp_error( $wpdb->query( $sql ) ) )
		return array( 2, sprintf( $statement, $result ) );

	return array( 0, sprintf( $statement, __( 'Durchgeführt!', 'psforum' ) ) );
}

/**
 * Recount topic hidden replies (spammed/trashed)
 *
 * @since PSForum (r2747)
 *
 * @uses wpdb::query() To run our recount sql queries
 * @uses is_wp_error() To check if the executed query returned {@link WP_Error}
 * @return array An array of the status code and the message
 */
function psf_admin_repair_topic_hidden_reply_count() {
	global $wpdb;

	$statement = __( 'Zählen der Spam- und Papierkorb-Antworten in jedem Thema&hellip; %s', 'psforum' );
	$result    = __( 'Fehlgeschlagen!', 'psforum' );

	$sql_delete = "DELETE FROM `{$wpdb->postmeta}` WHERE `meta_key` = '_psf_reply_count_hidden';";
	if ( is_wp_error( $wpdb->query( $sql_delete ) ) )
		return array( 1, sprintf( $statement, $result ) );

	$sql = "INSERT INTO `{$wpdb->postmeta}` (`post_id`, `meta_key`, `meta_value`) (SELECT `post_parent`, '_psf_reply_count_hidden', COUNT(`post_status`) as `meta_value` FROM `{$wpdb->posts}` WHERE `post_type` = '" . psf_get_reply_post_type() . "' AND `post_status` IN ( '" . implode( "','", array( psf_get_trash_status_id(), psf_get_spam_status_id() ) ) . "') GROUP BY `post_parent`);";
	if ( is_wp_error( $wpdb->query( $sql ) ) )
		return array( 2, sprintf( $statement, $result ) );

	return array( 0, sprintf( $statement, __( 'Durchgeführt!', 'psforum' ) ) );
}

/**
 * Repair group forum ID mappings after a PSForum 1.1 to PSForum 2.2 conversion
 *
 * @since PSForum (r4395)
 *
 * @global WPDB $wpdb
 * @return If a wp_error() occurs and no converted forums are found
 */
function psf_admin_repair_group_forum_relationship() {
	global $wpdb;

	$statement = __( 'Reparieren der BuddyPress-Gruppen-Forum-Beziehungen&hellip; %s', 'psforum' );
	$g_count     = 0;
	$f_count     = 0;
	$s_count     = 0;

	// Copy the BuddyPress filter here, incase BuddyPress is not active
	$prefix            = apply_filters( 'bp_core_get_table_prefix', $wpdb->base_prefix );
	$groups_table      = $prefix . 'bp_groups';
	$groups_meta_table = $prefix . 'bp_groups_groupmeta';

	// Get the converted forum IDs
	$forum_ids = $wpdb->query( "SELECT `forum`.`ID`, `forummeta`.`meta_value`
								FROM `{$wpdb->posts}` AS `forum`
									LEFT JOIN `{$wpdb->postmeta}` AS `forummeta`
										ON `forum`.`ID` = `forummeta`.`post_id`
										AND `forummeta`.`meta_key` = '_psf_old_forum_id'
								WHERE `forum`.`post_type` = 'forum'
								GROUP BY `forum`.`ID`;" );

	// Bail if forum IDs returned an error
	if ( is_wp_error( $forum_ids ) || empty( $wpdb->last_result ) )
		return array( 2, sprintf( $statement, __( 'Fehlgeschlagen!', 'psforum' ) ) );

	// Stash the last results
	$results = $wpdb->last_result;

	// Update each group forum
	foreach ( $results as $group_forums ) {

		// Only update if is a converted forum
		if ( ! isset( $group_forums->meta_value ) )
			continue;

		// Attempt to update group meta
		$updated = $wpdb->query( "UPDATE `{$groups_meta_table}` SET `meta_value` = '{$group_forums->ID}' WHERE `meta_key` = 'forum_id' AND `meta_value` = '{$group_forums->meta_value}';" );

		// Bump the count
		if ( !empty( $updated ) && ! is_wp_error( $updated ) ) {
			++$g_count;
		}

		// Update group to forum relationship data
		$group_id = (int) $wpdb->get_var( "SELECT `group_id` FROM `{$groups_meta_table}` WHERE `meta_key` = 'forum_id' AND `meta_value` = '{$group_forums->ID}';" );
		if ( !empty( $group_id ) ) {

			// Update the group to forum meta connection in forums
			update_post_meta( $group_forums->ID, '_psf_group_ids', array( $group_id ) );

			// Get the group status
			$group_status = $wpdb->get_var( "SELECT `status` FROM `{$groups_table}` WHERE `id` = '{$group_id}';" );

			// Sync up forum visibility based on group status
			switch ( $group_status ) {

				// Public groups have public forums
				case 'public' :
					psf_publicize_forum( $group_forums->ID );

					// Bump the count for output later
					++$s_count;
					break;

				// Private/hidden groups have hidden forums
				case 'private' :
				case 'hidden'  :
					psf_hide_forum( $group_forums->ID );

					// Bump the count for output later
					++$s_count;
					break;
			}

			// Bump the count for output later
			++$f_count;
		}
	}

	// Make some logical guesses at the old group root forum
	if ( function_exists( 'bp_forums_parent_forum_id' ) ) {
		$old_default_forum_id = bp_forums_parent_forum_id();
	} elseif ( defined( 'BP_FORUMS_PARENT_FORUM_ID' ) ) {
		$old_default_forum_id = (int) BP_FORUMS_PARENT_FORUM_ID;
	} else {
		$old_default_forum_id = 1;
	}

	// Try to get the group root forum
	$posts = get_posts( array(
		'post_type'   => psf_get_forum_post_type(),
		'meta_key'    => '_psf_old_forum_id',
		'meta_value'  => $old_default_forum_id,
		'numberposts' => 1
	) );

	// Found the group root forum
	if ( ! empty( $posts ) ) {

		// Rename 'Default Forum'  since it's now visible in sitewide forums
		if ( 'Default Forum' === $posts[0]->post_title ) {
			wp_update_post( array(
				'ID'         => $posts[0]->ID,
				'post_title' => __( 'Gruppenforen', 'psforum' ),
				'post_name'  => __( 'group-forums', 'psforum' ),
			) );
		}

		// Update the group forums root metadata
		update_option( '_psf_group_forums_root_id', $posts[0]->ID );
	}

	// Remove old PSForum 1.1 roles (BuddyPress)
	remove_role( 'member'    );
	remove_role( 'inactive'  );
	remove_role( 'blocked'   );
	remove_role( 'moderator' );
	remove_role( 'keymaster' );

	// Complete results
	$result = sprintf( __( 'Durchgeführt! %s Gruppen aktualisiert; %s Foren aktualisiert; %s Forenstatus synchronisiert.', 'psforum' ), psf_number_format( $g_count ), psf_number_format( $f_count ), psf_number_format( $s_count ) );
	return array( 0, sprintf( $statement, $result ) );
}

/**
 * Recount forum topics
 *
 * @since PSForum (r2613)
 *
 * @uses wpdb::query() To run our recount sql queries
 * @uses is_wp_error() To check if the executed query returned {@link WP_Error}
 * @uses psf_get_forum_post_type() To get the forum post type
 * @uses get_posts() To get the forums
 * @uses psf_update_forum_topic_count() To update the forum topic count
 * @return array An array of the status code and the message
 */
function psf_admin_repair_forum_topic_count() {
	global $wpdb;

	$statement = __( 'Zählen der Anzahl der Themen in jedem Forum&hellip; %s', 'psforum' );
	$result    = __( 'Fehlgeschlagen!', 'psforum' );

	$sql_delete = "DELETE FROM {$wpdb->postmeta} WHERE meta_key IN ( '_psf_topic_count', '_psf_total_topic_count' );";
	if ( is_wp_error( $wpdb->query( $sql_delete ) ) )
		return array( 1, sprintf( $statement, $result ) );

	$forums = get_posts( array( 'post_type' => psf_get_forum_post_type(), 'numberposts' => -1 ) );
	if ( !empty( $forums ) ) {
		foreach ( $forums as $forum ) {
			psf_update_forum_topic_count( $forum->ID );
		}
	} else {
		return array( 2, sprintf( $statement, $result ) );
	}

	return array( 0, sprintf( $statement, __( 'Durchgeführt!', 'psforum' ) ) );
}

/**
 * Recount forum replies
 *
 * @since PSForum (r2613)
 *
 * @uses wpdb::query() To run our recount sql queries
 * @uses is_wp_error() To check if the executed query returned {@link WP_Error}
 * @uses psf_get_forum_post_type() To get the forum post type
 * @uses get_posts() To get the forums
 * @uses psf_update_forum_reply_count() To update the forum reply count
 * @return array An array of the status code and the message
 */
function psf_admin_repair_forum_reply_count() {
	global $wpdb;

	$statement = __( 'Zählen der Antworten in jedem Forum&hellip; %s', 'psforum' );
	$result    = __( 'Fehlgeschlagen!', 'psforum' );

	// Post type
	$fpt = psf_get_forum_post_type();

	// Delete the meta keys _psf_reply_count and _psf_total_reply_count for each forum
	$sql_delete = "DELETE `postmeta` FROM `{$wpdb->postmeta}` AS `postmeta`
						LEFT JOIN `{$wpdb->posts}` AS `posts` ON `posts`.`ID` = `postmeta`.`post_id`
						WHERE `posts`.`post_type` = '{$fpt}'
						AND `postmeta`.`meta_key` = '_psf_reply_count'
						OR `postmeta`.`meta_key` = '_psf_total_reply_count'";

	if ( is_wp_error( $wpdb->query( $sql_delete ) ) ) {
 		return array( 1, sprintf( $statement, $result ) );
	}

	// Recalculate the metas key _psf_reply_count and _psf_total_reply_count for each forum
	$forums = get_posts( array( 'post_type' => psf_get_forum_post_type(), 'numberposts' => -1 ) );
	if ( !empty( $forums ) ) {
		foreach ( $forums as $forum ) {
			psf_update_forum_reply_count( $forum->ID );
		}
	} else {
		return array( 2, sprintf( $statement, $result ) );
	}

	return array( 0, sprintf( $statement, __( 'Durchgeführt!', 'psforum' ) ) );
}

/**
 * Recount topics by the users
 *
 * @since PSForum (r3889)
 *
 * @uses psf_get_reply_post_type() To get the reply post type
 * @uses wpdb::query() To run our recount sql queries
 * @uses is_wp_error() To check if the executed query returned {@link WP_Error}
 * @return array An array of the status code and the message
 */
function psf_admin_repair_user_topic_count() {
	global $wpdb;

	$statement   = __( 'Zählen der Themen, die jeder Benutzer erstellt hat&hellip; %s', 'psforum' );
	$result      = __( 'Fehlgeschlagen!', 'psforum' );
	$sql_select  = "SELECT `post_author`, COUNT(DISTINCT `ID`) as `_count` FROM `{$wpdb->posts}` WHERE `post_type` = '" . psf_get_topic_post_type() . "' AND `post_status` = '" . psf_get_public_status_id() . "' GROUP BY `post_author`;";
	$insert_rows = $wpdb->get_results( $sql_select );

	if ( is_wp_error( $insert_rows ) )
		return array( 1, sprintf( $statement, $result ) );

	$key           = $wpdb->prefix . '_psf_topic_count';
	$insert_values = array();
	foreach ( $insert_rows as $insert_row )
		$insert_values[] = "('{$insert_row->post_author}', '{$key}', '{$insert_row->_count}')";

	if ( !count( $insert_values ) )
		return array( 2, sprintf( $statement, $result ) );

	$sql_delete = "DELETE FROM `{$wpdb->usermeta}` WHERE `meta_key` = '{$key}';";
	if ( is_wp_error( $wpdb->query( $sql_delete ) ) )
		return array( 3, sprintf( $statement, $result ) );

	foreach ( array_chunk( $insert_values, 10000 ) as $chunk ) {
		$chunk = "\n" . implode( ",\n", $chunk );
		$sql_insert = "INSERT INTO `{$wpdb->usermeta}` (`user_id`, `meta_key`, `meta_value`) VALUES $chunk;";

		if ( is_wp_error( $wpdb->query( $sql_insert ) ) ) {
			return array( 4, sprintf( $statement, $result ) );
		}
	}

	return array( 0, sprintf( $statement, __( 'Durchgeführt!', 'psforum' ) ) );
}

/**
 * Recount topic replied by the users
 *
 * @since PSForum (r2613)
 *
 * @uses psf_get_reply_post_type() To get the reply post type
 * @uses wpdb::query() To run our recount sql queries
 * @uses is_wp_error() To check if the executed query returned {@link WP_Error}
 * @return array An array of the status code and the message
 */
function psf_admin_repair_user_reply_count() {
	global $wpdb;

	$statement   = __( 'Zählen der Themen, auf die jeder Benutzer geantwortet hat&hellip; %s', 'psforum' );
	$result      = __( 'Fehlgeschlagen!', 'psforum' );
	$sql_select  = "SELECT `post_author`, COUNT(DISTINCT `ID`) as `_count` FROM `{$wpdb->posts}` WHERE `post_type` = '" . psf_get_reply_post_type() . "' AND `post_status` = '" . psf_get_public_status_id() . "' GROUP BY `post_author`;";
	$insert_rows = $wpdb->get_results( $sql_select );

	if ( is_wp_error( $insert_rows ) )
		return array( 1, sprintf( $statement, $result ) );

	$key           = $wpdb->prefix . '_psf_reply_count';
	$insert_values = array();
	foreach ( $insert_rows as $insert_row )
		$insert_values[] = "('{$insert_row->post_author}', '{$key}', '{$insert_row->_count}')";

	if ( !count( $insert_values ) )
		return array( 2, sprintf( $statement, $result ) );

	$sql_delete = "DELETE FROM `{$wpdb->usermeta}` WHERE `meta_key` = '{$key}';";
	if ( is_wp_error( $wpdb->query( $sql_delete ) ) )
		return array( 3, sprintf( $statement, $result ) );

	foreach ( array_chunk( $insert_values, 10000 ) as $chunk ) {
		$chunk = "\n" . implode( ",\n", $chunk );
		$sql_insert = "INSERT INTO `{$wpdb->usermeta}` (`user_id`, `meta_key`, `meta_value`) VALUES $chunk;";

		if ( is_wp_error( $wpdb->query( $sql_insert ) ) ) {
			return array( 4, sprintf( $statement, $result ) );
		}
	}

	return array( 0, sprintf( $statement, __( 'Durchgeführt!', 'psforum' ) ) );
}

/**
 * Clean the users' favorites
 *
 * @since PSForum (r2613)
 *
 * @uses psf_get_topic_post_type() To get the topic post type
 * @uses wpdb::query() To run our recount sql queries
 * @uses is_wp_error() To check if the executed query returned {@link WP_Error}
 * @return array An array of the status code and the message
 */
function psf_admin_repair_user_favorites() {
	global $wpdb;

	$statement = __( 'Löschen von Themen aus dem Papierkorb aus den Benutzerfavoriten&hellip; %s', 'psforum' );
	$result    = __( 'Fehlgeschlagen!', 'psforum' );
	$key       = $wpdb->prefix . '_psf_favorites';
	$users     = $wpdb->get_results( "SELECT `user_id`, `meta_value` AS `favorites` FROM `{$wpdb->usermeta}` WHERE `meta_key` = '{$key}';" );

	if ( is_wp_error( $users ) )
		return array( 1, sprintf( $statement, $result ) );

	$topics = $wpdb->get_col( "SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_type` = '" . psf_get_topic_post_type() . "' AND `post_status` = '" . psf_get_public_status_id() . "';" );

	if ( is_wp_error( $topics ) )
		return array( 2, sprintf( $statement, $result ) );

	$values = array();
	foreach ( $users as $user ) {
		if ( empty( $user->favorites ) || !is_string( $user->favorites ) )
			continue;

		$favorites = array_intersect( $topics, explode( ',', $user->favorites ) );
		if ( empty( $favorites ) || !is_array( $favorites ) )
			continue;

		$favorites_joined = implode( ',', $favorites );
		$values[]         = "('{$user->user_id}', '{$key}', '{$favorites_joined}')";

		// Cleanup
		unset( $favorites, $favorites_joined );
	}

	if ( !count( $values ) ) {
		$result = __( 'Nichts zu entfernen!', 'psforum' );
		return array( 0, sprintf( $statement, $result ) );
	}

	$sql_delete = "DELETE FROM `{$wpdb->usermeta}` WHERE `meta_key` = '{$key}';";
	if ( is_wp_error( $wpdb->query( $sql_delete ) ) )
		return array( 4, sprintf( $statement, $result ) );

	foreach ( array_chunk( $values, 10000 ) as $chunk ) {
		$chunk = "\n" . implode( ",\n", $chunk );
		$sql_insert = "INSERT INTO `$wpdb->usermeta` (`user_id`, `meta_key`, `meta_value`) VALUES $chunk;";
		if ( is_wp_error( $wpdb->query( $sql_insert ) ) ) {
			return array( 5, sprintf( $statement, $result ) );
		}
	}

	return array( 0, sprintf( $statement, __( 'Durchgeführt!', 'psforum' ) ) );
}

/**
 * Clean the users' topic subscriptions
 *
 * @since PSForum (r2668)
 *
 * @uses psf_get_topic_post_type() To get the topic post type
 * @uses wpdb::query() To run our recount sql queries
 * @uses is_wp_error() To check if the executed query returned {@link WP_Error}
 * @return array An array of the status code and the message
 */
function psf_admin_repair_user_topic_subscriptions() {
	global $wpdb;

	$statement = __( 'Entfernen von Themen aus dem Papierkorb aus Benutzerabonnements&hellip; %s', 'psforum' );
	$result    = __( 'Fehlgeschlagen!', 'psforum' );
	$key       = $wpdb->prefix . '_psf_subscriptions';
	$users     = $wpdb->get_results( "SELECT `user_id`, `meta_value` AS `subscriptions` FROM `{$wpdb->usermeta}` WHERE `meta_key` = '{$key}';" );

	if ( is_wp_error( $users ) )
		return array( 1, sprintf( $statement, $result ) );

	$topics = $wpdb->get_col( "SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_type` = '" . psf_get_topic_post_type() . "' AND `post_status` = '" . psf_get_public_status_id() . "';" );
	if ( is_wp_error( $topics ) )
		return array( 2, sprintf( $statement, $result ) );

	$values = array();
	foreach ( $users as $user ) {
		if ( empty( $user->subscriptions ) || !is_string( $user->subscriptions ) )
			continue;

		$subscriptions = array_intersect( $topics, explode( ',', $user->subscriptions ) );
		if ( empty( $subscriptions ) || !is_array( $subscriptions ) )
			continue;

		$subscriptions_joined = implode( ',', $subscriptions );
		$values[]             = "('{$user->user_id}', '{$key}', '{$subscriptions_joined}')";

		// Cleanup
		unset( $subscriptions, $subscriptions_joined );
	}

	if ( !count( $values ) ) {
		$result = __( 'Nichts zu entfernen!', 'psforum' );
		return array( 0, sprintf( $statement, $result ) );
	}

	$sql_delete = "DELETE FROM `{$wpdb->usermeta}` WHERE `meta_key` = '{$key}';";
	if ( is_wp_error( $wpdb->query( $sql_delete ) ) )
		return array( 4, sprintf( $statement, $result ) );

	foreach ( array_chunk( $values, 10000 ) as $chunk ) {
		$chunk = "\n" . implode( ",\n", $chunk );
		$sql_insert = "INSERT INTO `{$wpdb->usermeta}` (`user_id`, `meta_key`, `meta_value`) VALUES $chunk;";
		if ( is_wp_error( $wpdb->query( $sql_insert ) ) ) {
			return array( 5, sprintf( $statement, $result ) );
		}
	}

	return array( 0, sprintf( $statement, __( 'Durchgeführt!', 'psforum' ) ) );
}

/**
 * Clean the users' forum subscriptions
 *
 * @since PSForum (r5155)
 *
 * @uses psf_get_forum_post_type() To get the topic post type
 * @uses wpdb::query() To run our recount sql queries
 * @uses is_wp_error() To check if the executed query returned {@link WP_Error}
 * @return array An array of the status code and the message
 */
function psf_admin_repair_user_forum_subscriptions() {
	global $wpdb;

	$statement = __( 'Löschen von gelöschten Foren aus Benutzerabonnements&hellip; %s', 'psforum' );
	$result    = __( 'Fehlgeschlagen!', 'psforum' );
	$key       = $wpdb->prefix . '_psf_forum_subscriptions';
	$users     = $wpdb->get_results( "SELECT `user_id`, `meta_value` AS `subscriptions` FROM `{$wpdb->usermeta}` WHERE `meta_key` = '{$key}';" );

	if ( is_wp_error( $users ) )
		return array( 1, sprintf( $statement, $result ) );

	$forums = $wpdb->get_col( "SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_type` = '" . psf_get_forum_post_type() . "' AND `post_status` = '" . psf_get_public_status_id() . "';" );
	if ( is_wp_error( $forums ) )
		return array( 2, sprintf( $statement, $result ) );

	$values = array();
	foreach ( $users as $user ) {
		if ( empty( $user->subscriptions ) || !is_string( $user->subscriptions ) )
			continue;

		$subscriptions = array_intersect( $forums, explode( ',', $user->subscriptions ) );
		if ( empty( $subscriptions ) || !is_array( $subscriptions ) )
			continue;

		$subscriptions_joined = implode( ',', $subscriptions );
		$values[]             = "('{$user->user_id}', '{$key}', '{$subscriptions_joined}')";

		// Cleanup
		unset( $subscriptions, $subscriptions_joined );
	}

	if ( !count( $values ) ) {
		$result = __( 'Nichts zu entfernen!', 'psforum' );
		return array( 0, sprintf( $statement, $result ) );
	}

	$sql_delete = "DELETE FROM `{$wpdb->usermeta}` WHERE `meta_key` = '{$key}';";
	if ( is_wp_error( $wpdb->query( $sql_delete ) ) )
		return array( 4, sprintf( $statement, $result ) );

	foreach ( array_chunk( $values, 10000 ) as $chunk ) {
		$chunk = "\n" . implode( ",\n", $chunk );
		$sql_insert = "INSERT INTO `{$wpdb->usermeta}` (`user_id`, `meta_key`, `meta_value`) VALUES $chunk;";
		if ( is_wp_error( $wpdb->query( $sql_insert ) ) ) {
			return array( 5, sprintf( $statement, $result ) );
		}
	}

	return array( 0, sprintf( $statement, __( 'Durchgeführt!', 'psforum' ) ) );
}

/**
 * This repair tool will map each user of the current site to their respective
 * forums role. By default, Admins will be Key Masters, and every other role
 * will be the default role defined in Settings > Forums (Participant).
 *
 * @since PSForum (r4340)
 *
 * @uses psf_get_user_role_map() To get the map of user roles
 * @uses get_editable_roles() To get the current WordPress roles
 * @uses get_users() To get the users of each role (limited to ID field)
 * @uses psf_set_user_role() To set each user's forums role
 */
function psf_admin_repair_user_roles() {

	$statement    = __( 'Forenrolle für jeden Benutzer auf dieser Seite neu zuordnen&hellip; %s', 'psforum' );
	$changed      = 0;
	$role_map     = psf_get_user_role_map();
	$default_role = psf_get_default_role();

	// Bail if no role map exists
	if ( empty( $role_map ) )
		return array( 1, sprintf( $statement, __( 'Fehlgeschlagen!', 'psforum' ) ) );

	// Iterate through each role...
	foreach ( array_keys( psf_get_blog_roles() ) as $role ) {

		// Reset the offset
		$offset = 0;

		// If no role map exists, give the default forum role (psf-participant)
		$new_role = isset( $role_map[$role] ) ? $role_map[$role] : $default_role;

		// Get users of this site, limited to 1000
		while ( $users = get_users( array(
				'role'   => $role,
				'fields' => 'ID',
				'number' => 1000,
				'offset' => $offset
			) ) ) {

			// Iterate through each user of $role and try to set it
			foreach ( (array) $users as $user_id ) {
				if ( psf_set_user_role( $user_id, $new_role ) ) {
					++$changed; // Keep a count to display at the end
				}
			}

			// Bump the offset for the next query iteration
			$offset = $offset + 1000;
		}
	}

	$result = sprintf( __( 'Durchgeführt! %s Benutzer aktualisiert.', 'psforum' ), psf_number_format( $changed ) );
	return array( 0, sprintf( $statement, $result ) );
}

/**
 * Recaches the last post in every topic and forum
 *
 * @since PSForum (r3040)
 *
 * @uses wpdb::query() To run our recount sql queries
 * @uses is_wp_error() To check if the executed query returned {@link WP_Error}
 * @return array An array of the status code and the message
 */
function psf_admin_repair_freshness() {
	global $wpdb;

	$statement = __( 'Neuberechnung des neuesten Beitrags in jedem Thema und Forum&hellip; %s', 'psforum' );
	$result    = __( 'Fehlgeschlagen!', 'psforum' );

	// First, delete everything.
	if ( is_wp_error( $wpdb->query( "DELETE FROM `$wpdb->postmeta` WHERE `meta_key` IN ( '_psf_last_reply_id', '_psf_last_topic_id', '_psf_last_active_id', '_psf_last_active_time' );" ) ) )
		return array( 1, sprintf( $statement, $result ) );

	// Next, give all the topics with replies the ID their last reply.
	if ( is_wp_error( $wpdb->query( "INSERT INTO `$wpdb->postmeta` (`post_id`, `meta_key`, `meta_value`)
			( SELECT `topic`.`ID`, '_psf_last_reply_id', MAX( `reply`.`ID` )
			FROM `$wpdb->posts` AS `topic` INNER JOIN `$wpdb->posts` AS `reply` ON `topic`.`ID` = `reply`.`post_parent`
			WHERE `reply`.`post_status` IN ( '" . psf_get_public_status_id() . "' ) AND `topic`.`post_type` = 'topic' AND `reply`.`post_type` = 'reply'
			GROUP BY `topic`.`ID` );" ) ) )
		return array( 2, sprintf( $statement, $result ) );

	// For any remaining topics, give a reply ID of 0.
	if ( is_wp_error( $wpdb->query( "INSERT INTO `$wpdb->postmeta` (`post_id`, `meta_key`, `meta_value`)
			( SELECT `ID`, '_psf_last_reply_id', 0
			FROM `$wpdb->posts` AS `topic` LEFT JOIN `$wpdb->postmeta` AS `reply`
			ON `topic`.`ID` = `reply`.`post_id` AND `reply`.`meta_key` = '_psf_last_reply_id'
			WHERE `reply`.`meta_id` IS NULL AND `topic`.`post_type` = 'topic' );" ) ) )
		return array( 3, sprintf( $statement, $result ) );

	// Now we give all the forums with topics the ID their last topic.
	if ( is_wp_error( $wpdb->query( "INSERT INTO `$wpdb->postmeta` (`post_id`, `meta_key`, `meta_value`)
			( SELECT `forum`.`ID`, '_psf_last_topic_id', MAX( `topic`.`ID` )
			FROM `$wpdb->posts` AS `forum` INNER JOIN `$wpdb->posts` AS `topic` ON `forum`.`ID` = `topic`.`post_parent`
			WHERE `topic`.`post_status` IN ( '" . psf_get_public_status_id() . "' ) AND `forum`.`post_type` = 'forum' AND `topic`.`post_type` = 'topic'
			GROUP BY `forum`.`ID` );" ) ) )
		return array( 4, sprintf( $statement, $result ) );

	// For any remaining forums, give a topic ID of 0.
	if ( is_wp_error( $wpdb->query( "INSERT INTO `$wpdb->postmeta` (`post_id`, `meta_key`, `meta_value`)
			( SELECT `ID`, '_psf_last_topic_id', 0
			FROM `$wpdb->posts` AS `forum` LEFT JOIN `$wpdb->postmeta` AS `topic`
			ON `forum`.`ID` = `topic`.`post_id` AND `topic`.`meta_key` = '_psf_last_topic_id'
			WHERE `topic`.`meta_id` IS NULL AND `forum`.`post_type` = 'forum' );" ) ) )
		return array( 5, sprintf( $statement, $result ) );

	// After that, we give all the topics with replies the ID their last reply (again, this time for a different reason).
	if ( is_wp_error( $wpdb->query( "INSERT INTO `$wpdb->postmeta` (`post_id`, `meta_key`, `meta_value`)
			( SELECT `topic`.`ID`, '_psf_last_active_id', MAX( `reply`.`ID` )
			FROM `$wpdb->posts` AS `topic` INNER JOIN `$wpdb->posts` AS `reply` ON `topic`.`ID` = `reply`.`post_parent`
			WHERE `reply`.`post_status` IN ( '" . psf_get_public_status_id() . "' ) AND `topic`.`post_type` = 'topic' AND `reply`.`post_type` = 'reply'
			GROUP BY `topic`.`ID` );" ) ) )
		return array( 6, sprintf( $statement, $result ) );

	// For any remaining topics, give a reply ID of themself.
	if ( is_wp_error( $wpdb->query( "INSERT INTO `$wpdb->postmeta` (`post_id`, `meta_key`, `meta_value`)
			( SELECT `ID`, '_psf_last_active_id', `ID`
			FROM `$wpdb->posts` AS `topic` LEFT JOIN `$wpdb->postmeta` AS `reply`
			ON `topic`.`ID` = `reply`.`post_id` AND `reply`.`meta_key` = '_psf_last_active_id'
			WHERE `reply`.`meta_id` IS NULL AND `topic`.`post_type` = 'topic' );" ) ) )
		return array( 7, sprintf( $statement, $result ) );

	// Give topics with replies their last update time.
	if ( is_wp_error( $wpdb->query( "INSERT INTO `$wpdb->postmeta` (`post_id`, `meta_key`, `meta_value`)
			( SELECT `topic`.`ID`, '_psf_last_active_time', MAX( `reply`.`post_date` )
			FROM `$wpdb->posts` AS `topic` INNER JOIN `$wpdb->posts` AS `reply` ON `topic`.`ID` = `reply`.`post_parent`
			WHERE `reply`.`post_status` IN ( '" . psf_get_public_status_id() . "' ) AND `topic`.`post_type` = 'topic' AND `reply`.`post_type` = 'reply'
			GROUP BY `topic`.`ID` );" ) ) )
		return array( 8, sprintf( $statement, $result ) );

	// Give topics without replies their last update time.
	if ( is_wp_error( $wpdb->query( "INSERT INTO `$wpdb->postmeta` (`post_id`, `meta_key`, `meta_value`)
			( SELECT `ID`, '_psf_last_active_time', `post_date`
			FROM `$wpdb->posts` AS `topic` LEFT JOIN `$wpdb->postmeta` AS `reply`
			ON `topic`.`ID` = `reply`.`post_id` AND `reply`.`meta_key` = '_psf_last_active_time'
			WHERE `reply`.`meta_id` IS NULL AND `topic`.`post_type` = 'topic' );" ) ) )
		return array( 9, sprintf( $statement, $result ) );

	// Forums need to know what their last active item is as well. Now it gets a bit more complex to do in the database.
	$forums = $wpdb->get_col( "SELECT `ID` FROM `$wpdb->posts` WHERE `post_type` = 'forum' and `post_status` != 'auto-draft';" );
	if ( is_wp_error( $forums ) )
		return array( 10, sprintf( $statement, $result ) );

 	// Loop through forums
 	foreach ( $forums as $forum_id ) {
		if ( !psf_is_forum_category( $forum_id ) ) {
			psf_update_forum( array( 'forum_id' => $forum_id ) );
		}
	}

	// Loop through categories when forums are done
	foreach ( $forums as $forum_id ) {
		if ( psf_is_forum_category( $forum_id ) ) {
			psf_update_forum( array( 'forum_id' => $forum_id ) );
		}
	}

	// Complete results
	return array( 0, sprintf( $statement, __( 'Durchgeführt!', 'psforum' ) ) );
}

/**
 * Repairs the relationship of sticky topics to the actual parent forum
 *
 * @since PSForum (r4695)
 *
 * @uses wpdb::get_col() To run our recount sql queries
 * @uses is_wp_error() To check if the executed query returned {@link WP_Error}
 * @return array An array of the status code and the message
 */
function psf_admin_repair_sticky() {
	global $wpdb;

	$statement = __( 'Reparieren des klebrigen Themas zu den Beziehungen des übergeordneten Forums&hellip; %s', 'psforum' );
	$result    = __( 'Fehlgeschlagen!', 'psforum' );
	$forums    = $wpdb->get_col( "SELECT ID FROM `{$wpdb->posts}` WHERE `post_type` = 'forum';" );

	// Bail if no forums found
	if ( empty( $forums ) || is_wp_error( $forums ) )
		return array( 1, sprintf( $statement, $result ) );

	// Loop through forums and get their sticky topics
	foreach ( $forums as $forum ) {
		$forum_stickies[$forum] = get_post_meta( $forum, '_psf_sticky_topics', true );
	}

	// Cleanup
	unset( $forums, $forum );

	// Loop through each forum with sticky topics
	foreach ( $forum_stickies as $forum_id => $stickies ) {

		// Skip if no stickies
		if ( empty( $stickies ) ) {
			continue;
		}

		// Loop through each sticky topic
		foreach ( $stickies as $id => $topic_id ) {

			// If the topic is not a super sticky, and the forum ID does not
			// match the topic's forum ID, unset the forum's sticky meta.
			if ( ! psf_is_topic_super_sticky( $topic_id ) && $forum_id !== psf_get_topic_forum_id( $topic_id ) ) {
				unset( $forum_stickies[$forum_id][$id] );
			}
		}

		// Get sticky topic ID's, or use empty string
		$stickers = empty( $forum_stickies[$forum_id] ) ? '' : array_values( $forum_stickies[$forum_id] );

		// Update the forum's sticky topics meta
		update_post_meta( $forum_id, '_psf_sticky_topics', $stickers );
	}

	// Complete results
	return array( 0, sprintf( $statement, __( 'Durchgeführt!', 'psforum' ) ) );
}

/**
 * Recaches the private and hidden forums
 *
 * @since PSForum (r4104)
 *
 * @uses delete_option() to delete private and hidden forum pointers
 * @uses WP_Query() To query post IDs
 * @uses is_wp_error() To return if error occurred
 * @uses update_option() To update the private and hidden post ID pointers
 * @return array An array of the status code and the message
 */
function psf_admin_repair_forum_visibility() {
	$statement = __( 'Recalculating forum visibility &hellip; %s', 'psforum' );

	// Bail if queries returned errors
	if ( ! psf_repair_forum_visibility() ) {
		return array( 2, sprintf( $statement, __( 'Fehlgeschlagen!',   'psforum' ) ) );

	// Complete results
	} else {
		return array( 0, sprintf( $statement, __( 'Durchgeführt!', 'psforum' ) ) );
	}
}

/**
 * Recaches the forum for each post
 *
 * @since PSForum (r3876)
 *
 * @uses wpdb::query() To run our recount sql queries
 * @uses is_wp_error() To check if the executed query returned {@link WP_Error}
 * @return array An array of the status code and the message
 */
function psf_admin_repair_forum_meta() {
	global $wpdb;

	$statement = __( 'Neuberechnung des Forums für jeden Beitrag &hellip; %s', 'psforum' );
	$result    = __( 'Fehlgeschlagen!', 'psforum' );

	// First, delete everything.
	if ( is_wp_error( $wpdb->query( "DELETE FROM `$wpdb->postmeta` WHERE `meta_key` = '_psf_forum_id';" ) ) )
		return array( 1, sprintf( $statement, $result ) );

	// Next, give all the topics with replies the ID their last reply.
	if ( is_wp_error( $wpdb->query( "INSERT INTO `$wpdb->postmeta` (`post_id`, `meta_key`, `meta_value`)
			( SELECT `forum`.`ID`, '_psf_forum_id', `forum`.`post_parent`
			FROM `$wpdb->posts`
				AS `forum`
			WHERE `forum`.`post_type` = 'forum'
			GROUP BY `forum`.`ID` );" ) ) )
		return array( 2, sprintf( $statement, $result ) );

	// Next, give all the topics with replies the ID their last reply.
	if ( is_wp_error( $wpdb->query( "INSERT INTO `$wpdb->postmeta` (`post_id`, `meta_key`, `meta_value`)
			( SELECT `topic`.`ID`, '_psf_forum_id', `topic`.`post_parent`
			FROM `$wpdb->posts`
				AS `topic`
			WHERE `topic`.`post_type` = 'topic'
			GROUP BY `topic`.`ID` );" ) ) )
		return array( 3, sprintf( $statement, $result ) );

	// Next, give all the topics with replies the ID their last reply.
	if ( is_wp_error( $wpdb->query( "INSERT INTO `$wpdb->postmeta` (`post_id`, `meta_key`, `meta_value`)
			( SELECT `reply`.`ID`, '_psf_forum_id', `topic`.`post_parent`
			FROM `$wpdb->posts`
				AS `reply`
			INNER JOIN `$wpdb->posts`
				AS `topic`
				ON `reply`.`post_parent` = `topic`.`ID`
			WHERE `topic`.`post_type` = 'topic'
				AND `reply`.`post_type` = 'reply'
			GROUP BY `reply`.`ID` );" ) ) )
		return array( 4, sprintf( $statement, $result ) );

	// Complete results
	return array( 0, sprintf( $statement, __( 'Durchgeführt!', 'psforum' ) ) );
}

/**
 * Recaches the topic for each post
 *
 * @since PSForum (r3876)
 *
 * @uses wpdb::query() To run our recount sql queries
 * @uses is_wp_error() To check if the executed query returned {@link WP_Error}
 * @return array An array of the status code and the message
 */
function psf_admin_repair_topic_meta() {
	global $wpdb;

	$statement = __( 'Neuberechnung des Themas für jeden Beitrag &hellip; %s', 'psforum' );
	$result    = __( 'Fehlgeschlagen!', 'psforum' );

	// First, delete everything.
	if ( is_wp_error( $wpdb->query( "DELETE FROM `$wpdb->postmeta` WHERE `meta_key` = '_psf_topic_id';" ) ) )
		return array( 1, sprintf( $statement, $result ) );

	// Next, give all the topics with replies the ID their last reply.
	if ( is_wp_error( $wpdb->query( "INSERT INTO `$wpdb->postmeta` (`post_id`, `meta_key`, `meta_value`)
			( SELECT `topic`.`ID`, '_psf_topic_id', `topic`.`ID`
			FROM `$wpdb->posts`
				AS `topic`
			WHERE `topic`.`post_type` = 'topic'
			GROUP BY `topic`.`ID` );" ) ) )
		return array( 3, sprintf( $statement, $result ) );

	// Next, give all the topics with replies the ID their last reply.
	if ( is_wp_error( $wpdb->query( "INSERT INTO `$wpdb->postmeta` (`post_id`, `meta_key`, `meta_value`)
			( SELECT `reply`.`ID`, '_psf_topic_id', `topic`.`ID`
			FROM `$wpdb->posts`
				AS `reply`
			INNER JOIN `$wpdb->posts`
				AS `topic`
				ON `reply`.`post_parent` = `topic`.`ID`
			WHERE `topic`.`post_type` = 'topic'
				AND `reply`.`post_type` = 'reply'
			GROUP BY `reply`.`ID` );" ) ) )
		return array( 4, sprintf( $statement, $result ) );

	// Complete results
	return array( 0, sprintf( $statement, __( 'Durchgeführt!', 'psforum' ) ) );
}

/**
 * Recalculate reply menu order
 *
 * @since PSForum (r5367)
 *
 * @uses wpdb::query() To run our recount sql queries
 * @uses is_wp_error() To check if the executed query returned {@link WP_Error}
 * @uses psf_get_reply_post_type() To get the reply post type
 * @uses psf_update_reply_position() To update the reply position
 * @return array An array of the status code and the message
 */
function psf_admin_repair_reply_menu_order() {
	global $wpdb;

	$statement = __( 'Neuberechnung der Reihenfolge des Antwortmenüs &hellip; %s', 'psforum' );
	$result    = __( 'Keine Antwortpositionen neu zu berechnen!', 'psforum' );

	// Delete cases where `_psf_reply_to` was accidentally set to itself
	if ( is_wp_error( $wpdb->query( "DELETE FROM `{$wpdb->postmeta}` WHERE `meta_key` = '_psf_reply_to' AND `post_id` = `meta_value`;" ) ) ) {
		return array( 1, sprintf( $statement, $result ) );
	}

	// Post type
	$rpt = psf_get_reply_post_type();

	// Get an array of reply id's to update the menu oder for each reply
	$replies = $wpdb->get_results( "SELECT `a`.`ID` FROM `{$wpdb->posts}` AS `a`
										INNER JOIN (
											SELECT `menu_order`, `post_parent`
											FROM `{$wpdb->posts}`
											GROUP BY `menu_order`, `post_parent`
											HAVING COUNT( * ) >1
										)`b`
										ON `a`.`menu_order` = `b`.`menu_order`
										AND `a`.`post_parent` = `b`.`post_parent`
										WHERE `post_type` = '{$rpt}';", OBJECT_K );

	// Bail if no replies returned
	if ( empty( $replies ) ) {
		return array( 1, sprintf( $statement, $result ) );
	}

	// Recalculate the menu order position for each reply
	foreach ( $replies as $reply ) {
		psf_update_reply_position( $reply->ID );
	}

	// Cleanup
	unset( $replies, $reply );

	// Flush the cache; things are about to get ugly.
	wp_cache_flush();

	return array( 0, sprintf( $statement, __( 'Durchgeführt!', 'psforum' ) ) );
}

/** Reset ********************************************************************/

/**
 * Admin reset page
 *
 * @since PSForum (r2613)
 *
 * @uses check_admin_referer() To verify the nonce and the referer
 * @uses do_action() Calls 'admin_notices' to display the notices
 * @uses wp_nonce_field() To add a hidden nonce field
 */
function psf_admin_reset() {
?>

	<div class="wrap">

		<h2 class="nav-tab-wrapper"><?php psf_tools_admin_tabs( __( 'Foren zurücksetzen', 'psforum' ) ); ?></h2>
		<p><?php esc_html_e( 'Setze Deine Foren auf eine brandneue Installation zurück. Dieser Vorgang kann nicht rückgängig gemacht werden.', 'psforum' ); ?></p>
		<p><strong><?php esc_html_e( 'Sichere Deine Datenbank, bevor Du fortfährst.', 'psforum' ); ?></strong></p>

		<form class="settings" method="post" action="">
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Folgende Daten werden entfernt:', 'psforum' ) ?></th>
						<td>
							<?php esc_html_e( 'Alle Foren', 'psforum' ); ?><br />
							<?php esc_html_e( 'Alle Themen', 'psforum' ); ?><br />
							<?php esc_html_e( 'Alle Antworten', 'psforum' ); ?><br />
							<?php esc_html_e( 'Alle Themen-Tags', 'psforum' ); ?><br />
							<?php esc_html_e( 'Zugehörige Metadaten', 'psforum' ); ?><br />
							<?php esc_html_e( 'Forumseinstellungen', 'psforum' ); ?><br />
							<?php esc_html_e( 'Forum-Aktivität', 'psforum' ); ?><br />
							<?php esc_html_e( 'Forum-Benutzerrollen', 'psforum' ); ?><br />
							<?php esc_html_e( 'Importeur-Helferdaten', 'psforum' ); ?><br />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Importierte Benutzer löschen?', 'psforum' ); ?></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><span><?php esc_html_e( "Sag, es ist nicht so!", 'psforum' ); ?></span></legend>
								<label><input type="checkbox" class="checkbox" name="psforum-delete-imported-users" id="psforum-delete-imported-users" value="1" /> <?php esc_html_e( 'Diese Option löscht alle zuvor importierten Benutzer und kann nicht rückgängig gemacht werden.', 'psforum' ); ?></label>
								<p class="description"><?php esc_html_e( 'Hinweis: Durch das Zurücksetzen ohne dieses Häkchen werden die Metadaten gelöscht, die zum Löschen dieser Benutzer erforderlich sind.', 'psforum' ); ?></p>
							</fieldset>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Are you sure you want to do this?', 'psforum' ); ?></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><span><?php esc_html_e( "Sag, es ist nicht so!", 'psforum' ); ?></span></legend>
								<label><input type="checkbox" class="checkbox" name="psforum-are-you-sure" id="psforum-are-you-sure" value="1" /> <?php esc_html_e( 'Dieser Vorgang kann nicht rückgängig gemacht werden.', 'psforum' ); ?></label>
								<p class="description"><?php esc_html_e( 'Menschenopfer, Zusammenleben von Hunden und Katzen... Massenhysterie!', 'psforum' ); ?></p>
							</fieldset>
						</td>
					</tr>
				</tbody>
			</table>

			<fieldset class="submit">
				<input class="button-primary" type="submit" name="submit" value="<?php esc_attr_e( 'PS Forum zurücksetzen', 'psforum' ); ?>" />
				<?php wp_nonce_field( 'psforum-reset' ); ?>
			</fieldset>
		</form>
	</div>

<?php
}

/**
 * Handle the processing and feedback of the admin tools page
 *
 * @since PSForum (r2613)
 *
 * @uses check_admin_referer() To verify the nonce and the referer
 * @uses wp_cache_flush() To flush the cache
 */
function psf_admin_reset_handler() {

	// Bail if not resetting
	if ( ! psf_is_post_request() || empty( $_POST['psforum-are-you-sure'] ) )
		return;

	// Only keymasters can proceed
	if ( ! psf_is_user_keymaster() )
		return;

	check_admin_referer( 'psforum-reset' );

	global $wpdb;

	// Stores messages
	$messages = array();
	$failed   = __( 'Fehlgeschlagen',   'psforum' );
	$success  = __( 'Erfolg!', 'psforum' );

	// Flush the cache; things are about to get ugly.
	wp_cache_flush();

	/** Posts *****************************************************************/

	$statement  = __( 'Beiträge löschen&hellip; %s', 'psforum' );
	$sql_posts  = $wpdb->get_results( "SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_type` IN ('forum', 'topic', 'reply')", OBJECT_K );
	$sql_delete = "DELETE FROM `{$wpdb->posts}` WHERE `post_type` IN ('forum', 'topic', 'reply')";
	$result     = is_wp_error( $wpdb->query( $sql_delete ) ) ? $failed : $success;
	$messages[] = sprintf( $statement, $result );

	/** Post Meta *************************************************************/

	if ( !empty( $sql_posts ) ) {
		$sql_meta = array();
		foreach ( $sql_posts as $key => $value ) {
			$sql_meta[] = $key;
		}
		$statement  = __( 'Post-Meta löschen&hellip; %s', 'psforum' );
		$sql_meta   = implode( "', '", $sql_meta );
		$sql_delete = "DELETE FROM `{$wpdb->postmeta}` WHERE `post_id` IN ('{$sql_meta}');";
		$result     = is_wp_error( $wpdb->query( $sql_delete ) ) ? $failed : $success;
		$messages[] = sprintf( $statement, $result );
	}

	/** Topic Tags ************************************************************/

	$statement  = __( 'Themen-Tags löschen&hellip; %s', 'psforum' );
	$sql_delete = "DELETE a,b,c FROM `{$wpdb->terms}` AS a LEFT JOIN `{$wpdb->term_taxonomy}` AS c ON a.term_id = c.term_id LEFT JOIN `{$wpdb->term_relationships}` AS b ON b.term_taxonomy_id = c.term_taxonomy_id WHERE c.taxonomy = 'topic-tag';";
	$result     = is_wp_error( $wpdb->query( $sql_delete ) ) ? $failed : $success;
	$messages[] = sprintf( $statement, $result );

	/** User ******************************************************************/

	// Delete users
	if ( !empty( $_POST['psforum-delete-imported-users'] ) ) {
		$sql_users  = $wpdb->get_results( "SELECT `user_id` FROM `{$wpdb->usermeta}` WHERE `meta_key` = '_psf_user_id'", OBJECT_K );
		if ( !empty( $sql_users ) ) {
			$sql_meta = array();
			foreach ( $sql_users as $key => $value ) {
				$sql_meta[] = $key;
			}
			$statement  = __( 'Benutzer löschen&hellip; %s', 'psforum' );
			$sql_meta   = implode( "', '", $sql_meta );
			$sql_delete = "DELETE FROM `{$wpdb->users}` WHERE `ID` IN ('{$sql_meta}');";
			$result     = is_wp_error( $wpdb->query( $sql_delete ) ) ? $failed : $success;
			$messages[] = sprintf( $statement, $result );
			$statement  = __( 'Löschen von Benutzer-Meta&hellip; %s', 'psforum' );
			$sql_delete = "DELETE FROM `{$wpdb->usermeta}` WHERE `user_id` IN ('{$sql_meta}');";
			$result     = is_wp_error( $wpdb->query( $sql_delete ) ) ? $failed : $success;
			$messages[] = sprintf( $statement, $result );
		}

	// Delete imported user metadata
	} else {
		$statement  = __( 'Löschen von Benutzer-Meta&hellip; %s', 'psforum' );
		$sql_delete = "DELETE FROM `{$wpdb->usermeta}` WHERE `meta_key` LIKE '%%_psf_%%';";
		$result     = is_wp_error( $wpdb->query( $sql_delete ) ) ? $failed : $success;
		$messages[] = sprintf( $statement, $result );
	}

	/** Converter *************************************************************/

	$statement  = __( 'Umrechnungstabelle löschen&hellip; %s', 'psforum' );
	$table_name = $wpdb->prefix . 'psf_converter_translator';
	if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) === $table_name ) {
		$wpdb->query( "DROP TABLE {$table_name}" );
		$result = $success;
	} else {
		$result = $failed;
	}
	$messages[] = sprintf( $statement, $result );

	/** Options ***************************************************************/

	$statement  = __( 'Einstellungen löschen&hellip; %s', 'psforum' );
	psf_delete_options();
	$messages[] = sprintf( $statement, $success );

	/** Roles *****************************************************************/

	$statement  = __( 'Löschen von Rollen und Funktionen&hellip; %s', 'psforum' );
	remove_role( psf_get_moderator_role() );
	remove_role( psf_get_participant_role() );
	psf_remove_caps();
	$messages[] = sprintf( $statement, $success );

	/** Output ****************************************************************/

	if ( count( $messages ) ) {
		foreach ( $messages as $message ) {
			psf_admin_tools_feedback( $message );
		}
	}
}
