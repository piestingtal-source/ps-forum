<?php

/**
 * PSForum Widgets
 *
 * Contains the forum list, topic list, reply list and login form widgets.
 *
 * @package PSForum
 * @subpackage Widgets
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * PSForum Login Widget
 *
 * Adds a widget which displays the login form
 *
 * @since PSForum (r2827)
 *
 * @uses WP_Widget
 */
class PSF_Login_Widget extends WP_Widget {

	/**
	 * PSForum Login Widget
	 *
	 * Registers the login widget
	 *
	 * @since PSForum (r2827)
	 *
	 * @uses apply_filters() Calls 'psf_login_widget_options' with the
	 *                        widget options
	 */
	public function __construct() {
		$widget_ops = apply_filters( 'psf_login_widget_options', array(
			'classname'   => 'psf_widget_login',
			'description' => __( 'Ein einfaches Anmeldeformular mit optionalen Links zu Anmelde- und Passwortseiten.', 'psforum' )
		) );

		parent::__construct( false, __( '(PS Forum) Login Widget', 'psforum' ), $widget_ops );
	}

	/**
	 * Register the widget
	 *
	 * @since PSForum (r3389)
	 *
	 * @uses register_widget()
	 */
	public static function register_widget() {
		register_widget( 'PSF_Login_Widget' );
	}

	/**
	 * Displays the output, the login form
	 *
	 * @since PSForum (r2827)
	 *
	 * @param mixed $args Arguments
	 * @param array $instance Instance
	 * @uses apply_filters() Calls 'psf_login_widget_title' with the title
	 * @uses get_template_part() To get the login/logged in form
	 */
	public function widget( $args = array(), $instance = array() ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance );

		// Typical WordPress filter
		$settings['title'] = apply_filters( 'widget_title', $settings['title'], $instance, $this->id_base );

		// PSForum filters
		$settings['title']    = apply_filters( 'psf_login_widget_title',    $settings['title'],    $instance, $this->id_base );
		$settings['register'] = apply_filters( 'psf_login_widget_register', $settings['register'], $instance, $this->id_base );
		$settings['lostpass'] = apply_filters( 'psf_login_widget_lostpass', $settings['lostpass'], $instance, $this->id_base );

		echo $args['before_widget'];

		if ( !empty( $settings['title'] ) ) {
			echo $args['before_title'] . $settings['title'] . $args['after_title'];
		}

		if ( !is_user_logged_in() ) : ?>

			<form method="post" action="<?php psf_wp_login_action( array( 'context' => 'login_post' ) ); ?>" class="psf-login-form">
				<fieldset>
					<legend><?php _e( 'Einloggen', 'psforum' ); ?></legend>

					<div class="psf-username">
						<label for="user_login"><?php _e( 'Benutzername', 'psforum' ); ?>: </label>
						<input type="text" name="log" value="<?php psf_sanitize_val( 'user_login', 'text' ); ?>" size="20" id="user_login" tabindex="<?php psf_tab_index(); ?>" />
					</div>

					<div class="psf-password">
						<label for="user_pass"><?php _e( 'Passwort', 'psforum' ); ?>: </label>
						<input type="password" name="pwd" value="<?php psf_sanitize_val( 'user_pass', 'password' ); ?>" size="20" id="user_pass" tabindex="<?php psf_tab_index(); ?>" />
					</div>

					<div class="psf-remember-me">
						<input type="checkbox" name="rememberme" value="forever" <?php checked( psf_get_sanitize_val( 'rememberme', 'checkbox' ), true, true ); ?> id="rememberme" tabindex="<?php psf_tab_index(); ?>" />
						<label for="rememberme"><?php _e( 'Erinnere dich an mich', 'psforum' ); ?></label>
					</div>

					<div class="psf-submit-wrapper">

						<?php do_action( 'login_form' ); ?>

						<button type="submit" name="user-submit" id="user-submit" tabindex="<?php psf_tab_index(); ?>" class="button submit user-submit"><?php _e( 'Einloggen', 'psforum' ); ?></button>

						<?php psf_user_login_fields(); ?>

					</div>

					<?php if ( !empty( $settings['register'] ) || !empty( $settings['lostpass'] ) ) : ?>

						<div class="psf-login-links">

							<?php if ( !empty( $settings['register'] ) ) : ?>

								<a href="<?php echo esc_url( $settings['register'] ); ?>" title="<?php esc_attr_e( 'Registrieren', 'psforum' ); ?>" class="psf-register-link"><?php _e( 'RegistrierenRegister', 'psforum' ); ?></a>

							<?php endif; ?>

							<?php if ( !empty( $settings['lostpass'] ) ) : ?>

								<a href="<?php echo esc_url( $settings['lostpass'] ); ?>" title="<?php esc_attr_e( 'Passwort verloren', 'psforum' ); ?>" class="psf-lostpass-link"><?php _e( 'Passwort verloren', 'psforum' ); ?></a>

							<?php endif; ?>

						</div>

					<?php endif; ?>

				</fieldset>
			</form>

		<?php else : ?>

			<div class="psf-logged-in">
				<a href="<?php psf_user_profile_url( psf_get_current_user_id() ); ?>" class="submit user-submit"><?php echo get_avatar( psf_get_current_user_id(), '40' ); ?></a>
				<h4><?php psf_user_profile_link( psf_get_current_user_id() ); ?></h4>

				<?php psf_logout_link(); ?>
			</div>

		<?php endif;

		echo $args['after_widget'];
	}

	/**
	 * Update the login widget options
	 *
	 * @since PSForum (r2827)
	 *
	 * @param array $new_instance The new instance options
	 * @param array $old_instance The old instance options
	 */
	public function update( $new_instance, $old_instance ) {
		$instance             = $old_instance;
		$instance['title']    = strip_tags( $new_instance['title'] );
		$instance['register'] = esc_url_raw( $new_instance['register'] );
		$instance['lostpass'] = esc_url_raw( $new_instance['lostpass'] );

		return $instance;
	}

	/**
	 * Output the login widget options form
	 *
	 * @since PSForum (r2827)
	 *
	 * @param $instance Instance
	 * @uses PSF_Login_Widget::get_field_id() To output the field id
	 * @uses PSF_Login_Widget::get_field_name() To output the field name
	 */
	public function form( $instance = array() ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Titel:', 'psforum' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $settings['title'] ); ?>" /></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'register' ); ?>"><?php _e( 'Registrierung URI:', 'psforum' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'register' ); ?>" name="<?php echo $this->get_field_name( 'register' ); ?>" type="text" value="<?php echo esc_url( $settings['register'] ); ?>" /></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'lostpass' ); ?>"><?php _e( 'Passwort verloren URI:', 'psforum' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'lostpass' ); ?>" name="<?php echo $this->get_field_name( 'lostpass' ); ?>" type="text" value="<?php echo esc_url( $settings['lostpass'] ); ?>" /></label>
		</p>

		<?php
	}

	/**
	 * Merge the widget settings into defaults array.
	 *
	 * @since PSForum (r4802)
	 *
	 * @param $instance Instance
	 * @uses psf_parse_args() To merge widget settings into defaults
	 */
	public function parse_settings( $instance = array() ) {
		return psf_parse_args( $instance, array(
			'title'    => '',
			'register' => '',
			'lostpass' => ''
		), 'login_widget_settings' );
	}
}

/**
 * PSForum Views Widget
 *
 * Adds a widget which displays the view list
 *
 * @since PSForum (r3020)
 *
 * @uses WP_Widget
 */
class PSF_Views_Widget extends WP_Widget {

	/**
	 * PSForum View Widget
	 *
	 * Registers the view widget
	 *
	 * @since PSForum (r3020)
	 *
	 * @uses apply_filters() Calls 'psf_views_widget_options' with the
	 *                        widget options
	 */
	public function __construct() {
		$widget_ops = apply_filters( 'psf_views_widget_options', array(
			'classname'   => 'widget_display_views',
			'description' => __( 'Eine Liste der registrierten optionalen Themenansichten.', 'psforum' )
		) );

		parent::__construct( false, __( '(PS Forum) Themenansichten Liste', 'psforum' ), $widget_ops );
	}

	/**
	 * Register the widget
	 *
	 * @since PSForum (r3389)
	 *
	 * @uses register_widget()
	 */
	public static function register_widget() {
		register_widget( 'PSF_Views_Widget' );
	}

	/**
	 * Displays the output, the view list
	 *
	 * @since PSForum (r3020)
	 *
	 * @param mixed $args Arguments
	 * @param array $instance Instance
	 * @uses apply_filters() Calls 'psf_view_widget_title' with the title
	 * @uses psf_get_views() To get the views
	 * @uses psf_view_url() To output the view url
	 * @uses psf_view_title() To output the view title
	 */
	public function widget( $args = array(), $instance = array() ) {

		// Only output widget contents if views exist
		if ( ! psf_get_views() ) {
			return;
		}

		// Get widget settings
		$settings = $this->parse_settings( $instance );

		// Typical WordPress filter
		$settings['title'] = apply_filters( 'widget_title',          $settings['title'], $instance, $this->id_base );

		// PSForum filter
		$settings['title'] = apply_filters( 'psf_view_widget_title', $settings['title'], $instance, $this->id_base );

		echo $args['before_widget'];

		if ( !empty( $settings['title'] ) ) {
			echo $args['before_title'] . $settings['title'] . $args['after_title'];
		} ?>

		<ul>

			<?php foreach ( array_keys( psf_get_views() ) as $view ) : ?>

				<li><a class="psf-view-title" href="<?php psf_view_url( $view ); ?>"><?php psf_view_title( $view ); ?></a></li>

			<?php endforeach; ?>

		</ul>

		<?php echo $args['after_widget'];
	}

	/**
	 * Update the view widget options
	 *
	 * @since PSForum (r3020)
	 *
	 * @param array $new_instance The new instance options
	 * @param array $old_instance The old instance options
	 */
	public function update( $new_instance = array(), $old_instance = array() ) {
		$instance          = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

	/**
	 * Output the view widget options form
	 *
	 * @since PSForum (r3020)
	 *
	 * @param $instance Instance
	 * @uses PSF_Views_Widget::get_field_id() To output the field id
	 * @uses PSF_Views_Widget::get_field_name() To output the field name
	 */
	public function form( $instance = array() ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Titel:', 'psforum' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $settings['title'] ); ?>" />
			</label>
		</p>

		<?php
	}

	/**
	 * Merge the widget settings into defaults array.
	 *
	 * @since PSForum (r4802)
	 *
	 * @param $instance Instance
	 * @uses psf_parse_args() To merge widget settings into defaults
	 */
	public function parse_settings( $instance = array() ) {
		return psf_parse_args( $instance, array(
			'title' => ''
		), 'view_widget_settings' );
	}
}

/**
 * PSForum Search Widget
 *
 * Adds a widget which displays the forum search form
 *
 * @since PSForum (r4579)
 *
 * @uses WP_Widget
 */
class PSF_Search_Widget extends WP_Widget {

	/**
	 * PSForum Search Widget
	 *
	 * Registers the search widget
	 *
	 * @since PSForum (r4579)
	 *
	 * @uses apply_filters() Calls 'psf_search_widget_options' with the
	 *                        widget options
	 */
	public function __construct() {
		$widget_ops = apply_filters( 'psf_search_widget_options', array(
			'classname'   => 'widget_display_search',
			'description' => __( 'Das PS Forum Suchformular.', 'psforum' )
		) );

		parent::__construct( false, __( '(PS Forum) Forum-Suchformular', 'psforum' ), $widget_ops );
	}

	/**
	 * Register the widget
	 *
	 * @since PSForum (r4579)
	 *
	 * @uses register_widget()
	 */
	public static function register_widget() {
		register_widget( 'PSF_Search_Widget' );
	}

	/**
	 * Displays the output, the search form
	 *
	 * @since PSForum (r4579)
	 *
	 * @uses apply_filters() Calls 'psf_search_widget_title' with the title
	 * @uses get_template_part() To get the search form
	 */
	public function widget( $args, $instance ) {

		// Bail if search is disabled
		if ( ! psf_allow_search() )
			return;

		// Get widget settings
		$settings = $this->parse_settings( $instance );

		// Typical WordPress filter
		$settings['title'] = apply_filters( 'widget_title', $settings['title'], $instance, $this->id_base );

		// PSForum filter
		$settings['title'] = apply_filters( 'psf_search_widget_title', $settings['title'], $instance, $this->id_base );

		echo $args['before_widget'];

		if ( !empty( $settings['title'] ) ) {
			echo $args['before_title'] . $settings['title'] . $args['after_title'];
		}

		psf_get_template_part( 'form', 'search' );

		echo $args['after_widget'];
	}

	/**
	 * Update the widget options
	 *
	 * @since PSForum (r4579)
	 *
	 * @param array $new_instance The new instance options
	 * @param array $old_instance The old instance options
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

	/**
	 * Output the search widget options form
	 *
	 * @since PSForum (r4579)
	 *
	 * @param $instance Instance
	 * @uses PSF_Search_Widget::get_field_id() To output the field id
	 * @uses PSF_Search_Widget::get_field_name() To output the field name
	 */
	public function form( $instance ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Titel:', 'psforum' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $settings['title'] ); ?>" />
			</label>
		</p>

		<?php
	}

	/**
	 * Merge the widget settings into defaults array.
	 *
	 * @since PSForum (r4802)
	 *
	 * @param $instance Instance
	 * @uses psf_parse_args() To merge widget settings into defaults
	 */
	public function parse_settings( $instance = array() ) {
		return psf_parse_args( $instance, array(
			'title' => __( 'Foren durchsuchen', 'psforum' )
		), 'search_widget_settings' );
	}
}

/**
 * PSForum Forum Widget
 *
 * Adds a widget which displays the forum list
 *
 * @since PSForum (r2653)
 *
 * @uses WP_Widget
 */
class PSF_Forums_Widget extends WP_Widget {

	/**
	 * PSForum Forum Widget
	 *
	 * Registers the forum widget
	 *
	 * @since PSForum (r2653)
	 *
	 * @uses apply_filters() Calls 'psf_forums_widget_options' with the
	 *                        widget options
	 */
	public function __construct() {
		$widget_ops = apply_filters( 'psf_forums_widget_options', array(
			'classname'   => 'widget_display_forums',
			'description' => __( 'Eine Liste von Foren mit einer Option zum Festlegen des Elternteils.', 'psforum' )
		) );

		parent::__construct( false, __( '(PS Forum) Forenliste', 'psforum' ), $widget_ops );
	}

	/**
	 * Register the widget
	 *
	 * @since PSForum (r3389)
	 *
	 * @uses register_widget()
	 */
	public static function register_widget() {
		register_widget( 'PSF_Forums_Widget' );
	}

	/**
	 * Displays the output, the forum list
	 *
	 * @since PSForum (r2653)
	 *
	 * @param mixed $args Arguments
	 * @param array $instance Instance
	 * @uses apply_filters() Calls 'psf_forum_widget_title' with the title
	 * @uses get_option() To get the forums per page option
	 * @uses current_user_can() To check if the current user can read
	 *                           private() To resety name
	 * @uses psf_has_forums() The main forum loop
	 * @uses psf_forums() To check whether there are more forums available
	 *                     in the loop
	 * @uses psf_the_forum() Loads up the current forum in the loop
	 * @uses psf_forum_permalink() To display the forum permalink
	 * @uses psf_forum_title() To display the forum title
	 */
	public function widget( $args, $instance ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance );

		// Typical WordPress filter
		$settings['title'] = apply_filters( 'widget_title',           $settings['title'], $instance, $this->id_base );

		// PSForum filter
		$settings['title'] = apply_filters( 'psf_forum_widget_title', $settings['title'], $instance, $this->id_base );

		// Note: private and hidden forums will be excluded via the
		// psf_pre_get_posts_normalize_forum_visibility action and function.
		$widget_query = new WP_Query( array(
			'post_type'           => psf_get_forum_post_type(),
			'post_parent'         => $settings['parent_forum'],
			'post_status'         => psf_get_public_status_id(),
			'posts_per_page'      => get_option( '_psf_forums_per_page', 50 ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'orderby'             => 'menu_order title',
			'order'               => 'ASC'
		) );

		// Bail if no posts
		if ( ! $widget_query->have_posts() ) {
			return;
		}

		echo $args['before_widget'];

		if ( !empty( $settings['title'] ) ) {
			echo $args['before_title'] . $settings['title'] . $args['after_title'];
		} ?>

		<ul>

			<?php while ( $widget_query->have_posts() ) : $widget_query->the_post(); ?>

				<li><a class="psf-forum-title" href="<?php psf_forum_permalink( $widget_query->post->ID ); ?>"><?php psf_forum_title( $widget_query->post->ID ); ?></a></li>

			<?php endwhile; ?>

		</ul>

		<?php echo $args['after_widget'];

		// Reset the $post global
		wp_reset_postdata();
	}

	/**
	 * Update the forum widget options
	 *
	 * @since PSForum (r2653)
	 *
	 * @param array $new_instance The new instance options
	 * @param array $old_instance The old instance options
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                 = $old_instance;
		$instance['title']        = strip_tags( $new_instance['title'] );
		$instance['parent_forum'] = sanitize_text_field( $new_instance['parent_forum'] );

		// Force to any
		if ( !empty( $instance['parent_forum'] ) && !is_numeric( $instance['parent_forum'] ) ) {
			$instance['parent_forum'] = 'any';
		}

		return $instance;
	}

	/**
	 * Output the forum widget options form
	 *
	 * @since PSForum (r2653)
	 *
	 * @param $instance Instance
	 * @uses PSF_Forums_Widget::get_field_id() To output the field id
	 * @uses PSF_Forums_Widget::get_field_name() To output the field name
	 */
	public function form( $instance ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Titel:', 'psforum' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $settings['title'] ); ?>" />
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'parent_forum' ); ?>"><?php _e( 'Elternforum-ID:', 'psforum' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'parent_forum' ); ?>" name="<?php echo $this->get_field_name( 'parent_forum' ); ?>" type="text" value="<?php echo esc_attr( $settings['parent_forum'] ); ?>" />
			</label>

			<br />

			<small><?php _e( '"0", um nur Root anzuzeigen - "any", um alle anzuzeigen', 'psforum' ); ?></small>
		</p>

		<?php
	}

	/**
	 * Merge the widget settings into defaults array.
	 *
	 * @since PSForum (r4802)
	 *
	 * @param $instance Instance
	 * @uses psf_parse_args() To merge widget settings into defaults
	 */
	public function parse_settings( $instance = array() ) {
		return psf_parse_args( $instance, array(
			'title'        => __( 'Foren', 'psforum' ),
			'parent_forum' => 0
		), 'forum_widget_settings' );
	}
}

/**
 * PSForum Topic Widget
 *
 * Adds a widget which displays the topic list
 *
 * @since PSForum (r2653)
 *
 * @uses WP_Widget
 */
class PSF_Topics_Widget extends WP_Widget {

	/**
	 * PSForum Topic Widget
	 *
	 * Registers the topic widget
	 *
	 * @since PSForum (r2653)
	 *
	 * @uses apply_filters() Calls 'psf_topics_widget_options' with the
	 *                        widget options
	 */
	public function __construct() {
		$widget_ops = apply_filters( 'psf_topics_widget_options', array(
			'classname'   => 'widget_display_topics',
			'description' => __( 'Eine Liste aktueller Themen, sortiert nach Beliebtheit oder Aktualität.', 'psforum' )
		) );

		parent::__construct( false, __( '(PS Forum) Aktuelle Themen', 'psforum' ), $widget_ops );
	}

	/**
	 * Register the widget
	 *
	 * @since PSForum (r3389)
	 *
	 * @uses register_widget()
	 */
	public static function register_widget() {
		register_widget( 'PSF_Topics_Widget' );
	}

	/**
	 * Displays the output, the topic list
	 *
	 * @since PSForum (r2653)
	 *
	 * @param mixed $args
	 * @param array $instance
	 * @uses apply_filters() Calls 'psf_topic_widget_title' with the title
	 * @uses psf_topic_permalink() To display the topic permalink
	 * @uses psf_topic_title() To display the topic title
	 * @uses psf_get_topic_last_active_time() To get the topic last active
	 *                                         time
	 * @uses psf_get_topic_id() To get the topic id
	 */
	public function widget( $args = array(), $instance = array() ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance );

		// Typical WordPress filter
		$settings['title'] = apply_filters( 'widget_title',           $settings['title'], $instance, $this->id_base );

		// PSForum filter
		$settings['title'] = apply_filters( 'psf_topic_widget_title', $settings['title'], $instance, $this->id_base );

		// How do we want to order our results?
		switch ( $settings['order_by'] ) {

			// Order by most recent replies
			case 'freshness' :
				$topics_query = array(
					'post_type'           => psf_get_topic_post_type(),
					'post_parent'         => $settings['parent_forum'],
					'posts_per_page'      => (int) $settings['max_shown'],
					'post_status'         => array( psf_get_public_status_id(), psf_get_closed_status_id() ),
					'ignore_sticky_posts' => true,
					'no_found_rows'       => true,
					'meta_key'            => '_psf_last_active_time',
					'orderby'             => 'meta_value',
					'order'               => 'DESC',
				);
				break;

			// Order by total number of replies
			case 'popular' :
				$topics_query = array(
					'post_type'           => psf_get_topic_post_type(),
					'post_parent'         => $settings['parent_forum'],
					'posts_per_page'      => (int) $settings['max_shown'],
					'post_status'         => array( psf_get_public_status_id(), psf_get_closed_status_id() ),
					'ignore_sticky_posts' => true,
					'no_found_rows'       => true,
					'meta_key'            => '_psf_reply_count',
					'orderby'             => 'meta_value',
					'order'               => 'DESC'
				);
				break;

			// Order by which topic was created most recently
			case 'newness' :
			default :
				$topics_query = array(
					'post_type'           => psf_get_topic_post_type(),
					'post_parent'         => $settings['parent_forum'],
					'posts_per_page'      => (int) $settings['max_shown'],
					'post_status'         => array( psf_get_public_status_id(), psf_get_closed_status_id() ),
					'ignore_sticky_posts' => true,
					'no_found_rows'       => true,
					'order'               => 'DESC'
				);
				break;
		}

		// Note: private and hidden forums will be excluded via the
		// psf_pre_get_posts_normalize_forum_visibility action and function.
		$widget_query = new WP_Query( $topics_query );

		// Bail if no topics are found
		if ( ! $widget_query->have_posts() ) {
			return;
		}

		echo $args['before_widget'];

		if ( !empty( $settings['title'] ) ) {
			echo $args['before_title'] . $settings['title'] . $args['after_title'];
		} ?>

		<ul>

			<?php while ( $widget_query->have_posts() ) :

				$widget_query->the_post();
				$topic_id    = psf_get_topic_id( $widget_query->post->ID );
				$author_link = '';

				// Maybe get the topic author
				if ( ! empty( $settings['show_user'] ) ) :
					$author_link = psf_get_topic_author_link( array( 'post_id' => $topic_id, 'type' => 'both', 'size' => 14 ) );
				endif; ?>

				<li>
					<a class="psf-forum-title" href="<?php psf_topic_permalink( $topic_id ); ?>"><?php psf_topic_title( $topic_id ); ?></a>

					<?php if ( ! empty( $author_link ) ) : ?>

						<?php printf( _x( 'von %1$s', 'widgets', 'psforum' ), '<span class="topic-author">' . $author_link . '</span>' ); ?>

					<?php endif; ?>

					<?php if ( ! empty( $settings['show_date'] ) ) : ?>

						<div><?php psf_topic_last_active_time( $topic_id ); ?></div>

					<?php endif; ?>

				</li>

			<?php endwhile; ?>

		</ul>

		<?php echo $args['after_widget'];

		// Reset the $post global
		wp_reset_postdata();
	}

	/**
	 * Update the topic widget options
	 *
	 * @since PSForum (r2653)
	 *
	 * @param array $new_instance The new instance options
	 * @param array $old_instance The old instance options
	 */
	public function update( $new_instance = array(), $old_instance = array() ) {
		$instance                 = $old_instance;
		$instance['title']        = strip_tags( $new_instance['title'] );
		$instance['order_by']     = strip_tags( $new_instance['order_by'] );
		$instance['parent_forum'] = sanitize_text_field( $new_instance['parent_forum'] );
		$instance['max_shown']    = (int) $new_instance['max_shown'];

		// Date
		$instance['show_date'] = isset( $new_instance['show_date'] )
			? (bool) $new_instance['show_date']
			: false;

		// Author
		$instance['show_user'] = isset( $new_instance['show_user'] )
			? (bool) $new_instance['show_user']
			: false;

		// Force to any
		if ( !empty( $instance['parent_forum'] ) && !is_numeric( $instance['parent_forum'] ) ) {
			$instance['parent_forum'] = 'any';
		}

		return $instance;
	}

	/**
	 * Output the topic widget options form
	 *
	 * @since PSForum (r2653)
	 *
	 * @param $instance Instance
	 * @uses PSF_Topics_Widget::get_field_id() To output the field id
	 * @uses PSF_Topics_Widget::get_field_name() To output the field name
	 */
	public function form( $instance = array() ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance ); ?>

		<p><label for="<?php echo $this->get_field_id( 'title'     ); ?>"><?php _e( 'Titel:', 'psforum' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title'     ); ?>" name="<?php echo $this->get_field_name( 'title'     ); ?>" type="text" value="<?php echo esc_attr( $settings['title']     ); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'max_shown' ); ?>"><?php _e( 'Maximale Anzahl anzuzeigender Themen:', 'psforum' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_shown' ); ?>" name="<?php echo $this->get_field_name( 'max_shown' ); ?>" type="text" value="<?php echo esc_attr( $settings['max_shown'] ); ?>" /></label></p>

		<p>
			<label for="<?php echo $this->get_field_id( 'parent_forum' ); ?>"><?php _e( 'ID des übergeordneten Forums:', 'psforum' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'parent_forum' ); ?>" name="<?php echo $this->get_field_name( 'parent_forum' ); ?>" type="text" value="<?php echo esc_attr( $settings['parent_forum'] ); ?>" />
			</label>

			<br />

			<small><?php _e( '"0", um nur Root anzuzeigen - "any", um alle anzuzeigen', 'psforum' ); ?></small>
		</p>

		<p><label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Postdatum anzeigen:', 'psforum' ); ?> <input type="checkbox" id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" <?php checked( true, $settings['show_date'] ); ?> value="1" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'show_user' ); ?>"><?php _e( 'Thema Autor anzeigen:', 'psforum' ); ?> <input type="checkbox" id="<?php echo $this->get_field_id( 'show_user' ); ?>" name="<?php echo $this->get_field_name( 'show_user' ); ?>" <?php checked( true, $settings['show_user'] ); ?> value="1" /></label></p>

		<p>
			<label for="<?php echo $this->get_field_id( 'order_by' ); ?>"><?php _e( 'Sortieren nach:',        'psforum' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'order_by' ); ?>" id="<?php echo $this->get_field_name( 'order_by' ); ?>">
				<option <?php selected( $settings['order_by'], 'newness' );   ?> value="newness"><?php _e( 'Neueste Themen', 'psforum' ); ?></option>
				<option <?php selected( $settings['order_by'], 'popular' );   ?> value="popular"><?php _e( 'Beliebte Themen', 'psforum' ); ?></option>
				<option <?php selected( $settings['order_by'], 'freshness' ); ?> value="freshness"><?php _e( 'Themen mit aktuellen Antworten', 'psforum' ); ?></option>
			</select>
		</p>

		<?php
	}

	/**
	 * Merge the widget settings into defaults array.
	 *
	 * @since PSForum (r4802)
	 *
	 * @param $instance Instance
	 * @uses psf_parse_args() To merge widget options into defaults
	 */
	public function parse_settings( $instance = array() ) {
		return psf_parse_args( $instance, array(
			'title'        => __( 'Aktuelle Themen', 'psforum' ),
			'max_shown'    => 5,
			'show_date'    => false,
			'show_user'    => false,
			'parent_forum' => 'any',
			'order_by'     => false
		), 'topic_widget_settings' );
	}
}

/**
 * PSForum Stats Widget
 *
 * Adds a widget which displays the forum statistics
 *
 * @since PSForum (r4509)
 *
 * @uses WP_Widget
 */
class PSF_Stats_Widget extends WP_Widget {

	/**
	 * PSForum Stats Widget
	 *
	 * Registers the stats widget
	 *
	 * @since PSForum (r4509)
	 *
	 * @uses  apply_filters() Calls 'psf_stats_widget_options' with the
	 *        widget options
	 */
	public function __construct() {
		$widget_ops = apply_filters( 'psf_stats_widget_options', array(
			'classname'   => 'widget_display_stats',
			'description' => __( 'Einige Statistiken aus deinem Forum.', 'psforum' )
		) );

		parent::__construct( false, __( '(PSForum) Statistiken', 'psforum' ), $widget_ops );
	}

	/**
	 * Register the widget
	 *
	 * @since PSForum (r4509)
	 *
	 * @uses register_widget()
	 */
	public static function register_widget() {
		register_widget( 'PSF_Stats_Widget' );
	}

	/**
	 * Displays the output, the statistics
	 *
	 * @since PSForum (r4509)
	 *
	 * @param mixed $args     Arguments
	 * @param array $instance Instance
	 *
	 * @uses apply_filters() Calls 'psf_stats_widget_title' with the title
	 * @uses psf_get_template_part() To get the content-forum-statistics template
	 */
	public function widget( $args = array(), $instance = array() ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance );

		// Typical WordPress filter
		$settings['title'] = apply_filters( 'widget_title',           $settings['title'], $instance, $this->id_base );

		// PSForum widget title filter
		$settings['title'] = apply_filters( 'psf_stats_widget_title', $settings['title'], $instance, $this->id_base );

		echo $args['before_widget'];

		if ( !empty( $settings['title'] ) ) {
			echo $args['before_title'] . $settings['title'] . $args['after_title'];
		}

		psf_get_template_part( 'content', 'statistics' );

		echo $args['after_widget'];
	}

	/**
	 * Update the stats widget options
	 *
	 * @since PSForum (r4509)
	 *
	 * @param array $new_instance The new instance options
	 * @param array $old_instance The old instance options
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

	/**
	 * Output the stats widget options form
	 *
	 * @since PSForum (r4509)
	 *
	 * @param $instance
	 *
	 * @return string|void
	 */
	public function form( $instance ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Titel:', 'psforum' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $settings['title'] ); ?>"/>
			</label>
		</p>

	<?php
	}

	/**
	 * Merge the widget settings into defaults array.
	 *
	 * @since PSForum (r4802)
	 *
	 * @param $instance Instance
	 * @uses psf_parse_args() To merge widget settings into defaults
	 */
	public function parse_settings( $instance = array() ) {
		return psf_parse_args( $instance, array(
			'title' => __( 'Forum Statistiken', 'psforum' )
		),
		'stats_widget_settings' );
	}
}

/**
 * PSForum Replies Widget
 *
 * Adds a widget which displays the replies list
 *
 * @since PSForum (r2653)
 *
 * @uses WP_Widget
 */
class PSF_Replies_Widget extends WP_Widget {

	/**
	 * PSForum Replies Widget
	 *
	 * Registers the replies widget
	 *
	 * @since PSForum (r2653)
	 *
	 * @uses apply_filters() Calls 'psf_replies_widget_options' with the
	 *                        widget options
	 */
	public function __construct() {
		$widget_ops = apply_filters( 'psf_replies_widget_options', array(
			'classname'   => 'widget_display_replies',
			'description' => __( 'Eine Liste der letzten Antworten.', 'psforum' )
		) );

		parent::__construct( false, __( '(PS Forum) Aktuelle Antworten', 'psforum' ), $widget_ops );
	}

	/**
	 * Register the widget
	 *
	 * @since PSForum (r3389)
	 *
	 * @uses register_widget()
	 */
	public static function register_widget() {
		register_widget( 'PSF_Replies_Widget' );
	}

	/**
	 * Displays the output, the replies list
	 *
	 * @since PSForum (r2653)
	 *
	 * @param mixed $args
	 * @param array $instance
	 * @uses apply_filters() Calls 'psf_reply_widget_title' with the title
	 * @uses psf_get_reply_author_link() To get the reply author link
	 * @uses psf_get_reply_id() To get the reply id
	 * @uses psf_get_reply_url() To get the reply url
	 * @uses psf_get_reply_excerpt() To get the reply excerpt
	 * @uses psf_get_reply_topic_title() To get the reply topic title
	 * @uses get_the_date() To get the date of the reply
	 * @uses get_the_time() To get the time of the reply
	 */
	public function widget( $args, $instance ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance );

		// Typical WordPress filter
		$settings['title'] = apply_filters( 'widget_title',             $settings['title'], $instance, $this->id_base );

		// PSForum filter
		$settings['title'] = apply_filters( 'psf_replies_widget_title', $settings['title'], $instance, $this->id_base );

		// Note: private and hidden forums will be excluded via the
		// psf_pre_get_posts_normalize_forum_visibility action and function.
		$widget_query = new WP_Query( array(
			'post_type'           => psf_get_reply_post_type(),
			'post_status'         => array( psf_get_public_status_id(), psf_get_closed_status_id() ),
			'posts_per_page'      => (int) $settings['max_shown'],
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		) );

		// Bail if no replies
		if ( ! $widget_query->have_posts() ) {
			return;
		}

		echo $args['before_widget'];

		if ( !empty( $settings['title'] ) ) {
			echo $args['before_title'] . $settings['title'] . $args['after_title'];
		} ?>

		<ul>

			<?php while ( $widget_query->have_posts() ) : $widget_query->the_post(); ?>

				<li>

					<?php

					// Verify the reply ID
					$reply_id   = psf_get_reply_id( $widget_query->post->ID );
					$reply_link = '<a class="psf-reply-topic-title" href="' . esc_url( psf_get_reply_url( $reply_id ) ) . '" title="' . esc_attr( psf_get_reply_excerpt( $reply_id, 50 ) ) . '">' . psf_get_reply_topic_title( $reply_id ) . '</a>';

					// Only query user if showing them
					if ( ! empty( $settings['show_user'] ) ) :
						$author_link = psf_get_reply_author_link( array( 'post_id' => $reply_id, 'type' => 'both', 'size' => 14 ) );
					else :
						$author_link = false;
					endif;

					// Reply author, link, and timestamp
					if ( ! empty( $settings['show_date'] ) && !empty( $author_link ) ) :

						// translators: 1: reply author, 2: reply link, 3: reply timestamp
						printf( _x( '%1$s in %2$s %3$s', 'widgets', 'psforum' ), $author_link, $reply_link, '<div>' . psf_get_time_since( get_the_time( 'U' ) ) . '</div>' );

					// Reply link and timestamp
					elseif ( ! empty( $settings['show_date'] ) ) :

						// translators: 1: reply link, 2: reply timestamp
						printf( _x( '%1$s %2$s', 'widgets', 'psforum' ), $reply_link,  '<div>' . psf_get_time_since( get_the_time( 'U' ) ) . '</div>'              );

					// Reply author and title
					elseif ( !empty( $author_link ) ) :

						// translators: 1: reply author, 2: reply link
						printf( _x( '%1$s in %2$s', 'widgets', 'psforum' ), $author_link, $reply_link                                                                 );

					// Only the reply title
					else :

						// translators: 1: reply link
						printf( _x( '%1$s', 'widgets', 'psforum' ), $reply_link                                                                               );

					endif;

					?>

				</li>

			<?php endwhile; ?>

		</ul>

		<?php echo $args['after_widget'];

		// Reset the $post global
		wp_reset_postdata();
	}

	/**
	 * Update the reply widget options
	 *
	 * @since PSForum (r2653)
	 *
	 * @param array $new_instance The new instance options
	 * @param array $old_instance The old instance options
	 */
	public function update( $new_instance = array(), $old_instance = array() ) {
		$instance              = $old_instance;
		$instance['title']     = strip_tags( $new_instance['title'] );
		$instance['max_shown'] = (int) $new_instance['max_shown'];

		// Date
		$instance['show_date'] = isset( $new_instance['show_date'] )
			? (bool) $new_instance['show_date']
			: false;

		// Author
		$instance['show_user'] = isset( $new_instance['show_user'] )
			? (bool) $new_instance['show_user']
			: false;

		return $instance;
	}

	/**
	 * Output the reply widget options form
	 *
	 * @since PSForum (r2653)
	 *
	 * @param $instance Instance
	 * @uses PSF_Replies_Widget::get_field_id() To output the field id
	 * @uses PSF_Replies_Widget::get_field_name() To output the field name
	 */
	public function form( $instance = array() ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance ); ?>

		<p><label for="<?php echo $this->get_field_id( 'title'     ); ?>"><?php _e( 'Titel:', 'psforum' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title'     ); ?>" name="<?php echo $this->get_field_name( 'title'     ); ?>" type="text" value="<?php echo esc_attr( $settings['title']     ); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'max_shown' ); ?>"><?php _e( 'Maximale Antworten zum Anzeigen:', 'psforum' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_shown' ); ?>" name="<?php echo $this->get_field_name( 'max_shown' ); ?>" type="text" value="<?php echo esc_attr( $settings['max_shown'] ); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Beitragsdatum anzeigen:', 'psforum' ); ?> <input type="checkbox" id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" <?php checked( true, $settings['show_date'] ); ?> value="1" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'show_user' ); ?>"><?php _e( 'Antwortautor anzeigen:', 'psforum' ); ?> <input type="checkbox" id="<?php echo $this->get_field_id( 'show_user' ); ?>" name="<?php echo $this->get_field_name( 'show_user' ); ?>" <?php checked( true, $settings['show_user'] ); ?> value="1" /></label></p>

		<?php
	}

	/**
	 * Merge the widget settings into defaults array.
	 *
	 * @since PSForum (r4802)
	 *
	 * @param $instance Instance
	 * @uses psf_parse_args() To merge widget settings into defaults
	 */
	public function parse_settings( $instance = array() ) {
		return psf_parse_args( $instance, array(
			'title'     => __( 'Aktuelle Antworten', 'psforum' ),
			'max_shown' => 5,
			'show_date' => false,
			'show_user' => false
		),
		'replies_widget_settings' );
	}
}
