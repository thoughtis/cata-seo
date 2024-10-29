<?php
/**
 * Robots
 * 
 * @package Cata\SEO
 * @since 0.1.0
 */

namespace Cata\SEO;

/**
 * Robots
 */
class Robots {
	/**
	 * Noindex, Follow
	 *
	 * @var array
	 */
	const NOINDEX_FOLLOW = array( 
		'noindex' => true, 
		'follow'  => true,
	);

	/**
	 * Construct
	 */
	public function __construct() {
		/**
		 * Noindex
		 */
		add_filter( 'wp_robots', array( __CLASS__, 'noindex_attachments' ) );
		add_filter( 'wp_robots', array( __CLASS__, 'noindex_paged_content' ) );
		add_filter( 'wp_robots', array( __CLASS__, 'noindex_get_requests' ) );
		add_filter( 'wp_robots', array( __CLASS__, 'noindex_search' ) );
		add_filter( 'wp_robots', array( __CLASS__, 'noindex_not_found' ) );

		/**
		 * Disallow
		 */
		add_filter( 'robots_txt', array( __CLASS__, 'disallow_feeds' ) );
	}

	/**
	 * Noindex Attachments
	 *
	 * @param array $robots_array
	 * 
	 * @return array
	 */
	public static function noindex_attachments( array $robots_array ): array {
		if ( ! is_attachment() ) {
			return $robots_array;
		}
		return array_merge( $robots_array, self::NOINDEX_FOLLOW );
	}

	/**
	 * Noindex Paged Content
	 *
	 * @param array $robots_array
	 * 
	 * @return array
	 */
	public static function noindex_paged_content( array $robots_array ): array {
		if ( ! is_paged() ) {
			return $robots_array;
		}
		return array_merge( $robots_array, self::NOINDEX_FOLLOW );
	}

	/**
	 * Noindex GET Requests
	 *
	 * @param array $robots_array
	 * 
	 * @return array
	 */
	public static function noindex_get_requests( array $robots_array ): array {
		if ( empty( $_GET ) ) {
			return $robots_array;
		}
		return array_merge( $robots_array, self::NOINDEX_FOLLOW );
	}

	/**
	 * Noindex Search
	 * 
	 * @param array $robots
	 * 
	 * @return array
	 */
	public static function noindex_search( array $robots ): array {
		if ( ! is_search() ) {
			return $robots;
		}
		return array_merge( $robots, self::NOINDEX_FOLLOW );
	}

	/**
	 * Noindex Not Found
	 * 
	 * @param array $robots
	 * 
	 * @return array $robots
	 */
	public static function noindex_not_found( array $robots ): array {
		if ( ! is_404() ) {
			return $robots;
		}
		return array_merge( $robots, self::NOINDEX_FOLLOW );
	}

	/**
	 * Disallow Feeds
	 * 
	 * Disallow RSS feeds in robots.txt
	 *
	 * @param string $robots
	 * 
	 * @return string
	 */
	public static function disallow_feeds( string $robots ): string {
		return $robots . "Disallow: */feed/\n";
	}
}
