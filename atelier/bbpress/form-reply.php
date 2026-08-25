<?php
/**
 * Atelier — compositeur de réponse : surface sombre, champ unique lisible,
 * champs bbPress conservés pour une publication réelle et accessible.
 */
defined( 'ABSPATH' ) || exit;
?>
<form id="new-post" name="new-post" method="post" class="bbp-reply-form atelier-native-reply-form">
	<fieldset class="bbp-form">
		<legend class="screen-reader-text"><?php esc_html_e( 'Ajouter une réponse', 'atelier' ); ?></legend>
		<div class="atelier-native-reply-form__field">
			<label for="bbp_reply_content">Votre réponse</label>
			<textarea id="bbp_reply_content" name="bbp_reply_content" tabindex="1" placeholder="Ajoutez un contexte, une expérience ou une source utile…" required></textarea>
		</div>
		<?php if ( bbp_allow_topic_tags() ) : ?>
			<div class="atelier-native-reply-form__field atelier-native-reply-form__tags"><label for="bbp_topic_tags">Étiquettes <span>(facultatif)</span></label><input type="text" id="bbp_topic_tags" name="bbp_topic_tags" value="" tabindex="2" placeholder="méthode, source, pratique"></div>
		<?php endif; ?>
		<?php if ( bbp_is_subscriptions_active() && ! bbp_is_anonymous() ) : ?>
			<label class="atelier-native-reply-form__subscribe"><input type="checkbox" id="bbp_topic_subscription" name="bbp_topic_subscription" value="bbp_subscribe" tabindex="3"> <span>Me prévenir des réponses par e-mail.</span></label>
		<?php endif; ?>
		<div class="atelier-native-reply-form__submit"><p>Votre nom, votre rang et la date de publication seront associés clairement à votre contribution.</p><button type="submit" id="bbp_reply_submit" tabindex="4">Publier la réponse <span aria-hidden="true">↗</span></button></div>
		<?php bbp_reply_form_fields(); ?>
	</fieldset>
</form>
