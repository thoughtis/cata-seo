<?php
/**
 * Share Image
 * 
 * @package Cata\SEO\Jetpack_Settings
 */

namespace Cata\SEO\Jetpack_Settings;

use WP_Customize_Manager;
use WP_Customize_Media_Control;

/**
 * Share Image
 */
class Share_Image {
	/**
	 * Construct
	 */
	public function __construct() {
		/**
		 * Add share image setting to customizer
		 */
		add_action( 'customize_register', array( __CLASS__, 'add_share_image' ), 10 );
		add_action( 'customize_register', array( __CLASS__, 'add_customizer_section' ), 10 );
		add_action( 'customize_register', array( __CLASS__, 'add_share_image_setting' ), 20 );

		/**
		 * Use share image
		 */
		add_filter( 'jetpack_open_graph_tags', array( __CLASS__, 'add_homepage_share_image' ) );
		add_filter( 'jetpack_twitter_cards_image_default', array( __CLASS__, 'get_homepage_share_image_url' ) );
	}

	/**
	 * Add Share Image Panel
	 *
	 * @param WP_Customize_Manager $wp_customize - instance of WP_Customize_Manager.
	 */
	public static function add_share_image( WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_panel(
			'cata_seo_jetpack_settings_share_image_panel',
			array(
				'title'       => __( 'Share Image', 'cata' ),
				'description' => __( 'Edit Homepage Share Image', 'cata' ),
				'capability'  => 'edit_theme_options',
				'priority'    => 10,
			) 
		);
	}

	/**
	 * Add Customizer Section
	 * 
	 * @param WP_Customize_Manager $wp_customize - instance of WP_Customize_Manager.
	 */
	public static function add_customizer_section( WP_Customize_Manager $wp_customize ): void {
		/**
		 * Add a Customizer Section for share image theme.
		 */
		$section_options = array(
			'title'       => __( 'Homepage' ),
			'description' => __( 'Settings used by the Cata SEO plugin.' ),
			'capability'  => 'edit_theme_options',
			'panel'       => 'cata_seo_jetpack_settings_share_image_panel',
		);
		$wp_customize->add_section( 'cata_seo_jetpack_settings_share_image_section', $section_options );
	}

	/**
	 * Add Share Image Setting
	 * 
	 * @param WP_Customize_Manager $wp_customize - instance of WP_Customize_Manager.
	 */
	public static function add_share_image_setting( WP_Customize_Manager $wp_customize ): void {
		/**
		 * Add Setting for Homepage Share Image
		 */
		$setting_name    = 'cata_seo_jetpack_settings_share_image_setting';
		$setting_options = array(
			'type'    => 'theme_mod',
			'default' => 0,
		);
		$wp_customize->add_setting( $setting_name, $setting_options );

		/**
		 * Add a Media Control which provides UI for the setting,
		 * and connects it to the theme's Section in the Customizer.
		 */
		$control_options  = array(
			'description' => 'Upload an image with a 2:1 aspect ratio to be used as the preview image anywhere the homepage is shared.',
			'label'       => __( 'Homepage Share Image', 'cata' ),
			'mime_type'   => 'image',
			'section'     => 'cata_seo_jetpack_settings_share_image_section',
		);
		$control_instance = new WP_Customize_Media_Control( $wp_customize, $setting_name, $control_options );
		$wp_customize->add_control( $control_instance );
	}

	/**
	 * Add Homepage Share Image
	 * 
	 * @param array $tags - provided open graph tags.
	 * 
	 * @return array $tags - maybe updated to ad image, width and height for homepage.
	 */
	public static function add_homepage_share_image( array $tags ): array {

		// Bail if we're not on the homepage.
		if ( ! is_home() ) {
			return $tags;
		}

		// Bail if the homepage is a Page where you can set the feature image.
		if ( 'posts' !== get_option( 'show_on_front' ) ) {
			return $tags;
		}
		
		$image_info = self::get_homepage_share_image_info();

		if ( empty( $image_info ) ) {
			return $tags;
		}

		$og_image = array(
			'og:image'        => $image_info[0],
			'og:image:width'  => $image_info[1],
			'og:image:height' => $image_info[2],
		);

		return array_merge( $tags, $og_image );
	}

	/**
	 * Get Homepae Share Image Info
	 * 
	 * @see /includes/customizer/class-customizer.php
	 * 
	 * @return array $image_info - result of wp_get_attachment_image_src or empty array.
	 */
	public static function get_homepage_share_image_info(): array {

		$image_id = absint( get_theme_mod( 'cata_seo_jetpack_settings_share_image_setting', 0 ) );

		if ( 0 === $image_id ) {
			return array();
		}

		$image_info = wp_get_attachment_image_src( $image_id, '2048x2048' );

		if ( ! is_array( $image_info ) || empty( $image_info ) ) {
			return array();
		}

		return $image_info;

	}

	/**
	 * Get Homepage Share Image URL
	 * 
	 * @return string - URL of image or empty string.
	 */
	public static function get_homepage_share_image_url(): string {
		$image = self::get_homepage_share_image_info();
		if ( empty( $image ) ) {
			return '';
		}
		return current( $image );
	}

}
