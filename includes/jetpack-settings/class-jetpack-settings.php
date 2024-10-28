<?php
/**
 * Jetpack Settings
 * 
 * @package Cata\SEO
 * @since 0.1.0
 */

namespace Cata\SEO;

/**
 * Jetpack Settings
 */
class Jetpack_Settings {
	/**
	 * Construct
	 */
	public function __construct() {
		/**
		 * Open Graph
		 */
		add_filter( 'jetpack_enable_open_graph', '__return_true' );

		/**
		 * Meta
		 */
		add_filter( 'jetpack_seo_meta_tags', array( __CLASS__, 'truncate_meta_description' ), 10, 1 );

		/**
		 * Sitemaps
		 */
		add_filter( 'jetpack_sitemap_image_skip_post', array( __CLASS__, 'jetpack_sitemap_image_skip_orphans' ), 10, 2 );
		add_filter( 'jetpack_sitemap_image_skip_post', array( __CLASS__, 'jetpack_sitemap_image_skip_reusable_blocks' ), 12, 2 );
	}

	/**
	 * Truncate Meta Description
	 * 
	 * @link https://github.com/Automattic/jetpack/blob/8.3/modules/seo-tools/jetpack-seo.php#L197
	 * @link https://stackoverflow.com/questions/79960/how-to-truncate-a-string-in-php-to-the-word-closest-to-a-certain-number-of-chara
	 * 
	 * @param array $meta - the meta tags Jetpack would like to output.
	 * 
	 * @return array $meta - possibly updated $meta with truncated description.
	 */
	public static function truncate_meta_description( array $meta ): array {
		if ( ! isset( $meta['description'] ) || ! is_string( $meta['description'] ) ) {
			return $meta;
		}

		if ( 150 >= strlen( $meta['description'] ) ) {
			return $meta;
		}

		// Wrap to new lines at 150 characters.
		$wrapped  = wordwrap( $meta['description'], 150, "\n" );
		$position = strpos( $wrapped, "\n" );

		if ( false === $position ) {
			return $meta;
		}

		// Find first line.
		$proposed = substr( $meta['description'], 0, $position );

		if ( empty( $proposed ) ) {
			return $meta;
		}

		return array_merge(
			$meta,
			array(
				'description' => $proposed,
			)
		);
	}

	/**
	 * Jetpack Sitemap Image Skip Orphans
	 * 
	 * We don't want anything in the image sitemap that doesn't use a real post for its Page URL.
	 * Attachment permalink URLs are not indexed.
	 * 
	 * @param bool             $skip - whether to skip. defaults to false.
	 * @param stdClass|WP_Post $post - post we're checking on.
	 * 
	 * @return bool - skip any post without a parent.
	 */
	public static function jetpack_sitemap_image_skip_orphans( bool $skip, $post ): bool {
		if ( true === $skip ) {
			return $skip;
		}
		if ( ! is_object( $post ) || ! property_exists( $post, 'post_parent' ) ) {
			return $skip;
		}
		return ( 0 === absint( $post->post_parent ) );
	}

	/**
	 * Skip Resuable Block Images
	 *
	 * @param boolean          $skip Whether to skip.
	 * @param stdClass|WP_Post $post Attachment post we're checking.
	 * 
	 * @return boolean skip any reusbale block posts.
	 */
	public static function jetpack_sitemap_image_skip_reusable_blocks( bool $skip, $post ): bool {
		if ( true === $skip ) {
			return $skip;
		}

		if ( ! is_object( $post ) || ! property_exists( $post, 'post_parent' ) ) {
			return $skip;
		}

		if ( 'wp_block' !== get_post_type( $post->post_parent ) ) {
			return $skip;
		}

		return true;
	}
}
