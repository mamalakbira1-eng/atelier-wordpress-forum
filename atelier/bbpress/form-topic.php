<?php /* Atelier — publication guidée : structure explicite, progression visuelle et soumission bbPress sans dépendance JavaScript. */ defined( 'ABSPATH' ) || exit; ?>
<?php if ( bbp_is_topic_edit() ) : bbp_the_topic(); endif; ?>
<form id="new-post" class="atelier-topic-form" name="new-post" method="post" action="<?php the_permalink(); ?>">
	<fieldset class="bbp-form">
		<legend><?php echo bbp_is_topic_edit() ? esc_html__( 'Modifier la source', 'bbpress' ) : esc_html__( 'Composer une nouvelle source', 'bbpress' ); ?></legend>
		<p class="atelier-topic-form__step"><span>01</span><label for="bbp_topic_title">Titre précis de la discussion</label><small>Formulez la question, le fait ou la décision à retrouver plus tard.</small><input type="text" id="bbp_topic_title" value="<?php bbp_form_topic_title(); ?>"  size="40" name="bbp_topic_title" maxlength="120" required /></p>
		<?php do_action( 'bbp_theme_before_topic_form_content' ); ?>
		<div class="atelier-topic-form__step"><span>02</span><label for="bbp_topic_content">Contexte, éléments vérifiables et question</label><small>Indiquez les hypothèses, les sources ou les limites utiles à la lecture.</small><?php bbp_the_content( array( 'context' => 'topic' ) ); ?></div>
		<?php do_action( 'bbp_theme_after_topic_form_content' ); ?>
		<?php if ( ! bbp_is_single_forum() ) : ?><p class="atelier-topic-form__step"><span>03</span><label for="bbp_forum_id">Espace de discussion</label><small>Choisissez la catégorie la plus utile à la future lecture.</small><?php bbp_dropdown( array( 'select_id' => 'bbp_forum_id', 'show_none' => false, 'selected' => bbp_get_form_topic_forum() ) ); ?></p><?php endif; ?>
		<div class="atelier-topic-form__submit"><p>La publication crée une URL canonique et une archive de discussion publique.</p><button type="submit"  id="bbp_topic_submit" name="bbp_topic_submit">Publier la discussion</button></div>
		<?php bbp_topic_form_fields(); ?>
	</fieldset>
</form>
