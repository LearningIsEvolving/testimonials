<?php
/**
 * Handles custom post meta boxes for the 'testimonial' post type.
 *
 * @package   Cherry_Testimonials_Admin
 * @author    Cherry Team
 * @license   GPL-2.0+
 * @link      http://www.cherryframework.com/
 * @copyright 2014 Cherry Team
 */

class Cherry_Testimonials_Meta_Boxes {

	/**
	 * Holds the instances of this class.
	 *
	 * @since 1.0.0
	 * @var   object
	 */
	private static $instance = null;

	/**
	 * Sets up the needed actions for adding and saving the meta boxes.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post',      array( $this, 'save_post'      ), 10, 2 );
	}

	/**
	 * Adds the meta box container.
	 *
	 * @since 1.0.0
	 */
	public function add_meta_boxes() {
		/**
		 * Filter the array of 'add_meta_box' parametrs.
		 *
		 * @since 1.0.0
		 */
		$metabox = apply_filters( 'cherry_testimonials_metabox_params', array(
			'id'            => 'cherry-testi-options',
			'title'         => __( 'Testimonial Options', 'cherry-testimonials' ),
			'page'          => CHERRY_TESTI_NAME,
			'context'       => 'side',
			'priority'      => 'core',
			'callback_args' => array(
				array(
					'name' => __( 'Name:', 'cherry-testimonials' ),
					'desc' => __( "Enter author's name.", 'cherry-testimonials' ),
					'id'   => 'name',
					'std'  => '',
					),
				array(
					'name' => __( 'E-mail:', 'cherry-testimonials' ),
					'desc' => __( 'Enter in an e-mail address.', 'cherry-testimonials' ),
					'id'   => 'email',
					'std'  => '',
					),
				array(
					'name' => __( 'URL:', 'cherry-testimonials' ),
					'desc' => __( 'Enter a URL.', 'cherry-testimonials' ),
					'id'   => 'url',
					'std'  => '',
					),
				)
			)
		);

		/**
		 * Add meta box to the administrative interface.
		 *
		 * @link http://codex.wordpress.org/Function_Reference/add_meta_box
		 */
		add_meta_box(
			$metabox['id'],
			$metabox['title'],
			array( $this, 'callback_metabox' ),
			$metabox['page'],
			$metabox['context'],
			$metabox['priority'],
			$metabox['callback_args']
		);
	}

	/**
	 * Prints the box content.
	 *
	 * @since 1.0.0
	 * @param object $post    Current post object.
	 * @param array  $metabox
	 */
	public function callback_metabox( $post, $metabox ) {

		// Add an nonce field so we can check for it later.
		wp_nonce_field( plugin_basename( __FILE__ ), 'cherry_testi_options_meta_nonce' );

		foreach ( $metabox['args'] as $field ) :

			// Check if set the 'name' and 'id' value for custom field. If not - don't add field.
			if ( !isset( $field['name'] ) || !isset( $field['id'] ) )
				continue;

			// Define the field attributes value.
			$field_id   = CHERRY_TESTI_POSTMETA . '_' . $field['id'];
			$field_name = CHERRY_TESTI_POSTMETA . '[' . $field['id'] . ']';
			$field_desc = ( isset( $field['desc'] ) ) ? $field['desc'] : '';
			$field_std  = ( isset( $field['std'] ) ) ? $field['std'] : '';

			// Get current post meta data.
			$post_meta  = get_post_meta( $post->ID, CHERRY_TESTI_POSTMETA, true );

			if ( !empty( $post_meta ) && isset( $post_meta[ $field['id'] ] ) ) {
				$field_value = $post_meta[ $field['id'] ];
			} else {
				$field_value = $field_std;
			}

			echo '<p>';
				echo '<label for="' . esc_attr( $field_id ) . '">' . esc_html( $field['name'] ) . '</label>';
				echo '<input type="text" class="widefat" name="' . esc_attr( $field_name ) . '" id="' . esc_attr( $field_id ) . '" value="' . esc_attr( $field_value ) . '">';
				echo '<small>' . esc_html( $field_desc ) . '</small>';
			echo '</p>';

		endforeach;

		/**
		 * Fires after testimonial fields of metabox.
		 *
		 * @since 1.0.0
		 * @param object $post                 Current post object.
		 * @param array  $metabox
		 * @param string CHERRY_TESTI_POSTMETA Name for 'meta_key' value in the 'wp_postmeta' table.
		 */
		do_action( 'cherry_testimonials_metabox_after', $post, $metabox, CHERRY_TESTI_POSTMETA );
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @since 1.0.0
	 * @param int    $post_id
	 * @param object $post
	 */
	public function save_post( $post_id, $post ) {

		// Verify the nonce.
		if ( !isset( $_POST['cherry_testi_options_meta_nonce'] ) || !wp_verify_nonce( $_POST['cherry_testi_options_meta_nonce'], plugin_basename( __FILE__ ) ) )
			return;

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Get the post type object.
		$post_type = get_post_type_object( $post->post_type );

		// Check if the current user has permission to edit the post.
		if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
			return $post_id;

		// Don't save if the post is only a revision.
		if ( 'revision' == $post->post_type )
			return;

		// Array of new post meta value.
		$new_meta_value = array();

		// Check if $_POST have a needed key.
		if ( isset( $_POST[ CHERRY_TESTI_POSTMETA ] ) && !empty( $_POST[ CHERRY_TESTI_POSTMETA ] ) ) {

			foreach ( $_POST[ CHERRY_TESTI_POSTMETA ] as $key => $value ) {

				if ( 'email' == $key ) {
					$new_meta_value[ $key ] = sanitize_email( $value );
					continue;
				}

				if ( 'url' == $key ) {
					$new_meta_value[ $key ] = esc_url_raw( $value );
					continue;
				}

				// Sanitize the user input.
				$new_meta_value[ $key ] = sanitize_text_field( $value );
			}

		}

		// Check if nothing found in $_POST array.
		if ( empty( $new_meta_value ) )
			return;

		// Get current post meta data.
		$meta_value = get_post_meta( $post_id, CHERRY_TESTI_POSTMETA, true );

		// If a new meta value was added and there was no previous value, add it.
		if ( $new_meta_value && '' == $meta_value )
			add_post_meta( $post_id, CHERRY_TESTI_POSTMETA, $new_meta_value, true );

		// If the new meta value does not match the old value, update it.
		elseif ( $new_meta_value && $new_meta_value != $meta_value )
			update_post_meta( $post_id, CHERRY_TESTI_POSTMETA, $new_meta_value );

		// If there is no new meta value but an old value exists, delete it.
		elseif ( '' == $new_meta_value && $meta_value )
			delete_post_meta( $post_id, CHERRY_TESTI_POSTMETA, $meta_value );
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

Cherry_Testimonials_Meta_Boxes::get_instance();