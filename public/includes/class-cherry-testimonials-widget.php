<?php
/**
 * Cherry Testimonials
 *
 * @package   Cherry_Testimonials
 * @author    Cherry Team
 * @license   GPL-2.0+
 * @link      http://www.cherryframework.com/
 * @copyright 2014 Cherry Team
 */

/**
 * Class for Testimonials widget.
 *
 * @since 1.0.0
 */
class Cherry_Testimonials_Widget extends WP_Widget {

	/**
	 * Unique identifier for widget.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected $widget_slug = 'cherry_testimonials_widget';

	/**
	 * Instance of Cherry_Testimonials_Data class.
	 *
	 * @since 1.0.0
	 * @var   object
	 */
	private $data;

	/*--------------------------------------------------*/
	/* Constructor
	/*--------------------------------------------------*/
	public function __construct() {

		parent::__construct(
			$this->get_widget_slug(),
			__( 'Cherry Testimonials', 'cherry-testimonials' ),
			array(
				'classname'   => $this->get_widget_slug(),
				'description' => __( "Your site's most recent Testimonials.", 'cherry-testimonials' )
			)
		);

		$this->data = new Cherry_Testimonials_Data;

		// Refreshing the widget's cached output with each new post.
		add_action( 'save_post',    array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );

		// Changed item template.
		// add_filter( 'cherry_testimonials_item_template', array( $this, 'item_template' ), 9, 2 );
	}

	/**
	 * Call method overloading.
	 *
	 * @since  1.0.0
	 * @param  string $method Name of the method being called.
	 * @param  array  $args   Array containing the parameters passed to the $name'ed method.
	 * @return void
	 */
	public function __call( $method, $args ) {
		return $this->data->$method( $args[0] );
	}

	/**
	 * Return the widget slug.
	 *
	 * @since  1.0.0
	 * @return Plugin slug variable.
	 */
	public function get_widget_slug() {
		return $this->widget_slug;
	}

	/*--------------------------------------------------*/
	/* Widget API Functions
	/*--------------------------------------------------*/

	/**
	 * Outputs the content of the widget.
	 *
	 * @since 1.0.0
	 * @param array args     The array of form elements.
	 * @param array instance The current instance of the widget.
	 */
	public function widget( $args, $instance ) {

		// Check if there is a cached output.
		$cache = wp_cache_get( $this->get_widget_slug(), 'widget' );

		if ( !is_array( $cache ) )
			$cache = array();

		if ( !isset( $args['widget_id'] ) )
			$args['widget_id'] = $this->widget_slug;

		if ( isset( $cache[ $args['widget_id'] ] ) )
			return print $cache[ $args['widget_id'] ];

		extract( $args, EXTR_SKIP );

		/**
		 * Filter the widget title.
		 *
		 * @since 1.0.0
		 * @param string $title       The widget title.
		 * @param array  $instance    An array of the widget's settings.
		 * @param mixed  $widget_slug The widget ID.
		 */
		$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->widget_slug );

		$atts = array();
		$widget_string = $before_widget;

		// Display the widget title if one was input.
		if ( $title ) {
			$atts['before_title'] = $before_title;
			$atts['title']        = $title;
			$atts['after_title']  = $after_title;
		}

		/**
		 * Fires before a content widget.
		 *
		 * @since 1.0.0
		 */
		do_action( $this->widget_slug . '_before' );

		// Integer values.
		if ( isset( $instance['limit'] ) && ( 0 < count( $instance['limit'] ) ) ) {
			$atts['limit'] = intval( $instance['limit'] );
		}
		if ( isset( $instance['specific_id'] ) && ( 0 < count( $instance['specific_id'] ) ) ) {
			$atts['id'] = $instance['specific_id'];
		}
		if ( isset( $instance['size'] ) && ( 0 < count( $instance['size'] ) ) ) {
			$atts['size'] = intval( $instance['size'] );
		}

		// Boolean values.
		if ( isset( $instance['display_author'] ) && ( 1 == $instance['display_author'] ) ) {
			$atts['display_author'] = true;
		} else {
			$atts['display_author'] = false;
		}
		if ( isset( $instance['display_avatar'] ) && ( 1 == $instance['display_avatar'] ) ) {
			$atts['display_avatar'] = true;
		} else {
			$atts['display_avatar'] = false;
		}

		// Select boxes.
		if ( isset( $instance['orderby'] ) && in_array( $instance['orderby'], array_keys( $this->get_orderby_options() ) ) ) {
			$atts['orderby'] = $instance['orderby'];
		}
		if ( isset( $instance['order'] ) && in_array( $instance['order'], array_keys( $this->get_order_options() ) ) ) {
			$atts['order'] = $instance['order'];
		}

		$atts['custom_class'] = $instance['custom_class'];

		// Make sure we return and don't echo.
		$atts['echo'] = false;

		/**
		 * Filter the array of widget arguments.
		 *
		 * @since 1.0.0
		 * @param array Arguments.
		 */
		$atts = apply_filters( 'cherry_testimonials_widget_args', $atts );

		$widget_string .= $this->the_testimonials( $atts );
		$widget_string .= $after_widget;


		$cache[ $args['widget_id'] ] = $widget_string;

		wp_cache_set( $this->get_widget_slug(), $cache, 'widget' );

		print $widget_string;

		/**
		 * Fires after a content widget.
		 *
		 * @since 1.0.0
		 */
		do_action( $this->widget_slug . '_after' );
	}

	public function flush_widget_cache() {
		wp_cache_delete( $this->get_widget_slug(), 'widget' );
	}

	/**
	 * Processes the widget's options to be saved.
	 *
	 * @since 1.0.0
	 * @param array new_instance The new instance of values to be generated via the update.
	 * @param array old_instance The previous instance of values before the update.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		// Strip tags for title and name to remove HTML (important for text inputs).
		$instance['title']       = strip_tags( $new_instance['title'] );
		$instance['specific_id'] = strip_tags( $new_instance['specific_id'] );

		// Make sure the integer values are definitely integers.
		$instance['limit'] = intval( $new_instance['limit'] );
		$instance['size']  = intval( $new_instance['size'] );

		// The select box is returning a text value, so we escape it.
		$instance['orderby'] = esc_attr( $new_instance['orderby'] );
		$instance['order']   = esc_attr( $new_instance['order'] );

		// The checkbox is returning a Boolean (true/false), so we check for that.
		$instance['display_author'] = (bool) esc_attr( $new_instance['display_author'] );
		$instance['display_avatar'] = (bool) esc_attr( $new_instance['display_avatar'] );

		$instance['custom_class'] = sanitize_html_class( $new_instance['custom_class'] );

		return apply_filters( 'cherry_testimonials_widget_update', $instance );
	}

	/**
	 * Generates the administration form for the widget.
	 *
	 * @since 1.0.0
	 * @param array instance The array of keys and values for the widget.
	 */
	public function form( $instance ) {
		/**
		 * Filters some default widget settings.
		 *
		 * @since 1.0.0
		 * @param array
		 */
		$defaults = apply_filters( 'cherry_testimonials_widget_form_defaults_args', array(
			'title'          => '',
			'limit'          => 2,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'specific_id'    => '',
			'display_author' => true,
			'display_avatar' => true,
			'size'           => 50,
			'custom_class'   => '',
		) );

		$instance       = wp_parse_args( (array) $instance, $defaults );
		$title          = esc_attr( $instance['title'] );
		$limit          = absint( $instance['limit'] );
		$size           = absint( $instance['size'] );
		$display_author = (bool) $instance['display_author'];
		$display_avatar = (bool) $instance['display_avatar'];
		$specific_id    = esc_attr( $instance['specific_id'] );
		$orderby        = $this->get_orderby_options();
		$order          = $this->get_order_options();
		$custom_class   = esc_attr( $instance['custom_class'] );

		// Display the admin form.
		include( apply_filters( 'cherry_testimonials_widget_form_file', trailingslashit( CHERRY_TESTI_DIR ) . 'admin/views/widget.php' ) );
	}

	/*--------------------------------------------------*/
	/* Public/Protected Functions
	/*--------------------------------------------------*/

	public function item_template( $template, $args ) {
		return '<blockquote>%%TEXT%% %%AVATAR%% %%AUTHOR%%</blockquote>';
	}

	/**
	 * Get an array of the available orderby options.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	protected function get_orderby_options() {
		return apply_filters( 'cherry_testimonials_get_orderby_options', array(
			'none'       => __( 'No Order', 'cherry-testimonials' ),
			'ID'         => __( 'Entry ID', 'cherry-testimonials' ),
			'title'      => __( 'Title', 'cherry-testimonials' ),
			'date'       => __( 'Date Added', 'cherry-testimonials' ),
			'menu_order' => __( 'Attributes Order', 'cherry-testimonials' ),
			'rand'       => __( 'Random Order', 'cherry-testimonials' ),
			) );
	}

	/**
	 * Get an array of the available order options.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	protected function get_order_options() {
		return array(
			'ASC'  => __( 'Ascending', 'cherry-testimonials' ),
			'DESC' => __( 'Descending', 'cherry-testimonials' )
			);
	}

}

add_action( 'widgets_init', create_function( '', 'register_widget("Cherry_Testimonials_Widget");' ) );