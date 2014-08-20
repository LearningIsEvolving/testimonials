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
 * Class for register post types.
 *
 * @since 1.0.0
 */
class Cherry_Testimonials_Registration {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since 1.0.0
	 * @var   object
	 */
	private static $instance = null;

	/**
	 * Sets up needed actions/filters.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Adds the testimonials post type.
		add_action( 'init', array( $this, 'register' ) );

		// Removes rewrite rules and then recreate rewrite rules.
		add_action( 'init', array( $this, 'rewrite_rules' ) );
	}

	public function rewrite_rules() {
		flush_rewrite_rules();
	}

	/**
	 * Register the custom post type.
	 *
	 * @since 1.0.0
	 * @link http://codex.wordpress.org/Function_Reference/register_post_type
	 */
	public function register() {
		$labels = array(
			'name'               => __( 'Testimonials', 'cherry-testimonials' ),
			'singular_name'      => __( 'Testimonial', 'cherry-testimonials' ),
			'add_new'            => __( 'Add New', 'cherry-testimonials' ),
			'add_new_item'       => __( 'Add New Testimonial', 'cherry-testimonials' ),
			'edit_item'          => __( 'Edit Testimonial', 'cherry-testimonials' ),
			'new_item'           => __( 'New Testimonial', 'cherry-testimonials' ),
			'view_item'          => __( 'View Testimonial', 'cherry-testimonials' ),
			'search_items'       => __( 'Search Testimonials', 'cherry-testimonials' ),
			'not_found'          => __( 'No testimonials found', 'cherry-testimonials' ),
			'not_found_in_trash' => __( 'No testimonials found in trash', 'cherry-testimonials' ),
		);

		$supports = array(
			'title',
			'editor',
			'thumbnail',
			'revisions',
			'page-attributes',
		);

		$args = array(
			'labels'          => $labels,
			'supports'        => $supports,
			'public'          => true,
			'capability_type' => 'post',
			'rewrite'         => array( 'slug' => 'testimonial', ), // Permalinks format
			'menu_position'   => null,
			'menu_icon'       => ( version_compare( $GLOBALS['wp_version'], '3.8', '>=' ) ) ? 'dashicons-testimonial' : '',
			'can_export'      => true,
			'has_archive'     => true,
		);

		$args = apply_filters( 'cherry_testimonials_post_type_args', $args );

		register_post_type( CHERRY_TESTI_NAME, $args );
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

Cherry_Testimonials_Registration::get_instance();