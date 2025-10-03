@extends('components.admin-layout')

@section('title', 'Live Camera - MyRVM Platform')
@section('description', 'Live Camera Feed from Jetson Orin')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/admin/forms/live-camera.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/animations.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/responsive.css') }}">
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="fas fa-video me-2"></i>Live Camera
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
                            <i class="fas fa-video me-2"></i>Live Camera - Jetson Orin
                        </h1>
                        <p class="page-subtitle text-muted mb-0">Real-time camera feed from Jetson devices</p>
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

    <!-- Live Camera Form -->
    <div class="row g-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Live Camera Feed</h6>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-success" id="startCamera">
                            <i class="fas fa-play"></i> Start
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" id="stopCamera" disabled>
                            <i class="fas fa-stop"></i> Stop
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="camera-container" style="position: relative; background: #000; border-radius: 8px; overflow: hidden;">
                        <video id="liveCamera" width="100%" height="400" style="display: none;">
                            Your browser does not support the video tag.
                        </video>
                        <div id="cameraPlaceholder" class="d-flex align-items-center justify-content-center" style="height: 400px; color: #666;">
                            <div class="text-center">
                                <i class="fas fa-video fa-3x mb-3"></i>
                                <p>Camera feed will appear here</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Selection Device</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Select Device</label>
                        <select class="form-select" id="jetsonSelect">
                            <option value="">Choose Device...</option>
                            <!-- Options will be populated dynamically -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Processing Mode</label>
                        <select class="form-select" id="processingMode">
                            <option value="yolo">YOLO11 Only</option>
                            <option value="sam2">SAM2 Only</option>
                            <option value="both">YOLO11 + SAM2</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="enableRecording">
                            <label class="form-check-label" for="enableRecording">
                                Enable Recording
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Detection Results</h6>
                </div>
                <div class="card-body">
                    <div id="detectionResults">
                        <p class="text-muted text-center">No detections yet</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-js')
    <script src="{{ asset('js/admin/forms/live-camera.js') }}"></script>
@endsection
