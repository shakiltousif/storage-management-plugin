/**
 * Royal Storage Booking Form JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';

    let currentStep = 1;
    let bookingData = {
        unit_type: null,
        unit_id: null,
        selected_unit: null,
        start_date: null,
        end_date: null,
        period: 'monthly'
    };

    initBookingForm();

    function initBookingForm() {
        // Set min dates
        const today = new Date().toISOString().split('T')[0];
        $('#start_date').attr('min', today);

        // Auto-calculate end date when start date is selected (1 month later)
        $('#start_date').on('change', function() {
            const startDate = $(this).val();
            if (startDate) {
                const start = new Date(startDate);
                const end = new Date(start);
                end.setMonth(end.getMonth() + 1);

                const endDateStr = end.toISOString().split('T')[0];
                $('#end_date').val(endDateStr);
                bookingData.start_date = startDate;
                bookingData.end_date = endDateStr;
            }
        });

        // Event: Unit Type Card Click
        $('.unit-type-card').on('click', function() {
            const type = $(this).find('input[name="unit_type"]').val();
            $(this).find('input[name="unit_type"]').prop('checked', true);
            bookingData.unit_type = type;
            
            // Auto move to next step
            setTimeout(() => nextStep(), 300);
        });

        // Event: Unit Selection from Grid
        $(document).on('unit_selected', function(e, unit) {
            bookingData.selected_unit = unit;
            bookingData.unit_id = unit.id;
            
            // Enable next button or auto-advance
            $('#next-step').prop('disabled', false).text('Continue with Unit #' + unit.id);
        });

        // Navigation
        $('#next-step').on('click', nextStep);
        $('#prev-step').on('click', prevStep);
        
        $('#royal-storage-booking-form').on('submit', (e) => {
            e.preventDefault();
            handleFormSubmit();
        });

        updateStepVisibility();
    }

    function nextStep() {
        if (validateCurrentStep()) {
            currentStep++;
            updateStepVisibility();
            handleStepChange();
            window.scrollTo({ top: $('.royal-storage-booking').offset().top - 50, behavior: 'smooth' });
        }
    }

    function prevStep() {
        currentStep--;
        updateStepVisibility();
        window.scrollTo({ top: $('.royal-storage-booking').offset().top - 50, behavior: 'smooth' });
    }

    function updateStepVisibility() {
        $('.form-step').removeClass('active');
        $(`.form-step[data-step="${currentStep}"]`).addClass('active');
        
        // Progress Steps
        $('.royal-storage-form-step').removeClass('active completed');
        $('.royal-storage-form-step').each(function() {
            const s = $(this).data('step');
            if (s < currentStep) $(this).addClass('completed');
            if (s === currentStep) $(this).addClass('active');
        });

        // Buttons
        $('#prev-step').toggle(currentStep > 1);
        $('#next-step').toggle(currentStep < 5);
        $('#submit-booking').toggle(currentStep === 5);

        // Step-specific button state
        if (currentStep === 2) {
            const hasUnit = !!(window.unitSelection && window.unitSelection.getSelectedUnit());
            $('#next-step').prop('disabled', !hasUnit);
            if (!hasUnit) $('#next-step').text('Select a Unit to Continue');
        } else if (currentStep === 5) {
            // Keep submit button disabled until summary loads
            $('#submit-booking').prop('disabled', true).text('Loading Summary...');
        } else {
            $('#next-step').prop('disabled', false).text('Next Step');
        }
    }

    function validateCurrentStep() {
        switch (currentStep) {
            case 1:
                if (!bookingData.unit_type) {
                    RoyalStorageUtils.showToast('Please select a unit type', 'error');
                    return false;
                }
                return true;
            case 2:
                const unit = window.unitSelection ? window.unitSelection.getSelectedUnit() : null;
                if (!unit) {
                    RoyalStorageUtils.showToast('Please select a unit from the grid', 'error');
                    return false;
                }
                bookingData.selected_unit = unit;
                bookingData.unit_id = unit.id;
                return true;
            case 3:
                const start = $('#start_date').val();
                const end = $('#end_date').val();
                if (!start || !end) {
                    RoyalStorageUtils.showToast('Please select both dates', 'error');
                    return false;
                }
                if (new Date(start) >= new Date(end)) {
                    RoyalStorageUtils.showToast('End date must be after start date', 'error');
                    return false;
                }
                bookingData.start_date = start;
                bookingData.end_date = end;
                bookingData.period = $('#period').val();
                return true;
            case 4:
                return validateCustomerInfo();
            default:
                return true;
        }
    }

    function validateCustomerInfo() {
        if (window.royalStorageBooking.isLoggedIn) return true;
        
        const email = $('#guest_email').val();
        const fname = $('#guest_first_name').val();
        const lname = $('#guest_last_name').val();
        const phone = $('#guest_phone').val();

        if (!email || !fname || !lname || !phone) {
            RoyalStorageUtils.showToast('All fields are required', 'error');
            return false;
        }
        return true;
    }

    function handleStepChange() {
        if (currentStep === 2 && window.unitSelection) {
            window.unitSelection.refreshUnits();
        }
        if (currentStep === 5) {
            loadSummary();
        }
    }

    function loadSummary() {
        RoyalStorageUtils.ajax({
            url: royalStorageBooking.ajaxUrl,
            data: {
                action: 'calculate_booking_price',
                nonce: royalStorageBooking.nonce,
                unit_id: bookingData.unit_id,
                unit_type: bookingData.unit_type,
                start_date: bookingData.start_date,
                end_date: bookingData.end_date,
                period: bookingData.period
            },
            beforeSend: () => {
                $('#booking-summary').html(`
                    <div class="summary-card skeleton">
                        <div class="summary-section">
                            <div class="skeleton-line" style="width: 40%; height: 20px; margin-bottom: 1rem;"></div>
                            <div class="skeleton-line" style="width: 100%; height: 15px; margin-bottom: 0.5rem;"></div>
                            <div class="skeleton-line" style="width: 80%; height: 15px;"></div>
                        </div>
                        <div class="summary-section">
                            <div class="skeleton-line" style="width: 30%; height: 20px; margin-bottom: 1rem;"></div>
                            <div class="skeleton-line" style="width: 100%; height: 15px;"></div>
                        </div>
                    </div>
                `);
                $('#submit-booking').prop('disabled', true).text('Calculating...');
            },
            success: function(response) {
                renderSummary(response.data);
                $('#submit-booking').prop('disabled', false).text('Book Now & Pay');
            },
            onError: () => {
                $('#booking-summary').html('<div class="error-notice">Failed to load summary. Please go back and try again.</div>');
                $('#submit-booking').prop('disabled', true).text('Error Loading Summary');
            }
        });
    }

    function renderSummary(pricing) {
        const u = bookingData.selected_unit;
        const html = `
            <div class="summary-card">
                <div class="summary-section">
                    <h4><span class="icon">📦</span> Unit Information</h4>
                    <div class="summary-row"><span>Type</span><span>${bookingData.unit_type.toUpperCase()}</span></div>
                    <div class="summary-row"><span>Unit ID</span><span>#${u.id}</span></div>
                    <div class="summary-row"><span>Size</span><span>${u.size}</span></div>
                </div>
                <div class="summary-section">
                    <h4><span class="icon">📅</span> Schedule</h4>
                    <div class="summary-row"><span>Start</span><span>${bookingData.start_date}</span></div>
                    <div class="summary-row"><span>End</span><span>${bookingData.end_date}</span></div>
                    <div class="summary-row"><span>Billing Period</span><span>Monthly</span></div>
                </div>
                <div class="summary-section total">
                    <div class="summary-row"><span>Subtotal</span><span>${parseFloat(pricing.subtotal).toFixed(2)} RSD</span></div>
                    <div class="summary-row"><span>VAT (20%)</span><span>${parseFloat(pricing.vat).toFixed(2)} RSD</span></div>
                    <div class="summary-row final"><span>Total Due</span><strong>${parseFloat(pricing.total).toFixed(2)} RSD</strong></div>
                </div>
            </div>`;
        $('#booking-summary').html(html);
    }

    function handleFormSubmit() {
        const data = {
            action: 'create_booking',
            nonce: royalStorageBooking.nonce,
            ...bookingData
        };

        if (!window.royalStorageBooking.isLoggedIn) {
            data.guest_email = $('#guest_email').val();
            data.guest_first_name = $('#guest_first_name').val();
            data.guest_last_name = $('#guest_last_name').val();
            data.guest_phone = $('#guest_phone').val();
            data.create_account = $('input[name="create_account"]').is(':checked') ? 1 : 0;
        }

        RoyalStorageUtils.ajax({
            url: royalStorageBooking.ajaxUrl,
            data: data,
            beforeSend: () => {
                RoyalStorageUtils.showLoading('Processing your booking...');
                $('#submit-booking').prop('disabled', true).text('Processing...');
            },
            success: function(response) {
                RoyalStorageUtils.showToast('Booking successful! Redirecting to payment...', 'success');
                setTimeout(() => window.location.href = response.data.redirect_url, 2000);
            },
            onError: () => {
                $('#submit-booking').prop('disabled', false).text('Book Now & Pay');
            }
        });
    }
});
