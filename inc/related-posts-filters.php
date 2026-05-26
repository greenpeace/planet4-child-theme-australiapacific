<?php

/**
 * Related posts: always uses the current post's own tags for the p4/related-posts block.
 *
 * @package Planet4_Child_Theme_AustraliaPacific
 */

declare(strict_types=1);

/**
 * Build taxQuery for the p4/related-posts block using the current post's own tags.
 *
<<<<<<< feature/related-posts
 * @param int $post_id WordPress post ID (unused, kept for call-site compatibility).
 * @param \P4\MasterTheme\Post $timber_post Timber post.
=======
 * @param string $hook Current admin page.
 */
function ap_related_posts_filters_admin_assets(string $hook): void
{
    if ($hook !== 'post.php' && $hook !== 'post-new.php') {
        return;
    }

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (! $screen || $screen->post_type !== 'post') {
        return;
    }

    $rel = '/assets/css/related-posts-filters-admin.css';
    $path = get_stylesheet_directory() . $rel;
    $ver = file_exists($path) ? (string) filemtime($path) : null;

    wp_enqueue_style(
        'ap-related-posts-filters-admin',
        get_stylesheet_directory_uri() . $rel,
        [],
        $ver
    );
}

add_action('admin_enqueue_scripts', 'ap_related_posts_filters_admin_assets');

/**
 * Normalize stored CMB2 / meta term ID list to a clean int array.
 *
 * @param mixed $value Raw meta value.
 * @return int[]
 */
function ap_normalize_related_posts_term_ids($value): array
{
    if ($value === null || $value === '' || $value === false) {
        return [];
    }

    if (is_string($value)) {
        $value = maybe_unserialize($value);
    }

    if (is_array($value)) {
        return array_values(array_unique(array_filter(array_map('intval', $value))));
    }

    return [(int) $value];
}

/**
 * Options for tag multicheck (term ID => label). Stored as meta only; does not change post tags.
 *
 * @return array<int, string>
 */
function ap_related_posts_tag_options(): array
{
    $terms = get_terms(
        [
            'taxonomy' => 'post_tag',
            'hide_empty' => false,
            'orderby' => 'name',
        ]
    );

    if (is_wp_error($terms) || ! is_array($terms)) {
        return [];
    }

    return array_column($terms, 'name', 'term_id');
}

/**
 * Meta box: related-posts filters (core API).
 * Note: in Gutenberg this renders underneath the editor, which is intended here.
 */
function ap_add_related_posts_filters_metabox(): void
{
    add_meta_box(
        'ap_related_posts_filters',
        __('Related posts filters', 'planet4-child-theme-australiapacific'),
        'ap_render_related_posts_filters_metabox',
        'post',
        'normal',
        'default'
    );
}

add_action('add_meta_boxes', 'ap_add_related_posts_filters_metabox');

/**
 * @param \WP_Post $post Current post.
 */
function ap_render_related_posts_filters_metabox($post): void
{
    wp_nonce_field('ap_save_related_posts_filters', 'ap_related_posts_filters_nonce');

    $selected_tags = ap_normalize_related_posts_term_ids(
        get_post_meta($post->ID, 'ap_related_posts_tag_ids', true)
    );
    $tag_options = ap_related_posts_tag_options();

    echo '<div class="ap-related-posts-filters-metabox">';
    echo '<p class="ap-related-posts-filters-metabox__heading"><strong>' . esc_html__('Tags', 'planet4-child-theme-australiapacific') . '</strong></p>';
    echo '<p class="ap-related-posts-filters-metabox__description description">';
    esc_html_e('Leave all unchecked to use this post\'s tags.', 'planet4-child-theme-australiapacific');
    echo '</p>';

    echo '<div class="ap-related-posts-checkboxes">';
    foreach ($tag_options as $term_id => $label) {
        printf(
            '<label style="display:block;margin:4px 0;"><input type="checkbox" name="ap_related_posts_tag_ids[]" value="%d" %s /> %s</label>',
            (int) $term_id,
            checked(in_array((int) $term_id, $selected_tags, true), true, false),
            esc_html($label)
        );
    }
    echo '</div>';

    echo '</div>';
}

/**
 * @param int     $post_id Post ID.
 * @param \WP_Post $post   Post object.
 */
function ap_save_related_posts_filters(int $post_id, $post): void
{
    if (! isset($_POST['ap_related_posts_filters_nonce'])
        || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ap_related_posts_filters_nonce'])), 'ap_save_related_posts_filters')
    ) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (! current_user_can('edit_post', $post_id)) {
        return;
    }

    if (! $post || 'post' !== $post->post_type) {
        return;
    }

    if (wp_is_post_revision($post_id)) {
        return;
    }

    $tag_ids = isset($_POST['ap_related_posts_tag_ids']) && is_array($_POST['ap_related_posts_tag_ids'])
        ? array_map('intval', (array) wp_unslash($_POST['ap_related_posts_tag_ids']))
        : [];
    $tag_ids = array_values(array_unique(array_filter($tag_ids)));

    if (! empty($tag_ids)) {
        update_post_meta($post_id, 'ap_related_posts_tag_ids', $tag_ids);
    } else {
        delete_post_meta($post_id, 'ap_related_posts_tag_ids');
    }
}

add_action('save_post_post', 'ap_save_related_posts_filters', 10, 2);

/**
 * Build taxQuery arrays for the p4/related-posts block (used by single.php).
 *
 * @param int $post_id WordPress post ID.
 * @param \P4\MasterTheme\Post $timber_post Timber post (used for fallback category terms).
>>>>>>> main
 * @return array<string, int[]>
 */
function ap_build_related_posts_tax_query(int $post_id, $timber_post): array
{
<<<<<<< feature/related-posts
    $tag_ids = [];
    foreach ($timber_post->terms('post_tag') as $tag) {
        $tag_ids[] = (int) $tag->id;
    }
    return ['post_tag' => $tag_ids];
}
=======
    $custom_tags = ap_normalize_related_posts_term_ids(
        get_post_meta($post_id, 'ap_related_posts_tag_ids', true)
    );

    if (!empty($custom_tags)) {
        $tag_id_array = $custom_tags;
    } else {
        $tag_id_array = [];
        foreach ($timber_post->terms('post_tag') as $tag) {
            $tag_id_array[] = (int) $tag->id;
        }
    }

    return ['post_tag' => $tag_id_array];
}

// The p4/related-posts block rendering and taxQuery filtering is handled by the master theme’s
// render_related_posts_block callback, which reads the taxQuery passed from single.php via
// ap_build_related_posts_tax_query. No render_block override is needed here.
>>>>>>> main
