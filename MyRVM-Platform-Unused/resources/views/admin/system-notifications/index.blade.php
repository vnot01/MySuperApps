@extends('components.admin-layout')

@section('title', 'System Notifications Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">System Notifications</h1>
            <p class="text-muted">Manage and broadcast system-wide notifications to users and tenants</p>
        </div>
        <div>
            <a href="{{ route('admin.system-notifications.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Create Notification
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Notifications
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-notifications">
                                {{ $statistics['total'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bell fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Notifications
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="active-notifications">
                                {{ $statistics['active'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-broadcast-tower fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Recipients Reached
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="recipients-reached">
                                {{ $statistics['recipients_reached'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                This Month
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="monthly-notifications">
                                {{ $statistics['monthly'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form id="filter-form" class="row g-3">
                <div class="col-md-3">
                    <label for="filter-type" class="form-label">Type</label>
                    <select class="form-select" id="filter-type" name="type">
                        <option value="">All Types</option>
                        <option value="info">Info</option>
                        <option value="warning">Warning</option>
                        <option value="error">Error</option>
                        <option value="success">Success</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter-target" class="form-label">Target</label>
                    <select class="form-select" id="filter-target" name="target_type">
                        <option value="">All Targets</option>
                        <option value="all">All Users</option>
                        <option value="tenants">Tenants Only</option>
                        <option value="users">Users Only</option>
                        <option value="specific">Specific Users</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter-priority" class="form-label">Priority</label>
                    <select class="form-select" id="filter-priority" name="priority">
                        <option value="">All Priorities</option>
                        <option value="low">Low</option>
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter-date" class="form-label">Date Range</label>
                    <select class="form-select" id="filter-date" name="date_range">
                        <option value="">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Notifications Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">System Notifications</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="notifications-table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Target</th>
                            <th>Priority</th>
                            <th>Recipients</th>
                            <th>Created</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notifications as $notification)
                        <tr>
                            <td>{{ $notification->id }}</td>
                            <td>
                                <div class="fw-bold">{{ $notification->title }}</div>
                                <small class="text-muted">{{ Str::limit($notification->message, 50) }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $notification->type === 'error' ? 'danger' : ($notification->type === 'warning' ? 'warning' : ($notification->type === 'success' ? 'success' : 'info')) }}">
                                    {{ ucfirst($notification->type) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ ucfirst(str_replace('_', ' ', $notification->data['target_type'] ?? 'unknown')) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $notification->data['priority'] === 'urgent' ? 'danger' : ($notification->data['priority'] === 'high' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($notification->data['priority'] ?? 'normal') }}
                                </span>
                            </td>
                            <td>
                                <span class="text-muted">
                                    {{ $notification->data['recipients_count'] ?? 0 }} users
                                </span>
                            </td>
                            <td>
                                <small>{{ $notification->created_at->format('M d, Y H:i') }}</small>
                            </td>
                            <td>
                                @if($notification->data['is_active'] ?? true)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.system-notifications.show', $notification) }}" 
                                       class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="deleteNotification({{ $notification->id }})" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-bell-slash fa-3x mb-3"></i>
                                <p>No system notifications found.</p>
                                <a href="{{ route('admin.system-notifications.create') }}" class="btn btn-primary">
                                    Create First Notification
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($notifications->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $notifications->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this system notification? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let deleteNotificationId = null;

function deleteNotification(id) {
    deleteNotificationId = id;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDelete').addEventListener('click', function() {
    if (deleteNotificationId) {
        fetch(`/admin/system-notifications/${deleteNotificationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting notification: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting notification');
        });
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
        modal.hide();
    }
});

// Filter functionality
document.getElementById('filter-form').addEventListener('change', function() {
    const formData = new FormData(this);
    const params = new URLSearchParams(formData);
    window.location.search = params.toString();
});

// Auto-refresh statistics every 30 seconds
setInterval(function() {
    fetch('/admin/system-notifications/statistics/overview')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('total-notifications').textContent = data.statistics.total || 0;
                document.getElementById('active-notifications').textContent = data.statistics.active || 0;
                document.getElementById('recipients-reached').textContent = data.statistics.recipients_reached || 0;
                document.getElementById('monthly-notifications').textContent = data.statistics.monthly || 0;
            }
        })
        .catch(error => console.error('Error updating statistics:', error));
}, 30000);
</script>
@endpush

@push('styles')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
</style>
@endpush