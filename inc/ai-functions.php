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