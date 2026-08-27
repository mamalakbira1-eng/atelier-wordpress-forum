<?php
/**
 * PFC — inscription Atelier : parcours éditorial, validation e-mail par code,
 * compte inactif tant que l’adresse n’est pas confirmée.
 */
defined( 'ABSPATH' ) || exit;

final class PFC_Registration {
	private const META_PENDING = '_pfc_email_pending';
	private const META_HASH    = '_pfc_email_code_hash';
	private const META_EXPIRES = '_pfc_email_code_expires';
	private const META_ATTEMPTS = '_pfc_email_code_attempts';
		private const META_RESEND_AT = '_pfc_email_code_resend_at';
		private const REGISTER_RATE_LIMIT = 5;

	public static function init(): void {
		add_action( 'login_init', array( __CLASS__, 'route_login' ) );
		add_filter( 'authenticate', array( __CLASS__, 'block_unverified_user' ), 30, 3 );
			add_filter( 'login_body_class', array( __CLASS__, 'login_body_class' ) );
			add_action( 'login_header', array( __CLASS__, 'open_standard_login_landmark' ), 1 );
			add_action( 'login_footer', array( __CLASS__, 'close_standard_login_landmark' ), 99 );
			add_action( 'wp_ajax_nopriv_pfc_check_username', array( __CLASS__, 'ajax_check_username' ) );
		add_action( 'wp_ajax_pfc_check_username', array( __CLASS__, 'ajax_check_username' ) );
	}

	public static function route_login(): void {
		$action = sanitize_key( $_REQUEST['action'] ?? '' );
		if ( 'register' === $action ) {
			if ( 'POST' === strtoupper( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
				self::handle_register();
			}
			self::render_register();
		}
			if ( 'verify' === $action ) {
				if ( 'POST' === strtoupper( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
					self::handle_verify();
				}
				self::render_verify();
		}
		if ( 'resend' === $action && 'POST' === strtoupper( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			self::handle_resend();
		}
	}

	public static function login_body_class( array $classes ): array {
		$action = sanitize_key( $_REQUEST['action'] ?? '' );
		if ( in_array( $action, array( 'register', 'verify', 'resend' ), true ) ) $classes[] = 'atelier-registration-route';
		return $classes;
	}

	/**
	 * WordPress core does not wrap wp-login.php in a main landmark. Keep one
	 * outer landmark around both core login chrome and Atelier auth views.
	 */
	public static function open_standard_login_landmark(): void {
		echo '<main id="wp-login-main" aria-label="Connexion WordPress">';
	}

	public static function close_standard_login_landmark(): void {
		echo '</main>';
	}

	public static function ajax_check_username(): void {
		$nonce = sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ?? '' ) );
		if ( ! wp_verify_nonce( $nonce, 'pfc_username_check' ) ) {
			wp_send_json_error( array( 'message' => 'Vérification expirée.' ), 403 );
		}
		$login = sanitize_user( wp_unslash( $_REQUEST['username'] ?? '' ), true );
		if ( '' === $login || ! validate_username( $login ) ) {
			wp_send_json_success( array( 'available' => false, 'message' => 'Ce pseudo n’est pas utilisable.' ) );
		}
		$available = ! username_exists( $login );
		$suggestions = array();
		if ( ! $available ) {
			for ( $i = 2; $i <= 4; $i++ ) {
				$candidate = $login . $i;
				if ( ! username_exists( $candidate ) ) {
					$suggestions[] = $candidate;
				}
			}
		}
		wp_send_json_success( array( 'available' => $available, 'message' => $available ? 'Pseudo disponible.' : 'Ce pseudo est déjà utilisé.', 'suggestions' => $suggestions ) );
	}

	public static function block_unverified_user( $user, string $username, string $password ) {
		if ( $user instanceof WP_User && get_user_meta( $user->ID, self::META_PENDING, true ) ) {
			return new WP_Error( 'pfc_email_pending', __( 'Confirmez votre adresse e-mail avant de vous connecter.', 'premium-forum-core' ) );
		}
		return $user;
	}

	private static function handle_register(): void {
		if ( ! isset( $_POST['pfc_register_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pfc_register_nonce'] ) ), 'pfc_register' ) ) {
			self::render_register( array( 'error' => __( 'La session du formulaire a expiré. Rechargez la page.', 'premium-forum-core' ) ) );
		}
		if ( ! empty( $_POST['pfc_website'] ?? '' ) ) {
			self::render_register( array( 'error' => __( 'Votre demande n’a pas pu être traitée. Rechargez la page avant de réessayer.', 'premium-forum-core' ) ) );
		}
		$first = sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) );
		$last  = sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) );
		$email = sanitize_email( wp_unslash( $_POST['user_email'] ?? '' ) );
		$login = sanitize_user( wp_unslash( $_POST['user_login'] ?? '' ), true );
		$pass  = (string) ( $_POST['user_pass'] ?? '' );
		$pass2 = (string) ( $_POST['user_pass_confirm'] ?? '' );
		$errors = array();
		if ( '' === $first || '' === $last ) $errors[] = __( 'Indiquez votre prénom et votre nom.', 'premium-forum-core' );
		if ( ! is_email( $email ) ) $errors[] = __( 'Indiquez une adresse e-mail valide.', 'premium-forum-core' );
		if ( email_exists( $email ) ) $errors[] = __( 'Cette adresse e-mail est déjà utilisée.', 'premium-forum-core' );
		if ( '' === $login || ! validate_username( $login ) || username_exists( $login ) ) $errors[] = __( 'Choisissez un pseudo disponible.', 'premium-forum-core' );
		if ( strlen( $pass ) < 10 ) $errors[] = __( 'Le mot de passe doit contenir au moins 10 caractères.', 'premium-forum-core' );
		if ( $pass !== $pass2 ) $errors[] = __( 'Les deux mots de passe ne correspondent pas.', 'premium-forum-core' );
		if ( ! empty( $errors ) ) self::render_register( array( 'error' => implode( ' ', $errors ), 'first' => $first, 'last' => $last, 'email' => $email, 'login' => $login ) );
		if ( ! self::allow_register_request() ) {
			self::render_register( array( 'error' => __( 'Trop de demandes ont été envoyées depuis cette connexion. Réessayez dans une heure.', 'premium-forum-core' ) ) );
		}
		$user_id = wp_insert_user( array( 'user_login' => $login, 'user_pass' => $pass, 'user_email' => $email, 'first_name' => $first, 'last_name' => $last, 'display_name' => trim( $first . ' ' . $last ), 'role' => 'subscriber' ) );
		if ( is_wp_error( $user_id ) ) self::render_register( array( 'error' => $user_id->get_error_message(), 'first' => $first, 'last' => $last, 'email' => $email, 'login' => $login ) );
		$sent = false;
		try {
			$code = function_exists( 'random_int' ) ? (string) random_int( 100000, 999999 ) : (string) wp_rand( 100000, 999999 );
			update_user_meta( $user_id, self::META_PENDING, 1 );
				update_user_meta( $user_id, self::META_HASH, wp_hash_password( $code ) );
				update_user_meta( $user_id, self::META_EXPIRES, time() + 15 * MINUTE_IN_SECONDS );
				update_user_meta( $user_id, self::META_ATTEMPTS, 0 );
					$sent = self::send_verification_mail( $email, __( 'Votre code de confirmation Atelier', 'premium-forum-core' ), sprintf( __( 'Bonjour %1$s,\n\nVotre code de confirmation Atelier est : %2$s\n\nIl est valable 15 minutes. Si vous n’êtes pas à l’origine de cette demande, ignorez ce message.', 'premium-forum-core' ), $first, $code ) );
				} catch ( \Throwable $exception ) {
					error_log( 'PFC registration mail setup failed.' );
			}
			if ( ! $sent ) {
				try {
					if ( ! function_exists( 'wp_delete_user' ) ) {
						require_once ABSPATH . 'wp-admin/includes/user.php';
					}
					wp_delete_user( $user_id );
				} catch ( \Throwable $exception ) {
					error_log( 'PFC registration cleanup failed.' );
			}
			self::render_register( array( 'error' => __( 'L’e-mail n’a pas pu être envoyé. Aucun compte n’a été conservé ; réessayez plus tard.', 'premium-forum-core' ), 'first' => $first, 'last' => $last, 'email' => $email, 'login' => $login ) );
		}
		self::render_verify( array( 'success' => __( 'Votre code a été envoyé. Consultez votre boîte de réception pour confirmer votre adresse.', 'premium-forum-core' ), 'email' => $email ) );
	}

	private static function handle_verify(): void {
		if ( ! self::valid_nonce( 'pfc_verify_nonce', 'pfc_verify' ) ) self::render_verify( array( 'error' => __( 'La session du formulaire a expiré. Rechargez la page.', 'premium-forum-core' ) ) );
			$email = sanitize_email( wp_unslash( $_POST['user_email'] ?? '' ) );
			$code  = preg_replace( '/\D+/', '', (string) ( $_POST['verification_code'] ?? '' ) );
			$user  = get_user_by( 'email', $email );
			if ( ! $user || ! get_user_meta( $user->ID, self::META_PENDING, true ) ) self::render_verify( array( 'error' => __( 'L’adresse ou le code ne permet pas de confirmer cette demande.', 'premium-forum-core' ), 'email' => $email ) );
		$attempts = (int) get_user_meta( $user->ID, self::META_ATTEMPTS, true );
		if ( $attempts >= 5 ) self::render_verify( array( 'error' => __( 'Trop de tentatives. Demandez un nouveau code.', 'premium-forum-core' ), 'email' => $email ) );
		update_user_meta( $user->ID, self::META_ATTEMPTS, $attempts + 1 );
		$hash = (string) get_user_meta( $user->ID, self::META_HASH, true );
		if ( time() > (int) get_user_meta( $user->ID, self::META_EXPIRES, true ) || ! wp_check_password( $code, $hash ) ) self::render_verify( array( 'error' => __( 'Ce code est invalide ou expiré.', 'premium-forum-core' ), 'email' => $email ) );
		delete_user_meta( $user->ID, self::META_PENDING );
		delete_user_meta( $user->ID, self::META_HASH );
		delete_user_meta( $user->ID, self::META_EXPIRES );
		delete_user_meta( $user->ID, self::META_ATTEMPTS );
		wp_safe_redirect( add_query_arg( array( 'checkemail' => 'confirmed', 'atelier_validation' => 'registration_confirmed' ), wp_login_url() ) );
		exit;
	}

	private static function handle_resend(): void {
		if ( ! self::valid_nonce( 'pfc_resend_nonce', 'pfc_resend' ) ) self::render_verify( array( 'error' => __( 'La session du formulaire a expiré. Rechargez la page.', 'premium-forum-core' ) ) );
		$email = sanitize_email( wp_unslash( $_POST['user_email'] ?? '' ) );
		$user = get_user_by( 'email', $email );
			if ( ! $user || ! get_user_meta( $user->ID, self::META_PENDING, true ) ) self::render_verify( array( 'error' => __( 'L’adresse ou le code ne permet pas de confirmer cette demande.', 'premium-forum-core' ), 'email' => $email ) );
		$now = time();
		$last_resend = (int) get_user_meta( $user->ID, self::META_RESEND_AT, true );
		if ( $last_resend > 0 && $last_resend > ( $now - MINUTE_IN_SECONDS ) ) {
			self::render_verify( array( 'error' => __( 'Attendez une minute avant de demander un nouveau code.', 'premium-forum-core' ), 'email' => $email ) );
		}
		$sent = false;
		try {
			$code = function_exists( 'random_int' ) ? (string) random_int( 100000, 999999 ) : (string) wp_rand( 100000, 999999 );
				update_user_meta( $user->ID, self::META_HASH, wp_hash_password( $code ) );
				update_user_meta( $user->ID, self::META_EXPIRES, $now + 15 * MINUTE_IN_SECONDS );
				update_user_meta( $user->ID, self::META_ATTEMPTS, 0 );
					$sent = self::send_verification_mail( $email, __( 'Votre nouveau code Atelier', 'premium-forum-core' ), sprintf( __( "Votre nouveau code de confirmation Atelier est : %s\n\nIl est valable 15 minutes.", 'premium-forum-core' ), $code ) );
				} catch ( \Throwable $exception ) {
					error_log( 'PFC registration resend setup failed.' );
		}
		if ( ! $sent ) {
			self::render_verify( array( 'error' => __( 'Le nouveau code n’a pas pu être envoyé. Réessayez plus tard.', 'premium-forum-core' ), 'email' => $email ) );
		}
			update_user_meta( $user->ID, self::META_RESEND_AT, $now );
			self::render_verify( array( 'success' => __( 'Un nouveau code vient d’être envoyé.', 'premium-forum-core' ), 'email' => $email ) );
		}

			private static function send_verification_mail( string $recipient, string $subject, string $message ): bool {
			$failure_message = '';
			$listener = static function ( $error ) use ( &$failure_message ): void {
				if ( $error instanceof WP_Error ) {
					$failure_message = sanitize_text_field( $error->get_error_message() );
				}
			};
			add_action( 'wp_mail_failed', $listener );
			try {
				$sent = wp_mail( $recipient, $subject, $message, array( 'Content-Type: text/plain; charset=UTF-8' ) );
			} catch ( \Throwable $exception ) {
				$failure_message = sanitize_text_field( $exception->getMessage() );
				$sent = false;
			} finally {
				remove_action( 'wp_mail_failed', $listener );
			}
				if ( ! $sent ) {
					error_log( 'PFC registration email could not be sent.' );
				}
				return $sent;
			}

		private static function valid_nonce( string $field, string $action ): bool { return isset( $_POST[ $field ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $field ] ) ), $action ); }
	private static function allow_register_request(): bool {
		$remote_address = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
		if ( '' === $remote_address ) return true;
		$key = 'pfc_reg_rate_' . substr( hash_hmac( 'sha256', $remote_address, wp_salt( 'nonce' ) ), 0, 24 );
		$record = get_transient( $key );
		$count = is_array( $record ) ? (int) ( $record['count'] ?? 0 ) : 0;
		if ( $count >= self::REGISTER_RATE_LIMIT ) return false;
		set_transient( $key, array( 'count' => $count + 1 ), HOUR_IN_SECONDS );
		return true;
	}
	private static function login_url( string $action ): string { return add_query_arg( 'action', $action, wp_login_url() ); }

	private static function render_register( array $state = array() ): void {
		login_header( __( 'Créer un compte Atelier', 'premium-forum-core' ), '', new WP_Error( 'pfc_register', $state['error'] ?? '' ) );
		$first = esc_attr( $state['first'] ?? '' ); $last = esc_attr( $state['last'] ?? ''); $email = esc_attr( $state['email'] ?? '' ); $login = esc_attr( $state['login'] ?? '' );
		echo '<section class="atelier-auth-card atelier-register-card"><p class="atelier-kicker">Nouvelle archive membre</p><h2>Créer votre accès Atelier.</h2><p class="atelier-auth-lead">Une identité claire pour contribuer, suivre les discussions et retrouver vos sources.</p><form name="registerform" id="registerform" action="' . esc_url( self::login_url( 'register' ) ) . '" method="post" novalidate><input type="hidden" name="pfc_register_nonce" value="' . esc_attr( wp_create_nonce( 'pfc_register' ) ) . '"><div class="pfc-honeypot" aria-hidden="true"><label for="pfc_website">Site web</label><input id="pfc_website" name="pfc_website" type="text" tabindex="-1" autocomplete="off"></div><div class="atelier-form-grid"><p><label for="first_name">Prénom</label><input id="first_name" name="first_name" type="text" value="' . $first . '" autocomplete="given-name" required></p><p><label for="last_name">Nom</label><input id="last_name" name="last_name" type="text" value="' . $last . '" autocomplete="family-name" required></p></div><p><label for="user_email">Adresse e-mail</label><input id="user_email" name="user_email" type="email" value="' . $email . '" autocomplete="email" required></p><p><label for="user_login">Votre pseudo <span>(modifiable)</span></label><input id="user_login" name="user_login" type="text" value="' . $login . '" autocomplete="username" required><small id="atelier-username-hint">Nous vous proposerons un pseudo à partir de votre nom.</small><span id="atelier-username-status" class="atelier-username-status" role="status" aria-live="polite"></span><div id="atelier-username-alternatives" class="atelier-username-alternatives" hidden></div></p><div class="atelier-form-grid"><p><label for="user_pass">Mot de passe</label><input id="user_pass" name="user_pass" type="password" autocomplete="new-password" minlength="10" required></p><p><label for="user_pass_confirm">Confirmation</label><input id="user_pass_confirm" name="user_pass_confirm" type="password" autocomplete="new-password" minlength="10" required></p></div><p class="atelier-password-note">10 caractères minimum. Votre compte restera inactif jusqu’à la confirmation de l’adresse.</p><p class="submit"><button type="submit" class="button button-primary">Recevoir mon code <span>↗</span></button></p></form><p class="atelier-auth-switch">Vous avez déjà un accès ? <a href="' . esc_url( wp_login_url() ) . '">Se connecter</a></p></section><script>(function(){const f=document.getElementById("first_name"),l=document.getElementById("last_name"),u=document.getElementById("user_login"),s=document.getElementById("atelier-username-status"),a=document.getElementById("atelier-username-alternatives");let timer;function suggest(){if(!u.dataset.edited){u.value=(f.value+"."+l.value).toLowerCase().normalize("NFD").replace(/[\\u0300-\\u036f]/g,"").replace(/[^a-z0-9.]/g,"").replace(/^\\.|\\.$/g,"");}check();}function check(){clearTimeout(timer);const value=u.value.trim();a.hidden=true;a.innerHTML="";if(!value){s.textContent="";return;}s.textContent="Vérification…";timer=setTimeout(()=>{fetch("' . esc_url( admin_url( 'admin-ajax.php' ) ) . '?action=pfc_check_username&username="+encodeURIComponent(value)+"&nonce=' . esc_attr( wp_create_nonce( 'pfc_username_check' ) ) . '",{credentials:"same-origin"}).then(r=>r.json()).then(d=>{const x=d.data||{};s.textContent=x.message||"";s.className="atelier-username-status "+(x.available?"is-available":"is-taken");if(!x.available&&(x.suggestions||[]).length){a.hidden=false;a.innerHTML="Suggestions : "+x.suggestions.map(v=>"<button type=\\"button\\" data-username=\\""+v+"\\">"+v+"</button>").join("");a.querySelectorAll("button").forEach(b=>b.onclick=()=>{u.value=b.dataset.username;u.dataset.edited="1";check();});}}).catch(()=>{s.textContent="";});},280);} [f,l].forEach(e=>e.addEventListener("input",suggest));u.addEventListener("input",()=>{u.dataset.edited="1";check();});suggest();})();</script>';
		login_footer();
		exit;
	}

		private static function render_verify( array $state = array() ): void {
			login_header( __( 'Confirmer votre adresse Atelier', 'premium-forum-core' ), '', new WP_Error( 'pfc_verify', $state['error'] ?? '' ) );
			$email = esc_attr( $state['email'] ?? '' );
			$email_input = '' !== $email ? '<input type="hidden" name="user_email" value="' . $email . '">' : '<p><label for="user_email">Adresse e-mail utilisée à l’inscription</label><input id="user_email" name="user_email" type="email" autocomplete="email" required></p>';
			$success = ! empty( $state['success'] ) ? '<p class="atelier-auth-success" role="status">' . esc_html( $state['success'] ) . '</p>' : '';
			echo '<section class="atelier-auth-card atelier-verify-card"><p class="atelier-kicker">Dernier repère</p><h2>Confirmer votre adresse.</h2>' . $success . '<p class="atelier-auth-lead">Votre code à six chiffres vous attend dans votre boîte de réception. Il reste valable pendant 15 minutes.</p><form action="' . esc_url( self::login_url( 'verify' ) ) . '" method="post"><input type="hidden" name="pfc_verify_nonce" value="' . esc_attr( wp_create_nonce( 'pfc_verify' ) ) . '"><input type="hidden" name="pfc_resend_nonce" value="' . esc_attr( wp_create_nonce( 'pfc_resend' ) ) . '">' . $email_input . '<p><label for="verification_code">Code de confirmation</label><input id="verification_code" name="verification_code" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" placeholder="000000" required></p><p class="submit"><button type="submit" class="button button-primary">Valider mon adresse <span>↗</span></button></p><p class="atelier-resend-form"><button type="submit" formaction="' . esc_url( self::login_url( 'resend' ) ) . '" formnovalidate class="atelier-text-button">Renvoyer un code</button></p></form><p class="atelier-auth-switch"><a href="' . esc_url( wp_login_url() ) . '">Retourner à la connexion</a></p></section>';
		login_footer();
		exit;
	}
}
