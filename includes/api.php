<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'rest_api_init', function() {

    /* GET /wp-json/pv/v1/schilderijen — alle schilderijen (voor dropdown) */
    register_rest_route( 'pv/v1', '/schilderijen', [
        'methods'             => 'GET',
        'callback'            => 'pv_rest_get_schilderijen',
        'permission_callback' => '__return_true',
    ] );

    /* GET /wp-json/pv/v1/schilderijen/<id> — detail */
    register_rest_route( 'pv/v1', '/schilderijen/(?P<id>\d+)', [
        'methods'             => 'GET',
        'callback'            => 'pv_rest_get_schilderij',
        'permission_callback' => '__return_true',
        'args' => [
            'id' => [ 'validate_callback' => fn($v) => is_numeric($v) ],
        ],
    ] );
} );

function pv_rest_get_schilderijen( WP_REST_Request $request ): WP_REST_Response {
    $posts = get_posts( [
        'post_type'      => 'schilderij',
        'posts_per_page' => 100,
        'post_status'    => 'publish',
    ] );

    $data = array_map( fn($p) => pv_format_schilderij( $p ), $posts );
    return new WP_REST_Response( $data, 200 );
}

function pv_rest_get_schilderij( WP_REST_Request $request ): WP_REST_Response {
    $post = get_post( (int) $request['id'] );
    if ( ! $post || $post->post_status !== 'publish' ) {
        return new WP_REST_Response( [ 'error' => 'Niet gevonden' ], 404 );
    }
    return new WP_REST_Response( pv_format_schilderij( $post ), 200 );
}

function pv_format_schilderij( WP_Post $post ): array {
    $thumb_id  = get_post_thumbnail_id( $post->ID );
    $thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'large' ) : '';

    return [
        'id'       => $post->ID,
        'titel'    => $post->post_title,
        'afbeelding' => $thumb_url,
        'breedte'  => (int) get_post_meta( $post->ID, '_pv_breedte', true ),
        'hoogte'   => (int) get_post_meta( $post->ID, '_pv_hoogte',  true ),
        'koop_url' => get_permalink( $post->ID ),
    ];
}
