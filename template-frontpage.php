<?php
/**
 *Template Name: Frontpage
 *
 * @package coletivo
 */

get_header(); ?>

	<div id="content" class="site-content">
		<main id="main" class="site-main" role="main">
            <?php

            do_action( 'coletivo_frontpage_before_section_parts' );

			if ( ! has_action( 'coletivo_frontpage_section_parts' ) ) {
                
                $order = get_theme_mod( 'coletivo_sections_order', 'hero,content,features,yourslider,featuredpage,services,portfolio,videolightbox,gallery,team,news,contact,social' );
                $order = explode( ',', $order );
                
                // var_dump( get_theme_mod( 'coletivo_sections_order' ) );
                $sections = apply_filters( 'coletivo_frontpage_sections_order',
                $order );

				foreach ( $sections as $section ){
                    /**
                     * Hook before section
                     */
                    do_action('coletivo_before_section_'.$section );
                    do_action( 'coletivo_before_section_part', $section );

                    /**
                     * Load section template part
                     */
					get_template_part( 'section-parts/section', $section );

                    /**
                     * Hook after section
                     */
                    do_action('coletivo_after_section_part', $section );
                    do_action('coletivo_after_section_'.$section );
				}

			} else {
				do_action( 'coletivo_frontpage_section_parts' );
			}

            do_action( 'coletivo_frontpage_after_section_parts' );

			?>
		</main><!-- #main -->
	</div><!-- #content -->

<?php get_footer(); ?>
