<?php
/**
 * PFC Media — uploads bbPress sécurisés, conversion AVIF et galerie accessible.
 */
defined( 'ABSPATH' ) || exit;

final class PFC_Media {
	const MAX_FILES = 3;
	const MAX_BYTES = 5242880;
	const DAILY_LIMIT = 10;
	const MAX_PIXELS = 64000000;

	public static function init(): void {
		add_action( 'bbp_new_topic_pre_insert', array( __CLASS__, 'guard_submission' ), 10, 3 );
		add_action( 'bbp_new_reply_pre_insert', array( __CLASS__, 'guard_submission' ), 10, 4 );
		add_action( 'bbp_new_topic', array( __CLASS__, 'process_topic' ), 20, 4 );
		add_action( 'bbp_new_reply', array( __CLASS__, 'process_reply' ), 20, 5 );
		add_action( 'before_delete_post', array( __CLASS__, 'cleanup_post_media' ), 10, 1 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 20 );
		add_shortcode( 'pfc_gallery', array( __CLASS__, 'render_gallery_shortcode' ) );
	}

	public static function enqueue(): void {
		if ( ! is_singular( array( 'topic', 'reply' ) ) && ! is_page() && ! is_post_type_archive() ) return;
		wp_enqueue_style( 'pfc-media', PFC_URL . 'assets/media.css', array(), PFC_VERSION );
		wp_enqueue_script( 'pfc-media', PFC_URL . 'assets/media.js', array(), PFC_VERSION, true );
		wp_localize_script( 'pfc-media', 'pfcMedia', array( 'maxFiles' => self::MAX_FILES, 'maxBytes' => self::MAX_BYTES, 'dailyLimit' => self::DAILY_LIMIT ) );
	}

	private static function field_name(): string {
		return 'topic' === get_post_type( get_queried_object_id() ) ? 'atelier_topic_images' : 'atelier_reply_images';
	}

	private static function files(): array {
		$name = isset( $_FILES['atelier_topic_images'] ) ? 'atelier_topic_images' : ( isset( $_FILES['atelier_reply_images'] ) ? 'atelier_reply_images' : '' );
		if ( '' === $name || ! is_array( $_FILES[ $name ]['name'] ?? null ) ) return array();
		$out = array();
		foreach ( $_FILES[ $name ]['name'] as $i => $filename ) {
			$out[] = array( 'name' => sanitize_file_name( $filename ), 'type' => (string) ( $_FILES[ $name ]['type'][ $i ] ?? '' ), 'tmp_name' => (string) ( $_FILES[ $name ]['tmp_name'][ $i ] ?? '' ), 'error' => (int) ( $_FILES[ $name ]['error'][ $i ] ?? UPLOAD_ERR_NO_FILE ), 'size' => (int) ( $_FILES[ $name ]['size'][ $i ] ?? 0 ) );
		}
		return array_values( array_filter( $out, static fn( array $file ): bool => UPLOAD_ERR_NO_FILE !== $file['error'] ) );
	}

	private static function fail( string $code, string $message ): void {
		if ( function_exists( 'bbp_add_error' ) ) bbp_add_error( $code, $message );
	}

	public static function guard_submission(): void {
		$files = self::files();
		if ( ! $files ) return;
		if ( ! is_user_logged_in() || ! current_user_can( 'upload_files' ) ) { self::fail( 'pfc_media_permission', 'Vous n’êtes pas autorisé à ajouter une image.' ); return; }
		if ( ! isset( $_POST['pfc_media_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pfc_media_nonce'] ) ), 'pfc_media_upload' ) ) { self::fail( 'pfc_media_nonce', 'La session du formulaire a expiré. Rechargez la page puis réessayez.' ); return; }
		if ( count( $files ) > self::MAX_FILES ) { self::fail( 'pfc_media_count', sprintf( 'Vous pouvez ajouter %d images maximum par publication.', self::MAX_FILES ) ); return; }
		$used = self::daily_count( get_current_user_id() );
		if ( $used + count( $files ) > self::DAILY_LIMIT ) { self::fail( 'pfc_media_daily_limit', sprintf( 'Votre quota quotidien est dépassé. Il vous reste %d image(s).', max( 0, self::DAILY_LIMIT - $used ) ) ); return; }
		foreach ( $files as $file ) {
			if ( UPLOAD_ERR_OK !== $file['error'] || $file['size'] < 1 || $file['size'] > self::MAX_BYTES ) { self::fail( 'pfc_media_size', 'Chaque image doit peser entre 1 octet et 5 Mio.' ); continue; }
			$check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], array( 'jpg|jpeg|jpe' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp' ) );
			if ( empty( $check['type'] ) || empty( $check['ext'] ) ) { self::fail( 'pfc_media_type', 'Format refusé. Utilisez JPEG, PNG ou WebP.' ); continue; }
			$size = @getimagesize( $file['tmp_name'] );
			if ( ! $size || ( $size[0] * $size[1] ) > self::MAX_PIXELS ) self::fail( 'pfc_media_dimensions', 'Les dimensions de cette image dépassent la limite autorisée.' );
		}
	}

	private static function daily_count( int $user_id ): int { return gmdate( 'Y-m-d' ) === (string) get_user_meta( $user_id, '_pfc_media_day', true ) ? (int) get_user_meta( $user_id, '_pfc_media_count', true ) : 0; }
	private static function increment_daily( int $user_id, int $count ): void { update_user_meta( $user_id, '_pfc_media_day', gmdate( 'Y-m-d' ) ); update_user_meta( $user_id, '_pfc_media_count', self::daily_count( $user_id ) + $count ); }

	public static function process_topic( int $topic_id ): void { self::process_post( $topic_id ); }
	public static function process_reply( int $reply_id ): void { self::process_post( $reply_id ); }
	private static function process_post( int $post_id ): void {
		$files = self::files(); if ( ! $files || ! get_post( $post_id ) ) return;
		$ids = array();
		require_once ABSPATH . 'wp-admin/includes/file.php'; require_once ABSPATH . 'wp-admin/includes/media.php'; require_once ABSPATH . 'wp-admin/includes/image.php';
		foreach ( $files as $file ) { $id = self::store( $file, $post_id ); if ( $id ) $ids[] = $id; }
		if ( $ids ) { self::increment_daily( (int) get_post_field( 'post_author', $post_id ), count( $ids ) ); $content = (string) get_post_field( 'post_content', $post_id ); wp_update_post( array( 'ID' => $post_id, 'post_content' => $content . "\n\n" . self::gallery_html( $ids ) ) ); update_post_meta( $post_id, '_pfc_media_ids', $ids ); }
	}

	private static function store( array $file, int $parent_id ): int {
		$upload = wp_handle_sideload( $file, array( 'test_form' => false, 'mimes' => array( 'jpg|jpeg|jpe' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp' ) ) );
		if ( isset( $upload['error'] ) ) return 0;
		$source = $upload['file']; $avif = preg_replace( '/\.[^.]+$/', '.avif', $source ); $editor = wp_get_image_editor( $source ); $converted = false;
		if ( ! is_wp_error( $editor ) ) { $saved = $editor->save( $avif, 'image/avif' ); if ( ! is_wp_error( $saved ) && is_readable( $avif ) && filesize( $avif ) > 0 ) { $converted = true; } }
		$final = $converted ? $avif : $source; $type = $converted ? 'image/avif' : ( wp_check_filetype( $final )['type'] ?: 'application/octet-stream' );
		$attachment = array( 'post_mime_type' => $type, 'post_title' => sanitize_text_field( pathinfo( $file['name'], PATHINFO_FILENAME ) ), 'post_content' => '', 'post_status' => 'inherit', 'post_parent' => $parent_id );
		$id = wp_insert_attachment( $attachment, $final, $parent_id ); if ( ! $id || is_wp_error( $id ) ) { @unlink( $avif ); return 0; }
		update_post_meta( $id, '_pfc_media_original_deleted', false ); update_post_meta( $id, '_pfc_media_converted_avif', $converted ); update_post_meta( $id, '_pfc_media_alt', sanitize_text_field( pathinfo( $file['name'], PATHINFO_FILENAME ) ) );
		$meta = wp_generate_attachment_metadata( $id, $final ); if ( is_array( $meta ) ) wp_update_attachment_metadata( $id, $meta );
		if ( $converted ) @unlink( $source );
		return (int) $id;
	}

	private static function gallery_html( array $ids ): string {
		$label = 'Galerie de la contribution'; $html = '<div class="pfc-media-carousel" role="region" aria-label="' . esc_attr( $label ) . '" data-count="' . count( $ids ) . '"><div class="pfc-media-carousel__track">';
		foreach ( $ids as $i => $id ) { $url = wp_get_attachment_image_url( $id, 'large' ); $alt = get_post_meta( $id, '_pfc_media_alt', true ) ?: 'Image de la contribution'; if ( ! $url ) continue; $dimensions = wp_get_attachment_image_src( $id, 'large' ); $width = is_array( $dimensions ) ? (int) $dimensions[1] : 0; $height = is_array( $dimensions ) ? (int) $dimensions[2] : 0; $html .= '<figure class="pfc-media-slide" data-index="' . (int) $i . '"><img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '" loading="' . ( 0 === $i ? 'eager' : 'lazy' ) . '"' . ( $width ? ' width="' . $width . '"' : '' ) . ( $height ? ' height="' . $height . '"' : '' ) . ' /></figure>'; }
		$html .= '</div>'; if ( count( $ids ) > 1 ) $html .= '<div class="pfc-media-carousel__controls"><button type="button" class="pfc-media-prev" aria-label="Image précédente">←</button><span class="pfc-media-status" aria-live="polite">1 sur ' . count( $ids ) . '</span><button type="button" class="pfc-media-next" aria-label="Image suivante">→</button></div>'; return $html . '</div>';
	}
	public static function render_gallery_shortcode( array $atts ): string { return ''; }
	public static function cleanup_post_media( int $post_id ): void { $ids = (array) get_post_meta( $post_id, '_pfc_media_ids', true ); foreach ( $ids as $id ) { if ( (int) get_post_field( 'post_parent', $id ) === $post_id ) wp_delete_attachment( (int) $id, true ); } delete_post_meta( $post_id, '_pfc_media_ids' ); }
}

PFC_Media::init();
