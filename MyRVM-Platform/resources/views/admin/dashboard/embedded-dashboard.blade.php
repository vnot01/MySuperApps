@extends('components.admin-layout')

@section('title', 'Embedded Dashboard - MyRVM Platform')
@section('description', 'RVM Dashboard Integration')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/animations.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/responsive.css') }}">
    <style>
        .dashboard-container {
            position: relative;
            background: #000;
            border-radius: 8px;
            overflow: hidden;
            min-height: 600px;
        }
        
        .dashboard-iframe {
            width: 100%;
            height: 600px;
            border: none;
            background: #fff;
        }
        
        .dashboard-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #666;
            text-align: center;
        }
        
        .dashboard-error {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #dc3545;
            text-align: center;
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #dc3545;
        }
        
        .device-info {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 10px;
            margin-top: 10px;
        }
        
        .device-info small {
            color: #6c757d;
        }
    </style>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="fas fa-tv me-2"></i>Embedded Dashboard
    </li>
@endsection

@section('content')
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header modern-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="page-title fw-bold mb-2">
                            <i class="fas fa-tv me-2"></i>Embedded Dashboard
                        </h1>
                        <p class="page-subtitle text-muted mb-0">RVM Dashboard Integration</p>
                    </div>
                    <div class="page-actions">
                        <a href="{{ url('/admin/rvm-dashboard') }}" class="btn btn-outline-primary btn-modern">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Embedded Dashboard -->
    <div class="row g-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">RVM Dashboard</h6>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-success" id="refreshDashboard">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                        <button type="button" class="btn btn-sm btn-info" id="openInNewTab">
                            <i class="fas fa-external-link-alt"></i> Open in New Tab
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="dashboard-container">
                        <div id="dashboardLoading" class="dashboard-loading">
                            <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                            <p>Loading dashboard...</p>
                        </div>
                        <div id="dashboardError" class="dashboard-error" style="display: none;">
                            <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                            <h6>Dashboard Unavailable</h6>
                            <p id="errorMessage">Unable to load dashboard. Please check the connection.</p>
                        </div>
                        <iframe id="dashboardIframe" class="dashboard-iframe" style="display: none;" 
                                src="" 
                                title="RVM Dashboard">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Device Selection</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Select RVM Device</label>
                        <select class="form-select" id="rvmSelect">
                            <option value="">Choose RVM...</option>
                            <!-- Options will be populated dynamically -->
                        </select>
                    </div>
                    <div id="deviceInfo" class="device-info" style="display: none;">
                        <h6 class="mb-2">Device Information</h6>
                        <div class="row">
                            <div class="col-6">
                                <small><strong>IP Address:</strong></small><br>
                                <span id="deviceIP">-</span>
                            </div>
                            <div class="col-6">
                                <small><strong>Port:</strong></small><br>
                                <span id="devicePort">-</span>
                            </div>
                        </div>
                        <div class="mt-2">
                            <small><strong>Dashboard URL:</strong></small><br>
                            <span id="dashboardURL" class="text-break">-</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Connection Status</h6>
                </div>
                <div class="card-body">
                    <div id="connectionStatus">
                        <div class="d-flex align-items-center">
                            <div class="spinner-border spinner-border-sm text-warning me-2" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span>Checking connection...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-js')
    <script>
        class EmbeddedDashboardManager {
            constructor() {
                this.currentRvm = null;
                this.initializeElements();
                this.setupEventListeners();
                this.loadRvmDevices();
            }

            initializeElements() {
                this.rvmSelect = document.getElementById('rvmSelect');
                this.dashboardIframe = document.getElementById('dashboardIframe');
                this.dashboardLoading = document.getElementById('dashboardLoading');
                this.dashboardError = document.getElementById('dashboardError');
                this.errorMessage = document.getElementById('errorMessage');
                this.deviceInfo = document.getElementById('deviceInfo');
                this.deviceIP = document.getElementById('deviceIP');
                this.devicePort = document.getElementById('devicePort');
                this.dashboardURL = document.getElementById('dashboardURL');
                this.connectionStatus = document.getElementById('connectionStatus');
                this.refreshButton = document.getElementById('refreshDashboard');
                this.openInNewTabButton = document.getElementById('openInNewTab');
            }

            setupEventListeners() {
                this.rvmSelect?.addEventListener('change', (e) => this.onRvmChange(e.target.value));
                this.refreshButton?.addEventListener('click', () => this.refreshDashboard());
                this.openInNewTabButton?.addEventListener('click', () => this.openInNewTab());
            }

            async loadRvmDevices() {
                try {
                    // Use hardcoded RVM data for now (since API requires authentication)
                    const rvmDevices = [
                        { id: 1, name: 'RVM-Jetson-Orin', ip: '100.117.234.2', port: '5002' },
                        { id: 3, name: 'RVM-Test-Maintenance', ip: '192.168.1.100', port: '8001' }
                    ];
                    
                    // Populate dropdown
                    this.rvmSelect.innerHTML = '<option value="">Choose RVM...</option>';
                    
                    rvmDevices.forEach(rvm => {
                        const option = document.createElement('option');
                        option.value = rvm.id;
                        option.textContent = `${rvm.name} (${rvm.ip}:${rvm.port})`;
                        option.dataset.ip = rvm.ip;
                        option.dataset.port = rvm.port;
                        this.rvmSelect.appendChild(option);
                    });
                    
                } catch (error) {
                    console.error('Error loading RVM devices:', error);
                    this.rvmSelect.innerHTML = '<option value="">Error loading devices</option>';
                }
            }

            onRvmChange(rvmId) {
                if (!rvmId) {
                    this.hideDashboard();
                    return;
                }

                // Get RVM data from dropdown
                const selectedOption = this.rvmSelect.options[this.rvmSelect.selectedIndex];
                const name = selectedOption.textContent.split(' (')[0];
                const ip = selectedOption.dataset.ip;
                const port = selectedOption.dataset.port;
                
                if (ip && port) {
                    this.currentRvm = { id: rvmId, name, ip, port };
                    this.updateDeviceInfo();
                    this.loadDashboard();
                }
            }

            updateDeviceInfo() {
                if (this.currentRvm) {
                    this.deviceIP.textContent = this.currentRvm.ip;
                    this.devicePort.textContent = this.currentRvm.port;
                    this.dashboardURL.textContent = `http://${this.currentRvm.ip}:${this.currentRvm.port}`;
                    this.deviceInfo.style.display = 'block';
                } else {
                    this.deviceInfo.style.display = 'none';
                }
            }

            async loadDashboard() {
                if (!this.currentRvm) return;

                const dashboardUrl = `http://${this.currentRvm.ip}:${this.currentRvm.port}`;
                
                // Show loading
                this.showLoading();
                
                // Test connection first
                try {
                    await this.testConnection(dashboardUrl);
                    
                    // Load iframe
                    this.dashboardIframe.src = dashboardUrl;
                    this.dashboardIframe.onload = () => {
                        this.hideLoading();
                        this.showDashboard();
                        this.updateConnectionStatus('success', 'Connected');
                    };
                    
                    this.dashboardIframe.onerror = () => {
                        this.hideLoading();
                        this.showError('Failed to load dashboard');
                        this.updateConnectionStatus('error', 'Connection failed');
                    };
                    
                } catch (error) {
                    this.hideLoading();
                    this.showError('Unable to connect to dashboard');
                    this.updateConnectionStatus('error', 'Connection failed');
                }
            }

            async testConnection(url) {
                try {
                    // Create a simple image request to test connection
                    return new Promise((resolve) => {
                        const img = new Image();
                        img.onload = () => resolve(true);
                        img.onerror = () => resolve(false);
                        img.src = url + '/favicon.ico?' + Date.now();
                        
                        // Timeout after 3 seconds
                        setTimeout(() => resolve(false), 3000);
                    });
                } catch (error) {
                    return false;
                }
            }

            showLoading() {
                this.dashboardLoading.style.display = 'block';
                this.dashboardError.style.display = 'none';
                this.dashboardIframe.style.display = 'none';
            }

            hideLoading() {
                this.dashboardLoading.style.display = 'none';
            }

            showDashboard() {
                this.dashboardIframe.style.display = 'block';
                this.dashboardError.style.display = 'none';
            }

            showError(message) {
                this.errorMessage.textContent = message;
                this.dashboardError.style.display = 'block';
                this.dashboardIframe.style.display = 'none';
            }

            hideDashboard() {
                this.dashboardIframe.style.display = 'none';
                this.dashboardError.style.display = 'none';
                this.dashboardLoading.style.display = 'none';
                this.deviceInfo.style.display = 'none';
                this.updateConnectionStatus('warning', 'No device selected');
            }

            refreshDashboard() {
                if (this.currentRvm) {
                    this.loadDashboard();
                }
            }

            openInNewTab() {
                if (this.currentRvm) {
                    const dashboardUrl = `http://${this.currentRvm.ip}:${this.currentRvm.port}`;
                    window.open(dashboardUrl, '_blank');
                }
            }

            updateConnectionStatus(type, message) {
                const statusMap = {
                    success: { class: 'text-success', icon: 'fa-check-circle' },
                    error: { class: 'text-danger', icon: 'fa-times-circle' },
                    warning: { class: 'text-warning', icon: 'fa-exclamation-triangle' }
                };
                
                const status = statusMap[type] || statusMap.warning;
                
                this.connectionStatus.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="fas ${status.icon} ${status.class} me-2"></i>
                        <span>${message}</span>
                    </div>
                `;
            }
        }

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            new EmbeddedDashboardManager();
        });
    </script>
@endsection
