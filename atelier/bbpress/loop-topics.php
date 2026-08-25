<?php /* Atelier — index de sources : titre, auteur, rang, date, réponses et votes dans le HTML initial. */ defined( 'ABSPATH' ) || exit; ?>
<?php if ( bbp_has_topics() ) : ?>
<section class="atelier-topic-index" aria-label="Sujets de la catégorie">
	<?php while ( bbp_topics() ) : bbp_the_topic(); ?>
		<article id="bbp-topic-<?php bbp_topic_id(); ?>" class="atelier-topic-index__item">
			<p class="atelier-topic-index__number">SOURCE <?php echo esc_html( str_pad( (string) bbp_get_topic_id(), 3, '0', STR_PAD_LEFT ) ); ?></p>
			<h3><a href="<?php bbp_topic_permalink(); ?>"><?php bbp_topic_title(); ?></a></h3>
			<p class="atelier-topic-index__summary"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( bbp_get_topic_content() ), 27 ) ); ?></p>
			<footer><span class="atelier-author"><?php bbp_topic_author_link( array( 'type' => 'name' ) ); ?></span><span class="atelier-rank"><?php echo esc_html( atelier_rank( bbp_get_topic_author_id() ) ?: 'Membre' ); ?></span><time datetime="<?php echo esc_attr( get_post_time( DATE_W3C, true, bbp_get_topic_id() ) ); ?>"><?php echo esc_html( get_the_date( 'd.m.Y', bbp_get_topic_id() ) ); ?></time><span><?php bbp_topic_reply_count(); ?> réponses</span><span><?php echo esc_html( atelier_total_upvotes( bbp_get_topic_id() ) ); ?> votes</span></footer>
		</article>
	<?php endwhile; ?>
</section>
<?php else : ?><p>Aucun sujet public dans cette catégorie pour le moment.</p><?php endif; ?>
