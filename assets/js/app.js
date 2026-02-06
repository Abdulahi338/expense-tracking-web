/*
 * Main Application JavaScript
 * Expense Tracking System
 */

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initTogglePassword();
    initMobileSidebar();
    initFormValidation();
    initTooltips();
});

/**
 * Toggle Password Visibility
 */
function initTogglePassword() {
    const toggleButtons = document.querySelectorAll('.toggle-password');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input[type="password"], input[type="text"]');
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });
}

/**
 * Mobile Sidebar Toggle
 */
function initMobileSidebar() {
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });
    }
}

/**
 * Form Validation Enhancement
 */
function initFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Please wait...';
            }
            
            // Re-enable after 10 seconds as fallback
            setTimeout(() => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    // Restore original text
                    const originalText = submitBtn.getAttribute('data-original-text');
                    if (originalText) {
                        submitBtn.innerHTML = originalText;
                    }
                }
            }, 10000);
        });
        
        // Store original button text
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn && !submitBtn.getAttribute('data-original-text')) {
            submitBtn.setAttribute('data-original-text', submitBtn.innerHTML);
        }
    });
}

/**
 * Initialize Bootstrap Tooltips
 */
function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Format Currency
 * @param {number} amount - Amount to format
 * @returns {string} Formatted currency string
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

/**
 * Show Toast Notification
 * @param {string} message - Message to display
 * @param {string} type - 'success', 'error', 'warning', 'info'
 */
function showToast(message, type = 'info') {
    const icons = {
        success: 'bi-check-circle-fill',
        error: 'bi-exclamation-circle-fill',
        warning: 'bi-exclamation-triangle-fill',
        info: 'bi-info-circle-fill'
    };
    
    Swal.fire({
        icon: type,
        title: message,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}

/**
 * Confirm Action
 * @param {string} title - Confirmation title
 * @param {string} text - Confirmation text
 * @param {string} confirmButtonText - Confirm button text
 * @returns {Promise} SweetAlert result
 */
function confirmAction(title, text, confirmButtonText = 'Yes, continue!') {
    return Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: confirmButtonText
    });
}

/**
 * Copy to Clipboard
 * @param {string} text - Text to copy
 * @param {string} message - Success message
 */
async function copyToClipboard(text, message = 'Copied to clipboard!') {
    try {
        await navigator.clipboard.writeText(text);
        showToast(message, 'success');
    } catch (err) {
        showToast('Failed to copy', 'error');
    }
}

/**
 * Debounce Function
 * @param {Function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @returns {Function} Debounced function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Format Date
 * @param {string} dateString - Date string to format
 * @param {string} format - Format type ('short', 'long', 'relative')
 * @returns {string} Formatted date
 */
function formatDate(dateString, format = 'short') {
    const date = new Date(dateString);
    
    switch (format) {
        case 'long':
            return date.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        case 'relative':
            return getRelativeTime(date);
        default:
            return date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
    }
}

/**
 * Get Relative Time String
 * @param {Date} date - Date to compare
 * @returns {string} Relative time string
 */
function getRelativeTime(date) {
    const now = new Date();
    const diff = now - date;
    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    
    if (days > 0) return days === 1 ? 'Yesterday' : `${days} days ago`;
    if (hours > 0) return hours === 1 ? '1 hour ago' : `${hours} hours ago`;
    if (minutes > 0) return minutes === 1 ? '1 minute ago' : `${minutes} minutes ago`;
    return 'Just now';
}

/**
 * Export Data to CSV
 * @param {Array} data - Data to export
 * @param {string} filename - Filename for download
 */
function exportToCSV(data, filename = 'export.csv') {
    if (!data || data.length === 0) {
        showToast('No data to export', 'error');
        return;
    }
    
    const headers = Object.keys(data[0]);
    const csvContent = [
        headers.join(','),
        ...data.map(row => headers.map(header => {
            let cell = row[header] || '';
            // Escape quotes and wrap in quotes if contains comma
            if (typeof cell === 'string' && (cell.includes(',') || cell.includes('"'))) {
                cell = `"${cell.replace(/"/g, '""')}"`;
            }
            return cell;
        }).join(','))
    ].join('\n');
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
    
    showToast('Export completed!', 'success');
}

/**
 * Print Page
 */
function printPage() {
    window.print();
}

/**
 * Scroll to Top
 */
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

