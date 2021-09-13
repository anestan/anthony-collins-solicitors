<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package acs
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'acs' ); ?></a>

	<header id="masthead" class="site-header">
		<nav class="navbar navbar-expand-lg navbar-dark" role="navigation">
			<div class="container-fluid">
				<div class="row">
					<div class="col">
						<a class="navbar-brand" href="#">
							<?php echo get_custom_logo(); ?>
						</a>
					</div>
				</div>
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-controls="bs-example-navbar-collapse-1" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle navigation', 'acs' ); ?>">
					<span class="navbar-toggler-icon"></span>
				</button>
					<?php
					wp_nav_menu( array(
						'theme_location'    => 'header-menu',
						'depth'             => 2,
						'container'         => 'div',
						'container_class'   => 'collapse navbar-collapse',
						'container_id'      => 'bs-example-navbar-collapse-1',
						'menu_class'        => 'nav navbar-nav',
						'fallback_cb'       => 'WP_Bootstrap_Navwalker::fallback',
						'walker'            => new WP_Bootstrap_Navwalker(),
					) );
					?>

					<form class="form-inline my-2 my-lg-0">
						<input class="form-control mr-sm-2 bg-transparent border-0 text-white text-right" type="search" placeholder="Search" aria-label="Search">
						<button class="btn btn-secondary rounded-circle my-2 my-sm-0 btn-search-nav border-0" type="submit">
							<i class="bi bi-search"></i>
						</button>
					</form>
				</div>
			</nav>

		<!-- #site-navigation -->
	</header>
	<!-- #masthead -->
