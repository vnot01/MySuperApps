@extends('components.admin-layout')

@section('title', 'Engine Configuration - MyRVM Platform')
@section('description', 'Configure and manage AI processing engines')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/admin/forms/engine-config.css') }}">
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="fas fa-cogs me-2"></i>Engine Configuration
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
                            <i class="fas fa-cogs me-2"></i>Engine Configuration
                        </h1>
                        <p class="page-subtitle text-muted mb-0">Configure and manage AI processing engines</p>
                    </div>
                    <div class="page-actions">
                        <button type="button" class="btn btn-primary btn-modern" id="addEngineButton">
                            <i class="fas fa-plus me-2"></i>Add Engine
                        </button>
                        <a href="{{ url('/admin/rvm-dashboard') }}" class="btn btn-outline-primary btn-modern">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Engine Configuration -->
    <div class="row">
        <div class="col-12">
            <div id="enginesContainer">
                <!-- Engines will be loaded dynamically -->
            </div>
        </div>
    </div>
@endsection

@section('page-js')
    <script src="{{ asset('js/admin/forms/engine-config.js') }}"></script>
@endsection
