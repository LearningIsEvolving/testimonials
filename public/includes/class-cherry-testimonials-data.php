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
 * Class for Testimonials data.
 *
 * @since 1.0.0
 */
class Cherry_Testimonials_Data {

	/**
	 * The array of arguments for query.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	private $query_args = array();

	private $replace_args = array();

	/**
	 * Sets up our actions/filters.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		/**
		 * Fires when you need to display testimonials.
		 *
		 * @since 1.0.0
		 */
		add_action( 'cherry_get_testimonials', array( $this, 'the_testimonials' ) );
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
		 * @param array The 'the_testimonials' function argument.
		 */
		$defaults = apply_filters( 'cherry_the_testimonials_default_args', array(
			'limit'          => 3,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'id'             => 0,
			'display_author' => true,
			'display_avatar' => true,
			'size'           => 50,
			'echo'           => true,
			'title'          => '',
			'wrap_class'     => 'testimonials-wrap',
			'before_title'   => '<h2>',
			'after_title'    => '</h2>',
			'pager'          => false,
			'template'       => 'default.tmpl',
			'custom_class'   => '',
		), $args );

		$args = wp_parse_args( $args, $defaults );

		/**
		 * Filter the array of arguments.
		 *
		 * @since 1.0.0
		 * @param array Arguments.
		 */
		$args = apply_filters( 'cherry_the_testimonials_args', $args );
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

		$all_posts = '';

		// Fix boolean.
		if ( isset( $args['pager'] ) && ( 'true' == $args['pager'] ) ) {
			$args['pager'] = true;

			// Get the array of all posts.
			$all_posts = $this->get_testimonials( array( 'limit' => -1 ) );
			wp_reset_postdata();

		} else {
			$args['pager'] = false;
		}

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
				$output .= $this->get_testimonials_loop( $query, $args, $all_posts );
			$output .= '</div><!--/.testimonials-list-->';

			// Close wrapper.
			$output .= '</div>';
		}

		/**
		 * Filters HTML-formatted testimonials before display or return.
		 *
		 * @since 1.0.0
		 * @param string $output The HTML-formatted testimonials.
		 * @param array  $query  List of WP_Post objects.
		 * @param array  $args   The array of arguments.
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
		$this->query_args['post_type']        = CHERRY_TESTI_NAME;
		$this->query_args['numberposts']      = $args['limit'];
		$this->query_args['orderby']          = $args['orderby'];
		$this->query_args['order']            = $args['order'];
		$this->query_args['suppress_filters'] = false;

		if ( isset( $args['pager'] ) && ( 'true' == $args['pager'] ) ) :

			if ( get_query_var('paged') ) {
				$this->query_args['paged'] = get_query_var('paged');
			} elseif ( get_query_var('page') ) {
				$this->query_args['paged'] = get_query_var('page');
			} else {
				$this->query_args['paged'] = 1;
			}

		endif;

		$ids = explode( ',', $args['id'] );

		if ( 0 < intval( $args['id'] ) && 0 < count( $ids ) ) :

			$ids = array_map( 'intval', $ids );

			if ( 1 == count( $ids ) && is_numeric( $ids[0] ) && ( 0 < intval( $ids[0] ) ) ) {

				$this->query_args['p'] = intval( $args['id'] );

			} else {

				$this->query_args['ignore_sticky_posts'] = 1;
				$this->query_args['post__in'] = $ids;

			}

		endif;

		// Whitelist checks.
		if ( !in_array( $this->query_args['orderby'], array( 'none', 'ID', 'author', 'title', 'date', 'modified', 'parent', 'rand', 'comment_count', 'menu_order', 'meta_value', 'meta_value_num' ) ) ) {
			$this->query_args['orderby'] = 'date';
		}

		if ( !in_array( $this->query_args['order'], array( 'ASC', 'DESC' ) ) ) {
			$this->query_args['order'] = 'DESC';
		}

		/**
		 * Filters the query.
		 *
		 * @since 1.0.0
		 * @param array The array of query arguments.
		 * @param array The array of arguments to be passed to the query.
		 */
		$this->query_args = apply_filters( 'cherry_get_testimonials_query_args', $this->query_args, $args );

		// The Query.
		$query = get_posts( $this->query_args );

		// Return if is a query for all 'testimonial' posts.
		if ( -1 === $this->query_args['numberposts'] ) {
			return $query;
		}

		if ( !is_wp_error( $query ) && is_array( $query ) && count( $query ) > 0 ) {

			foreach ( $query as $i => $post ) {

				// Get the post image.
				$query[ $i ]->image = $this->get_image( $post->ID, $args['size'] );

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

	public function replace_callback( $matches ) {
		$key = strtolower( trim( $matches[0], '%%' ) );

		if ( array_key_exists( $key, $this->replace_args ) ) {
			return $this->replace_args[ $key ];
		} else {
			__return_empty_string();
		}
	}

	/**
	 * Get testimonials items.
	 *
	 * @since  1.0.0
	 * @param  array         $query      List of WP_Post objects.
	 * @param  array         $args       The array of arguments.
	 * @param  array|string  $all_posts  List of all WP_Post objects with type 'testimonial'.
	 * @return string
	 */
	public function get_testimonials_loop( $query, $args, $all_posts = '' ) {
		global $post, $more;

		// Item template.
		$template = $this->get_template_by_name( $args['template'], Cherry_Testimonials_Shortcode::$name );

		/**
		 * Filters template for testimonials item.
		 *
		 * @since 1.0.0
		 * @param string.
		 * @param array   Arguments.
		 */
		$template = apply_filters( 'cherry_testimonials_item_template', $template, $args );

		$count  = 1;
		$output = '';
		foreach ( $query as $post ) :

			// Sets up global post data.
			setup_postdata( $post );

			$tpl       = $template;
			$post_id   = $post->ID;
			$post_meta = ( isset( $post->{CHERRY_TESTI_POSTMETA} ) ) ? $post->{CHERRY_TESTI_POSTMETA} : false;
			$name      = ( isset( $post_meta['name'] ) && ( !empty( $post_meta['name'] ) ) ) ? $post_meta['name'] : get_the_title( $post_id );
			$url       = ( isset( $post_meta['url'] ) ) ? $post_meta['url'] : '';
			$avatar    = ( isset( $post->image ) && $post->image ) ? $post->image  : '';

			$real_more = $more;
			$more      = 0;

			/**
			 * Filters the Testimonials post content.
			 *
			 * @since 1.0.0
			 * @param string A post content.
			 * @param object A post object.
			 */
			// $content = apply_filters( 'cherry_testimonials_content', apply_filters( 'the_content', get_the_content() ), $post );
			$content = apply_filters( 'cherry_testimonials_content', get_the_content(), $post );
			$more    = $real_more;

			$author = '<footer><cite class="author" title="' . esc_attr( $name ) . '">';
			if ( !empty( $url ) ) {
				$author .= '<a href="' . esc_url( $url ) . '">' . $name . '</a>';
			} else {
				$author .= $name;
			}
			$author .= '</cite></footer>';

			$this->replace_args['avatar']  = ( true === $args['display_avatar'] ) ? $avatar : '';
			$this->replace_args['content'] = $content;
			$this->replace_args['author']  = ( true === $args['display_avatar'] ) ? $author : '';

			$tpl = preg_replace_callback( "/%%.+?%%/", array( $this, 'replace_callback' ), $tpl );

			$output .= '<div id="quote-' . $post_id . '" class="testimonials-item item-' . $count . ( ( $count++ % 2 ) ? ' odd' : ' even' ) . ' clearfix">';

				/**
				 * Filters testimonails item.
				 *
				 * @since 1.0.0
				 * @param string.
				 * @param array  A post meta.
				 */
				$tpl = apply_filters( 'cherry_get_testimonails_loop', $tpl, $post_meta );

				$output .= $tpl;

			$output .= '</div><!--/.testimonials-item-->';

		endforeach;

		if ( !is_wp_error( $all_posts ) && is_array( $all_posts ) && count( $all_posts ) > 0 ) :

			$posts_id = wp_list_pluck( $all_posts, 'ID' );

			if ( isset( $args['pager'] ) ) {

				if ( ( true === $args['pager'] ) ) {

					$current     = array_search( get_the_ID(), $posts_id );
					$count_query = count( $query );

					if ( array_key_exists( $current + $count_query, $posts_id ) ) {
						$prevID = $posts_id[ $current + $count_query ];
					}
					if ( array_key_exists( $current - $count_query, $posts_id ) ) {
						$nextID = $posts_id[ $current - $count_query ];
					}

					$pagination = '<nav class="navigation paging-navigation" role="navigation">';
						$pagination .= '<div class="nav-links">';

							if ( isset( $prevID ) ) :

								$pagination .= '<div class="nav-previous">';
									$pagination .= '<a href="' . get_pagenum_link( $this->query_args['paged']+1 ) . '">' . __( '<span class="meta-nav">&larr;</span> Older posts', 'cherry' ) . '</a>';
								$pagination .= '</div>';

							endif;

							if ( isset( $nextID ) ) :

								$pagination .= '<div class="nav-next">';
									$pagination .= '<a href="' . get_pagenum_link( $this->query_args['paged']-1 ) . '">' . __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'cherry' ) . '</a>';
								$pagination .= '</div>';

							endif;

						$pagination .= '</div>';
					$pagination .= '</nav>';

					/**
					 * Filters HTML-formatted pagination for testimonials page before return.
					 *
					 * @since 1.0.0
					 * @param string $pagination The HTML-formatted pagination.
					 * @param array  $args   The array of arguments.
					 */
					$output .= apply_filters( 'cherry_testimonails_pagination_html', $pagination, $args );

				}
			}

		endif;

		// Restore the global $post variable.
		wp_reset_postdata();

		return $output;
	}

	/**
	 * Read template (static).
	 *
	 * @since  1.0.0
	 * @return bool|WP_Error|string - false on failure, stored text on success.
	 */
	public static function get_contents( $template ) {

		if ( !function_exists( 'WP_Filesystem' ) ) {
			include_once( ABSPATH . '/wp-admin/includes/file.php' );
		}

		WP_Filesystem();
		global $wp_filesystem;

		if ( !$wp_filesystem->exists( $template ) ) { // Check for existence.
			return false;
		}

		// Read the file.
		$content = $wp_filesystem->get_contents( $template );

		if ( !$content ) {
			return new WP_Error( 'reading_error', 'Error when reading file' ); // Return error object.
		}

		return $content;
	}

	public function get_template_by_name( $template, $shortcode ) {
		$file    = '';
		$subdir  = 'templates/shortcodes/' . $shortcode . '/' . $template;
		$default = CHERRY_TESTI_DIR . 'templates/shortcodes/' . $shortcode . '/default.tmpl';

		$content = apply_filters( 'cherry_testimonials_fallback_template', '%%avatar%%<blockquote>%%content%% %%author%%</blockquote>' );

		if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $subdir ) ) {
			$file = trailingslashit( get_stylesheet_directory() ) . $subdir;
		} elseif ( file_exists( CHERRY_TESTI_DIR . $subdir ) ) {
			$file = CHERRY_TESTI_DIR . $subdir;
		} else {
			$file = $default;
		}

		if ( !empty( $file ) ) {
			$content = self::get_contents( $file );
		}

		return $content;
	}
}