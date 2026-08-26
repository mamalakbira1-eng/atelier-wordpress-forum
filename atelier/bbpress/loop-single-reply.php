<?php defined( 'ABSPATH' ) || exit; ?>
<?php /** Atelier — carte de réponse : lecture autonome, vote utile et réponse contextuelle. */ ?>
<?php
$reply_id   = bbp_get_reply_id();
$author_id  = bbp_get_reply_author_id( $reply_id );
$reply_url  = get_permalink( $reply_id );
$voted      = class_exists( 'PFC_Community' ) && is_user_logged_in() && PFC_Community::has_voted( get_current_user_id(), $reply_id );
$vote_count = (int) atelier_total_upvotes( $reply_id );
?>
<article id="bbp-reply-<?php echo esc_attr( $reply_id ); ?>" class="atelier-reply" data-reply-id="<?php echo esc_attr( $reply_id ); ?>" data-reply-votes="<?php echo esc_attr( $vote_count ); ?>" data-reply-date="<?php echo esc_attr( get_post_time( 'U', true, $reply_id ) ); ?>">
	<header class="atelier-reply__header">
		<div class="atelier-reply__author">
			<aside class="atelier-avatar" style="background:<?php echo esc_attr( atelier_avatar_color( $author_id ) ); ?>" aria-label="<?php echo esc_attr( bbp_get_reply_author_display_name( $reply_id ) ); ?>"><?php echo esc_html( atelier_initials( $author_id ) ); ?></aside>
			<div class="atelier-reply__identity">
				<span class="atelier-author"><?php bbp_reply_author_link( array( 'type' => 'name' ) ); ?></span>
				<span class="atelier-rank"><?php echo esc_html( atelier_rank( $author_id ) ); ?></span>
				<?php if ( function_exists( 'atelier_user_role_label' ) && atelier_user_role_label( $author_id ) ) : ?><span class="atelier-role-badge"><?php echo esc_html( atelier_user_role_label( $author_id ) ); ?></span><?php endif; ?>
			</div>
		</div>
		<div class="atelier-reply__meta">
			<time datetime="<?php echo esc_attr( get_post_time( DATE_W3C, true, $reply_id ) ); ?>"><?php bbp_reply_post_date(); ?></time>
			<button type="button" class="atelier-reply__more" aria-label="Plus d’options pour cette réponse" data-atelier-reply-menu>•••</button>
		</div>
	</header>
	<div class="atelier-reply__content" dir="<?php echo esc_attr( atelier_content_dir( (string) get_post_field( 'post_content', $reply_id ) ) ); ?>"><?php bbp_reply_content(); ?></div>
	<footer class="atelier-reply__footer">
		<?php if ( class_exists( 'PFC_Community' ) ) : ?>
                <button type="button" class="atelier-vote-button atelier-vote-button--reply<?php echo $voted ? ' is-active' : ''; ?>" data-pfc-vote data-object-id="<?php echo esc_attr( $reply_id ); ?>" data-login="<?php echo esc_url( wp_login_url( $reply_url ) ); ?>" aria-pressed="<?php echo $voted ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( sprintf( '%d utile — Marquer la réponse de %s comme utile', $vote_count, bbp_get_reply_author_display_name( $reply_id ) ) ); ?>"><svg class="atelier-vote-icon" aria-hidden="true" viewBox="0 0 24 24" focusable="false"><path d="M7.5 10.5v9m0-9 4.2-8.2c.5-1 1.8-.8 2.1.3l.2.8c.3 1.2.1 2.5-.5 3.6h6.1c1.3 0 2.2 1.2 1.9 2.5l-1.6 7.1a2.5 2.5 0 0 1-2.4 2H7.5m0-9H4.8a1.8 1.8 0 0 0-1.8 1.8v5.4a1.8 1.8 0 0 0 1.8 1.8h2.7" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg><strong data-pfc-vote-count><?php echo esc_html( $vote_count ); ?></strong><span data-pfc-vote-label>utile</span></button>
		<?php else : ?><span class="atelier-reply__vote-static"><?php echo esc_html( $vote_count ); ?> utile</span><?php endif; ?>
		<a class="atelier-reply__respond" href="#new-reply" data-reply-to="<?php echo esc_attr( $reply_id ); ?>">Répondre <span aria-hidden="true">↗</span></a>
	</footer>
</article>
