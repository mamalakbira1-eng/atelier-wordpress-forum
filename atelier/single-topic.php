<?php
/** Atelier: point d’entrée WordPress dédié aux sujets bbPress, sans wrapper générique. */
defined( 'ABSPATH' ) || exit;
get_header();
if ( function_exists( 'bbp_get_template_part' ) ) {
	bbp_get_template_part( 'content', 'single-topic' );
} else {
	while ( have_posts() ) { the_post(); the_content(); }
}
get_footer();
