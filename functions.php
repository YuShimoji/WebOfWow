<?php

function webofwow_enqueue_styles() {
    wp_enqueue_style( 'webofwow-style', get_stylesheet_uri() );
}
add_action( 'wp_enqueue_scripts', 'webofwow_enqueue_styles' );