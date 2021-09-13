<?php

/**
 * PSForum Topics Admin Class
 *
 * @package PSForum
 * @subpackage Administration
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'PSF_Topics_Admin' ) ) :
/**
 * Loads PSForum topics admin area
 *
 * @package PSForum
 * @subpackage Administration
 * @since PSForum (r2464)
 */
class PSF_Topics_Admin {

	/** Variables *************************************************************/

	/**
	 * @var The post type of this admin component
	 */
	private $post_type = '';

	/** Functions *************************************************************/

	/**
	 * The main PSForum topics admin loader
	 *
	 * @since PSForum (r2515)
	 *
	 * @uses PSF_Topics_Admin::setup_globals() Setup the globals needed
	 * @uses PSF_Topics_Admin::setup_actions() Setup the hooks and actions
	 * @uses PSF_Topics_Admin::setup_help() Setup the help text
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

		// Topic column headers.
		add_filter( 'manage_' . $this->post_type . '_posts_columns',        array( $this, 'column_headers' ) );

		// Topic columns (in post row)
		add_action( 'manage_' . $this->post_type . '_posts_custom_column',  array( $this, 'column_data' ), 10, 2 );
		add_filter( 'post_row_actions',                                     array( $this, 'row_actions' ), 10, 2 );

		// Topic metabox actions
		add_action( 'add_meta_boxes', array( $this, 'attributes_metabox'      ) );
		add_action( 'save_post',      array( $this, 'attributes_metabox_save' ) );

		// Check if there are any psf_toggle_topic_* requests on admin_init, also have a message displayed
		add_action( 'load-edit.php',  array( $this, 'toggle_topic'        ) );
		add_action( 'admin_notices',  array( $this, 'toggle_topic_notice' ) );

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
		$this->post_type = psf_get_topic_post_type();
	}

	/** Contextual Help *******************************************************/

	/**
	 * Contextual help for PSForum topic edit page
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
				'<p>' . __( 'Dieser Bildschirm zeigt die einzelnen Themen Deiner Webseite an. Du kannst die Anzeige dieses Bildschirms an Deinen Workflow anpassen.', 'psforum' ) . '</p>'
		) );

		// Screen Content
		get_current_screen()->add_help_tab( array(
			'id'		=> 'screen-content',
			'title'		=> __( 'Screen Content', 'psforum' ),
			'content'	=>
				'<p>' . __( 'Du kannst die Anzeige des Inhalts dieses Bildschirms auf verschiedene Weise anpassen:', 'psforum' ) . '</p>' .
				'<ul>' .
					'<li>' . __( 'Du kannst Spalten je nach Bedarf ein- und ausblenden und über die Registerkarte Bildschirmoptionen entscheiden, wie viele Themen pro Bildschirm aufgelistet werden sollen.', 'psforum' ) . '</li>' .
					'<li>' . __( 'Du kannst die Themenliste nach Themenstatus filtern, indem Du die Textlinks oben links verwendest, um alle, veröffentlichten oder im Papierkorb abgelegten Themen anzuzeigen. In der Standardansicht werden alle Themen angezeigt.', 'psforum' ) . '</li>' .
					'<li>' . __( 'Du kannst die Liste so verfeinern, dass nur Themen aus einem bestimmten Monat angezeigt werden, indem Du die Dropdown-Menüs über der Themenliste verwenden. Klicke die Schaltfläche Filter, nachdem Du Deine Auswahl getroffen haben. Du kannst die Liste auch verfeinern, indem Du in der Themenliste auf den Themenersteller klickst.', 'psforum' ) . '</li>' .
				'</ul>'
		) );

		// Available Actions
		get_current_screen()->add_help_tab( array(
			'id'		=> 'action-links',
			'title'		=> __( 'Verfügbare Aktionen', 'psforum' ),
			'content'	=>
				'<p>' . __( 'Wenn Du mit der Maus über eine Zeile in der Themenliste fährst, werden Aktionslinks angezeigt, mit denen Du Dein Thema verwalten kannst. Du kannst die folgenden Aktionen ausführen:', 'psforum' ) . '</p>' .
				'<ul>' .
					'<li>' . __( '<strong>Bearbeiten</strong> führt Dich zum Bearbeitungsbildschirm für dieses Thema. Du kannst diesen Bildschirm auch erreichen, indem Du auf den Thementitel klickst.', 'psforum' ) . '</li>' .
					'<li>' . __( '<strong>Papierkorb</strong> entfernt Dein Thema aus dieser Liste und legt es in den Papierkorb, aus dem Du es endgültig löschen kannst.', 'psforum' ) . '</li>' .
					'<li>' . __( '<strong>Spam</strong> entfernt Dein Thema aus dieser Liste und platziert es in der Spam-Warteschlange, aus der Du es endgültig löschen kannst.', 'psforum' ) . '</li>' .
					'<li>' . __( 'Die <strong>Vorschau</strong> zeigt Dir, wie Dein Themenentwurf aussehen wird, wenn Du ihn veröffentlichst. Die Ansicht führt Dich zu Deiner Live-Site, um das Thema anzuzeigen. Welcher Link verfügbar ist, hängt vom Status Deines Themas ab.', 'psforum' ) . '</li>' .
					'<li>' . __( '<strong>Schließen</strong> markiert das ausgewählte Thema als &#8217;geschlossen&#8217; und deaktiviere die Option zum Posten neuer Antworten zum Thema.', 'psforum' ) . '</li>' .
					'<li>' . __( '<strong>Stick</strong> hält das ausgewählte Thema &#8217;angeheftet&#8217; oben in der Themenliste des übergeordneten Forums.', 'psforum' ) . '</li>' .
					'<li>' . __( '<strong>Stick <em>(nach vorne)</em></strong> hält das ausgewählte Thema &#8217;angepinnt&#8217; an die Spitze ALLER Foren und in jeder Foren-Themenliste sichtbar sein.', 'psforum' ) . '</li>' .
				'</ul>'
		) );

		// Bulk Actions
		get_current_screen()->add_help_tab( array(
			'id'		=> 'bulk-actions',
			'title'		=> __( 'Massenaktionen', 'psforum' ),
			'content'	=>
				'<p>' . __( 'Du kannst auch mehrere Themen gleichzeitig bearbeiten oder in den Papierkorb verschieben. Wähle mithilfe der Kontrollkästchen die Themen aus, die Du bearbeiten möchtest, wähle dann die gewünschte Aktion aus dem Menü Massenaktionen aus und klicke auf Übernehmen.', 'psforum' ) . '</p>' .
				'<p>' . __( 'Wenn Sie Bulk Edit verwenden, können Sie die Metadaten (Kategorien, Autor usw.) für alle Ausgewählte auf einmal zu ändern. Um ein Thema aus der Gruppierung zu entfernen, klicke einfach auf das x neben seinem Namen im angezeigten Massenbearbeitungsbereich.', 'psforum' ) . '</p>'
		) );

		// Help Sidebar
		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'Für mehr Informationen:', 'psforum' ) . '</strong></p>' .
			'<p>' . __( '<a href="https://n3rds.work/docs/ps-forum-plugin-handbuch/" target="_blank">PS Forum Dokumentation</a>', 'psforum' ) . '</p>' .
			'<p>' . __( '<a href="https://n3rds.work/forums/forum/psource-support-foren/ps-forum-supportforum/" target="_blank">PS Forum Support Forum</a>', 'psforum' ) . '</p>'
		);
	}

	/**
	 * Contextual help for PSForum topic edit page
	 *
	 * @since PSForum (r3119)
	 * @uses get_current_screen()
	 */
	public function new_help() {

		if ( $this->bail() ) return;

		$customize_display = '<p>' . __( 'Das Titelfeld und der große Themenbearbeitungsbereich sind fixiert, aber Du kannst alle anderen Boxen per Drag-and-Drop neu positionieren und durch Klicken auf die Titelleiste der einzelnen Boxen minimieren oder erweitern. Verwende die Registerkarte Bildschirmoptionen, um weitere Felder (Auszug, Trackbacks senden, benutzerdefinierte Felder, Diskussion, Slug, Autor) einzublenden oder ein 1- oder 2-spaltiges Layout für diesen Bildschirm auszuwählen.', 'psforum' ) . '</p>';

		get_current_screen()->add_help_tab( array(
			'id'      => 'customize-display',
			'title'   => __( 'Anpassen dieser Anzeige', 'psforum' ),
			'content' => $customize_display,
		) );

		get_current_screen()->add_help_tab( array(
			'id'      => 'title-topic-editor',
			'title'   => __( 'Titel- und Themeneditor', 'psforum' ),
			'content' =>
				'<p>' . __( '<strong>Titel</strong> – Gib einen Titel für Dein Thema ein. Nachdem Du einen Titel eingegeben hast, siehst Du unten den Permalink, den Du bearbeiten kannst.', 'psforum' ) . '</p>' .
				'<p>' . __( '<strong>Themeneditor</strong> – Gib den Text für Dein Thema ein. Es gibt zwei Bearbeitungsmodi: Visual und HTML. Wähle den Modus aus, indem Du auf die entsprechende Registerkarte klickst. Der visuelle Modus bietet Dir einen WYSIWYG-Editor. Klicke auf das letzte Symbol in der Zeile, um eine zweite Zeile mit Steuerelementen anzuzeigen. Im HTML-Modus kannst Du zusammen mit Deinem Thementext Roh-HTML eingeben. Du kannst Mediendateien einfügen, indem Du auf die Symbole über dem Themeneditor klickst und den Anweisungen folgst. Zum ablenkungsfreien Schreibbildschirm gelangst Du über das Vollbild-Icon im visuellen Modus (vorletzter in der obersten Reihe) oder den Vollbild-Button im HTML-Modus (letzter in der Reihe). Sobald Du dort bist, kannst Du Schaltflächen sichtbar machen, indem Du mit der Maus über den oberen Bereich fährst. Beende den Vollbildmodus und kehre zum regulären Themeneditor zurück.', 'psforum' ) . '</p>'
		) );

		$publish_box = '<p>' . __( '<strong>Veröffentlichen</strong> – Du kannst die Bedingungen für die Veröffentlichung Deines Themas im Feld „Veröffentlichen“ festlegen. Klicke für Status, Sichtbarkeit und Veröffentlichen (sofort) auf den Link Bearbeiten, um weitere Optionen anzuzeigen. Die Sichtbarkeit umfasst Optionen zum Passwortschutz eines Themas oder zum dauerhaften Verbleib oben in Deinem Blog (Sticky). Mit (sofort) veröffentlichen kannst Du ein Datum und eine Uhrzeit in der Zukunft oder in der Vergangenheit festlegen, sodass Du die Veröffentlichung eines Themas in der Zukunft planen oder ein Thema zurückdatieren kannst.', 'psforum' ) . '</p>';

		if ( current_theme_supports( 'topic-thumbnails' ) && post_type_supports( 'topic', 'thumbnail' ) ) {
			$publish_box .= '<p>' . __( '<strong>Empfohlenes Bild</strong> – Hiermit kannst Du Deinem Thema ein Bild zuordnen, ohne es einzufügen. Dies ist normalerweise nur dann nützlich, wenn Dein Thema das vorgestellte Bild als Themen-Miniaturansicht auf der Startseite, als benutzerdefinierte Kopfzeile usw. verwendet.', 'psforum' ) . '</p>';
		}

		get_current_screen()->add_help_tab( array(
			'id'      => 'topic-attributes',
			'title'   => __( 'Themenattribute', 'psforum' ),
			'content' =>
				'<p>' . __( 'Wähle die Attribute aus, die Dein Thema haben soll:', 'psforum' ) . '</p>' .
				'<ul>' .
					'<li>' . __( 'Das Dropdown-Menü <strong>Forum</strong> bestimmt das übergeordnete Forum, zu dem das Thema gehört. Wähle das Forum oder die Kategorie aus der Dropdown-Liste aus oder belasse die Standardeinstellung (Kein Forum), um das Thema ohne zugewiesenes Forum zu veröffentlichen.', 'psforum' ) . '</li>' .
					'<li>' . __( 'Das Dropdown-Menü <strong>Thementyp</strong> zeigt den aktuellen Status des Themas an. Wenn Du die Option "Super Sticky" auswählst, wird das Thema im Vordergrund Deines Forums angezeigt, d.h. Die Auswahl von normal würde das Thema nirgendwo festhalten.', 'psforum' ) . '</li>' .
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
	 * Add the topic attributes metabox
	 *
	 * @since PSForum (r2744)
	 *
	 * @uses psf_get_topic_post_type() To get the topic post type
	 * @uses add_meta_box() To add the metabox
	 * @uses do_action() Calls 'psf_topic_attributes_metabox'
	 */
	public function attributes_metabox() {

		if ( $this->bail() ) return;

		add_meta_box (
			'psf_topic_attributes',
			__( 'Themenattribute', 'psforum' ),
			'psf_topic_metabox',
			$this->post_type,
			'side',
			'high'
		);

		do_action( 'psf_topic_attributes_metabox' );
	}

	/**
	 * Pass the topic attributes for processing
	 *
	 * @since PSForum (r2746)
	 *
	 * @param int $topic_id Topic id
	 * @uses current_user_can() To check if the current user is capable of
	 *                           editing the topic
	 * @uses do_action() Calls 'psf_topic_attributes_metabox_save' with the
	 *                    topic id and parent id
	 * @return int Parent id
	 */
	public function attributes_metabox_save( $topic_id ) {

		if ( $this->bail() ) return $topic_id;

		// Bail if doing an autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $topic_id;

		// Bail if not a post request
		if ( ! psf_is_post_request() )
			return $topic_id;

		// Nonce check
		if ( empty( $_POST['psf_topic_metabox'] ) || !wp_verify_nonce( $_POST['psf_topic_metabox'], 'psf_topic_metabox_save' ) )
			return $topic_id;

		// Bail if current user cannot edit this topic
		if ( !current_user_can( 'edit_topic', $topic_id ) )
			return $topic_id;

		// Get the forum ID
		$forum_id = !empty( $_POST['parent_id'] ) ? (int) $_POST['parent_id'] : 0;

		// Get topic author data
		$anonymous_data = psf_filter_anonymous_post_data();
		$author_id      = psf_get_topic_author_id( $topic_id );
		$is_edit        = (bool) isset( $_POST['save'] );

		// Formally update the topic
		psf_update_topic( $topic_id, $forum_id, $anonymous_data, $author_id, $is_edit );

		// Stickies
		if ( !empty( $_POST['psf_stick_topic'] ) && in_array( $_POST['psf_stick_topic'], array( 'stick', 'super', 'unstick' ) ) ) {

			// What's the haps?
			switch ( $_POST['psf_stick_topic'] ) {

				// Sticky in this forum
				case 'stick'   :
					psf_stick_topic( $topic_id );
					break;

				// Super sticky in all forums
				case 'super'   :
					psf_stick_topic( $topic_id, true );
					break;

				// Normal
				case 'unstick' :
				default        :
					psf_unstick_topic( $topic_id );
					break;
			}
		}

		// Allow other fun things to happen
		do_action( 'psf_topic_attributes_metabox_save', $topic_id, $forum_id       );
		do_action( 'psf_author_metabox_save',           $topic_id, $anonymous_data );

		return $topic_id;
	}

	/**
	 * Add the author info metabox
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

		// Bail if post_type is not a topic
		if ( empty( $_GET['action'] ) || ( 'edit' !== $_GET['action'] ) )
			return;

		// Add the metabox
		add_meta_box(
			'psf_author_metabox',
			__( 'Autor Information', 'psforum' ),
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
	 * Toggle topic
	 *
	 * Handles the admin-side opening/closing, sticking/unsticking and
	 * spamming/unspamming of topics
	 *
	 * @since PSForum (r2727)
	 *
	 * @uses psf_get_topic() To get the topic
	 * @uses current_user_can() To check if the user is capable of editing
	 *                           the topic
	 * @uses wp_die() To die if the user isn't capable or the post wasn't
	 *                 found
	 * @uses check_admin_referer() To verify the nonce and check referer
	 * @uses psf_is_topic_open() To check if the topic is open
	 * @uses psf_close_topic() To close the topic
	 * @uses psf_open_topic() To open the topic
	 * @uses psf_is_topic_sticky() To check if the topic is a sticky or
	 *                              super sticky
	 * @uses psf_unstick_topic() To unstick the topic
	 * @uses psf_stick_topic() To stick the topic
	 * @uses psf_is_topic_spam() To check if the topic is marked as spam
	 * @uses psf_unspam_topic() To unmark the topic as spam
	 * @uses psf_spam_topic() To mark the topic as spam
	 * @uses do_action() Calls 'psf_toggle_topic_admin' with success, post
	 *                    data, action and message
	 * @uses add_query_arg() To add custom args to the url
	 * @uses wp_safe_redirect() Redirect the page to custom url
	 */
	public function toggle_topic() {

		if ( $this->bail() ) return;

		// Only proceed if GET is a topic toggle action
		if ( psf_is_get_request() && !empty( $_GET['action'] ) && in_array( $_GET['action'], array( 'psf_toggle_topic_close', 'psf_toggle_topic_stick', 'psf_toggle_topic_spam' ) ) && !empty( $_GET['topic_id'] ) ) {
			$action    = $_GET['action'];            // What action is taking place?
			$topic_id  = (int) $_GET['topic_id'];    // What's the topic id?
			$success   = false;                      // Flag
			$post_data = array( 'ID' => $topic_id ); // Prelim array
			$topic     = psf_get_topic( $topic_id );

			// Bail if topic is missing
			if ( empty( $topic ) )
				wp_die( __( 'Das Thema wurde nicht gefunden!', 'psforum' ) );

			if ( !current_user_can( 'moderate', $topic->ID ) ) // What is the user doing here?
				wp_die( __( 'Du hast keine Berechtigung dazu!', 'psforum' ) );

			switch ( $action ) {
				case 'psf_toggle_topic_close' :
					check_admin_referer( 'close-topic_' . $topic_id );

					$is_open = psf_is_topic_open( $topic_id );
					$message = true === $is_open ? 'closed' : 'opened';
					$success = true === $is_open ? psf_close_topic( $topic_id ) : psf_open_topic( $topic_id );

					break;

				case 'psf_toggle_topic_stick' :
					check_admin_referer( 'stick-topic_' . $topic_id );

					$is_sticky = psf_is_topic_sticky( $topic_id );
					$is_super  = false === $is_sticky && !empty( $_GET['super'] ) && ( "1" === $_GET['super'] ) ? true : false;
					$message   = true  === $is_sticky ? 'unsticked'     : 'sticked';
					$message   = true  === $is_super  ? 'super_sticked' : $message;
					$success   = true  === $is_sticky ? psf_unstick_topic( $topic_id ) : psf_stick_topic( $topic_id, $is_super );

					break;

				case 'psf_toggle_topic_spam'  :
					check_admin_referer( 'spam-topic_' . $topic_id );

					$is_spam = psf_is_topic_spam( $topic_id );
					$message = true === $is_spam ? 'unspammed' : 'spammed';
					$success = true === $is_spam ? psf_unspam_topic( $topic_id ) : psf_spam_topic( $topic_id );

					break;
			}

			$message = array( 'psf_topic_toggle_notice' => $message, 'topic_id' => $topic->ID );

			if ( false === $success || is_wp_error( $success ) )
				$message['failed'] = '1';

			// Do additional topic toggle actions (admin side)
			do_action( 'psf_toggle_topic_admin', $success, $post_data, $action, $message );

			// Redirect back to the topic
			$redirect = add_query_arg( $message, remove_query_arg( array( 'action', 'topic_id' ) ) );
			wp_safe_redirect( $redirect );

			// For good measure
			exit();
		}
	}

	/**
	 * Toggle topic notices
	 *
	 * Display the success/error notices from
	 * {@link PSF_Admin::toggle_topic()}
	 *
	 * @since PSForum (r2727)
	 *
	 * @uses psf_get_topic() To get the topic
	 * @uses psf_get_topic_title() To get the topic title of the topic
	 * @uses esc_html() To sanitize the topic title
	 * @uses apply_filters() Calls 'psf_toggle_topic_notice_admin' with
	 *                        message, topic id, notice and is it a failure
	 */
	public function toggle_topic_notice() {

		if ( $this->bail() ) return;

		// Only proceed if GET is a topic toggle action
		if ( psf_is_get_request() && !empty( $_GET['psf_topic_toggle_notice'] ) && in_array( $_GET['psf_topic_toggle_notice'], array( 'opened', 'closed', 'super_sticked', 'sticked', 'unsticked', 'spammed', 'unspammed' ) ) && !empty( $_GET['topic_id'] ) ) {
			$notice     = $_GET['psf_topic_toggle_notice'];         // Which notice?
			$topic_id   = (int) $_GET['topic_id'];                  // What's the topic id?
			$is_failure = !empty( $_GET['failed'] ) ? true : false; // Was that a failure?

			// Bais if no topic_id or notice
			if ( empty( $notice ) || empty( $topic_id ) )
				return;

			// Bail if topic is missing
			$topic = psf_get_topic( $topic_id );
			if ( empty( $topic ) )
				return;

			$topic_title = psf_get_topic_title( $topic->ID );

			switch ( $notice ) {
				case 'opened'    :
					$message = $is_failure === true ? sprintf( __( 'Beim Öffnen des Themas "%1$s" ist ein Problem aufgetreten.', 'psforum' ), $topic_title ) : sprintf( __( 'Topic "%1$s" successfully opened.',           'psforum' ), $topic_title );
					break;

				case 'closed'    :
					$message = $is_failure === true ? sprintf( __( 'Beim Schließen des Themas "%1$s" ist ein Problem aufgetreten.', 'psforum' ), $topic_title ) : sprintf( __( 'Topic "%1$s" successfully closed.',           'psforum' ), $topic_title );
					break;

				case 'super_sticked' :
					$message = $is_failure === true ? sprintf( __( 'Beim Anhängen des Themas "%1$s" ist ein Problem aufgetreten.', 'psforum' ), $topic_title ) : sprintf( __( 'Topic "%1$s" successfully sticked to front.', 'psforum' ), $topic_title );
					break;

				case 'sticked'   :
					$message = $is_failure === true ? sprintf( __( 'Beim Festhalten des Themas "%1$s" ist ein Problem aufgetreten..', 'psforum' ), $topic_title ) : sprintf( __( 'Topic "%1$s" successfully sticked.',          'psforum' ), $topic_title );
					break;

				case 'unsticked' :
					$message = $is_failure === true ? sprintf( __( 'Beim Trennen des Themas "%1$s" ist ein Problem aufgetreten..', 'psforum' ), $topic_title ) : sprintf( __( 'Topic "%1$s" successfully unsticked.',        'psforum' ), $topic_title );
					break;

				case 'spammed'   :
					$message = $is_failure === true ? sprintf( __( 'Beim Markieren des Themas "%1$s" als Spam ist ein Problem aufgetreten.', 'psforum' ), $topic_title ) : sprintf( __( 'Topic "%1$s" successfully marked as spam.',   'psforum' ), $topic_title );
					break;

				case 'unspammed' :
					$message = $is_failure === true ? sprintf( __( 'Beim Aufheben der Markierung des Themas "%1$s" als Spam ist ein Problem aufgetreten.', 'psforum' ), $topic_title ) : sprintf( __( 'Topic "%1$s" successfully unmarked as spam.', 'psforum' ), $topic_title );
					break;
			}

			// Do additional topic toggle notice filters (admin side)
			$message = apply_filters( 'psf_toggle_topic_notice_admin', $message, $topic->ID, $notice, $is_failure );

			?>

			<div id="message" class="<?php echo $is_failure === true ? 'error' : 'updated'; ?> fade">
				<p style="line-height: 150%"><?php echo esc_html( $message ); ?></p>
			</div>

			<?php
		}
	}

	/**
	 * Manage the column headers for the topics page
	 *
	 * @since PSForum (r2485)
	 *
	 * @param array $columns The columns
	 * @uses apply_filters() Calls 'psf_admin_topics_column_headers' with
	 *                        the columns
	 * @return array $columns PSForum topic columns
	 */
	public function column_headers( $columns ) {

		if ( $this->bail() ) return $columns;

		$columns = array(
			'cb'                    => '<input type="checkbox" />',
			'title'                 => __( 'Themen',    'psforum' ),
			'psf_topic_forum'       => __( 'Forum',     'psforum' ),
			'psf_topic_reply_count' => __( 'Antworten',   'psforum' ),
			'psf_topic_voice_count' => __( 'Stimmen',    'psforum' ),
			'psf_topic_author'      => __( 'Autor',    'psforum' ),
			'psf_topic_created'     => __( 'Erstellt',   'psforum' ),
			'psf_topic_freshness'   => __( 'Frische', 'psforum' )
		);

		return apply_filters( 'psf_admin_topics_column_headers', $columns );
	}

	/**
	 * Print extra columns for the topics page
	 *
	 * @since PSForum (r2485)
	 *
	 * @param string $column Column
	 * @param int $topic_id Topic id
	 * @uses psf_get_topic_forum_id() To get the forum id of the topic
	 * @uses psf_forum_title() To output the topic's forum title
	 * @uses apply_filters() Calls 'topic_forum_row_actions' with an array
	 *                        of topic forum actions
	 * @uses psf_get_forum_permalink() To get the forum permalink
	 * @uses admin_url() To get the admin url of post.php
	 * @uses psf_topic_reply_count() To output the topic reply count
	 * @uses psf_topic_voice_count() To output the topic voice count
	 * @uses psf_topic_author_display_name() To output the topic author name
	 * @uses get_the_date() Get the topic creation date
	 * @uses get_the_time() Get the topic creation time
	 * @uses esc_attr() To sanitize the topic creation time
	 * @uses psf_get_topic_last_active_time() To get the time when the topic was
	 *                                    last active
	 * @uses do_action() Calls 'psf_admin_topics_column_data' with the
	 *                    column and topic id
	 */
	public function column_data( $column, $topic_id ) {

		if ( $this->bail() ) return;

		// Get topic forum ID
		$forum_id = psf_get_topic_forum_id( $topic_id );

		// Populate column data
		switch ( $column ) {

			// Forum
			case 'psf_topic_forum' :

				// Output forum name
				if ( !empty( $forum_id ) ) {

					// Forum Title
					$forum_title = psf_get_forum_title( $forum_id );
					if ( empty( $forum_title ) ) {
						$forum_title = esc_html__( 'Kein Forum', 'psforum' );
					}

					// Output the title
					echo $forum_title;

				} else {
					esc_html_e( '(Kein Forum)', 'psforum' );
				}

				break;

			// Reply Count
			case 'psf_topic_reply_count' :
				psf_topic_reply_count( $topic_id );
				break;

			// Reply Count
			case 'psf_topic_voice_count' :
				psf_topic_voice_count( $topic_id );
				break;

			// Author
			case 'psf_topic_author' :
				psf_topic_author_display_name( $topic_id );
				break;

			// Freshness
			case 'psf_topic_created':
				printf( '%1$s <br /> %2$s',
					get_the_date(),
					esc_attr( get_the_time() )
				);

				break;

			// Freshness
			case 'psf_topic_freshness' :
				$last_active = psf_get_topic_last_active_time( $topic_id, false );
				if ( !empty( $last_active ) ) {
					echo esc_html( $last_active );
				} else {
					esc_html_e( 'Keine Antworten', 'psforum' ); // This should never happen
				}

				break;

			// Do an action for anything else
			default :
				do_action( 'psf_admin_topics_column_data', $column, $topic_id );
				break;
		}
	}

	/**
	 * Topic Row actions
	 *
	 * Remove the quick-edit action link under the topic title and add the
	 * content and close/stick/spam links
	 *
	 * @since PSForum (r2485)
	 *
	 * @param array $actions Actions
	 * @param array $topic Topic object
	 * @uses psf_get_topic_post_type() To get the topic post type
	 * @uses psf_topic_content() To output topic content
	 * @uses psf_get_topic_permalink() To get the topic link
	 * @uses psf_get_topic_title() To get the topic title
	 * @uses current_user_can() To check if the current user can edit or
	 *                           delete the topic
	 * @uses psf_is_topic_open() To check if the topic is open
	 * @uses psf_is_topic_spam() To check if the topic is marked as spam
	 * @uses psf_is_topic_sticky() To check if the topic is a sticky or a
	 *                              super sticky
	 * @uses get_post_type_object() To get the topic post type object
	 * @uses add_query_arg() To add custom args to the url
	 * @uses remove_query_arg() To remove custom args from the url
	 * @uses wp_nonce_url() To nonce the url
	 * @uses get_delete_post_link() To get the delete post link of the topic
	 * @return array $actions Actions
	 */
	public function row_actions( $actions, $topic ) {

		if ( $this->bail() ) return $actions;

		unset( $actions['inline hide-if-no-js'] );

		// Show view link if it's not set, the topic is trashed and the user can view trashed topics
		if ( empty( $actions['view'] ) && ( psf_get_trash_status_id() === $topic->post_status ) && current_user_can( 'view_trash' ) )
			$actions['view'] = '<a href="' . esc_url( psf_get_topic_permalink( $topic->ID ) ) . '" title="' . esc_attr( sprintf( __( 'Ansehen &#8220;%s&#8221;', 'psforum' ), psf_get_topic_title( $topic->ID ) ) ) . '" rel="permalink">' . esc_html__( 'Ansehen', 'psforum' ) . '</a>';

		// Only show the actions if the user is capable of viewing them :)
		if ( current_user_can( 'moderate', $topic->ID ) ) {

			// Close
			// Show the 'close' and 'open' link on published and closed posts only
			if ( in_array( $topic->post_status, array( psf_get_public_status_id(), psf_get_closed_status_id() ) ) ) {
				$close_uri = wp_nonce_url( add_query_arg( array( 'topic_id' => $topic->ID, 'action' => 'psf_toggle_topic_close' ), remove_query_arg( array( 'psf_topic_toggle_notice', 'topic_id', 'failed', 'super' ) ) ), 'close-topic_' . $topic->ID );
				if ( psf_is_topic_open( $topic->ID ) )
					$actions['closed'] = '<a href="' . esc_url( $close_uri ) . '" title="' . esc_attr__( 'Dieses Thema schließen', 'psforum' ) . '">' . _x( 'Schließen', 'Thema schließen', 'psforum' ) . '</a>';
				else
					$actions['closed'] = '<a href="' . esc_url( $close_uri ) . '" title="' . esc_attr__( 'Dieses Thema öffnen',  'psforum' ) . '">' . _x( 'Öffnen',  'Öffne ein Thema',  'psforum' ) . '</a>';
			}

			// Dont show sticky if topic links is spam or trash
			if ( !psf_is_topic_spam( $topic->ID ) && !psf_is_topic_trash( $topic->ID ) ) {

				// Sticky
				$stick_uri  = wp_nonce_url( add_query_arg( array( 'topic_id' => $topic->ID, 'action' => 'psf_toggle_topic_stick' ), remove_query_arg( array( 'psf_topic_toggle_notice', 'topic_id', 'failed', 'super' ) ) ), 'stick-topic_'  . $topic->ID );
				if ( psf_is_topic_sticky( $topic->ID ) ) {
					$actions['stick'] = '<a href="' . esc_url( $stick_uri ) . '" title="' . esc_attr__( 'Dieses Thema lösen', 'psforum' ) . '">' . esc_html__( 'Lösen', 'psforum' ) . '</a>';
				} else {
					$super_uri        = wp_nonce_url( add_query_arg( array( 'topic_id' => $topic->ID, 'action' => 'psf_toggle_topic_stick', 'super' => '1' ), remove_query_arg( array( 'psf_topic_toggle_notice', 'topic_id', 'failed', 'super' ) ) ), 'stick-topic_'  . $topic->ID );
					$actions['stick'] = '<a href="' . esc_url( $stick_uri ) . '" title="' . esc_attr__( 'Hefte dieses Thema in seinem Forum an', 'psforum' ) . '">' . esc_html__( 'Stick', 'psforum' ) . '</a> <a href="' . esc_url( $super_uri ) . '" title="' . esc_attr__( 'Halte dieses Thema oben', 'psforum' ) . '">' . esc_html__( '(nach oben)', 'psforum' ) . '</a>';
				}
			}

			// Spam
			$spam_uri  = wp_nonce_url( add_query_arg( array( 'topic_id' => $topic->ID, 'action' => 'psf_toggle_topic_spam' ), remove_query_arg( array( 'psf_topic_toggle_notice', 'topic_id', 'failed', 'super' ) ) ), 'spam-topic_'  . $topic->ID );
			if ( psf_is_topic_spam( $topic->ID ) )
				$actions['spam'] = '<a href="' . esc_url( $spam_uri ) . '" title="' . esc_attr__( 'Markiere das Thema als kein Spam', 'psforum' ) . '">' . esc_html__( 'Kein Spam', 'psforum' ) . '</a>';
			else
				$actions['spam'] = '<a href="' . esc_url( $spam_uri ) . '" title="' . esc_attr__( 'Dieses Thema als Spam markieren', 'psforum' ) . '">' . esc_html__( 'Spam',     'psforum' ) . '</a>';

		}

		// Do not show trash links for spam topics, or spam links for trashed topics
		if ( current_user_can( 'delete_topic', $topic->ID ) ) {
			if ( psf_get_trash_status_id() === $topic->post_status ) {
				$post_type_object   = get_post_type_object( psf_get_topic_post_type() );
				$actions['untrash'] = "<a title='" . esc_attr__( 'Dieses Element aus dem Papierkorb wiederherstellen', 'psforum' ) . "' href='" . esc_url( wp_nonce_url( add_query_arg( array( '_wp_http_referer' => add_query_arg( array( 'post_type' => psf_get_topic_post_type() ), admin_url( 'edit.php' ) ) ), admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $topic->ID ) ) ), 'untrash-' . $topic->post_type . '_' . $topic->ID ) ) . "'>" . esc_html__( 'Wiederherstellen', 'psforum' ) . "</a>";
			} elseif ( EMPTY_TRASH_DAYS ) {
				$actions['trash'] = "<a class='submitdelete' title='" . esc_attr__( 'Dieses Element in den Papierkorb verschieben', 'psforum' ) . "' href='" . esc_url( add_query_arg( array( '_wp_http_referer' => add_query_arg( array( 'post_type' => psf_get_topic_post_type() ), admin_url( 'edit.php' ) ) ), get_delete_post_link( $topic->ID ) ) ) . "'>" . esc_html__( 'Papierkorb', 'psforum' ) . "</a>";
			}

			if ( psf_get_trash_status_id() === $topic->post_status || !EMPTY_TRASH_DAYS ) {
				$actions['delete'] = "<a class='submitdelete' title='" . esc_attr__( 'Dieses Element endgültig löschen', 'psforum' ) . "' href='" . esc_url( add_query_arg( array( '_wp_http_referer' => add_query_arg( array( 'post_type' => psf_get_topic_post_type() ), admin_url( 'edit.php' ) ) ), get_delete_post_link( $topic->ID, '', true ) ) ) . "'>" . esc_html__( 'Dauerhaft löschen', 'psforum' ) . "</a>";
			} elseif ( psf_get_spam_status_id() === $topic->post_status ) {
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
	function filter_post_rows( $query_vars ) {

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
	 * Custom user feedback messages for topic post type
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
		$topic_url = psf_get_topic_permalink( $post_ID );

		// Current topic's post_date
		$post_date = psf_get_global_post_field( 'post_date', 'raw' );

		// Messages array
		$messages[$this->post_type] = array(
			0 =>  '', // Left empty on purpose

			// Updated
			1 =>  sprintf( __( 'Thema aktualisiert. <a href="%s">Thema anzeigen</a>', 'psforum' ), $topic_url ),

			// Custom field updated
			2 => __( 'Benutzerdefiniertes Feld aktualisiert.', 'psforum' ),

			// Custom field deleted
			3 => __( 'Benutzerdefiniertes Feld gelöscht.', 'psforum' ),

			// Topic updated
			4 => __( 'Thema aktualisiert.', 'psforum' ),

			// Restored from revision
			// translators: %s: date and time of the revision
			5 => isset( $_GET['revision'] )
					? sprintf( __( 'Thema in Überarbeitung von %s wiederhergestellt', 'psforum' ), wp_post_revision_title( (int) $_GET['revision'], false ) )
					: false,

			// Topic created
			6 => sprintf( __( 'Thema erstellt. <a href="%s">Thema anzeigen</a>', 'psforum' ), $topic_url ),

			// Topic saved
			7 => __( 'Thema gespeichert.', 'psforum' ),

			// Topic submitted
			8 => sprintf( __( 'Thema eingereicht. <a target="_blank" href="%s">Vorschau Thema</a>', 'psforum' ), esc_url( add_query_arg( 'preview', 'true', $topic_url ) ) ),

			// Topic scheduled
			9 => sprintf( __( 'Thema geplant für: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Vorschau Thema</a>', 'psforum' ),
					// translators: Publish box date format, see http://php.net/date
					date_i18n( __( 'M j, Y @ G:i', 'psforum' ),
					strtotime( $post_date ) ),
					$topic_url ),

			// Topic draft updated
			10 => sprintf( __( 'Themenentwurf aktualisiert. <a target="_blank" href="%s">Vorschau Thema</a>', 'psforum' ), esc_url( add_query_arg( 'preview', 'true', $topic_url ) ) ),
		);

		return $messages;
	}
}
endif; // class_exists check

/**
 * Setup PSForum Topics Admin
 *
 * This is currently here to make hooking and unhooking of the admin UI easy.
 * It could use dependency injection in the future, but for now this is easier.
 *
 * @since PSForum (r2596)
 *
 * @uses PSF_Forums_Admin
 */
function psf_admin_topics() {
	psforum()->admin->topics = new PSF_Topics_Admin();
}
