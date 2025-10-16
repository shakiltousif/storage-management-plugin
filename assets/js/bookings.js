/**
 * Royal Storage Bookings JavaScript
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		initBookings();
	});

	/**
	 * Initialize bookings
	 */
	function initBookings() {
		// Renew booking button
		$(document).on('click', '.renew-booking', function(e) {
			e.preventDefault();
			var bookingId = $(this).data('booking-id');
			showRenewDialog(bookingId);
		});

		// Cancel booking button
		$(document).on('click', '.cancel-booking', function(e) {
			e.preventDefault();
			var bookingId = $(this).data('booking-id');
			showCancelConfirmation(bookingId);
		});
	}

	/**
	 * Show renew dialog
	 */
	function showRenewDialog(bookingId) {
		var days = prompt('Enter number of days to renew:', '30');

		if (days && !isNaN(days) && parseInt(days) > 0) {
			renewBooking(bookingId, parseInt(days));
		} else if (days !== null) {
			alert('Please enter a valid number of days.');
		}
	}

	/**
	 * Show cancel confirmation
	 */
	function showCancelConfirmation(bookingId) {
		if (confirm('Are you sure you want to cancel this booking? This action cannot be undone.')) {
			cancelBooking(bookingId);
		}
	}

	/**
	 * Renew booking
	 */
	function renewBooking(bookingId, days) {
		$.ajax({
			url: royalStorageBookings.ajaxUrl,
			type: 'POST',
			data: {
				action: 'royal_storage_renew_booking',
				nonce: royalStorageBookings.nonce,
				booking_id: bookingId,
				days: days
			},
			beforeSend: function() {
				showLoadingSpinner();
			},
			success: function(response) {
				hideLoadingSpinner();
				if (response.success) {
					showSuccessMessage('Booking renewed successfully! New end date: ' + response.data.new_end_date);
					setTimeout(function() {
						location.reload();
					}, 2000);
				} else {
					showErrorMessage('Error: ' + response.data.message);
				}
			},
			error: function() {
				hideLoadingSpinner();
				showErrorMessage('An error occurred. Please try again.');
			}
		});
	}

	/**
	 * Cancel booking
	 */
	function cancelBooking(bookingId) {
		$.ajax({
			url: royalStorageBookings.ajaxUrl,
			type: 'POST',
			data: {
				action: 'royal_storage_cancel_booking',
				nonce: royalStorageBookings.nonce,
				booking_id: bookingId
			},
			beforeSend: function() {
				showLoadingSpinner();
			},
			success: function(response) {
				hideLoadingSpinner();
				if (response.success) {
					showSuccessMessage('Booking cancelled successfully!');
					setTimeout(function() {
						location.reload();
					}, 2000);
				} else {
					showErrorMessage('Error: ' + response.data.message);
				}
			},
			error: function() {
				hideLoadingSpinner();
				showErrorMessage('An error occurred. Please try again.');
			}
		});
	}

	/**
	 * Show loading spinner
	 */
	function showLoadingSpinner() {
		$('body').append('<div class="royal-storage-spinner"><div class="spinner"></div></div>');
	}

	/**
	 * Hide loading spinner
	 */
	function hideLoadingSpinner() {
		$('.royal-storage-spinner').remove();
	}

	/**
	 * Show success message
	 */
	function showSuccessMessage(message) {
		showNotification(message, 'success');
	}

	/**
	 * Show error message
	 */
	function showErrorMessage(message) {
		showNotification(message, 'error');
	}

	/**
	 * Show notification
	 */
	function showNotification(message, type) {
		var notificationClass = type === 'success' ? 'notification-success' : 'notification-error';
		var notification = $('<div class="royal-storage-notification ' + notificationClass + '">' + message + '</div>');

		$('body').append(notification);

		notification.fadeIn(300);

		setTimeout(function() {
			notification.fadeOut(300, function() {
				$(this).remove();
			});
		}, 5000);
	}

})(jQuery);

/* Notification Styles */
<style>
	.royal-storage-spinner {
		position: fixed;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background: rgba(0, 0, 0, 0.5);
		display: flex;
		align-items: center;
		justify-content: center;
		z-index: 9999;
	}

	.spinner {
		border: 4px solid #f3f3f3;
		border-top: 4px solid #667eea;
		border-radius: 50%;
		width: 40px;
		height: 40px;
		animation: spin 1s linear infinite;
	}

	@keyframes spin {
		0% { transform: rotate(0deg); }
		100% { transform: rotate(360deg); }
	}

	.royal-storage-notification {
		position: fixed;
		top: 20px;
		right: 20px;
		padding: 15px 20px;
		border-radius: 5px;
		color: white;
		font-weight: bold;
		z-index: 10000;
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
		display: none;
	}

	.notification-success {
		background: #27ae60;
	}

	.notification-error {
		background: #e74c3c;
	}
</style>

