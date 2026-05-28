<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Gebruik: [schilderij_visualizer id="42"]
 * Of zonder id om een dropdown te tonen met alle schilderijen.
 */
add_shortcode( 'schilderij_visualizer', function( $atts ) {
    $atts = shortcode_atts( [ 'id' => '' ], $atts );
    ob_start();
    pv_render_widget( (int) $atts['id'] );
    return ob_get_clean();
} );

function pv_render_widget( int $schilderij_id = 0 ): void {
    ?>
    <div class="pv-wrap" data-schilderij-id="<?php echo esc_attr( $schilderij_id ); ?>">

        <!-- Stap 1: kies schilderij (alleen als geen id opgegeven) -->
        <?php if ( ! $schilderij_id ) : ?>
        <div class="pv-step pv-step--kies">
            <label class="pv-label"><?php _e( 'Kies een schilderij', 'painting-visualizer' ); ?></label>
            <select class="pv-select-schilderij">
                <option value=""><?php _e( '— selecteer —', 'painting-visualizer' ); ?></option>
            </select>
        </div>
        <?php endif; ?>

        <!-- Canvas: wordt zichtbaar na upload (staat boven het upload-veld) -->
        <div class="pv-canvas-wrap" style="display:none;">
            <p class="pv-hint"><?php _e( '📌 Klik op de muur om het schilderij te plaatsen', 'painting-visualizer' ); ?></p>
            <div class="pv-canvas-container">
                <canvas id="pv-canvas"></canvas>
            </div>

            <!-- Bedieningspaneel -->
            <div class="pv-controls">
                <label class="pv-label"><?php _e( 'Grootte aanpassen', 'painting-visualizer' ); ?></label>
                <input type="range" id="pv-scale" min="20" max="200" value="100" step="1">
                <span id="pv-scale-val">100%</span>
            </div>

            <div class="pv-controls pv-controls--buttons">
                <button class="pv-btn pv-btn--secondary" id="pv-reset">
                    <?php _e( 'Opnieuw', 'painting-visualizer' ); ?>
                </button>
                <button class="pv-btn pv-btn--primary" id="pv-download">
                    <?php _e( 'Foto opslaan', 'painting-visualizer' ); ?>
                </button>
                <a class="pv-btn pv-btn--koop" id="pv-koop-link" href="#" target="_blank">
                    <?php _e( '🛒 Bestel dit schilderij', 'painting-visualizer' ); ?>
                </a>
            </div>
        </div>

        <!-- Stap 2: upload kamerfoto (altijd zichtbaar, staat onder canvas) -->
        <div class="pv-step pv-step--upload">
            <label class="pv-label"><?php _e( 'Maak of upload een foto van je kamer', 'painting-visualizer' ); ?></label>
            <div class="pv-dropzone" id="pv-dropzone">
                <input type="file" id="pv-file-input" accept="image/*" capture="environment">
                <div class="pv-dropzone__inner">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                    <p><?php _e( 'Klik of sleep hier je kamerfoto', 'painting-visualizer' ); ?></p>
                    <small><?php _e( 'JPG, PNG, WEBP — max. 10 MB', 'painting-visualizer' ); ?></small>
                </div>
            </div>
        </div>

    </div>
    <?php
}
