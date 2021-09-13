<?php
/**
 * acs functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package acs
 */

if ( ! defined( '_S_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( '_S_VERSION', '1.0.0' );
}

if ( ! function_exists( 'acs_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function acs_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on acs, use a find and replace
		 * to change 'acs' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'acs', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus(
			array(
				'menu-1' => esc_html__( 'Primary', 'acs' ),
			)
		);

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);

		// Set up the WordPress core custom background feature.
		add_theme_support(
			'custom-background',
			apply_filters(
				'acs_custom_background_args',
				array(
					'default-color' => 'ffffff',
					'default-image' => '',
				)
			)
		);

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 250,
				'width'       => 250,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);
	}
endif;
add_action( 'after_setup_theme', 'acs_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function acs_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'acs_content_width', 640 );
}
add_action( 'after_setup_theme', 'acs_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function acs_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'acs' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'acs' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'acs_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function acs_scripts() {
	wp_enqueue_style( 'acs-style', get_stylesheet_uri(), array(), _S_VERSION );
	wp_style_add_data( 'acs-style', 'rtl', 'replace' );

	wp_enqueue_script( 'acs-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'acs_scripts' );

function acs_custom_styles_and_scripts() {
	wp_enqueue_style( 'acs-custom-css', get_template_directory_uri() . '/public/css/app.css' );
	wp_enqueue_script( 'acs-custom-js', get_template_directory_uri() . '/public/js/app.js', array ( 'jquery' ), 1.1, true);
}
add_action( 'wp_enqueue_scripts', 'acs_custom_styles_and_scripts' );

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

/**
 * Declare ACF blocks.
 */
add_action('acf/init', 'acs_acf_init_block_types');
function acs_acf_init_block_types() {

    // Check function exists.
    if( function_exists('acf_register_block_type') ) {

        // register a Hero Section block.
        acf_register_block_type(array(
            'name'              => 'Hero Section',
            'title'             => __('Hero Section'),
            'description'       => __('A hero section block.'),
            'render_template'   => 'template-parts/blocks/hero-section/hero-section.php',
            'category'          => 'formatting',
            'icon'              => 'cover-image',
            'keywords'          => array( 'hero' ),
        ));

        // register a Card block.
        acf_register_block_type(array(
            'name'              => 'Card',
            'title'             => __('Card'),
            'description'       => __('A card block.'),
            'render_template'   => 'template-parts/blocks/card/card.php',
            'category'          => 'formatting',
            'icon'              => 'index-card',
            'keywords'          => array( 'hero' ),
        ));
    }
}

/**
 * Register Custom Navigation Walker
 */
function register_navwalker(){
    require_once get_template_directory() . '/class-wp-bootstrap-navwalker.php';
}
add_action( 'after_setup_theme', 'register_navwalker' );

/**
 * Register Menu
 */
function register_my_menu() {
	register_nav_menu('header-menu',__( 'Header Menu' ));
}
add_action( 'init', 'register_my_menu' );

/**
 * Create Services post type
 */
function cptui_register_my_cpts_service() {
	$labels = [
		"name" => __( "Services", "acs" ),
		"singular_name" => __( "Service", "acs" ),
	];

	$args = [
		"label" => __( "Services", "acs" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"has_archive" => 'services',
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => true,
		"rewrite" => [ "slug" => "who-we-help/%sector%", "with_front" => false ],
		"query_var" => true,
		"supports" => [ "title", "editor", "thumbnail", "page-attributes" ],
		"show_in_graphql" => false,
	];

	register_post_type( "service", $args );
}
add_action( 'init', 'cptui_register_my_cpts_service' );

/**
 * Create Sector taxonomy for Services
 */
function cptui_register_my_taxes_sector() {
	$labels = [
		"name" => __( "Sectors", "acs" ),
		"singular_name" => __( "Sector", "acs" ),
	];
	
	$args = [
		"label" => __( "Sectors", "acs" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'sector', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"rest_base" => "sector",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"show_in_quick_edit" => false,
		"show_in_graphql" => false,
	];
	register_taxonomy( "sector", [ "service" ], $args );
}
add_action( 'init', 'cptui_register_my_taxes_sector' );

/**
 * Rewrite rule 
 */
function acs_service_permalinks( $post_link, $post ) {
    if ( is_object( $post ) && $post->post_type == 'service' ) {
        $terms = wp_get_object_terms( $post->ID, 'sector' );
		if( $terms ) {
			if ( wp_get_post_parent_id( wp_get_post_parent_id( $post->ID ) ) ) {
				// Post with grandparent
				$new_link = str_replace( '%sector%' , $terms[0]->slug , $post_link );
				return str_replace( '%sector%' , get_post_field( 'post_name', wp_get_post_parent_id( $post->ID ) ) , $new_link );
			} else if ( wp_get_post_parent_id( $post->ID ) ) {
				// Post with parent
				$new_link = str_replace( '%sector%' , $terms[0]->slug , $post_link );
				return str_replace( '%sector%' , get_post_field( 'post_name', wp_get_post_parent_id( $post->ID ) ) , $new_link );
			} else {
				return str_replace( '%sector%' , $terms[0]->slug , $post_link );
			}
		}
	}
    return $post_link;
}
add_filter( 'post_type_link', 'acs_service_permalinks', 1, 2 );
