<?php
/**
 * Varya Theme: Custom Colors Class
 *
 * @package WordPress
 * @subpackage Varya
 * @since 1.0.0
 */

/**
 * This class is in charge of color customization via the Customizer.
 *
 * Each variable that needs to be updated is defined in the $varya_custom_color_variables array below.
 * A customizer setting is created for each color, and custom CSS is enqueued in the front and back end.
 *
 * @since 1.0.0
 */
class Varya_Custom_Colors {

private $varya_custom_color_variables = array();

	function __construct() {

		/**
		 * Define color variables
		 */
		$this->$varya_custom_color_variables = array(
			array( '--global--color-primary', '#000000', 'Primary Color' ),
			array( '--global--color-secondary', '#A36265', 'Secondary Color' ),
			array( '--global--color-foreground', '#333333', 'Foreground Color' ),
			array( '--global--color-background-light', '#FAFBF6', 'Background Light Color' ),
			array( '--global--color-background', '#FFFFFF', 'Background Color' ),
			array( '--global--color-border', '#EFEFEF', 'Borders Color' )
		);

		/**
		 * Register Customizer actions
		 */
		add_action( 'customize_register', array( $this, 'varya_customize_custom_colors_register' ) );

		/**
		 * Enqueue color variables for customizer & frontend.
		 */
		add_action( 'wp_enqueue_scripts', array( $this, 'varya_custom_color_variables' ) );

		/**
		 * Enqueue color variables for editor.
		 */
		add_action( 'enqueue_block_editor_assets', array( $this, 'varya_editor_custom_color_variables' ) );
	}

	/**
	 * Add Theme Options for the Customizer.
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	function varya_customize_custom_colors_register( $wp_customize ) {

		/**
		 * Create color options panel.
		 */
		$wp_customize->add_section( 'varya_options', array(
			'capability' => 'edit_theme_options',
			'title'      => esc_html__( 'Colors', 'varya' ),
		) );

		/**
		 * Create toggle between default and custom colors.
		 */
		$wp_customize->add_setting(
			'custom_colors_active',
			array(
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => array( $this, 'sanitize_select' ),
				'transport'         => 'refresh',
				'default'           => 'default',
			)
		);

		$wp_customize->add_control(
			'custom_colors_active',
			array(
				'type'    => 'radio',
				'section' => 'varya_options',
				'label'   => __( 'Colors', 'varya' ),
				'choices' => array(
					'default' => __( 'Theme Default', 'varya' ),
					'custom'  => __( 'Custom', 'varya' ),
				),
			)
		);

		/**
		 * Create customizer color controls.
		 */
		foreach ( $this->$varya_custom_color_variables as $variable ) {
			$wp_customize->add_setting(
				"varya_$variable[0]",
				array(
					'default'	=> esc_html( $variable[1] )
				)
			);
			$wp_customize->add_control( new WP_Customize_Color_Control(
				$wp_customize,
				"varya_$variable[0]",
				array(
					'section'   => 'varya_options',
					'label'     => __( $variable[2], 'varya' ),
					'active_callback' => function() use ( $wp_customize ) {
						return ( 'custom' === $wp_customize->get_setting( 'custom_colors_active' )->value() );
					},
				)
			) );
		}
	}

	/**
	 * Generate color variables for customizer & frontend.
	 */
	function varya_generate_custom_color_variables( $context = null ) {

		if ( $context == 'editor' ) {
			$theme_css = ':root .editor-styles-wrapper {';
		} else {
			$theme_css = ':root {';
		}

		foreach ( $this->$varya_custom_color_variables as $variable ) {
			if ( '' !== get_theme_mod( "varya_$variable" ) ) {
				$theme_css .= $variable[0] . ":" . get_theme_mod( "varya_$variable[0]" ) . ";";
			}
		}

		$theme_css .= "}";
		return $theme_css;
	}

	/**
	 * Customizer & frontend custom color variables.
	 */
	function varya_custom_color_variables() {
		if ( 'default' !== get_theme_mod( 'custom_colors_active' ) ) {
			wp_add_inline_style( 'varya-style', $this->varya_generate_custom_color_variables() );
		}
	}

	/**
	 * Editor custom color variables.
	 */
	function varya_editor_custom_color_variables() {
		wp_enqueue_style( 'varya-editor-variables', get_template_directory_uri() . '/assets/css/variables-editor.css', array(), wp_get_theme()->get( 'Version' ) );
		if ( 'default' !== get_theme_mod( 'custom_colors_active' ) ) {
			wp_add_inline_style( 'varya-editor-variables', $this->varya_generate_custom_color_variables( 'editor' ) );
		}
	}

	/**
	 * Sanitize select.
	 *
	 * @param string $input The input from the setting.
	 * @param object $setting The selected setting.
	 *
	 * @return string $input|$setting->default The input from the setting or the default setting.
	 */
	public static function sanitize_select( $input, $setting ) {
		$input   = sanitize_key( $input );
		$choices = $setting->manager->get_control( $setting->id )->choices;
		return ( array_key_exists( $input, $choices ) ? $input : $setting->default );
	}

}

new Varya_Custom_Colors;
