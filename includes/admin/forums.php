<?php

/**
 * PSForum Forum Admin Class
 *
 * @package PSForum
 * @subpackage Administration
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'PSF_Forums_Admin' ) ) :
/**
 * Loads PSForum forums admin area
 *
 * @package PSForum
 * @subpackage Administration
 * @since PSForum (r2464)
 */
class PSF_Forums_Admin {

	/** Variables *************************************************************/

	/**
	 * @var The post type of this admin component
	 */
	private $post_type = '';

	/** Functions *************************************************************/

	/**
	 * The main PSForum forums admin loader
	 *
	 * @since PSForum (r2515)
	 *
	 * @uses PSF_Forums_Admin::setup_globals() Setup the globals needed
	 * @uses PSF_Forums_Admin::setup_actions() Setup the hooks and actions
	 * @uses PSF_Forums_Admin::setup_help() Setup the help text
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
		add_action( 'psf_admin_head', array( $this, 'admin_head' ) );

		// Messages
		add_filter( 'post_updated_messages', array( $this, 'updated_messages' ) );

		// Metabox actions
		add_action( 'add_meta_boxes', array( $this, 'attributes_metabox' ) );
		add_action( 'save_post', array( $this, 'attributes_metabox_save' ) );

		// Column headers.
		add_filter( 'manage_' . $this->post_type . '_posts_columns', array( $this, 'column_headers' ) );

		// Columns (in page row)
		add_action( 'manage_' . $this->post_type . '_posts_custom_column', array( $this, 'column_data' ), 10, 2 );
		add_filter( 'page_row_actions', array( $this, 'row_actions' ), 10, 2 );

		// Contextual Help
		add_action( 'load-edit.php', array( $this, 'edit_help' ) );
		add_action( 'load-post.php', array( $this, 'new_help'  ) );
		add_action( 'load-post-new.php', array( $this, 'new_help'  ) );
	}

	/**
	 * Should we bail out of this method?
	 *
	 * @since PSForum (r4067)
	 * @return boolean
	 */
	private function bail() {
		if ( !isset( get_current_screen()->post_type ) || ( $this->post_type != get_current_screen()->post_type ) )
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
		$this->post_type = psf_get_forum_post_type();
	}

	/** Contextual Help *******************************************************/

	/**
	 * Contextual help for PSForum forum edit page
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
				'<p>' . __( 'Dieser Bildschirm zeigt die einzelnen Foren auf Deiner Webseite an. Du kannst die Anzeige dieses Bildschirms an Deinen Workflow anpassen.', 'psforum' ) . '</p>'
		) );

		// Screen Content
		get_current_screen()->add_help_tab( array(
			'id'		=> 'screen-content',
			'title'		=> __( 'Bildschirminhalt', 'psforum' ),
			'content'	=>
				'<p>' . __( 'Du kannst die Anzeige des Inhalts dieses Bildschirms auf verschiedene Weise anpassen:', 'psforum' ) . '</p>' .
				'<ul>' .
					'<li>' . __( 'Du kannst Spalten nach Deinen Bedürfnissen ausblenden/anzeigen und über die Registerkarte Bildschirmoptionen entscheiden, wie viele Foren pro Bildschirm aufgelistet werden sollen.', 'psforum' ) . '</li>' .
					'<li>' . __( 'Du kannst die Liste der Foren nach Forenstatus filtern, indem Du die Textlinks oben links verwendest, um alle, veröffentlichten oder im Papierkorb abgelegten Foren anzuzeigen. In der Standardansicht werden alle Foren angezeigt.', 'psforum' ) . '</li>' .
					'<li>' . __( 'Du kannst die Liste so verfeinern, dass nur Foren aus einem bestimmten Monat angezeigt werden, indem Du die Dropdown-Menüs über der Forenliste verwendest. Klicke auf die Schaltfläche Filter, nachdem Du Deine Auswahl getroffen hast. Du kannst die Liste auch verfeinern, indem Du in der Forenliste auf den Forenersteller klickst.', 'psforum' ) . '</li>' .
				'</ul>'
		) );

		// Available Actions
		get_current_screen()->add_help_tab( array(
			'id'		=> 'action-links',
			'title'		=> __( 'Verfügbare Aktionen', 'psforum' ),
			'content'	=>
				'<p>' . __( 'Wenn Du mit der Maus über eine Zeile in der Forenliste fährst, werden Aktionslinks angezeigt, mit denen Du Dein Forum verwalten kannst. Du kannst die folgenden Aktionen ausführen:', 'psforum' ) . '</p>' .
				'<ul>' .
					'<li>' . __( '<strong>Bearbeiten</strong> führt Dich zum Bearbeitungsbildschirm für dieses Forum. Du kannst diesen Bildschirm auch erreichen, indem Du auf den Forumstitel klickst.', 'psforum' ) . '</li>' .
					'<li>' . __( '<strong>Papierkorb</strong> entfernt Dein Forum aus dieser Liste und legt es in den Papierkorb, aus dem Du es endgültig löschen kannst.', 'psforum' ) . '</li>' .
					'<li>' . __( '<strong>Ansicht</strong> zeigt Dir, wie Dein Forumsentwurf aussehen wird, wenn Du ihn veröffentlichst. Ansehen führt Dich zu Deiner Live-Site, um das Forum anzuzeigen. Welcher Link verfügbar ist, hängt vom Status Deines Forums ab.', 'psforum' ) . '</li>' .
				'</ul>'
		) );

		// Bulk Actions
		get_current_screen()->add_help_tab( array(
			'id'		=> 'bulk-actions',
			'title'		=> __( 'Massenaktionen', 'psforum' ),
			'content'	=>
				'<p>' . __( 'Du kannst auch mehrere Foren gleichzeitig bearbeiten oder in den Papierkorb verschieben. Wähle die Foren aus, in denen Du mit den Kontrollkästchen handeln möchtest, wähle dann die gewünschte Aktion aus dem Menü Massenaktionen aus und klicke auf Übernehmen.', 'psforum' ) . '</p>' .
				'<p>' . __( 'Bei der Massenbearbeitung kannst Du die Metadaten (Kategorien, Autor usw.) für alle ausgewählten Foren gleichzeitig ändern. Um ein Forum aus der Gruppierung zu entfernen, klicke einfach auf das x neben seinem Namen im angezeigten Massenbearbeitungsbereich.', 'psforum' ) . '</p>'
		) );

		// Help Sidebar
		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'Für mehr Informationen:', 'psforum' ) . '</strong></p>' .
			'<p>' . __( '<a href="https://n3rds.work/docs/ps-forum-plugin-handbuch/" target="_blank">PS Forum Dokumentation</a>', 'psforum' ) . '</p>' .
			'<p>' . __( '<a href="https://n3rds.work/forums/forum/psource-support-foren/ps-forum-supportforum/" target="_blank">PS Forum Support Forum</a>', 'psforum' ) . '</p>'
		);
	}

	/**
	 * Contextual help for PSForum forum edit page
	 *
	 * @since PSForum (r3119)
	 * @uses get_current_screen()
	 */
	public function new_help() {

		if ( $this->bail() ) return;

		$customize_display = '<p>' . __( 'Das Titelfeld und der große Forenbearbeitungsbereich sind fest vorgegeben, aber Du kannst alle anderen Boxen per Drag-and-Drop neu positionieren und durch Klicken auf die Titelleiste der einzelnen Boxen minimieren oder erweitern. Verwende die Registerkarte Bildschirmoptionen, um weitere Felder (Auszug, Trackbacks senden, benutzerdefinierte Felder, Diskussion, Slug, Autor) einzublenden oder ein 1- oder 2-spaltiges Layout für diesen Bildschirm auszuwählen.', 'psforum' ) . '</p>';

		get_current_screen()->add_help_tab( array(
			'id'      => 'customize-display',
			'title'   => __( 'Anpassen dieser Anzeige', 'psforum' ),
			'content' => $customize_display,
		) );

		get_current_screen()->add_help_tab( array(
			'id'      => 'title-forum-editor',
			'title'   => __( 'Titel- und Forumseditor', 'psforum' ),
			'content' =>
				'<p>' . __( '<strong>Titel</strong> – Gib einen Titel für Dein Forum ein. Nachdem Du einen Titel eingegeben hast, siehe unten den Permalink, den Du bearbeiten kannst.', 'psforum' ) . '</p>' .
				'<p>' . __( '<strong>Forum-Editor</strong> – Gib den Text für Dein Forum ein. Es gibt zwei Bearbeitungsmodi: Visual und HTML. Wähle den Modus aus, indem Du auf die entsprechende Registerkarte klickst. Der visuelle Modus bietet Dir einen WYSIWYG-Editor. Klicke auf das letzte Symbol in der Zeile, um eine zweite Zeile mit Steuerelementen anzuzeigen. Im HTML-Modus kannst Du zusammen mit Deinem Forentext Roh-HTML eingeben. Du kannst Mediendateien einfügen, indem Du auf die Symbole über dem Forumseditor klickst und den Anweisungen folgst. Zum ablenkungsfreien Schreibbildschirm gelangst Du über das Vollbild-Icon im visuellen Modus (vorletzter in der obersten Reihe) oder über den Vollbild-Button im HTML-Modus (letzter in der Reihe). Sobald Du dort bist, kannst Du Schaltflächen sichtbar machen, indem Du mit der Maus über den oberen Bereich fährst. Beende den Vollbildmodus und kehre zum regulären Forumseditor zurück.', 'psforum' ) . '</p>'
		) );

		$publish_box = '<p>' . __( '<strong>Veröffentlichen</strong> – Du kannst die Bedingungen für die Veröffentlichung Deines Forums im Feld „Veröffentlichen“ festlegen. Klicke für Status, Sichtbarkeit und Veröffentlichen (sofort) auf den Link Bearbeiten, um weitere Optionen anzuzeigen. Die Sichtbarkeit umfasst Optionen, um ein Forum mit einem Passwort zu schützen oder es auf unbestimmte Zeit ganz oben in Deinem Blog zu halten (sticky). Mit (sofort) veröffentlichen kannst Du ein Datum und eine Uhrzeit in der Zukunft oder in der Vergangenheit festlegen, sodass Du die Veröffentlichung eines Forums in der Zukunft planen oder ein Forum zurückdatieren kannst.', 'psforum' ) . '</p>';

		if ( current_theme_supports( 'forum-thumbnails' ) && post_type_supports( 'forum', 'thumbnail' ) ) {
			$publish_box .= '<p>' . __( '<strong>Empfohlenes Bild</strong> – Hiermit kannst Du ein Bild mit Deinem Forum verknüpfen, ohne es einzufügen. Dies ist normalerweise nur dann nützlich, wenn Dein Theme das vorgestellte Bild als Forum-Miniaturansicht auf der Startseite, als benutzerdefinierter Header usw. verwendet.', 'psforum' ) . '</p>';
		}

		get_current_screen()->add_help_tab( array(
			'id'      => 'forum-attributes',
			'title'   => __( 'Forum-Attribute', 'psforum' ),
			'content' =>
				'<p>' . __( 'Wähle die Attribute aus, die Dein Forum haben soll:', 'psforum' ) . '</p>' .
				'<ul>' .
					'<li>' . __( '<strong>Typ</strong> gibt an, ob das Forum eine Kategorie oder ein Forum ist. Kategorien enthalten im Allgemeinen andere Foren.', 'psforum' ) . '</li>' .
					'<li>' . __( '<strong>Status</strong> ermöglicht es Dir, ein Forum für neue Themen und Foren zu schließen.', 'psforum' ) . '</li>' .
					'<li>' . __( 'Mit <strong>Sichtbarkeit</strong> kannst Du den Umfang jedes Forums und die Zugriffsberechtigungen der Nutzer auswählen.', 'psforum' ) . '</li>' .
					'<li>' . __( 'Das Dropdown-Menü <strong>Eltern</strong> bestimmt das übergeordnete Forum. Wähle das Forum oder die Kategorie aus der Dropdown-Liste aus oder belasse die Standardeinstellung (Kein Elternteil), um das Forum im Stammverzeichnis Deines Forums zu erstellen.', 'psforum' ) . '</li>' .
					'<li>' . __( 'Mit <strong>Sortieren</strong> kannst Du Deine Foren numerisch ordnen.', 'psforum' ) . '</li>' .
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
	 * Add the forum attributes metabox
	 *
	 * @since PSForum (r2746)
	 *
	 * @uses psf_get_forum_post_type() To get the forum post type
	 * @uses add_meta_box() To add the metabox
	 * @uses do_action() Calls 'psf_forum_attributes_metabox'
	 */
	public function attributes_metabox() {

		if ( $this->bail() ) return;

		add_meta_box (
			'psf_forum_attributes',
			__( 'Forum-Attribute', 'psforum' ),
			'psf_forum_metabox',
			$this->post_type,
			'side',
			'high'
		);

		do_action( 'psf_forum_attributes_metabox' );
	}

	/**
	 * Pass the forum attributes for processing
	 *
	 * @since PSForum (r2746)
	 *
	 * @param int $forum_id Forum id
	 * @uses current_user_can() To check if the current user is capable of
	 *                           editing the forum
	 * @uses psf_get_forum() To get the forum
	 * @uses psf_is_forum_closed() To check if the forum is closed
	 * @uses psf_is_forum_category() To check if the forum is a category
	 * @uses psf_is_forum_private() To check if the forum is private
	 * @uses psf_close_forum() To close the forum
	 * @uses psf_open_forum() To open the forum
	 * @uses psf_categorize_forum() To make the forum a category
	 * @uses psf_normalize_forum() To make the forum normal (not category)
	 * @uses psf_privatize_forum() To mark the forum as private
	 * @uses psf_publicize_forum() To mark the forum as public
	 * @uses do_action() Calls 'psf_forum_attributes_metabox_save' with the
	 *                    forum id
	 * @return int Forum id
	 */
	public function attributes_metabox_save( $forum_id ) {

		if ( $this->bail() ) return $forum_id;

		// Bail if doing an autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $forum_id;

		// Bail if not a post request
		if ( ! psf_is_post_request() )
			return $forum_id;

		// Nonce check
		if ( empty( $_POST['psf_forum_metabox'] ) || !wp_verify_nonce( $_POST['psf_forum_metabox'], 'psf_forum_metabox_save' ) )
			return $forum_id;

		// Only save for forum post-types
		if ( ! psf_is_forum( $forum_id ) )
			return $forum_id;

		// Bail if current user cannot edit this forum
		if ( !current_user_can( 'edit_forum', $forum_id ) )
			return $forum_id;

		// Parent ID
		$parent_id = ( !empty( $_POST['parent_id'] ) && is_numeric( $_POST['parent_id'] ) ) ? (int) $_POST['parent_id'] : 0;

		// Update the forum meta bidness
		psf_update_forum( array(
			'forum_id'    => $forum_id,
			'post_parent' => (int) $parent_id
		) );

		do_action( 'psf_forum_attributes_metabox_save', $forum_id );

		return $forum_id;
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

			#misc-publishing-actions,
			#save-post {
				display: none;
			}

			strong.label {
				display: inline-block;
				width: 60px;
			}

			#psf_forum_attributes hr {
				border-style: solid;
				border-width: 1px;
				border-color: #ccc #fff #fff #ccc;
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
	 * Manage the column headers for the forums page
	 *
	 * @since PSForum (r2485)
	 *
	 * @param array $columns The columns
	 * @uses apply_filters() Calls 'psf_admin_forums_column_headers' with
	 *                        the columns
	 * @return array $columns PSForum forum columns
	 */
	public function column_headers( $columns ) {

		if ( $this->bail() ) return $columns;

		$columns = array (
			'cb'                    => '<input type="checkbox" />',
			'title'                 => __( 'Forum',     'psforum' ),
			'psf_forum_topic_count' => __( 'Themen',    'psforum' ),
			'psf_forum_reply_count' => __( 'Antworten',   'psforum' ),
			'author'                => __( 'Ersteller',   'psforum' ),
			'psf_forum_created'     => __( 'Erstellt' ,  'psforum' ),
			'psf_forum_freshness'   => __( 'Frische', 'psforum' )
		);

		return apply_filters( 'psf_admin_forums_column_headers', $columns );
	}

	/**
	 * Print extra columns for the forums page
	 *
	 * @since PSForum (r2485)
	 *
	 * @param string $column Column
	 * @param int $forum_id Forum id
	 * @uses psf_forum_topic_count() To output the forum topic count
	 * @uses psf_forum_reply_count() To output the forum reply count
	 * @uses get_the_date() Get the forum creation date
	 * @uses get_the_time() Get the forum creation time
	 * @uses esc_attr() To sanitize the forum creation time
	 * @uses psf_get_forum_last_active_time() To get the time when the forum was
	 *                                    last active
	 * @uses do_action() Calls 'psf_admin_forums_column_data' with the
	 *                    column and forum id
	 */
	public function column_data( $column, $forum_id ) {

		if ( $this->bail() ) return;

		switch ( $column ) {
			case 'psf_forum_topic_count' :
				psf_forum_topic_count( $forum_id );
				break;

			case 'psf_forum_reply_count' :
				psf_forum_reply_count( $forum_id );
				break;

			case 'psf_forum_created':
				printf( '%1$s <br /> %2$s',
					get_the_date(),
					esc_attr( get_the_time() )
				);

				break;

			case 'psf_forum_freshness' :
				$last_active = psf_get_forum_last_active_time( $forum_id, false );
				if ( !empty( $last_active ) )
					echo esc_html( $last_active );
				else
					esc_html_e( 'Keine Themen', 'psforum' );

				break;

			default:
				do_action( 'psf_admin_forums_column_data', $column, $forum_id );
				break;
		}
	}

	/**
	 * Forum Row actions
	 *
	 * Remove the quick-edit action link and display the description under
	 * the forum title
	 *
	 * @since PSForum (r2577)
	 *
	 * @param array $actions Actions
	 * @param array $forum Forum object
	 * @uses psf_forum_content() To output forum description
	 * @return array $actions Actions
	 */
	public function row_actions( $actions, $forum ) {

		if ( $this->bail() ) return $actions;

		unset( $actions['inline hide-if-no-js'] );

		// simple hack to show the forum description under the title
		psf_forum_content( $forum->ID );

		return $actions;
	}

	/**
	 * Custom user feedback messages for forum post type
	 *
	 * @since PSForum (r3080)
	 *
	 * @global int $post_ID
	 * @uses psf_get_forum_permalink()
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

		// URL for the current forum
		$forum_url = psf_get_forum_permalink( $post_ID );

		// Current forum's post_date
		$post_date = psf_get_global_post_field( 'post_date', 'raw' );

		// Messages array
		$messages[$this->post_type] = array(
			0 =>  '', // Left empty on purpose

			// Updated
			1 =>  sprintf( __( 'Forum aktualisiert. <a href="%s">Forum ansehen</a>', 'psforum' ), $forum_url ),

			// Custom field updated
			2 => __( 'Benutzerdefiniertes Feld aktualisiert.', 'psforum' ),

			// Custom field deleted
			3 => __( 'Benutzerdefiniertes Feld gelöscht.', 'psforum' ),

			// Forum updated
			4 => __( 'Forum aktualisiert.', 'psforum' ),

			// Restored from revision
			// translators: %s: date and time of the revision
			5 => isset( $_GET['revision'] )
					? sprintf( __( 'Forum von Überarbeitung %s wiederhergestellt', 'psforum' ), wp_post_revision_title( (int) $_GET['revision'], false ) )
					: false,

			// Forum created
			6 => sprintf( __( 'Forum erstellt. <a href="%s">Forum ansehen</a>', 'psforum' ), $forum_url ),

			// Forum saved
			7 => __( 'Forum gespeichert.', 'psforum' ),

			// Forum submitted
			8 => sprintf( __( 'Forum eingereicht. <a target="_blank" href="%s">Forum Vorschau</a>', 'psforum' ), esc_url( add_query_arg( 'preview', 'true', $forum_url ) ) ),

			// Forum scheduled
			9 => sprintf( __( 'Forum geplant für: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Forum Vorschau</a>', 'psforum' ),
					// translators: Publish box date format, see http://php.net/date
					date_i18n( __( 'M j, Y @ G:i', 'psforum' ),
					strtotime( $post_date ) ),
					$forum_url ),

			// Forum draft updated
			10 => sprintf( __( 'Forumsentwurf aktualisiert. <a target="_blank" href="%s">Forum Vorschau</a>', 'psforum' ), esc_url( add_query_arg( 'preview', 'true', $forum_url ) ) ),
		);

		return $messages;
	}
}
endif; // class_exists check

/**
 * Setup PSForum Forums Admin
 *
 * This is currently here to make hooking and unhooking of the admin UI easy.
 * It could use dependency injection in the future, but for now this is easier.
 *
 * @since PSForum (r2596)
 *
 * @uses PSF_Forums_Admin
 */
function psf_admin_forums() {
	psforum()->admin->forums = new PSF_Forums_Admin();
}
