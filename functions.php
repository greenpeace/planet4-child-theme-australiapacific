<?php

/**
 * Additional code for the child theme goes in here.
 */

add_action( 'wp_enqueue_scripts', 'enqueue_child_styles', 99);

function enqueue_child_styles() {
    $css_creation = filectime(get_stylesheet_directory() . '/style.css');

    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', [], $css_creation );
}
if( function_exists('acf_add_options_page') ) {
    acf_add_options_page();
}

// SVG Support
add_filter( 'wp_check_filetype_and_ext', function($data, $file, $filename, $mimes) {
  global $wp_version;
  if ( $wp_version !== '4.7.1' ) {
     return $data;
  }
  $filetype = wp_check_filetype( $filename, $mimes );
  return [
      'ext'             => $filetype['ext'],
      'type'            => $filetype['type'],
      'proper_filename' => $data['proper_filename']
  ];
}, 10, 4 );

function cc_mime_types( $mimes ){
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
}
add_filter( 'upload_mimes', 'cc_mime_types' );

function fix_svg() {
  echo '<style type="text/css">
        .attachment-266x266, .thumbnail img {
             width: 100% !important;
             height: auto !important;
        }
        </style>';
}
add_action( 'admin_head', 'fix_svg' );

// Register ACF Long Bio Fields
if (function_exists('acf_add_local_field_group')) {

  acf_add_local_field_group(array(
      'key' => 'group_65540d4060e4e',
      'title' => 'Author Profile',
      'fields' => array(
	        // Add Long Bio Field
          array(
              'key' => 'field_65540d4fbe46e',
              'label' => 'Long Bio',
              'name' => 'long_bio',
              'type' => 'wysiwyg',
          ),
          // Add a Role field
          array(
              'key' => 'field_65541c2c2230a',
              'label' => 'Role',
              'name' => 'role',
              'type' => 'text',
          ),
          // Add a Bio Image field
          array(
              'key' => 'field_657904407246a',
              'label' => 'Bio Image',
              'name' => 'bio_image',
              'type' => 'image',
          ),
          // Add a LinkedIn Profile field
          array(
             'key' => 'field_65541d8c0b0a7',
              'label' => 'Linkedin Profile',
              'name' => 'linkedin_profile',
              'type' => 'url',
           ),
          // You can add as many fields as needed
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

