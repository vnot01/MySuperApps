// Loading Animation System

class LoadingManager {
    constructor() {
        this.activeLoaders = new Set();
        this.createGlobalOverlay();
    }

    createGlobalOverlay() {
        // Create global loading overlay if it doesn't exist
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
        const overlay = document.getElementById('loadingOverlay');
        if (!overlay) return;

        // Update loading text if provided
        const loadingText = overlay.querySelector('.loading-text');
        if (loadingText && text) {
            loadingText.textContent = text;
        }

        // Show overlay
        overlay.style.display = 'flex';
        overlay.offsetHeight; // Trigger reflow
        overlay.style.opacity = '1';
        overlay.style.visibility = 'visible';
        
        this.activeLoaders.add(id);
    }

    hide(id = 'default') {
        const overlay = document.getElementById('loadingOverlay');
        if (!overlay) return;

        this.activeLoaders.delete(id);

        // Only hide if no other loaders are active
        if (this.activeLoaders.size === 0) {
            overlay.style.opacity = '0';
            overlay.style.visibility = 'hidden';
            
            setTimeout(() => {
                if (overlay.style.opacity === '0') {
                    overlay.style.display = 'none';
                }
            }, 300);
        }
    }

    hideAll() {
        const overlay = document.getElementById('loadingOverlay');
        if (!overlay) return;

        this.activeLoaders.clear();
        overlay.style.opacity = '0';
        overlay.style.visibility = 'hidden';
        
        setTimeout(() => {
            if (overlay.style.opacity === '0') {
                overlay.style.display = 'none';
            }
        }, 300);
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
