<?php
/**
 * The main Flagship Site Logo class.
 *
 * Based on the Jetpack site logo feature.
 *
 * @package     FlagshipLibrary
 * @subpackage  HybridCore
 * @copyright   Copyright (c) 2014, Flagship Software, LLC
 * @license     GPL-2.0+
 * @link        http://flagshipwp.com/
 * @since       1.0.0
 */

/**
 * Our Site Logo class for managing a theme-agnostic logo through the Customizer.
 *
 * @package FlagshipLibrary
 */
class Flagship_Site_Logo {

	/**
	 * Stores our current logo settings.
	 */
	public $logo;

	/**
	 * Get our current logo settings stored in options.
	 *
	 * @uses get_option()
	 */
	public function __construct() {
		$this->logo = get_option( 'site_logo', null );
	}

	/**
	 * Return our instance, creating a new one if necessary.
	 *
	 * @return object Flagship_Site_Logo
	 */
	public function run() {
		self::wp_hooks();
	}

	/**
	 * Register our actions and filters.
	 *
	 * @uses Flagship_Site_Logo::head_text_styles()
	 * @uses Flagship_Site_Logo::customize_register()
	 * @uses Flagship_Site_Logo::preview_enqueue()
	 * @uses Flagship_Site_Logo::body_classes()
	 * @uses Flagship_Site_Logo::media_manager_image_sizes()
	 * @uses add_action
	 * @uses add_filter
	 */
	private function wp_hooks() {
		add_action( 'wp_head',                 array( $this, 'head_text_styles' ) );
		add_action( 'customize_register',      array( $this, 'customize_register' ) );
		add_action( 'customize_preview_init',  array( $this, 'preview_enqueue' ) );
		add_action( 'delete_attachment',       array( $this, 'reset_on_attachment_delete' ) );
		add_filter( 'body_class',              array( $this, 'body_classes' ) );
		add_filter( 'image_size_names_choose', array( $this, 'media_manager_image_sizes' ) );
		add_filter( 'display_media_states',    array( $this, 'add_media_state' ) );
	}

	/**
	 * Add our logo uploader to the Customizer.
	 *
	 * @param object $wp_customize Customizer object.
	 * @uses current_theme_supports()
	 * @uses current_theme_supports()
	 * @uses WP_Customize_Manager::add_setting()
	 * @uses WP_Customize_Manager::add_control()
	 * @uses Flagship_Site_Logo::sanitize_checkbox()
	 */
	public function customize_register( $wp_customize ) {

		//Update the Customizer section title for discoverability.
		$wp_customize->get_section( 'title_tagline' )->title = __( 'Site Title, Tagline, and Logo', 'flagship-library' );

		// Add a setting to hide header text if the theme isn't supporting the feature itself
		if ( ! current_theme_supports( 'custom-header' ) ) {
			$wp_customize->add_setting(
				'site_logo_header_text',
				array(
					'default'           => 1,
					'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
					'transport'         => 'postMessage',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Control(
					$wp_customize,
					'site_logo_header_text',
					array(
						'label'    => __( 'Display Header Text', 'flagship-library' ),
						'section'  => 'title_tagline',
						'settings' => 'site_logo_header_text',
						'type'     => 'checkbox',
					)
				)
			);
		}

		// Add the setting for our logo value.
		$wp_customize->add_setting(
			'site_logo',
			array(
				'capability' => 'manage_options',
				'default'    => array(
					'id'     => 0,
					'sizes'  => array(),
					'url'    => false,
				),
				'sanitize_callback' => array( $this, 'sanitize_logo_setting' ),
				'transport'         => 'postMessage',
				'type'              => 'option',
			)
		);

		// Add our image uploader.
		$wp_customize->add_control(
			new Flagship_Site_Logo_Image_Control(
				$wp_customize,
				'site_logo',
				array(
					'label'    => __( 'Logo', 'flagship-library' ),
					'section'  => 'title_tagline',
					'settings' => 'site_logo',
				)
			)
		);
	}

	/**
	 * Enqueue scripts for the Customizer live preview.
	 *
	 * @uses wp_enqueue_script()
	 * @uses plugins_url()
	 * @uses current_theme_supports()
	 * @uses Flagship_Site_Logo::header_text_classes()
	 * @uses wp_localize_script()
	 */
	public function preview_enqueue() {
		$assets_uri = trailingslashit( flagship_library()->get_library_uri() ) . 'assets/';

		wp_enqueue_script(
			'site-logo-preview',
			$assets_uri . 'js/site-logo/preview.js',
			array( 'media-views' ),
			'',
			true
		);
		wp_enqueue_script(
			'site-logo-header-text',
			$assets_uri . 'js/site-logo/header-text.js',
			array( 'media-views' ),
			'',
			true
		);
	}

	/**
	 * Hide header text on front-end if necessary.
	 *
	 * @uses current_theme_supports()
	 * @uses get_theme_mod()
	 * @uses Flagship_Site_Logo::header_text_classes()
	 * @uses esc_html()
	 */
	public function head_text_styles() {
		// Bail if our theme supports custom headers or  header text isn't hidden.
		if ( current_theme_supports( 'custom-header' ) || get_theme_mod( 'site_logo_header_text', 0 ) ) {
			return;
		}

		// hide our header text if display Header Text is unchecked.
		?>
		<!-- Site Logo: hide header text -->
		<style type="text/css">
			.site-title,
			.site-description {
				clip: rect(1px, 1px, 1px, 1px);
				position: absolute;
			}
		</style>
		<?php
	}

	/**
	 * Determine image size to use for the logo.
	 *
	 * @uses get_theme_support()
	 * @return string Size specified in add_theme_support declaration, or 'thumbnail' default
	 */
	public function theme_size() {
		$args        = get_theme_support( 'site-logo' );
		$valid_sizes = get_intermediate_image_sizes();

		// Add 'full' to the list of accepted values.
		$valid_sizes[] = 'full';

		// If the size declared in add_theme_support is valid, use it; otherwise, just go with 'thumbnail'.
		$size = ( isset( $args[0]['size'] ) && in_array( $args[0]['size'], $valid_sizes ) ) ? $args[0]['size'] : 'thumbnail';

		return $size;
	}

	/**
	 * Make custom image sizes available to the media manager.
	 *
	 * @param array $sizes
	 * @uses get_intermediate_image_sizes()
	 * @return array All default and registered custom image sizes.
	 */
	public function media_manager_image_sizes( $sizes ) {
		// Get an array of all registered image sizes.
		$intermediate = get_intermediate_image_sizes();

		// Bail if we don't have any image sizes to work with.
		if ( empty( $intermediate ) ) {
			return;
		}
		foreach ( (array) $intermediate as $key => $size ) {
			// If the size isn't already in the $sizes array, add it.
			if ( ! array_key_exists( $size, $sizes ) ) {
				$sizes[ $size ] = $size;
			}
		}

		return $sizes;
	}

	/**
	 * Add site logos to media states in the Media Manager.
	 *
	 * @return array The current attachment's media states.
	 */
	public function add_media_state( $media_states ) {
		// Only bother testing if we have a site logo set.
		if ( ! $this->has_site_logo() ) {
			return $media_states;
		}
		global $post;

		// If our attachment ID and the site logo ID match, this image is the site logo.
		if ( $post->ID == $this->logo['id'] ) {
			$media_states[] = __( 'Site Logo', 'flagship-library' );
		}
		return $media_states;
	}

	/**
	 * Reset the site logo if the current logo is deleted in the media manager.
	 *
	 * @param int $site_id
	 * @uses Flagship_Site_Logo::remove_site_logo()
	 */
	public function reset_on_attachment_delete( $post_id ) {
		// Do nothing if the logo id doesn't match the post id.
		if ( $this->logo['id'] !== $post_id ) {
			return;
		}
		$this->remove_site_logo();
	}

	/**
	 * Retrieve the site logo URL or ID (URL by default). Pass in the string 'id' for ID.
	 *
	 * @uses get_option()
	 * @uses esc_url_raw()
	 * @uses set_url_scheme()
	 * @return mixed The URL or ID of our site logo, false if not set
	 * @since 1.0
	 */
	function get_site_logo( $show = 'url' ) {
		$logo = $this->logo;

		// Return false if no logo is set
		if ( ! isset( $logo['id'] ) || 0 === absint( $logo['id'] ) ) {
			return false;
		}

		// Return the ID if specified, otherwise return the URL by default
		if ( 'id' == $show ) {
			return $logo['id'];
		}

		return esc_url_raw( set_url_scheme( $logo['url'] ) );
	}

	/**
	 * Determine if a site logo is assigned or not.
	 *
	 * @uses Flagship_Logo::$logo
	 * @return boolean True if there is an active logo, false otherwise
	 */
	public function has_site_logo() {
		return ( isset( $this->logo['id'] ) && 0 !== $this->logo['id'] ) ? true : false;
	}

	/**
	 * Output an <img> tag of the site logo, at the size specified
	 * in the theme's add_theme_support() declaration.
	 *
	 * @uses Flagship_Logo::logo
	 * @uses Flagship_Logo::theme_size()
	 * @uses Flagship_Logo::has_site_logo()
	 * @uses Flagship_Library::is_customize_preview()
	 * @uses esc_url()
	 * @uses home_url()
	 * @uses esc_attr()
	 * @uses wp_get_attachment_image()
	 * @uses apply_filters()
	 * @since 1.0.0
	 */
	function the_site_logo() {
		$logo = $this->logo;
		$size = $this->theme_size();

		// Bail if no logo is set. Leave a placeholder if we're in the Customizer, though (needed for the live preview).
		if ( ! $this->has_site_logo() ) {
			if ( flagship_library()->is_customize_preview() ) {
				printf( '<a href="%1$s" class="site-logo-link" style="display:none;"><img class="site-logo" data-size="%2$s" /></a>',
					esc_url( home_url( '/' ) ),
					esc_attr( $size )
				);
			}
			return;
		}

		// We have a logo. Logo is go.
		$html = sprintf( '<a href="%1$s" class="site-logo-link" rel="home">%2$s</a>',
			esc_url( home_url( '/' ) ),
			wp_get_attachment_image(
				$logo['id'],
				$size,
				false,
				array(
					'class'     => "site-logo attachment-$size",
					'data-size' => $size,
				)
			)
		);

		echo apply_filters( 'the_site_logo', $html, $logo, $size );
	}

	/**
	 * Reset the site logo option to zero (empty).
	 *
	 * @uses update_option()
	 */
	public function remove_site_logo() {
		update_option( 'site_logo',
			array(
				'id'    => (int) 0,
				'sizes' => array(),
				'url'   => '',
			)
		);
	}

	/**
	 * Adds custom classes to the array of body classes.
	 *
	 * @uses Flagship_Site_Logo::has_site_logo()
	 * @return array Array of <body> classes
	 */
	public function body_classes( $classes ) {
		// Add a class if a Site Logo is active
		if ( $this->has_site_logo() ) {
			$classes[] = 'has-site-logo';
		}

		return $classes;
	}

	/**
	 * Sanitize our header text Customizer setting.
	 *
	 * @param $input
	 * @return mixed 1 if checked, empty string if not checked.
	 */
	public function sanitize_checkbox( $input ) {
		return ( 1 == $input ) ? 1 : '';
	}

	/**
	 * Validate and sanitize a new site logo setting.
	 *
	 * @param $input
	 * @return mixed 1 if checked, empty string if not checked.
	 */
	public function sanitize_logo_setting( $input ) {
		$input['id']  = absint( $input['id'] );
		$input['url'] = esc_url_raw( $input['url'] );

		// If the new setting doesn't point to a valid attachment, just reset the whole thing.
		if ( false == wp_get_attachment_image_src( $input['id'] ) ) {
			$input = array(
				'id'    => (int) 0,
				'sizes' => array(),
				'url'   => '',
			);
		}

		return $input;
	}

	/**
	 * Sanitize the string of classes used for header text.
	 * Limit to A-Z,a-z,0-9,(space),(comma),_,-
	 *
	 * @return string Sanitized string of CSS classes.
	 */
	function sanitize_header_text_classes( $classes ) {
		return preg_replace( '/[^A-Za-z0-9\,\ ._-]/', '', $classes );
	}

}
