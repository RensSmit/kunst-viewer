<?php
if ( ! defined( 'ABSPATH' ) ) exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class PV_Elementor_Widget extends Widget_Base {

    public function get_name():  string { return 'schilderij_visualizer'; }
    public function get_title(): string { return __( 'Schilderij Visualizer', 'painting-visualizer' ); }
    public function get_icon():  string { return 'eicon-image-rollover'; }
    public function get_categories(): array { return [ 'general' ]; }

    protected function register_controls(): void {

        $this->start_controls_section( 'section_content', [
            'label' => __( 'Instellingen', 'painting-visualizer' ),
        ] );

        /* Kies een specifiek schilderij (optioneel) */
        $opties = [ '' => __( '— toon selectielijst —', 'painting-visualizer' ) ];
        $posts  = get_posts( [ 'post_type' => 'schilderij', 'posts_per_page' => 200, 'post_status' => 'publish' ] );
        foreach ( $posts as $p ) {
            $opties[ $p->ID ] = $p->post_title;
        }

        $this->add_control( 'schilderij_id', [
            'label'   => __( 'Schilderij', 'painting-visualizer' ),
            'type'    => Controls_Manager::SELECT,
            'options' => $opties,
            'default' => '',
        ] );

        $this->add_control( 'titel', [
            'label'   => __( 'Blok-titel', 'painting-visualizer' ),
            'type'    => Controls_Manager::TEXT,
            'default' => __( 'Hang het schilderij virtueel op', 'painting-visualizer' ),
        ] );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings = $this->get_settings_for_display();
        if ( $settings['titel'] ) {
            echo '<h3 class="pv-widget-titel">' . esc_html( $settings['titel'] ) . '</h3>';
        }
        pv_render_widget( (int) $settings['schilderij_id'] );
    }
}
