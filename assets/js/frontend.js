/**
 * Royal Storage Frontend JavaScript
 */

(function($) {
	'use strict';

	var RoyalStorageBooking = {
		init: function() {
			this.bindEvents();
		},

		bindEvents: function() {
			$(document).on('change', '.royal-storage-unit-type', this.onUnitTypeChange.bind(this));
			$(document).on('change', '.royal-storage-start-date', this.onDateChange.bind(this));
			$(document).on('change', '.royal-storage-end-date', this.onDateChange.bind(this));
			$(document).on('click', '.royal-storage-unit-card', this.selectUnit.bind(this));
			$(document).on('submit', '.royal-storage-booking-form', this.submitBooking.bind(this));
		},

		onUnitTypeChange: function(e) {
			var unitType = $(e.target).val();
			this.loadAvailableUnits(unitType);
		},

		onDateChange: function(e) {
			var startDate = $('.royal-storage-start-date').val();
			var endDate = $('.royal-storage-end-date').val();

			if (startDate && endDate) {
				this.calculatePrice(startDate, endDate);
			}
		},

		loadAvailableUnits: function(unitType) {
			var startDate = $('.royal-storage-start-date').val();
			var endDate = $('.royal-storage-end-date').val();

			if (!startDate || !endDate) {
				alert('Please select start and end dates first.');
				return;
			}

			$.ajax({
				url: royalStorageData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'get_available_units',
					unit_type: unitType,
					start_date: startDate,
					end_date: endDate,
					nonce: royalStorageData.nonce
				},
				success: function(response) {
					if (response.success) {
						RoyalStorageBooking.renderUnits(response.data);
					} else {
						alert('Error loading units: ' + response.data.message);
					}
				},
				error: function() {
					alert('An error occurred. Please try again.');
				}
			});
		},

		renderUnits: function(units) {
			var html = '';

			if (units.length === 0) {
				html = '<p>No units available for the selected dates.</p>';
			} else {
				$.each(units, function(index, unit) {
					html += '<div class="royal-storage-unit-card" data-id="' + unit.id + '">';
					html += '<h4>' + unit.post_title + '</h4>';
					html += '<p>Size: ' + (unit.size || 'N/A') + '</p>';
					html += '<div class="royal-storage-unit-price">RSD ' + unit.base_price + '/day</div>';
					html += '</div>';
				});
			}

			$('.royal-storage-units').html(html);
		},

		selectUnit: function(e) {
			var $card = $(e.target).closest('.royal-storage-unit-card');
			var unitId = $card.data('id');

			$('.royal-storage-unit-card').removeClass('selected');
			$card.addClass('selected');

			$('.royal-storage-selected-unit').val(unitId);
		},

		calculatePrice: function(startDate, endDate) {
			var start = new Date(startDate);
			var end = new Date(endDate);
			var days = Math.ceil((end - start) / (1000 * 60 * 60 * 24));

			// This is a placeholder - actual calculation should be done on the server
			var basePrice = 100; // Should be fetched from unit
			var subtotal = days * basePrice;
			var vat = subtotal * 0.20; // 20% VAT
			var total = subtotal + vat;

			$('.royal-storage-price-subtotal').text(subtotal.toFixed(2));
			$('.royal-storage-price-vat').text(vat.toFixed(2));
			$('.royal-storage-price-total').text(total.toFixed(2));
		},

		submitBooking: function(e) {
			e.preventDefault();

			var unitId = $('.royal-storage-selected-unit').val();
			var unitType = $('.royal-storage-unit-type').val();
			var startDate = $('.royal-storage-start-date').val();
			var endDate = $('.royal-storage-end-date').val();
			var paymentMethod = $('.royal-storage-payment-method').val();

			if (!unitId || !unitType || !startDate || !endDate) {
				alert('Please fill in all required fields.');
				return;
			}

			$.ajax({
				url: royalStorageData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'process_booking',
					unit_id: unitId,
					unit_type: unitType,
					start_date: startDate,
					end_date: endDate,
					payment_method: paymentMethod,
					nonce: royalStorageData.nonce
				},
				success: function(response) {
					if (response.success) {
						alert('Booking created successfully!');
						window.location.href = '?page=royal-storage-portal';
					} else {
						alert('Error: ' + response.data.message);
					}
				},
				error: function() {
					alert('An error occurred. Please try again.');
				}
			});
		}
	};

	$(document).ready(function() {
		RoyalStorageBooking.init();
	});

})(jQuery);

