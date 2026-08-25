<?php defined( 'ABSPATH' ) || exit; ?>
<article id="bbp-topic-<?php bbp_topic_id(); ?>" class="atelier-topic" data-topic-id="<?php bbp_topic_id(); ?>">
	<header class="atelier-topic__header">
		<nav aria-label="Fil d’Ariane"><?php bbp_breadcrumb(); ?></nav>
		<p class="atelier-meta">Sujet · <?php echo esc_html( bbp_get_forum_title( bbp_get_topic_forum_id() ) ); ?></p>
		<h1 class="atelier-topic__title"><?php bbp_topic_title(); ?></h1>
	</header>
	<section class="atelier-post" aria-labelledby="atelier-initial-title"><span class="atelier-post__marker" aria-hidden="true"></span><div><h2 id="atelier-initial-title" class="screen-reader-text">Message initial</h2><header class="atelier-meta"><?php bbp_topic_author_link(); ?> · <span class="atelier-rank"><?php echo esc_html( atelier_rank( bbp_get_topic_author_id() ) ); ?></span> · <time datetime="<?php echo esc_attr( get_post_time( DATE_W3C, true, bbp_get_topic_id() ) ); ?>"><?php bbp_topic_freshness_link(); ?></time></header><div class="atelier-post__content"><?php bbp_topic_content(); ?></div></div></section>
</article>
