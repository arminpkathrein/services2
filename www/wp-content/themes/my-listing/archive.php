<?php get_header();

global $wp_query;
c27()->get_section('blog-feed', [
	'paged' => ( get_query_var('paged') ) ? get_query_var('paged') : 1,
	'query' => $wp_query,
	'show_title' => false,
	]);

get_footer();