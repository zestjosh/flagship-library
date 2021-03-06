<?php
/**
 * Tweaks and Adjustments for the Hybrid Core Framework.
 *
 * @package     FlagshipLibrary
 * @subpackage  HybridCore
 * @copyright   Copyright (c) 2014, Flagship Software, LLC
 * @license     GPL-2.0+
 * @link        https://flagshipwp.com/
 * @since       1.0.0
 */

// Remove unwanted default Hybrid head elements.
remove_action( 'wp_head', 'hybrid_meta_template', 1 );
remove_action( 'wp_head', 'hybrid_doctitle',      0 );

add_action( 'wp_head', 'flagship_doctitle', 0 );
/**
 * Add the title to the header with a more SEO-friendly seperator.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function flagship_doctitle() {
	?>
	<title><?php wp_title( '-' ); ?></title>
	<?php
}

add_filter( 'hybrid_site_title', 'flagship_seo_site_title' );
/**
 * Returns the linked site title wrapped in a `<p>` tag unless on the home page
 * or the main blog page where no other H1 exists.
 *
 * @since  1.0.0
 * @access public
 * @param  string $title
 * @return string
 */
function flagship_seo_site_title( $title ) {
	if ( is_front_page() || is_home() ) {
		return $title;
	}
	return str_replace( array( '<h1', '</h1' ), array( '<p', '</p' ), $title );
}

add_filter( 'hybrid_site_description', 'flagship_seo_site_description' );
/**
 * Returns the site description wrapped in a `<p>` tag.
 *
 * @since  1.0.0
 * @access public
 * @param  string $desc
 * @return string
 */
function flagship_seo_site_description( $desc ) {
	return str_replace( array( '<h2', '</h2' ), array( '<p', '</p' ), $desc );
}

add_filter( 'excerpt_more', 'flagship_seo_excerpt_more' );
/**
 * Filter the default Hybrid more link to add a rel="nofollow" attribute.
 *
 * @since  1.0.0
 * @access public
 * @param  string $text
 * @return string
 */
function flagship_seo_excerpt_more( $text ) {
	return str_replace( 'class="more-link"', 'rel="nofollow" class="more-link"', $text );
}
