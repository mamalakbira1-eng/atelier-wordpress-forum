<?php defined( 'ABSPATH' ) || exit; ?>
<?php /** Atelier: chaque réponse reste une unité sémantique autonome et citable. */ ?>
<article id="bbp-reply-<?php bbp_reply_id(); ?>" class="atelier-reply" data-reply-id="<?php bbp_reply_id(); ?>">
	<aside class="atelier-avatar" style="background:<?php echo esc_attr( atelier_avatar_color( bbp_get_reply_author_id() ) ); ?>" aria-label="<?php echo esc_attr( bbp_get_reply_author_display_name() ); ?>"><?php echo esc_html( atelier_initials( bbp_get_reply_author_id() ) ); ?></aside>
	<div class="atelier-reply__body"><header class="atelier-post-meta"><span class="atelier-author"><?php bbp_reply_author_link( array( 'type' => 'name' ) ); ?></span><span class="atelier-rank"><?php echo esc_html( atelier_rank( bbp_get_reply_author_id() ) ); ?></span><time datetime="<?php echo esc_attr( get_post_time( DATE_W3C, true, bbp_get_reply_id() ) ); ?>"><?php bbp_reply_post_date(); ?></time></header><div class="atelier-reply__content"><?php bbp_reply_content(); ?></div><footer class="atelier-post-footer"><span>Réponse archivée</span><span><?php echo esc_html( atelier_total_upvotes( bbp_get_reply_id() ) ); ?> votes utiles</span></footer></div>
</article>
