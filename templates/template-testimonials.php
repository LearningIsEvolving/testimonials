<?php
/**
 * Template Name: Testimonials
 *
 * The template for displaying CPT Testimonials.
 *
 * @package Cherry_Testimonials
 * @since   1.0.0
 */

if ( have_posts() ) :

	while ( have_posts() ) :

			the_post(); ?>

			<article <?php cherry_attr( 'post' ); ?>>

				<?php
					// Display a page title.
					cherry_the_post_header();

					// Display a page content.
					cherry_the_post_content();

					$args = array(
						'limit'        => 4,
						'pager'        => 'true',
						'custom_class' => 'testimonials-page',
					);
					$data = new Cherry_Testimonials_Data;
					$data->the_testimonials( $args );
				?>

			</article>

	<?php endwhile;

endif; ?>