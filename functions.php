<?php

/**
 * Additional code for the child theme goes in here.
 */

 /**
  * Adds custom styles to their relevant pages
  * Authored by GPI Team
  */
function enqueue_child_styles() {
    $css_creation = filectime(get_stylesheet_directory() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', [], $css_creation);
}

/**
 * Load custom JS for front-page which allows logo & navbar styles to change during scrolling
 * Overview: https://github.com/greenpeace/planet4-child-theme-australiapacific/pull/43
 */
function register_custom_script() {
    if (is_front_page()) {
        wp_enqueue_script('update-DOM-on-scroll', get_stylesheet_directory_uri() .  '/js/update-DOM-on-scroll.js');
    }
}

/**
 * Scales selected SVGs on all pages to 100% of width and height
 * Code from Marlin in Nov '23, no docs as of yet
 */
function fix_svg() {
  echo '<style type="text/css">
        .attachment-266x266, .thumbnail img {
             width: 100% !important;
             height: auto !important;
        }
        </style>';
}

/**
 * Register Advanced Custom Fields, if the plugin is installed
 * See more about ACFs: https://www.notion.so/greenpeaceaustraliapacific/P4-Planet-4-Technical-Documentation-015b1907ad574dde97b1e210da340b76?pvs=4#e930904cb2ef4470a83266ae0c2a5b0f
 */
if (function_exists('acf_add_local_field_group')) {
  
  acf_add_local_field_group(array(
    'key' => 'group_65af4ec31be1a',
    'title' => 'Author Profile',
    'fields' => array(
      // Add a Bio Image field
      array(
        'key' => 'field_65af4ecfd296a',
        'label' => 'Bio Image',
        'name' => 'bio_image',
        'type' => 'image',
      ),
      // Add a Role field
      array(
        'key' => 'field_65af4f6dd296b',
        'label' => 'Role',
        'name' => 'role',
        'type' => 'text',
      ),
      // Add a LinkedIn Profile field
      array(
        'key' => 'field_65af4f81d296c',
        'label' => 'Linkedin Profile',
        'name' => 'linkedin_profile',
        'type' => 'url',
      ),
      // Add a Twitter Profile field
      array(
        'key' => 'field_65af4f8ed296d',
        'label' => 'Twitter Profile',
        'name' => 'twitter_profile',
        'type' => 'url',
      ),
      // Add Long Bio Field
      array(
        'key' => 'field_65af4f96d296e',
        'label' => 'Long Bio',
        'name' => 'long_bio',
        'type' => 'wysiwyg',
      ),
    ),
    'location' => array(
      array(
        array(
          'param' => 'user_form',
          'operator' => '==',
          'value' => 'edit',
        ),
      ),
    ),
  ));
}


/**
 * Adds theme support for Custom Post Type Templates
 * Read more about Custom Post Type Templates: https://www.notion.so/greenpeaceaustraliapacific/P4-Planet-4-Technical-Documentation-015b1907ad574dde97b1e210da340b76?pvs=4#427e7a7ea5a34e63947261f9c59614a3
 */
function add_block_template_part_support() {
  add_theme_support( 'block-template-parts' );
}

/**
 * Retrieves content for templates using block_template_part
 * Necessary for Custom Post Type Templates
 */
function get_template_content($name = null){
  ob_start();
  block_template_part($name);
  return ob_get_clean();
}

add_action( 'wp_enqueue_scripts', 'enqueue_child_styles', 99 );
add_action( 'admin_head', 'fix_svg' );
add_action( 'after_setup_theme', 'add_block_template_part_support' );
add_action( 'wp_enqueue_scripts', 'register_custom_script' );
