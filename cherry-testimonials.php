<?php
/**
 * Plugin Name: Cherry Testimonials
 * Plugin URI:  http://www.cherryframework.com/
 * Description: A testimonials management plugin for WordPress.
 * Version:     1.0.0
 * Author:      Cherry Team
 * Author URI:  http://www.cherryframework.com/
 * Text Domain: cherry-testimonials
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
	die;
}

/**
 * Sets up and initializes the Cherry Testimonials plugin.
 *
 * @since 1.0.0
 */
class Cherry_Testimonials {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since 1.0.0
	 * @var   object
	 */
	private static $instance = null;

	/**
	 * Sets up needed actions/filters for the plugin to initialize.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Set the constants needed by the plugin.
		add_action( 'plugins_loaded', array( $this, 'constants' ), 1 );

		// Internationalize the text strings used.
		add_action( 'plugins_loaded', array( $this, 'lang' ),      2 );

		// Load the functions files.
		add_action( 'plugins_loaded', array( $this, 'includes' ),  3 );

		// Load the admin files.
		add_action( 'plugins_loaded', array( $this, 'admin' ),     4 );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		// add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Register activation and deactivation hook.
		register_activation_hook( __FILE__, array( $this, 'activation'   ) );
		register_activation_hook( __FILE__, array( $this, 'deactivation' ) );
	}

	/**
	 * Defines constants for the plugin.
	 *
	 * @since 1.0.0
	 */
	function constants() {

		/**
		 * Set constant name for the post type name.
		 *
		 * @since 1.0.0
		 */
		define( 'CHERRY_TESTI_NAME', 'testimonial' );

		/**
		 * Set the version number of the plugin.
		 *
		 * @since 1.0.0
		 */
		define( 'CHERRY_TESTI_VERSION', '1.0.0' );

		/**
		 * Set the name for the 'meta_key' value in the 'wp_postmeta' table.
		 *
		 * @since 1.0.0
		 */
		define( 'CHERRY_TESTI_POSTMETA', '_cherry_testimonial' );

		/**
		 * Set constant path to the plugin directory.
		 *
		 * @since 1.0.0
		 */
		define( 'CHERRY_TESTI_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );

		/**
		 * Set constant path to the plugin URI.
		 *
		 * @since 1.0.0
		 */
		define( 'CHERRY_TESTI_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );
	}

	/**
	 * Loads files from the '/inc' folder.
	 *
	 * @since 1.0.0
	 */
	function includes() {
		require_once( CHERRY_TESTI_DIR . 'inc/class-cherry-testimonials-registration.php'  );
		require_once( CHERRY_TESTI_DIR . 'inc/class-cherry-testimonials-page-template.php' );
		require_once( CHERRY_TESTI_DIR . 'inc/class-cherry-testimonials-shortcode.php' );
	}

	/**
	 * Loads the translation files.
	 *
	 * @since 1.0.0
	 */
	function lang() {
		load_plugin_textdomain( 'cherry-testimonials', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Loads admin files.
	 *
	 * @since 1.0.0
	 */
	function admin() {

		if ( is_admin() ) {
			require_once( CHERRY_TESTI_DIR . 'admin/class-cherry-testimonials-admin.php' );
		}
	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( 'cherry-testimonials', plugins_url( 'assets/css/style.css', __FILE__ ), array(), CHERRY_TESTI_VERSION );
	}

	/**
	 * On plugin activation.
	 *
	 * @since 1.0.0
	 */
	function activation() {
		flush_rewrite_rules();
	}

	/**
	 * On plugin deactivation.
	 *
	 * @since 1.0.0
	 */
	function deactivation() {
		flush_rewrite_rules();
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

Cherry_Testimonials::get_instance();