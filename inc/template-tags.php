<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package coletivo
 */

/**
 * Display header brand
 *
 * @since 1.2.1
 */

if ( ! function_exists( 'coletivo_site_logo' ) ) {
	/**
	 * Site logo
	 */
	function coletivo_site_logo() {
		$classes         = array();
		$html            = '';
		$classes['logo'] = 'no-logo-img';

		if ( function_exists( 'has_custom_logo' ) ) {
			if ( has_custom_logo() ) {
				$classes['logo'] = 'has-logo-img';
				$html           .= '<div class="site-logo-div">';
				$html           .= get_custom_logo();
				$html           .= '</div>';
			}
		}

		$hide_sitetile = get_theme_mod( 'coletivo_hide_sitetitle', 0 );
		$hide_tagline  = get_theme_mod( 'coletivo_hide_tagline', 0 );

		if ( ! $hide_sitetile ) {
			$classes['title'] = 'has-title';
			if ( is_front_page() && ! is_home() ) {
				$html .= '<h1 class="site-title"><a class="site-text-logo" href="' . esc_url( home_url( '/' ) ) . '" rel="home">' . get_bloginfo( 'name' ) . '</a></h1>';
			} else {
				$html .= '<p class="site-title"><a class="site-text-logo" href="' . esc_url( home_url( '/' ) ) . '" rel="home">' . get_bloginfo( 'name' ) . '</a></p>';
			}
		}

		if ( ! $hide_tagline ) {
			$description = get_bloginfo( 'description', 'display' );
			if ( $description || is_customize_preview() ) {
				$classes['desc'] = 'has-desc';
				$html           .= '<p class="site-description">' . $description . '</p>';
			}
		} else {
			$classes['desc'] = 'no-desc';
		}

		printf(
			'<div class="%s %s">%s</div>',
			'site-brand-inner',
			esc_attr( join( ' ', $classes ) ),
			$html // phpcs:ignore
		);
	}
}

add_action( 'coletivo_site_start', 'coletivo_site_header' );
if ( ! function_exists( 'coletivo_site_header' ) ) {
	/**
	 * Display site header
	 */
	function coletivo_site_header() {
		?>
		<header id="masthead" class="site-header" role="banner">
			<div class="container">
				<div class="site-branding">
				<?php
				coletivo_site_logo();
				?>
				</div>
				<!-- .site-branding -->

				<div class="header-right-wrapper">
					<a href="#0" id="nav-toggle"><?php esc_html_e( 'Menu', 'coletivo' ); ?><span></span></a>
					<nav id="site-navigation" class="main-navigation" role="navigation">
						<ul class="coletivo-menu">
							<?php
							wp_nav_menu(
								array(
									'theme_location' => 'primary',
									'container'      => '',
									'items_wrap'     => '%3$s',
								)
							);
							?>
						</ul>
					</nav>
					<!-- #site-navigation -->
				</div>
			</div>
		</header><!-- #masthead -->
		<?php
	}
}


if ( ! function_exists( 'coletivo_posted_on' ) ) {
	/**
	 * Prints HTML with meta information for the current post-date/time and author.
	 */
	function coletivo_posted_on() {
		the_post();

		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
		if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated hide" datetime="%3$s">%4$s</time>';
		}

		$time_string = sprintf(
			$time_string,
			esc_attr( get_the_date( 'c' ) ),
			esc_html( get_the_date() ),
			esc_attr( get_the_modified_date( 'c' ) ),
			esc_html( get_the_modified_date() )
		);

		$posted_on = sprintf(
			__x( 'Posted on %s', 'post date', 'coletivo' ),
			'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
		);

		$byline = sprintf(
			__x( 'by %s', 'post author', 'coletivo' ),
			'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
		);

		echo '<span class="posted-on">' . $posted_on . '</span><span class="byline"> ' . $byline . '</span>'; // phpcs:ignore

		rewind_posts();
	}
}

if ( ! function_exists( 'coletivo_entry_footer' ) ) {
	/**
	 * Prints HTML with meta information for the categories, tags and comments.
	 */
	function coletivo_entry_footer() {
		// Hide category and tag text for pages.
		if ( 'post' === get_post_type() ) {
			/* translators: used between list items, there is a space after the comma */
			$categories_list = get_the_category_list( esc_html__( ', ', 'coletivo' ) );
			if ( $categories_list && coletivo_categorized_blog() ) {
				printf( '<span class="cat-links">' . esc_html__( 'Posted in %1$s', 'coletivo' ) . '</span>', $categories_list ); // phpcs:ignore
			}

			/* translators: used between list items, there is a space after the comma */
			$tags_list = get_the_tag_list( '', esc_html__( ', ', 'coletivo' ) );
			if ( $tags_list ) {
				printf( '<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'coletivo' ) . '</span>', $tags_list ); // phpcs:ignore
			}
		}

		if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
			echo '<span class="comments-link">';
			comments_popup_link( esc_html__( 'Leave a comment', 'coletivo' ), esc_html__( '1 Comment', 'coletivo' ), esc_html__( '% Comments', 'coletivo' ) );
			echo '</span>';
		}

	}
}

/**
 * Returns true if a blog has more than 1 category.
 *
 * @return bool
 */
function coletivo_categorized_blog() {
	$all_the_cool_cats = get_transient( 'coletivo_categories' );
	if ( false === $all_the_cool_cats ) {
		// Create an array of all the categories that are attached to posts.
		$all_the_cool_cats = get_categories(
			array(
				'fields'     => 'ids',
				'hide_empty' => 1,

				// We only need to know if there is more than one category.
				'number'     => 2,
			)
		);

		// Count the number of categories that are attached to the posts.
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'coletivo_categories', $all_the_cool_cats );
	}

	if ( $all_the_cool_cats > 1 ) {
		// This blog has more than 1 category so coletivo_categorized_blog should return true.
		return true;
	} else {
		// This blog has only 1 category so coletivo_categorized_blog should return false.
		return false;
	}
}

/**
 * Flush out the transients used in coletivo_categorized_blog.
 */
function coletivo_category_transient_flusher() {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// Like, beat it. Dig?
	delete_transient( 'coletivo_categories' );
}
add_action( 'edit_category', 'coletivo_category_transient_flusher' );
add_action( 'save_post', 'coletivo_category_transient_flusher' );


if ( ! function_exists( 'coletivo_comment' ) ) {
	/**
	 * Template for comments and pingbacks.
	 *
	 * To override this walker in a child theme without modifying the comments template
	 * simply create your own coletivo_comment(), and that function will be used instead.
	 *
	 * Used as a callback by wp_list_comments() for displaying the comments.
	 *
	 * @param WP_Comment[] $comment Array of WP_Comment objects.
	 * @param string|array $args    Formatting options.
	 * @param string|int   $depth   The comments depth.
	 *
	 * @return void|string
	 */
	function coletivo_comment( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment; // phpcs:ignore
		switch ( $comment->comment_type ) {
			case 'pingback':
			case 'trackback':
				// Display trackbacks differently than normal comments.
				?>
				<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
					<p><?php esc_html_e( 'Pingback:', 'coletivo' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( '(Edit)', 'coletivo' ), '<span class="edit-link">', '</span>' ); ?></p>
				<?php
				break;
			default:
				// Proceed with normal comments.
				global $post;
				?>
				<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
					<article id="comment-<?php comment_ID(); ?>" class="comment clearfix">

						<?php echo get_avatar( $comment, 60 ); ?>

						<div class="comment-wrapper">

							<header class="comment-meta comment-author vcard">
								<?php
									printf(
										'<cite><b class="fn">%1$s</b> %2$s</cite>',
										get_comment_author_link(),
										// If current post author is also comment author, make it known visually.
										( $comment->user_id === $post->post_author ) ? '<span>' . esc_html__( 'Post author', 'coletivo' ) . '</span>' : ''
									);
									printf(
										'<a class="comment-time" href="%1$s"><time datetime="%2$s">%3$s</time></a>',
										esc_url( get_comment_link( $comment->comment_ID ) ),
										esc_html( get_comment_time( 'c' ) ),
										/* translators: 1: date, 2: time */
										esc_html( get_comment_date() )
									);
									comment_reply_link(
										array_merge(
											$args,
											array(
												'reply_text' => __( 'Reply', 'coletivo' ),
												'after' => '',
												'depth' => $depth,
												'max_depth' => $args['max_depth'],
											)
										)
									);
									edit_comment_link( __( 'Edit', 'coletivo' ), '<span class="edit-link">', '</span>' );
								?>
							</header><!-- .comment-meta -->

							<?php if ( '0' === $comment->comment_approved ) : ?>
								<p class="comment-awaiting-moderation"><?php esc_html_e( 'Your comment is awaiting moderation . ', 'coletivo' ); ?></p>
							<?php endif; ?>

							<div class="comment-content entry-content">
								<?php comment_text(); ?>
												</div><!-- .comment-content -->

						</div><!--/comment-wrapper-->

					</article><!-- #comment-## -->
				<?php
				break;
		} // end comment_type check
	}
}

if ( ! function_exists( 'coletivo_hex_to_rgba' ) ) {
	/**
	 * Convert hex color to rgba color
	 *
	 * @since 1.1.5
	 *
	 * @param string $color The hex color.
	 * @param int    $alpha The alpha value.
	 *
	 * @return bool|string
	 */
	function coletivo_hex_to_rgba( $color, $alpha = 1 ) {
		$color = str_replace( '#', '', $color );
		if ( '' === $color ) {
			return '';
		}

		if ( strpos( trim( $color ), 'rgb' ) !== false ) {
			return $color;
		}

		// 3 or 6 hex digits, or the empty string.
		if ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', '#' . $color ) ) {
			// convert to rgb.
			$colour = $color;
			if ( strlen( $colour ) === 6 ) {
				list( $r, $g, $b) = array( $colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5] );
			} elseif ( strlen( $colour ) === 3 ) {
				list( $r, $g, $b) = array( $colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2] );
			} else {
				return false;
			}
			$r = hexdec( $r );
			$g = hexdec( $g );
			$b = hexdec( $b );
			return 'rgba( ' .
				join(
					',',
					array(
						'r' => $r,
						'g' => $g,
						'b' => $b,
						'a' => $alpha,
					)
				) .
			' )';
		}

		return false;

	}
}

add_action( 'wp_enqueue_scripts', 'coletivo_custom_inline_style', 100 );
if ( ! function_exists( 'coletivo_custom_inline_style' ) ) {
	/**
	 * Add custom css to header
	 *
	 * @change 1.1.5
	 */
	function coletivo_custom_inline_style() {

		/**
		 *  Custom hero section css
		 */
		$hero_bg_color = get_theme_mod( 'coletivo_hero_overlay_color', '#000000' );

		// Deprecate form v 1.1.5.
		$hero_bg_color = coletivo_hex_to_rgba( $hero_bg_color, get_theme_mod( 'coletivo_hero_overlay_opacity', .3 ) );

		/**
		 *  Custom featuredpage section css
		 */
		$featuredpage_bg_color = get_theme_mod( 'coletivo_featuredpage_overlay_color', '#000000' );

		// Deprecate form v 1.1.5.
		$featuredpage_bg_color = coletivo_hex_to_rgba( $featuredpage_bg_color, get_theme_mod( 'coletivo_featuredpage_overlay_opacity', .3 ) );

		ob_start();
		?>
		#main .video-section section.hero-slideshow-wrapper {
			background: transparent;
		}
		.hero-slideshow-wrapper:after {
			position: absolute;
			top: 0px;
			left: 0px;
			width: 100%;
			height: 100%;
			background-color: <?php echo esc_attr( $hero_bg_color ); ?>;
			display: block;
			content: "";
		}
		.body-desktop .parallax-hero .hero-slideshow-wrapper:after {
			display: none !important;
		}
		.parallax-hero .parallax-mirror:after {
			position: absolute;
			top: 0px;
			left: 0px;
			width: 100%;
			height: 100%;
			background-color: <?php echo esc_attr( $hero_bg_color ); ?>;
			display: block;
			content: "";
		}
		.parallax-hero .hero-slideshow-wrapper:after {
			display: none !important;
		}
		.parallax-hero .parallax-mirror:after {
			position: absolute;
			top: 0px;
			left: 0px;
			width: 100%;
			height: 100%;
			background-color: <?php echo esc_attr( $hero_bg_color ); ?>;
			display: block;
			content: "";
		}

		.section-featuredpage:before {
			background-color: <?php echo esc_attr( $featuredpage_bg_color ); ?>;
			display: block;
		}
		<?php
		/**
		 * Theme Color
		 */
		$primary = get_theme_mod( 'coletivo_primary_color' );
		if ( '' !== $primary ) {
			?>
			a, .screen-reader-text:hover, .screen-reader-text:active, .screen-reader-text:focus, .header-social a, .coletivo-menu a:hover,
			.coletivo-menu ul li a:hover, .coletivo-menu li.coletivo-current-item > a, .coletivo-menu ul li.current-menu-item > a, .coletivo-menu > li a.menu-actived,
			.coletivo-menu.coletivo-menu-mobile li.coletivo-current-item > a, .site-footer a, .section-social a, .section-social .footer-social a:hover, .site-footer .btt a:hover,
			.highlight, #comments .comment .comment-wrapper .comment-meta .comment-time:hover, #comments .comment .comment-wrapper .comment-meta .comment-reply-link:hover, #comments .comment .comment-wrapper .comment-meta .comment-edit-link:hover,
			.btn-theme-primary-outline, .sidebar .widget a:hover, .section-services .service-item .service-image i, .counter_item .counter__number,
			.team-member .member-thumb .member-profile a:hover, .icon-background-default
			{
				color: #<?php echo esc_attr( $primary ); ?>;
			}
			input[type="reset"], input[type="submit"], input[type="submit"], .nav-links a:hover, .btn-theme-primary, .btn-theme-primary-outline:hover, .card-theme-primary,
			.woocommerce #respond input#submit, .woocommerce a.button, .woocommerce button.button, .woocommerce input.button, .woocommerce button.button.alt
			{
				background: #<?php echo esc_attr( $primary ); ?>;
			}
			.btn-theme-primary-outline, .btn-theme-primary-outline:hover, .pricing__item:hover, .card-theme-primary, .entry-content blockquote
			{
				border-color : #<?php echo esc_attr( $primary ); ?>;
			}
			<?php
		} // End $primary

		/**
		 * Header background
		 */
		$header_bg_color = get_theme_mod( 'coletivo_header_bg_color' );
		if ( $header_bg_color ) {
			?>
			.site-header {
				background: #<?php echo esc_attr( $header_bg_color ); ?>;
				border-bottom: 0px none;
			}
			<?php
		} // END $header_bg_color

		/**
		 * Menu color
		 */
		$menu_color = get_theme_mod( 'coletivo_menu_color' );
		if ( $menu_color ) {
			?>
			.coletivo-menu > li > a {
				color: #<?php echo esc_attr( $menu_color ); ?>;
			}
			<?php
		} // END $menu_color

		/**
		 * Menu hover color
		 */
		$menu_hover_color = get_theme_mod( 'coletivo_menu_hover_color' );
		if ( $menu_hover_color ) {
			?>
			.coletivo-menu > li > a:hover,
			.coletivo-menu > li.coletivo-current-item > a{
				color: #<?php echo esc_attr( $menu_hover_color ); ?>;
				-webkit-transition: all 0.5s ease-in-out;
				-moz-transition: all 0.5s ease-in-out;
				-o-transition: all 0.5s ease-in-out;
				transition: all 0.5s ease-in-out;
			}
			<?php
		} // END $menu_hover_color

		/**
		 * Menu hover background color
		 */
		$menu_hover_bg = get_theme_mod( 'coletivo_menu_hover_bg_color' );
		if ( $menu_hover_bg ) {
			?>
			@media screen and (min-width: 1140px) {
				.coletivo-menu > li:last-child > a {
					padding-right: 17px;
				}
				.coletivo-menu > li > a:hover,
				.coletivo-menu > li.coletivo-current-item > a
				{
					background: #<?php echo esc_attr( $menu_hover_bg ); ?>;
					-webkit-transition: all 0.5s ease-in-out;
					-moz-transition: all 0.5s ease-in-out;
					-o-transition: all 0.5s ease-in-out;
					transition: all 0.5s ease-in-out;
				}
			}
			<?php
		} // END $menu_hover_bg

		/**
		 * Reponsive Mobile button color
		 */
		$menu_button_color = get_theme_mod( 'coletivo_menu_toggle_button_color' );
		if ( $menu_button_color ) {
			?>
			#nav-toggle span, #nav-toggle span::before, #nav-toggle span::after,
			#nav-toggle.nav-is-visible span::before, #nav-toggle.nav-is-visible span::after {
				background: #<?php echo esc_attr( $menu_button_color ); ?>;
			}
			<?php
		}

		/**
		 * Site Title
		 */
		$coletivo_logo_text_color = get_theme_mod( 'coletivo_logo_text_color' );
		if ( $coletivo_logo_text_color ) {
			?>
			.site-branding .site-title, .site-branding .site-text-logo {
				color: #<?php echo esc_attr( $coletivo_logo_text_color ); ?>;
			}
			<?php
		}

		$coletivo_footer_bg = get_theme_mod( 'coletivo_footer_bg' );
		if ( $coletivo_footer_bg ) {
			?>
			.section-social {
				background-color: #<?php echo esc_attr( $coletivo_footer_bg ); ?>;
			}
			.section-social .footer-connect .follow-heading {
				color: rgba(255, 255, 255, 0.9);
			}
			<?php
		}

		$coletivo_footer_info_bg = get_theme_mod( 'coletivo_footer_info_bg' );
		if ( $coletivo_footer_info_bg ) {
			?>
			.site-footer .site-info, .site-footer .btt a{
				background-color: #<?php echo esc_attr( $coletivo_footer_info_bg ); ?>;
			}
			.site-footer .site-info {
				color: rgba(255, 255, 255, 0.7);
			}
			.site-footer .btt a, .site-footer .site-info a {
				color: rgba(255, 255, 255, 0.9);
			}
			<?php
		}

		$gallery_spacing = absint( get_theme_mod( 'coletivo_g_spacing', 20 ) );

		?>
		.gallery-carousel .g-item{
			padding: 0px <?php echo intval( $gallery_spacing / 2 ); ?>px;
		}
		.gallery-carousel {
			margin-left: -<?php echo intval( $gallery_spacing / 2 ); ?>px;
			margin-right: -<?php echo intval( $gallery_spacing / 2 ); ?>px;
		}
		.gallery-grid .g-item, .gallery-masonry .g-item .inner {
			padding: <?php echo intval( $gallery_spacing / 2 ); ?>px;
		}
		.gallery-grid, .gallery-masonry {
			margin: -<?php echo intval( $gallery_spacing / 2 ); ?>px;
		}
		<?php

		$css = ob_get_clean();

		if ( '' === trim( $css ) ) {
			return;
		}
		$css = preg_replace(
			array(
				// Remove comment(s).
				'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\' )|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
				// Remove unused white-space(s).
				'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/) )|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~+]|\s*+-(?![0-9\.] )|!important\b)\s*+|([[(:] )\s++|\s++([] )] )|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\' )*+{)|^\s++|\s++\z|(\s)\s+#si',
			),
			array(
				'$1',
				'$1$2$3$4$5$6$7',
			),
			$css
		);

		$custom = get_option( 'coletivo_custom_css' );
		if ( $custom ) {
			$css .= '\n/* --- Begin custom CSS --- */\n' . $custom . '\n/* --- End custom CSS --- */\n';
		}

		wp_add_inline_style( 'coletivo-style', $css );
	}
}

if ( ! function_exists( 'coletivo_get_section_featuredpage_data' ) ) {
	/**
	 * Get Featured Page data
	 *
	 * @return array
	 */
	function coletivo_get_section_featuredpage_data() {
		$id = get_theme_mod( 'coletivo_featuredpage_content' );
		if ( ! $id ) {
			return false;
		} else {
			$page_ids = array( $id );
			return $page_ids;
		}
	}
}

if ( ! function_exists( 'coletivo_get_section_services_data' ) ) {
	/**
	 * Get services data
	 *
	 * @return array
	 */
	function coletivo_get_section_services_data() {
		$services = get_theme_mod( 'coletivo_services' );
		if ( is_string( $services ) ) {
			$services = json_decode( $services, true );
		}
		$page_ids = array();
		if ( ! empty( $services ) && is_array( $services ) ) {
			foreach ( $services as $k => $v ) {
				if ( isset( $v['content_page'] ) ) {
					$v['content_page'] = absint( $v['content_page'] );
					if ( $v['content_page'] > 0 ) {
						$page_ids[] = wp_parse_args(
							$v,
							array(
								'icon_type'   => 'icon',
								'image'       => '',
								'icon'        => 'gg',
								'enable_link' => 0,
							)
						);
					}
				}
			}
		}
		// if still empty data then get some page for demo.
		return $page_ids;
	}
}

if ( ! function_exists( 'coletivo_get_section_team_data' ) ) {
	/**
	 * Get team members
	 *
	 * @return array
	 */
	function coletivo_get_section_team_data() {
		$members = get_theme_mod( 'coletivo_team_members' );
		if ( is_string( $members ) ) {
			$members = json_decode( $members, true );
		}
		if ( ! is_array( $members ) ) {
			$members = array();
		}
		return $members;
	}
}

if ( ! function_exists( 'coletivo_get_section_features_data' ) ) {
	/**
	 * Get features data
	 *
	 * @since 1.1.4
	 * @return array
	 */
	function coletivo_get_section_features_data() {
		$array = get_theme_mod( 'coletivo_features_boxes' );
		if ( is_string( $array ) ) {
			$array = json_decode( $array, true );
		}
		if ( ! empty( $array ) && is_array( $array ) ) {
			foreach ( $array as $k => $v ) {
				$array[ $k ] = wp_parse_args(
					$v,
					array(
						'icon'  => 'gg',
						'title' => '',
						'desc'  => '',
						'link'  => '',
					)
				);

				// Get/Set social icons.
				$array[ $k ]['icon'] = trim( $array[ $k ]['icon'] );
				if ( '' !== $array[ $k ]['icon'] && 0 !== strpos( $array[ $k ]['icon'], 'fa' ) ) {
					$array[ $k ]['icon'] = 'fa-' . $array[ $k ]['icon'];
				}
			}
		}
		return $array;
	}
}

if ( ! function_exists( 'coletivo_get_social_profiles' ) ) {
	/**
	 * Get social profiles
	 *
	 * @since 1.1.4
	 * @return bool|array
	 */
	function coletivo_get_social_profiles() {
		$array = get_theme_mod( 'coletivo_social_profiles' );
		if ( is_string( $array ) ) {
			$array = json_decode( $array, true );
		}
		$html = '';
		if ( ! empty( $array ) && is_array( $array ) ) {
			foreach ( $array as $k => $v ) {
				$array[ $k ] = wp_parse_args(
					$v,
					array(
						'network' => '',
						'icon'    => '',
						'link'    => '',
					)
				);

				// Get/Set social icons.
				// If icon isset.
				$icons               = array();
				$array[ $k ]['icon'] = trim( $array[ $k ]['icon'] );
				if ( '' !== $array[ $k ]['icon'] && 0 !== strpos( $array[ $k ]['icon'], 'fa' ) ) {
					$icons[ $array[ $k ]['icon'] ] = 'fa-' . $array[ $k ]['icon'];
				} else {
					$icons[ $array[ $k ]['icon'] ] = $array[ $k ]['icon'];
				}
				$network = ( $array[ $k ]['network'] ) ? sanitize_title( $array[ $k ]['network'] ) : false;
				if ( $network && ! $array[ $k ]['icon'] ) {
					$icons[ 'fa-' . $network ] = 'fa-' . $network;
				}

				$array[ $k ]['icon'] = join( ' ', $icons );

			}
		}

		foreach ( (array) $array as $s ) {
			if ( '' !== $s['icon'] ) {
				$html .= '<a target="_blank" href="' . $s['link'] . '" title="' . esc_attr( $s['network'] ) . '"><i class="fa ' . esc_attr( $s['icon'] ) . '"></i></a>';
			}
		}

		return $html;
	}
}


if ( ! function_exists( 'coletivo_get_section_gallery_data' ) ) {
	/**
	 * Get Gallery data
	 *
	 * @since 1.2.6
	 *
	 * @return array
	 */
	function coletivo_get_section_gallery_data() {

		$source = 'page';
		if ( has_filter( 'coletivo_get_section_gallery_data' ) ) {
			$data = apply_filters( 'coletivo_get_section_gallery_data', false );
			return $data;
		}

		$data = array();

		switch ( $source ) {
			default:
				$page_id = get_theme_mod( 'coletivo_gallery_source_page' );
				$images  = '';
				if ( $page_id ) {
					$gallery = get_post_gallery( $page_id, false );
					if ( $gallery ) {
						$images = $gallery['ids'];
					}
				}

				$image_thumb_size = apply_filters( 'coletivo_gallery_page_img_size', 'coletivo-medium' );

				if ( ! empty( $images ) ) {
					$images = explode( ',', $images );
					foreach ( $images as $post_id ) {
						$post = get_post( $post_id );
						if ( $post ) {
							$img_thumb = wp_get_attachment_image_src( $post_id, $image_thumb_size );
							if ( $img_thumb ) {
								$img_thumb = $img_thumb[0];
							}

							$img_full = wp_get_attachment_image_src( $post_id, 'full' );
							if ( $img_full ) {
								$img_full = $img_full[0];
							}

							if ( $img_thumb && $img_full ) {
								$data[ $post_id ] = array(
									'id'        => $post_id,
									'thumbnail' => $img_thumb,
									'full'      => $img_full,
									'title'     => $post->post_title,
									'content'   => $post->post_content,
								);
							}
						}
					}
				}
				break;
		}

		return $data;

	}
}

/**
 * Generate HTML content for gallery items.
 *
 * @since 1.2.6
 *
 * @param string $data  The gallery item data.
 * @param bool   $inner HTML Class.
 * @param string $size  The thumbnail size.
 *
 * @return string
 */
function coletivo_gallery_html( $data, $inner = true, $size = 'thumbnail' ) {
	$max_item = get_theme_mod( 'coletivo_g_number', 10 );
	$html     = '';
	if ( ! is_array( $data ) ) {
		return $html;
	}
	$n = count( $data );
	if ( $max_item > $n ) {
		$max_item = $n;
	}
	$i = 0;
	while ( $i < $max_item ) {
		$photo = current( $data );
		$i ++;
		if ( 'full' === $size ) {
			$thumb = $photo['full'];
		} else {
			$thumb = $photo['thumbnail'];
		}

		$html .= '<a href="' . esc_attr( $photo['full'] ) . '" class="g-item" title="' . esc_attr( wp_strip_all_tags( $photo['title'] ) ) . '">';
		if ( $inner ) {
			$html .= '<span class="inner">';
			$html .= '<span class="inner-content">';
			$html .= '<img src="' . esc_url( $thumb ) . '" alt="">';
			$html .= '</span>';
			$html .= '</span>';
		} else {
			$html .= '<img src="' . esc_url( $thumb ) . '" alt="">';
		}

		$html .= '</a>';
		next( $data );
	}
	reset( $data );

	return $html;
}


/**
 * Generate Gallery HTML
 *
 * @since 1.2.6
 * @param bool $echo Variable to prints $div.
 *
 * @return string
 */
function coletivo_gallery_generate( $echo = true ) {

	$div = '';

	$data         = coletivo_get_section_gallery_data();
	$display_type = get_theme_mod( 'coletivo_gallery_display', 'grid' );
	$lightbox     = get_theme_mod( 'coletivo_g_lightbox', 1 );
	$class        = '';
	if ( $lightbox ) {
		$class = ' enable-lightbox ';
	}
	$col = absint( get_theme_mod( 'coletivo_g_col', 4 ) );
	if ( $col <= 0 ) {
		$col = 4;
	}
	switch ( $display_type ) {
		case 'masonry':
			$html = coletivo_gallery_html( $data );
			if ( $html ) {
				$div .= '<div data-col="' . $col . '" class="g-zoom-in gallery-masonry ' . $class . ' gallery-grid g-col-' . $col . '">';
				$div .= $html;
				$div .= '</div>';
			}
			break;
		case 'carousel':
			$html = coletivo_gallery_html( $data );
			if ( $html ) {
				$div .= '<div data-col="' . $col . '" class="g-zoom-in gallery-carousel' . $class . '">';
				$div .= $html;
				$div .= '</div>';
			}
			break;
		case 'slider':
			$html = coletivo_gallery_html( $data, true, 'full' );
			if ( $html ) {
				$div .= '<div class="gallery-slider' . $class . '">';
				$div .= $html;
				$div .= '</div>';
			}
			break;
		case 'justified':
			$html = coletivo_gallery_html( $data, false );
			if ( $html ) {
				$gallery_spacing = absint( get_theme_mod( 'coletivo_g_spacing', 20 ) );
				$row_height      = absint( get_theme_mod( 'coletivo_g_row_height', 120 ) );
				$div            .= '<div data-row-height="' . $row_height . '" data-spacing="' . $gallery_spacing . '" class="g-zoom-in gallery-justified' . $class . '">';
				$div            .= $html;
				$div            .= '</div>';
			}
			break;
		default: // grid.
			$html = coletivo_gallery_html( $data );
			if ( $html ) {
				$div .= '<div class="gallery-grid g-zoom-in ' . $class . ' g-col-' . $col . '">';
				$div .= $html;
				$div .= '</div>';
			}
			break;
	}

	if ( $echo ) {
		echo $div; // phpcs:ignore
	} else {
		return $div;
	}

}


if ( ! function_exists( 'coletivo_footer_site_info' ) ) {
	/**
	 * Add Copyright and Credit text to footer
	 *
	 * @since 1.1.3
	 */
	function coletivo_footer_site_info() {
		$coletivo_footer_text      = get_theme_mod( 'coletivo_footer_text', esc_html__( 'Few Rights Reserved', 'coletivo' ) );
		$coletivo_footer_text_link = get_theme_mod( 'coletivo_footer_text_link' );

		if ( '' !== $coletivo_footer_text_link ) {
			echo '<a href="' . esc_html( $coletivo_footer_text_link ) . '" alt="" target="_blank">';
		}

		if ( '' !== $coletivo_footer_text ) {
			echo '<div class="container">' . esc_html( $coletivo_footer_text ) . '</div>';
		}

		if ( '' !== $coletivo_footer_text_link ) {
			echo '</a>';
		}

		printf( esc_html__( '%2$s %1$s', 'coletivo' ), esc_attr( gmdate( 'Y' ) ), esc_attr( get_bloginfo() ) );
		?>
		<span class="sep"> &ndash; </span>
		<?php printf( esc_html__( 'Proudly Powered by %1$s', 'coletivo' ), '<a href="' . esc_url( 'https://br.wordpress.org', 'coletivo' ) . '">WordPress</a>' ); ?>
		<?php
	}
}
add_action( 'coletivo_footer_site_info', 'coletivo_footer_site_info' );


/**
 * Breadcrumb NavXT Compatibility.
 */
function coletivo_breadcrumb() {
	if ( function_exists( 'bcn_display' ) ) {
		?>
		<div class="breadcrumbs" typeof="BreadcrumbList" vocab="http://schema.org/">
			<div class="container">
				<?php bcn_display(); ?>
			</div>
		</div>
		<?php
	}
}

if ( ! function_exists( 'coletivo_is_selective_refresh' ) ) {
	/**
	 * Coletivo Selective Refresh function
	 */
	function coletivo_is_selective_refresh() {
		return isset( $GLOBALS['coletivo_is_selective_refresh'] ) && $GLOBALS['coletivo_is_selective_refresh'] ? true : false;
	}
}

/**
 * Get blog posts class
 *
 * @return array
 */
function coletivo_get_blog_post_class() {
	$style   = get_theme_mod( 'coletivo_blog_page_style', 'grid' );
	$classes = '';
	if ( 'list' === $style ) {
		$classes = array( 'list-style' );
	} else {
		$classes = array( 'list-article', 'clearfix' );
	}
	return $classes;
}
