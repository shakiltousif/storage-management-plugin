/**
 * Royal Storage Admin JavaScript
 */

(function($) {
	'use strict';

	var RoyalStorageAdmin = {
		init: function() {
			this.bindEvents();
		},

		bindEvents: function() {
			$(document).on('click', '.royal-storage-btn-delete', this.deleteItem.bind(this));
			$(document).on('click', '.royal-storage-btn-edit', this.editItem.bind(this));
			$(document).on('submit', '.royal-storage-form', this.submitForm.bind(this));
		},

		deleteItem: function(e) {
			e.preventDefault();

			if (!confirm('Are you sure you want to delete this item?')) {
				return;
			}

			var $btn = $(e.currentTarget);
			var itemId = $btn.data('id');
			var itemType = $btn.data('type');

			RoyalStorageUtils.ajax({
				url: royalStorageAdmin.ajaxUrl,
				data: {
					action: 'royal_storage_delete_' + itemType,
					id: itemId,
					nonce: royalStorageAdmin.nonce
				},
				success: function(response) {
					RoyalStorageUtils.showToast('Deleted successfully');
					setTimeout(() => location.reload(), 1000);
				}
			});
		},

		editItem: function(e) {
			e.preventDefault();
			var $btn = $(e.currentTarget);
			var itemId = $btn.data('id');
			var itemType = $btn.data('type');
			window.location.href = '?page=royal-storage-' + itemType + '&action=edit&id=' + itemId;
		},

		submitForm: function(e) {
			e.preventDefault();
			var $form = $(e.target);
			var formData = $form.serialize();

			RoyalStorageUtils.ajax({
				url: royalStorageAdmin.ajaxUrl,
				data: formData + '&nonce=' + royalStorageAdmin.nonce,
				success: function(response) {
					RoyalStorageUtils.showToast('Saved successfully');
					setTimeout(() => location.reload(), 1000);
				}
			});
		}
	};

	$(document).ready(function() {
		RoyalStorageAdmin.init();
	});

})(jQuery);

	/**
	 * Unit Reassignment Functionality
	 * Added: 2026-01-06
	 */
	const RoyalStorageReassignment = {
		currentBooking: null,

		init: function() {
			this.bindEvents();
		},

		bindEvents: function() {
			const self = this;

			// Open reassignment modal
			$(document).on('click', '.reassign-unit-btn', function() {
				const bookingId = $(this).data('booking-id');
				self.openModal(bookingId);
			});

			// Close modal
			$(document).on('click', '.modal-close, .modal-overlay', function() {
				self.closeModal();
			});

			// Confirm reassignment
			$(document).on('click', '#confirm-reassignment', function() {
				self.performReassignment();
			});
		},

		openModal: function(bookingId) {
			const self = this;

			// Fetch booking details
			$.ajax({
				url: royalStorageAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'get_booking_details',
					nonce: royalStorageAdmin.nonce,
					booking_id: bookingId
				},
				success: function(response) {
					if (response.success) {
						self.currentBooking = response.data.booking;
						self.displayModal(self.currentBooking);
						self.loadAvailableUnits(self.currentBooking);
					} else {
						alert(response.data.message || 'Failed to load booking details');
					}
				},
				error: function() {
					alert('Error loading booking details');
				}
			});
		},

		displayModal: function(booking) {
			$('#reassign-booking-id').text(booking.id);
			$('#reassign-current-unit').text('Unit #' + booking.unit_id);
			$('#reassign-dates').text(booking.start_date + ' to ' + booking.end_date);
			$('#reassign-unit-modal').fadeIn(200);
		},

		loadAvailableUnits: function(booking) {
			$.ajax({
				url: royalStorageAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'get_available_units_for_reassignment',
					nonce: royalStorageAdmin.nonce,
					booking_id: booking.id,
					unit_type: booking.unit_type || 'storage',
					start_date: booking.start_date,
					end_date: booking.end_date
				},
				beforeSend: function() {
					$('#new-unit-id').html('<option value="">Loading...</option>').prop('disabled', true);
				},
				success: function(response) {
					if (response.success && response.data.units) {
						const units = response.data.units;
						let options = '<option value="">-- Select a unit --</option>';

						units.forEach(function(unit) {
							const dimensions = unit.dimensions || '';
							const price = unit.base_price || '';
							options += `<option value="${unit.id}">Unit #${unit.id} - ${unit.size} ${dimensions} - ${price} RSD</option>`;
						});

						$('#new-unit-id').html(options).prop('disabled', false);
					} else {
						$('#new-unit-id').html('<option value="">No available units found</option>');
						alert('No available units found for the selected dates');
					}
				},
				error: function() {
					$('#new-unit-id').html('<option value="">Error loading units</option>');
					alert('Error loading available units');
				}
			});
		},

		performReassignment: function() {
			const self = this;
			const newUnitId = $('#new-unit-id').val();

			if (!newUnitId) {
				alert('Please select a unit');
				return;
			}

			if (!confirm('Are you sure you want to reassign this booking to a different unit?')) {
				return;
			}

			$.ajax({
				url: royalStorageAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'reassign_booking_unit',
					nonce: royalStorageAdmin.nonce,
					booking_id: self.currentBooking.id,
					new_unit_id: newUnitId
				},
				beforeSend: function() {
					$('#confirm-reassignment').prop('disabled', true).text('Reassigning...');
				},
				success: function(response) {
					if (response.success) {
						alert('Unit reassigned successfully!');
						self.closeModal();
						location.reload(); // Refresh the page to show updated data
					} else {
						alert(response.data.message || 'Failed to reassign unit');
						$('#confirm-reassignment').prop('disabled', false).text('Reassign Unit');
					}
				},
				error: function() {
					alert('Error reassigning unit');
					$('#confirm-reassignment').prop('disabled', false).text('Reassign Unit');
				}
			});
		},

		closeModal: function() {
			$('#reassign-unit-modal').fadeOut(200);
			this.currentBooking = null;
			$('#new-unit-id').html('<option value="">Loading available units...</option>');
		}
	};

	$(document).ready(function() {
		RoyalStorageReassignment.init();
	});

