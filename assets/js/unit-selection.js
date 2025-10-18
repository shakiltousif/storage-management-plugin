/**
 * Unit Selection JavaScript
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

jQuery(document).ready(function($) {
    'use strict';

    let selectedUnit = null;
    let availableUnits = [];
    let layout = null;

    // Initialize unit selection
    initUnitSelection();

    function initUnitSelection() {
        loadUnits();
        bindEvents();
    }

    function loadUnits() {
        $.ajax({
            url: royalStorageUnitSelection.ajaxUrl,
            type: 'POST',
            data: {
                action: 'get_available_units',
                nonce: royalStorageUnitSelection.nonce
            },
            success: function(response) {
                console.log('Unit selection AJAX response:', response);
                if (response.success) {
                    availableUnits = response.data.units;
                    layout = response.data.layout;
                    renderUnitGrid();
                    updateSelectionSummary();
                } else {
                    console.log('Full error response:', response);
                    console.log('Error data:', response.data);
                    showError('Failed to load units: ' + (response.data.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.log('Unit selection AJAX error:', xhr, status, error);
                showError('Failed to load units. Please try again.');
            }
        });
    }

    function renderUnitGrid() {
        if (!layout) return;

        const $grid = $('.unit-grid');
        $grid.empty();

        // Set grid dimensions
        $grid.css({
            'grid-template-columns': `repeat(${layout.grid_width}, 1fr)`,
            'grid-template-rows': `repeat(${layout.grid_height}, 1fr)`
        });

        // Create grid cells
        for (let y = 0; y < layout.grid_height; y++) {
            for (let x = 0; x < layout.grid_width; x++) {
                const unit = findUnitAtPosition(x, y);
                const $cell = createUnitCell(unit, x, y);
                $grid.append($cell);
            }
        }
    }

    function findUnitAtPosition(x, y) {
        return availableUnits.find(unit => 
            unit.position_x == x && unit.position_y == y
        );
    }

    function createUnitCell(unit, x, y) {
        const $cell = $('<div class="unit-item"></div>');
        
        if (unit) {
            // Unit exists at this position
            const visualProps = unit.visual_properties || {};
            const status = getUnitStatus(unit);
            
            $cell.addClass(status);
            $cell.attr('data-unit-id', unit.id);
            $cell.attr('data-unit-type', unit.size);
            $cell.attr('data-position', `${x},${y}`);
            
            // Add unit icon
            const $icon = $('<div class="unit-icon"></div>');
            $icon.addClass(visualProps.icon || 'single-rect');
            $cell.append($icon);
            
            // Add unit number
            if (visualProps.unit_number) {
                const $number = $('<div class="unit-number"></div>');
                $number.text(visualProps.unit_number);
                $cell.append($number);
            }
            
            // Add unit details tooltip
            const $details = $('<div class="unit-details"></div>');
            $details.html(`
                <strong>${unit.size} Unit #${unit.id}</strong><br>
                ${unit.dimensions || 'N/A'}<br>
                ${formatPrice(unit.base_price)} RSD
            `);
            $cell.append($details);
            
            // Add click handler for available units
            if (status === 'available') {
                $cell.on('click', function() {
                    selectUnit(unit);
                });
            }
        } else {
            // Empty cell
            $cell.addClass('disabled');
        }
        
        return $cell;
    }

    function getUnitStatus(unit) {
        if (unit.status === 'available') {
            return 'available';
        } else if (unit.status === 'occupied') {
            return 'occupied';
        } else if (unit.status === 'reserved') {
            return 'reserved';
        } else {
            return 'disabled';
        }
    }

    function selectUnit(unit) {
        // Remove previous selection
        $('.unit-item.selected').removeClass('selected');
        
        // Add selection to new unit
        $(`.unit-item[data-unit-id="${unit.id}"]`).addClass('selected');
        
        selectedUnit = unit;
        updateSelectionSummary();
        updateActionButtons();
        
        // Store selected unit in global booking data if available
        if (typeof window.bookingData !== 'undefined') {
            window.bookingData.selected_unit = selectedUnit;
            window.bookingData.unit_id = selectedUnit.id;
            window.bookingData.unit_type = selectedUnit.size.toLowerCase();
        }
    }

    function updateSelectionSummary() {
        const $summary = $('.selection-summary');
        const $info = $('.selected-unit-info');
        
        if (selectedUnit) {
            $summary.show();
            
            const visualProps = selectedUnit.visual_properties || {};
            const unitType = getUnitTypeLabel(selectedUnit.size);
            
            $info.html(`
                <div class="unit-info-left">
                    <div class="unit-icon ${visualProps.icon || 'single-rect'}"></div>
                    <div>
                        <div class="unit-description">
                            <strong>${unitType} Unit #${selectedUnit.id}</strong><br>
                            ${selectedUnit.dimensions || 'N/A'} | ${selectedUnit.amenities || 'Standard amenities'}
                        </div>
                    </div>
                </div>
                <div class="unit-info-right">
                    <div class="unit-price">${formatPrice(selectedUnit.base_price)} RSD</div>
                    <div class="unit-description">per month</div>
                </div>
            `);
        } else {
            $summary.hide();
        }
    }

    function updateActionButtons() {
        const $continueBtn = $('.btn-continue');
        
        if (selectedUnit) {
            $continueBtn.prop('disabled', false);
            $continueBtn.text('Continue with Selected Unit');
        } else {
            $continueBtn.prop('disabled', true);
            $continueBtn.text('Please Select a Unit');
        }
    }

    function getUnitTypeLabel(size) {
        const labels = {
            'M': 'M Size Box',
            'L': 'L Size Box', 
            'XL': 'XL Size Box',
            'PARKING': 'Parking Space'
        };
        return labels[size] || size;
    }

    function formatPrice(price) {
        return new Intl.NumberFormat('sr-RS').format(parseFloat(price));
    }

    function bindEvents() {
        // Continue button
        $(document).on('click', '.btn-continue', function(e) {
            e.preventDefault();
            
            if (!selectedUnit) {
                showError('Please select a unit to continue.');
                return;
            }
            
            // Store selected unit in booking data
            if (typeof window.bookingData !== 'undefined') {
                window.bookingData.selected_unit = selectedUnit;
                window.bookingData.unit_id = selectedUnit.id;
                window.bookingData.unit_type = selectedUnit.size.toLowerCase();
            }
            
            // Trigger next step
            if (typeof window.nextStep === 'function') {
                window.nextStep();
            } else {
                // Fallback: redirect to next step
                window.location.href = '#next-step';
            }
        });
        
        // Back button
        $(document).on('click', '.btn-back', function(e) {
            e.preventDefault();
            
            if (typeof window.previousStep === 'function') {
                window.previousStep();
            } else {
                // Fallback: go back
                window.history.back();
            }
        });
    }

    function showError(message) {
        $('.error-message').remove();
        $('<div class="error-message" style="color: red; padding: 10px; margin: 10px 0; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px;">' + message + '</div>').insertAfter('.unit-selection-header');
    }

    // Expose functions globally for integration with booking flow
    window.unitSelection = {
        getSelectedUnit: function() {
            return selectedUnit;
        },
        clearSelection: function() {
            $('.unit-item.selected').removeClass('selected');
            selectedUnit = null;
            updateSelectionSummary();
            updateActionButtons();
        },
        refreshUnits: function() {
            loadUnits();
        }
    };
    
    // Also expose selected unit to global booking data
    window.getSelectedUnit = function() {
        return selectedUnit;
    };
});
