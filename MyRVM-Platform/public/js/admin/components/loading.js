// Loading Animation System

class LoadingManager {
    constructor() {
        this.activeLoaders = new Set();
        this.createGlobalOverlay();
    }

    createGlobalOverlay() {
        // Only create overlay when explicitly needed, not on page load
        // This method will be called when show() is called
    }

    ensureOverlayExists() {
        // Create global loading overlay if it doesn't exist and is needed
        if (!document.getElementById('loadingOverlay')) {
            const overlay = document.createElement('div');
            overlay.id = 'loadingOverlay';
            overlay.className = 'loading-overlay';
            overlay.innerHTML = `
                <div class="text-center">
                    <div class="loading-spinner-large"></div>
                    <div class="loading-text">Loading...</div>
                </div>
            `;
            document.body.appendChild(overlay);
        }
    }

    show(text = 'Loading...', id = 'default') {
        this.ensureOverlayExists();
        const overlay = document.getElementById('loadingOverlay');
        if (!overlay) return;

        // Update loading text if provided
        const loadingText = overlay.querySelector('.loading-text');
        if (loadingText && text) {
            loadingText.textContent = text;
        }

        // Show overlay using class
        overlay.classList.add('show');
        
        this.activeLoaders.add(id);
    }

    hide(id = 'default') {
        const overlay = document.getElementById('loadingOverlay');
        if (!overlay) return;

        this.activeLoaders.delete(id);

        // Only hide if no other loaders are active
        if (this.activeLoaders.size === 0) {
            overlay.classList.remove('show');
        }
    }

    hideAll() {
        const overlay = document.getElementById('loadingOverlay');
        if (!overlay) return;

        this.activeLoaders.clear();
        overlay.classList.remove('show');
    }

    isLoading(id = 'default') {
        return this.activeLoaders.has(id);
    }

    // Create a local loading spinner
    createSpinner(container, size = 'sm') {
        const spinner = document.createElement('div');
        spinner.className = `loading-spinner ${size === 'lg' ? 'loading-spinner-large' : ''}`;
        spinner.style.display = 'inline-block';
        
        if (container) {
            container.appendChild(spinner);
        }
        
        return spinner;
    }

    // Remove a local loading spinner
    removeSpinner(spinner) {
        if (spinner && spinner.parentNode) {
            spinner.remove();
        }
    }
}

// Global loading manager instance
const loadingManager = new LoadingManager();

// Auto-hide loading on page load
document.addEventListener('DOMContentLoaded', function() {
    // Hide any loading overlay that might be showing
    setTimeout(() => {
        loadingManager.hideAll();
    }, 100);
});

// Also hide on window load
window.addEventListener('load', function() {
    loadingManager.hideAll();
});

// Convenience functions
function showLoading(text = 'Loading...', id = 'default') {
    return loadingManager.show(text, id);
}

function hideLoading(id = 'default') {
    return loadingManager.hide(id);
}

function hideAllLoading() {
    return loadingManager.hideAll();
}

function isLoading(id = 'default') {
    return loadingManager.isLoading(id);
}

// Legacy functions for backward compatibility
function showLoadingAnimation() {
    return loadingManager.show();
}

function hideLoadingAnimation() {
    return loadingManager.hide();
}
