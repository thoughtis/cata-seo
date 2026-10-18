<?php
/**
 * Sitemaps
 *
 * @package Cata\SEO
 * @since 0.1.0
 */

namespace Cata\SEO\Jetpack_Settings;

use WP_Post;

/**
 * Sitemaps
 *
 * Keeps Jetpack's sitemaps current and clean. Jetpack rebuilds sitemap.xml on
 * a 12-hour cron, so a new story could sit outside it for half a day; the news
 * sitemap double-escaped titles and carried stories past the two-day news window.
 */
class Sitemaps {

	/**
	 * Oldest story the news sitemap lists, in seconds since publication.
	 */
	public const NEWS_WINDOW = 2 * DAY_IN_SECONDS;

	/**
	 * Delay before the rebuild a publish queues, so its terms and meta are saved.
	 */
	public const REBUILD_DELAY = 5 * MINUTE_IN_SECONDS;

	/**
	 * Construct
	 */
	public function __construct() {
		add_filter( 'jetpack_sitemap_news_sitemap_item', array( __CLASS__, 'news_sitemap_item' ) );
		add_filter( 'jetpack_sitemap_news_skip_post', array( __CLASS__, 'news_sitemap_skip_post' ), 20, 2 );
		add_action( 'transition_post_status', array( __CLASS__, 'schedule_sitemap_rebuild' ), 10, 3 );
	}

	/**
	 * News Sitemap Item
	 *
	 * Jetpack fills news:title through `the_title_rss`, which HTML-escapes the
	 * title, and its XML writer then escapes it again: "Carpenter's" reached
	 * Google as "Carpenter&amp;#039;s". Every Jetpack sitemap buffer escapes
	 * text itself, so it gets the plain title.
	 *
	 * @param array $item News sitemap item.
	 * @return array
	 */
	public static function news_sitemap_item( $item ): array {
		$item = (array) $item;

		if ( ! isset( $item['url']['news:news']['news:title'] ) ) {
			return $item;
		}

		/**
		 * Whether news sitemap titles are decoded before Jetpack's XML writer escapes them.
		 *
		 * @param bool $enabled Whether to decode news:title.
		 */
		if ( ! apply_filters( 'cata_seo_decode_news_titles', true ) ) {
			return $item;
		}

		$item['url']['news:news']['news:title'] = html_entity_decode( (string) $item['url']['news:news']['news:title'], ENT_QUOTES | ENT_HTML5, 'UTF-8' );

		return $item;
	}

	/**
	 * News Sitemap Skip Post
	 *
	 * Jetpack's news query starts at midnight two days ago, so stories up to
	 * three days old stayed listed. Google News reads the last two days.
	 *
	 * @param bool   $skip Whether Jetpack already skips the post.
	 * @param object $post Post row from Jetpack's query.
	 * @return bool
	 */
	public static function news_sitemap_skip_post( $skip, $post ): bool {
		if ( $skip || ! is_object( $post ) || empty( $post->post_date_gmt ) ) {
			return (bool) $skip;
		}

		/**
		 * Oldest story the news sitemap lists, in seconds. Zero keeps Jetpack's own window.
		 *
		 * @param int $window Seconds since publication.
		 */
		$window = (int) apply_filters( 'cata_seo_news_sitemap_window', self::NEWS_WINDOW );

		if ( $window <= 0 ) {
			return false;
		}

		$published = strtotime( $post->post_date_gmt . ' UTC' );

		return false !== $published && $published < time() - $window;
	}

	/**
	 * Schedule Sitemap Rebuild
	 *
	 * Queues one run of Jetpack's own sitemap job shortly after a story goes
	 * live. A finished Jetpack build starts over on its next run, so that run
	 * rebuilds every file. WordPress drops a duplicate event due within ten
	 * minutes, so a burst of publishes queues one rebuild, and the 12-hour
	 * schedule carries on as before.
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Previous post status.
	 * @param WP_Post $post       Post.
	 */
	public static function schedule_sitemap_rebuild( $new_status, $old_status, $post ): void {
		if ( 'publish' !== $new_status || 'publish' === $old_status || ! $post instanceof WP_Post || 'post' !== $post->post_type ) {
			return;
		}

		// Only when Jetpack's sitemaps are running here.
		if ( ! has_action( 'jp_sitemap_cron_hook' ) ) {
			return;
		}

		/**
		 * Whether publishing a post queues a Jetpack sitemap rebuild.
		 *
		 * @param bool    $enabled Whether to queue the rebuild.
		 * @param WP_Post $post    The post that went live.
		 */
		if ( ! apply_filters( 'cata_seo_rebuild_sitemaps_after_publish', true, $post ) ) {
			return;
		}

		wp_schedule_single_event( time() + self::REBUILD_DELAY, 'jp_sitemap_cron_hook' );
	}
}
