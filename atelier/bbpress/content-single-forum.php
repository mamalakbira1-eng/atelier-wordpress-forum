<?php
/** Atelier — catégorie : index éditorial asymétrique, sources canoniques et repères d’activité. */
defined( 'ABSPATH' ) || exit;
$forum_id = bbp_get_forum_id();
?>
<main class="atelier-forum" id="main-content" data-content-kind="forum-category">
	<a class="atelier-backlink" href="<?php echo esc_url( function_exists( 'atelier_spaces_url' ) ? atelier_spaces_url() : atelier_home_section_url( 'espaces' ) ); ?>">Tous les espaces</a>
	<header class="atelier-forum__hero">
		<p class="atelier-kicker">Dossier de discussion</p>
		<p class="atelier-forum__index">CATÉGORIE <strong>0<?php echo esc_html( $forum_id ); ?></strong><span></span> Forum public</p>
		<h1><?php bbp_forum_title( $forum_id ); ?></h1>
		<?php if ( bbp_get_forum_content( $forum_id ) ) : ?><div class="atelier-forum__description"><?php bbp_forum_content( $forum_id ); ?></div><?php endif; ?>
		<div class="atelier-forum__facts"><span><?php echo esc_html( bbp_get_forum_topic_count( $forum_id ) ); ?> sujets</span><span><?php echo esc_html( atelier_forum_reply_total( $forum_id ) ); ?> réponses</span><a href="#nouveau-sujet">Ouvrir une discussion</a></div>
	</header>
	<div class="atelier-forum__layout">
		<section class="atelier-forum__topics" aria-labelledby="atelier-forum-topics-title"><header class="atelier-section-heading"><p>Archive active</p><h2 id="atelier-forum-topics-title">Sujets dans cet espace</h2></header><?php bbp_get_template_part( 'loop', 'topics' ); ?></section>
		<aside class="atelier-forum__aside" aria-label="Repères de l’espace"><p class="atelier-kicker">Lire avant de contribuer</p><p>Chaque sujet doit formuler un contexte, une question ou une décision vérifiable.</p><dl><div><dt>Réponses</dt><dd>Liées à la source</dd></div><div><dt>Dates</dt><dd>Historique conservé</dd></div></dl></aside>
	</div>
	<section id="nouveau-sujet" class="atelier-forum__composer" aria-labelledby="atelier-composer-title"><header class="atelier-section-heading"><p>Nouvelle source</p><h2 id="atelier-composer-title">Ouvrir une discussion</h2></header><?php bbp_get_template_part( 'form', 'topic' ); ?></section>
</main>
