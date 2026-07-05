<?php
/**
 * Portrait Share Image
 *
 * @package Cata\SEO\Jetpack_Settings
 */

namespace Cata\SEO\Jetpack_Settings;

use WP_Post;

/**
 * Portrait Share Image
 *
 * Jetpack builds singular og:image URLs with a center crop
 * (`?w=…&h=…&crop=1`). Center-cropping a portrait featured image to the
 * 2:1 share ratio keeps the middle band of the photo — torsos instead of
 * faces. This filter re-crops portrait and square sources to a band that
 * starts near the top of the image, where editorial photos keep faces.
 */
class Portrait_Share_Image {
	/**
	 * Share image aspect ratio (width / height) used by Jetpack's output.
	 *
	 * @var int
	 */
	const TARGET_RATIO = 2;

	/**
	 * Maximum output width.
	 *
	 * @var int
	 */
	const MAX_WIDTH = 1536;

	/**
	 * Construct
	 */
	public function __construct() {
		add_filter( 'jetpack_open_graph_tags', array( __CLASS__, 'crop_portrait_share_image' ), 30 );
	}

	/**
	 * Crop Portrait Share Image
	 *
	 * @param array $tags - provided open graph tags.
	 *
	 * @return array $tags - og:image re-cropped to a face-safe band for portrait sources.
	 */
	public static function crop_portrait_share_image( array $tags ): array {

		// Bail unless a singular view provided an image.
		if ( ! is_singular() || empty( $tags['og:image'] ) ) {
			return $tags;
		}

		$post = get_queried_object();

		if ( ! $post instanceof WP_Post ) {
			return $tags;
		}

		$thumbnail_id = (int) get_post_thumbnail_id( $post );

		if ( 0 === $thumbnail_id ) {
			return $tags;
		}

		// Bail if Jetpack chose an image other than the featured image.
		$thumbnail_url = (string) wp_get_attachment_url( $thumbnail_id );

		if ( wp_basename( strtok( (string) $tags['og:image'], '?' ) ) !== wp_basename( $thumbnail_url ) ) {
			return $tags;
		}

		$metadata = wp_get_attachment_metadata( $thumbnail_id );

		if ( empty( $metadata['width'] ) || empty( $metadata['height'] ) ) {
			return $tags;
		}

		$width  = (int) $metadata['width'];
		$height = (int) $metadata['height'];

		// Landscape sources keep >= 75% of their height under a center
		// crop — only portrait and square sources need rescuing.
		if ( $height < $width ) {
			return $tags;
		}

		// Height of the 2:1 crop window as a percentage of the image.
		$band = round( ( $width / ( $height * self::TARGET_RATIO ) ) * 100, 2 );

		/**
		 * Filters how far down the image the share crop starts, as a
		 * percentage of image height. Editorial photos keep faces near —
		 * but rarely at — the very top of the frame.
		 *
		 * @param float $offset Percentage of image height above the crop window.
		 */
		$offset = (float) apply_filters( 'cata_seo_portrait_share_image_offset', 12 );
		$offset = max( 0, min( $offset, 100 - $band ) );

		$output_width  = min( $width, self::MAX_WIDTH );
		$output_height = (int) round( $output_width / self::TARGET_RATIO );

		$tags['og:image']        = add_query_arg(
			array(
				'crop' => sprintf( '0,%s,100,%s', $offset, $band ),
				'w'    => $output_width,
			),
			$thumbnail_url
		);
		$tags['og:image:width']  = $output_width;
		$tags['og:image:height'] = $output_height;

		if ( ! empty( $tags['twitter:image'] ) ) {
			$tags['twitter:image'] = $tags['og:image'];
		}

		return $tags;
	}
}
