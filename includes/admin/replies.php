<?php

/**
 * PSForum Replies Admin Class
 *
 * @package PSForum
 * @subpackage Administration
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'PSF_Replies_Admin' ) ) :
/**
 * Loads PSForum replies admin area
 *
 * @package PSForum
 * @subpackage Administration
 * @since PSForum (r2464)
 */
class PSF_Replies_Admin {

	/** Variables *************************************************************/

	/**
	 * @var The post type of this admin component
	 */
	private $post_type = '';

	/** Functions *************************************************************/

	/**
	 * The main PSForum admin loader
	 *
	 * @since PSForum (r2515)
	 *
	 * @uses PSF_Replies_Admin::setup_globals() Setup the globals needed
	 * @uses PSF_Replies_Admin::setup_actions() Setup the hooks and actions
	 * @uses PSF_Replies_Admin::setup_actions() Setup the help text
	 */
	public function __construct() {
		$this->setup_globals();
		$this->setup_actions();
	}

	/**
	 * Setup the admin hooks, actions and filters
	 *
	 * @since PSForum (r2646)
	 * @access private
	 *
	 * @uses add_action() To add various actions
	 * @uses add_filter() To add various filters
	 * @uses psf_get_forum_post_type() To get the forum post type
	 * @uses psf_get_topic_post_type() To get the topic post type
	 * @uses psf_get_reply_post_type() To get the reply post type
	 */
	private function setup_actions() {

		// Add some general styling to the admin area
		add_action( 'psf_admin_head',        array( $this, 'admin_head'       ) );

		// Messages
		add_filter( 'post_updated_messages', array( $this, 'updated_messages' ) );

		// Reply column headers.
		add_filter( 'manage_' . $this->post_type . '_posts_columns',  array( $this, 'column_headers' ) );

		// Reply columns (in post row)
		add_action( 'manage_' . $this->post_type . '_posts_custom_column',  array( $this, 'column_data' ), 10, 2 );
		add_filter( 'post_row_actions',                                     array( $this, 'row_actions' ), 10, 2 );

		// Reply metabox actions
		add_action( 'add_meta_boxes', array( $this, 'attributes_metabox'      ) );
		add_action( 'save_post',      array( $this, 'attributes_metabox_save' ) );

		// Check if there are any psf_toggle_reply_* requests on admin_init, also have a message displayed
		add_action( 'load-edit.php',  array( $this, 'toggle_reply'        ) );
		add_action( 'admin_notices',  array( $this, 'toggle_reply_notice' ) );

		// Anonymous metabox actions
		add_action( 'add_meta_boxes', array( $this, 'author_metabox'      ) );

		// Add ability to filter topics and replies per forum
		add_filter( 'restrict_manage_posts', array( $this, 'filter_dropdown'  ) );
		add_filter( 'psf_request',           array( $this, 'filter_post_rows' ) );

		// Contextual Help
		add_action( 'load-edit.php',     array( $this, 'edit_help' ) );
		add_action( 'load-post.php',     array( $this, 'new_help'  ) );
		add_action( 'load-post-new.php', array( $this, 'new_help'  ) );
	}

	/**
	 * Should we bail out of this method?
	 *
	 * @since PSForum (r4067)
	 * @return boolean
	 */
	private function bail() {
		if ( !isset( get_current_screen()->post_type ) || ( $this->post_type !== get_current_screen()->post_type ) )
			return true;

		return false;
	}

	/**
	 * Admin globals
	 *
	 * @since PSForum (r2646)
	 * @access private
	 */
	private function setup_globals() {
		$this->post_type = psf_get_reply_post_type();
	}

	/** Contextual Help *******************************************************/

	/**
	 * Contextual help for PSForum reply edit page
	 *
	 * @since PSForum (r3119)
	 * @uses get_current_screen()
	 */
	public function edit_help() {

		if ( $this->bail() ) return;

		// Overview
		get_current_screen()->add_help_tab( array(
			'id'		=> 'overview',
			'title'		=> __( 'Überblick', 'psforum' ),
			'content'	=>
				'<p>' . __( 'Dieser Bildschirm bietet Zugriff auf alle Deine Antworten. Du kannst die Anzeige dieses Bildschirms an Deinen Workflow anpassen.', 'psforum' ) . '</p>'
		) );

		// Screen Content
		get_current_screen()->add_help_tab( array(
			'id'		=> 'screen-content',
			'title'		=> __( 'Bildschirminhalt', 'psforum' ),
			'content'	=>
				'<p>' . __( 'Du kannst die Anzeige des Inhalts dieses Bildschirms auf verschiedene Weise anpassen:', 'psforum' ) . '</p>' .
				'<ul>' .
					'<li>' . __( 'Du kannst Spalten je nach Bedarf ausblenden/anzeigen und über die Registerkarte Bildschirmoptionen entscheiden, wie viele Antworten pro Bildschirm aufgelistet werden sollen.', 'psforum' ) . '</li>' .
					'<li>' . __( 'Du kannst die Liste der Antworten nach dem Antwortstatus filtern, indem Du die Textlinks oben links verwendest, um alle, veröffentlichte, Entwürfe oder im Papierkorb abgelegte Antworten anzuzeigen. In der Standardansicht werden alle Antworten angezeigt.', 'psforum' ) . '</li>' .
					'<li>' . __( 'Du kannst Antworten in einer einfachen Titelliste oder mit einem Auszug anzeigen. Wähle die gewünschte Ansicht, indem Du auf die Symbole oben in der Liste rechts klickst.', 'psforum' ) . '</li>' .
					'<li>' . __( 'Du kannst die Liste so verfeinern, dass nur Antworten in einer bestimmten Kategorie oder aus einem bestimmten Monat angezeigt werden, indem Du die Dropdown-Menüs über der Antwortliste verwendest. Klicke auf die Schaltfläche Filter, nachdem Du Deine Auswahl getroffen hast. Du kannst die Liste auch verfeinern, indem Du in der Antwortliste auf den Antwortautor, die Kategorie oder das Tag klickst.', 'psforum' ) . '</li>' .
				'</ul>'
		) );

		// Available Actions
		get_current_screen()->add_help_tab( array(
			'id'		=> 'action-links',
			'title'		=> __( 'Verfügbare Aktionen', 'psforum' ),
			'content'	=>
				'<p>' . __( 'Wenn Du mit der Maus über eine Zeile in der Antwortliste fährst, werden Aktionslinks angezeigt, mit denen Du Deine Antwort verwalten kannst. Du kannst die folgenden Aktionen ausführen:', 'psforum' ) . '</p>' .
				'<ul>' .
					'<li>' . __( '<strong>Bearbeiten</strong> führt Dich zum Bearbeitungsbildschirm für diese Antwort. Du kannst diesen Bildschirm auch erreichen, indem Du auf den Antworttitel klickst.', 'psforum' ) . '</li>' .
					//'<li>' . __( '<strong>Quick Edit</strong> provides inline access to the metadata of your reply, allowing you to update reply details without leaving this screen.', 'psforum' ) . '</li>' .
					'<li>' . __( '<strong>Papierkorb</strong> entfernt Deine Antwort aus dieser Liste und legt sie in den Papierkorb, aus dem Du sie endgültig löschen kannst.', 'psforum' ) . '</li>' .
					'<li>' . __( '<strong>Spam</strong> entfernt Deine Antwort aus dieser Liste und fügt sie in die Spam-Warteschlange ein, aus der Du sie endgültig löschen kannst.', 'psforum' ) . '</li>' .
					'<li>' . __( '<strong>Preview</strong> will show you what your draft reply will look like if you publish it. View will take you to your live site to view the reply. Which link is available depends on your reply&#8217;s status.', 'psforum' ) . '</li>' .
				'</ul>'
		) );

		// Bulk Actions
		get_current_screen()->add_help_tab( array(
			'id'		=> 'bulk-actions',
			'title'		=> __( 'Massenaktionen', 'psforum' ),
			'content'	=>
				'<p>' . __( 'Du kannst auch mehrere Antworten gleichzeitig bearbeiten oder in den Papierkorb verschieben. Wähle mithilfe der Kontrollkästchen die Antworten aus, auf die Du reagieren möchtest, wähle dann die gewünschte Aktion aus dem Menü Massenaktionen aus und klicke auf Übernehmen.', 'psforum' ) . '</p>' .
				'<p>' . __( 'Wenn Du die Massenbearbeitung verwendest, kannst Du die Metadaten (Kategorien, Autor usw.) für alle ausgewählten Antworten gleichzeitig ändern. Um eine Antwort aus der Gruppierung zu entfernen, klicke einfach auf das x neben ihrem Namen im angezeigten Massenbearbeitungsbereich.', 'psforum' ) . '</p>'
		) );

		// Help Sidebar
		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'Für mehr Informationen:', 'psforum' ) . '</strong></p>' .
			'<p>' . __( '<a href="https://n3rds.work/docs/ps-forum-plugin-handbuch/" target="_blank">PS Forum Dokumentation</a>', 'psforum' ) . '</p>' .
			'<p>' . __( '<a href="https://n3rds.work/forums/forum/psource-support-foren/ps-forum-supportforum/" target="_blank">PS Forum Support Forum</a>', 'psforum' ) . '</p>'
		);
	}

	/**
	 * Contextual help for PSForum reply edit page
	 *
	 * @since PSForum (r3119)
	 * @uses get_current_screen()
	 */
	public function new_help() {

		if ( $this->bail() ) return;

		$customize_display = '<p>' . __( 'Das Titelfeld und der große Antwortbearbeitungsbereich sind fixiert, aber Du kannst alle anderen Boxen per Drag-and-Drop neu positionieren und durch Klicken auf die Titelleiste der einzelnen Boxen minimieren oder erweitern. Verwende die Registerkarte Bildschirmoptionen, um weitere Felder (Auszug, Trackbacks senden, benutzerdefinierte Felder, Diskussion, Slug, Autor) einzublenden oder ein 1- oder 2-spaltiges Layout für diesen Bildschirm auszuwählen.', 'psforum' ) . '</p>';

		get_current_screen()->add_help_tab( array(
			'id'      => 'customize-display',
			'title'   => __( 'Anpassen dieser Anzeige', 'psforum' ),
			'content' => $customize_display,
		) );

		get_current_screen()->add_help_tab( array(
			'id'      => 'title-reply-editor',
			'title'   => __( 'Titel- und Antworteditor', 'psforum' ),
			'content' =>
				'<p>' . __( '<strong>Titel</strong> – Gib einen Titel für Deine Antwort ein. Nachdem Du einen Titel eingegeben hast, siehst Du unten den Permalink, den Du bearbeiten kannst.', 'psforum' ) . '</p>' .
				'<p>' . __( '<strong>Antworteditor</strong> – Gib den Text für Deine Antwort ein. Es gibt zwei Bearbeitungsmodi: Visual und HTML. Wähle den Modus aus, indem Du auf die entsprechende Registerkarte klickst. Der visuelle Modus bietet Dir einen WYSIWYG-Editor. Klicke auf das letzte Symbol in der Zeile, um eine zweite Zeile mit Steuerelementen anzuzeigen. Im HTML-Modus kannst Du zusammen mit Deinem Antworttext Roh-HTML eingeben. Du kannst Mediendateien einfügen, indem Du auf die Symbole über dem Antworteditor klickst und den Anweisungen folgst. Zum ablenkungsfreien Schreibbildschirm gelangst Du über das Vollbild-Icon im visuellen Modus (vorletzter in der obersten Reihe) oder über den Vollbild-Button im HTML-Modus (letzter in der Reihe). Sobald Du dort bist, kannst Du Schaltflächen sichtbar machen, indem Du mit der Maus über den oberen Bereich fährst. Beende den Vollbildmodus und kehre zum regulären Antworteditor zurück.', 'psforum' ) . '</p>'
		) );

		$publish_box = '<p>' . __( '<strong>Veröffentlichen</strong> – Du kannst die Bedingungen für die Veröffentlichung Deiner Antwort im Feld „Veröffentlichen“ festlegen. Klicke für Status, Sichtbarkeit und Veröffentlichen (sofort) auf den Link Bearbeiten, um weitere Optionen anzuzeigen. Die Sichtbarkeit umfasst Optionen, um eine Antwort mit einem Passwort zu schützen oder sie auf unbestimmte Zeit ganz oben in Deinem Blog zu halten (sticky). Veröffentlichen (sofort) ermöglicht Dir, ein Datum und eine Uhrzeit in der Zukunft oder in der Vergangenheit festzulegen, sodass Du die Veröffentlichung einer Antwort in der Zukunft planen oder eine Antwort rückdatieren kannst.', 'psforum' ) . '</p>';

		if ( current_theme_supports( 'reply-thumbnails' ) && post_type_supports( 'reply', 'thumbnail' ) ) {
			$publish_box .= '<p>' . __( '<strong>Empfohlenes Bild</strong> – Hiermit kannst Du Deiner Antwort ein Bild zuordnen, ohne es einzufügen. Dies ist normalerweise nur dann nützlich, wenn Dein Theme das vorgestellte Bild als Antwortminiatur auf der Startseite, als benutzerdefinierter Header usw. verwendet.', 'psforum' ) . '</p>';
		}

		get_current_screen()->add_help_tab( array(
			'id'      => 'reply-attributes',
			'title'   => __( 'Antwortattribute', 'psforum' ),
			'content' =>
				'<p>' . __( 'Wähle die Attribute aus, die Deine Antwort haben soll:', 'psforum' ) . '</p>' .
				'<ul>' .
					'<li>' . __( 'Das Dropdown-Menü <strong>Forum</strong> bestimmt das übergeordnete Forum, zu dem die Antwort gehört. Wähle das Forum aus oder belasse die Standardeinstellung (Forum des Themas verwenden), um die Antwort im Forum des Themas zu posten.', 'psforum' ) . '</li>' .
					'<li>' . __( '<strong>Thema</strong> bestimmt das übergeordnete Thema, zu dem die Antwort gehört.', 'psforum' ) . '</li>' .
					'<li>' . __( '<strong>Antworten an</strong> bestimmt das Threading der Antwort.', 'psforum' ) . '</li>' .
				'</ul>'
		) );

		get_current_screen()->add_help_tab( array(
			'id'      => 'publish-box',
			'title'   => __( 'Veröffentlichungsbox', 'psforum' ),
			'content' => $publish_box,
		) );

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'Für mehr Informationen:', 'psforum' ) . '</strong></p>' .
			'<p>' . __( '<a href="https://n3rds.work/docs/ps-forum-plugin-handbuch/" target="_blank">PS Forum Dokumentation</a>', 'psforum' ) . '</p>' .
			'<p>' . __( '<a href="https://n3rds.work/forums/forum/psource-support-foren/ps-forum-supportforum/" target="_blank">PS Forum Support Forum</a>', 'psforum' ) . '</p>'
		);
	}

	/**
	 * Add the reply attributes metabox
	 *
	 * @since PSForum (r2746)
	 *
	 * @uses psf_get_reply_post_type() To get the reply post type
	 * @uses add_meta_box() To add the metabox
	 * @uses do_action() Calls 'psf_reply_attributes_metabox'
	 */
	public function attributes_metabox() {

		if ( $this->bail() ) return;

		add_meta_box (
			'psf_reply_attributes',
			__( 'Antwortattribute', 'psforum' ),
			'psf_reply_metabox',
			$this->post_type,
			'side',
			'high'
		);

		do_action( 'psf_reply_attributes_metabox' );
	}

	/**
	 * Pass the reply attributes for processing
	 *
	 * @since PSForum (r2746)
	 *
	 * @param int $reply_id Reply id
	 * @uses current_user_can() To check if the current user is capable of
	 *                           editing the reply
	 * @uses do_action() Calls 'psf_reply_attributes_metabox_save' with the
	 *                    reply id and parent id
	 * @return int Parent id
	 */
	public function attributes_metabox_save( $reply_id ) {

		if ( $this->bail() ) return $reply_id;

		// Bail if doing an autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $reply_id;

		// Bail if not a post request
		if ( ! psf_is_post_request() )
			return $reply_id;

		// Check action exists
		if ( empty( $_POST['action'] ) )
			return $reply_id;

		// Nonce check
		if ( empty( $_POST['psf_reply_metabox'] ) || !wp_verify_nonce( $_POST['psf_reply_metabox'], 'psf_reply_metabox_save' ) )
			return $reply_id;

		// Current user cannot edit this reply
		if ( !current_user_can( 'edit_reply', $reply_id ) )
			return $reply_id;

		// Get the reply meta post values
		$topic_id = !empty( $_POST['parent_id']    ) ? (int) $_POST['parent_id']    : 0;
		$forum_id = !empty( $_POST['psf_forum_id'] ) ? (int) $_POST['psf_forum_id'] : psf_get_topic_forum_id( $topic_id );
		$reply_to = !empty( $_POST['psf_reply_to'] ) ? (int) $_POST['psf_reply_to'] : 0;

		// Get reply author data
		$anonymous_data = psf_filter_anonymous_post_data();
		$author_id      = psf_get_reply_author_id( $reply_id );
		$is_edit        = (bool) isset( $_POST['save'] );

		// Formally update the reply
		psf_update_reply( $reply_id, $topic_id, $forum_id, $anonymous_data, $author_id, $is_edit, $reply_to );

		// Allow other fun things to happen
		do_action( 'psf_reply_attributes_metabox_save', $reply_id, $topic_id, $forum_id, $reply_to );
		do_action( 'psf_author_metabox_save',           $reply_id, $anonymous_data                 );

		return $reply_id;
	}

	/**
	 * Add the author info metabox
	 *
	 * Allows editing of information about an author
	 *
	 * @since PSForum (r2828)
	 *
	 * @uses psf_get_topic() To get the topic
	 * @uses psf_get_reply() To get the reply
	 * @uses psf_get_topic_post_type() To get the topic post type
	 * @uses psf_get_reply_post_type() To get the reply post type
	 * @uses add_meta_box() To add the metabox
	 * @uses do_action() Calls 'psf_author_metabox' with the topic/reply
	 *                    id
	 */
	public function author_metabox() {

		if ( $this->bail() ) return;

		// Bail if post_type is not a reply
		if ( empty( $_GET['action'] ) || ( 'edit' !== $_GET['action'] ) )
			return;

		// Add the metabox
		add_meta_box(
			'psf_author_metabox',
			__( 'Autor Informationen', 'psforum' ),
			'psf_author_metabox',
			$this->post_type,
			'side',
			'high'
		);

		do_action( 'psf_author_metabox', get_the_ID() );
	}

	/**
	 * Add some general styling to the admin area
	 *
	 * @since PSForum (r2464)
	 *
	 * @uses psf_get_forum_post_type() To get the forum post type
	 * @uses psf_get_topic_post_type() To get the topic post type
	 * @uses psf_get_reply_post_type() To get the reply post type
	 * @uses sanitize_html_class() To sanitize the classes
	 * @uses do_action() Calls 'psf_admin_head'
	 */
	public function admin_head() {

		if ( $this->bail() ) return;

		?>

		<style type="text/css" media="screen">
		/*<![CDATA[*/

			strong.label {
				display: inline-block;
				width: 60px;
			}

			.column-psf_forum_topic_count,
			.column-psf_forum_reply_count,
			.column-psf_topic_reply_count,
			.column-psf_topic_voice_count {
				width: 8% !important;
			}

			.column-author,
			.column-psf_reply_author,
			.column-psf_topic_author {
				width: 10% !important;
			}

			.column-psf_topic_forum,
			.column-psf_reply_forum,
			.column-psf_reply_topic {
				width: 10% !important;
			}

			.column-psf_forum_freshness,
			.column-psf_topic_freshness {
				width: 10% !important;
			}

			.column-psf_forum_created,
			.column-psf_topic_created,
			.column-psf_reply_created {
				width: 15% !important;
			}

			.status-closed {
				background-color: #eaeaea;
			}

			.status-spam {
				background-color: #faeaea;
			}

		/*]]>*/
		</style>

		<?php
	}

	/**
	 * Toggle reply
	 *
	 * Handles the admin-side spamming/unspamming of replies
	 *
	 * @since PSForum (r2740)
	 *
	 * @uses psf_get_reply() To get the reply
	 * @uses current_user_can() To check if the user is capable of editing
	 *                           the reply
	 * @uses wp_die() To die if the user isn't capable or the post wasn't
	 *                 found
	 * @uses check_admin_referer() To verify the nonce and check referer
	 * @uses psf_is_reply_spam() To check if the reply is marked as spam
	 * @uses psf_unspam_reply() To unmark the reply as spam
	 * @uses psf_spam_reply() To mark the reply as spam
	 * @uses do_action() Calls 'psf_toggle_reply_admin' with success, post
	 *                    data, action and message
	 * @uses add_query_arg() To add custom args to the url
	 * @uses wp_safe_redirect() Redirect the page to custom url
	 */
	public function toggle_reply() {

		if ( $this->bail() ) return;

		// Only proceed if GET is a reply toggle action
		if ( psf_is_get_request() && !empty( $_GET['action'] ) && in_array( $_GET['action'], array( 'psf_toggle_reply_spam' ) ) && !empty( $_GET['reply_id'] ) ) {
			$action    = $_GET['action'];            // What action is taking place?
			$reply_id  = (int) $_GET['reply_id'];    // What's the reply id?
			$success   = false;                      // Flag
			$post_data = array( 'ID' => $reply_id ); // Prelim array

			// Get reply and die if empty
			$reply = psf_get_reply( $reply_id );
			if ( empty( $reply ) ) // Which reply?
				wp_die( __( 'Die Antwort wurde nicht gefunden!', 'psforum' ) );

			if ( !current_user_can( 'moderate', $reply->ID ) ) // What is the user doing here?
				wp_die( __( 'Du hast keine Berechtigung dazu!', 'psforum' ) );

			switch ( $action ) {
				case 'psf_toggle_reply_spam' :
					check_admin_referer( 'spam-reply_' . $reply_id );

					$is_spam = psf_is_reply_spam( $reply_id );
					$message = $is_spam ? 'unspammed' : 'spammed';
					$success = $is_spam ? psf_unspam_reply( $reply_id ) : psf_spam_reply( $reply_id );

					break;
			}

			$success = wp_update_post( $post_data );
			$message = array( 'psf_reply_toggle_notice' => $message, 'reply_id' => $reply->ID );

			if ( false === $success || is_wp_error( $success ) )
				$message['failed'] = '1';

			// Do additional reply toggle actions (admin side)
			do_action( 'psf_toggle_reply_admin', $success, $post_data, $action, $message );

			// Redirect back to the reply
			$redirect = add_query_arg( $message, remove_query_arg( array( 'action', 'reply_id' ) ) );
			wp_safe_redirect( $redirect );

			// For good measure
			exit();
		}
	}

	/**
	 * Toggle reply notices
	 *
	 * Display the success/error notices from
	 * {@link PSF_Admin::toggle_reply()}
	 *
	 * @since PSForum (r2740)
	 *
	 * @uses psf_get_reply() To get the reply
	 * @uses psf_get_reply_title() To get the reply title of the reply
	 * @uses esc_html() To sanitize the reply title
	 * @uses apply_filters() Calls 'psf_toggle_reply_notice_admin' with
	 *                        message, reply id, notice and is it a failure
	 */
	public function toggle_reply_notice() {

		if ( $this->bail() ) return;

		// Only proceed if GET is a reply toggle action
		if ( psf_is_get_request() && !empty( $_GET['psf_reply_toggle_notice'] ) && in_array( $_GET['psf_reply_toggle_notice'], array( 'spammed', 'unspammed' ) ) && !empty( $_GET['reply_id'] ) ) {
			$notice     = $_GET['psf_reply_toggle_notice'];         // Which notice?
			$reply_id   = (int) $_GET['reply_id'];                  // What's the reply id?
			$is_failure = !empty( $_GET['failed'] ) ? true : false; // Was that a failure?

			// Empty? No reply?
			if ( empty( $notice ) || empty( $reply_id ) )
				return;

			// Get reply and bail if empty
			$reply = psf_get_reply( $reply_id );
			if ( empty( $reply ) )
				return;

			$reply_title = psf_get_reply_title( $reply->ID );

			switch ( $notice ) {
				case 'spammed' :
					$message = $is_failure === true ? sprintf( __( 'Beim Markieren der Antwort "%1$s" als Spam ist ein Problem aufgetreten.', 'psforum' ), $reply_title ) : sprintf( __( 'Antwort "%1$s" erfolgreich als Spam markiert.', 'psforum' ), $reply_title );
					break;

				case 'unspammed' :
					$message = $is_failure === true ? sprintf( __( 'Beim Aufheben der Markierung der Antwort "%1$s" als Spam ist ein Problem aufgetreten.', 'psforum' ), $reply_title ) : sprintf( __( 'Antwort "%1$s" erfolgreich als Spam aufgehoben.', 'psforum' ), $reply_title );
					break;
			}

			// Do additional reply toggle notice filters (admin side)
			$message = apply_filters( 'psf_toggle_reply_notice_admin', $message, $reply->ID, $notice, $is_failure );

			?>

			<div id="message" class="<?php echo $is_failure === true ? 'error' : 'updated'; ?> fade">
				<p style="line-height: 150%"><?php echo esc_html( $message ); ?></p>
			</div>

			<?php
		}
	}

	/**
	 * Manage the column headers for the replies page
	 *
	 * @since PSForum (r2577)
	 *
	 * @param array $columns The columns
	 * @uses apply_filters() Calls 'psf_admin_replies_column_headers' with
	 *                        the columns
	 * @return array $columns PSForum reply columns
	 */
	public function column_headers( $columns ) {

		if ( $this->bail() ) return $columns;

		$columns = array(
			'cb'                => '<input type="checkbox" />',
			'title'             => __( 'Titel',   'psforum' ),
			'psf_reply_forum'   => __( 'Forum',   'psforum' ),
			'psf_reply_topic'   => __( 'Thema',   'psforum' ),
			'psf_reply_author'  => __( 'Autor',  'psforum' ),
			'psf_reply_created' => __( 'Erstellt', 'psforum' ),
		);

		return apply_filters( 'psf_admin_replies_column_headers', $columns );
	}

	/**
	 * Print extra columns for the replies page
	 *
	 * @since PSForum (r2577)
	 *
	 * @param string $column Column
	 * @param int $reply_id reply id
	 * @uses psf_get_reply_topic_id() To get the topic id of the reply
	 * @uses psf_topic_title() To output the reply's topic title
	 * @uses apply_filters() Calls 'reply_topic_row_actions' with an array
	 *                        of reply topic actions
	 * @uses psf_get_topic_permalink() To get the topic permalink
	 * @uses psf_get_topic_forum_id() To get the forum id of the topic of
	 *                                 the reply
	 * @uses psf_get_forum_permalink() To get the forum permalink
	 * @uses admin_url() To get the admin url of post.php
	 * @uses apply_filters() Calls 'reply_topic_forum_row_actions' with an
	 *                        array of reply topic forum actions
	 * @uses psf_reply_author_display_name() To output the reply author name
	 * @uses get_the_date() Get the reply creation date
	 * @uses get_the_time() Get the reply creation time
	 * @uses esc_attr() To sanitize the reply creation time
	 * @uses psf_get_reply_last_active_time() To get the time when the reply was
	 *                                    last active
	 * @uses do_action() Calls 'psf_admin_replies_column_data' with the
	 *                    column and reply id
	 */
	public function column_data( $column, $reply_id ) {

		if ( $this->bail() ) return;

		// Get topic ID
		$topic_id = psf_get_reply_topic_id( $reply_id );

		// Populate Column Data
		switch ( $column ) {

			// Topic
			case 'psf_reply_topic' :

				// Output forum name
				if ( !empty( $topic_id ) ) {

					// Topic Title
					$topic_title = psf_get_topic_title( $topic_id );
					if ( empty( $topic_title ) ) {
						$topic_title = esc_html__( 'Kein Thema', 'psforum' );
					}

					// Output the title
					echo $topic_title;

				// Reply has no topic
				} else {
					esc_html_e( 'Kein Thema', 'psforum' );
				}

				break;

			// Forum
			case 'psf_reply_forum' :

				// Get Forum ID's
				$reply_forum_id = psf_get_reply_forum_id( $reply_id );
				$topic_forum_id = psf_get_topic_forum_id( $topic_id );

				// Output forum name
				if ( !empty( $reply_forum_id ) ) {

					// Forum Title
					$forum_title = psf_get_forum_title( $reply_forum_id );
					if ( empty( $forum_title ) ) {
						$forum_title = esc_html__( 'Kein Forum', 'psforum' );
					}

					// Alert capable users of reply forum mismatch
					if ( $reply_forum_id !== $topic_forum_id ) {
						if ( current_user_can( 'edit_others_replies' ) || current_user_can( 'moderate' ) ) {
							$forum_title .= '<div class="attention">' . esc_html__( '(Nichtübereinstimmung)', 'psforum' ) . '</div>';
						}
					}

					// Output the title
					echo $forum_title;

				// Reply has no forum
				} else {
					_e( 'Kein Forum', 'psforum' );
				}

				break;

			// Author
			case 'psf_reply_author' :
				psf_reply_author_display_name ( $reply_id );
				break;

			// Freshness
			case 'psf_reply_created':

				// Output last activity time and date
				printf( '%1$s <br /> %2$s',
					get_the_date(),
					esc_attr( get_the_time() )
				);

				break;

			// Do action for anything else
			default :
				do_action( 'psf_admin_replies_column_data', $column, $reply_id );
				break;
		}
	}

	/**
	 * Reply Row actions
	 *
	 * Remove the quick-edit action link under the reply title and add the
	 * content and spam link
	 *
	 * @since PSForum (r2577)
	 *
	 * @param array $actions Actions
	 * @param array $reply Reply object
	 * @uses psf_get_reply_post_type() To get the reply post type
	 * @uses psf_reply_content() To output reply content
	 * @uses psf_get_reply_permalink() To get the reply link
	 * @uses psf_get_reply_title() To get the reply title
	 * @uses current_user_can() To check if the current user can edit or
	 *                           delete the reply
	 * @uses psf_is_reply_spam() To check if the reply is marked as spam
	 * @uses get_post_type_object() To get the reply post type object
	 * @uses add_query_arg() To add custom args to the url
	 * @uses remove_query_arg() To remove custom args from the url
	 * @uses wp_nonce_url() To nonce the url
	 * @uses get_delete_post_link() To get the delete post link of the reply
	 * @return array $actions Actions
	 */
	public function row_actions( $actions, $reply ) {

		if ( $this->bail() ) return $actions;

		unset( $actions['inline hide-if-no-js'] );

		// Reply view links to topic
		$actions['view'] = '<a href="' . esc_url( psf_get_reply_url( $reply->ID ) ) . '" title="' . esc_attr( sprintf( __( 'Ansehen &#8220;%s&#8221;', 'psforum' ), psf_get_reply_title( $reply->ID ) ) ) . '" rel="permalink">' . esc_html__( 'Ansehen', 'psforum' ) . '</a>';

		// User cannot view replies in trash
		if ( ( psf_get_trash_status_id() === $reply->post_status ) && !current_user_can( 'view_trash' ) )
			unset( $actions['view'] );

		// Only show the actions if the user is capable of viewing them
		if ( current_user_can( 'moderate', $reply->ID ) ) {
			if ( in_array( $reply->post_status, array( psf_get_public_status_id(), psf_get_spam_status_id() ) ) ) {
				$spam_uri  = wp_nonce_url( add_query_arg( array( 'reply_id' => $reply->ID, 'action' => 'psf_toggle_reply_spam' ), remove_query_arg( array( 'psf_reply_toggle_notice', 'reply_id', 'failed', 'super' ) ) ), 'spam-reply_'  . $reply->ID );
				if ( psf_is_reply_spam( $reply->ID ) ) {
					$actions['spam'] = '<a href="' . esc_url( $spam_uri ) . '" title="' . esc_attr__( 'Antwort als kein Spam markieren', 'psforum' ) . '">' . esc_html__( 'Kein spam', 'psforum' ) . '</a>';
				} else {
					$actions['spam'] = '<a href="' . esc_url( $spam_uri ) . '" title="' . esc_attr__( 'Diese Antwort als Spam markieren',    'psforum' ) . '">' . esc_html__( 'Spam',     'psforum' ) . '</a>';
				}
			}
		}

		// Trash
		if ( current_user_can( 'delete_reply', $reply->ID ) ) {
			if ( psf_get_trash_status_id() === $reply->post_status ) {
				$post_type_object   = get_post_type_object( psf_get_reply_post_type() );
				$actions['untrash'] = "<a title='" . esc_attr__( 'Dieses Element aus dem Papierkorb wiederherstellen', 'psforum' ) . "' href='" . esc_url( add_query_arg( array( '_wp_http_referer' => add_query_arg( array( 'post_type' => psf_get_reply_post_type() ), admin_url( 'edit.php' ) ) ), wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $reply->ID ) ), 'untrash-' . $reply->post_type . '_' . $reply->ID ) ) ) . "'>" . esc_html__( 'Wiederherstellen', 'psforum' ) . "</a>";
			} elseif ( EMPTY_TRASH_DAYS ) {
				$actions['trash'] = "<a class='submitdelete' title='" . esc_attr__( 'Dieses Element in den Papierkorb verschieben', 'psforum' ) . "' href='" . esc_url( add_query_arg( array( '_wp_http_referer' => add_query_arg( array( 'post_type' => psf_get_reply_post_type() ), admin_url( 'edit.php' ) ) ), get_delete_post_link( $reply->ID ) ) ) . "'>" . esc_html__( 'Müll', 'psforum' ) . "</a>";
			}

			if ( psf_get_trash_status_id() === $reply->post_status || !EMPTY_TRASH_DAYS ) {
				$actions['delete'] = "<a class='submitdelete' title='" . esc_attr__( 'Dieses Element endgültig löschen', 'psforum' ) . "' href='" . esc_url( add_query_arg( array( '_wp_http_referer' => add_query_arg( array( 'post_type' => psf_get_reply_post_type() ), admin_url( 'edit.php' ) ) ), get_delete_post_link( $reply->ID, '', true ) ) ) . "'>" . esc_html__( 'Dauerhaft löschen', 'psforum' ) . "</a>";
			} elseif ( psf_get_spam_status_id() === $reply->post_status ) {
				unset( $actions['trash'] );
			}
		}

		return $actions;
	}

	/**
	 * Add forum dropdown to topic and reply list table filters
	 *
	 * @since PSForum (r2991)
	 *
	 * @uses psf_get_reply_post_type() To get the reply post type
	 * @uses psf_get_topic_post_type() To get the topic post type
	 * @uses psf_dropdown() To generate a forum dropdown
	 * @return bool False. If post type is not topic or reply
	 */
	public function filter_dropdown() {

		if ( $this->bail() ) return;

		// Add Empty Spam button
		if ( !empty( $_GET['post_status'] ) && ( psf_get_spam_status_id() === $_GET['post_status'] ) && current_user_can( 'moderate' ) ) {
			wp_nonce_field( 'bulk-destroy', '_destroy_nonce' );
			$title = esc_attr__( 'Spam leeren', 'psforum' );
			submit_button( $title, 'button-secondary apply', 'delete_all', false );
		}

		// Get which forum is selected
		$selected = !empty( $_GET['psf_forum_id'] ) ? $_GET['psf_forum_id'] : '';

		// Show the forums dropdown
		psf_dropdown( array(
			'selected'  => $selected,
			'show_none' => __( 'In allen Foren', 'psforum' )
		) );
	}

	/**
	 * Adjust the request query and include the forum id
	 *
	 * @since PSForum (r2991)
	 *
	 * @param array $query_vars Query variables from {@link WP_Query}
	 * @uses is_admin() To check if it's the admin section
	 * @uses psf_get_topic_post_type() To get the topic post type
	 * @uses psf_get_reply_post_type() To get the reply post type
	 * @return array Processed Query Vars
	 */
	public function filter_post_rows( $query_vars ) {

		if ( $this->bail() ) return $query_vars;

		// Add post_parent query_var if one is present
		if ( !empty( $_GET['psf_forum_id'] ) ) {
			$query_vars['meta_key']   = '_psf_forum_id';
			$query_vars['meta_value'] = $_GET['psf_forum_id'];
		}

		// Return manipulated query_vars
		return $query_vars;
	}

	/**
	 * Custom user feedback messages for reply post type
	 *
	 * @since PSForum (r3080)
	 *
	 * @global int $post_ID
	 * @uses psf_get_topic_permalink()
	 * @uses wp_post_revision_title()
	 * @uses esc_url()
	 * @uses add_query_arg()
	 *
	 * @param array $messages
	 *
	 * @return array
	 */
	public function updated_messages( $messages ) {
		global $post_ID;

		if ( $this->bail() ) return $messages;

		// URL for the current topic
		$topic_url = psf_get_topic_permalink( psf_get_reply_topic_id( $post_ID ) );

		// Current reply's post_date
		$post_date = psf_get_global_post_field( 'post_date', 'raw' );

		// Messages array
		$messages[$this->post_type] = array(
			0 =>  '', // Left empty on purpose

			// Updated
			1 =>  sprintf( __( 'Antwort aktualisiert. <a href="%s">Thema anzeigen</a>', 'psforum' ), $topic_url ),

			// Custom field updated
			2 => __( 'Benutzerdefiniertes Feld aktualisiert.', 'psforum' ),

			// Custom field deleted
			3 => __( 'Benutzerdefiniertes Feld gelöscht.', 'psforum' ),

			// Antwort aktualisiert
			4 => __( 'Antwort aktualisiert.', 'psforum' ),

			// Restored from revision
			// translators: %s: date and time of the revision
			5 => isset( $_GET['revision'] )
					? sprintf( __( 'Antwort auf Überarbeitung von %s wiederhergestellt', 'psforum' ), wp_post_revision_title( (int) $_GET['revision'], false ) )
					: false,

			// Reply created
			6 => sprintf( __( 'Antwort erstellt. <a href="%s">Thema anzeigen</a>', 'psforum' ), $topic_url ),

			// Reply saved
			7 => __( 'Antwort gespeichert.', 'psforum' ),

			// Reply submitted
			8 => sprintf( __( 'Antwort gesendet. <a target="_blank" href="%s">Vorschauthema</a>', 'psforum' ), esc_url( add_query_arg( 'preview', 'true', $topic_url ) ) ),

			// Reply scheduled
			9 => sprintf( __( 'Antwort geplant für: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Vorschauthema</a>', 'psforum' ),
					// translators: Publish box date format, see http://php.net/date
					date_i18n( __( 'M j, Y @ G:i', 'psforum' ),
					strtotime( $post_date ) ),
					$topic_url ),

			// Reply draft updated
			10 => sprintf( __( 'Antwortentwurf aktualisiert. <a target="_blank" href="%s">Vorschauthema</a>', 'psforum' ), esc_url( add_query_arg( 'preview', 'true', $topic_url ) ) ),
		);

		return $messages;
	}
}
endif; // class_exists check

/**
 * Setup PSForum Replies Admin
 *
 * This is currently here to make hooking and unhooking of the admin UI easy.
 * It could use dependency injection in the future, but for now this is easier.
 *
 * @since PSForum (r2596)
 *
 * @uses PSF_Replies_Admin
 */
function psf_admin_replies() {
	psforum()->admin->replies = new PSF_Replies_Admin();
}
