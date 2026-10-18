<?php
/**
 * Description
 *
 * @package Cata\SEO
 * @since 0.1.0
 */

namespace Cata\SEO\Jetpack_Settings;

use WP_Post;

/**
 * Description
 *
 * Article meta, Open Graph and Twitter descriptions end on a whole sentence
 * instead of a word cut mid-thought.
 */
class Description {

	/**
	 * Construct
	 *
	 * Runs after the 150-character word cut in Jetpack_Settings and after the
	 * Open Graph filters cata-social adds at priority 20.
	 */
	public function __construct() {
		add_filter( 'jetpack_seo_meta_tags', array( __CLASS__, 'meta_description' ), 20 );
		add_filter( 'jetpack_open_graph_tags', array( __CLASS__, 'social_description' ), 40 );
	}

	/**
	 * Replace Jetpack's trimmed SEO description with complete sentences.
	 *
	 * @param array $meta Jetpack SEO tags, escaped by Jetpack at output.
	 * @return array
	 */
	public static function meta_description( array $meta ): array {
		$description = self::article_description();

		if ( '' !== $description ) {
			$meta['description'] = $description;
		}

		return $meta;
	}

	/**
	 * Use the same plain text for both social descriptions.
	 *
	 * @param array $tags Jetpack Open Graph tags, escaped by Jetpack at output.
	 * @return array
	 */
	public static function social_description( array $tags ): array {
		$description = self::article_description();

		if ( '' !== $description ) {
			$tags['og:description']      = $description;
			$tags['twitter:description'] = $description;
		}

		return $tags;
	}

	/**
	 * Read Jetpack's untrimmed source so its custom copy and paywall rules win.
	 *
	 * @return string
	 */
	private static function article_description(): string {
		if ( ! self::is_article() ) {
			return '';
		}

		$post = get_queried_object();

		/**
		 * Whether an article's descriptions end on a whole sentence.
		 *
		 * @param bool $enabled Whether to replace Jetpack's descriptions.
		 * @param int  $post_id The current post ID.
		 */
		if ( ! apply_filters( 'cata_seo_sentence_descriptions', true, $post->ID ) ) {
			return '';
		}

		$source = class_exists( '\Jetpack_SEO_Posts' )
			? \Jetpack_SEO_Posts::get_post_description( $post )
			: ( '' !== trim( $post->post_excerpt ) ? $post->post_excerpt : $post->post_content );

		if ( has_blocks( $source ) ) {
			$source = excerpt_remove_blocks( $source );
			// Block rendering need not leave whitespace between adjacent text blocks.
			$source = preg_replace( '/<\/(?:p|h[1-6]|li|pre|blockquote|td|th|tr)>/i', '$0 ', $source );
		}

		$source = html_entity_decode( wp_strip_all_tags( strip_shortcodes( (string) $source ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$source = trim( (string) preg_replace( '/\s+/u', ' ', $source ) );

		return self::sentence_description( $source );
	}

	/**
	 * Keep whole sentences within 160 characters, extending short results to 300.
	 * A complete short source is preserved; truncated fragments retain an ellipsis.
	 *
	 * @param string $source Normalized, plain-text source.
	 * @return string
	 */
	public static function sentence_description( string $source ): string {
		if ( '' === $source ) {
			return '';
		}

		preg_match_all( '/[.!?]["”’\'\)\]]*(?=\s+(?:\p{Lu}|\d|["“‘\'])|$)/u', $source, $ends, PREG_OFFSET_CAPTURE );
		$description     = '';
		$extended_budget = false;

		foreach ( $ends[0] as $end ) {
			$candidate = substr( $source, 0, $end[1] + strlen( $end[0] ) );
			$unquoted  = (string) preg_replace( '/["”’\'\)\]]+$/u', '', $candidate );

			if ( preg_match( '/(?:\.\.\.|…|\[…\])$/u', $unquoted ) || self::ends_in_abbreviation( $unquoted ) ) {
				continue;
			}

			$length = mb_strlen( $candidate, 'UTF-8' );

			if ( $length <= 160 || ( $extended_budget && $length <= 300 ) ) {
				$description = $candidate;
				continue;
			}

			if ( '' === $description && $length <= 300 ) {
				return $candidate;
			}

			if ( ! $extended_budget && mb_strlen( $description, 'UTF-8' ) < 80 && $length <= 300 ) {
				$description     = $candidate;
				$extended_budget = true;
				continue;
			}

			break;
		}

		if ( '' !== $description ) {
			return $description;
		}

		if ( mb_strlen( $source, 'UTF-8' ) <= 160 && ! preg_match( '/(?:…|\.\.\.|\[…\])["”’\'\)\]]*$/u', $source ) ) {
			return $source;
		}

		$cut = mb_substr( $source, 0, 160, 'UTF-8' );

		if ( mb_strlen( $source, 'UTF-8' ) > 160 && ' ' !== mb_substr( $source, 160, 1, 'UTF-8' ) ) {
			$space = mb_strrpos( $cut, ' ', 0, 'UTF-8' );
			$cut   = false === $space ? '' : mb_substr( $cut, 0, $space, 'UTF-8' );
		}

		$cut = (string) preg_replace( '/[\s\p{P}]+$/u', '', $cut );

		return '' !== $cut ? $cut . '…' : '';
	}

	/**
	 * A name's initial and common abbreviations do not end a sentence.
	 *
	 * @param string $text Text through a possible sentence boundary.
	 * @return bool
	 */
	private static function ends_in_abbreviation( string $text ): bool {
		return 1 === preg_match( '/(?:^|[\s"“‘\'\(\[])(?:Mr|Mrs|Ms|Dr|St|Jr|Sr|vs|No|Inc|Co|Ltd|U\.S|U\.K|etc|e\.g|i\.e|Jan|Feb|Mar|Apr|Jun|Jul|Aug|Sep|Sept|Oct|Nov|Dec)\.$/iu', $text )
			|| 1 === preg_match( '/(?:^|[\s"“‘\'\(\[])\p{Lu}\.$/u', $text );
	}

	/**
	 * Only a published, unprotected post on a public request may supply article metadata.
	 *
	 * @return bool
	 */
	private static function is_article(): bool {
		if ( is_admin() || is_feed() || is_search() || is_404() || ! is_singular( 'post' ) ) {
			return false;
		}

		$post = get_queried_object();

		return $post instanceof WP_Post && 'publish' === $post->post_status && '' === $post->post_password;
	}
}
