<?php
/** Atelier — portail de communauté : modernisme éditorial, sources publiées et HTML LLM-first. */
$atelier_request_path = trim( (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ), '/' );
$atelier_user_base = function_exists( 'bbp_get_root_slug' ) && function_exists( 'bbp_get_user_slug' ) ? trim( bbp_get_root_slug() . '/' . bbp_get_user_slug(), '/' ) : '';
$atelier_is_user_profile = $atelier_user_base && ( $atelier_request_path === $atelier_user_base || 0 === strpos( $atelier_request_path, $atelier_user_base . '/' ) );
if ( $atelier_is_user_profile || ( function_exists( 'bbp_is_single_user_profile' ) && bbp_is_single_user_profile() ) ) {
	get_header();
	bbp_get_template_part( 'content', 'single-user' );
	get_footer();
	return;
}
get_header();
$atelier_stats = atelier_community_stats();
$atelier_topic_type = function_exists( 'bbp_get_topic_post_type' ) ? bbp_get_topic_post_type() : 'topic';
$atelier_forum_type = function_exists( 'bbp_get_forum_post_type' ) ? bbp_get_forum_post_type() : 'forum';
$atelier_recent_topics = get_posts( array( 'post_type' => $atelier_topic_type, 'post_status' => 'publish', 'posts_per_page' => 6, 'orderby' => 'date', 'order' => 'DESC', 'no_found_rows' => true ) );
$atelier_forums = get_posts( array( 'post_type' => $atelier_forum_type, 'post_status' => 'publish', 'posts_per_page' => 8, 'orderby' => 'menu_order title', 'order' => 'ASC', 'no_found_rows' => true ) );
$atelier_contributors = atelier_top_contributors( 5 );
$atelier_asset_base = trailingslashit( get_template_directory_uri() ) . 'assets/images/';
$atelier_spaces_url = function_exists( 'atelier_spaces_url' ) ? atelier_spaces_url() : atelier_home_section_url( 'espaces' );
$atelier_compose_url = atelier_compose_url();
?>
<main class="atelier-home" id="main-content" data-content-kind="forum-home">
	<header class="atelier-home__hero">
		<div class="atelier-home__hero-copy">
			<p class="atelier-kicker"><span aria-hidden="true"></span>Mémoire collective · 2024</p>
			<h1>Les bonnes conversations ne doivent pas disparaître.</h1>
			<p>Atelier transforme les discussions exigeantes en ressources vivantes, sourcées et partageables.</p>
			<div class="atelier-home__actions"><a class="atelier-home__primary-action" href="<?php echo esc_url( $atelier_spaces_url ); ?>">Explorer les espaces <span class="atelier-glyph atelier-glyph--arrow" aria-hidden="true"></span></a><a class="atelier-home__text-action" href="#sources-recentes">Lire les dernières sources <span class="atelier-glyph atelier-glyph--arrow" aria-hidden="true"></span></a></div>
		</div>
		<figure class="atelier-home__hero-art"><img src="<?php echo esc_url( $atelier_asset_base . 'atelier-hero.webp' ); ?>" alt="Composition éditoriale abstraite de papier ivoire, d’ombres bleutées et d’un ruban vermillon." fetchpriority="high" /></figure>
		<dl class="atelier-home__metrics" aria-label="Repères de la communauté"><div><dt>Espaces</dt><dd><?php echo esc_html( number_format_i18n( $atelier_stats['forums'] ) ); ?></dd></div><div><dt>Sources</dt><dd><?php echo esc_html( number_format_i18n( $atelier_stats['topics'] ) ); ?></dd></div><div><dt>Contributeurs</dt><dd><?php echo esc_html( number_format_i18n( $atelier_stats['contributors'] ) ); ?></dd></div></dl>
	</header>

	<section class="atelier-home__featured" id="sources-recentes" aria-labelledby="atelier-home-featured-title">
		<header class="atelier-section-heading"><p>À consulter maintenant</p><h2 id="atelier-home-featured-title">Sources récentes</h2></header>
		<div class="atelier-home__content-grid">
			<div class="atelier-topic-index">
				<?php if ( $atelier_recent_topics ) : foreach ( $atelier_recent_topics as $atelier_topic ) : $atelier_author = get_userdata( (int) $atelier_topic->post_author ); ?>
					<article class="atelier-topic-index__item" data-content-kind="forum-topic"><p class="atelier-topic-index__number">SOURCE <?php echo esc_html( str_pad( (string) $atelier_topic->ID, 3, '0', STR_PAD_LEFT ) ); ?></p><h3><a href="<?php echo esc_url( get_permalink( $atelier_topic ) ); ?>"><?php echo esc_html( get_the_title( $atelier_topic ) ); ?></a></h3><p class="atelier-topic-index__summary"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $atelier_topic->post_content ), 30 ) ); ?></p><footer><span><?php echo esc_html( $atelier_author ? $atelier_author->display_name : __( 'Membre', 'atelier' ) ); ?></span><time datetime="<?php echo esc_attr( get_post_time( 'c', true, $atelier_topic ) ); ?>"><?php echo esc_html( get_the_date( 'd.m.Y', $atelier_topic ) ); ?></time><span><?php echo esc_html( number_format_i18n( function_exists( 'bbp_get_topic_reply_count' ) ? (int) bbp_get_topic_reply_count( $atelier_topic->ID ) : 0 ) ); ?> réponses</span><span><?php echo esc_html( number_format_i18n( atelier_total_upvotes( $atelier_topic->ID ) ) ); ?> votes</span></footer></article>
				<?php endforeach; else : ?><p class="atelier-empty">Le forum prépare ses premières conversations.</p><?php endif; ?>
			</div>
				<aside class="atelier-home__aside" aria-label="Contributeurs actifs"><p class="atelier-kicker">Contributions visibles</p><h3>Voix de l’atelier</h3><?php if ( $atelier_contributors ) : ?><ol class="atelier-contributor-list"><?php foreach ( $atelier_contributors as $atelier_contributor ) : $atelier_user = get_userdata( (int) $atelier_contributor->user_id ); if ( ! $atelier_user ) continue; ?><li><span class="atelier-avatar" style="background:<?php echo esc_attr( atelier_avatar_color( (int) $atelier_user->ID ) ); ?>"><?php echo esc_html( atelier_initials( (int) $atelier_user->ID ) ); ?></span><span><a href="<?php echo esc_url( function_exists( 'bbp_get_user_profile_url' ) ? bbp_get_user_profile_url( $atelier_user->ID ) : get_author_posts_url( $atelier_user->ID ) ); ?>"><?php echo esc_html( $atelier_user->display_name ); ?></a><small><?php echo esc_html( (int) $atelier_contributor->topic_total ); ?> sujets · <?php echo esc_html( (int) $atelier_contributor->reply_total ); ?> réponses</small></span></li><?php endforeach; ?></ol><?php else : ?><p class="atelier-empty">Les contributions apparaîtront ici dès la première publication.</p><?php endif; ?><figure class="atelier-home__knowledge-figure"><img src="<?php echo esc_url( $atelier_asset_base . 'atelier-knowledge-map.webp' ); ?>" alt="Carte abstraite en papiers superposés, traversée par un marqueur vermillon." loading="lazy" /><figcaption>Une discussion lisible garde chaque source à sa place.</figcaption></figure></aside>
		</div>
	</section>

	<section class="atelier-home__spaces" id="espaces" aria-labelledby="atelier-home-spaces-title"><header class="atelier-section-heading"><p>Explorer par contexte</p><h2 id="atelier-home-spaces-title">Espaces disponibles</h2></header><div class="atelier-space-grid"><?php if ( $atelier_forums ) : foreach ( $atelier_forums as $atelier_forum ) : ?><article class="atelier-space-card" data-content-kind="forum-category"><p>ESPACE <?php echo esc_html( str_pad( (string) $atelier_forum->ID, 3, '0', STR_PAD_LEFT ) ); ?></p><h3><a href="<?php echo esc_url( get_permalink( $atelier_forum ) ); ?>"><?php echo esc_html( get_the_title( $atelier_forum ) ); ?></a></h3><p><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $atelier_forum->post_content ), 18 ) ); ?></p><footer><span><?php echo esc_html( number_format_i18n( function_exists( 'bbp_get_forum_topic_count' ) ? (int) bbp_get_forum_topic_count( $atelier_forum->ID ) : 0 ) ); ?> sujets</span><span><?php echo esc_html( number_format_i18n( atelier_forum_reply_total( $atelier_forum->ID ) ) ); ?> réponses</span></footer></article><?php endforeach; else : ?><p class="atelier-empty">Les espaces de discussion seront bientôt publiés.</p><?php endif; ?></div></section>

	<section class="atelier-home__callout" id="methodes" aria-labelledby="atelier-home-callout-title"><p class="atelier-kicker">Méthodes de contribution</p><h2 id="atelier-home-callout-title">Une question mérite une trace que d’autres pourront prolonger.</h2><p>Choisissez un espace, formulez le contexte et indiquez les éléments utiles à une lecture future.</p><a href="<?php echo esc_url( $atelier_compose_url ); ?>">Ouvrir une discussion</a></section>
</main>
<?php get_footer();
