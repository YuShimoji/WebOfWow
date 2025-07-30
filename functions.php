<?php

function webofwow_enqueue_styles() {
    wp_enqueue_style( 'webofwow-style', get_stylesheet_uri() );
}
add_action( 'wp_enqueue_scripts', 'webofwow_enqueue_styles' );

// Include AI functions.
require_once get_template_directory() . '/inc/ai-functions.php';

// Add AI Post Generator menu to the admin dashboard.
function webofwow_ai_post_generator_menu() {
    add_menu_page(
        'AI Post Generator',
        'AI Post Generator',
        'manage_options',
        'webofwow-ai-generator',
        'webofwow_ai_generator_page',
        'dashicons-robot',
        20
    );
}
add_action('admin_menu', 'webofwow_ai_post_generator_menu');

// Add Settings submenu page.
function webofwow_ai_settings_submenu() {
    add_submenu_page(
        'webofwow-ai-generator',       // Parent slug
        'AI Settings',                 // Page title
        'Settings',                    // Menu title
        'manage_options',              // Capability
        'webofwow-ai-settings',        // Menu slug
        'webofwow_ai_settings_page_callback' // Callback function
    );
}
add_action('admin_menu', 'webofwow_ai_settings_submenu');

// Callback function for the Settings page.
function webofwow_ai_settings_page_callback() {
    ?>
    <div class="wrap">
        <h1>AI Generator Settings</h1>
        <form method="post" action="options.php">
            <?php
                settings_fields('webofwow_ai_settings_group');
                do_settings_sections('webofwow-ai-settings');
                submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings, section, and fields.
function webofwow_register_ai_settings() {
    register_setting('webofwow_ai_settings_group', 'webofwow_openai_api_key');

    add_settings_section(
        'webofwow_api_settings_section',
        'API Key Settings',
        null, // No callback needed for the section description
        'webofwow-ai-settings'
    );

    add_settings_field(
        'webofwow_openai_api_key_field',
        'OpenAI API Key',
        'webofwow_api_key_field_callback',
        'webofwow-ai-settings',
        'webofwow_api_settings_section'
    );
}
add_action('admin_init', 'webofwow_register_ai_settings');

// Callback for the API key field.
function webofwow_api_key_field_callback() {
    $api_key = get_option('webofwow_openai_api_key');
    echo '<input type="text" name="webofwow_openai_api_key" value="' . esc_attr($api_key) . '" size="50" />';
}

// Callback function for the AI Post Generator page.
function webofwow_ai_generator_page() {
    // Check if the form has been submitted
    if (isset($_POST['webofwow_topic']) && check_admin_referer('webofwow_ai_generator_nonce')) {
        $topic = sanitize_text_field($_POST['webofwow_topic']);

        if (!empty($topic)) {
            // Create a new post
            $new_post = array(
                'post_title'    => $topic,
                'post_content'  => webofwow_generate_ai_content($topic),
                'post_status'   => 'publish',
                'post_author'   => get_current_user_id(),
            );

            // Insert the post into the database
            $post_id = wp_insert_post($new_post);

            if ($post_id) {
                echo '<div class="updated"><p>Successfully generated a new post: <a href="' . get_permalink($post_id) . '" target="_blank">' . $topic . '</a></p></div>';
            } else {
                echo '<div class="error"><p>Error generating the post.</p></div>';
            }
        }
    }
    ?>
    <div class="wrap">
        <h1>AI Post Generator</h1>
        <p>Enter a topic or keyword to generate a test post. The actual AI integration will be added later.</p>
        
        <form method="post" action="">
            <?php wp_nonce_field('webofwow_ai_generator_nonce'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="webofwow_topic">Topic</label></th>
                    <td><input type="text" id="webofwow_topic" name="webofwow_topic" value="" style="width: 100%;"/></td>
                </tr>
            </table>
            <?php submit_button('Generate Test Post'); ?>
        </form>
    </div>
    <?php
}