<?php

/**
 * PSForum Classes
 *
 * @package PSForum
 * @subpackage Classes
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'PSF_Component' ) ) :
/**
 * PSForum Component Class
 *
 * The PSForum component class is responsible for simplifying the creation
 * of components that share similar behaviors and routines. It is used
 * internally by PSForum to create forums, topics and replies, but can be
 * extended to create other really neat things.
 *
 * @package PSForum
 * @subpackage Classes
 *
 * @since PSForum (r2688)
 */
class PSF_Component {

	/**
	 * @var string Unique name (for internal identification)
	 * @internal
	 */
	var $name;

	/**
	 * @var Unique ID (normally for custom post type)
	 */
	var $id;

	/**
	 * @var string Unique slug (used in query string and permalinks)
	 */
	var $slug;

	/**
	 * @var WP_Query The loop for this component
	 */
	var $query;

	/**
	 * @var string The current ID of the queried object
	 */
	var $current_id;


	/** Methods ***************************************************************/

	/**
	 * PSForum Component loader
	 *
	 * @since PSForum (r2700)
	 *
	 * @param mixed $args Required. Supports these args:
	 *  - name: Unique name (for internal identification)
	 *  - id: Unique ID (normally for custom post type)
	 *  - slug: Unique slug (used in query string and permalinks)
	 *  - query: The loop for this component (WP_Query)
	 *  - current_id: The current ID of the queried object
	 * @uses PSF_Component::setup_globals() Setup the globals needed
	 * @uses PSF_Component::includes() Include the required files
	 * @uses PSF_Component::setup_actions() Setup the hooks and actions
	 */
	public function __construct( $args = '' ) {
		if ( empty( $args ) )
			return;

		$this->setup_globals( $args );
		$this->includes();
		$this->setup_actions();
	}

	/**
	 * Component global variables
	 *
	 * @since PSForum (r2700)
	 * @access private
	 *
	 * @uses apply_filters() Calls 'psf_{@link PSF_Component::name}_id'
	 * @uses apply_filters() Calls 'psf_{@link PSF_Component::name}_slug'
	 */
	private function setup_globals( $args = '' ) {
		$this->name = $args['name'];
		$this->id   = apply_filters( 'psf_' . $this->name . '_id',   $args['id']   );
		$this->slug = apply_filters( 'psf_' . $this->name . '_slug', $args['slug'] );
	}

	/**
	 * Include required files
	 *
	 * @since PSForum (r2700)
	 * @access private
	 *
	 * @uses do_action() Calls 'psf_{@link PSF_Component::name}includes'
	 */
	private function includes() {
		do_action( 'psf_' . $this->name . 'includes' );
	}

	/**
	 * Setup the actions
	 *
	 * @since PSForum (r2700)
	 * @access private
	 *
	 * @uses add_action() To add various actions
	 * @uses do_action() Calls
	 *                    'psf_{@link PSF_Component::name}setup_actions'
	 */
	private function setup_actions() {
		add_action( 'psf_register_post_types',    array( $this, 'register_post_types'    ), 10, 2 ); // Register post types
		add_action( 'psf_register_taxonomies',    array( $this, 'register_taxonomies'    ), 10, 2 ); // Register taxonomies
		add_action( 'psf_add_rewrite_tags',       array( $this, 'add_rewrite_tags'       ), 10, 2 ); // Add the rewrite tags
		add_action( 'psf_generate_rewrite_rules', array( $this, 'generate_rewrite_rules' ), 10, 2 ); // Generate rewrite rules

		// Additional actions can be attached here
		do_action( 'psf_' . $this->name . 'setup_actions' );
	}

	/**
	 * Setup the component post types
	 *
	 * @since PSForum (r2700)
	 *
	 * @uses do_action() Calls 'psf_{@link PSF_Component::name}_register_post_types'
	 */
	public function register_post_types() {
		do_action( 'psf_' . $this->name . '_register_post_types' );
	}

	/**
	 * Register component specific taxonomies
	 *
	 * @since PSForum (r2700)
	 *
	 * @uses do_action() Calls 'psf_{@link PSF_Component::name}_register_taxonomies'
	 */
	public function register_taxonomies() {
		do_action( 'psf_' . $this->name . '_register_taxonomies' );
	}

	/**
	 * Add any additional rewrite tags
	 *
	 * @since PSForum (r2700)
	 *
	 * @uses do_action() Calls 'psf_{@link PSF_Component::name}_add_rewrite_tags'
	 */
	public function add_rewrite_tags() {
		do_action( 'psf_' . $this->name . '_add_rewrite_tags' );
	}

	/**
	 * Generate any additional rewrite rules
	 *
	 * @since PSForum (r2700)
	 *
	 * @uses do_action() Calls 'psf_{@link PSF_Component::name}_generate_rewrite_rules'
	 */
	public function generate_rewrite_rules( $wp_rewrite ) {
		do_action_ref_array( 'psf_' . $this->name . '_generate_rewrite_rules', $wp_rewrite );
	}
}
endif; // PSF_Component

if ( class_exists( 'Walker' ) ) :
/**
 * Create HTML dropdown list of PSForum forums/topics.
 *
 * @package PSForum
 * @subpackage Classes
 *
 * @since PSForum (r2746)
 * @uses Walker
 */
class PSF_Walker_Dropdown extends Walker {

	/**
	 * @see Walker::$tree_type
	 *
	 * @since PSForum (r2746)
	 *
	 * @var string
	 */
	var $tree_type;

	/**
	 * @see Walker::$db_fields
	 *
	 * @since PSForum (r2746)
	 *
	 * @var array
	 */
	var $db_fields = array( 'parent' => 'post_parent', 'id' => 'ID' );

	/** Methods ***************************************************************/

	/**
	 * Set the tree_type
	 *
	 * @since PSForum (r2746)
	 */
	public function __construct() {
		$this->tree_type = psf_get_forum_post_type();
	}

	/**
	 * @see Walker::start_el()
	 *
	 * @since PSForum (r2746)
	 *
	 * @param string $output Passed by reference. Used to append additional
	 *                        content.
	 * @param object $_post Post data object.
	 * @param int $depth Depth of post in reference to parent posts. Used
	 *                    for padding.
	 * @param array $args Uses 'selected' argument for selected post to set
	 *                     selected HTML attribute for option element.
	 * @param int $current_object_id
	 * @uses psf_is_forum_category() To check if the forum is a category
	 * @uses current_user_can() To check if the current user can post in
	 *                           closed forums
	 * @uses psf_is_forum_closed() To check if the forum is closed
	 * @uses apply_filters() Calls 'psf_walker_dropdown_post_title' with the
	 *                        title, output, post, depth and args
	 */
	public function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) {
		$pad     = str_repeat( '&nbsp;', (int) $depth * 3 );
		$output .= '<option class="level-' . (int) $depth . '"';

		// Disable the <option> if:
		// - we're told to do so
		// - the post type is a forum
		// - the forum is a category
		// - forum is closed
		if (	( true === $args['disable_categories'] )
				&& ( psf_get_forum_post_type() === $object->post_type )
				&& ( psf_is_forum_category( $object->ID )
					|| ( !current_user_can( 'edit_forum', $object->ID ) && psf_is_forum_closed( $object->ID )
				)
			) ) {
			$output .= ' disabled="disabled" value=""';
		} else {
			$output .= ' value="' . (int) $object->ID .'"' . selected( $args['selected'], $object->ID, false );
		}

		$output .= '>';
		$title   = apply_filters( 'psf_walker_dropdown_post_title', $object->post_title, $output, $object, $depth, $args );
		$output .= $pad . esc_html( $title );
		$output .= "</option>\n";
	}
}

/**
 * Create hierarchical list of PSForum replies.
 *
 * @package PSForum
 * @subpackage Classes
 *
 * @since PSForum (r4944)
 */
class PSF_Walker_Reply extends Walker {

	/**
	 * @see Walker::$tree_type
	 *
	 * @since PSForum (r4944)
	 *
	 * @var string
	 */
	var $tree_type = 'reply';

	/**
	 * @see Walker::$db_fields
	 *
	 * @since PSForum (r4944)
	 *
	 * @var array
	 */
	var $db_fields = array(
		'parent' => 'reply_to',
		'id'     => 'ID'
	);

	/**
	 * @see Walker::start_lvl()
	 *
	 * @since PSForum (r4944)
	 *
	 * @param string $output Passed by reference. Used to append additional content
	 * @param int $depth Depth of reply
	 * @param array $args Uses 'style' argument for type of HTML list
	 */
	public function start_lvl( &$output = '', $depth = 0, $args = array() ) {
		psforum()->reply_query->reply_depth = $depth + 1;

		switch ( $args['style'] ) {
			case 'div':
				break;
			case 'ol':
				echo "<ol class='psf-threaded-replies'>\n";
				break;
			case 'ul':
			default:
				echo "<ul class='psf-threaded-replies'>\n";
				break;
		}
	}

	/**
	 * @see Walker::end_lvl()
	 *
	 * @since PSForum (r4944)
	 *
	 * @param string $output Passed by reference. Used to append additional content
	 * @param int $depth Depth of reply
	 * @param array $args Will only append content if style argument value is 'ol' or 'ul'
	 */
	public function end_lvl( &$output = '', $depth = 0, $args = array() ) {
		psforum()->reply_query->reply_depth = (int) $depth + 1;

		switch ( $args['style'] ) {
			case 'div':
				break;
			case 'ol':
				echo "</ol>\n";
				break;
			case 'ul':
			default:
				echo "</ul>\n";
				break;
		}
	}

	/**
	 * @since PSForum (r4944)
	 */
	public function display_element( $element = false, &$children_elements = array(), $max_depth = 0, $depth = 0, $args = array(), &$output = '' ) {

		if ( empty( $element ) )
			return;

		// Get element's id
		$id_field = $this->db_fields['id'];
		$id       = $element->$id_field;

		// Display element
		parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );

		// If we're at the max depth and the current element still has children, loop over those
		// and display them at this level to prevent them being orphaned to the end of the list.
		if ( ( $max_depth <= (int) $depth + 1 ) && isset( $children_elements[$id] ) ) {
			foreach ( $children_elements[$id] as $child ) {
				$this->display_element( $child, $children_elements, $max_depth, $depth, $args, $output );
			}
			unset( $children_elements[$id] );
		}
	}

	/**
	 * @see Walker:start_el()
	 *
	 * @since PSForum (r4944)
	 */
	public function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) {

		// Set up reply
		$depth++;
		psforum()->reply_query->reply_depth = $depth;
		psforum()->reply_query->post        = $object;
		psforum()->current_reply_id         = $object->ID;

		// Check for a callback and use it if specified
		if ( !empty( $args['callback'] ) ) {
			call_user_func( $args['callback'], $object, $args, $depth );
			return;
		}

		// Style for div or list element
		if ( !empty( $args['style'] ) && ( 'div' === $args['style'] ) ) {
			echo "<div>\n";
		} else {
			echo "<li>\n";
		}

		psf_get_template_part( 'loop', 'single-reply' );
	}

	/**
	 * @since PSForum (r4944)
	 */
	public function end_el( &$output = '', $object = false, $depth = 0, $args = array() ) {

		// Check for a callback and use it if specified
		if ( !empty( $args['end-callback'] ) ) {
			call_user_func( $args['end-callback'], $object, $args, $depth );
			return;
		}

		// Style for div or list element
		if ( !empty( $args['style'] ) && ( 'div' === $args['style'] ) ) {
			echo "</div>\n";
		} else {
			echo "</li>\n";
		}
	}
}
endif; // class_exists check
