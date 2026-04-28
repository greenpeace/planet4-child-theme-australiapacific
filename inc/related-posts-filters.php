<?php

/**
 * Related posts filters (child theme only): optional category override for p4/related-posts.
 *
 * @package Planet4_Child_Theme_AustraliaPacific
 */

declare(strict_types=1);

/**
 * Enqueue admin styles for the related posts filters metabox.
 *
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
 * Options for category multicheck (term ID => label). Stored as meta only; does not change post categories.
 *
 * @return array<int, string>
 */
function ap_related_posts_category_options(): array
{
    $terms = get_terms(
        [
            'taxonomy' => 'category',
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

    $selected_cats = ap_normalize_related_posts_term_ids(
        get_post_meta($post->ID, 'ap_related_posts_category_ids', true)
    );
    $cat_options = ap_related_posts_category_options();

    echo '<div class="ap-related-posts-filters-metabox">';
    echo '<p class="ap-related-posts-filters-metabox__heading"><strong>' . esc_html__('Categories', 'planet4-child-theme-australiapacific') . '</strong></p>';
    echo '<p class="ap-related-posts-filters-metabox__description description">';
    esc_html_e('Leave all unchecked to use this post\'s categories.', 'planet4-child-theme-australiapacific');
    echo '</p>';

    echo '<div class="ap-related-posts-checkboxes">';
    foreach ($cat_options as $term_id => $label) {
        printf(
            '<label style="display:block;margin:4px 0;"><input type="checkbox" name="ap_related_posts_category_ids[]" value="%d" %s /> %s</label>',
            (int) $term_id,
            checked(in_array((int) $term_id, $selected_cats, true), true, false),
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

    $cat_ids = isset($_POST['ap_related_posts_category_ids']) && is_array($_POST['ap_related_posts_category_ids'])
        ? array_map('intval', (array) wp_unslash($_POST['ap_related_posts_category_ids']))
        : [];
    $cat_ids = array_values(array_unique(array_filter($cat_ids)));

    if (! empty($cat_ids)) {
        update_post_meta($post_id, 'ap_related_posts_category_ids', $cat_ids);
    } else {
        delete_post_meta($post_id, 'ap_related_posts_category_ids');
    }
}

add_action('save_post_post', 'ap_save_related_posts_filters', 10, 2);

/**
 * Build taxQuery arrays for the p4/related-posts block (used by single.php).
 *
 * @param int $post_id WordPress post ID.
 * @param \P4\MasterTheme\Post $timber_post Timber post (used for fallback category terms).
 * @return array<string, int[]>
 */
function ap_build_related_posts_tax_query(int $post_id, $timber_post): array
{
    $custom_cats = ap_normalize_related_posts_term_ids(
        get_post_meta($post_id, 'ap_related_posts_category_ids', true)
    );

    if (!empty($custom_cats)) {
        $category_id_array = $custom_cats;
    } else {
        $category_id_array = [];
        foreach ($timber_post->terms('category') as $category) {
            $category_id_array[] = (int) $category->id;
        }
    }

    return ['category' => $category_id_array];
}

/**
 * Child-theme override for rendering p4/related-posts with a real WP_Query tax_query.
 * We can’t change the master theme render callback; the core Query block expects a different taxQuery format.
 *
 * @param string $block_content Rendered HTML.
 * @param array  $block Parsed block array.
 */
function ap_override_render_related_posts_block(string $block_content, array $block): string
{
    if (($block['blockName'] ?? '') !== 'p4/related-posts') {
        return $block_content;
    }

    if (!is_singular('post')) {
        return $block_content;
    }

    $post_id = get_the_ID();
    if (!$post_id) {
        return $block_content;
    }

    // Category-only filtering.
    $category_ids = ap_normalize_related_posts_term_ids(get_post_meta($post_id, 'ap_related_posts_category_ids', true));
    if (empty($category_ids)) {
        $category_ids = array_values(array_filter(array_map('intval', wp_get_post_terms($post_id, 'category', ['fields' => 'ids']))));
    }

    $tax_query = [ 'relation' => 'AND' ];
    if (!empty($category_ids)) {
        $tax_query[] = [
            'taxonomy' => 'category',
            'field' => 'term_id',
            'terms' => $category_ids,
            'operator' => 'IN',
        ];
    }
    // No tag or post-type filtering.

    // Build "See all posts" link consistent with master behaviour.
    $see_all_link_group = '';
    $news_stories_page = (int) get_option('page_for_posts');
    if ($news_stories_page) {
        $news_stories_url = get_permalink($news_stories_page);
        $query_args = [];

        if (!empty($category_ids)) {
            $c = get_term_by('id', (int) $category_ids[0], 'category');
            if ($c && !is_wp_error($c)) {
                $query_args['category'] = $c->slug;
            }
        }

        if (!empty($query_args)) {
            $news_stories_url = add_query_arg($query_args, $news_stories_url);
        }

        $see_all_link_group = '<!-- wp:navigation-link {"label":"' . __('See all posts', 'planet4-master-theme') . '","url":"' . $news_stories_url . '","className":"see-all-link"} /-->';
    }

    // A proper core/query attribute structure (WP_Query style).
    $query_block_attrs = [
        'query' => [
            'perPage' => 3,
            'pages' => 0,
            'offset' => 0,
            'postType' => 'post',
            'order' => 'desc',
            'orderBy' => 'date',
            'exclude' => [ (int) $post_id ],
            'inherit' => false,
        ],
        'className' => 'posts-list p4-query-loop is-custom-layout-list',
    ];

    // Use a queryId so we can target this Query Loop instance server-side.
    $query_id = 94001;
    $query_block_attrs['queryId'] = $query_id;

    // Store the tax_query temporarily for this request (picked up by query_loop_block_query_vars).
    $GLOBALS['ap_related_posts_tax_query'] = [
        'query_id' => $query_id,
        'tax_query' => $tax_query,
    ];

    $template = file_get_contents(__DIR__ . '/related-posts-block-template.html');
    $output = str_replace(
        ['{{QUERY_ATTRS}}', '{{RELATED_POSTS_LABEL}}', '{{SEE_ALL_LINK}}'],
        [wp_json_encode($query_block_attrs, JSON_UNESCAPED_SLASHES), __('Related Posts', 'planet4-master-theme'), $see_all_link_group],
        $template
    );

    return do_blocks($output);
}

add_filter('render_block', 'ap_override_render_related_posts_block', 9, 2);

/**
 * Apply our related-posts tax_query to the Query Loop block when it uses our queryId.
 * This hook is designed for Query Loop blocks (more reliable than pre_get_posts here).
 *
 * @param array         $query_vars Query vars for WP_Query.
 * @param array|WP_Block $block      Parsed block array (older WP) or WP_Block (newer WP).
 * @return array
 */
function ap_related_posts_query_loop_block_query_vars(array $query_vars, $block): array
{
    if (!isset($GLOBALS['ap_related_posts_tax_query']) || !is_array($GLOBALS['ap_related_posts_tax_query'])) {
        return $query_vars;
    }

    $data = $GLOBALS['ap_related_posts_tax_query'];
    $query_id = (int) ($data['query_id'] ?? 0);

    $block_query_id = is_array($block)
        ? (int) ($block['attrs']['queryId'] ?? 0)
        : (int) ($block->parsed_block['attrs']['queryId'] ?? 0);

    if (!$query_id || $block_query_id !== $query_id) {
        return $query_vars;
    }

    $tax_query = $data['tax_query'] ?? [];
    if (is_array($tax_query) && count($tax_query) > 1) {
        $query_vars['tax_query'] = $tax_query;
    }

    return $query_vars;
}

add_filter('query_loop_block_query_vars', 'ap_related_posts_query_loop_block_query_vars', 20, 2);
