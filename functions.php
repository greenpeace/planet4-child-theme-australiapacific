<?php

/**
 * Additional code for the child theme goes in here.
 */

add_action( 'wp_enqueue_scripts', 'enqueue_child_styles', 99);

function enqueue_child_styles() {
    $css_creation = filectime(get_stylesheet_directory() . '/style.css');

    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', [], $css_creation );
    wp_enqueue_style( 'custom-style', get_stylesheet_directory_uri() . '/dist/custom.css', [], $css_creation );
}
if( function_exists('acf_add_options_page') ) {
    acf_add_options_page();
}

function register_custom_script() {
  wp_enqueue_script( 'marlin-script', get_stylesheet_directory_uri() .  '/js/scripts.js' );
}
add_action( 'wp_enqueue_scripts', 'register_custom_script' );