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

			var $btn = $(e.target);
			var itemId = $btn.data('id');
			var itemType = $btn.data('type');

			$.ajax({
				url: royalStorageAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'royal_storage_delete_' + itemType,
					id: itemId,
					nonce: royalStorageAdmin.nonce
				},
				success: function(response) {
					if (response.success) {
						location.reload();
					} else {
						alert('Error: ' + response.data.message);
					}
				},
				error: function() {
					alert('An error occurred. Please try again.');
				}
			});
		},

		editItem: function(e) {
			e.preventDefault();

			var $btn = $(e.target);
			var itemId = $btn.data('id');
			var itemType = $btn.data('type');

			// Redirect to edit page
			window.location.href = '?page=royal-storage-' + itemType + '&action=edit&id=' + itemId;
		},

		submitForm: function(e) {
			e.preventDefault();

			var $form = $(e.target);
			var formData = $form.serialize();

			$.ajax({
				url: royalStorageAdmin.ajaxUrl,
				type: 'POST',
				data: formData + '&nonce=' + royalStorageAdmin.nonce,
				success: function(response) {
					if (response.success) {
						alert('Saved successfully!');
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
	};

	$(document).ready(function() {
		RoyalStorageAdmin.init();
	});

})(jQuery);

