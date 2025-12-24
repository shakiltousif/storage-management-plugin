/**
 * Royal Storage Utilities - Global UI Helpers
 */
window.RoyalStorageUtils = (function($) {
    'use strict';

    // Private state
    let $overlay = null;
    let $toastContainer = null;

    /**
     * Initialize UI components
     */
    function init() {
        createLoadingOverlay();
        createToastContainer();
    }

    /**
     * Create loading overlay in DOM
     */
    function createLoadingOverlay() {
        if ($('.rs-loading-overlay').length) return;
        
        $overlay = $('<div class="rs-loading-overlay"><div class="rs-spinner"></div><p class="rs-loading-text"></p></div>');
        $('body').append($overlay);
    }

    /**
     * Create toast container in DOM
     */
    function createToastContainer() {
        if ($('.rs-toast-container').length) return;
        
        $toastContainer = $('<div class="rs-toast-container"></div>');
        $('body').append($toastContainer);
    }

    /**
     * Modal helper
     */
    function openModal(options) {
        const defaults = {
            title: 'Message',
            content: '',
            footer: '',
            size: 'md',
            onOpen: null,
            onClose: null
        };
        const settings = $.extend({}, defaults, options);
        
        $('.royal-storage-modal').remove(); // Clean up

        const $modal = $(`
            <div class="royal-storage-modal">
                <div class="royal-storage-modal-content rs-modal-${settings.size}">
                    <div class="royal-storage-modal-header">
                        <h3 class="royal-storage-modal-title">${settings.title}</h3>
                        <span class="royal-storage-modal-close">&times;</span>
                    </div>
                    <div class="royal-storage-modal-body">
                        ${settings.content}
                    </div>
                    <div class="royal-storage-modal-footer">
                        ${settings.footer || '<button class="royal-storage-btn modal-close-btn">Close</button>'}
                    </div>
                </div>
            </div>
        `);

        $('body').append($modal);
        setTimeout(() => $modal.addClass('show'), 10);

        $modal.find('.royal-storage-modal-close, .modal-close-btn').on('click', () => {
            $modal.removeClass('show');
            setTimeout(() => {
                $modal.remove();
                if (settings.onClose) settings.onClose();
            }, 300);
        });

        if (settings.onOpen) settings.onOpen($modal);
        return $modal;
    }

    return {
        init: init,
        showLoading: function(text = 'Please wait...') {
            if (!$overlay) createLoadingOverlay();
            $('.rs-loading-text').text(text);
            $('.rs-loading-overlay').addClass('active');
        },
        hideLoading: function() {
            $('.rs-loading-overlay').removeClass('active');
        },
        showToast: function(message, type = 'success') {
            if (!$toastContainer) createToastContainer();
            const $toast = $(`<div class="rs-toast ${type}">${message}</div>`);
            $('.rs-toast-container').append($toast);
            setTimeout(() => {
                $toast.fadeOut(400, function() { $(this).remove(); });
            }, 4000);
        },
        openModal: openModal,
        ajax: function(options) {
            const self = this;
            const defaults = {
                type: 'POST',
                dataType: 'json',
                beforeSend: function() { self.showLoading(); },
                complete: function() { self.hideLoading(); }
            };

            const settings = $.extend({}, defaults, options);
            
            // Override success to handle WP response format
            const originalSuccess = settings.success;
            settings.success = function(response) {
                if (response.success) {
                    if (originalSuccess) originalSuccess(response);
                } else {
                    self.showToast(response.data.message || 'An error occurred', 'error');
                    if (settings.onError) settings.onError(response);
                }
            };

            // Global error handler for network/server errors
            settings.error = function(xhr, status, error) {
                console.error('RS AJAX Error:', error);
                self.showToast('Network error. Please try again.', 'error');
                if (options.error) options.error(xhr, status, error);
            };

            return $.ajax(settings);
        }
    };
})(jQuery);

// Initialize on load
jQuery(document).ready(function() {
    window.RoyalStorageUtils.init();
});

