@extends('components.admin-layout')

@section('title', 'Maintenance Mode - ' . $rvm->name)
@section('description', 'RVM Maintenance Mode - Advanced Monitoring & Control')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/animations.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/remote-access.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/remote-gui-client.css') }}">
    <style>
        .maintenance-mode-header {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(255, 107, 53, 0.3);
        }
        
        .maintenance-mode-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .maintenance-mode-header .subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .maintenance-status-badge {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 600;
            display: inline-block;
            margin-top: 1rem;
        }
        
        .metrics-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .metrics-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        
        .metrics-card .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            border: none;
            padding: 1.5rem;
        }
        
        .metrics-card .card-body {
            padding: 2rem;
        }
        
        .metric-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .metric-item:last-child {
            border-bottom: none;
        }
        
        .metric-label {
            font-weight: 600;
            color: #333;
        }
        
        .metric-value {
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        .terminal-container {
            background: #1a1a1a;
            border-radius: 15px;
            padding: 1.5rem;
            color: #00ff00;
            font-family: 'Courier New', monospace;
            min-height: 400px;
            max-height: 500px;
            overflow-y: auto;
        }
        
        .terminal-header {
            background: #333;
            color: #fff;
            padding: 0.5rem 1rem;
            border-radius: 10px 10px 0 0;
            margin: -1.5rem -1.5rem 1rem -1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .terminal-input {
            background: transparent;
            border: none;
            color: #00ff00;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            width: 100%;
            outline: none;
        }
        
        .command-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin: 0.25rem;
        }
        
        .command-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .ota-card {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            border-radius: 15px;
            border: none;
        }
        
        .ota-card .card-header {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 15px 15px 0 0;
        }
        
        .update-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .exit-maintenance-btn {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
            border: none;
            color: white;
            padding: 1rem 2rem;
            border-radius: 25px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1000;
            box-shadow: 0 10px 30px rgba(255, 65, 108, 0.4);
        }
        
        .exit-maintenance-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(255, 65, 108, 0.6);
            color: white;
        }
        
        .real-time-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            background: #00ff00;
            border-radius: 50%;
            animation: pulse 2s infinite;
            margin-right: 0.5rem;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 0.75rem;
            color: #667eea;
        }
    </style>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ url('/admin/dashboard') }}">
            <i class="fas fa-home me-2"></i>Dashboard
        </a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ url('/admin/rvm') }}">
            <i class="fas fa-recycle me-2"></i>RVM Management
        </a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ url('/admin/rvm/' . $rvm->id) }}">
            <i class="fas fa-cog me-2"></i>{{ $rvm->name }}
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        <i class="fas fa-wrench me-2"></i>Maintenance Mode
    </li>
@endsection

@section('content')
    <!-- Maintenance Mode Header -->
    <div class="maintenance-mode-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1>
                    <i class="fas fa-tools me-3"></i>
                    Maintenance Mode
                </h1>
                <p class="subtitle mb-0">
                    Advanced monitoring and control for <strong>{{ $rvm->name }}</strong>
                </p>
                <div class="maintenance-status-badge">
                    <i class="fas fa-circle me-2"></i>
                    <span class="real-time-indicator"></span>
                    MAINTENANCE MODE ACTIVE
                </div>
            </div>
            <div class="col-md-4 text-end">
                <div class="text-white">
                    <h5>RVM ID: {{ $rvm->id }}</h5>
                    <p class="mb-0">Location: {{ $rvm->location ?? 'Not Set' }}</p>
                    <p class="mb-0">IP: {{ $rvm->ip_address ?? 'Not Set' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Real-time Metrics Section -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="section-title">
                <i class="fas fa-chart-line"></i>
                Real-time System Metrics
            </h2>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <!-- System Metrics Card -->
        <div class="col-md-6">
            <div class="card metrics-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-microchip me-2"></i>
                        System Performance
                        <span class="badge bg-warning ms-2" id="system-simulation-badge" style="display: none;">
                            <i class="fas fa-exclamation-triangle me-1"></i>SIMULATED DATA
                        </span>
                        <span class="badge bg-success ms-2" id="system-real-badge" style="display: none;">
                            <i class="fas fa-check-circle me-1"></i>REAL DATA
                        </span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="metric-item">
                        <span class="metric-label">CPU Usage</span>
                        <span class="metric-value text-primary" id="cpu-usage">
                            {{ $latestSystemMetrics ? $latestSystemMetrics->cpu_usage : 'N/A' }}%
                        </span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Memory Usage</span>
                        <span class="metric-value text-info" id="memory-usage">
                            {{ $latestSystemMetrics ? $latestSystemMetrics->memory_usage : 'N/A' }}%
                        </span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">GPU Usage</span>
                        <span class="metric-value text-warning" id="gpu-usage">
                            {{ $latestSystemMetrics ? $latestSystemMetrics->gpu_usage : 'N/A' }}%
                        </span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Disk Usage</span>
                        <span class="metric-value text-danger" id="disk-usage">
                            {{ $latestSystemMetrics ? $latestSystemMetrics->disk_usage : 'N/A' }}%
                        </span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Temperature</span>
                        <span class="metric-value text-secondary" id="temperature">
                            {{ $latestSystemMetrics ? $latestSystemMetrics->temperature : 'N/A' }}°C
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Application Metrics Card -->
        <div class="col-md-6">
            <div class="card metrics-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        Application Status
                        <span class="badge bg-warning ms-2" id="app-simulation-badge" style="display: none;">
                            <i class="fas fa-exclamation-triangle me-1"></i>SIMULATED DATA
                        </span>
                        <span class="badge bg-success ms-2" id="app-real-badge" style="display: none;">
                            <i class="fas fa-check-circle me-1"></i>REAL DATA
                        </span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="metric-item">
                        <span class="metric-label">Software Version</span>
                        <span class="metric-value text-success" id="software-version">
                            {{ $latestApplicationMetrics->software_version ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">AI Model Version</span>
                        <span class="metric-value text-info" id="ai-model-version">
                            {{ $latestApplicationMetrics ? $latestApplicationMetrics->ai_model_version : 'N/A' }}
                        </span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Uptime</span>
                        <span class="metric-value text-primary" id="uptime">
                            {{ $latestApplicationMetrics && $latestApplicationMetrics->uptime_seconds ? gmdate('H:i:s', $latestApplicationMetrics->uptime_seconds) : 'N/A' }}
                        </span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Deposits Today</span>
                        <span class="metric-value text-warning" id="deposits-today">
                            {{ $latestApplicationMetrics ? $latestApplicationMetrics->deposit_count_since_restart : 'N/A' }}
                        </span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Error Count</span>
                        <span class="metric-value text-danger" id="error-count">
                            {{ $latestApplicationMetrics ? $latestApplicationMetrics->error_count : 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Remote Commands Section -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="section-title">
                <i class="fas fa-terminal"></i>
                Remote Commands Terminal
            </h2>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <!-- Command Buttons -->
        <div class="col-md-4">
            <div class="card metrics-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-play-circle me-2"></i>
                        Quick Commands
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="command-button" onclick="executeCommand('reboot_system')">
                            <i class="fas fa-power-off me-2"></i>Reboot System
                        </button>
                        <button class="command-button" onclick="executeCommand('restart_app')">
                            <i class="fas fa-redo me-2"></i>Restart App
                        </button>
                        <button class="command-button" onclick="executeCommand('open_door')">
                            <i class="fas fa-door-open me-2"></i>Open Door
                        </button>
                        <button class="command-button" onclick="executeCommand('close_door')">
                            <i class="fas fa-door-closed me-2"></i>Close Door
                        </button>
                        <button class="command-button" onclick="executeCommand('run_motor_test')">
                            <i class="fas fa-cog me-2"></i>Motor Test
                        </button>
                        <button class="command-button" onclick="executeCommand('check_system_health')">
                            <i class="fas fa-heartbeat me-2"></i>System Health
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Terminal Output -->
        <div class="col-md-8">
            <div class="card metrics-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-terminal me-2"></i>
                        Command Terminal
                        <span class="badge bg-warning ms-2" id="terminal-simulation-badge">
                            <i class="fas fa-exclamation-triangle me-1"></i>SIMULATED COMMANDS
                        </span>
                        <span class="badge bg-success ms-2" id="terminal-real-badge" style="display: none;">
                            <i class="fas fa-check-circle me-1"></i>REAL COMMANDS
                        </span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="terminal-container" id="terminal-output">
                        <div class="terminal-header">
                            <span>RVM Terminal - {{ $rvm->name }}</span>
                            <span class="real-time-indicator"></span>
                            <div class="alert alert-warning alert-sm mt-2 mb-0" id="terminal-notification" style="display: none;">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>RVM-Jetson Not Reachable!</strong> Commands are being simulated. 
                                <button class="btn btn-sm btn-outline-warning ms-2" onclick="testRvmConnection()">
                                    <i class="fas fa-wifi me-1"></i>Test Connection
                                </button>
                            </div>
                        </div>
                        <div id="terminal-content">
                            <div class="terminal-line">
                                <span class="text-success">admin@rvm-{{ $rvm->id }}:~$</span> 
                                <span class="text-white">Welcome to RVM Maintenance Mode</span>
                            </div>
                            <div class="terminal-line">
                                <span class="text-success">admin@rvm-{{ $rvm->id }}:~$</span> 
                                <span class="text-white">System ready for remote commands</span>
                            </div>
                            <div class="terminal-line">
                                <span class="text-success">admin@rvm-{{ $rvm->id }}:~$</span> 
                                <span class="text-white">Type 'help' for available commands</span>
                            </div>
                        </div>
                        <div class="terminal-input-line">
                            <span class="text-success">admin@rvm-{{ $rvm->id }}:~$</span>
                            <input type="text" class="terminal-input" id="terminal-input" placeholder="Enter command...">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- OTA Management Section -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="section-title">
                <i class="fas fa-download"></i>
                OTA Management
            </h2>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <!-- Current Status -->
        <div class="col-md-6">
            <div class="card ota-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Current Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="update-item">
                        <h6>Software Version</h6>
                        <p class="mb-0">{{ $latestSoftwareUpdate ? $latestSoftwareUpdate->current_version : 'Unknown' }}</p>
                    </div>
                    <div class="update-item">
                        <h6>Active AI Model</h6>
                        <p class="mb-0">{{ $activeAiModel ? $activeAiModel->model_name : 'None' }} (v{{ $activeAiModel ? $activeAiModel->model_version : 'N/A' }})</p>
                    </div>
                    <div class="update-item">
                        <h6>Last Update</h6>
                        <p class="mb-0">{{ $latestSoftwareUpdate && $latestSoftwareUpdate->completed_at ? $latestSoftwareUpdate->completed_at->format('Y-m-d H:i:s') : 'Never' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- GitHub Pull Command -->
        <div class="col-md-6">
            <div class="card ota-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fab fa-github me-2"></i>
                        GitHub Update
                    </h5>
                </div>
                <div class="card-body">
                    <div class="update-item">
                        <h6>Pull Latest Changes</h6>
                        <p class="mb-3">Execute git pull to get latest software updates, AI models, and configurations from GitHub repository.</p>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> This will pull the latest changes from the main branch and restart services if needed.
                        </div>
                        <button class="btn btn-light btn-lg w-100" onclick="executeGitPull()">
                            <i class="fab fa-github me-2"></i>Execute Git Pull
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Exit Maintenance Mode Button -->
    <button class="exit-maintenance-btn" onclick="exitMaintenanceMode()">
        <i class="fas fa-sign-out-alt me-2"></i>
        Exit Maintenance Mode
    </button>

    <!-- Data for JavaScript -->
    <script>
        window.maintenanceModeData = {
            rvmId: {{ $rvm->id }},
            rvmName: '{{ $rvm->name }}',
            apiBaseUrl: '{{ url('/api/v2') }}',
            csrfToken: '{{ csrf_token() }}'
        };
    </script>
@endsection

@section('page-js')
    <script src="{{ asset('js/admin/dashboard/enhanced-metrics.js') }}"></script>
    <script src="{{ asset('js/admin/dashboard/remote-commands.js') }}"></script>
    <script src="{{ asset('js/admin/dashboard/ota-management.js') }}"></script>
    
    <script>
    // Maintenance Mode JavaScript
    let metricsRefreshInterval;
    let terminalHistory = [];
    let terminalHistoryIndex = -1;

    // Initialize Maintenance Mode
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Maintenance Mode initialized for RVM:', window.maintenanceModeData.rvmId);
        
        // Start real-time metrics refresh
        startMetricsRefresh();
        
        // Initialize terminal
        initializeTerminal();
        
        // Initialize command managers
        if (typeof EnhancedMetricsManager !== 'undefined') {
            window.enhancedMetricsManager = new EnhancedMetricsManager(window.maintenanceModeData.rvmId);
        }
        
        if (typeof RemoteCommandsManager !== 'undefined') {
            window.remoteCommandsManager = new RemoteCommandsManager(window.maintenanceModeData.rvmId);
        }
        
        if (typeof OTAManagementManager !== 'undefined') {
            window.otaManagementManager = new OTAManagementManager(window.maintenanceModeData.rvmId);
        }
    });

    // Start real-time metrics refresh
    function startMetricsRefresh() {
        // Initial load
        refreshMetrics();
        
        // Set interval for auto-refresh
        metricsRefreshInterval = setInterval(() => {
            refreshMetrics();
        }, 5000); // Refresh every 5 seconds
    }
    
    // Refresh metrics using Enhanced Metrics Controller
    function refreshMetrics() {
        fetch(`/admin/rvm/${window.maintenanceModeData.rvmId}/metrics`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.maintenanceModeData.csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateMetricsDisplay(data.data);
            } else {
                console.error('Failed to fetch metrics:', data.error);
            }
        })
        .catch(error => {
            console.error('Error fetching metrics:', error);
        });
    }
    
    // Update metrics display
    function updateMetricsDisplay(metrics) {
        // Update System Performance metrics
        if (metrics.system) {
            document.getElementById('cpu-usage').textContent = metrics.system.cpu_usage + '%';
            document.getElementById('memory-usage').textContent = metrics.system.memory_usage + '%';
            document.getElementById('disk-usage').textContent = metrics.system.disk_usage + '%';
            document.getElementById('gpu-usage').textContent = metrics.system.gpu_usage + '%';
            document.getElementById('temperature').textContent = metrics.system.temperature + '°C';
            
            // Show simulation indicator for system metrics
            if (metrics.system.simulation) {
                document.getElementById('system-simulation-badge').style.display = 'inline-block';
                document.getElementById('system-real-badge').style.display = 'none';
            } else {
                document.getElementById('system-simulation-badge').style.display = 'none';
                document.getElementById('system-real-badge').style.display = 'inline-block';
            }
        }
        
        // Update Application Status metrics
        if (metrics.application) {
            document.getElementById('software-version').textContent = metrics.application.software_version;
            document.getElementById('ai-model-version').textContent = metrics.application.ai_model_version;
            document.getElementById('uptime').textContent = formatUptime(metrics.application.uptime_seconds);
            document.getElementById('deposits-today').textContent = metrics.application.deposit_count_since_restart;
            document.getElementById('error-count').textContent = metrics.application.error_count;
            
            // Show simulation indicator for application metrics
            if (metrics.application.simulation) {
                document.getElementById('app-simulation-badge').style.display = 'inline-block';
                document.getElementById('app-real-badge').style.display = 'none';
            } else {
                document.getElementById('app-simulation-badge').style.display = 'none';
                document.getElementById('app-real-badge').style.display = 'inline-block';
            }
        }
        
        // Update Network Information
        if (metrics.network) {
            // Update network info display if elements exist
            const networkElements = {
                'local-ip': metrics.network.local_ip,
                'gateway-ip': metrics.network.gateway_ip,
                'dns-servers': Array.isArray(metrics.network.dns_servers) ? metrics.network.dns_servers.join(', ') : 'N/A',
                'network-interface': metrics.network.network_interface,
                'connection-type': metrics.network.connection_type,
                'signal-strength': metrics.network.signal_strength + ' dBm'
            };
            
            Object.entries(networkElements).forEach(([id, value]) => {
                const element = document.getElementById(id);
                if (element) {
                    element.textContent = value;
                }
            });
        }
    }
    
    // Format uptime from seconds to readable format
    function formatUptime(seconds) {
        if (!seconds) return 'N/A';
        
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        
        return `${hours}h ${minutes}m ${secs}s`;
    }
    
    // Show simulation indicator
    function showSimulationIndicator(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            let indicator = element.querySelector('.simulation-indicator');
            if (!indicator) {
                indicator = document.createElement('div');
                indicator.className = 'simulation-indicator';
                indicator.innerHTML = '<small class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Simulated Data</small>';
                element.appendChild(indicator);
            }
            indicator.style.display = 'block';
        }
    }
    
    // Hide simulation indicator
    function hideSimulationIndicator(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            const indicator = element.querySelector('.simulation-indicator');
            if (indicator) {
                indicator.style.display = 'none';
            }
        }
    }


    // Format duration
    function formatDuration(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }

    // Initialize terminal
    function initializeTerminal() {
        const terminalInput = document.getElementById('terminal-input');
        
        terminalInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const command = this.value.trim();
                if (command) {
                    executeTerminalCommand(command);
                    this.value = '';
                }
            }
        });
        
        terminalInput.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (terminalHistoryIndex < terminalHistory.length - 1) {
                    terminalHistoryIndex++;
                    this.value = terminalHistory[terminalHistory.length - 1 - terminalHistoryIndex];
                }
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (terminalHistoryIndex > 0) {
                    terminalHistoryIndex--;
                    this.value = terminalHistory[terminalHistory.length - 1 - terminalHistoryIndex];
                } else {
                    this.value = '';
                    terminalHistoryIndex = -1;
                }
            }
        });
    }

    // Execute terminal command
    function executeTerminalCommand(command) {
        addTerminalLine(`admin@rvm-${window.maintenanceModeData.rvmId}:~$ ${command}`);
        
        // Add to history
        terminalHistory.push(command);
        terminalHistoryIndex = -1;
        
        // Process command
        if (command === 'help') {
            addTerminalLine('Available commands:');
            addTerminalLine('  reboot_system - Reboot the RVM');
            addTerminalLine('  restart_app - Restart the application');
            addTerminalLine('  open_door - Open the collection door');
            addTerminalLine('  close_door - Close the collection door');
            addTerminalLine('  run_motor_test - Test motor functionality');
            addTerminalLine('  git_pull - Pull latest changes from GitHub');
            addTerminalLine('  update_ai_model - Update AI model from GitHub');
            addTerminalLine('  check_system_health - Check system health status');
            addTerminalLine('  status - Show system status');
            addTerminalLine('  status <command_id> - Show command execution status');
            addTerminalLine('  history - Show recent command history');
            addTerminalLine('  clear - Clear terminal');
        } else if (command === 'clear') {
            clearTerminal();
        } else if (command === 'status') {
            addTerminalLine('System Status:');
            addTerminalLine('  CPU: ' + document.getElementById('cpu-usage').textContent);
            addTerminalLine('  Memory: ' + document.getElementById('memory-usage').textContent);
            addTerminalLine('  Temperature: ' + document.getElementById('temperature').textContent);
        } else if (command === 'history') {
            showCommandHistory();
        } else if (command.startsWith('status ')) {
            const commandId = command.split(' ')[1];
            if (commandId) {
                getCommandStatus(commandId);
            } else {
                addTerminalLine('Usage: status <command_id>');
            }
        } else if (command === 'git_pull') {
            executeGitPull();
        } else if (['reboot_system', 'restart_app', 'open_door', 'close_door', 'run_motor_test', 'update_ai_model', 'check_system_health'].includes(command)) {
            executeCommand(command);
        } else {
            addTerminalLine(`Command not found: ${command}. Type 'help' for available commands.`);
        }
    }

    // Add line to terminal
    function addTerminalLine(text) {
        const terminalContent = document.getElementById('terminal-content');
        const line = document.createElement('div');
        line.className = 'terminal-line';
        line.innerHTML = `<span class="text-white">${text}</span>`;
        terminalContent.appendChild(line);
        terminalContent.scrollTop = terminalContent.scrollHeight;
    }

    // Clear terminal
    function clearTerminal() {
        document.getElementById('terminal-content').innerHTML = '';
    }

    // Execute command
    function executeCommand(commandType) {
        addTerminalLine(`Executing command: ${commandType}...`);
        
        fetch(`/admin/rvm/${window.maintenanceModeData.rvmId}/execute-command`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.maintenanceModeData.csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                command_type: 'system',
                command_name: commandType,
                command_payload: {}
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const simulationText = data.data && data.data.simulation ? ' (SIMULATED)' : '';
                addTerminalLine(`✅ Command executed successfully: ${data.message}${simulationText}`);
                
                // Show command ID for status tracking
                if (data.command_id) {
                    addTerminalLine(`   Command ID: ${data.command_id} (use 'status ${data.command_id}' to check status)`);
                }
                
                // Show additional data if available
                if (data.data && Object.keys(data.data).length > 0) {
                    Object.entries(data.data).forEach(([key, value]) => {
                        if (key !== 'simulation') {
                            addTerminalLine(`   ${key}: ${value}`);
                        }
                    });
                }
            } else {
                addTerminalLine(`❌ Command failed: ${data.message}`);
            }
        })
        .catch(error => {
            addTerminalLine(`❌ Network error: ${error.message}`);
        });
    }

    // Show Command History
    function showCommandHistory() {
        addTerminalLine('📋 Recent Command History:');
        
        fetch(`/admin/rvm/${window.maintenanceModeData.rvmId}/recent-commands`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.maintenanceModeData.csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data && data.data.length > 0) {
                data.data.forEach((cmd, index) => {
                    const statusIcon = cmd.status === 'completed' ? '✅' : cmd.status === 'failed' ? '❌' : '⏳';
                    const timestamp = new Date(cmd.executed_at).toLocaleString();
                    addTerminalLine(`  ${index + 1}. ${statusIcon} [${cmd.id}] ${cmd.command_name} - ${cmd.status} (${timestamp})`);
                });
            } else {
                addTerminalLine('  No command history found.');
            }
        })
        .catch(error => {
            addTerminalLine(`❌ Error fetching command history: ${error.message}`);
        });
    }

    // Get Command Status
    function getCommandStatus(commandId) {
        addTerminalLine(`🔍 Checking status for command ID: ${commandId}`);
        
        fetch(`/admin/rvm/${window.maintenanceModeData.rvmId}/command/${commandId}/status`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.maintenanceModeData.csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const cmd = data.data;
                const statusIcon = cmd.status === 'completed' ? '✅' : cmd.status === 'failed' ? '❌' : '⏳';
                addTerminalLine(`Command Status: ${statusIcon} ${cmd.command_name}`);
                addTerminalLine(`  Status: ${cmd.status}`);
                addTerminalLine(`  Executed: ${new Date(cmd.executed_at).toLocaleString()}`);
                if (cmd.completed_at) {
                    addTerminalLine(`  Completed: ${new Date(cmd.completed_at).toLocaleString()}`);
                }
                if (cmd.result) {
                    const result = typeof cmd.result === 'string' ? JSON.parse(cmd.result) : cmd.result;
                    if (result.message) {
                        addTerminalLine(`  Result: ${result.message}`);
                    }
                }
                if (cmd.error_message) {
                    addTerminalLine(`  Error: ${cmd.error_message}`);
                }
            } else {
                addTerminalLine(`❌ Command not found or error: ${data.message || 'Unknown error'}`);
            }
        })
        .catch(error => {
            addTerminalLine(`❌ Error fetching command status: ${error.message}`);
        });
    }

    // Execute Git Pull
    function executeGitPull() {
        if (confirm('Are you sure you want to execute git pull? This will update the RVM software from GitHub.')) {
            addTerminalLine('🔄 Executing git pull from GitHub...');
            
            fetch(`/admin/rvm/${window.maintenanceModeData.rvmId}/execute-command`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.maintenanceModeData.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    command_type: 'system',
                    command_name: 'git_pull',
                    command_payload: {
                        repository: 'main',
                        branch: 'main',
                        restart_services: true
                    }
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const simulationText = data.data && data.data.simulation ? ' (SIMULATED)' : '';
                    addTerminalLine(`✅ Git pull executed successfully: ${data.message}${simulationText}`);
                    
                    // Show command ID for status tracking
                    if (data.command_id) {
                        addTerminalLine(`   Command ID: ${data.command_id} (use 'status ${data.command_id}' to check status)`);
                    }
                    
                    addTerminalLine('🔄 Services will be restarted automatically...');
                } else {
                    addTerminalLine(`❌ Git pull failed: ${data.message}`);
                }
            })
            .catch(error => {
                addTerminalLine(`❌ Network error: ${error.message}`);
            });
        }
    }

    // Test RVM Connection
    function testRvmConnection() {
        addTerminalLine('🔍 Testing RVM-Jetson connection...');
        
        fetch(`/admin/rvm/${window.maintenanceModeData.rvmId}/test-connection`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.maintenanceModeData.csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.connected) {
                addTerminalLine('✅ RVM-Jetson connection successful!');
                addTerminalLine('🔄 Switching to real commands...');
                
                // Hide simulation badge and show real badge
                document.getElementById('terminal-simulation-badge').style.display = 'none';
                document.getElementById('terminal-real-badge').style.display = 'inline-block';
                
                // Hide notification
                document.getElementById('terminal-notification').style.display = 'none';
                
                // Refresh metrics to get real data
                refreshMetrics();
            } else {
                addTerminalLine('❌ RVM-Jetson connection failed: ' + (data.message || 'Unknown error'));
                addTerminalLine('💡 Please check:');
                addTerminalLine('   - RVM-Jetson is powered on');
                addTerminalLine('   - Network connection is stable');
                addTerminalLine('   - API service is running on port 8000');
            }
        })
        .catch(error => {
            addTerminalLine('❌ Connection test failed: ' + error.message);
        });
    }

    // Exit maintenance mode
    function exitMaintenanceMode() {
        if (confirm('Are you sure you want to exit Maintenance Mode? This will restore normal RVM operations.')) {
            // Stop metrics refresh
            if (metricsRefreshInterval) {
                clearInterval(metricsRefreshInterval);
            }
            
            // Redirect back to RVM management
            window.location.href = '/admin/rvm';
        }
    }

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (metricsRefreshInterval) {
            clearInterval(metricsRefreshInterval);
        }
    });
    </script>
@endsection
