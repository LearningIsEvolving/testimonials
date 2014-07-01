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
 * Class for custom shortcode.
 *
 * @since 1.0.0
 */
class Cherry_Testimonials_Shortcode {

	/**
	 * Shortcode name.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	private $name = 'cherry_testimonials';

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

		// Register shortcodes on 'init'.
		add_action( 'init', array( $this, 'register_shortcode' ) );

		/**
		 * Fires when you need to display testimonials.
		 *
		 * @since 1.0.0
		 */
		add_action( 'cherry_get_testimonials', array( $this, 'the_testimonials' ) );
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
		$tag = apply_filters( "{$this->name}_shortcode_name", $this->name );

		add_shortcode( $tag, array( $this, 'do_shortcode' ) );
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
			'display_url'    => true,
			'size'           => 50,
			'echo'           => false,
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
		foreach ( array( 'display_author', 'display_avatar', 'display_url' ) as $k => $v ) :

			if ( isset( $atts[$v] ) && ( 'true' == $atts[$v] ) ) {
				$atts[$v] = true;
			} else {
				$atts[$v] = false;
			}

		endforeach;

		return $this->the_testimonials( $atts );
	}

	/**
	 * Display or return HTML-formatted testimonials.
	 *
	 * @since  1.0.0
	 * @param  string|array $args Arguments.
	 * @return string
	 */
	public function the_testimonials( $args = '' ) {
		/**
		 * Filter the array of default arguments.
		 *
		 * @since 1.0.0
		 * @param array Default arguments.
		 */
		$defaults = apply_filters( 'cherry_testimonials_default_args', array(
			'limit'          => 3,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'id'             => 0,
			'display_author' => true,
			'display_avatar' => true,
			'display_url'    => true,
			'size'           => 50,
			'echo'           => true,
			'title'          => '',
			'wrap_class'     => 'testimonials-wrap',
			'before_title'   => '<h2>',
			'after_title'    => '</h2>',
			'custom_class'   => '',
		) );

		$args = wp_parse_args( $args, $defaults );

		/**
		 * Filter the array of arguments.
		 *
		 * @since 1.0.0
		 * @param array Arguments.
		 */
		$args = apply_filters( 'cherry_testimonials_args', $args );
		$output = '';

		/**
		 * Fires before the Testimonials.
		 *
		 * @since 1.0.0
		 * @param array $array The array of arguments.
		 */
		do_action( 'cherry_testimonials_before', $args );

		// The Query.
		$query = $this->get_testimonials( $args );

		// The Display.
		if ( !is_wp_error( $query ) && is_array( $query ) && count( $query ) > 0 ) {

			$css_class = '';

			if ( !empty( $args['wrap_class'] ) ) {
				$css_class .= sanitize_html_class( $args['wrap_class'] ) . ' ';
			}

			if ( !empty( $args['custom_class'] ) ) {
				$css_class .= sanitize_html_class( $args['custom_class'] );
			}

			// Open wrapper.
			$output .= sprintf( '<div class="%s">', trim( $css_class ) );

			if ( !empty( $args['title'] ) ) {
				$output .= $args['before_title'] . __( esc_html( $args['title'] ), 'cherry-testimonials' ) . $args['after_title'];
			}

			$output .= '<div class="testimonials-list">';

			// Begin templating logic.
			$template = '<blockquote>%%TEXT%% %%AVATAR%% <footer>%%AUTHOR%% %%URL%%</footer></blockquote>';
			$template = apply_filters( 'cherry_testimonials_item_template', $template, $args );

			$count = 1;
			foreach ( $query as $post ) :

				// Sets up global post data.
				setup_postdata( $post );

				$tpl       = $template;
				$post_id   = $post->ID;
				$post_meta = ( isset( $post->{CHERRY_TESTI_POSTMETA} ) ) ? $post->{CHERRY_TESTI_POSTMETA} : false;
				$url       = ( isset( $post_meta['url'] ) ) ? make_clickable( $post_meta['url'] ) : '';
				$avatar    = ( isset( $post->image ) && $post->image ) ? $post->image  : '';

				/**
				 * Filters the Testimonials post content.
				 *
				 * @since 1.0.0
				 * @param string A post content.
				 * @param object A post object.
				 */
				$content = apply_filters( 'cherry_testimonials_content', apply_filters( 'the_content', get_the_content() ), $post );
				$tpl     = str_replace( '%%TEXT%%', $content, $tpl );

				$output .= '<div id="quote-'. $post_id .'" class="testimonials-item item-'. $count++ .' clearfix">';
					// $output .= '<blockquote>';
						// $output .= '<footer>';

						// Check 'display_avatar' option.
						if ( true === $args['display_avatar'] ) {
							$tpl = str_replace( '%%AVATAR%%', $avatar, $tpl );
						} else {
							$tpl = str_replace( '%%AVATAR%%', '', $tpl );
						}

						// Check 'display_author' option.
						if ( true === $args['display_author'] ) {

							$author = '<cite class="author" title="' . esc_attr( get_the_title( $post_id ) ) . '">' . get_the_title( $post_id ) . '</cite>';
							$tpl    = str_replace( '%%AUTHOR%%', $author, $tpl );

						} else {
							$tpl = str_replace( '%%AUTHOR%%', '', $tpl );
						}

						// Check 'display_url' option.
						if ( true === $args['display_url'] ) {
							$tpl = str_replace( '%%URL%%', $url, $tpl );
						} else {
							$tpl = str_replace( '%%URL%%', '', $tpl );
						}

						$output .= $tpl;

						// $output .= '</footer>';
					// $output .= '</blockquote>';
				$output .= '</div><!--/.testimonials-item-->';

			endforeach;

			// Restore the global $post variable.
			wp_reset_postdata();

			$output .= '</div><!--/.testimonials-list-->';

			// Close wrapper
			$output .= '</div>';
		}

		/**
		 * Filters HTML-formatted testimonials before display or return.
		 *
		 * @since 1.0.0
		 * @param string $output The HTML-formatted testimonials.
		 * @param array  $query  List of WP_Post objects.
		 * @param array  $array  The array of arguments.
		 */
		$output = apply_filters( 'cherry_testimonials_html', $output, $query, $args );

		if ( $args['echo'] != true ) {
			return $output;
		}

		// If "echo" is set to true.
		echo $output;

		/**
		 * Fires after the Testimonials.
		 *
		 * This hook fires only when "echo" is set to true.
		 *
		 * @since 1.0.0
		 * @param array $array The array of arguments.
		 */
		do_action( 'cherry_testimonials_after', $args );
	}

	/**
	 * Get testimonials.
	 *
	 * @since  1.0.0
	 * @param  array|string $args Arguments to be passed to the query.
	 * @return array|bool         Array if true, boolean if false.
	 */
	public function get_testimonials( $args = '' ) {

		$defaults = array(
			'limit'   => 5,
			'orderby' => 'date',
			'order'   => 'DESC',
			'id'      => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		/**
		 * Filter the array of arguments.
		 *
		 * @since 1.0.0
		 * @param array Arguments to be passed to the query.
		 */
		$args = apply_filters( 'cherry_get_testimonials_args', $args );

		// The Query Arguments.
		$query_args = array();
		$query_args['post_type']        = CHERRY_TESTI_NAME;
		$query_args['numberposts']      = $args['limit'];
		$query_args['orderby']          = $args['orderby'];
		$query_args['order']            = $args['order'];
		$query_args['suppress_filters'] = false;

		$ids = explode( ',', $args['id'] );

		if ( 0 < intval( $args['id'] ) && 0 < count( $ids ) ) :

			$ids = array_map( 'intval', $ids );

			if ( 1 == count( $ids ) && is_numeric( $ids[0] ) && ( 0 < intval( $ids[0] ) ) ) {

				$query_args['p'] = intval( $args['id'] );

			} else {

				$query_args['ignore_sticky_posts'] = 1;
				$query_args['post__in'] = $ids;

			}

		endif;

		// Whitelist checks.
		if ( !in_array( $query_args['orderby'], array( 'none', 'ID', 'author', 'title', 'date', 'modified', 'parent', 'rand', 'comment_count', 'menu_order', 'meta_value', 'meta_value_num' ) ) ) {
			$query_args['orderby'] = 'date';
		}

		if ( !in_array( $query_args['order'], array( 'ASC', 'DESC' ) ) ) {
			$query_args['order'] = 'DESC';
		}

		/**
		 * Filters the query.
		 *
		 * @since 1.0.0
		 * @param array The array of query arguments.
		 * @param array The array of arguments to be passed to the query.
		 */
		$query_args = apply_filters( 'cherry_testimonials_query_args', $query_args, $args );

		// The Query.
		$query = get_posts( $query_args );

		if ( !is_wp_error( $query ) && is_array( $query ) && count( $query ) > 0 ) {

			foreach ( $query as $i => $post ) {

				// Get the post image.
				$query[$i]->image = $this->get_image( $post->ID, $args['size'] );

				// Get the post meta data.
				$post_meta = get_post_meta( $post->ID, CHERRY_TESTI_POSTMETA, true );

				if ( !empty( $post_meta ) ) :

					// Adds new property to the post object.
					$query[ $i ]->{CHERRY_TESTI_POSTMETA} = $post_meta;

				endif;

			}

			return $query;

		} else {
			return false;
		}
	}

	/**
	 * Get the image for the given ID. If no featured image, check for Gravatar e-mail.
	 *
	 * @since  1.0.0
	 * @param  int              $id   The post ID.
	 * @param  string|array|int $size The image dimension.
	 * @return string
	 */
	public function get_image( $id, $size ) {
		$image = '';

		if ( has_post_thumbnail( $id ) ) :

			// If not a string or an array, and not an integer, default to 150x9999.
			if ( ( is_int( $size ) || ( 0 < intval( $size ) ) ) && !is_array( $size ) ) {

				$size = array( intval( $size ), intval( $size ) );

			} elseif ( !is_string( $size ) && !is_array( $size ) ) {

				$size = array( 50, 50 );

			}

			$image = get_the_post_thumbnail( intval( $id ), $size, array( 'class' => 'avatar' ) );

		else :

			$post_meta = get_post_meta( $id, CHERRY_TESTI_POSTMETA, true );

			if ( !empty( $post_meta ) && is_array( $post_meta ) && isset( $post_meta['email'] ) ) {

				$email = $post_meta['email'];

				if ( !empty( $email ) && is_email( $email ) ) {

					$image = get_avatar( $email, $size );

				}
			}

		endif;

		return $image;
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