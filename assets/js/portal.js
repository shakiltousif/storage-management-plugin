/**
 * Royal Storage Portal JavaScript
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		// Initialize portal
		initPortal();
		initBookingActions();
		initInvoiceActions();
		initAccountForms();
	});

	/**
	 * Initialize portal
	 */
	function initPortal() {
		// Handle tab navigation
		$('.portal-nav a').on('click', function(e) {
			e.preventDefault();
			var tab = $(this).data('tab');
			switchTab(tab);
		});

		// Set active tab from URL
		var urlParams = new URLSearchParams(window.location.search);
		var activeTab = urlParams.get('tab') || 'dashboard';
		switchTab(activeTab);
	}

	/**
	 * Switch tab
	 */
	function switchTab(tab) {
		// Update URL
		window.history.pushState({}, '', '?tab=' + tab);

		// Update nav
		$('.portal-nav a').removeClass('active');
		$('.portal-nav a[data-tab="' + tab + '"]').addClass('active');

		// Update content
		$('.portal-content > div').hide();
		$('.portal-' + tab).show();
	}

	/**
	 * Initialize booking actions
	 */
	function initBookingActions() {
		// Renew booking
		$(document).on('click', '.renew-booking', function(e) {
			e.preventDefault();
			var bookingId = $(this).data('booking-id');
			var days = prompt('Enter number of days to renew:', '30');

			if (days && !isNaN(days)) {
				renewBooking(bookingId, parseInt(days));
			}
		});

		// Cancel booking
		$(document).on('click', '.cancel-booking', function(e) {
			e.preventDefault();
			var bookingId = $(this).data('booking-id');

			if (confirm('Are you sure you want to cancel this booking?')) {
				cancelBooking(bookingId);
			}
		});
	}

	/**
	 * Renew booking
	 */
	function renewBooking(bookingId, days) {
		$.ajax({
			url: royalStoragePortal.ajaxUrl,
			type: 'POST',
			data: {
				action: 'royal_storage_renew_booking',
				nonce: royalStoragePortal.nonce,
				booking_id: bookingId,
				days: days
			},
			success: function(response) {
				if (response.success) {
					alert('Booking renewed successfully!');
					location.reload();
				} else {
					alert('Error: ' + response.data.message);
				}
			},
			error: function() {
				alert('An error occurred. Please try again.');
			}
		});
	}

	/**
	 * Cancel booking
	 */
	function cancelBooking(bookingId) {
		$.ajax({
			url: royalStoragePortal.ajaxUrl,
			type: 'POST',
			data: {
				action: 'royal_storage_cancel_booking',
				nonce: royalStoragePortal.nonce,
				booking_id: bookingId
			},
			success: function(response) {
				if (response.success) {
					alert('Booking cancelled successfully!');
					location.reload();
				} else {
					alert('Error: ' + response.data.message);
				}
			},
			error: function() {
				alert('An error occurred. Please try again.');
			}
		});
	}

	/**
	 * Initialize invoice actions
	 */
	function initInvoiceActions() {
		// Download invoice
		$(document).on('click', '.download-invoice', function(e) {
			e.preventDefault();
			var invoiceId = $(this).data('invoice-id');
			downloadInvoice(invoiceId);
		});

		// Pay invoice
		$(document).on('click', '.pay-invoice', function(e) {
			e.preventDefault();
			var invoiceId = $(this).data('invoice-id');
			window.location.href = royalStoragePortal.checkoutUrl + '?invoice_id=' + invoiceId;
		});
	}

	/**
	 * Download invoice
	 */
	function downloadInvoice(invoiceId) {
		$.ajax({
			url: royalStoragePortal.ajaxUrl,
			type: 'POST',
			data: {
				action: 'royal_storage_download_invoice',
				nonce: royalStoragePortal.nonce,
				invoice_id: invoiceId
			},
			success: function(response) {
				if (response.success) {
					// Trigger download
					window.location.href = response.data.download_url;
				} else {
					alert('Error: ' + response.data.message);
				}
			},
			error: function() {
				alert('An error occurred. Please try again.');
			}
		});
	}

	/**
	 * Initialize account forms
	 */
	function initAccountForms() {
		// Update profile form
		$('#profile-form').on('submit', function(e) {
			e.preventDefault();
			updateProfile($(this));
		});

		// Change password form
		$('#password-form').on('submit', function(e) {
			e.preventDefault();
			changePassword($(this));
		});
	}

	/**
	 * Update profile
	 */
	function updateProfile(form) {
		var formData = form.serializeArray();
		formData.push({
			name: 'action',
			value: 'royal_storage_update_profile'
		});
		formData.push({
			name: 'nonce',
			value: royalStoragePortal.nonce
		});

		$.ajax({
			url: royalStoragePortal.ajaxUrl,
			type: 'POST',
			data: $.param(formData),
			success: function(response) {
				if (response.success) {
					alert('Profile updated successfully!');
				} else {
					alert('Error: ' + response.data.message);
				}
			},
			error: function() {
				alert('An error occurred. Please try again.');
			}
		});
	}

	/**
	 * Change password
	 */
	function changePassword(form) {
		var newPassword = form.find('#new_password').val();
		var confirmPassword = form.find('#confirm_password').val();

		if (newPassword !== confirmPassword) {
			alert('Passwords do not match!');
			return;
		}

		var formData = form.serializeArray();
		formData.push({
			name: 'action',
			value: 'royal_storage_change_password'
		});
		formData.push({
			name: 'nonce',
			value: royalStoragePortal.nonce
		});

		$.ajax({
			url: royalStoragePortal.ajaxUrl,
			type: 'POST',
			data: $.param(formData),
			success: function(response) {
				if (response.success) {
					alert('Password changed successfully!');
					form[0].reset();
				} else {
					alert('Error: ' + response.data.message);
				}
			},
			error: function() {
				alert('An error occurred. Please try again.');
			}
		});
	}

})(jQuery);

