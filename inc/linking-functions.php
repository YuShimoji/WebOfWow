<?php
/**
 * Functions for auto-linking between posts.
 *
 * @package WebOfWow
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Automatically adds links to other posts when a post is saved.
 *
 * @param int     $post_id The post ID.
 * @param WP_Post $post    The post object.
 */
function webofwow_auto_link_posts($post_id, $post) {
    // Check if auto-linking is enabled.
    $is_enabled = get_option('webofwow_auto_linking_enabled');
    if (!$is_enabled) {
        return;
    }

    // Check if this is an auto-save, or if the post type is not 'post', or if the post is not being published.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if ($post->post_type !== 'post' || $post->post_status !== 'publish') {
        return;
    }

    // Get all published posts to link to.
    $all_posts = get_posts([
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'exclude'        => [$post_id], // Exclude the current post.
    ]);

    if (empty($all_posts)) {
        return;
    }

    $content = $post->post_content;
    $updated_content = $content;
    $linked_keywords = [];

    foreach ($all_posts as $link_target_post) {
        $keyword = $link_target_post->post_title;
        $link = get_permalink($link_target_post->ID);

        // Skip empty keywords or already linked keywords.
        if (empty($keyword) || in_array($keyword, $linked_keywords)) {
            continue;
        }

        // Use regex to find the keyword that is not already inside an <a> tag.
        $pattern = '/(?<!<a[^>]*>)' . preg_quote($keyword, '/') . '(?![^<]*<\/a>)/i';

        // Replace only the first occurrence of the keyword.
        $new_content = preg_replace($pattern, '<a href="' . esc_url($link) . '">' . esc_html($keyword) . '</a>', $updated_content, 1);

        if ($new_content !== $updated_content) {
            $updated_content = $new_content;
            $linked_keywords[] = $keyword; // Mark this keyword as linked.
        }
    }

    // If the content was updated, save the post without triggering the hook again.
    if ($updated_content !== $content) {
        // Unhook this function to prevent infinite loops.
        remove_action('save_post', 'webofwow_auto_link_posts', 10, 2);

        // Update the post content.
        wp_update_post([
            'ID'           => $post_id,
            'post_content' => $updated_content,
        ]);

        // Re-hook this function.
        add_action('save_post', 'webofwow_auto_link_posts', 10, 2);
    }
}
add_action('save_post', 'webofwow_auto_link_posts', 10, 2);