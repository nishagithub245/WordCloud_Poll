// Global utility functions
document.addEventListener('DOMContentLoaded', function() {
  
    initTooltips();
    initCopyButtons();
    initFormValidation();
});

// Initialize tooltips
function initTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(el => {
        el.addEventListener('mouseenter', function(e) {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.dataset.tooltip;
            tooltip.style.position = 'absolute';
            tooltip.style.background = '#333';
            tooltip.style.color = 'white';
            tooltip.style.padding = '8px 12px';
            tooltip.style.borderRadius = '4px';
            tooltip.style.fontSize = '14px';
            tooltip.style.zIndex = '1000';
            tooltip.style.whiteSpace = 'nowrap';
            
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = (rect.left + rect.width/2 - tooltip.offsetWidth/2) + 'px';
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
            
            this._tooltip = tooltip;
        });
        
        el.addEventListener('mouseleave', function() {
            if (this._tooltip) {
                this._tooltip.remove();
            }
        });
    });
}

// Initialize copy buttons
function initCopyButtons() {
    document.querySelectorAll('[data-copy-target]').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.dataset.copyTarget;
            const target = document.getElementById(targetId);
            
            if (target) {
                copyToClipboardElement(target);
                showSuccessMessage('Copied to clipboard!', this);
            }
        });
    });
}

// Initialize form validation
function initFormValidation() {
    const forms = document.querySelectorAll('form[needs-validation]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            this.classList.add('was-validated');
        });
    });
}

// Copy text from element
function copyToClipboardElement(element) {
    if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
        element.select();
        element.setSelectionRange(0, 99999);
    } else {
        const range = document.createRange();
        range.selectNode(element);
        window.getSelection().removeAllRanges();
        window.getSelection().addRange(range);
    }
    
    try {
        document.execCommand('copy');
        return true;
    } catch (err) {
        console.error('Failed to copy:', err);
        return false;
    }
}

// Show success message near element
function showSuccessMessage(message, element) {
    const successMsg = document.createElement('div');
    successMsg.className = 'success-message';
    successMsg.textContent = message;
    successMsg.style.position = 'absolute';
    successMsg.style.zIndex = '1000';
    
    const rect = element.getBoundingClientRect();
    successMsg.style.left = (rect.left + window.scrollX) + 'px';
    successMsg.style.top = (rect.top + window.scrollY - 40) + 'px';
    
    document.body.appendChild(successMsg);
    
    setTimeout(() => {
        successMsg.remove();
    }, 2000);
}

// API helper function
async function apiRequest(endpoint, data = null, method = 'POST') {
    const url = endpoint.startsWith('http') ? endpoint : `api/${endpoint}`;
    
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        }
    };
    
    if (data) {
        if (data instanceof FormData) {
            options.body = data;
            delete options.headers['Content-Type'];
        } else if (method === 'POST') {
            const params = new URLSearchParams();
            for (const key in data) {
                params.append(key, data[key]);
            }
            options.body = params;
        } else {
            url += '?' + new URLSearchParams(data).toString();
        }
    }
    
    try {
        const response = await fetch(url, options);
        return await response.json();
    } catch (error) {
        console.error('API request failed:', error);
        return { success: false, message: 'Network error' };
    }
}

// Debounce function
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

// Throttle function
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Export to global scope
window.WordCloudPoll = {
    apiRequest,
    debounce,
    throttle,
    copyToClipboard: copyToClipboardElement
};

// Show error message
function showError(message) {
    const el = document.getElementById('errorMessage');
    if (el) {
        el.textContent = message;
        el.style.display = 'block';
        setTimeout(() => el.style.display = 'none', 5000);
    }
}

// Show success message
function showSuccess(message) {
    const el = document.getElementById('successMessage');
    if (el) {
        el.textContent = message;
        el.style.display = 'block';
        setTimeout(() => el.style.display = 'none', 3000);
    }
}

