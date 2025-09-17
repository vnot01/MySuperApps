// Live Camera Form Functionality

class LiveCameraManager {
    constructor() {
        this.isStreaming = false;
        this.currentStream = null;
        this.selectedJetson = null;
        this.processingMode = 'yolo';
        this.isRecording = false;
        this.detectionResults = [];
        
        this.initializeElements();
        this.setupEventListeners();
        this.loadJetsonDevices();
    }

    initializeElements() {
        this.videoElement = document.getElementById('liveCamera');
        this.placeholderElement = document.getElementById('cameraPlaceholder');
        this.jetsonSelect = document.getElementById('jetsonSelect');
        this.processingModeSelect = document.getElementById('processingMode');
        this.recordingCheckbox = document.getElementById('enableRecording');
        this.startButton = document.getElementById('startCamera');
        this.stopButton = document.getElementById('stopCamera');
        this.resultsContainer = document.getElementById('detectionResults');
    }

    setupEventListeners() {
        this.startButton?.addEventListener('click', () => this.startCamera());
        this.stopButton?.addEventListener('click', () => this.stopCamera());
        this.jetsonSelect?.addEventListener('change', (e) => this.onJetsonChange(e.target.value));
        this.processingModeSelect?.addEventListener('change', (e) => this.onProcessingModeChange(e.target.value));
    }

    async loadJetsonDevices() {
        try {
            showLoading('Loading Jetson devices...');
            
            // Fetch Jetson devices from API
            const response = await fetch('/api/v2/processing-engines/jetson-edge');
            const engines = await response.json();
            
            // Populate dropdown
            this.jetsonSelect.innerHTML = '<option value="">Choose Jetson...</option>';
            
            engines.forEach(engine => {
                const option = document.createElement('option');
                option.value = engine.id;
                option.textContent = `${engine.name} (${engine.server_address})`;
                this.jetsonSelect.appendChild(option);
            });
            
        } catch (error) {
            console.error('Error loading Jetson devices:', error);
            showError('Failed to load Jetson devices');
        } finally {
            hideLoading();
        }
    }

    onJetsonChange(jetsonId) {
        this.selectedJetson = jetsonId;
        if (this.isStreaming) {
            this.stopCamera();
        }
    }

    onProcessingModeChange(mode) {
        this.processingMode = mode;
    }

    async startCamera() {
        if (!this.selectedJetson) {
            showWarning('Please select a Jetson device first');
            return;
        }

        try {
            showLoading('Starting camera stream...');
            
            // Get camera stream from Jetson device
            const streamUrl = await this.getCameraStreamUrl();
            
            if (streamUrl) {
                this.videoElement.src = streamUrl;
                this.videoElement.style.display = 'block';
                this.placeholderElement.style.display = 'none';
                
                this.isStreaming = true;
                this.updateUI();
                
                // Start processing if enabled
                if (this.processingMode !== 'none') {
                    this.startProcessing();
                }
                
                showSuccess('Camera stream started successfully');
            }
            
        } catch (error) {
            console.error('Error starting camera:', error);
            showError('Failed to start camera stream');
        } finally {
            hideLoading();
        }
    }

    stopCamera() {
        if (this.currentStream) {
            this.currentStream.getTracks().forEach(track => track.stop());
            this.currentStream = null;
        }
        
        this.videoElement.src = '';
        this.videoElement.style.display = 'none';
        this.placeholderElement.style.display = 'flex';
        
        this.isStreaming = false;
        this.isRecording = false;
        this.updateUI();
        
        showInfo('Camera stream stopped');
    }

    async getCameraStreamUrl() {
        // In a real implementation, this would connect to the Jetson device
        // For now, we'll simulate with a placeholder
        return 'data:video/mp4;base64,placeholder';
    }

    startProcessing() {
        // Simulate processing results
        setInterval(() => {
            if (this.isStreaming) {
                this.addDetectionResult();
            }
        }, 3000);
    }

    addDetectionResult() {
        const detections = [
            { label: 'Bottle', confidence: 0.95 },
            { label: 'Can', confidence: 0.87 },
            { label: 'Plastic', confidence: 0.92 },
            { label: 'Glass', confidence: 0.78 }
        ];
        
        const randomDetection = detections[Math.floor(Math.random() * detections.length)];
        
        this.detectionResults.unshift({
            ...randomDetection,
            timestamp: new Date().toLocaleTimeString()
        });
        
        // Keep only last 10 results
        if (this.detectionResults.length > 10) {
            this.detectionResults = this.detectionResults.slice(0, 10);
        }
        
        this.updateDetectionResults();
    }

    updateDetectionResults() {
        if (!this.resultsContainer) return;
        
        if (this.detectionResults.length === 0) {
            this.resultsContainer.innerHTML = '<p class="text-muted text-center">No detections yet</p>';
            return;
        }
        
        this.resultsContainer.innerHTML = this.detectionResults.map(result => `
            <div class="detection-item">
                <div class="detection-label">${result.label}</div>
                <div class="detection-confidence">Confidence: ${(result.confidence * 100).toFixed(1)}%</div>
                <div class="detection-time">${result.timestamp}</div>
            </div>
        `).join('');
    }

    updateUI() {
        if (this.isStreaming) {
            this.startButton.disabled = true;
            this.stopButton.disabled = false;
            this.jetsonSelect.disabled = true;
        } else {
            this.startButton.disabled = false;
            this.stopButton.disabled = true;
            this.jetsonSelect.disabled = false;
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new LiveCameraManager();
});
