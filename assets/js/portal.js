/**
 * Royal Storage Portal JavaScript
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		initPortal();
		initBookingActions();
		initInvoiceActions();
		initAccountForms();
	});

	function initPortal() {
		var urlParams = new URLSearchParams(window.location.search);
		var activeTab = urlParams.get('tab') || 'dashboard';
		$('.portal-nav a').removeClass('active');
		$('.portal-nav a[data-tab="' + activeTab + '"]').addClass('active');
	}

	function initBookingActions() {
		$(document).on('click', '.renew-booking', function(e) {
			e.preventDefault();
			var bookingId = $(this).data('booking-id');
			var days = prompt('Enter number of days to renew:', '30');
			if (days && !isNaN(days)) {
				RoyalStorageUtils.ajax({
					url: royalStoragePortal.ajaxUrl,
					data: {
						action: 'royal_storage_renew_booking',
						nonce: royalStoragePortal.nonce,
						booking_id: bookingId,
						days: days
					},
					success: function() {
						RoyalStorageUtils.showToast('Booking renewed successfully');
						setTimeout(() => location.reload(), 1000);
					}
				});
			}
		});

		$(document).on('click', '.cancel-booking', function(e) {
			e.preventDefault();
			var bookingId = $(this).data('booking-id');
			if (confirm('Are you sure you want to cancel this booking?')) {
				RoyalStorageUtils.ajax({
					url: royalStoragePortal.ajaxUrl,
					data: {
						action: 'royal_storage_cancel_booking',
						nonce: royalStoragePortal.nonce,
						booking_id: bookingId
					},
					success: function() {
						RoyalStorageUtils.showToast('Booking cancelled successfully');
						setTimeout(() => location.reload(), 1000);
					}
				});
			}
		});
	}

	function initInvoiceActions() {
		$(document).on('click', '.download-invoice', function(e) {
			e.preventDefault();
			var invoiceId = $(this).data('invoice-id');
			RoyalStorageUtils.ajax({
				url: royalStoragePortal.ajaxUrl,
				data: {
					action: 'royal_storage_download_invoice',
					nonce: royalStoragePortal.nonce,
					invoice_id: invoiceId
				},
				success: function(response) {
					window.location.href = response.data.download_url;
				}
			});
		});

		$(document).on('click', '.pay-invoice', function(e) {
			e.preventDefault();
			var invoiceId = $(this).data('invoice-id');
			window.location.href = royalStoragePortal.checkoutUrl + '?invoice_id=' + invoiceId;
		});
	}

	function initAccountForms() {
		$('#profile-form').on('submit', function(e) {
			e.preventDefault();
			var formData = $(this).serializeArray();
			formData.push({ name: 'action', value: 'royal_storage_update_profile' });
			formData.push({ name: 'nonce', value: royalStoragePortal.nonce });

			RoyalStorageUtils.ajax({
				url: royalStoragePortal.ajaxUrl,
				data: $.param(formData),
				success: function() {
					RoyalStorageUtils.showToast('Profile updated successfully');
				}
			});
		});

		$('#password-form').on('submit', function(e) {
			e.preventDefault();
			var newPass = $('#new_password').val();
			if (newPass !== $('#confirm_password').val()) {
				RoyalStorageUtils.showToast('Passwords do not match', 'error');
				return;
			}

			var formData = $(this).serializeArray();
			formData.push({ name: 'action', value: 'royal_storage_change_password' });
			formData.push({ name: 'nonce', value: royalStoragePortal.nonce });

			RoyalStorageUtils.ajax({
				url: royalStoragePortal.ajaxUrl,
				data: $.param(formData),
				success: function() {
					RoyalStorageUtils.showToast('Password changed successfully');
					$('#password-form')[0].reset();
				}
			});
		});
	}

})(jQuery);
