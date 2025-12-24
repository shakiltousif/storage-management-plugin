/**
 * Unit Selection JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';

    let selectedUnit = null;
    let availableUnits = [];
    let layout = null;

    initUnitSelection();

    function initUnitSelection() {
        loadUnits();
        bindEvents();
    }

    function loadUnits() {
        RoyalStorageUtils.ajax({
            url: royalStorageUnitSelection.ajaxUrl,
            data: {
                action: 'get_available_units',
                nonce: royalStorageUnitSelection.nonce
            },
            beforeSend: () => $('.unit-grid').html('<div class="rs-spinner" style="grid-column: 1/-1; margin: 4rem auto;"></div>'),
            success: function(response) {
                availableUnits = response.data.units;
                layout = response.data.layout;
                renderUnitGrid();
                updateSelectionSummary();
            }
        });
    }

    function renderUnitGrid() {
        if (!layout) return;
        const $grid = $('.unit-grid');
        $grid.empty().css({
            'grid-template-columns': `repeat(${layout.grid_width}, 1fr)`,
            'grid-template-rows': `repeat(${layout.grid_height}, 1fr)`
        });

        for (let y = 0; y < layout.grid_height; y++) {
            for (let x = 0; x < layout.grid_width; x++) {
                const unit = availableUnits.find(u => u.position_x == x && u.position_y == y);
                $grid.append(createUnitCell(unit, x, y));
            }
        }
    }

    function createUnitCell(unit, x, y) {
        const $cell = $('<div class="unit-item"></div>');
        if (unit) {
            const status = unit.status || 'available';
            $cell.addClass(status).attr({ 'data-unit-id': unit.id, 'data-unit-type': unit.size });
            $cell.append($('<div class="unit-icon"></div>').addClass(unit.visual_properties?.icon || 'single-rect'));
            if (unit.visual_properties?.unit_number) $cell.append($('<div class="unit-number"></div>').text(unit.visual_properties.unit_number));
            
            const $tooltip = $('<div class="unit-details"></div>').html(`<strong>${unit.size} #${unit.id}</strong><br>${unit.dimensions || ''}<br>${unit.base_price} RSD`);
            $cell.append($tooltip);

            if (status === 'available') $cell.on('click', () => selectUnit(unit));
        } else {
            $cell.addClass('disabled');
        }
        return $cell;
    }

    function selectUnit(unit) {
        $('.unit-item.selected').removeClass('selected');
        $(`.unit-item[data-unit-id="${unit.id}"]`).addClass('selected');
        selectedUnit = unit;
        updateSelectionSummary();
        updateActionButtons();

        // Trigger event for main booking form
        $(document).trigger('unit_selected', [unit]);
    }

    function updateSelectionSummary() {
        const $summary = $('.selection-summary');
        if (selectedUnit) {
            $summary.show().find('.selected-unit-info').html(`
                <div class="unit-info-left">
                    <h4>${selectedUnit.size} Unit #${selectedUnit.id}</h4>
                    <p class="rs-text-muted">${selectedUnit.dimensions || 'Standard size'}</p>
                </div>
                <div class="unit-info-right">
                    <div class="unit-price">${selectedUnit.base_price} RSD</div>
                </div>
            `);
        } else {
            $summary.hide();
        }
    }

    function updateActionButtons() {
        const $btn = $('.btn-continue');
        if (selectedUnit) {
            $btn.prop('disabled', false).text('Continue with Unit #' + selectedUnit.id);
        } else {
            $btn.prop('disabled', true).text('Please Select a Unit');
        }
    }

    function bindEvents() {
        $(document).on('click', '.btn-continue', function(e) {
            e.preventDefault();
            if (!selectedUnit) {
                RoyalStorageUtils.showToast('Please select a unit', 'error');
                return;
            }
            if (window.nextStep) window.nextStep();
        });
        
        $(document).on('click', '.btn-back', (e) => {
            e.preventDefault();
            if (window.previousStep) window.previousStep();
        });
    }

    window.unitSelection = {
        getSelectedUnit: () => selectedUnit,
        refreshUnits: loadUnits
    };
});
