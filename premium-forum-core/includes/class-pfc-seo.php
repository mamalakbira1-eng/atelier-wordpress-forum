<?php
defined( 'ABSPATH' ) || exit;

final class PFC_SEO {
	public static function init(): void {
		add_filter( 'the_content', array( __CLASS__, 'ugc_links' ), 30 );
		/* bbPress initialise sa boucle de réponses pendant le rendu du template :
		 * le JSON-LD est donc émis avant </body>, une position valide pour schema.org. */
		add_action( 'wp_footer', array( __CLASS__, 'topic_schema' ), 20 );
		add_filter( 'document_title_parts', array( __CLASS__, 'title' ) );
	}
	public static function ugc_links( string $content ): string { return function_exists( 'wp_rel_ugc' ) ? wp_rel_ugc( $content ) : $content; }
	public static function title( array $parts ): array { if ( function_exists( 'bbp_is_single_topic' ) && bbp_is_single_topic() ) { $parts['title'] = get_the_title() . ' — ' . bbp_get_forum_title( bbp_get_topic_forum_id() ); } return $parts; }
	public static function topic_schema(): void {
		if ( ! function_exists( 'bbp_is_single_topic' ) || ! bbp_is_single_topic() ) return;
		$topic = get_queried_object(); if ( ! $topic instanceof WP_Post ) return;
		$author = get_userdata( $topic->post_author );
		$data = array( '@context' => 'https://schema.org', '@type' => 'DiscussionForumPosting', 'headline' => get_the_title( $topic ), 'text' => wp_strip_all_tags( $topic->post_content ), 'datePublished' => get_post_time( DATE_W3C, true, $topic ), 'dateModified' => self::historical_or_modified( $topic ), 'url' => get_permalink( $topic ), 'author' => array( '@type' => 'Person', 'name' => $author ? $author->display_name : 'Membre' ) );
		$replies = self::public_replies( $topic );
		if ( $replies ) {
			$data['commentCount'] = count( $replies );
			$data['comment'] = array_map( static function( WP_Post $reply ): array {
				$reply_author = get_userdata( $reply->post_author );
				return array(
					'@type'        => 'Comment',
					'text'         => wp_strip_all_tags( $reply->post_content ),
					'dateCreated'  => get_post_time( DATE_W3C, true, $reply ),
					'dateModified' => self::historical_or_modified( $reply ),
					'url'          => get_permalink( $reply ),
					'author'       => array( '@type' => 'Person', 'name' => $reply_author ? $reply_author->display_name : 'Membre' ),
				);
			}, $replies );
		}
		echo "\n<script type=\"application/ld+json\">" . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "</script>\n";
	}
	private static function historical_or_modified( WP_Post $post ): string {
		$historical = get_post_meta( $post->ID, 'pfc_historical_updated_at', true );
		if ( $historical ) {
			try { return ( new DateTimeImmutable( $historical, wp_timezone() ) )->format( DATE_W3C ); } catch ( Exception $exception ) { /* Retombe sur WordPress. */ }
		}
		return get_post_modified_time( DATE_W3C, true, $post );
	}
	/**
	 * Lit les réponses visibles à partir de la relation WordPress et du méta bbPress.
	 * La double relation couvre les imports historiques comme les réponses créées nativement.
	 */
	private static function public_replies( WP_Post $topic ): array {
		if ( ! function_exists( 'bbp_has_replies' ) || ! bbp_has_replies( array( 'post_parent' => $topic->ID, 'posts_per_page' => -1 ) ) ) {
			return array();
		}
		$replies = array();
		while ( bbp_replies() ) {
			bbp_the_reply();
			$reply = get_post( bbp_get_reply_id() );
			if ( $reply instanceof WP_Post && $reply->ID !== $topic->ID ) {
				$replies[] = $reply;
			}
		}
		wp_reset_postdata();
		return $replies;
	}
}
