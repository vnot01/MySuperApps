@extends('components.admin-layout')

@section('title', 'Remote Control - MyRVM Platform')
@section('description', 'Remote control and management of RVM devices')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/admin/forms/remote-control.css') }}">
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="fas fa-desktop me-2"></i>Remote Control
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
                            <i class="fas fa-desktop me-2"></i>Remote Control
                        </h1>
                        <p class="page-subtitle text-muted mb-0">Remote control and management of RVM devices</p>
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

    <!-- Remote Control Panel -->
    <div class="row g-4">
        <div class="col-md-8">
            <div class="remote-control-panel">
                <!-- Connection Status -->
                <div class="control-section">
                    <h6>Connection Status</h6>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <select class="form-select" id="rvmSelect">
                                <option value="">Select RVM...</option>
                            </select>
                        </div>
                        <div>
                            <button type="button" class="btn btn-success me-2" id="connectButton">
                                <i class="fas fa-plug me-1"></i>Connect
                            </button>
                            <button type="button" class="btn btn-danger" id="disconnectButton" disabled>
                                <i class="fas fa-unlink me-1"></i>Disconnect
                            </button>
                        </div>
                    </div>
                    <div id="statusIndicator" class="status-indicator disconnected">
                        <i class="fas fa-circle me-1"></i>Disconnected
                    </div>
                </div>

                <!-- Connection Info -->
                <div class="control-section">
                    <h6>Connection Information</h6>
                    <div id="connectionInfo">
                        <p class="text-muted text-center">Not connected</p>
                    </div>
                </div>

                <!-- Control Actions -->
                <div class="control-section">
                    <h6>Control Actions</h6>
                    <div class="control-grid">
                        <div class="control-button" data-action="restart">
                            <i class="fas fa-redo"></i>
                            <span>Restart System</span>
                        </div>
                        <div class="control-button" data-action="shutdown">
                            <i class="fas fa-power-off"></i>
                            <span>Shutdown</span>
                        </div>
                        <div class="control-button" data-action="maintenance">
                            <i class="fas fa-tools"></i>
                            <span>Maintenance Mode</span>
                        </div>
                        <div class="control-button" data-action="update">
                            <i class="fas fa-sync"></i>
                            <span>Update Software</span>
                        </div>
                        <div class="control-button" data-action="diagnostics">
                            <i class="fas fa-stethoscope"></i>
                            <span>Run Diagnostics</span>
                        </div>
                        <div class="control-button" data-action="logs">
                            <i class="fas fa-file-alt"></i>
                            <span>View Logs</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Activity Log</h6>
                </div>
                <div class="card-body p-0">
                    <div id="logContainer" class="log-container">
                        <div class="log-entry info">
                            <span class="log-timestamp">[00:00:00]</span>
                            <span>Remote control system initialized</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-js')
    <script src="{{ asset('js/admin/forms/remote-control.js') }}"></script>
@endsection
