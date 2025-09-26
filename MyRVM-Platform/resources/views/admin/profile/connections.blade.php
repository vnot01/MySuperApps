@extends('components.admin-layout')

@section('title', 'Connections - MyRVM Platform')
@section('description', 'Manage your integrations and connected services')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ url('/admin/dashboard') }}">
            <i class="fas fa-home me-2"></i>Dashboard
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        <i class="fas fa-link me-2"></i>Connections
    </li>
@endsection

@section('content')
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-1 fw-bold text-primary">
                        <i class="fas fa-link me-2"></i>Connections
                    </h1>
                    <p class="text-muted mb-0">Manage your integrations and connected services</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add Connection
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Connected Services -->
    <div class="row">
        @foreach($connections as $connection)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md me-3">
                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                        <i class="{{ $connection['icon'] }} fs-4"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $connection['name'] }}</h6>
                                    @if($connection['status'] == 'connected')
                                        <span class="badge bg-success">Connected</span>
                                    @else
                                        <span class="badge bg-secondary">Disconnected</span>
                                    @endif
                                </div>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-text-secondary" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    @if($connection['status'] == 'connected')
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Configure</a></li>
                                        <li><a class="dropdown-item text-warning" href="#"><i class="fas fa-unlink me-2"></i>Disconnect</a></li>
                                    @else
                                        <li><a class="dropdown-item text-primary" href="#"><i class="fas fa-link me-2"></i>Connect</a></li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-info-circle me-2"></i>View Details</a></li>
                                </ul>
                            </div>
                        </div>
                        
                        <p class="text-muted mb-3">{{ $connection['description'] }}</p>
                        
                        @if($connection['status'] == 'connected')
                            <div class="d-flex align-items-center text-success mb-3">
                                <i class="fas fa-check-circle me-2"></i>
                                <small>Connected {{ $connection['connected_at']->diffForHumans() }}</small>
                            </div>
                        @else
                            <div class="d-flex align-items-center text-muted mb-3">
                                <i class="fas fa-times-circle me-2"></i>
                                <small>Not connected</small>
                            </div>
                        @endif
                        
                        <div class="d-flex gap-2 mt-auto">
                            @if($connection['status'] == 'connected')
                                <button class="btn btn-outline-primary btn-sm flex-fill">
                                    <i class="fas fa-cog me-1"></i>Configure
                                </button>
                                <button class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-unlink"></i>
                                </button>
                            @else
                                <button class="btn btn-primary btn-sm flex-fill">
                                    <i class="fas fa-link me-1"></i>Connect
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Available Integrations -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="mb-0">Available Integrations</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="d-flex align-items-center p-3 border rounded">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded-circle bg-label-info">
                                        <i class="fab fa-microsoft fs-5"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">Microsoft Teams</h6>
                                    <small class="text-muted">Team collaboration</small>
                                </div>
                                <button class="btn btn-sm btn-outline-primary">Connect</button>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="d-flex align-items-center p-3 border rounded">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded-circle bg-label-success">
                                        <i class="fab fa-whatsapp fs-5"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">WhatsApp Business</h6>
                                    <small class="text-muted">Customer messaging</small>
                                </div>
                                <button class="btn btn-sm btn-outline-primary">Connect</button>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="d-flex align-items-center p-3 border rounded">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded-circle bg-label-warning">
                                        <i class="fab fa-aws fs-5"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">AWS Services</h6>
                                    <small class="text-muted">Cloud infrastructure</small>
                                </div>
                                <button class="btn btn-sm btn-outline-primary">Connect</button>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="d-flex align-items-center p-3 border rounded">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded-circle bg-label-danger">
                                        <i class="fab fa-telegram fs-5"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">Telegram Bot</h6>
                                    <small class="text-muted">Instant notifications</small>
                                </div>
                                <button class="btn btn-sm btn-outline-primary">Connect</button>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="d-flex align-items-center p-3 border rounded">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded-circle bg-label-dark">
                                        <i class="fab fa-github fs-5"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">GitHub</h6>
                                    <small class="text-muted">Code repository</small>
                                </div>
                                <button class="btn btn-sm btn-outline-primary">Connect</button>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="d-flex align-items-center p-3 border rounded">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                        <i class="fas fa-database fs-5"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">Database Backup</h6>
                                    <small class="text-muted">Automated backups</small>
                                </div>
                                <button class="btn btn-sm btn-outline-primary">Connect</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Connection Statistics -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="avatar avatar-lg mx-auto mb-3">
                        <span class="avatar-initial rounded-circle bg-success">
                            <i class="fas fa-link fs-3"></i>
                        </span>
                    </div>
                    <h4 class="mb-1">{{ $connections->where('status', 'connected')->count() }}</h4>
                    <p class="text-muted mb-0">Active Connections</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="avatar avatar-lg mx-auto mb-3">
                        <span class="avatar-initial rounded-circle bg-warning">
                            <i class="fas fa-clock fs-3"></i>
                        </span>
                    </div>
                    <h4 class="mb-1">{{ $connections->where('status', 'disconnected')->count() }}</h4>
                    <p class="text-muted mb-0">Pending Connections</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="avatar avatar-lg mx-auto mb-3">
                        <span class="avatar-initial rounded-circle bg-info">
                            <i class="fas fa-plus fs-3"></i>
                        </span>
                    </div>
                    <h4 class="mb-1">6</h4>
                    <p class="text-muted mb-0">Available Integrations</p>
                </div>
            </div>
        </div>
    </div>
@endsection