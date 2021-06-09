<?php
/**
 * The front page template file.
 *
 * The front-page.php template file is used to render your site’s front page,
 * whether the front page displays the blog posts index (mentioned above) or a static page.
 * The front page template takes precedence over the blog posts index (home.php) template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#front-page-display
 *
 * @package Coletivo
 */

$blog_style      = get_theme_mod( 'coletivo_blog_page_style', 'grid' );
$container_class = 'container';
if ( 'list' === $blog_style ) {
	$container_class = 'container right-sidebar';
}
get_header(); ?>

	<div id="content" class="site-content">
	<div class="page-header">
		<div class="container">
			<h1 class="page-title"><?php single_post_title(); ?></h1>
		</div>
	</div><!-- container -->

	<?php if ( function_exists( 'coletivo_breadcrumb' ) ) { ?>
		<?php echo wp_kses_post( coletivo_breadcrumb() ); ?>
	<?php } ?>

	<div id="content-inside" class="<?php echo esc_attr( $container_class ); ?>">
		<section id="primary" class="content-area">
			<main id="main" class="site-main" role="main">

				<?php
				if ( have_posts() ) {
					if ( is_home() && ! is_front_page() ) {
						?>
						<header>
							<h1 class="page-title screen-reader-text"><?php single_post_title(); ?></h1>
						</header>
						<?php
					}

					/* Start the Loop */
					while ( have_posts() ) {
						the_post();

						/*
							* Include the Post-Format-specific template for the content.
							* If you want to override this in a child theme, then include a file
							* called content-___.php (where ___ is the Post Format name) and that will be used instead.
							*/
						get_template_part( 'template-parts/content', get_post_format() );
					}

						the_posts_navigation();

				} else {
					get_template_part( 'template-parts/content', 'none' );
				}
				?>

			</main><!-- #main -->
		</section><!-- #primary -->

		<?php
		if ( 'list' === $blog_style ) {
			get_sidebar();
		}
		?>

		</div><!--#content-inside -->
	</div><!-- #content -->

<?php get_footer(); ?>
