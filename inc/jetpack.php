<?php
/**
 * Jetpack Compatibility File
 * See: http://jetpack.me/
 *
 * @package Meteor
 */

/**
 * Add theme support for Jetpack Features.
 */
function meteor_jetpack_setup() {
	/**
	 * Add support for Infinite Scroll
	 */
	add_theme_support( 'infinite-scroll', array(
		'container'      => 'post-wrap',
		'footer'         => false,
		'footer_widgets' => array( 'footer-1', 'footer-2', 'footer-3' ),
		'render'         => 'meteor_render_infinite_posts',
		'wrapper'        => 'new-infinite-posts',
	) );

	/**
	 * Add support for Responsive Videos
	 */
	add_theme_support( 'jetpack-responsive-videos' );

	/**
	 * Enable Jetpack Portfolio support
	 */
	add_theme_support( 'jetpack-portfolio' );
}
add_action( 'after_setup_theme', 'meteor_jetpack_setup' );


/**
 * Adjust content width for tiled gallery
 */
function meteor_custom_tiled_gallery_width() {
    return '1600';
}
add_filter( 'tiled_gallery_content_width', 'meteor_custom_tiled_gallery_width' );


/**
 * Remove Related Posts CSS
 */
function meteor_rp_css() {
	wp_deregister_style( 'jetpack_related-posts' );
}
add_action( 'wp_print_styles', 'meteor_rp_css' );
add_filter( 'jetpack_implode_frontend_css', '__return_false' );


/**
 * Render infinite posts by using template parts
 */
function meteor_render_infinite_posts() {
	while ( have_posts() ) {
		the_post();

		if ( is_search() ) {
			get_template_part( 'template-parts/content-search' );
		} else {
			get_template_part( 'template-parts/content' );
		}
	}
}


/**
 * Changes the text of the "Older posts" button in infinite scroll
 */
function meteor_infinite_scroll_button_text( $js_settings ) {
	$js_settings['text'] = esc_html__( 'Load more', 'meteor' );
	return $js_settings;
}
add_filter( 'infinite_scroll_js_settings', 'meteor_infinite_scroll_button_text' );


/**
 * Move Related Posts
 */
function meteor_remove_rp() {
    if ( class_exists( 'Jetpack_RelatedPosts' ) ) {
        $jprp = Jetpack_RelatedPosts::init();
        $callback = array( $jprp, 'filter_add_target_to_dom' );
        remove_filter( 'post_flair', $callback, 40 );
        remove_filter( 'the_content', $callback, 40 );
    }
}
add_filter( 'wp', 'meteor_remove_rp', 20 );


/**
 * Remove flair from excerpts and content
 */
function meteor_remove_flair() {
	// Remove Poll
	remove_filter( 'the_content', 'polldaddy_show_rating' );
	remove_filter( 'the_excerpt', 'polldaddy_show_rating' );
	// Remove sharing
	remove_filter( 'the_content', 'sharing_display', 19 );
	remove_filter( 'the_excerpt', 'sharing_display', 19 );
}
add_action( 'loop_start', 'meteor_remove_flair' );


/**
 * Remove auto output of Sharing and Likes
 */
function meteor_remove_sharing() {
	if ( function_exists( 'sharing_display' ) ) {
		remove_filter( 'the_content', 'sharing_display', 19 );
		remove_filter( 'the_excerpt', 'sharing_display', 19 );
	}

	if ( class_exists( 'Jetpack_Likes' ) ) {
		remove_filter( 'the_content', array( Jetpack_Likes::init(), 'post_likes' ), 30, 1 );
		remove_filter( 'the_excerpt', array( Jetpack_Likes::init(), 'post_likes' ), 30, 1 );
	}
}

/**
 * Add Gallery and Video support to Portfolio
 */
function meteor_add_portfolio_format() {
    add_post_type_support( 'jetpack-portfolio', 'post-formats' );
}
add_action( 'init', 'meteor_add_portfolio_format', 100 );


/**
 * Change the number of portfolio items
 */
function meteor_archive_portfolio_count() {
	$archive_style = get_theme_mod( 'meteor_portfolio_archive_style', 'grid' );

    if ( $archive_style == 'grid' ) {
        $posts_per_page = get_theme_mod( 'meteor_portfolio_grid_count', '9' );
    } else if ( $archive_style == 'masonry' ) {
        $posts_per_page = get_theme_mod( 'meteor_portfolio_masonry_count', '10' );
    } else if ( $archive_style == 'blocks' ) {
        $posts_per_page = get_theme_mod( 'meteor_portfolio_block_count', '6' );
    } else {
		$posts_per_page = '9';
	}

	update_option( 'jetpack_portfolio_posts_per_page', $posts_per_page );
}
add_action( 'after_setup_theme', 'meteor_archive_portfolio_count' );
