<?php
/**
 * AI-related functions for the WebOfWow theme.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// This function is responsible for calling the OpenAI API and returning the generated content.
function webofwow_generate_ai_content($topic) {
    $api_key = get_option('webofwow_openai_api_key');

    if (empty($api_key)) {
        return 'Error: API key is not set. Please set it in the AI Generator Settings.';
    }

    $api_url = 'https://api.openai.com/v1/chat/completions';

    $headers = array(
        'Authorization' => 'Bearer ' . $api_key,
        'Content-Type'  => 'application/json',
    );

    $body = array(
        'model'    => 'gpt-3.5-turbo',
        'messages' => array(
            array(
                'role'    => 'system',
                'content' => 'You are a helpful assistant that writes blog posts.',
            ),
            array(
                'role'    => 'user',
                'content' => 'Write a short blog post about: ' . $topic,
            ),
        ),
        'max_tokens' => 500,
    );

    $args = array(
        'body'    => json_encode($body),
        'headers' => $headers,
        'timeout' => 60,
    );

    $response = wp_remote_post($api_url, $args);

    if (is_wp_error($response)) {
        return 'Error: ' . $response->get_error_message();
    }

    $response_body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($response_body['choices'][0]['message']['content'])) {
        return $response_body['choices'][0]['message']['content'];
    } elseif (isset($response_body['error']['message'])) {
        return 'Error from API: ' . $response_body['error']['message'];
    } else {
        return 'Error: Could not retrieve content from API response.';
    }
}

/**
 * Fetches and parses RSS feeds.
 *
 * @return array An array of fetched feed items.
 */
function webofwow_fetch_rss_feeds() {
    include_once(ABSPATH . WPINC . '/feed.php');

    $rss_urls_string = get_option('webofwow_rss_feed_urls');
    if (empty($rss_urls_string)) {
        return [];
    }

    $rss_urls = explode("\n", trim($rss_urls_string));
    $rss_urls = array_map('trim', $rss_urls);
    $rss_urls = array_filter($rss_urls);

    if (empty($rss_urls)) {
        return [];
    }

    $feed_items = [];
    foreach ($rss_urls as $url) {
        $feed = fetch_feed($url);

        if (!is_wp_error($feed)) {
            $maxitems = $feed->get_item_quantity(5); // Get the 5 latest items.
            $rss_items = $feed->get_items(0, $maxitems);

            foreach ($rss_items as $item) {
                $feed_items[] = [
                    'title' => $item->get_title(),
                    'url'   => $item->get_permalink(),
                    'source' => $feed->get_title(),
                ];
            }
        } else {
            // Optionally, log the error for debugging.
            error_log('Error fetching feed from ' . $url . ': ' . $feed->get_error_message());
        }
    }

    return $feed_items;
}