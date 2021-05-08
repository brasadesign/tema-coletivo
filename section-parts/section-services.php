<?php
/**
 * Section Services
 *
 * @package coletivo
 */

$coletivo_service_id       = get_theme_mod( 'coletivo_services_id', esc_html__( 'services', 'coletivo' ) );
$coletivo_service_disable  = get_theme_mod( 'coletivo_services_disable' ) === 1 ? true : false;
$coletivo_service_title    = get_theme_mod( 'coletivo_services_title', esc_html__( 'Our Services', 'coletivo' ) );
$coletivo_service_subtitle = get_theme_mod( 'coletivo_services_subtitle', esc_html__( 'Section subtitle', 'coletivo' ) );
// Get data.
$page_ids = coletivo_get_section_services_data();
if ( coletivo_is_selective_refresh() ) {
	$coletivo_service_disable = false;
}
if ( ! empty( $page_ids ) ) {
	$layout = intval( get_theme_mod( 'coletivo_service_layout', 6 ) );
	$desc   = get_theme_mod( 'coletivo_services_desc' );

	if ( ! $coletivo_service_disable ) {
		if ( ! coletivo_is_selective_refresh() ) {
			?>
			<section id="<?php if ( '' !== $coletivo_service_id )  echo esc_attr( $coletivo_service_id ); ?>" <?php do_action( 'coletivo_section_atts', 'services' ); ?> class="<?php echo esc_attr( apply_filters( 'coletivo_section_class', 'section-services section-padding section-meta onepage-section', 'services' ) ); // phpcs:ignore ?>">
			<?php
		}
		?>
		<?php do_action( 'coletivo_section_before_inner', 'services' ); ?>
		<div class="container">
			<?php if ( $coletivo_service_title || $coletivo_service_subtitle || $desc ) { ?>
			<div class="section-title-area">
				<?php
				if ( '' !== $coletivo_service_subtitle ) {
					echo '<h5 class="section-subtitle">' . esc_html( $coletivo_service_subtitle ) . '</h5>';
				}

				if ( '' !== $coletivo_service_title ) {
					echo '<h2 class="section-title">' . esc_html( $coletivo_service_title ) . '</h2>';
				}

				if ( $desc ) {
					echo '<div class="section-desc">' . apply_filters( 'the_content', wp_kses_post( $desc ) ) . '</div>'; // phpcs:ignore
				}
				?>
			</div>
			<?php } ?>
			<div class="row">
				<?php
				if ( ! empty( $page_ids ) ) {
					global $post;

					$columns = 2;
					switch ( $layout ) {
						case 12:
							$columns = 1;
							break;
						case 6:
							$columns = 2;
							break;
						case 4:
							$columns = 3;
							break;
						case 3:
							$columns = 4;
							break;
					}
					$j = 0;
					foreach ( $page_ids as $settings ) {
						$postid  = $settings['content_page'];
						$postid  = apply_filters( 'wpml_object_id', $postid, 'page', true );
						$thepost = get_post( $postid );
						setup_postdata( $thepost );
						$settings['icon'] = trim( $settings['icon'] );

						$media = '';

						if ( 'image' === $settings['icon_type'] && $settings['image'] ) {
							$url = coletivo_get_media_url( $settings['image'] );
							if ( $url ) {
								$media = '<div class="service-image icon-image"><img src="' . esc_url( $url ) . '" alt=""></div>';
							}
						} elseif ( $settings['icon'] ) {
							$settings['icon'] = trim( $settings['icon'] );
							// Get Set social icons.
							if ( '' !== $settings['icon'] && 0 !== strpos( $settings['icon'], 'fa' ) ) {
								$settings['icon'] = 'fa-' . $settings['icon'];
							}
							$media = '<div class="service-image"><i class="fa ' . esc_attr( $settings['icon'] ) . ' fa-5x"></i></div>';
						}

						$classes = 'col-sm-6 col-lg-' . $layout;
						if ( $j >= $columns ) {
							$j        = 1;
							$classes .= ' clearleft';
						} else {
							$j++;
						}

						?>
						<div class="<?php echo esc_attr( $classes ); ?> wow slideInUp">
							<div class="service-item ">
								<?php
								if ( ! empty( $settings['enable_link'] ) ) {
									?>
									<a class="service-link" href="<?php the_permalink(); ?>"><span class="screen-reader-text"><?php the_title(); ?></span></a>
									<?php
								}
								?>
								<?php if ( has_post_thumbnail() ) { ?>
									<div class="service-thumbnail ">
										<?php
										the_post_thumbnail( 'coletivo-medium' );
										?>
									</div>
								<?php } ?>
								<?php
								if ( '' !== $media ) {
									echo wp_kses_post( $media );
								}
								?>
								<div class="service-content">
									<h4 class="service-title"><?php the_title(); ?></h4>
									<?php the_excerpt(); ?>
								</div>
							</div>
						</div>
						<?php
					}
					wp_reset_postdata();
				}

				?>
			</div>
		</div>
		<?php
		do_action( 'coletivo_section_after_inner', 'services' );
		if ( ! coletivo_is_selective_refresh() ) {
			?>
			</section>
			<?php
		}
	}
}
