/**
 * Layout Admin JavaScript
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

jQuery(document).ready(function($) {
    'use strict';

    let currentLayout = null;
    let currentUnits = [];
    let selectedCell = null;

    // Initialize layout admin
    initLayoutAdmin();

    function initLayoutAdmin() {
        loadLayout();
        bindEvents();
    }

    function loadLayout() {
        $.ajax({
            url: royalStorageLayoutAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'get_unit_layout',
                nonce: royalStorageLayoutAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    currentLayout = response.data.layout;
                    currentUnits = response.data.units;
                    renderLayoutGrid();
                    updateUnitsTable();
                } else {
                    showError('Failed to load layout: ' + response.data.message);
                }
            },
            error: function() {
                showError('Failed to load layout. Please try again.');
            }
        });
    }

    function renderLayoutGrid() {
        if (!currentLayout) return;

        const $grid = $('#layout-grid');
        $grid.empty();

        const gridWidth = parseInt($('#grid-width').val()) || currentLayout.grid_width;
        const gridHeight = parseInt($('#grid-height').val()) || currentLayout.grid_height;
        const unitSize = parseInt($('#unit-size').val()) || currentLayout.unit_size;

        // Set grid dimensions
        $grid.css({
            'grid-template-columns': `repeat(${gridWidth}, 1fr)`,
            'grid-template-rows': `repeat(${gridHeight}, 1fr)`
        });

        // Create grid cells
        for (let y = 0; y < gridHeight; y++) {
            for (let x = 0; x < gridWidth; x++) {
                const unit = findUnitAtPosition(x, y);
                const $cell = createGridCell(unit, x, y);
                $grid.append($cell);
            }
        }
    }

    function findUnitAtPosition(x, y) {
        return currentUnits.find(unit => 
            unit.position_x == x && unit.position_y == y
        );
    }

    function createGridCell(unit, x, y) {
        const $cell = $('<div class="grid-cell"></div>');
        $cell.attr('data-x', x);
        $cell.attr('data-y', y);
        
        if (unit) {
            // Unit exists at this position
            const visualProps = unit.visual_properties || {};
            const status = getUnitStatus(unit);
            
            $cell.addClass(status);
            $cell.addClass(getUnitTypeClass(unit.size));
            $cell.attr('data-unit-id', unit.id);
            
            // Add unit content
            const $content = $('<div class="cell-content"></div>');
            
            // Add unit icon
            const $icon = $('<div class="unit-icon"></div>');
            $icon.addClass(visualProps.icon || 'single-rect');
            $content.append($icon);
            
            // Add unit number
            if (visualProps.unit_number) {
                const $number = $('<div class="cell-number"></div>');
                $number.text(visualProps.unit_number);
                $content.append($number);
            }
            
            $cell.append($content);
            
            // Add click handler
            $cell.on('click', function() {
                selectCell($(this), unit);
            });
        } else {
            // Empty cell
            $cell.addClass('empty');
            $cell.on('click', function() {
                selectCell($(this), null);
            });
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

    function getUnitTypeClass(size) {
        const classes = {
            'M': 'm-size',
            'L': 'l-size',
            'XL': 'xl-size',
            'PARKING': 'parking'
        };
        return classes[size] || 'm-size';
    }

    function selectCell($cell, unit) {
        // Remove previous selection
        $('.grid-cell.selected').removeClass('selected');
        
        // Add selection to new cell
        $cell.addClass('selected');
        selectedCell = {
            element: $cell,
            unit: unit,
            x: parseInt($cell.attr('data-x')),
            y: parseInt($cell.attr('data-y'))
        };
    }

    function updateUnitsTable() {
        // This would update the units table if needed
        // For now, the table is rendered server-side
    }

    function bindEvents() {
        // Save layout
        $('#save-layout').on('click', function() {
            saveLayout();
        });

        // Reset layout
        $('#reset-layout').on('click', function() {
            if (confirm(royalStorageLayoutAdmin.strings.confirm_reset)) {
                resetLayout();
            }
        });

        // Create sample units
        $('#create-sample-units').on('click', function() {
            createSampleUnits();
        });

        // Grid dimension changes
        $('#grid-width, #grid-height, #unit-size').on('change', function() {
            renderLayoutGrid();
        });

        // Edit unit
        $(document).on('click', '.edit-unit', function() {
            const unitId = $(this).data('unit-id');
            editUnit(unitId);
        });
    }

    function saveLayout() {
        const layoutData = {
            facility_name: $('#facility-name').val(),
            grid_width: parseInt($('#grid-width').val()),
            grid_height: parseInt($('#grid-height').val()),
            unit_size: parseInt($('#unit-size').val()),
            units: currentUnits
        };

        $.ajax({
            url: royalStorageLayoutAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'save_unit_layout',
                nonce: royalStorageLayoutAdmin.nonce,
                layout_data: JSON.stringify(layoutData),
                facility_name: layoutData.facility_name,
                grid_width: layoutData.grid_width,
                grid_height: layoutData.grid_height,
                unit_size: layoutData.unit_size
            },
            success: function(response) {
                if (response.success) {
                    showSuccess(royalStorageLayoutAdmin.strings.save_success);
                } else {
                    showError(royalStorageLayoutAdmin.strings.save_error);
                }
            },
            error: function() {
                showError(royalStorageLayoutAdmin.strings.save_error);
            }
        });
    }

    function resetLayout() {
        $.ajax({
            url: royalStorageLayoutAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'create_sample_units',
                nonce: royalStorageLayoutAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    showSuccess('Layout reset successfully!');
                    loadLayout();
                } else {
                    showError('Failed to reset layout.');
                }
            },
            error: function() {
                showError('Failed to reset layout.');
            }
        });
    }

    function createSampleUnits() {
        $.ajax({
            url: royalStorageLayoutAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'create_sample_units',
                nonce: royalStorageLayoutAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    showSuccess('Sample units created successfully!');
                    loadLayout();
                } else {
                    showError('Failed to create sample units.');
                }
            },
            error: function() {
                showError('Failed to create sample units.');
            }
        });
    }

    function editUnit(unitId) {
        const unit = currentUnits.find(u => u.id == unitId);
        if (!unit) return;

        // Create modal for editing unit
        const modal = createEditUnitModal(unit);
        $('body').append(modal);
        modal.show();
    }

    function createEditUnitModal(unit) {
        const $modal = $(`
            <div class="modal" id="edit-unit-modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Edit Unit #${unit.id}</h3>
                        <span class="modal-close">&times;</span>
                    </div>
                    <div class="modal-body">
                        <form id="edit-unit-form">
                            <div class="form-group">
                                <label for="unit-size">Unit Size:</label>
                                <select id="unit-size" name="size">
                                    <option value="M" ${unit.size === 'M' ? 'selected' : ''}>M Size</option>
                                    <option value="L" ${unit.size === 'L' ? 'selected' : ''}>L Size</option>
                                    <option value="XL" ${unit.size === 'XL' ? 'selected' : ''}>XL Size</option>
                                    <option value="PARKING" ${unit.size === 'PARKING' ? 'selected' : ''}>Parking</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="unit-status">Status:</label>
                                <select id="unit-status" name="status">
                                    <option value="available" ${unit.status === 'available' ? 'selected' : ''}>Available</option>
                                    <option value="occupied" ${unit.status === 'occupied' ? 'selected' : ''}>Occupied</option>
                                    <option value="reserved" ${unit.status === 'reserved' ? 'selected' : ''}>Reserved</option>
                                    <option value="disabled" ${unit.status === 'disabled' ? 'selected' : ''}>Disabled</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="unit-price">Base Price (RSD):</label>
                                <input type="number" id="unit-price" name="base_price" value="${unit.base_price}" step="0.01" min="0">
                            </div>
                            <div class="form-group">
                                <label for="unit-dimensions">Dimensions:</label>
                                <input type="text" id="unit-dimensions" name="dimensions" value="${unit.dimensions || ''}">
                            </div>
                            <div class="form-group">
                                <label for="unit-amenities">Amenities:</label>
                                <textarea id="unit-amenities" name="amenities" rows="3">${unit.amenities || ''}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="unit-access-code">Access Code:</label>
                                <input type="text" id="unit-access-code" name="access_code" value="${unit.access_code || ''}">
                            </div>
                        </form>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="button button-secondary" id="cancel-edit">Cancel</button>
                        <button type="button" class="button button-primary" id="save-edit">Save Changes</button>
                    </div>
                </div>
            </div>
        `);

        // Bind modal events
        $modal.find('.modal-close, #cancel-edit').on('click', function() {
            $modal.remove();
        });

        $modal.find('#save-edit').on('click', function() {
            saveUnitChanges(unit.id, $modal);
        });

        // Close modal when clicking outside
        $modal.on('click', function(e) {
            if (e.target === this) {
                $modal.remove();
            }
        });

        return $modal;
    }

    function saveUnitChanges(unitId, $modal) {
        const formData = {
            size: $modal.find('#unit-size').val(),
            status: $modal.find('#unit-status').val(),
            base_price: parseFloat($modal.find('#unit-price').val()),
            dimensions: $modal.find('#unit-dimensions').val(),
            amenities: $modal.find('#unit-amenities').val(),
            access_code: $modal.find('#unit-access-code').val()
        };

        // Update unit in current units array
        const unitIndex = currentUnits.findIndex(u => u.id == unitId);
        if (unitIndex !== -1) {
            currentUnits[unitIndex] = { ...currentUnits[unitIndex], ...formData };
        }

        // Re-render grid and table
        renderLayoutGrid();
        updateUnitsTable();

        // Close modal
        $modal.remove();
        showSuccess('Unit updated successfully!');
    }

    function showSuccess(message) {
        showNotice(message, 'success');
    }

    function showError(message) {
        showNotice(message, 'error');
    }

    function showNotice(message, type) {
        const $notice = $(`<div class="notice notice-${type}"><p>${message}</p></div>`);
        $('.layout-admin-container').prepend($notice);
        
        setTimeout(function() {
            $notice.fadeOut(300, function() {
                $notice.remove();
            });
        }, 3000);
    }
});
