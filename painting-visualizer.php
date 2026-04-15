<?php
/**
 * Plugin Name: Schilderij Visualizer
 * Plugin URI:  https://jouwwebsite.nl
 * Description: Laat klanten schilderijen virtueel ophangen in hun eigen kamer.
 * Version:     1.0.0
 * Author:      Jouw Naam
 * Text Domain: painting-visualizer
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PV_VERSION',  '1.0.0' );
define( 'PV_PATH',     plugin_dir_path( __FILE__ ) );
define( 'PV_URL',      plugin_dir_url( __FILE__ ) );

/* ---------- autoload helpers ---------- */
require_once PV_PATH . 'includes/post-type.php';
require_once PV_PATH . 'includes/api.php';
require_once PV_PATH . 'includes/shortcode.php';

/* ---------- Elementor widget ---------- */
add_action( 'elementor/widgets/register', function( $manager ) {
    require_once PV_PATH . 'includes/class-widget.php';
    $manager->register( new \PV_Elementor_Widget() );
} );

/* ---------- scripts & styles ---------- */
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'pv-style',
        PV_URL . 'assets/css/visualizer.css',
        [],
        PV_VERSION
    );
    wp_enqueue_script(
        'pv-script',
        PV_URL . 'assets/js/visualizer.js',
        [],
        PV_VERSION,
        true
    );
    wp_localize_script( 'pv-script', 'PV', [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'pv_nonce' ),
        'rest_url' => rest_url( 'pv/v1/' ),
    ] );
} );
