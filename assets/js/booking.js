/**
 * Royal Storage Booking Form JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';

    let currentStep = 1;
    let selectedUnit = null;
    let bookingData = {};

    // Initialize booking form
    initBookingForm();

    function initBookingForm() {
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        $('#start_date').attr('min', today);
        $('#end_date').attr('min', today);

        // Event listeners
        $('#next-step').on('click', nextStep);
        $('#prev-step').on('click', prevStep);
        $('#royal-storage-booking-form').on('submit', function(e) {
            e.preventDefault();
            handleFormSubmit(e);
        });
        
        // Unit type change
        $('input[name="unit_type"]').on('change', handleUnitTypeChange);
        
        // Date change
        $('#start_date, #end_date').on('change', handleDateChange);
        
        // Period change
        $('#period').on('change', handlePeriodChange);

        // Unit selection integration
        $(document).on('click', '.btn-continue', function() {
            const selectedUnit = window.unitSelection ? window.unitSelection.getSelectedUnit() : null;
            if (selectedUnit) {
                bookingData.selected_unit = selectedUnit;
                bookingData.unit_id = selectedUnit.id;
                bookingData.unit_type = selectedUnit.size.toLowerCase();
                
                // Move to next step
                nextStep();
            }
        });

        // Expose functions globally for unit selection integration
        window.nextStep = nextStep;
        window.previousStep = prevStep;

        // Initialize first step
        updateStepVisibility();
    }

    function nextStep() {
        if (validateCurrentStep()) {
            currentStep++;
            updateStepVisibility();
            handleStepChange();
        }
    }

    function prevStep() {
        currentStep--;
        updateStepVisibility();
    }

    function updateStepVisibility() {
        $('.form-step').removeClass('active');
        $(`.form-step[data-step="${currentStep}"]`).addClass('active');
        
        // Update navigation buttons
        $('#prev-step').toggle(currentStep > 1);
        $('#next-step').toggle(currentStep < 4);
        $('#submit-booking').toggle(currentStep === 4);
    }

    function validateCurrentStep() {
        switch (currentStep) {
            case 1:
                return validateUnitType();
            case 2:
                return validateUnitSelection();
            case 3:
                return validateDates();
            case 4:
                return true;
            default:
                return false;
        }
    }

    function validateUnitType() {
        const unitType = $('input[name="unit_type"]:checked').val();
        if (!unitType) {
            showError('Please select a unit type.');
            return false;
        }
        bookingData.unit_type = unitType;
        return true;
    }

    function validateDates() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        
        if (!startDate || !endDate) {
            showError('Please select both start and end dates.');
            return false;
        }
        
        if (new Date(startDate) >= new Date(endDate)) {
            showError('End date must be after start date.');
            return false;
        }
        
        bookingData.start_date = startDate;
        bookingData.end_date = endDate;
        bookingData.period = $('#period').val();
        
        return true;
    }

    function validateUnitSelection() {
        const selectedUnit = window.unitSelection ? window.unitSelection.getSelectedUnit() : null;
        if (!selectedUnit) {
            showError('Please select a unit from the grid.');
            return false;
        }
        
        // Store selected unit data
        bookingData.selected_unit = selectedUnit;
        bookingData.unit_id = selectedUnit.id;
        bookingData.unit_type = selectedUnit.size.toLowerCase();
        
        return true;
    }

    function handleStepChange() {
        switch (currentStep) {
            case 2:
                // Unit selection step - no action needed, handled by unit selection component
                break;
            case 3:
                handleDateChange();
                break;
            case 4:
                loadBookingSummary();
                break;
        }
    }

    function handleUnitTypeChange() {
        // Reset selected unit when unit type changes
        selectedUnit = null;
        if (currentStep >= 3) {
            loadAvailableUnits();
        }
    }

    function handleDateChange() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        
        if (startDate && endDate) {
            // Update booking data
            bookingData.start_date = startDate;
            bookingData.end_date = endDate;
            
            // Update end date minimum to be after start date
            $('#end_date').attr('min', startDate);
            
            // Load available units if we're on step 3
            if (currentStep >= 3) {
                loadAvailableUnits();
            }
        }
    }

    function handlePeriodChange() {
        if (selectedUnit && currentStep >= 4) {
            loadBookingSummary();
        }
    }

    function loadAvailableUnits() {
        const unitType = bookingData.unit_type;
        const startDate = bookingData.start_date;
        const endDate = bookingData.end_date;
        
        if (!unitType || !startDate || !endDate) {
            return;
        }

        $('#available-units').html('<p class="loading">Loading available units...</p>');

        console.log('Sending AJAX request with data:', {
            action: 'get_available_units',
            nonce: royalStorageBooking.nonce,
            unit_type: unitType,
            start_date: startDate,
            end_date: endDate
        });
        
        $.ajax({
            url: royalStorageBooking.ajaxUrl,
            type: 'POST',
            data: {
                action: 'get_available_units',
                nonce: royalStorageBooking.nonce,
                unit_type: unitType,
                start_date: startDate,
                end_date: endDate
            },
            success: function(response) {
                console.log('AJAX success response:', response);
                if (response.success) {
                    displayAvailableUnits(response.data);
                } else {
                    showError(response.data.message || 'Failed to load available units.');
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX error:', xhr, status, error);
                showError('Failed to load available units. Please try again.');
            }
        });
    }

    function displayAvailableUnits(units) {
        if (units.length === 0) {
            $('#available-units').html('<p>No units available for the selected dates.</p>');
            return;
        }

        let html = '';
        units.forEach(function(unit) {
            const size = unit.size || unit.spot_number || 'N/A';
            const price = parseFloat(unit.base_price).toFixed(2);
            
            html += `
                <div class="unit-card" data-unit-id="${unit.id}" data-price="${unit.base_price}">
                    <div class="unit-size">${size}</div>
                    <h4>${bookingData.unit_type === 'storage' ? 'Storage Unit' : 'Parking Space'} #${unit.id}</h4>
                    <div class="unit-details">
                        ${bookingData.unit_type === 'storage' ? 
                            `Dimensions: ${unit.dimensions || 'N/A'}<br>
                             Amenities: ${unit.amenities || 'N/A'}` :
                            `Spot: ${unit.spot_number || 'N/A'}<br>
                             Height Limit: ${unit.height_limit || 'N/A'}`
                        }
                    </div>
                    <div class="unit-price">${price} RSD</div>
                </div>
            `;
        });

        $('#available-units').html(html);

        // Add click handlers for unit selection
        $('.unit-card').on('click', function() {
            $('.unit-card').removeClass('selected');
            $(this).addClass('selected');
            
            selectedUnit = {
                id: $(this).data('unit-id'),
                price: $(this).data('price')
            };
            
            bookingData.unit_id = selectedUnit.id;
        });
    }

    function loadBookingSummary() {
        const selectedUnit = bookingData.selected_unit;
        if (!selectedUnit) {
            return;
        }

        $('#booking-summary').html('<p class="loading">Calculating pricing...</p>');

        $.ajax({
            url: royalStorageBooking.ajaxUrl,
            type: 'POST',
            data: {
                action: 'calculate_booking_price',
                nonce: royalStorageBooking.nonce,
                unit_id: selectedUnit.id,
                unit_type: bookingData.unit_type,
                start_date: bookingData.start_date,
                end_date: bookingData.end_date,
                period: bookingData.period
            },
            success: function(response) {
                if (response.success) {
                    displayBookingSummary(response.data);
                } else {
                    showError(response.data.message || 'Failed to calculate pricing.');
                }
            },
            error: function() {
                showError('Failed to calculate pricing. Please try again.');
            }
        });
    }

    function displayBookingSummary(pricing) {
        const selectedUnit = bookingData.selected_unit;
        const startDate = new Date(bookingData.start_date).toLocaleDateString();
        const endDate = new Date(bookingData.end_date).toLocaleDateString();
        const days = Math.ceil((new Date(bookingData.end_date) - new Date(bookingData.start_date)) / (1000 * 60 * 60 * 24));
        
        const html = `
            <h4>Booking Summary</h4>
            <div class="summary-item">
                <span class="summary-label">Unit Type:</span>
                <span class="summary-value">${selectedUnit.size} Unit</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Unit ID:</span>
                <span class="summary-value">#${selectedUnit.id}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Dimensions:</span>
                <span class="summary-value">${selectedUnit.dimensions || 'N/A'}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Start Date:</span>
                <span class="summary-value">${startDate}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">End Date:</span>
                <span class="summary-value">${endDate}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Duration:</span>
                <span class="summary-value">${days} days</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Billing Period:</span>
                <span class="summary-value">${bookingData.period}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Base Price:</span>
                <span class="summary-value">${parseFloat(selectedUnit.base_price).toFixed(2)} RSD</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Subtotal:</span>
                <span class="summary-value">${parseFloat(pricing.subtotal).toFixed(2)} RSD</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">VAT (20%):</span>
                <span class="summary-value">${parseFloat(pricing.vat).toFixed(2)} RSD</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Total:</span>
                <span class="summary-value">${parseFloat(pricing.total).toFixed(2)} RSD</span>
            </div>
        `;

        $('#booking-summary').html(html);
    }

    function handleFormSubmit(e) {
        if (!validateCurrentStep()) {
            console.log('Validation failed for current step');
            return;
        }

        const selectedUnit = bookingData.selected_unit;
        if (!selectedUnit) {
            showError('Please select a unit.');
            return;
        }
        
        // Additional validation for required fields
        if (!bookingData.unit_type) {
            showError('Please select a unit type.');
            return;
        }
        
        if (!bookingData.start_date) {
            showError('Please select a start date.');
            return;
        }
        
        if (!bookingData.end_date) {
            showError('Please select an end date.');
            return;
        }

        // Show loading state
        $('#submit-booking').prop('disabled', true).text('Processing Booking...');

        $.ajax({
            url: royalStorageBooking.ajaxUrl,
            type: 'POST',
            data: {
                action: 'create_booking',
                nonce: royalStorageBooking.nonce,
                unit_id: selectedUnit.id,
                unit_type: bookingData.unit_type,
                start_date: bookingData.start_date,
                end_date: bookingData.end_date,
                period: bookingData.period
            },
            success: function(response) {
                if (response.success) {
                    showSuccess(response.data.message);
                    setTimeout(function() {
                        window.location.href = response.data.redirect_url;
                    }, 2000);
                } else {
                    showError(response.data.message || 'Failed to create booking.');
                    $('#submit-booking').prop('disabled', false).text('Book Now & Pay');
                }
            },
            error: function() {
                showError('Failed to create booking. Please try again.');
                $('#submit-booking').prop('disabled', false).text('Book Now & Pay');
            }
        });
    }

    function showError(message) {
        $('.error-message, .success-message').remove();
        $('.royal-storage-booking-form').prepend(`<div class="error-message">${message}</div>`);
        setTimeout(function() {
            $('.error-message').fadeOut();
        }, 5000);
    }

    function showSuccess(message) {
        $('.error-message, .success-message').remove();
        $('.royal-storage-booking-form').prepend(`<div class="success-message">${message}</div>`);
    }
});
