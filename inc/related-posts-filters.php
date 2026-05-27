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
 * @param int $post_id WordPress post ID (unused, kept for call-site compatibility).
 * @param \P4\MasterTheme\Post $timber_post Timber post.
 * @return array<string, int[]>
 */
function ap_build_related_posts_tax_query(int $post_id, $timber_post): array
{
    $tag_ids = [];
    foreach ($timber_post->terms('post_tag') as $tag) {
        $tag_ids[] = (int) $tag->id;
    }
    return ['post_tag' => $tag_ids];
}
