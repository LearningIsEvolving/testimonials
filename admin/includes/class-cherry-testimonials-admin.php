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
	 * @since 1.0.0
	 * @var   object
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

		// Only run our customization on the 'edit.php' page in the admin.
		add_action( 'load-edit.php', array( $this, 'load_edit' ) );

		// Modify the columns on the "Testimonials" screen.
		add_filter( 'manage_edit-testimonial_columns',        array( $this, 'edit_testimonial_columns'   ) );
		add_action( 'manage_testimonial_posts_custom_column', array( $this, 'manage_testimonial_columns' ), 10, 2 );
	}

	/**
	 * Loads custom meta boxes on the "Add New Testimonial" and "Edit Testimonial" screens.
	 *
	 * @since 1.0.0
	 */
	public function load_post_meta_boxes() {
		$screen = get_current_screen();

		if ( !empty( $screen->post_type ) && 'testimonial' === $screen->post_type ) {
			require_once( trailingslashit( CHERRY_TESTI_DIR ) . 'admin/includes/class-cherry-testimonials-meta-boxes.php' );
		}
	}

	/**
	 * Adds a custom filter on 'request' when viewing the "Testimonials" screen in the admin.
	 *
	 * @since 1.0.0
	 */
	public function load_edit() {
		$screen = get_current_screen();

		if ( !empty( $screen->post_type ) && 'testimonial' === $screen->post_type ) {
			add_action( 'admin_head', array( $this, 'print_styles' ) );
		}
	}

	/**
	 * Style adjustments for the manage menu items screen.
	 *
	 * @since 1.0.0
	 */
	public function print_styles() { ?>
		<style type="text/css">
		.edit-php .wp-list-table td.thumbnail.column-thumbnail,
		.edit-php .wp-list-table th.manage-column.column-thumbnail,
		.edit-php .wp-list-table td.author_name.column-author_name,
		.edit-php .wp-list-table th.manage-column.column-author_name {
			text-align: center;
		}
		</style>
	<?php }

	/**
	 * Filters the columns on the "Testimonials" screen.
	 *
	 * @since  1.0.0
	 * @param  array $post_columns
	 * @return array
	 */
	public function edit_testimonial_columns( $post_columns ) {
		// Adds the checkbox column.
		$columns['cb'] = $post_columns['cb'];

		// Add custom columns and overwrite the 'title' column.
		$columns['title']       = __( 'Title', 'cherry-testimonials' );
		$columns['author_name'] = __( 'Author', 'cherry-testimonials' );
		$columns['date']        = __( 'Date', 'cherry-testimonials' );
		$columns['thumbnail']   = __( 'Thumbnail', 'cherry-testimonials' );

		// Return the columns.
		return $columns;
	}

	/**
	 * Add output for custom columns on the "menu items" screen.
	 *
	 * @since  1.0.0
	 * @param  string $column
	 * @param  int    $post_id
	 */
	public function manage_testimonial_columns( $column, $post_id ) {

		switch( $column ) {

			case 'author_name' :

				$post_meta = get_post_meta( $post_id, CHERRY_TESTI_POSTMETA, true );

				if ( !empty( $post_meta ) ) {
					echo ( isset( $post_meta['name'] ) && !empty( $post_meta['name'] ) ) ? $post_meta['name'] : '&mdash;';
				}

				break;

			case 'thumbnail' :

				$thumb = get_the_post_thumbnail( $post_id, array( 50, 50 ) );

				echo !empty( $thumb ) ? $thumb : '&mdash;';

				break;

			default :
				break;
		}
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