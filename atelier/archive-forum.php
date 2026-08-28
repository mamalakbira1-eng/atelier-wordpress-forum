<?php
/**
 * Atelier — archive publique des forums bbPress.
 *
 * Cette route est distincte d’une page WordPress portant le même libellé :
 * bbPress appelle ici l’archive du post type forum.
 */
defined( 'ABSPATH' ) || exit;

$forums_type = function_exists( 'bbp_get_forum_post_type' ) ? bbp_get_forum_post_type() : 'forum';
$forums      = get_posts(
	array(
		'post_type'      => $forums_type,
		'post_status'    => 'publish',
		'posts_per_page' => 40,
		'orderby'        => 'menu_order title',
		'order'          => 'ASC',
		'no_found_rows'  => true,
	)
);

get_header();
?>
<main id="main-content" class="atelier-page atelier-page--forums" data-content-kind="forum-archive">
	<header class="atelier-page__hero">
		<p class="atelier-kicker">Index des discussions</p>
		<h1>Forums</h1>
		<p>Explorez les espaces publics et leurs conversations avant de rejoindre la discussion.</p>
	</header>
	<section class="atelier-page__section" aria-labelledby="atelier-forums-title">
		<header class="atelier-section-heading">
			<p>Communauté active</p>
			<h2 id="atelier-forums-title">Tous les forums</h2>
		</header>
		<div class="atelier-space-grid">
			<?php if ( $forums ) : ?>
				<?php foreach ( $forums as $forum ) : ?>
					<article class="atelier-space-card" data-content-kind="forum-index">
						<p>ESPACE <?php echo esc_html( str_pad( (string) $forum->ID, 3, '0', STR_PAD_LEFT ) ); ?></p>
						<h2><a href="<?php echo esc_url( get_permalink( $forum ) ); ?>"><?php echo esc_html( get_the_title( $forum ) ); ?></a></h2>
						<?php if ( $forum->post_content ) : ?>
							<p><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $forum->post_content ), 22 ) ); ?></p>
						<?php endif; ?>
						<footer>
							<span><?php echo esc_html( function_exists( 'bbp_get_forum_topic_count' ) ? (int) bbp_get_forum_topic_count( $forum->ID ) : 0 ); ?> sujets</span>
							<span><?php echo esc_html( function_exists( 'atelier_forum_reply_total' ) ? atelier_forum_reply_total( $forum->ID ) : 0 ); ?> réponses</span>
							<a href="<?php echo esc_url( get_permalink( $forum ) ); ?>">Lire l’espace <span aria-hidden="true">→</span></a>
						</footer>
					</article>
				<?php endforeach; ?>
			<?php else : ?>
				<p class="atelier-empty">Aucun forum public n’est encore disponible.</p>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php get_footer();
