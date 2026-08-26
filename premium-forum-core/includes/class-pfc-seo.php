<?php
defined( 'ABSPATH' ) || exit;

final class PFC_SEO {
	public static function init(): void {
		add_filter( 'the_content', array( __CLASS__, 'ugc_links' ), 30 );
		/* bbPress initialise sa boucle de réponses pendant le rendu du template :
		 * le JSON-LD est émis avant </body>, une position valide pour schema.org. */
			add_action( 'wp_head', array( __CLASS__, 'meta_description' ), 1 );
			add_action( 'wp_head', array( __CLASS__, 'front_page_canonical' ), 2 );
			add_action( 'wp_footer', array( __CLASS__, 'topic_schema' ), 20 );
			add_action( 'wp_footer', array( __CLASS__, 'breadcrumb_schema' ), 21 );
			add_filter( 'document_title_parts', array( __CLASS__, 'title' ) );
	}
	public static function ugc_links( string $content ): string { return function_exists( 'wp_rel_ugc' ) ? wp_rel_ugc( $content ) : $content; }
			public static function title( array $parts ): array {
			if ( function_exists( 'bbp_is_single_topic' ) && bbp_is_single_topic() ) {
				$parts['title'] = get_the_title() . ' — ' . bbp_get_forum_title( bbp_get_topic_forum_id() );
				$parts['site']  = 'Atelier';
				unset( $parts['tagline'] );
			} elseif ( is_front_page() || is_home() ) {
				$parts['title'] = 'Atelier — Forum de connaissances';
				$parts['site']  = 'Atelier';
				unset( $parts['tagline'] );
			}
			return $parts;
		}
	public static function meta_description(): void {
			$description = '';
			if ( function_exists( 'bbp_is_single_topic' ) && bbp_is_single_topic() ) {
				$topic = get_queried_object();
				$description = $topic instanceof WP_Post ? wp_trim_words( wp_strip_all_tags( $topic->post_content ), 28, '…' ) : '';
			} elseif ( function_exists( 'bbp_is_single_forum' ) && bbp_is_single_forum() ) {
				$forum_id = bbp_get_forum_id();
				$description = wp_trim_words( wp_strip_all_tags( bbp_get_forum_content( $forum_id ) ?: bbp_get_forum_title( $forum_id ) ), 28, '…' );
			} elseif ( is_front_page() || is_home() ) {
				$description = 'Atelier transforme les discussions exigeantes en ressources vivantes, sourcées et partageables.';
			} elseif ( is_page() ) {
				$description = wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', get_queried_object_id() ) ?: get_the_title() ), 28, '…' );
			}
			if ( $description ) {
				echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
			}
	}

	/** Rend un canonique explicite pour l’URL racine sans dupliquer les canoniques WordPress des contenus singuliers. */
	public static function front_page_canonical(): void {
		if ( ( is_front_page() || is_home() ) && ! is_paged() ) {
			echo '<link rel="canonical" href="' . esc_url( home_url( '/' ) ) . '">' . "\n";
		}
	}

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
		public static function breadcrumb_schema(): void {
			if ( ! function_exists( 'bbp_is_single_topic' ) || ! function_exists( 'bbp_is_single_forum' ) || ( ! bbp_is_single_topic() && ! bbp_is_single_forum() ) ) return;
			$items = array( array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Atelier', 'item' => home_url( '/' ) ) );
			if ( bbp_is_single_topic() ) {
				$forum_id = bbp_get_topic_forum_id();
				$items[] = array( '@type' => 'ListItem', 'position' => 2, 'name' => bbp_get_forum_title( $forum_id ), 'item' => bbp_get_forum_permalink( $forum_id ) );
				$items[] = array( '@type' => 'ListItem', 'position' => 3, 'name' => get_the_title(), 'item' => get_permalink() );
			} else {
				$items[] = array( '@type' => 'ListItem', 'position' => 2, 'name' => 'Espaces', 'item' => function_exists( 'atelier_spaces_url' ) ? atelier_spaces_url() : home_url( '/' ) );
				$items[] = array( '@type' => 'ListItem', 'position' => 3, 'name' => bbp_get_forum_title(), 'item' => bbp_get_forum_permalink() );
			}
			echo "\n<script type=\"application/ld+json\">" . wp_json_encode( array( '@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "</script>\n";
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
