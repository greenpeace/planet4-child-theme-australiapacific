<?php

/**
 * Additional code for the child theme goes in here.
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

add_action('wp_enqueue_scripts', 'register_custom_script');
