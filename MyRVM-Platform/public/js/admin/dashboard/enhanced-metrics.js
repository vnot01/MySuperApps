class EnhancedMetricsManager {
    constructor(rvmId) {
        this.rvmId = rvmId;
        this.refreshInterval = null;
        this.isActive = false;
    }
    
    start() {
        this.isActive = true;
        this.loadMetrics();
        this.refreshInterval = setInterval(() => {
            if (this.isActive) {
                this.loadMetrics();
            }
        }, 30000); // Refresh every 30 seconds
    }
    
    stop() {
        this.isActive = false;
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
    }
    
    async loadMetrics() {
        try {
            const response = await fetch(`/admin/rvm/${this.rvmId}/enhanced-metrics`);
            const data = await response.json();
            
            if (data.success) {
                this.updateMetricsDisplay(data.data);
            }
        } catch (error) {
            console.error('Failed to load enhanced metrics:', error);
        }
    }
    
    updateMetricsDisplay(metrics) {
        // Update system metrics
        if (metrics.system_metrics) {
            this.updateSystemMetrics(metrics.system_metrics);
        }
        
        // Update application metrics
        if (metrics.application_metrics) {
            this.updateApplicationMetrics(metrics.application_metrics);
        }
        
        // Update network information
        if (metrics.network_information) {
            this.updateNetworkInformation(metrics.network_information);
        }
    }
    
    updateSystemMetrics(systemMetrics) {
        // CPU Usage
        const cpuElement = document.getElementById('cpu-usage');
        if (cpuElement) {
            cpuElement.textContent = `${systemMetrics.cpu_usage || 0}%`;
            cpuElement.className = this.getUsageClass(systemMetrics.cpu_usage);
        }
        
        // Memory Usage
        const memoryElement = document.getElementById('memory-usage');
        if (memoryElement) {
            memoryElement.textContent = `${systemMetrics.memory_usage || 0}%`;
            memoryElement.className = this.getUsageClass(systemMetrics.memory_usage);
        }
        
        // GPU Usage
        const gpuElement = document.getElementById('gpu-usage');
        if (gpuElement) {
            gpuElement.textContent = `${systemMetrics.gpu_usage || 0}%`;
            gpuElement.className = this.getUsageClass(systemMetrics.gpu_usage);
        }
        
        // Temperature
        const tempElement = document.getElementById('temperature');
        if (tempElement) {
            tempElement.textContent = `${systemMetrics.temperature || 0}°C`;
            tempElement.className = this.getTemperatureClass(systemMetrics.temperature);
        }
        
        // GPU Temperature
        const gpuTempElement = document.getElementById('gpu-temperature');
        if (gpuTempElement) {
            gpuTempElement.textContent = `${systemMetrics.gpu_temperature || 0}°C`;
            gpuTempElement.className = this.getTemperatureClass(systemMetrics.gpu_temperature);
        }
        
        // Disk Usage
        const diskElement = document.getElementById('disk-usage');
        if (diskElement) {
            diskElement.textContent = `${systemMetrics.disk_usage || 0}%`;
            diskElement.className = this.getUsageClass(systemMetrics.disk_usage);
        }
        
        // Load Average
        const loadElement = document.getElementById('load-average');
        if (loadElement) {
            loadElement.textContent = `${systemMetrics.load_average || 0}`;
            loadElement.className = this.getLoadClass(systemMetrics.load_average);
        }
        
        // Process Count
        const processElement = document.getElementById('process-count');
        if (processElement) {
            processElement.textContent = `${systemMetrics.process_count || 0}`;
        }
        
        // Network Speeds
        const uploadElement = document.getElementById('network-upload-speed');
        if (uploadElement) {
            uploadElement.textContent = this.formatBytes(systemMetrics.network_upload_speed || 0) + '/s';
        }
        
        const downloadElement = document.getElementById('network-download-speed');
        if (downloadElement) {
            downloadElement.textContent = this.formatBytes(systemMetrics.network_download_speed || 0) + '/s';
        }
        
        // Disk Speeds
        const diskReadElement = document.getElementById('disk-read-speed');
        if (diskReadElement) {
            diskReadElement.textContent = this.formatBytes(systemMetrics.disk_read_speed || 0) + '/s';
        }
        
        const diskWriteElement = document.getElementById('disk-write-speed');
        if (diskWriteElement) {
            diskWriteElement.textContent = this.formatBytes(systemMetrics.disk_write_speed || 0) + '/s';
        }
    }
    
    updateApplicationMetrics(appMetrics) {
        // Software Version
        const versionElement = document.getElementById('software-version');
        if (versionElement) {
            versionElement.textContent = appMetrics.software_version || 'Unknown';
        }
        
        // AI Model Version
        const modelElement = document.getElementById('ai-model-version');
        if (modelElement) {
            const aiModelVersion = appMetrics.ai_model_version === 'not_found' ? 'Model tidak ditemukan' : (appMetrics.ai_model_version || 'Unknown');
            modelElement.textContent = aiModelVersion;
        }
        
        // AI Model Path
        const modelPathElement = document.getElementById('ai-model-path');
        if (modelPathElement) {
            modelPathElement.textContent = appMetrics.ai_model_path || 'Unknown';
        }
        
        // Uptime
        const uptimeElement = document.getElementById('uptime');
        if (uptimeElement) {
            uptimeElement.textContent = this.formatUptime(appMetrics.uptime_seconds || 0);
        }
        
        // Deposit Count
        const depositElement = document.getElementById('deposit-count');
        if (depositElement) {
            depositElement.textContent = appMetrics.deposit_count_since_restart || 0;
        }
        
        // Error Count
        const errorElement = document.getElementById('error-count');
        if (errorElement) {
            errorElement.textContent = appMetrics.error_count || 0;
            errorElement.className = appMetrics.error_count > 0 ? 'text-danger' : 'text-success';
        }
        
        // Warning Count
        const warningElement = document.getElementById('warning-count');
        if (warningElement) {
            warningElement.textContent = appMetrics.warning_count || 0;
            warningElement.className = appMetrics.warning_count > 0 ? 'text-warning' : 'text-success';
        }
        
        // Last Deposit Time
        const lastDepositElement = document.getElementById('last-deposit-time');
        if (lastDepositElement) {
            lastDepositElement.textContent = appMetrics.last_deposit_time ? 
                new Date(appMetrics.last_deposit_time).toLocaleString() : 'Never';
        }
    }
    
    updateNetworkInformation(networkInfo) {
        // Local IP
        const localIpElement = document.getElementById('local-ip');
        if (localIpElement) {
            localIpElement.textContent = networkInfo.local_ip || 'Unknown';
        }
        
        // Virtual IP
        const virtualIpElement = document.getElementById('virtual-ip');
        if (virtualIpElement) {
            virtualIpElement.textContent = networkInfo.virtual_ip || 'Unknown';
        }
        
        // Gateway IP
        const gatewayElement = document.getElementById('gateway-ip');
        if (gatewayElement) {
            gatewayElement.textContent = networkInfo.gateway_ip || 'Unknown';
        }
        
        // Network Interface
        const interfaceElement = document.getElementById('network-interface');
        if (interfaceElement) {
            interfaceElement.textContent = networkInfo.network_interface || 'Unknown';
        }
        
        // Connection Type
        const connectionElement = document.getElementById('connection-type');
        if (connectionElement) {
            connectionElement.textContent = networkInfo.connection_type || 'Unknown';
        }
        
        // Signal Strength
        const signalElement = document.getElementById('signal-strength');
        if (signalElement) {
            signalElement.textContent = networkInfo.signal_strength ? `${networkInfo.signal_strength}%` : 'Unknown';
            signalElement.className = this.getSignalClass(networkInfo.signal_strength);
        }
        
        // DNS Servers
        const dnsElement = document.getElementById('dns-servers');
        if (dnsElement) {
            try {
                const dnsServers = networkInfo.dns_servers ? JSON.parse(networkInfo.dns_servers) : [];
                dnsElement.textContent = dnsServers.join(', ') || 'Unknown';
            } catch (e) {
                dnsElement.textContent = networkInfo.dns_servers || 'Unknown';
            }
        }
        
        // Last Network Check
        const lastCheckElement = document.getElementById('last-network-check');
        if (lastCheckElement) {
            lastCheckElement.textContent = networkInfo.last_network_check ? 
                new Date(networkInfo.last_network_check).toLocaleString() : 'Never';
        }
    }
    
    getUsageClass(usage) {
        if (usage >= 90) return 'text-danger';
        if (usage >= 70) return 'text-warning';
        return 'text-success';
    }
    
    getTemperatureClass(temperature) {
        if (temperature >= 80) return 'text-danger';
        if (temperature >= 60) return 'text-warning';
        return 'text-success';
    }
    
    getLoadClass(load) {
        if (load >= 2.0) return 'text-danger';
        if (load >= 1.0) return 'text-warning';
        return 'text-success';
    }
    
    getSignalClass(signal) {
        if (!signal) return 'text-muted';
        if (signal >= 80) return 'text-success';
        if (signal >= 60) return 'text-warning';
        if (signal >= 40) return 'text-info';
        return 'text-danger';
    }
    
    formatUptime(seconds) {
        const days = Math.floor(seconds / 86400);
        const hours = Math.floor((seconds % 86400) / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        
        if (days > 0) {
            return `${days}d ${hours}h ${minutes}m`;
        } else if (hours > 0) {
            return `${hours}h ${minutes}m`;
        } else {
            return `${minutes}m`;
        }
    }
    
    formatBytes(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
}

// Initialize enhanced metrics manager
let enhancedMetricsManager = null;

function initEnhancedMetrics(rvmId) {
    if (enhancedMetricsManager) {
        enhancedMetricsManager.stop();
    }
    enhancedMetricsManager = new EnhancedMetricsManager(rvmId);
    enhancedMetricsManager.start();
}

function stopEnhancedMetrics() {
    if (enhancedMetricsManager) {
        enhancedMetricsManager.stop();
        enhancedMetricsManager = null;
    }
}
