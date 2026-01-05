<?php
/**
 * Unit Selection Template - MODIFIED FOR AUTO-ASSIGNMENT
 * Modified: 2026-01-06
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
===========================================
LEGACY GRID SELECTION CODE - PRESERVED FOR POTENTIAL RESTORATION
Date Disabled: 2026-01-06
Reason: Replaced with auto-assignment by size system
===========================================

$layout = $this->get_active_layout();
?>

<div class="royal-storage-unit-selection">
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
</div>

<script>
// Localize script data
window.royalStorageUnitSelection = {
    ajaxUrl: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
    nonce: '<?php echo esc_js( wp_create_nonce( 'royal_storage_booking' ) ); ?>',
    layout: <?php echo wp_json_encode( $layout ); ?>
};
</script>

===========================================
END LEGACY GRID SELECTION CODE
===========================================
*/
?>

<!-- NEW: Size Selection Interface for Auto-Assignment -->
<div class="royal-storage-size-selection">
	<h3><?php esc_html_e( 'Select Your Unit Size', 'royal-storage' ); ?></h3>
	<p class="size-selection-description"><?php esc_html_e( 'Choose a size and we\'ll automatically assign the best available unit for you.', 'royal-storage' ); ?></p>

	<div class="size-options">
		<div class="size-card" data-size="M">
			<input type="radio" id="size_m" name="unit_size" value="M">
			<label for="size_m">
				<div class="size-icon">📦</div>
				<h4><?php esc_html_e( 'Medium (M)', 'royal-storage' ); ?></h4>
				<p class="size-dimensions">3x3x3 <?php esc_html_e( 'meters', 'royal-storage' ); ?></p>
				<p class="size-price">10,000 RSD/<?php esc_html_e( 'month', 'royal-storage' ); ?></p>
			</label>
		</div>

		<div class="size-card" data-size="L">
			<input type="radio" id="size_l" name="unit_size" value="L">
			<label for="size_l">
				<div class="size-icon">📦📦</div>
				<h4><?php esc_html_e( 'Large (L)', 'royal-storage' ); ?></h4>
				<p class="size-dimensions">4x4x4 <?php esc_html_e( 'meters', 'royal-storage' ); ?></p>
				<p class="size-price">18,000 RSD/<?php esc_html_e( 'month', 'royal-storage' ); ?></p>
			</label>
		</div>

		<div class="size-card" data-size="XL">
			<input type="radio" id="size_xl" name="unit_size" value="XL">
			<label for="size_xl">
				<div class="size-icon">📦📦📦</div>
				<h4><?php esc_html_e( 'Extra Large (XL)', 'royal-storage' ); ?></h4>
				<p class="size-dimensions">5x5x5 <?php esc_html_e( 'meters', 'royal-storage' ); ?></p>
				<p class="size-price">25,000 RSD/<?php esc_html_e( 'month', 'royal-storage' ); ?></p>
			</label>
		</div>
	</div>

	<div class="auto-assignment-notice">
		<span class="notice-icon">ℹ️</span>
		<p><?php esc_html_e( 'Your unit will be automatically assigned when you complete the booking.', 'royal-storage' ); ?></p>
	</div>
</div>
