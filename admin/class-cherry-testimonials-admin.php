<?php
/**
 * Sets up the admin functionality for the plugin.
 *
 * @package   Cherry_Testimonials_Admin
 * @author    Cherry Team
 * @license   GPL-2.0+
 * @link      http://www.cherryframework.com/
 * @copyright 2014 Cherry Team
 */

class Cherry_Testimonials_Admin {

	/**
	 * Holds the instances of this class.
	 *
	 * @since  1.0.0
	 * @var    object
	 */
	private static $instance = null;

	/**
	 * Sets up needed actions/filters for the admin to initialize.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function __construct() {

		// Load post meta boxes on the post editing screen.
		add_action( 'load-post.php',     array( $this, 'load_post_meta_boxes' ) );
		add_action( 'load-post-new.php', array( $this, 'load_post_meta_boxes' ) );
	}

	/**
	 * Loads custom meta boxes on the "add new menu item" and "edit menu item" screens.
	 *
	 * @since  1.0.0
	 */
	public function load_post_meta_boxes() {
		require_once( CHERRY_TESTI_DIR . 'admin/class-cherry-testimonials-meta-boxes.php' );
	}

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
}

Cherry_Testimonials_Admin::get_instance();