<?php
/** Atelier: point d’entrée WordPress pour les catégories de discussion bbPress. */
defined( 'ABSPATH' ) || exit;
get_header();
if ( function_exists( 'bbp_get_template_part' ) ) {
	bbp_get_template_part( 'content', 'single-forum' );
} else {
	while ( have_posts() ) { the_post(); the_content(); }
}
get_footer();
