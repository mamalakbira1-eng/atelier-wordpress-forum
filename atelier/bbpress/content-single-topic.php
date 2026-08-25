<?php
/**
 * Atelier — discussion éditoriale tactile : rail d’index, source canonique,
 * lecture claire et réponses séparées pour les humains et les systèmes d’extraction.
 */
defined( 'ABSPATH' ) || exit;
$topic_id    = bbp_get_topic_id();
$author_id   = bbp_get_topic_author_id( $topic_id );
$forum_id    = bbp_get_topic_forum_id( $topic_id );
$reply_count = (int) bbp_get_topic_reply_count( $topic_id );
$vote_count  = (int) atelier_total_upvotes( $topic_id );
$asset_base  = trailingslashit( get_template_directory_uri() ) . 'assets/images/';
$topic_url   = get_permalink( $topic_id );
$forum_title = bbp_get_forum_title( $forum_id );
$related     = get_posts( array( 'post_type' => bbp_get_topic_post_type(), 'post_status' => 'publish', 'posts_per_page' => 3, 'post__not_in' => array( $topic_id ), 'orderby' => 'date', 'order' => 'DESC', 'no_found_rows' => true ) );
?>
<main id="main-content" class="atelier-thread" data-content-kind="discussion-forum-posting">
	<section id="topic-root" class="atelier-thread__hero" aria-label="Présentation du forum"><div><p class="atelier-kicker"><span aria-hidden="true"></span>Mémoire collective · 2024</p><h2>Les bonnes conversations ne doivent pas disparaître.</h2><p>Atelier transforme les discussions exigeantes en ressources vivantes, sourcées et partageables.</p><a href="#topic-root">Lire la discussion du jour <span aria-hidden="true">→</span></a></div><figure><img src="<?php echo esc_url( $asset_base . 'atelier-hero.webp' ); ?>" alt="Composition éditoriale abstraite de papier ivoire, d’ombres bleutées et d’un ruban vermillon." fetchpriority="high"></figure></section>
	<div class="atelier-thread__layout">
		<aside class="atelier-thread__rail" aria-label="Index de la discussion">
			<p class="atelier-thread__rail-label">Index / 05</p>
			<nav>
				<a href="#topic-root"><span>01</span>Contexte</a>
				<a href="#message-initial"><span>02</span>Message initial</a>
				<a href="#reponses"><span>03</span>Réponses</a>
				<a href="#machine-reading"><span>04</span>Lecture machine</a>
			</nav>
			<div class="atelier-thread__rail-filter">
				<p>Explorer par</p>
<a href="#reponses" data-atelier-filter="all" aria-current="true">Tout voir</a>
					<a href="#reponses" data-atelier-filter="featured">Contributions repérées</a>
					<a href="#reponses" data-atelier-filter="latest">Dernières réponses</a>
			</div>
		</aside>

		<div class="atelier-thread__main">
			<header class="atelier-thread__intro">
				<p class="atelier-kicker">Atelier · discussion archivée</p>
				<nav class="atelier-breadcrumb" aria-label="Fil d’Ariane"><?php bbp_breadcrumb(); ?></nav>
				<div class="atelier-thread__source-line"><span>SOURCE <strong><?php echo esc_html( str_pad( (string) $topic_id, 4, '0', STR_PAD_LEFT ) ); ?></strong></span><i></i><span><?php echo esc_html( $forum_title ); ?></span></div>
				<h1 dir="<?php echo esc_attr( atelier_content_dir( (string) get_the_title( $topic_id ) ) ); ?>"><?php bbp_topic_title( $topic_id ); ?></h1>
				<div class="atelier-thread__facts"><span><?php echo esc_html( $reply_count ); ?> réponse<?php echo 1 === $reply_count ? '' : 's'; ?></span><span><?php echo esc_html( $vote_count ); ?> votes utiles</span><time datetime="<?php echo esc_attr( get_post_time( DATE_W3C, true, $topic_id ) ); ?>">Publié <?php echo esc_html( get_the_date( 'd.m.Y', $topic_id ) ); ?></time></div>
				<div class="atelier-thread__actions" role="group" aria-label="Actions de la discussion"><a class="atelier-button atelier-button--primary" href="#new-reply"><svg aria-hidden="true" viewBox="0 0 24 24" focusable="false"><path d="M5 5.5h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-8l-4.5 3v-3H5a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg><span>Répondre</span><b aria-hidden="true">↗</b></a><button type="button" class="atelier-button atelier-button--quiet" data-atelier-share data-url="<?php echo esc_url( $topic_url ); ?>" data-label="Partager"><svg aria-hidden="true" viewBox="0 0 24 24" focusable="false"><circle cx="18" cy="5" r="2.2" fill="none" stroke="currentColor" stroke-width="1.7"/><circle cx="6" cy="12" r="2.2" fill="none" stroke="currentColor" stroke-width="1.7"/><circle cx="18" cy="19" r="2.2" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="m8 11 7.8-4.5M8 13l7.8 4.5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg><span>Partager</span></button><?php if ( class_exists( 'PFC_Community' ) ) : $voted = is_user_logged_in() && PFC_Community::has_voted( get_current_user_id(), $topic_id ); ?><button type="button" class="atelier-button atelier-button--quiet atelier-vote-button atelier-vote-button--topic <?php echo $voted ? 'is-active' : ''; ?>" data-pfc-vote data-object-id="<?php echo esc_attr( $topic_id ); ?>" data-login="<?php echo esc_url( wp_login_url( $topic_url . '#bbp-topic-' . $topic_id ) ); ?>" aria-pressed="<?php echo $voted ? 'true' : 'false'; ?>" aria-label="Voter utile pour ce sujet"><svg aria-hidden="true" viewBox="0 0 24 24" focusable="false"><path d="M12 4v16M6.5 10.5 12 4l5.5 6.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg><span data-pfc-vote-label>Voter utile</span><strong data-pfc-vote-count><?php echo esc_html( $vote_count ); ?></strong></button><?php endif; ?><?php if ( class_exists( 'PFC_Community' ) ) : $following = is_user_logged_in() && PFC_Community::is_following( get_current_user_id(), $topic_id ); ?><button type="button" class="atelier-button atelier-button--quiet" data-pfc-follow data-topic-id="<?php echo esc_attr( $topic_id ); ?>" data-login="<?php echo esc_url( wp_login_url( $topic_url ) ); ?>" aria-pressed="<?php echo $following ? 'true' : 'false'; ?>"><svg aria-hidden="true" viewBox="0 0 24 24" focusable="false"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9ZM10 21h4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg><span>Suivre</span></button><?php endif; ?></div>
			</header>

			<article id="message-initial" class="atelier-initial-post" data-topic-id="<?php bbp_topic_id(); ?>">
				<aside class="atelier-author-card"><span class="atelier-avatar atelier-avatar--large" style="background:<?php echo esc_attr( atelier_avatar_color( $author_id ) ); ?>"><?php echo esc_html( atelier_initials( $author_id ) ); ?></span><span class="atelier-author-card__role">Question posée par</span></aside>
				<div class="atelier-initial-post__body"><header class="atelier-post-meta"><span class="atelier-author"><?php bbp_topic_author_link( array( 'type' => 'name' ) ); ?></span><span class="atelier-rank"><?php echo esc_html( atelier_rank( $author_id ) ); ?></span><?php if ( function_exists( 'atelier_user_role_label' ) ) : ?><span class="atelier-role-badge"><?php echo esc_html( atelier_user_role_label( $author_id ) ); ?></span><?php endif; ?><time datetime="<?php echo esc_attr( get_post_time( DATE_W3C, true, $topic_id ) ); ?>"><?php echo esc_html( get_the_date( 'd F Y', $topic_id ) ); ?></time></header><div class="atelier-initial-post__content" dir="<?php echo esc_attr( atelier_content_dir( (string) get_post_field( 'post_content', $topic_id ) ) ); ?>"><?php bbp_topic_content( $topic_id ); ?></div><footer class="atelier-post-footer"><span>Message initial</span><a href="#reponses">Consulter les réponses</a></footer></div>
			</article>

			<section id="reponses" class="atelier-replies" data-replies-section aria-labelledby="atelier-replies-title"><header class="atelier-section-heading"><p>La conversation continue</p><h2 id="atelier-replies-title"><?php echo esc_html( $reply_count ); ?> réponse<?php echo 1 === $reply_count ? '' : 's'; ?> qui font avancer le sujet</h2><label class="atelier-replies__sort">Trier <select aria-label="Trier les réponses" data-atelier-sort><option value="relevance">Par pertinence</option><option value="date">Par date</option><option value="votes">Par upvotes</option></select></label></header><?php if ( bbp_has_replies() ) : while ( bbp_replies() ) : bbp_the_reply(); if ( (int) bbp_get_reply_id() !== (int) $topic_id ) bbp_get_template_part( 'loop', 'single-reply' ); endwhile; else : ?><p class="atelier-empty">Aucune réponse documentée pour le moment.</p><?php endif; ?></section>
			<section id="new-reply" class="atelier-reply-composer" aria-labelledby="atelier-reply-title"><header class="atelier-section-heading"><p>Votre contribution</p><h2 id="atelier-reply-title">Ajoutez une réponse qui fait avancer le sujet.</h2></header><?php if ( is_user_logged_in() && function_exists( 'bbp_get_template_part' ) ) : ?><p class="atelier-reply-composer__hint">Votre nom, votre rang et la date de publication seront associés clairement à votre contribution.</p><?php bbp_get_template_part( 'form', 'reply' ); else : ?><p>Connectez-vous pour répondre à cette discussion.</p><a class="atelier-button atelier-button--primary" href="<?php echo esc_url( wp_login_url( $topic_url . '#new-reply' ) ); ?>">Se connecter</a><?php endif; ?></section>
		</div>

		<aside class="atelier-thread__aside" aria-label="Lecture claire et ressources liées">
			<section class="atelier-reading-card" id="machine-reading"><header><span>✧ Lecture claire</span><small>LLM-FIRST</small></header><h2>Ce sujet expose ce qui compte.</h2><p>Contexte, auteurs, dates, réponses et source restent séparables pour une lecture humaine comme machine.</p><dl><div><dt>Source</dt><dd>Canonique</dd></div><div><dt>Structure</dt><dd>HTML initial</dd></div><div><dt>Dates</dt><dd>Historiques</dd></div></dl><a href="#topic-root">Copier le lien source</a></section>
			<section class="atelier-thread__stats" aria-label="Repères du sujet"><div><span><?php echo esc_html( $reply_count ); ?></span><small>réponses</small></div><div><span><?php echo esc_html( $vote_count ); ?></span><small>upvotes</small></div><div><span><?php echo esc_html( human_time_diff( get_post_time( 'U', true, $topic_id ), current_time( 'timestamp' ) ) ); ?></span><small>d’activité</small></div></section>
			<figure class="atelier-thread__figure"><img src="<?php echo esc_url( $asset_base . 'atelier-knowledge-map.webp' ); ?>" alt="Carte abstraite en papiers superposés, traversée par un marqueur vermillon." loading="lazy"><figcaption>Une discussion lisible garde chaque source à sa place.</figcaption></figure>
			<section class="atelier-related"><p class="atelier-kicker">À poursuivre</p><ol><?php foreach ( $related as $related_topic ) : ?><li><a href="<?php echo esc_url( get_permalink( $related_topic ) ); ?>"><?php echo esc_html( get_the_title( $related_topic ) ); ?></a><span><?php echo esc_html( atelier_total_upvotes( $related_topic->ID ) ); ?> votes</span></li><?php endforeach; ?></ol></section>
			<a class="atelier-collection-card" href="<?php echo esc_url( function_exists( 'atelier_methods_url' ) ? atelier_methods_url() : home_url( '/methodes/' ) ); ?>"><span>Collection</span><strong>Écrire pour être relu</strong><small>Explorer les repères →</small></a>
		</aside>
	</div>
</main>
