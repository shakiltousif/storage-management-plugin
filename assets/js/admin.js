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
