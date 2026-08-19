<?php

/**
 * Additional code for the child theme goes in here.
 * TODO: Document each function and action within this code.
 */

require_once __DIR__ . '/inc/related-posts-filters.php';

add_action('wp_enqueue_scripts', 'enqueue_child_styles', 99);

function enqueue_child_styles() {
    $css_creation = filectime(get_stylesheet_directory() . '/style.min.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.min.css', [], $css_creation);
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

// Allow additional Gutenberg core blocks on top of what the master theme permits.
// Uses allowed_block_types_all (replaces deprecated allowed_block_types hook).
function p4_child_theme_gpap_add_allowed_blocks( $allowed_block_types, $context ) {
    if ($allowed_block_types === true) {
        return true;
    }
    $allowed = is_array($allowed_block_types) ? $allowed_block_types : [];
    $extra = [
        'core/post-title',
        'core/post-excerpt',
        'core/post-featured-image',
        'core/post-content',
        'core/post-author',
        'core/post-date',
        'core/post-modified-date',
        'core/post-categories',
        'core/post-tags',
    ];
    return array_unique(array_merge($allowed, $extra));
}
add_filter('allowed_block_types_all', 'p4_child_theme_gpap_add_allowed_blocks', 11, 2);

// Maintenance mode: serves the page using the Maintenance template to visitors,
// while anyone who can edit posts keeps browsing the real site.
// Toggle with: wp option update gpap_maintenance_mode 1
function gpap_maintenance_page_id(): ?int {
    $pages = get_posts([
        'post_type' => 'page',
        'posts_per_page' => 1,
        'post_status' => ['publish', 'private'],
        'meta_key' => '_wp_page_template',
        'meta_value' => 'page-templates/maintenance.php',
        'fields' => 'ids',
    ]);

    return $pages[0] ?? null;
}

function gpap_maintenance_mode(): void {
    if (is_admin() || wp_doing_ajax() || !get_option('gpap_maintenance_mode')) {
        return;
    }

    if (current_user_can('edit_posts')) {
        return;
    }

    $page_id = gpap_maintenance_page_id();

    if (!$page_id || get_queried_object_id() === $page_id) {
        return;
    }

    global $wp_query, $post;

    $wp_query = new WP_Query(['page_id' => $page_id]);
    $post = get_post($page_id);
    setup_postdata($post);

    status_header(503);
    header('Retry-After: 3600');
    nocache_headers();

    include get_stylesheet_directory() . '/page-templates/maintenance.php';
    exit;
}

add_action('template_redirect', 'gpap_maintenance_mode', 0);

add_action('admin_init', 'gpap_maintenance_setting');

function gpap_maintenance_setting(): void {
    register_setting('reading', 'gpap_maintenance_mode', [
        'type' => 'boolean',
        'sanitize_callback' => fn($value) => $value ? 1 : 0,
        'default' => 0,
    ]);

    add_settings_field(
        'gpap_maintenance_mode',
        __('Maintenance mode', 'planet4-child-theme-australiapacific'),
        'gpap_maintenance_setting_field',
        'reading',
        'default'
    );
}

function gpap_maintenance_setting_field(): void {
    $page_id = gpap_maintenance_page_id();
    ?>
    <label for="gpap_maintenance_mode">
        <input
            type="checkbox"
            name="gpap_maintenance_mode"
            id="gpap_maintenance_mode"
            value="1"
            <?php checked(get_option('gpap_maintenance_mode')); ?>
            <?php disabled(!$page_id); ?>
        >
        <?php esc_html_e('Show the maintenance page to visitors', 'planet4-child-theme-australiapacific'); ?>
    </label>
    <p class="description">
        <?php if ($page_id) : ?>
            <?php
            printf(
                /* translators: %s: link to the maintenance page. */
                esc_html__('Visitors get %s with a 503 status. Anyone who can edit posts still sees the site.', 'planet4-child-theme-australiapacific'),
                '<a href="' . esc_url((string) get_permalink($page_id)) . '">' . esc_html(get_the_title($page_id)) . '</a>'
            );
            ?>
        <?php else : ?>
            <?php esc_html_e('Publish a page using the Maintenance template first.', 'planet4-child-theme-australiapacific'); ?>
        <?php endif; ?>
    </p>
    <?php
}

function gpap_maintenance_purge_cache(): void {
    global $nginx_purger;

    if (class_exists(\Cloudflare\APO\WordPress\Hooks::class)) {
        (new \Cloudflare\APO\WordPress\Hooks())->purgeCacheEverything();
    }

    if (is_object($nginx_purger) && method_exists($nginx_purger, 'purge_all')) {
        $nginx_purger->purge_all();
    }
}

add_action('add_option_gpap_maintenance_mode', 'gpap_maintenance_purge_cache');
add_action('update_option_gpap_maintenance_mode', 'gpap_maintenance_purge_cache');
