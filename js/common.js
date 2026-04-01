/**
 * OVACS Common JavaScript Functions
 * Shared functionality across all OVACS pages
 */

// Modal management functions
const OVACSModal = {
    /**
     * Show modal by ID
     */
    show: function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    },

    /**
     * Hide modal by ID
     */
    hide: function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            this.resetForm(modalId + 'Form');
        }
    },

    /**
     * Reset form by ID
     */
    resetForm: function(formId) {
        const form = document.getElementById(formId);
        if (form) {
            form.reset();
        }
    },

    /**
     * Setup modal close on outside click
     */
    setupCloseOnOutsideClick: function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('click', function(event) {
                if (event.target === modal) {
                    OVACSModal.hide(modalId);
                }
            });
        }
    },

    /**
     * Initialize all modals on page
     */
    initializeAll: function() {
        // Find all modals and setup outside click closing
        const modals = document.querySelectorAll('[id$="Modal"]');
        modals.forEach(modal => {
            this.setupCloseOnOutsideClick(modal.id);
        });

        // Hide all modals on page load
        modals.forEach(modal => {
            modal.style.display = 'none';
        });

        // Reset body overflow in case it was stuck
        document.body.style.overflow = 'auto';
    }
};

// Form utilities
const OVACSForm = {
    /**
     * Auto-uppercase input value
     */
    autoUppercase: function(inputElement) {
        if (inputElement && inputElement.value) {
            inputElement.value = inputElement.value.toUpperCase();
        }
    },

    /**
     * Format input as uppercase on keyup
     */
    setupAutoUppercase: function(selector) {
        const elements = document.querySelectorAll(selector);
        elements.forEach(element => {
            element.addEventListener('keyup', function() {
                this.value = this.value.toUpperCase();
            });
        });
    },

    /**
     * Validate required fields
     */
    validateRequired: function(formId) {
        const form = document.getElementById(formId);
        if (!form) return true;

        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.style.borderColor = '#dc2626';
                isValid = false;
            } else {
                field.style.borderColor = '#d1d5db';
            }
        });

        return isValid;
    }
};

// Event delegation utilities
const OVACSEvents = {
    /**
     * Setup event delegation for buttons with data attributes
     */
    setupButtonDelegation: function(buttonSelector, callback) {
        document.addEventListener('click', function(event) {
            if (event.target.matches(buttonSelector)) {
                event.preventDefault();
                
                // Extract data attributes
                const data = {};
                Array.from(event.target.attributes).forEach(attr => {
                    if (attr.name.startsWith('data-')) {
                        const key = attr.name.replace('data-', '').replace(/-([a-z])/g, function(g) {
                            return g[1].toUpperCase();
                        });
                        data[key] = attr.value;
                    }
                });

                callback(data, event.target);
            }
        });
    }
};

// Utility functions
const OVACSUtils = {
    /**
     * Show confirmation dialog
     */
    confirm: function(message, callback) {
        if (window.confirm(message)) {
            callback();
        }
    },

    /**
     * Format date for display
     */
    formatDate: function(dateString, format = 'yyyy-mm-dd') {
        if (!dateString) return 'N/A';
        
        const date = new Date(dateString);
        if (format === 'yyyy-mm-dd') {
            return date.toISOString().split('T')[0];
        } else if (format === 'datetime') {
            return date.toLocaleString();
        }
        return date.toDateString();
    },

    /**
     * Debounce function calls
     */
    debounce: function(func, delay) {
        let timeoutId;
        return function (...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
    },

    /**
     * Show loading state
     */
    showLoading: function(element, text = 'Loading...') {
        if (element) {
            element.dataset.originalText = element.textContent;
            element.textContent = text;
            element.disabled = true;
        }
    },

    /**
     * Hide loading state
     */
    hideLoading: function(element) {
        if (element && element.dataset.originalText) {
            element.textContent = element.dataset.originalText;
            element.disabled = false;
            delete element.dataset.originalText;
        }
    }
};

// Global initialization
document.addEventListener('DOMContentLoaded', function() {
    // Initialize modals
    OVACSModal.initializeAll();

    // Setup auto-uppercase for common fields
    OVACSForm.setupAutoUppercase('[data-uppercase]');

    // Setup keyboard shortcuts
    document.addEventListener('keydown', function(event) {
        // ESC key to close modals
        if (event.key === 'Escape') {
            const visibleModal = document.querySelector('[id$="Modal"][style*="flex"]');
            if (visibleModal) {
                OVACSModal.hide(visibleModal.id);
            }
        }
    });
});

// Expose global functions for backwards compatibility
window.showModal = function(modalId) { OVACSModal.show(modalId); };
window.hideModal = function(modalId) { OVACSModal.hide(modalId); };

// Error handling for external scripts
window.addEventListener('error', function(event) {
    // Suppress external errors that don't affect our application
    if (event.error && event.error.message && 
        (event.error.message.includes('mgt.clearMarks') || 
         event.error.message.includes('mgt is not defined'))) {
        event.preventDefault();
        console.warn('External script error suppressed:', event.error.message);
        return false;
    }
});

// Safe reference to prevent "mgt is not defined" errors
if (typeof mgt === 'undefined') {
    window.mgt = {
        clearMarks: function() {
            // Stub function to prevent errors
            console.warn('mgt.clearMarks called but mgt library not available');
        }
    };
}