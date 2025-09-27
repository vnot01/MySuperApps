@extends('components.admin-layout')

@section('title', 'Image Upload & Process - MyRVM Platform')
@section('description', 'Upload and process images using AI engines')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/admin/forms/image-upload.css') }}">
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="fas fa-upload me-2"></i>Image Upload & Process
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
                            <i class="fas fa-upload me-2"></i>Image Upload & Process
                        </h1>
                        <p class="page-subtitle text-muted mb-0">Upload images and process them using AI engines</p>
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

    <!-- Image Upload Form -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Upload Image</h6>
                </div>
                <div class="card-body">
                    <div class="upload-area" id="uploadArea">
                        <div class="upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <h5>Drag & Drop Image Here</h5>
                        <p class="text-muted">or click to browse</p>
                        <input type="file" id="fileInput" accept="image/*" style="display: none;">
                    </div>
                    
                    <div class="text-center mt-3">
                        <img id="imagePreview" class="image-preview" style="display: none;" alt="Preview">
                    </div>
                    
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-primary btn-modern" id="processButton" disabled>
                            <i class="fas fa-cogs me-2"></i>Process Image
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Processing Engines</h6>
                </div>
                <div class="card-body">
                    <div class="row" id="enginesContainer">
                        <!-- Engines will be loaded dynamically -->
                    </div>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Processing Results</h6>
                </div>
                <div class="card-body">
                    <div id="processingResults" class="processing-results">
                        <p class="text-muted text-center">No results yet</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-js')
    <script src="{{ asset('js/admin/forms/image-upload.js') }}"></script>
@endsection
