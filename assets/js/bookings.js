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
		// View booking button
		$(document).on('click', '.view-booking', function(e) {
			e.preventDefault();
			var bookingId = $(this).data('booking-id');
			viewBookingDetails(bookingId);
		});

		// Approve booking button
		$(document).on('click', '.approve-booking', function(e) {
			e.preventDefault();
			var bookingId = $(this).data('booking-id');
			approveBooking(bookingId);
		});

		// Cancel booking button (admin page)
		$(document).on('click', '.cancel-booking-btn', function(e) {
			e.preventDefault();
			var bookingId = $(this).data('booking-id');
			showCancelConfirmation(bookingId);
		});

		// Renew booking button
		$(document).on('click', '.renew-booking', function(e) {
			e.preventDefault();
			var bookingId = $(this).data('booking-id');
			showRenewDialog(bookingId);
		});

		// Mark as paid button
		$(document).on('click', '.mark-paid-btn', function(e) {
			e.preventDefault();
			var bookingId = $(this).data('booking-id');
			var notes = $('.payment-notes-field').val();
			markAsPaid(bookingId, notes);
		});

		// Cancel booking button (legacy support)
		$(document).on('click', '.cancel-booking', function(e) {
			e.preventDefault();
			var bookingId = $(this).data('booking-id');
			showCancelConfirmation(bookingId);
		});
	}

	/**
	 * View booking details in modal
	 */
	function viewBookingDetails(bookingId) {
		if (typeof RoyalStorageUtils === 'undefined') {
			alert('Required utilities not loaded. Please refresh the page.');
			return;
		}

		RoyalStorageUtils.ajax({
			url: royalStorageAdmin.ajaxUrl,
			data: {
				action: 'get_booking_details',
				nonce: royalStorageAdmin.nonce,
				booking_id: bookingId
			},
			success: function(response) {
				var booking = response.data.booking;

				var content = '<div class="booking-details">' +
					'<div class="booking-detail-row">' +
						'<strong>Booking ID:</strong> <span>#' + booking.id + '</span>' +
					'</div>' +
					'<div class="booking-detail-row">' +
						'<strong>Customer Name:</strong> <span>' + booking.customer_name + '</span>' +
					'</div>' +
					'<div class="booking-detail-row">' +
						'<strong>Customer Email:</strong> <span>' + booking.customer_email + '</span>' +
					'</div>' +
					'<div class="booking-detail-row">' +
						'<strong>Unit/Space:</strong> <span>' + booking.unit_details + '</span>' +
					'</div>' +
					'<div class="booking-detail-row">' +
						'<strong>Period:</strong> <span>' + booking.period + '</span>' +
					'</div>' +
					'<div class="booking-detail-row">' +
						'<strong>Start Date:</strong> <span>' + booking.start_date + '</span>' +
					'</div>' +
					'<div class="booking-detail-row">' +
						'<strong>End Date:</strong> <span>' + booking.end_date + '</span>' +
					'</div>' +
					'<div class="booking-detail-row">' +
						'<strong>Total Price:</strong> <span>' + booking.total_price + ' RSD</span>' +
					'</div>' +
					'<div class="booking-detail-row">' +
						'<strong>Status:</strong> <span class="status-badge status-' + booking.status.toLowerCase() + '">' + booking.status + '</span>' +
					'</div>' +
					'<div class="booking-detail-row">' +
						'<strong>Payment Status:</strong> <span class="status-badge status-' + booking.payment_status.toLowerCase() + '">' + booking.payment_status + '</span>' +
					'</div>' +
					'<div class="booking-detail-row">' +
						'<strong>Created At:</strong> <span>' + booking.created_at + '</span>' +
					'</div>';

				// Add payment notes field if payment is not complete
				if (booking.payment_status.toLowerCase() !== 'paid') {
					content += '<div class="booking-detail-row" style="margin-top: 1.5rem; flex-direction: column; align-items: flex-start;">' +
						'<strong style="margin-bottom: 0.5rem;">Payment Notes:</strong>' +
						'<textarea class="payment-notes-field" rows="3" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;" placeholder="Enter partial payment amount or notes (e.g., Paid 50,000 RSD of 360,000 RSD)"></textarea>' +
						'<small style="color: #666; margin-top: 0.25rem;">Use this field to record partial payments or payment details</small>' +
					'</div>';
				}

				content += '</div>';

				// Build footer with Mark as Paid button if unpaid
				var footer = '';
				if (booking.payment_status.toLowerCase() !== 'paid') {
					footer += '<button class="button button-primary mark-paid-btn" data-booking-id="' + booking.id + '" style="margin-right: 10px;">Mark as Paid</button>';
				}
				footer += '<button class="royal-storage-btn royal-storage-btn-secondary modal-close">Close</button>';

				RoyalStorageUtils.openModal({
					title: 'Booking Details #' + booking.id,
					content: content,
					footer: footer,
					onOpen: function($modal) {
						$modal.find('.modal-close').on('click', function() {
							$modal.find('.royal-storage-modal-close').click();
						});
					}
				});
			},
			error: function() {
				RoyalStorageUtils.showToast('Failed to load booking details', 'error');
			}
		});
	}

	/**
	 * Approve booking
	 */
	function approveBooking(bookingId) {
		if (!confirm('Are you sure you want to approve this booking?')) {
			return;
		}

		if (typeof RoyalStorageUtils === 'undefined') {
			alert('Required utilities not loaded. Please refresh the page.');
			return;
		}

		RoyalStorageUtils.ajax({
			url: royalStorageAdmin.ajaxUrl,
			data: {
				action: 'approve_booking',
				nonce: royalStorageAdmin.nonce,
				booking_id: bookingId
			},
			success: function(response) {
				RoyalStorageUtils.showToast(response.data.message, 'success');
				setTimeout(function() {
					location.reload();
				}, 1500);
			},
			error: function(response) {
				var message = response.data && response.data.message ? response.data.message : 'Failed to approve booking';
				RoyalStorageUtils.showToast(message, 'error');
			}
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
		if (typeof RoyalStorageUtils !== 'undefined') {
			RoyalStorageUtils.ajax({
				url: royalStorageAdmin.ajaxUrl,
				data: {
					action: 'cancel_booking_ajax',
					nonce: royalStorageAdmin.nonce,
					booking_id: bookingId
				},
				success: function(response) {
					RoyalStorageUtils.showToast(response.data.message, 'success');
					setTimeout(function() {
						location.reload();
					}, 1500);
				},
				error: function(response) {
					var message = response.data && response.data.message ? response.data.message : 'Failed to cancel booking';
					RoyalStorageUtils.showToast(message, 'error');
				}
			});
		} else {
			// Fallback to legacy implementation
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
	}

	/**
	 * Mark booking as paid
	 */
	function markAsPaid(bookingId, notes) {
		if (!confirm('Are you sure you want to mark this booking as paid?')) {
			return;
		}

		if (typeof RoyalStorageUtils === 'undefined') {
			alert('Required utilities not loaded. Please refresh the page.');
			return;
		}

		RoyalStorageUtils.ajax({
			url: royalStorageAdmin.ajaxUrl,
			data: {
				action: 'mark_booking_paid',
				nonce: royalStorageAdmin.nonce,
				booking_id: bookingId,
				payment_notes: notes
			},
			success: function(response) {
				RoyalStorageUtils.showToast(response.data.message, 'success');
				// Close modal
				$('.royal-storage-modal-close').click();
				setTimeout(function() {
					location.reload();
				}, 1500);
			},
			error: function(response) {
				var message = response.data && response.data.message ? response.data.message : 'Failed to mark booking as paid';
				RoyalStorageUtils.showToast(message, 'error');
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

