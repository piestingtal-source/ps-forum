<?php

/**
 * PSForum BuddyPress Group Extension Class
 *
 * This file is responsible for connecting PSForum to BuddyPress's Groups
 * Component. It's a great example of how to perform both simple and advanced
 * techniques to manipulate PSForum's default output.
 *
 * @package PSForum
 * @subpackage BuddyPress
 * @todo maybe move to BuddyPress Forums once PSForum 1.1 can be removed
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'PSF_Forums_Group_Extension' ) && class_exists( 'BP_Group_Extension' ) ) :
/**
 * Loads Group Extension for Forums Component
 *
 * @since PSForum (r3552)
 *
 * @package PSForum
 * @subpackage BuddyPress
 * @todo Everything
 */
class PSF_Forums_Group_Extension extends BP_Group_Extension {

	/** Methods ***************************************************************/

	/**
	 * Setup PSForum group extension variables
	 *
	 * @since PSForum (r3552)
	 */
	public function __construct() {
		$this->setup_variables();
		$this->setup_actions();
		$this->setup_filters();
		$this->maybe_unset_forum_menu();
	}

	/**
	 * Setup the group forums class variables
	 *
	 * @since PSForum ()
	 */
	private function setup_variables() {

		// Component Name
		$this->name          = __( 'Forum', 'psforum' );
		$this->nav_item_name = __( 'Forum', 'psforum' );

		// Component slugs (hardcoded to match PSForum 1.x functionality)
		$this->slug          = 'forum';
		$this->topic_slug    = 'topic';
		$this->reply_slug    = 'reply';

		// Forum component is visible
		$this->visibility = 'public';

		// Set positions towards end
		$this->create_step_position = 15;
		$this->nav_item_position    = 10;

		// Allow create step and show in nav
		$this->enable_create_step   = true;
		$this->enable_nav_item      = true;
		$this->enable_edit_item     = true;

		// Template file to load, and action to hook display on to
		$this->template_file        = 'groups/single/plugins';
		$this->display_hook         = 'bp_template_content';
	}

	/**
	 * Setup the group forums class actions
	 *
	 * @since PSForum (r4552)
	 */
	private function setup_actions() {

		// Possibly redirect
		add_action( 'psf_template_redirect',         array( $this, 'redirect_canonical'              ) );

		// Remove group forum cap map when view is done
		add_action( 'psf_after_group_forum_display', array( $this, 'remove_group_forum_meta_cap_map' ) );

		// PSForum needs to listen to BuddyPress group deletion.
		add_action( 'groups_before_delete_group',    array( $this, 'disconnect_forum_from_group'     ) );

		// Adds a PSForum metabox to the new BuddyPress Group Admin UI
		add_action( 'bp_groups_admin_meta_boxes',    array( $this, 'group_admin_ui_edit_screen'      ) );

		// Saves the PSForum options if they come from the BuddyPress Group Admin UI
		add_action( 'bp_group_admin_edit_after',     array( $this, 'edit_screen_save'                ) );

		// Adds a hidden input value to the "Group Settings" page
		add_action( 'bp_before_group_settings_admin', array( $this, 'group_settings_hidden_field'    ) );
	}

	/**
	 * Setup the group forums class filters
	 *
	 * @since PSForum (r4552)
	 */
	private function setup_filters() {

		// Group forum pagination
		add_filter( 'psf_topic_pagination',      array( $this, 'topic_pagination'   ) );
		add_filter( 'psf_replies_pagination',    array( $this, 'replies_pagination' ) );

		// Tweak the redirect field
		add_filter( 'psf_new_topic_redirect_to', array( $this, 'new_topic_redirect_to'        ), 10, 3 );
		add_filter( 'psf_new_reply_redirect_to', array( $this, 'new_reply_redirect_to'        ), 10, 3 );

		// Map forum/topic/replys permalinks to their groups
		add_filter( 'psf_get_forum_permalink',   array( $this, 'map_forum_permalink_to_group' ), 10, 2 );
		add_filter( 'psf_get_topic_permalink',   array( $this, 'map_topic_permalink_to_group' ), 10, 2 );
		add_filter( 'psf_get_reply_permalink',   array( $this, 'map_reply_permalink_to_group' ), 10, 2 );

		// Map reply edit links to their groups
		add_filter( 'psf_get_reply_edit_url',    array( $this, 'map_reply_edit_url_to_group'  ), 10, 2 );

		// Map assorted template function permalinks
		add_filter( 'post_link',                 array( $this, 'post_link'                    ), 10, 2 );
		add_filter( 'page_link',                 array( $this, 'page_link'                    ), 10, 2 );
		add_filter( 'post_type_link',            array( $this, 'post_type_link'               ), 10, 2 );

		// Map group forum activity items to groups
		add_filter( 'psf_before_record_activity_parse_args', array( $this, 'map_activity_to_group' ) );

		/** Caps **************************************************************/

		// Only add these filters if inside a group forum
		if ( bp_is_single_item() && bp_is_groups_component() && bp_is_current_action( 'forum' ) ) {

			// Allow group member to view private/hidden forums
			add_filter( 'psf_map_meta_caps', array( $this, 'map_group_forum_meta_caps' ), 10, 4 );

			// Group member permissions to view the topic and reply forms
			add_filter( 'psf_current_user_can_access_create_topic_form', array( $this, 'form_permissions' ) );
			add_filter( 'psf_current_user_can_access_create_reply_form', array( $this, 'form_permissions' ) );
		}
	}

	/**
	 * The primary display function for group forums
	 *
	 * @since PSForum (r3746)
	 *
	 * @param int $group_id ID of the current group. Available only on BP 2.2+.
	 */
	public function display( $group_id = null ) {

		// Prevent Topic Parent from appearing
		add_action( 'psf_theme_before_topic_form_forum', array( $this, 'ob_start'     ) );
		add_action( 'psf_theme_after_topic_form_forum',  array( $this, 'ob_end_clean' ) );
		add_action( 'psf_theme_after_topic_form_forum',  array( $this, 'topic_parent' ) );

		// Prevent Forum Parent from appearing
		add_action( 'psf_theme_before_forum_form_parent', array( $this, 'ob_start'     ) );
		add_action( 'psf_theme_after_forum_form_parent',  array( $this, 'ob_end_clean' ) );
		add_action( 'psf_theme_after_forum_form_parent',  array( $this, 'forum_parent' ) );

		// Hide breadcrumb
		add_filter( 'psf_no_breadcrumb', '__return_true' );

		$this->display_forums( 0 );
	}

	/**
	 * Maybe unset the group forum nav item if group does not have a forum
	 *
	 * @since PSForum (r4552)
	 *
	 * @return If not viewing a single group
	 */
	public function maybe_unset_forum_menu() {

		// Bail if not viewing a single group
		if ( ! bp_is_group() )
			return;

		// Are forums enabled for this group?
		$checked = bp_get_new_group_enable_forum() || groups_get_groupmeta( bp_get_new_group_id(), 'forum_id' );

		// Tweak the nav item variable based on if group has forum or not
		$this->enable_nav_item = (bool) $checked;
	}

	/**
	 * Allow group members to have advanced priviledges in group forum topics.
	 *
	 * @since PSForum (r4434)
	 *
	 * @param array $caps
	 * @param string $cap
	 * @param int $user_id
	 * @param array $args
	 * @return array
	 */
	public function map_group_forum_meta_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {

		switch ( $cap ) {

			// If user is a group mmember, allow them to create content.
			case 'read_forum'          :
			case 'publish_replies'     :
			case 'publish_topics'      :
			case 'read_hidden_forums'  :
			case 'read_private_forums' :
				if ( psf_group_is_member() || psf_group_is_mod() || psf_group_is_admin() ) {
					$caps = array( 'participate' );
				}
				break;

			// If user is a group mod ar admin, map to participate cap.
			case 'moderate'     :
			case 'edit_topic'   :
			case 'edit_reply'   :
			case 'view_trash'   :
			case 'edit_others_replies' :
			case 'edit_others_topics'  :
				if ( psf_group_is_mod() || psf_group_is_admin() ) {
					$caps = array( 'participate' );
				}
				break;

			// If user is a group admin, allow them to delete topics and replies.
			case 'delete_topic' :
			case 'delete_reply' :
				if ( psf_group_is_admin() ) {
					$caps = array( 'participate' );
				}
				break;
		}

		return apply_filters( 'psf_map_group_forum_topic_meta_caps', $caps, $cap, $user_id, $args );
	}

	/**
	 * Remove the topic meta cap map, so it doesn't interfere with sidebars.
	 *
	 * @since PSForum (r4434)
	 */
	public function remove_group_forum_meta_cap_map() {
		remove_filter( 'psf_map_meta_caps', array( $this, 'map_group_forum_meta_caps' ), 99, 4 );
	}

	/** Edit ******************************************************************/

	/**
	 * Show forums and new forum form when editing a group
	 *
	 * @since PSForum (r3563)
	 * @param object $group (the group to edit if in Group Admin UI)
	 * @uses is_admin() To check if we're in the Group Admin UI
	 * @uses psf_get_template_part()
	 */
	public function edit_screen( $group = false ) {
		$forum_id  = 0;
		$group_id  = empty( $group->id ) ? bp_get_new_group_id() : $group->id ;
		$forum_ids = psf_get_group_forum_ids( $group_id );

		// Get the first forum ID
		if ( !empty( $forum_ids ) ) {
			$forum_id = (int) is_array( $forum_ids ) ? $forum_ids[0] : $forum_ids;
		}

		// Should box be checked already?
		$checked = is_admin() ? bp_group_is_forum_enabled( $group ) : bp_get_new_group_enable_forum() || bp_group_is_forum_enabled( bp_get_group_id() ); ?>

		<h4><?php esc_html_e( 'Gruppenforumeinstellungen', 'psforum' ); ?></h4>

		<fieldset>
			<legend class="screen-reader-text"><?php esc_html_e( 'Gruppenforumeinstellungen', 'psforum' ); ?></legend>
			<p><?php esc_html_e( 'Erstelle ein Diskussionsforum, um den Mitgliedern dieser Gruppe eine strukturierte Kommunikation im Stil eines Bulletinboards zu ermöglichen.', 'psforum' ); ?></p>

			<div class="field-group">
				<div class="checkbox">
					<label><input type="checkbox" name="psf-edit-group-forum" id="psf-edit-group-forum" value="1"<?php checked( $checked ); ?> /> <?php esc_html_e( 'Jawohl. Ich möchte, dass diese Gruppe ein Forum hat.', 'psforum' ); ?></label>
				</div>

				<p class="description"><?php esc_html_e( 'Wenn Du Nein sagst, werden vorhandene Foreninhalte nicht gelöscht.', 'psforum' ); ?></p>
			</div>

			<?php if ( psf_is_user_keymaster() ) : ?>
				<div class="field-group">
					<label for="psf_group_forum_id"><?php esc_html_e( 'Gruppenforum:', 'psforum' ); ?></label>
					<?php
						psf_dropdown( array(
							'select_id' => 'psf_group_forum_id',
							'show_none' => __( '(Kein Forum)', 'psforum' ),
							'selected'  => $forum_id
						) );
					?>
					<p class="description"><?php esc_html_e( 'Netzwerkadministratoren können neu konfigurieren, welches Forum zu dieser Gruppe gehört.', 'psforum' ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( !is_admin() ) : ?>
				<input type="submit" value="<?php esc_attr_e( 'Einstellungen speichern', 'psforum' ); ?>" />
			<?php endif; ?>

		</fieldset>

		<?php

		// Verify intent
		if ( is_admin() ) {
			wp_nonce_field( 'groups_edit_save_' . $this->slug, 'forum_group_admin_ui' );
		} else {
			wp_nonce_field( 'groups_edit_save_' . $this->slug );
		}
	}

	/**
	 * Save the Group Forum data on edit
	 *
	 * @since PSForum (r3465)
	 * @param int $group_id (to handle Group Admin UI hook bp_group_admin_edit_after )
	 * @uses psf_new_forum_handler() To check for forum creation
	 * @uses psf_edit_forum_handler() To check for forum edit
	 */
	public function edit_screen_save( $group_id = 0 ) {

		// Bail if not a POST action
		if ( ! psf_is_post_request() )
			return;

		// Admin Nonce check
		if ( is_admin() ) {
			check_admin_referer( 'groups_edit_save_' . $this->slug, 'forum_group_admin_ui' );

		// Theme-side Nonce check
		} elseif ( ! psf_verify_nonce_request( 'groups_edit_save_' . $this->slug ) ) {
			psf_add_error( 'psf_edit_group_forum_screen_save', __( '<strong>FEHLER</strong>: Willst Du das wirklich?', 'psforum' ) );
			return;
 		}

		$edit_forum = !empty( $_POST['psf-edit-group-forum'] ) ? true : false;
		$forum_id   = 0;
		$group_id   = !empty( $group_id ) ? $group_id : bp_get_current_group_id();

		// Keymasters have the ability to reconfigure forums
		if ( psf_is_user_keymaster() ) {
			$forum_ids = ! empty( $_POST['psf_group_forum_id'] ) ? (array) (int) $_POST['psf_group_forum_id'] : array();

		// Use the existing forum IDs
		} else {
			$forum_ids = array_values( psf_get_group_forum_ids( $group_id ) );
		}

		// Normalize group forum relationships now
		if ( !empty( $forum_ids ) ) {

			// Loop through forums, and make sure they exist
			foreach ( $forum_ids as $forum_id ) {

				// Look for forum
				$forum = psf_get_forum( $forum_id );

				// No forum exists, so break the relationship
				if ( empty( $forum ) ) {
					$this->remove_forum( array( 'forum_id' => $forum_id ) );
					unset( $forum_ids[$forum_id] );
				}
			}

			// No support for multiple forums yet
			$forum_id = (int) ( is_array( $forum_ids ) ? $forum_ids[0] : $forum_ids );
		}

		// Update the group ID and forum ID relationships
		psf_update_group_forum_ids( $group_id, (array) $forum_ids );
		psf_update_forum_group_ids( $forum_id, (array) $group_id  );

		// Update the group forum setting
		$group = $this->toggle_group_forum( $group_id, $edit_forum );

		// Create a new forum
		if ( empty( $forum_id ) && ( true === $edit_forum ) ) {

			// Set the default forum status
			switch ( $group->status ) {
				case 'hidden'  :
					$status = psf_get_hidden_status_id();
					break;
				case 'private' :
					$status = psf_get_private_status_id();
					break;
				case 'public'  :
				default        :
					$status = psf_get_public_status_id();
					break;
			}

			// Create the initial forum
			$forum_id = psf_insert_forum( array(
				'post_parent'  => psf_get_group_forums_root_id(),
				'post_title'   => $group->name,
				'post_content' => $group->description,
				'post_status'  => $status
			) );

			// Setup forum args with forum ID
			$new_forum_args = array( 'forum_id' => $forum_id );

			// If in admin, also include the group ID
			if ( is_admin() && !empty( $group_id ) ) {
				$new_forum_args['group_id'] = $group_id;
			}

			// Run the BP-specific functions for new groups
			$this->new_forum( $new_forum_args );
		}

		// Redirect after save when not in admin
		if ( !is_admin() ) {
			bp_core_redirect( trailingslashit( bp_get_group_permalink( buddypress()->groups->current_group ) . '/admin/' . $this->slug ) );
		}
	}

	/**
	 * Adds a metabox to BuddyPress Group Admin UI
	 *
	 * @since PSForum (r4814)
	 *
	 * @uses add_meta_box
	 * @uses PSF_Forums_Group_Extension::group_admin_ui_display_metabox() To display the edit screen
	 */
	public function group_admin_ui_edit_screen() {
		add_meta_box(
			'psforum_group_admin_ui_meta_box',
			_x( 'Diskussionsforum', 'group admin edit screen', 'psforum' ),
			array( $this, 'group_admin_ui_display_metabox' ),
			get_current_screen()->id,
			'side',
			'core'
		);
	}

	/**
	 * Displays the PSForum metabox in BuddyPress Group Admin UI
	 *
	 * @since PSForum (r4814)
	 *
	 * @param object $item (group object)
	 * @uses add_meta_box
	 * @uses PSF_Forums_Group_Extension::edit_screen() To get the html
	 */
	public function group_admin_ui_display_metabox( $item ) {
		$this->edit_screen( $item );
	}

	/** Create ****************************************************************/

	/**
	 * Show forums and new forum form when creating a group
	 *
	 * @since PSForum (r3465)
	 */
	public function create_screen( $group_id = 0 ) {

		// Bail if not looking at this screen
		if ( !bp_is_group_creation_step( $this->slug ) )
			return false;

		// Check for possibly empty group_id
		if ( empty( $group_id ) ) {
			$group_id = bp_get_new_group_id();
		}

		$checked = bp_get_new_group_enable_forum() || groups_get_groupmeta( $group_id, 'forum_id' ); ?>

		<h4><?php esc_html_e( 'Gruppenforum', 'psforum' ); ?></h4>

		<p><?php esc_html_e( 'Erstelle ein Diskussionsforum, um den Mitgliedern dieser Gruppe eine strukturierte Kommunikation im Stil eines Bulletinboards zu ermöglichen.', 'psforum' ); ?></p>

		<div class="checkbox">
			<label><input type="checkbox" name="psf-create-group-forum" id="psf-create-group-forum" value="1"<?php checked( $checked ); ?> /> <?php esc_html_e( 'Jawohl. Ich möchte, dass diese Gruppe ein Forum hat.', 'psforum' ); ?></label>
		</div>

		<?php
	}

	/**
	 * Save the Group Forum data on create
	 *
	 * @since PSForum (r3465)
	 */
	public function create_screen_save( $group_id = 0 ) {

		// Nonce check
		if ( ! psf_verify_nonce_request( 'groups_create_save_' . $this->slug ) ) {
			psf_add_error( 'psf_create_group_forum_screen_save', __( '<strong>FEHLER</strong>: Willst Du das wirklich?', 'psforum' ) );
			return;
		}

		// Check for possibly empty group_id
		if ( empty( $group_id ) ) {
			$group_id = bp_get_new_group_id();
		}

		$create_forum = !empty( $_POST['psf-create-group-forum'] ) ? true : false;
		$forum_id     = 0;
		$forum_ids    = psf_get_group_forum_ids( $group_id );

		if ( !empty( $forum_ids ) )
			$forum_id = (int) is_array( $forum_ids ) ? $forum_ids[0] : $forum_ids;

		// Create a forum, or not
		switch ( $create_forum ) {
			case true  :

				// Bail if initial content was already created
				if ( !empty( $forum_id ) )
					return;

				// Set the default forum status
				switch ( bp_get_new_group_status() ) {
					case 'hidden'  :
						$status = psf_get_hidden_status_id();
						break;
					case 'private' :
						$status = psf_get_private_status_id();
						break;
					case 'public'  :
					default        :
						$status = psf_get_public_status_id();
						break;
				}

				// Create the initial forum
				$forum_id = psf_insert_forum( array(
					'post_parent'  => psf_get_group_forums_root_id(),
					'post_title'   => bp_get_new_group_name(),
					'post_content' => bp_get_new_group_description(),
					'post_status'  => $status
				) );

				// Run the BP-specific functions for new groups
				$this->new_forum( array( 'forum_id' => $forum_id ) );

				// Update forum active
				groups_update_groupmeta( bp_get_new_group_id(), '_psf_forum_enabled_' . $forum_id, true );

				// Toggle forum on
				$this->toggle_group_forum( bp_get_new_group_id(), true );

				break;
			case false :

				// Forum was created but is now being undone
				if ( !empty( $forum_id ) ) {

					// Delete the forum
					wp_delete_post( $forum_id, true );

					// Delete meta values
					groups_delete_groupmeta( bp_get_new_group_id(), 'forum_id' );
					groups_delete_groupmeta( bp_get_new_group_id(), '_psf_forum_enabled_' . $forum_id );

					// Toggle forum off
					$this->toggle_group_forum( bp_get_new_group_id(), false );
				}

				break;
		}
	}

	/**
	 * Used to start an output buffer
	 */
	public function ob_start() {
		ob_start();
	}

	/**
	 * Used to end an output buffer
	 */
	public function ob_end_clean() {
		ob_end_clean();
	}

	/**
	 * Creating a group forum or category (including root for group)
	 *
	 * @since PSForum (r3653)
	 * @param type $forum_args
	 * @uses psf_get_forum_id()
	 * @uses bp_get_current_group_id()
	 * @uses psf_add_forum_id_to_group()
	 * @uses psf_add_group_id_to_forum()
	 * @return if no forum_id is available
	 */
	public function new_forum( $forum_args = array() ) {

		// Bail if no forum_id was passed
		if ( empty( $forum_args['forum_id'] ) )
			return;

		// Validate forum_id
		$forum_id = psf_get_forum_id( $forum_args['forum_id'] );
		$group_id = !empty( $forum_args['group_id'] ) ? $forum_args['group_id'] : bp_get_current_group_id();

		psf_add_forum_id_to_group( $group_id, $forum_id );
		psf_add_group_id_to_forum( $forum_id, $group_id );
	}

	/**
	 * Removing a group forum or category (including root for group)
	 *
	 * @since PSForum (r3653)
	 * @param type $forum_args
	 * @uses psf_get_forum_id()
	 * @uses bp_get_current_group_id()
	 * @uses psf_add_forum_id_to_group()
	 * @uses psf_add_group_id_to_forum()
	 * @return if no forum_id is available
	 */
	public function remove_forum( $forum_args = array() ) {

		// Bail if no forum_id was passed
		if ( empty( $forum_args['forum_id'] ) )
			return;

		// Validate forum_id
		$forum_id = psf_get_forum_id( $forum_args['forum_id'] );
		$group_id = !empty( $forum_args['group_id'] ) ? $forum_args['group_id'] : bp_get_current_group_id();

		psf_remove_forum_id_from_group( $group_id, $forum_id );
		psf_remove_group_id_from_forum( $forum_id, $group_id );
	}

	/**
	 * Listening to BuddyPress Group deletion to remove the forum
	 *
	 * @param int $group_id The group ID
	 * @uses psf_get_group_forum_ids()
	 * @uses PSF_Forums_Group_Extension::remove_forum()
	 */
	public function disconnect_forum_from_group( $group_id = 0 ) {

		// Bail if no group ID available
		if ( empty( $group_id ) ) {
			return;
		}

		// Get the forums for the current group
		$forum_ids = psf_get_group_forum_ids( $group_id );

		// Use the first forum ID
		if ( empty( $forum_ids ) )
			return;

		// Get the first forum ID
		$forum_id = (int) is_array( $forum_ids ) ? $forum_ids[0] : $forum_ids;
		$this->remove_forum( array(
			'forum_id' => $forum_id,
			'group_id' => $group_id
		) );
	}

	/**
	 * Toggle the enable_forum group setting on or off
	 *
	 * @since PSForum (r4612)
	 *
	 * @param int $group_id The group to toggle
	 * @param bool $enabled True for on, false for off
	 * @uses groups_get_group() To get the group to toggle
	 * @return False if group is not found, otherwise return the group
	 */
	public function toggle_group_forum( $group_id = 0, $enabled = false ) {

		// Get the group
		$group = groups_get_group( array( 'group_id' => $group_id ) );

		// Bail if group cannot be found
		if ( empty( $group ) )
			return false;

		// Set forum enabled status
		$group->enable_forum = (int) $enabled;

		// Save the group
		$group->save();

		// Maybe disconnect forum from group
		if ( empty( $enabled ) ) {
			$this->disconnect_forum_from_group( $group_id );
		}

		// Update PSForum' internal private and forum ID variables
		psf_repair_forum_visibility();

		// Return the group
		return $group;
	}

	/** Display Methods *******************************************************/

	/**
	 * Output the forums for a group in the edit screens
	 *
	 * As of right now, PSForum only supports 1-to-1 group forum relationships.
	 * In the future, many-to-many should be allowed.
	 *
	 * @since PSForum (r3653)
	 * @uses bp_get_current_group_id()
	 * @uses psf_get_group_forum_ids()
	 * @uses psf_has_forums()
	 * @uses psf_get_template_part()
	 */
	public function display_forums( $offset = 0 ) {
		global $wp_query;

		// Allow actions immediately before group forum output
		do_action( 'psf_before_group_forum_display' );

		// Load up PSForum once
		$psf = psforum();

		/** Query Resets ******************************************************/

		// Forum data
		$forum_action = bp_action_variable( $offset );
		$forum_ids    = psf_get_group_forum_ids( bp_get_current_group_id() );
		$forum_id     = array_shift( $forum_ids );

		// Always load up the group forum
		psf_has_forums( array(
			'p'           => $forum_id,
			'post_parent' => null
		) );

		// Set the global forum ID
		$psf->current_forum_id = $forum_id;

		// Assume forum query
		psf_set_query_name( 'psf_single_forum' ); ?>

		<div id="psforum-forums">

			<?php switch ( $forum_action ) :

				/** Single Forum **********************************************/

				case false  :
				case 'page' :

					// Strip the super stickies from topic query
					add_filter( 'psf_get_super_stickies', array( $this, 'no_super_stickies'  ), 10, 1 );

					// Unset the super sticky option on topic form
					add_filter( 'psf_get_topic_types',    array( $this, 'unset_super_sticky' ), 10, 1 );

					// Query forums and show them if they exist
					if ( psf_forums() ) :

						// Setup the forum
						psf_the_forum(); ?>

						<h3><?php psf_forum_title(); ?></h3>

						<?php psf_get_template_part( 'content', 'single-forum' );

					// No forums found
					else : ?>

						<div id="message" class="info">
							<p><?php esc_html_e( 'Diese Gruppe hat derzeit kein Forum.', 'psforum' ); ?></p>
						</div>

					<?php endif;

					break;

				/** Single Topic **********************************************/

				case $this->topic_slug :

					// hide the 'to front' admin links
					add_filter( 'psf_get_topic_stick_link', array( $this, 'hide_super_sticky_admin_link' ), 10, 2 );

					// Get the topic
					psf_has_topics( array(
						'name'           => bp_action_variable( $offset + 1 ),
						'posts_per_page' => 1,
						'show_stickies'  => false
					) );

					// If no topic, 404
					if ( ! psf_topics() ) {
						bp_do_404( psf_get_forum_permalink( $forum_id ) ); ?>
						<h3><?php psf_forum_title(); ?></h3>
						<?php psf_get_template_part( 'feedback', 'no-topics' );
						return;
					}

					// Setup the topic
					psf_the_topic(); ?>

					<h3><?php psf_topic_title(); ?></h3>

					<?php

					// Topic edit
					if ( bp_action_variable( $offset + 2 ) === psf_get_edit_rewrite_id() ) :

						// Unset the super sticky link on edit topic template
						add_filter( 'psf_get_topic_types', array( $this, 'unset_super_sticky' ), 10, 1 );

						// Set the edit switches
						$wp_query->psf_is_edit       = true;
						$wp_query->psf_is_topic_edit = true;

						// Setup the global forum ID
						$psf->current_topic_id       = get_the_ID();

						// Merge
						if ( !empty( $_GET['action'] ) && 'merge' === $_GET['action'] ) :
							psf_set_query_name( 'psf_topic_merge' );
							psf_get_template_part( 'form', 'topic-merge' );

						// Split
						elseif ( !empty( $_GET['action'] ) && 'split' === $_GET['action'] ) :
							psf_set_query_name( 'psf_topic_split' );
							psf_get_template_part( 'form', 'topic-split' );

						// Edit
						else :
							psf_set_query_name( 'psf_topic_form' );
							psf_get_template_part( 'form', 'topic' );

						endif;

					// Single Topic
					else:
						psf_set_query_name( 'psf_single_topic' );
						psf_get_template_part( 'content', 'single-topic' );
					endif;
					break;

				/** Single Reply **********************************************/

				case $this->reply_slug :

					// Get the reply
					psf_has_replies( array(
						'name'           => bp_action_variable( $offset + 1 ),
						'posts_per_page' => 1
					) );

					// If no topic, 404
					if ( ! psf_replies() ) {
						bp_do_404( psf_get_forum_permalink( $forum_id ) ); ?>
						<h3><?php psf_forum_title(); ?></h3>
						<?php psf_get_template_part( 'feedback', 'no-replies' );
						return;
					}

					// Setup the reply
					psf_the_reply(); ?>

					<h3><?php psf_reply_title(); ?></h3>

					<?php if ( bp_action_variable( $offset + 2 ) === psf_get_edit_rewrite_id() ) :

						// Set the edit switches
						$wp_query->psf_is_edit       = true;
						$wp_query->psf_is_reply_edit = true;

						// Setup the global reply ID
						$psf->current_reply_id       = get_the_ID();

						// Move
						if ( !empty( $_GET['action'] ) && ( 'move' === $_GET['action'] ) ) :
							psf_set_query_name( 'psf_reply_move' );
							psf_get_template_part( 'form', 'reply-move' );

						// Edit
						else :
							psf_set_query_name( 'psf_reply_form' );
							psf_get_template_part( 'form', 'reply' );
						endif;
					endif;
					break;
			endswitch;

			// Reset the query
			wp_reset_query(); ?>

		</div>

		<?php

		// Allow actions immediately after group forum output
		do_action( 'psf_after_group_forum_display' );
	}

	/** Super sticky filters ***************************************************/

	/**
	 * Strip super stickies from the topic query
	 *
	 * @since PSForum (r4810)
	 * @access private
	 * @param array $super the super sticky post ID's
	 * @return array (empty)
	 */
	public function no_super_stickies( $super = array() ) {
		$super = array();
		return $super;
	}

	/**
	 * Unset the type super sticky from topic type
	 *
	 * @since PSForum (r4810)
	 * @access private
	 * @param array $args
	 * @return array $args without the to-front link
	 */
	public function unset_super_sticky( $args = array() ) {
		if ( isset( $args['super'] ) ) {
			unset( $args['super'] );
		}
		return $args;
	}

	/**
	 * Ugly preg_replace to hide the to front admin link
	 *
	 * @since PSForum (r4810)
	 * @access private
	 * @param string $retval
	 * @param array $args
	 * @return string $retval without the to-front link
	 */
	public function hide_super_sticky_admin_link( $retval = '', $args = array() ) {
		if ( strpos( $retval, '(' ) ) {
			$retval = preg_replace( '/(\(.+?)+(\))/i', '', $retval );
		}

		return $retval;
	}

	/** Redirect Helpers ******************************************************/

	/**
	 * Redirect to the group forum screen
	 *
	 * @since PSForum (r3653)
	 * @param str $redirect_url
	 * @param str $redirect_to
	 */
	public function new_topic_redirect_to( $redirect_url = '', $redirect_to = '', $topic_id = 0 ) {
		if ( bp_is_group() ) {
			$topic        = psf_get_topic( $topic_id );
			$topic_hash   = '#post-' . $topic_id;
			$redirect_url = trailingslashit( bp_get_group_permalink( groups_get_current_group() ) ) . trailingslashit( $this->slug ) . trailingslashit( $this->topic_slug ) . trailingslashit( $topic->post_name ) . $topic_hash;
		}

		return $redirect_url;
	}

	/**
	 * Redirect to the group forum screen
	 *
	 * @since PSForum (r3653)
	 */
	public function new_reply_redirect_to( $redirect_url = '', $redirect_to = '', $reply_id = 0 ) {
		global $wp_rewrite;

		if ( bp_is_group() ) {
			$topic_id       = psf_get_reply_topic_id( $reply_id );
			$topic          = psf_get_topic( $topic_id );
			$reply_position = psf_get_reply_position( $reply_id, $topic_id );
			$reply_page     = ceil( (int) $reply_position / (int) psf_get_replies_per_page() );
			$reply_hash     = '#post-' . $reply_id;
			$topic_url      = trailingslashit( bp_get_group_permalink( groups_get_current_group() ) ) . trailingslashit( $this->slug ) . trailingslashit( $this->topic_slug ) . trailingslashit( $topic->post_name );

			// Don't include pagination if on first page
			if ( 1 >= $reply_page ) {
				$redirect_url = trailingslashit( $topic_url ) . $reply_hash;

			// Include pagination
			} else {
				$redirect_url = trailingslashit( $topic_url ) . trailingslashit( $wp_rewrite->pagination_base ) . trailingslashit( $reply_page ) . $reply_hash;
			}

			// Add topic view query arg back to end if it is set
			if ( psf_get_view_all() ) {
				$redirect_url = psf_add_view_all( $redirect_url );
			}
		}

		return $redirect_url;
	}

	/**
	 * Redirect to the group admin forum edit screen
	 *
	 * @since PSForum (r3653)
	 * @uses groups_get_current_group()
	 * @uses bp_is_group_admin_screen()
	 * @uses trailingslashit()
	 * @uses bp_get_root_domain()
	 * @uses bp_get_groups_root_slug()
	 */
	public function edit_redirect_to( $redirect_url = '' ) {

		// Get the current group, if there is one
		$group = groups_get_current_group();

		// If this is a group of any kind, empty out the redirect URL
		if ( bp_is_group_admin_screen( $this->slug ) )
			$redirect_url = trailingslashit( bp_get_root_domain() . '/' . bp_get_groups_root_slug() . '/' . $group->slug . '/admin/' . $this->slug );

		return $redirect_url;
	}

	/** Form Helpers **********************************************************/

	public function forum_parent() {
	?>

		<input type="hidden" name="psf_forum_parent_id" id="psf_forum_parent_id" value="<?php psf_group_forums_root_id(); ?>" />

	<?php
	}

	public function topic_parent() {

		$forum_ids = psf_get_group_forum_ids( bp_get_current_group_id() ); ?>

		<p>
			<label for="psf_forum_id"><?php esc_html_e( 'Forum:', 'psforum' ); ?></label><br />
			<?php psf_dropdown( array( 'include' => $forum_ids, 'selected' => psf_get_form_topic_forum() ) ); ?>
		</p>

	<?php
	}

	/**
	 * Permissions to view the 'New Topic'/'Reply To' form in a BuddyPress group.
	 *
	 * @since PSForum (r4608)
	 *
	 * @param bool $retval Are we allowed to view the reply form?
	 * @uses bp_is_group() To determine if we're on a group page
	 * @uses is_user_logged_in() To determine if a user is logged in.
	 * @uses psf_is_user_keymaster() Is the current user a keymaster?
	 * @uses psf_group_is_member() Is the current user a member of the group?
	 * @uses psf_group_is_user_banned() Is the current user banned from the group?
	 *
	 * @return bool
	 */
	public function form_permissions( $retval = false ) {

		// Bail if not a group
		if ( ! bp_is_group() ) {
			return $retval;
		}

		// Bail if user is not logged in
		if ( ! is_user_logged_in() ) {
			return $retval;

		// Keymasters can always pass go
		} elseif ( psf_is_user_keymaster() ) {
			$retval = true;

		// Non-members cannot see forms
		} elseif ( ! psf_group_is_member() ) {
			$retval = false;

		// Banned users cannot see forms
		} elseif ( psf_group_is_banned() ) {
			$retval = false;
		}

		return $retval;
	}

	/**
	 * Add a hidden input field on the group settings page if the group forum is
	 * enabled.
	 *
	 * Due to the way BuddyPress' group admin settings page saves its settings,
	 * we need to let BP know that PSForum added a forum.
	 *
	 * @since PSForum (r5026)
	 *
	 * @link http://psforum.trac.wordpress.org/ticket/2339/
	 * @see groups_screen_group_admin_settings()
	 */
	public function group_settings_hidden_field() {

		// if a forum is not enabled, we don't need to add this field
		if ( ! bp_group_is_forum_enabled() )
			return; ?>

		<input type="hidden" name="group-show-forum" id="group-show-forum" value="1" />

	<?php
	}

	/** Permalink Mappers *****************************************************/

	/**
	 * Maybe map a PSForum forum/topic/reply permalink to the corresponding group
	 *
	 * @param int $post_id
	 * @uses get_post()
	 * @uses psf_is_reply()
	 * @uses psf_get_reply_topic_id()
	 * @uses psf_get_reply_forum_id()
	 * @uses psf_is_topic()
	 * @uses psf_get_topic_forum_id()
	 * @uses psf_is_forum()
	 * @uses get_post_field()
	 * @uses psf_get_forum_group_ids()
	 * @uses groups_get_group()
	 * @uses bp_get_group_admin_permalink()
	 * @uses bp_get_group_permalink()
	 * @return Bail early if not a group forum post
	 * @return string
	 */
	private function maybe_map_permalink_to_group( $post_id = 0, $url = false ) {

		switch ( get_post_type( $post_id ) ) {

			// Reply
			case psf_get_reply_post_type() :
				$topic_id = psf_get_reply_topic_id( $post_id );
				$forum_id = psf_get_reply_forum_id( $post_id );
				$url_end  = trailingslashit( $this->reply_slug ) . get_post_field( 'post_name', $post_id );
				break;

			// Topic
			case psf_get_topic_post_type() :
				$topic_id = $post_id;
				$forum_id = psf_get_topic_forum_id( $post_id );
				$url_end  = trailingslashit( $this->topic_slug ) . get_post_field( 'post_name', $post_id );
				break;

			// Forum
			case psf_get_forum_post_type() :
				$forum_id = $post_id;
				$url_end  = ''; //get_post_field( 'post_name', $post_id );
				break;

			// Unknown
			default :
				return $url;
				break;
		}

		// Get group ID's for this forum
		$group_ids = psf_get_forum_group_ids( $forum_id );

		// Bail if the post isn't associated with a group
		if ( empty( $group_ids ) )
			return $url;

		// @todo Multiple group forums/forum groups
		$group_id = $group_ids[0];
		$group    = groups_get_group( array( 'group_id' => $group_id ) );

		if ( bp_is_group_admin_screen( $this->slug ) ) {
			$group_permalink = trailingslashit( bp_get_group_admin_permalink( $group ) );
		} else {
			$group_permalink = trailingslashit( bp_get_group_permalink( $group ) );
		}

		return trailingslashit( trailingslashit( $group_permalink . $this->slug ) . $url_end );
	}

	/**
	 * Map a forum permalink to its corresponding group
	 *
	 * @since PSForum (r3802)
	 * @param string $url
	 * @param int $forum_id
	 * @uses maybe_map_permalink_to_group()
	 * @return string
	 */
	public function map_forum_permalink_to_group( $url, $forum_id ) {
		return $this->maybe_map_permalink_to_group( $forum_id, $url );
	}

	/**
	 * Map a topic permalink to its group forum
	 *
	 * @since PSForum (r3802)
	 * @param string $url
	 * @param int $topic_id
	 * @uses maybe_map_permalink_to_group()
	 * @return string
	 */
	public function map_topic_permalink_to_group( $url, $topic_id ) {
		return $this->maybe_map_permalink_to_group( $topic_id, $url );
	}

	/**
	 * Map a reply permalink to its group forum
	 *
	 * @since PSForum (r3802)
	 * @param string $url
	 * @param int $reply_id
	 * @uses maybe_map_permalink_to_group()
	 * @return string
	 */
	public function map_reply_permalink_to_group( $url, $reply_id ) {
		return $this->maybe_map_permalink_to_group( psf_get_reply_topic_id( $reply_id ), $url );
	}

	/**
	 * Map a reply edit link to its group forum
	 *
	 * @param string $url
	 * @param int $reply_id
	 * @uses maybe_map_permalink_to_group()
	 * @return string
	 */
	public function map_reply_edit_url_to_group( $url, $reply_id ) {
		$new = $this->maybe_map_permalink_to_group( $reply_id );

		if ( empty( $new ) )
			return $url;

		return trailingslashit( $new ) . psforum()->edit_id  . '/';
	}

	/**
	 * Map a post link to its group forum
	 *
	 * @param string $url
	 * @param obj $post
	 * @param boolean $leavename
	 * @uses maybe_map_permalink_to_group()
	 * @return string
	 */
	public function post_link( $url, $post ) {
		return $this->maybe_map_permalink_to_group( $post->ID, $url );
	}

	/**
	 * Map a page link to its group forum
	 *
	 * @param string $url
	 * @param int $post_id
	 * @param $sample
	 * @uses maybe_map_permalink_to_group()
	 * @return string
	 */
	public function page_link( $url, $post_id ) {
		return $this->maybe_map_permalink_to_group( $post_id, $url );
	}

	/**
	 * Map a custom post type link to its group forum
	 *
	 * @param string $url
	 * @param obj $post
	 * @param $leavename
	 * @param $sample
	 * @uses maybe_map_permalink_to_group()
	 * @return string
	 */
	public function post_type_link( $url, $post ) {
		return $this->maybe_map_permalink_to_group( $post->ID, $url );
	}

	/**
	 * Fix pagination of topics on forum view
	 *
	 * @param array $args
	 * @global $wp_rewrite
	 * @uses psf_get_forum_id()
	 * @uses maybe_map_permalink_to_group
	 * @return array
 	 */
	public function topic_pagination( $args ) {
		$new = $this->maybe_map_permalink_to_group( psf_get_forum_id() );

		if ( empty( $new ) )
			return $args;

		global $wp_rewrite;

		$args['base'] = trailingslashit( $new ) . $wp_rewrite->pagination_base . '/%#%/';

		return $args;
	}

	/**
	 * Fix pagination of replies on topic view
	 *
	 * @param array $args
	 * @global $wp_rewrite
	 * @uses psf_get_topic_id()
	 * @uses maybe_map_permalink_to_group
	 * @return array
	 */
	public function replies_pagination( $args ) {
		$new = $this->maybe_map_permalink_to_group( psf_get_topic_id() );
		if ( empty( $new ) )
			return $args;

		global $wp_rewrite;

		$args['base'] = trailingslashit( $new ) . $wp_rewrite->pagination_base . '/%#%/';

		return $args;
	}

	/**
	 * Ensure that forum content associated with a BuddyPress group can only be
	 * viewed via the group URL.
	 *
	 * @since PSForum (r3802)
	 */
	public function redirect_canonical() {

		// Viewing a single forum
		if ( psf_is_single_forum() ) {
			$forum_id  = get_the_ID();
			$group_ids = psf_get_forum_group_ids( $forum_id );

		// Viewing a single topic
		} elseif ( psf_is_single_topic() ) {
			$topic_id  = get_the_ID();
			$slug      = get_post_field( 'post_name', $topic_id );
			$forum_id  = psf_get_topic_forum_id( $topic_id );
			$group_ids = psf_get_forum_group_ids( $forum_id );

		// Not a forum or topic
		} else {
			return;
		}

		// Bail if not a group forum
		if ( empty( $group_ids ) )
			return;

		// Use the first group ID
		$group_id 	 = $group_ids[0];
		$group    	 = groups_get_group( array( 'group_id' => $group_id ) );
		$group_link  = trailingslashit( bp_get_group_permalink( $group ) );
		$redirect_to = trailingslashit( $group_link . $this->slug );

		// Add topic slug to URL
		if ( psf_is_single_topic() ) {
			$redirect_to  = trailingslashit( $redirect_to . $this->topic_slug . '/' . $slug );
		}

		bp_core_redirect( $redirect_to );
	}

	/** Activity **************************************************************/

	/**
	 * Map a forum post to its corresponding group in the group activity stream.
	 *
	 * @since PSForum (r4396)
	 *
	 * @param array $args Arguments from PSF_BuddyPress_Activity::record_activity()
	 * @uses groups_get_current_group() To see if we're posting from a BP group
	 *
	 * @return array
	 */
	public function map_activity_to_group( $args = array() ) {

		// Get current BP group
		$group = groups_get_current_group();

		// Not posting from a BuddyPress group? stop now!
		if ( empty( $group ) )
			return $args;

		// Set the component to 'groups' so the activity item shows up in the group
		$args['component']         = buddypress()->groups->id;

		// Move the forum post ID to the secondary item ID
		$args['secondary_item_id'] = $args['item_id'];

		// Set the item ID to the group ID so the activity item shows up in the group
		$args['item_id']           = $group->id;

		// Update the group's last activity
		groups_update_last_activity( $group->id );

		return $args;
	}
}
endif;
