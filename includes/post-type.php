<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* Voeg afmetingen meta box toe aan WooCommerce producten */
add_action( 'add_meta_boxes', function() {
    add_meta_box(
        'pv_dimensions',
        __( 'Schilderij afmetingen (cm)', 'painting-visualizer' ),
        'pv_dimensions_meta_box',
        'product',
        'side'
    );
} );

function pv_dimensions_meta_box( $post ) {
    $breedte = get_post_meta( $post->ID, '_pv_breedte', true );
    $hoogte  = get_post_meta( $post->ID, '_pv_hoogte',  true );
    wp_nonce_field( 'pv_save_meta', 'pv_meta_nonce' );
    echo '<p><label>' . __( 'Breedte (cm)', 'painting-visualizer' ) . '<br>';
    echo '<input type="number" name="pv_breedte" value="' . esc_attr( $breedte ) . '" style="width:100%"></label></p>';
    echo '<p><label>' . __( 'Hoogte (cm)', 'painting-visualizer' ) . '<br>';
    echo '<input type="number" name="pv_hoogte" value="' . esc_attr( $hoogte ) . '" style="width:100%"></label></p>';
}

add_action( 'save_post_product', function( $post_id ) {
    if ( ! isset( $_POST['pv_meta_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['pv_meta_nonce'], 'pv_save_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

    if ( isset( $_POST['pv_breedte'] ) ) {
        update_post_meta( $post_id, '_pv_breedte', absint( $_POST['pv_breedte'] ) );
    }
    if ( isset( $_POST['pv_hoogte'] ) ) {
        update_post_meta( $post_id, '_pv_hoogte', absint( $_POST['pv_hoogte'] ) );
    }
} );
