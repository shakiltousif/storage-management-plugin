/**
 * Royal Storage Checkout JavaScript
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		initCheckout();
	});

	/**
	 * Initialize checkout
	 */
	function initCheckout() {
		$('#payment-form').on('submit', function(e) {
			e.preventDefault();
			processPayment($(this));
		});
	}

	/**
	 * Process payment
	 */
	function processPayment(form) {
		var bookingId = form.find('input[name="booking_id"]').val();
		var invoiceId = form.find('input[name="invoice_id"]').val();
		var amount = form.find('input[name="amount"]').val();
		var paymentMethod = form.find('select[name="payment_method"]').val();
		var nonce = form.find('input[name="nonce"]').val();

		// Disable submit button
		var submitBtn = form.find('button[type="submit"]');
		submitBtn.prop('disabled', true).text('Processing...');

		$.ajax({
			url: royalStorageCheckout.ajaxUrl,
			type: 'POST',
			data: {
				action: 'royal_storage_process_payment',
				nonce: nonce,
				booking_id: bookingId,
				invoice_id: invoiceId,
				amount: amount,
				payment_method: paymentMethod
			},
			success: function(response) {
				if (response.success) {
					// Redirect to payment gateway or success page
					if (response.data.redirect) {
						window.location.href = response.data.redirect;
					} else {
						alert('Payment initiated successfully!');
						location.reload();
					}
				} else {
					alert('Error: ' + response.data.message);
					submitBtn.prop('disabled', false).text('Proceed to Payment');
				}
			},
			error: function() {
				alert('An error occurred. Please try again.');
				submitBtn.prop('disabled', false).text('Proceed to Payment');
			}
		});
	}

})(jQuery);

