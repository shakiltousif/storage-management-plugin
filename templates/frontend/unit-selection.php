<?php
/**
 * Unit Selection Template
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$layout = $this->get_active_layout();
?>

<div class="royal-storage-unit-selection">
    <div class="unit-selection-header">
        <h2><?php esc_html_e( 'Select Your Storage Unit', 'royal-storage' ); ?></h2>
        <p><?php esc_html_e( 'Choose your preferred unit from the available options below. Click on a unit to select it.', 'royal-storage' ); ?></p>
    </div>

    <!-- Legend -->
    <div class="unit-legend">
        <div class="legend-item">
            <div class="legend-color available"></div>
            <span><?php esc_html_e( 'Available', 'royal-storage' ); ?></span>
        </div>
        <div class="legend-item">
            <div class="legend-color occupied"></div>
            <span><?php esc_html_e( 'Occupied', 'royal-storage' ); ?></span>
        </div>
        <div class="legend-item">
            <div class="legend-color reserved"></div>
            <span><?php esc_html_e( 'Reserved', 'royal-storage' ); ?></span>
        </div>
        <div class="legend-item">
            <div class="legend-color selected"></div>
            <span><?php esc_html_e( 'Selected', 'royal-storage' ); ?></span>
        </div>
    </div>

    <!-- Unit Grid -->
    <div class="unit-grid-container">
        <div class="unit-grid unit-grid-10x8">
            <!-- Units will be loaded dynamically via JavaScript -->
        </div>
    </div>

    <!-- Selection Summary -->
    <div class="selection-summary" style="display: none;">
        <h3><?php esc_html_e( 'Selected Unit', 'royal-storage' ); ?></h3>
        <div class="selected-unit-info">
            <!-- Selected unit details will be populated via JavaScript -->
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="unit-selection-actions">
        <button type="button" class="btn btn-secondary btn-back">
            <?php esc_html_e( 'Back', 'royal-storage' ); ?>
        </button>
        <button type="button" class="btn btn-primary btn-continue" disabled>
            <?php esc_html_e( 'Please Select a Unit', 'royal-storage' ); ?>
        </button>
    </div>
</div>

<script>
// Localize script data
window.royalStorageUnitSelection = {
    ajaxUrl: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
    nonce: '<?php echo esc_js( wp_create_nonce( 'royal_storage_booking' ) ); ?>',
    layout: <?php echo wp_json_encode( $layout ); ?>
};
</script>
