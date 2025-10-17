/**
 * Royal Storage Checkout JavaScript
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

jQuery(document).ready(function($) {
    'use strict';

    // Handle payment form submission
    $('#payment-form').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        const originalText = $submitBtn.text();
        
        // Show loading state
        $submitBtn.prop('disabled', true).text('Processing Payment...');
        
        // Get form data
        const formData = {
            action: 'royal_storage_process_payment',
            nonce: $form.find('input[name="nonce"]').val(),
            booking_id: $form.find('input[name="booking_id"]').val(),
            invoice_id: $form.find('input[name="invoice_id"]').val(),
            amount: $form.find('input[name="amount"]').val(),
            payment_method: $form.find('select[name="payment_method"]').val()
        };
        
        // Send AJAX request
        $.ajax({
            url: royalStorageCheckout.ajaxUrl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showSuccess(response.data.message);
                    
                    // Redirect to success page or update UI
                    setTimeout(function() {
                        window.location.href = royalStorageCheckout.portalUrl;
                    }, 2000);
                } else {
                    showError(response.data.message || 'Payment failed. Please try again.');
                    $submitBtn.prop('disabled', false).text(originalText);
                }
            },
            error: function(xhr, status, error) {
                console.error('Payment error:', error);
                showError('Payment failed. Please try again.');
                $submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });
    
    // Show success message
    function showSuccess(message) {
        $('.error-message').remove();
        $('<div class="success-message" style="color: green; padding: 10px; margin: 10px 0; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px;">' + message + '</div>').insertAfter('.checkout-form h2');
    }
    
    // Show error message
    function showError(message) {
        $('.success-message').remove();
        $('<div class="error-message" style="color: red; padding: 10px; margin: 10px 0; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px;">' + message + '</div>').insertAfter('.checkout-form h2');
    }
});