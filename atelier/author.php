<?php
/** Atelier — profil d’archive : identité claire, contributions canoniques et métadonnées extractibles. */
defined( 'ABSPATH' ) || exit;
get_header();
$author = get_queried_object();
$author_id = $author instanceof WP_User ? (int) $author->ID : 0;
?>
<main class="atelier-profile" id="main-content" data-content-kind="member-profile">
	<a class="atelier-backlink" href="<?php echo esc_url( bbp_get_forums_url() ); ?>">Retour aux discussions</a>
	<header class="atelier-profile__hero">
		<div class="atelier-profile__identity">
			<span class="atelier-avatar atelier-avatar--profile" style="background:<?php echo esc_attr( atelier_avatar_color( $author_id ) ); ?>" aria-hidden="true"><?php echo esc_html( atelier_initials( $author_id ) ); ?></span>
			<div><p class="atelier-kicker">Archive membre · <?php echo esc_html( $author->user_login ); ?></p><h1><?php echo esc_html( $author->display_name ); ?></h1><p class="atelier-profile__role"><?php echo esc_html( atelier_rank( $author_id ) ?: 'Membre' ); ?></p></div>
		</div>
		<?php if ( ! empty( $author->description ) ) : ?><p class="atelier-profile__bio"><?php echo esc_html( $author->description ); ?></p><?php endif; ?>
	</header>
	<div class="atelier-profile__layout">
		<section class="atelier-profile__stream" aria-labelledby="atelier-contributions-title">
			<header class="atelier-section-heading"><p>Trace publique</p><h2 id="atelier-contributions-title">Contributions documentées</h2></header>
			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); $kind = get_post_type() === ( function_exists( 'bbp_get_topic_post_type' ) ? bbp_get_topic_post_type() : 'topic' ) ? 'Discussion' : 'Réponse'; ?>
				<article class="atelier-contribution" data-contribution-kind="<?php echo esc_attr( strtolower( $kind ) ); ?>">
					<p class="atelier-contribution__kind"><?php echo esc_html( $kind ); ?> · <time datetime="<?php echo esc_attr( get_post_time( DATE_W3C, true ) ); ?>"><?php echo esc_html( get_the_date( 'd.m.Y' ) ); ?></time></p>
					<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
					<p><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt() ?: get_the_content() ), 36 ) ); ?></p>
					<footer><span><?php echo esc_html( atelier_total_upvotes( get_the_ID() ) ); ?> votes utiles</span><a href="<?php the_permalink(); ?>">Lire la source</a></footer>
				</article>
			<?php endwhile; the_posts_pagination( array( 'screen_reader_text' => 'Navigation des contributions' ) ); else : ?><p class="atelier-empty">Aucune contribution publique n’est encore archivée pour ce membre.</p><?php endif; ?>
		</section>
		<aside class="atelier-profile__aside" aria-label="Repères du membre"><p class="atelier-kicker">Identité</p><dl><div><dt>Rang</dt><dd><?php echo esc_html( atelier_rank( $author_id ) ?: 'Membre' ); ?></dd></div><div><dt>Profil</dt><dd>@<?php echo esc_html( $author->user_login ); ?></dd></div><div><dt>Avatar</dt><dd>Initiales stables</dd></div></dl></aside>
	</div>
</main>
<?php get_footer(); ?>
