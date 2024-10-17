<?php
/**
 * Structured Data
 * 
 * @package Cata\SEO
 * @since 0.1.0
 */

namespace Cata\SEO;

/**
 * Structured_Data
 */
class Structured_Data {
	/**
	 * Construct
	 */
	public function __construct() {
		add_action( 'after_setup_theme', array( __CLASS__, 'structured_data_image_sizes' ) );
		add_action( 'wp_footer', array( __CLASS__, 'render_structured_data' ) );
	}

	/**
	 * Structured Data Image Sizes
	 *
	 * Adds Custom image sizes needed for Structured Data for Google Rich Search Results
	 */
	public static function structured_data_image_sizes(): void {
		add_image_size( 'structured-1x1', 768, 768, true );
		add_image_size( 'structured-4x3', 768, 576, true );
		add_image_size( 'structured-16x9', 768, 432, true );
	}

	/**
	 * Render Structured Data
	 *
	 * Encodes and Injects Structured Data into Post Head
	 */
	public static function render_structured_data(): void {
		$structured_data = self::get_structured_data();

		if ( empty( $structured_data ) ) {
			return;
		}

		foreach ( $structured_data as $schema ) {
			echo '<script type="application/ld+json">';
			echo wp_json_encode( $schema );
			echo '</script>' . "\n";
		}
	}

	/**
	 * Get Structured Data
	 *
	 * Collects Structured Data
	 *
	 * @return array
	 */
	public static function get_structured_data(): array {
		$structured_data = array();

		if ( is_front_page() ) {
			$structured_data = [ ...$structured_data, ...self::get_organization_data() ];
		}

		if ( is_singular( 'post' ) && has_post_thumbnail() ) {
			$structured_data = [ ...$structured_data, ...self::get_post_data() ];
		}

		if ( is_singular( 'post' ) ) {
			$structured_data = [ ...$structured_data, ...self::get_breadcrumb_data() ];
		}

		return apply_filters( 'cata_structured_data_array', $structured_data );
	}

	/**
	 * Get Organization Data
	 * 
	 * Gets the schema for the organization's structured data
	 * 
	 * @return array
	 */
	public static function get_organization_data(): array {
		$blog_info     = get_bloginfo();
		$icon_url      = get_site_icon_url();
		$home_url      = get_home_url();
		$founding_date = self::get_oldest_post_date();

		$org_data = array(
			array(
				'@context'      => 'http://schema.org',
				'@type'         => 'Organization',
				'name'          => $blog_info,
				'alternateName' => 'The Thought & Expression Company, Inc.',
				'url'           => $home_url,
				'logo'          => $icon_url,
				'parentOrganization' => array(
					'@type' => 'Organization',
					'name'  => 'The Thought & Expression Company, Inc',
					'url'   => 'https://thoughtcatalog.com',
					'logo'  => 'https://thoughtcatalog.com/wp-content/uploads/2020/09/cropped-favicon-512x512-1-1.png'
				),
				'foundingDate' => $founding_date,
				'founders' => array(
					array(
						'@type'    => 'Person',
						'name'     => 'Chris Lavergne',
						'jobTitle' => 'Founder & CEO'
					)
				),
				'address' => array(
					'@type'           => 'PostalAddress',
					'streetAddress'   => 'PO BOX J',
					'addressLocality' => 'Mclean',
					'addressRegion'   => 'VA',
					'postalCode'      => '22101',
					'addressCountry'  => 'USA'
				),
				'contactPoint' => array(
					'@type'       => 'ContactPoint',
					'telephone'   => '+1-845-820-3706',
					'email'       => 'hello@thoughtcatalog.com',
					'contactType' => 'general contact'
				),
			),
		);

		return $org_data;
	}

	/** 
	 * Get Post Data
	 * 
	 * Gets the schema for the post's structured data
	 * 
	 * @return array
	 */
	public static function get_post_data(): array {
		// get publisher info.
		$blog_info     = get_bloginfo();
		$icon_url      = get_site_icon_url();

		// post obj contains the data needed for the headline, dates, and ID.
		$post_object    = get_post();
		$post_headline  = $post_object->post_title;
		$post_published = str_replace( ' ', 'T', $post_object->post_date_gmt );
		$post_modified  = str_replace( ' ', 'T', $post_object->post_modified_gmt );
		$post_id        = $post_object->ID;
		$post_permalink = get_permalink( $post_id );

		// images with hard crops for required sizes.
		$post_image_1x1  = wp_get_attachment_image_url( get_post_thumbnail_id( $post_id ), 'structured-1x1' );
		$post_image_4x3  = wp_get_attachment_image_url( get_post_thumbnail_id( $post_id ), 'structured-4x3' );
		$post_image_16x9 = wp_get_attachment_image_url( get_post_thumbnail_id( $post_id ), 'structured-16x9' );

		// author list
		$author_list = self::get_author_list();

		$post_data = array(
			array(
				'@context'         => 'http://www.schema.org',
				'@type'            => self::get_type( $post_id ),
				'mainEntityOfPage' => array(
					'@type' => 'Webpage',
					'@id'   => $post_permalink,
				),
				'headline'         => $post_headline,
				'image'            => array(
					$post_image_1x1,
					$post_image_4x3,
					$post_image_16x9,
				),
				'datePublished'    => $post_published,
				'dateModified'     => $post_modified,
				'author'           => $author_list,
				'publisher'        => array(
					'@type' => 'Organization',
					'name'  => $blog_info,
					'logo'  => array(
						'@type' => 'ImageObject',
						'url'   => $icon_url,
					),
				),
			),
		);

		return $post_data;
	}

	/**
	 * Get Breadcrumb Data
	 * 
	 * Gets the schema for the post's breadcrumb data
	 * 
	 * @return array
	 */
	public static function get_breadcrumb_data(): array {
		$crumbs = apply_filters( 'cata_structured_data_breadcrumbs', array(), get_the_ID() );
	
		if ( empty( $crumbs ) ) {
			return array();
		}

		$breadcrumb_list = self::get_breadcrumb_list( $crumbs );

		if ( empty( $breadcrumb_list ) ) {
			return array();
		}

		return array(
			array(
				'@context'        => 'https://schema.org',
				'@type'           => 'BreadcrumbList',
				'itemListElement' => $breadcrumb_list,
			),
		);
	}

	/**
	 * Get Oldest Post Date
	 *
	 * Gets the publish date of the oldest post
	 *
	 * @return string
	 */
	public static function get_oldest_post_date(): string {
		$query = new \WP_Query( 
			array(
				'orderby'        => 'post_date',
				'post_type'      => 'post',
				'posts_per_page' => '1',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		);

		if( ! $query->have_posts() ) {
			return '';
		}

		$query->the_post();
		$post_date = get_the_date( 'Y-m-d' );
		wp_reset_postdata();
		
		return $post_date;
	}

	/** 
	 * Get Author List
	 * 
	 * Generates an array of authors for the current post
	 * 
	 * @return array
	 */
	public static function get_author_list(): array {
		// get_coauthors.
		$coauthor_info = get_coauthors();

		// forEach author make an entry in author array.
		$author_list = array();
		foreach ( $coauthor_info as $person ) {

			$person_info = array(
				'@type' => 'Person',
				'name'  => isset( $person->display_name ) ? $person->display_name : get_bloginfo(),
				'url'   => get_author_posts_url( $person->ID, $person->user_nicename ),
			);
			$author_list[] = $person_info;
		}

		return $author_list;
	}

	/**
	 * Get Type
	 *
	 * @param int $post_id
	 * 
	 * @return string
	 */
	private static function get_type( int $post_id ): string {
		return has_category( 'news', $post_id ) ? 'NewsArticle' : 'BlogPosting';
	}

	/**
	 * Get Breadcrumb List
	 * 
	 * @param array $crumbs
	 * 
	 * @return array
	 */
	public static function get_breadcrumb_list( array $crumbs ): array {
		return array_map(
			function( array $crumb, int $key ): array {
				if ( ! array_key_exists( 'url', $crumb ) || ! array_key_exists( 'title', $crumb ) ) {
					return array();
				}

				return array(
					'@type'    => 'ListItem',
					'position' => $key + 1,
					'name' => $crumb['title'],
					'item'  => $crumb['url'],
				);
			},
			$crumbs,
			array_keys( $crumbs )
		);
	}
}
