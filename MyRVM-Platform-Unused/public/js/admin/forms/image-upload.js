// Image Upload Form Functionality

class ImageUploadManager {
    constructor() {
        this.selectedFile = null;
        this.selectedEngine = null;
        this.processingResults = [];
        
        this.initializeElements();
        this.setupEventListeners();
        this.loadProcessingEngines();
    }

    initializeElements() {
        this.uploadArea = document.getElementById('uploadArea');
        this.fileInput = document.getElementById('fileInput');
        this.imagePreview = document.getElementById('imagePreview');
        this.uploadButton = document.getElementById('uploadButton');
        this.processButton = document.getElementById('processButton');
        this.enginesContainer = document.getElementById('enginesContainer');
        this.resultsContainer = document.getElementById('processingResults');
    }

    setupEventListeners() {
        // File input change
        this.fileInput?.addEventListener('change', (e) => this.handleFileSelect(e));
        
        // Drag and drop
        this.uploadArea?.addEventListener('dragover', (e) => this.handleDragOver(e));
        this.uploadArea?.addEventListener('dragleave', (e) => this.handleDragLeave(e));
        this.uploadArea?.addEventListener('drop', (e) => this.handleDrop(e));
        
        // Click to upload
        this.uploadArea?.addEventListener('click', () => this.fileInput?.click());
        
        // Process button
        this.processButton?.addEventListener('click', () => this.processImage());
    }

    handleDragOver(e) {
        e.preventDefault();
        this.uploadArea.classList.add('dragover');
    }

    handleDragLeave(e) {
        e.preventDefault();
        this.uploadArea.classList.remove('dragover');
    }

    handleDrop(e) {
        e.preventDefault();
        this.uploadArea.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            this.handleFileSelect({ target: { files } });
        }
    }

    handleFileSelect(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Validate file type
        if (!file.type.startsWith('image/')) {
            showError('Please select a valid image file');
            return;
        }

        // Validate file size (max 10MB)
        if (file.size > 10 * 1024 * 1024) {
            showError('File size must be less than 10MB');
            return;
        }

        this.selectedFile = file;
        this.displayImagePreview(file);
        this.updateUI();
    }

    displayImagePreview(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            this.imagePreview.src = e.target.result;
            this.imagePreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }

    async loadProcessingEngines() {
        try {
            showLoading('Loading processing engines...');
            
            const response = await fetch('/api/v2/processing-engines');
            const engines = await response.json();
            
            this.renderEngines(engines);
            
        } catch (error) {
            console.error('Error loading processing engines:', error);
            showError('Failed to load processing engines');
        } finally {
            hideLoading();
        }
    }

    renderEngines(engines) {
        if (!this.enginesContainer) return;
        
        this.enginesContainer.innerHTML = engines.map(engine => `
            <div class="col-md-6 mb-3">
                <div class="engine-card" data-engine-id="${engine.id}" onclick="imageUploadManager.selectEngine(${engine.id})">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0">${engine.name}</h6>
                        <span class="engine-status ${engine.is_online ? 'online' : 'offline'}">
                            ${engine.is_online ? 'Online' : 'Offline'}
                        </span>
                    </div>
                    <p class="text-muted small mb-2">${engine.server_address}:${engine.port}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-${engine.type === 'nvidia_cuda' ? 'primary' : 'success'}">
                            ${engine.type === 'nvidia_cuda' ? 'CUDA' : 'Jetson'}
                        </span>
                        <small class="text-muted">${engine.gpu_memory_limit || 'N/A'}</small>
                    </div>
                </div>
            </div>
        `).join('');
    }

    selectEngine(engineId) {
        // Remove previous selection
        document.querySelectorAll('.engine-card').forEach(card => {
            card.classList.remove('selected');
        });
        
        // Add selection to clicked card
        const selectedCard = document.querySelector(`[data-engine-id="${engineId}"]`);
        if (selectedCard) {
            selectedCard.classList.add('selected');
            this.selectedEngine = engineId;
            this.updateUI();
        }
    }

    async processImage() {
        if (!this.selectedFile) {
            showWarning('Please select an image first');
            return;
        }

        if (!this.selectedEngine) {
            showWarning('Please select a processing engine');
            return;
        }

        try {
            showLoading('Processing image...');
            
            const formData = new FormData();
            formData.append('image', this.selectedFile);
            formData.append('engine_id', this.selectedEngine);
            
            const response = await fetch('/api/v2/process-image', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const results = await response.json();
            this.displayResults(results);
            showSuccess('Image processed successfully');
            
        } catch (error) {
            console.error('Error processing image:', error);
            showError('Failed to process image');
        } finally {
            hideLoading();
        }
    }

    displayResults(results) {
        if (!this.resultsContainer) return;
        
        this.processingResults = results.detections || [];
        
        if (this.processingResults.length === 0) {
            this.resultsContainer.innerHTML = '<p class="text-muted text-center">No objects detected</p>';
            return;
        }
        
        this.resultsContainer.innerHTML = this.processingResults.map(result => `
            <div class="result-item">
                <div class="result-label">${result.label}</div>
                <div class="result-confidence">Confidence: ${(result.confidence * 100).toFixed(1)}%</div>
                <div class="result-coordinates">
                    Position: (${result.x}, ${result.y}) - Size: ${result.width}×${result.height}
                </div>
            </div>
        `).join('');
    }

    updateUI() {
        const hasFile = this.selectedFile !== null;
        const hasEngine = this.selectedEngine !== null;
        
        this.processButton.disabled = !hasFile || !hasEngine;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.imageUploadManager = new ImageUploadManager();
});
