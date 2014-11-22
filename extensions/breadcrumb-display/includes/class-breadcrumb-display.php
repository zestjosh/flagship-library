<?php
/**
 * Options for displaying breadcrumbs for use in the WordPrss customizer.
 *
 * @package     FlagshipLibrary
 * @subpackage  HybridCore
 * @copyright   Copyright (c) 2014, Flagship Software, LLC
 * @license     GPL-2.0+
 * @link        http://flagshipwp.com/
 * @since       1.1.0
 */

/**
 * Our Breadcrumb display class for managing breadcrumbs through the Customizer.
 *
 * @package FlagshipLibrary
 */
class Flagship_Breadcrumb_Display {

	/**
	 * Get our class up and running!
	 *
	 * @since  1.1.0
	 * @access public
	 * @uses   Flagship_Breadcrumb_Display::$wp_hooks
	 * @return void
	 */
	public function run() {
		self::wp_hooks();
	}

	/**
	 * Register our actions and filters.
	 *
	 * @since  1.1.0
	 * @access public
	 * @uses   Flagship_Breadcrumb_Display::register_breadcrumb_settings()
	 * @uses   add_action
	 * @return void
	 */
	private function wp_hooks() {
		add_action( 'customize_register', array( $this, 'register_breadcrumb_settings' ) );
	}

	/**
	 * Register a customizer section and options for our breadcrumbs.
	 *
	 * @since  1.1.0
	 * @access public
	 * @param  object  $wp_customize
	 * @return void
	 */
	public function register_breadcrumb_settings( $wp_customize ) {

		$capability = 'edit_theme_options';
		$section    = 'flagship_breadcrumbs';

		$wp_customize->add_section(
			$section,
			array(
				'title'       => __( 'Breadcrumbs', 'flagship-library' ),
				'description' => __( 'Choose where you would like breadcrumbs to display.', 'flagship-library' ),
				'priority'    => 110,
			)
		);

		$counter = 20;

		foreach ( $this->get_breadcrumb_options() as $breadcrumb => $setting ) {

			$wp_customize->add_setting(
				$breadcrumb,
				array(
					'default'           => $setting['default'],
					'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
				)
			);

			$wp_customize->add_control(
				$breadcrumb,
				array(
					'label'    => $setting['label'],
					'section'  => $section,
					'type'     => 'checkbox',
					'priority' => $counter++,
				)
			);
		}
	}

	/**
	 * An array of breadcrumb locations.
	 *
	 * @since  1.1.0
	 * @access public
	 * @return array $breadcrumbs
	 */
	public function get_breadcrumb_options() {
		$breadcrumbs = array(
			'flagship_breadcrumb_single' => array(
				'default'  => 0,
				'label'    => __( 'Single Entries', 'flagship-library' ),
			),
			'flagship_breadcrumb_pages' => array(
				'default'  => 0,
				'label'    => __( 'Pages', 'flagship-library' ),
			),
			'flagship_breadcrumb_blog_page' => array(
				'default'  => 0,
				'label'    => __( 'Blog Page', 'flagship-library' ),
			),
			'flagship_breadcrumb_archive' => array(
				'default'  => 0,
				'label'    => __( 'Archives', 'flagship-library' ),
			),
			'flagship_breadcrumb_404' => array(
				'default'  => 0,
				'label'    => __( '404 Page', 'flagship-library' ),
			),
			'flagship_breadcrumb_attachment' => array(
				'default'  => 0,
				'label'    => __( 'Attachment/Media Pages', 'flagship-library' ),
			),
		);
		return apply_filters( 'flagship_get_breadcrumb_options', $breadcrumbs );
	}

	/**
	 * Sanitize our breadcrumb checkbox.
	 *
	 * @since  1.1.0
	 * @access public
	 * @param  $input
	 * @return int
	 */
	public function sanitize_checkbox( $input ) {
		return ( 1 === absint( $input ) ) ? 1 : 0;
	}

}
