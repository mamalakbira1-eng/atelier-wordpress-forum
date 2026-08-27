<?php
defined( 'ABSPATH' ) || exit;
$atelier_discussions_url = function_exists( 'atelier_discussions_url' ) ? atelier_discussions_url() : home_url( '/#sources-recentes' );
$atelier_spaces_url = function_exists( 'atelier_spaces_url' ) ? atelier_spaces_url() : home_url( '/#espaces' );
$atelier_methods_url = function_exists( 'atelier_methods_url' ) ? atelier_methods_url() : home_url( '/#methodes' );
$atelier_member_url = function_exists( 'atelier_member_url' ) ? atelier_member_url() : home_url( '/#sources-recentes' );
?>
<!doctype html><html <?php language_attributes(); ?>><head><meta charset="<?php bloginfo( 'charset' ); ?>"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Manrope:wght@400;600;700;800&family=Noto+Naskh+Arabic:wght@400;500;600;700&family=Noto+Sans+Arabic:wght@400;500;600;700;800&display=swap" rel="stylesheet"><?php wp_head(); ?></head><body <?php body_class(); ?>><a class="screen-reader-text" href="#main-content">Aller au contenu</a><?php wp_body_open(); ?>
<header class="atelier-header">
	        <a class="atelier-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="atelier. forum de connaissances">atelier<span>.</span> <small>forum de connaissances</small></a>
		<nav aria-label="Navigation principale"><ul><li><a href="<?php echo esc_url( $atelier_discussions_url ); ?>">Discussions</a></li><li><a href="<?php echo esc_url( $atelier_spaces_url ); ?>">Espaces</a></li><li><a href="<?php echo esc_url( $atelier_methods_url ); ?>">Méthodes</a></li></ul></nav>
		<a class="atelier-header__member" href="<?php echo esc_url( $atelier_member_url ); ?>">Mon espace <span aria-hidden="true">↗</span></a>
	<div class="atelier-header__search-wrap">
	<form id="atelier-header-search-form" class="atelier-header__search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" data-atelier-search>
		<label class="screen-reader-text" for="atelier-header-search">Rechercher dans le forum</label>
        <input id="atelier-header-search" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="Rechercher dans le forum" autocomplete="off" />
		<button type="submit" aria-label="Lancer la recherche"><span aria-hidden="true">⌕</span><span class="atelier-header__search-label">Rechercher</span><kbd>/</kbd></button>
	</form>
		<div class="atelier-search-suggestions" id="atelier-search-suggestions" role="status" aria-live="polite" hidden></div>
	</div>
</header>
