/**
 * Layout Admin JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';

    let currentLayout = null;
    let currentUnits = [];
    let selectedCell = null;
    let unitsWithBookings = new Set(); // Track units that have bookings

    initLayoutAdmin();

    function initLayoutAdmin() {
        loadLayout();
        bindEvents();
    }

    function loadLayout() {
        RoyalStorageUtils.ajax({
            url: royalStorageLayoutAdmin.ajaxUrl,
            data: {
                action: 'get_unit_layout',
                nonce: royalStorageLayoutAdmin.nonce
            },
            success: function(response) {
                currentLayout = response.data.layout;
                currentUnits = response.data.units;
                checkUnitsBookings();
            }
        });
    }

    function checkUnitsBookings() {
        // Check each unit for bookings
        let checkPromises = currentUnits.map(unit => {
            return new Promise((resolve) => {
                RoyalStorageUtils.ajax({
                    url: royalStorageLayoutAdmin.ajaxUrl,
                    data: {
                        action: 'check_unit_has_bookings',
                        nonce: royalStorageLayoutAdmin.nonce,
                        unit_id: unit.id
                    },
                    success: function(response) {
                        if (response.data.has_bookings) {
                            unitsWithBookings.add(unit.id);
                        }
                        resolve();
                    },
                    error: function() {
                        resolve(); // Continue even if check fails
                    }
                });
            });
        });

        // Render grid after all checks complete
        Promise.all(checkPromises).then(() => {
            renderLayoutGrid();
        });
    }

    function renderLayoutGrid() {
        if (!currentLayout) return;

        const $grid = $('#layout-grid');
        $grid.empty();

        const gridWidth = parseInt($('#grid-width').val()) || currentLayout.grid_width;
        const gridHeight = parseInt($('#grid-height').val()) || currentLayout.grid_height;

        $grid.css({
            'grid-template-columns': `repeat(${gridWidth}, 1fr)`,
            'grid-template-rows': `repeat(${gridHeight}, 1fr)`
        });

        for (let y = 0; y < gridHeight; y++) {
            for (let x = 0; x < gridWidth; x++) {
                const unit = currentUnits.find(u => u.position_x == x && u.position_y == y);
                const $cell = createGridCell(unit, x, y);
                $grid.append($cell);
            }
        }
    }

    function createGridCell(unit, x, y) {
        const $cell = $('<div class="grid-cell"></div>').attr({ 'data-x': x, 'data-y': y });

        // Add position label to all cells
        const $posLabel = $('<div class="position-label"></div>').text(`${x},${y}`);
        $cell.append($posLabel);

        if (unit) {
            const hasBookings = unitsWithBookings.has(unit.id);
            const isDraggable = !hasBookings;

            $cell.addClass(unit.status || 'available')
                .addClass(getUnitTypeClass(unit.size))
                .attr({
                    'data-unit-id': unit.id,
                    'draggable': isDraggable ? 'true' : 'false'
                });

            // Add locked class if unit has bookings
            if (hasBookings) {
                $cell.addClass('locked');
            }

            const $content = $('<div class="cell-content"></div>');
            if (unit.visual_properties && unit.visual_properties.unit_number) {
                $content.append($('<div class="cell-number"></div>').text(unit.visual_properties.unit_number));
            }
            $content.append($('<div class="unit-type-label"></div>').text(unit.size));

            // Add lock icon if unit has bookings
            if (hasBookings) {
                $content.append($('<div class="lock-icon" title="Unit has active bookings">🔒</div>'));
            }

            $cell.append($content);

            // Drag events for units (only if draggable)
            if (isDraggable) {
                $cell.on('dragstart', (e) => handleDragStart(e, unit));
                $cell.on('dragend', handleDragEnd);
            }
        } else {
            $cell.addClass('empty');
        }

        // Drop events for all cells (so you can drop on empty cells too)
        $cell.on('dragover', handleDragOver);
        $cell.on('drop', (e) => handleDrop(e, x, y));
        $cell.on('dragleave', handleDragLeave);
        $cell.on('click', () => selectCell($cell, unit));

        return $cell;
    }

    function getUnitTypeClass(size) {
        const classes = { 'M': 'm-size', 'L': 'l-size', 'XL': 'xl-size', 'PARKING': 'parking' };
        return classes[size] || 'm-size';
    }

    function selectCell($cell, unit) {
        $('.grid-cell.selected').removeClass('selected');
        $cell.addClass('selected');
        if (unit) editUnit(unit.id);
    }

    // Drag and Drop Handlers
    let draggedUnit = null;

    function handleDragStart(e, unit) {
        // Check if unit has bookings
        if (unitsWithBookings.has(unit.id)) {
            e.preventDefault();
            RoyalStorageUtils.showToast('Cannot move this unit because it has active bookings', 'error');
            return false;
        }

        draggedUnit = unit;
        $(e.currentTarget).addClass('dragging');
        e.originalEvent.dataTransfer.effectAllowed = 'move';
        e.originalEvent.dataTransfer.setData('text/html', e.currentTarget.innerHTML);
    }

    function handleDragEnd(e) {
        $(e.currentTarget).removeClass('dragging');
        $('.grid-cell').removeClass('drag-over');
        draggedUnit = null;
    }

    function handleDragOver(e) {
        if (e.preventDefault) e.preventDefault();
        e.originalEvent.dataTransfer.dropEffect = 'move';
        $(e.currentTarget).addClass('drag-over');
        return false;
    }

    function handleDragLeave(e) {
        $(e.currentTarget).removeClass('drag-over');
    }

    function handleDrop(e, targetX, targetY) {
        if (e.stopPropagation) e.stopPropagation();
        if (!draggedUnit) return false;

        const $target = $(e.currentTarget);
        $target.removeClass('drag-over');

        // Check if target cell is already occupied
        const targetOccupied = currentUnits.find(u => u.position_x == targetX && u.position_y == targetY);
        if (targetOccupied && targetOccupied.id !== draggedUnit.id) {
            RoyalStorageUtils.showToast('Position already occupied', 'error');
            return false;
        }

        const oldX = draggedUnit.position_x;
        const oldY = draggedUnit.position_y;

        if (oldX == targetX && oldY == targetY) {
            return false; // Same position, no change
        }

        // Update unit position
        updateUnitPosition(draggedUnit.id, targetX, targetY, oldX, oldY);

        return false;
    }

    function updateUnitPosition(unitId, newX, newY, oldX, oldY) {
        RoyalStorageUtils.ajax({
            url: royalStorageLayoutAdmin.ajaxUrl,
            data: {
                action: 'update_unit_position',
                nonce: royalStorageLayoutAdmin.nonce,
                unit_id: unitId,
                position_x: newX,
                position_y: newY
            },
            success: function(response) {
                // Update local state
                const idx = currentUnits.findIndex(u => u.id == unitId);
                if (idx !== -1) {
                    currentUnits[idx].position_x = newX;
                    currentUnits[idx].position_y = newY;
                }
                renderLayoutGrid();
                RoyalStorageUtils.showToast(`Unit moved from (${oldX},${oldY}) to (${newX},${newY})`);
            },
            error: function(response) {
                // If unit has bookings, add it to the locked set
                if (response.data && response.data.has_bookings) {
                    unitsWithBookings.add(unitId);
                }
                // Show error message
                const message = response.data && response.data.message ? response.data.message : 'Failed to update unit position';
                RoyalStorageUtils.showToast(message, 'error');
                // Re-render to show locked state
                renderLayoutGrid();
            }
        });
    }

    function bindEvents() {
        $('#save-layout').on('click', saveLayout);
        $('#reset-layout').on('click', () => {
            if (confirm(royalStorageLayoutAdmin.strings.confirm_reset)) resetLayout();
        });
        $('#grid-width, #grid-height, #unit-size').on('change', renderLayoutGrid);
        $(document).on('click', '.edit-unit', function() {
            const unitId = $(this).data('unit-id');
            editUnit(unitId);
        });
    }

    function saveLayout() {
        RoyalStorageUtils.ajax({
            url: royalStorageLayoutAdmin.ajaxUrl,
            data: {
                action: 'save_unit_layout',
                nonce: royalStorageLayoutAdmin.nonce,
                facility_name: $('#facility-name').val(),
                grid_width: parseInt($('#grid-width').val()),
                grid_height: parseInt($('#grid-height').val()),
                unit_size: parseInt($('#unit-size').val()),
                layout_data: JSON.stringify({ units: currentUnits })
            },
            success: function() {
                RoyalStorageUtils.showToast('Layout saved successfully');
            }
        });
    }

    function resetLayout() {
        RoyalStorageUtils.ajax({
            url: royalStorageLayoutAdmin.ajaxUrl,
            data: {
                action: 'create_sample_units',
                nonce: royalStorageLayoutAdmin.nonce
            },
            success: function() {
                RoyalStorageUtils.showToast('Layout reset');
                loadLayout();
            }
        });
    }

    function editUnit(unitId) {
        const unit = currentUnits.find(u => u.id == unitId);
        if (!unit) return;

        const content = `
            <form id="edit-unit-form" class="royal-storage-form">
                <div class="royal-storage-form-group">
                    <label>Unit Size</label>
                    <select name="size">
                        <option value="M" ${unit.size === 'M' ? 'selected' : ''}>M Size</option>
                        <option value="L" ${unit.size === 'L' ? 'selected' : ''}>L Size</option>
                        <option value="XL" ${unit.size === 'XL' ? 'selected' : ''}>XL Size</option>
                        <option value="PARKING" ${unit.size === 'PARKING' ? 'selected' : ''}>Parking</option>
                    </select>
                </div>
                <div class="royal-storage-form-group">
                    <label>Dimensions</label>
                    <input type="text" name="dimensions" value="${unit.dimensions || ''}" placeholder="e.g., 3x3x3">
                </div>
                <div class="royal-storage-form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="available" ${unit.status === 'available' ? 'selected' : ''}>Available</option>
                        <option value="occupied" ${unit.status === 'occupied' ? 'selected' : ''}>Occupied</option>
                    </select>
                </div>
                <div class="royal-storage-form-group">
                    <label>Base Price (RSD)</label>
                    <input type="number" name="base_price" value="${unit.base_price}" step="0.01">
                </div>
                <div class="royal-storage-form-group">
                    <label>Position X</label>
                    <input type="number" name="position_x" value="${unit.position_x || 0}" min="0">
                </div>
                <div class="royal-storage-form-group">
                    <label>Position Y</label>
                    <input type="number" name="position_y" value="${unit.position_y || 0}" min="0">
                </div>
                <div class="royal-storage-form-group">
                    <label>Unit Group</label>
                    <input type="text" name="unit_group" value="${unit.unit_group || ''}" placeholder="e.g., m_boxes">
                </div>
                <div class="royal-storage-form-group">
                    <label>Access Code</label>
                    <input type="text" name="access_code" value="${unit.access_code || ''}" placeholder="e.g., ABC123">
                </div>
            </form>`;

        const footer = `
            <button class="royal-storage-btn royal-storage-btn-secondary modal-cancel">Cancel</button>
            <button class="royal-storage-btn modal-save">Save Changes</button>`;

        const $modal = RoyalStorageUtils.openModal({
            title: `Edit Unit #${unit.id}`,
            content: content,
            footer: footer,
            onOpen: ($m) => {
                $m.find('.modal-cancel').on('click', () => $m.find('.royal-storage-modal-close').click());
                $m.find('.modal-save').on('click', () => {
                    const formData = {
                        size: $m.find('[name="size"]').val(),
                        dimensions: $m.find('[name="dimensions"]').val(),
                        status: $m.find('[name="status"]').val(),
                        base_price: parseFloat($m.find('[name="base_price"]').val()),
                        position_x: parseInt($m.find('[name="position_x"]').val()),
                        position_y: parseInt($m.find('[name="position_y"]').val()),
                        unit_group: $m.find('[name="unit_group"]').val(),
                        access_code: $m.find('[name="access_code"]').val()
                    };

                    // Save to database via AJAX
                    RoyalStorageUtils.ajax({
                        url: royalStorageLayoutAdmin.ajaxUrl,
                        data: {
                            action: 'update_unit_data',
                            nonce: royalStorageLayoutAdmin.nonce,
                            unit_id: unitId,
                            unit_data: formData
                        },
                        success: function() {
                            const idx = currentUnits.findIndex(u => u.id == unitId);
                            if (idx !== -1) currentUnits[idx] = { ...currentUnits[idx], ...formData };
                            renderLayoutGrid();
                            $m.find('.royal-storage-modal-close').click();
                            RoyalStorageUtils.showToast('Unit updated successfully');
                        }
                    });
                });
            }
        });
    }
});
