<?php
/**
 * Cherry Testimonials.
 *
 * @package   Cherry_Testimonials
 * @author    Cherry Team
 * @license   GPL-2.0+
 * @link      http://www.cherryframework.com/
 * @copyright 2014 Cherry Team
 */

/**
 * Class for Testimonials shortcode.
 *
 * @since 1.0.0
 */
class Cherry_Testimonials_Shortcode extends Cherry_Testimonials_Data {

	/**
	 * Shortcode name.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	public static $name = 'testimonials';

	/**
	 * A reference to an instance of this class.
	 *
	 * @since 1.0.0
	 * @var   object
	 */
	private static $instance = null;

	/**
	 * Sets up our actions/filters.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Register shortcode on 'init'.
		add_action( 'init', array( $this, 'register_shortcode' ) );

		// Register shortcode and add it to the dialog.
		add_filter( 'su/data/shortcodes', array( $this, 'shortcodes' ) );

		add_filter( 'cherry_editor_target_dirs', array( $this, 'add_target_dir' ), 11 );
	}

	/**
	 * Registers the [$this->name] shortcode.
	 *
	 * @since 1.0.0
	 */
	public function register_shortcode() {
		/**
		 * Filters a shortcode name.
		 *
		 * @since 1.0.0
		 * @param string $this->name Shortcode name.
		 */
		$tag = apply_filters( self::$name . '_shortcode_name', self::$name );

		add_shortcode( $tag, array( $this, 'do_shortcode' ) );
	}

	/**
	 * Filter to modify original shortcodes data and add [$this->name] shortcode.
	 *
	 * @since  1.0.0
	 * @param  array   $shortcodes Original plugin shortcodes.
	 * @return array               Modified array.
	 */
	public function shortcodes( $shortcodes ) {
		$shortcodes[ self::$name ] = array(
			'name'  => __( 'Testimonials', 'su' ), // Shortcode name.
			'desc'  => 'This is a Testimonials Shortcode',
			'type'  => 'single', // Can be 'wrap' or 'single'. Example: [b]this is wrapped[/b], [this_is_single]
			'group' => 'content', // Can be 'content', 'box', 'media' or 'other'. Groups can be mixed, for example 'content box'.
			'atts'  => array( // List of shortcode params (attributes).
						'limit' => array(
							'type'    => 'slider',
							'min'     => -1,
							'max'     => 100,
							'step'    => 1,
							'default' => 3,
							'name'    => __( 'Limit', 'su' ),
							'desc'    => __( 'Maximum number of posts.', 'su' )
						),
						'order' => array(
							'type' => 'select',
							'values' => array(
								'desc' => __( 'Descending', 'su' ),
								'asc'  => __( 'Ascending', 'su' )
							),
							'default' => 'DESC',
							'name' => __( 'Order', 'su' ),
							'desc' => __( 'Posts order', 'su' )
						),
						'orderby' => array(
							'type' => 'select',
							'values' => array(
								'none'       => __( 'None', 'su' ),
								'id'         => __( 'Post ID', 'su' ),
								'author'     => __( 'Post author', 'su' ),
								'title'      => __( 'Post title', 'su' ),
								'name'       => __( 'Post slug', 'su' ),
								'date'       => __( 'Date', 'su' ), 'modified' => __( 'Last modified date', 'su' ),
								'parent'     => __( 'Post parent', 'su' ),
								'rand'       => __( 'Random', 'su' ), 'comment_count' => __( 'Comments number', 'su' ),
								'menu_order' => __( 'Menu order', 'su' ), 'meta_value' => __( 'Meta key values', 'su' ),
							),
							'default' => 'date',
							'name'    => __( 'Order by', 'su' ),
							'desc'    => __( 'Order posts by', 'su' )
						),
						'id' => array(
							'default' => 0,
							'name'    => __( 'Post ID\'s', 'su' ),
							'desc'    => __( 'Enter comma separated ID\'s of the posts that you want to show', 'su' )
						),
						'display_author' => array(
							'type'    => 'bool',
							'default' => 'yes', 'name' => __( 'Display author?', 'su' ),
							'desc'    => __( 'Display author?', 'su' )
						),
						'display_avatar' => array(
							'type'    => 'bool',
							'default' => 'yes', 'name' => __( 'Display avatar?', 'su' ),
							'desc'    => __( 'Display avatar?', 'su' )
						),
						'size' => array(
							'type'    => 'slider',
							'min'     => 10,
							'max'     => 1000,
							'step'    => 10,
							'default' => 50,
							'name'    => __( 'Avatar size', 'su' ),
							'desc'    => __( 'Avatar size (in pixels)', 'su' )
						),
						'custom_class' => array(
							'default' => '',
							'name'    => __( 'Class', 'su' ),
							'desc'    => __( 'Extra CSS class', 'su' )
						),
					),
			'icon'     => 'h-square', // Custom icon (font-awesome).
			'function' => array( $this, 'do_shortcode' ) // Name of shortcode function.
		);

		return $shortcodes;
	}

	public function add_target_dir( $target_dirs ) {
		array_push( $target_dirs, CHERRY_TESTI_DIR );

		return $target_dirs;
	}

	/**
	 * The shortcode function.
	 *
	 * @since  1.0.0
	 * @param  array  $atts      The user-inputted arguments.
	 * @param  string $content   The enclosed content (if the shortcode is used in its enclosing form).
	 * @param  string $shortcode The shortcode tag, useful for shared callback functions.
	 * @return string
	 */
	public function do_shortcode( $atts, $content = null, $shortcode = '' ) {

		// Set up the default arguments.
		$defaults = array(
			'limit'          => 3,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'id'             => 0,
			'display_author' => true,
			'display_avatar' => true,
			'size'           => 50,
			'echo'           => false,
			'template'       => 'default.tmpl',
			'custom_class'   => '',
		);

		/**
		 * Parse the arguments.
		 *
		 * @link http://codex.wordpress.org/Function_Reference/shortcode_atts
		 */
		$atts = shortcode_atts( $defaults, $atts, $shortcode );

		// Make sure we return and don't echo.
		$atts['echo'] = false;

		// Fix integers.
		if ( isset( $atts['limit'] ) ) {
			$atts['limit'] = intval( $atts['limit'] );
		}

		if ( isset( $atts['size'] ) &&  ( 0 < intval( $atts['size'] ) ) ) {
			$atts['size'] = intval( $atts['size'] );
		}

		// Fix booleans.
		foreach ( array( 'display_author', 'display_avatar' ) as $k => $v ) :

			if ( isset( $atts[ $v ] ) && ( 'true' == $atts[ $v ] ) ) {
				$atts[ $v ] = true;
			} else {
				$atts[ $v ] = false;
			}

		endforeach;

		return $this->the_testimonials( $atts );
	}

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance )
			self::$instance = new self;

		return self::$instance;
	}

}

Cherry_Testimonials_Shortcode::get_instance();