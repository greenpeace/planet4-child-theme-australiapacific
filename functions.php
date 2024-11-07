<?php

/**
 * Additional code for the child theme goes in here.
 * TODO: Document each function and action within this code.
 */

add_action('wp_enqueue_scripts', 'enqueue_child_styles', 99);

function enqueue_child_styles() {
    $css_creation = filectime(get_stylesheet_directory() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', [], $css_creation);
}

function register_custom_script() {
    if (is_front_page()) {
        wp_enqueue_script('update-DOM-on-scroll', get_stylesheet_directory_uri() .  '/js/update-DOM-on-scroll.js');
    }
}

function fix_svg() {
  echo '<style type="text/css">
        .attachment-266x266, .thumbnail img {
             width: 100% !important;
             height: auto !important;
        }
        </style>';
}

add_action( 'admin_head', 'fix_svg' );

// Template parts theme support
function add_block_template_part_support() {
  add_theme_support( 'block-template-parts' );
}

add_action( 'after_setup_theme', 'add_block_template_part_support' );

function get_template_content($name = null){
  ob_start();
  block_template_part($name);
  return ob_get_clean();
}

add_action('wp_enqueue_scripts', 'register_custom_script');

// Allow Gutenberg core blocks
function p4_child_theme_gpap_whitelist_blocks( $allowed_blocks, $post ) {
	$allowed = is_array($allowed_blocks) ? $allowed_blocks : array();
  array_push($allowed, 'core/cover');
	array_push($allowed, 'core/post-title');
  array_push($allowed, 'core/post-excerpt');
  array_push($allowed, 'core/post-featured-image');
  array_push($allowed, 'core/post-content');
  array_push($allowed, 'core/post-author');
  array_push($allowed, 'core/post-date');
  array_push($allowed, 'core/post-modified-date');
  array_push($allowed, 'core/post-categories');
  array_push($allowed, 'core/post-tags');
	return $allowed;
}
add_filter('allowed_block_types', 'p4_child_theme_gpap_whitelist_blocks', 11, 2);
