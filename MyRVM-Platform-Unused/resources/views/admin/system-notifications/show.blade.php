@extends('components.admin-layout')

@section('title', 'System Notification Details')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Notification Details</h1>
            <p class="text-muted">View system notification information and delivery statistics</p>
        </div>
        <div>
            <a href="{{ route('admin.system-notifications.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to List
            </a>
            <button type="button" class="btn btn-danger" onclick="deleteNotification({{ $notification->id }})">
                <i class="fas fa-trash me-2"></i>Delete
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Notification Details -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Notification Information</h6>
                    <div>
                        @if($notification->data['is_active'] ?? true)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                        <span class="badge bg-{{ $notification->data['priority'] === 'urgent' ? 'danger' : ($notification->data['priority'] === 'high' ? 'warning' : 'secondary') }}">
                            {{ ucfirst($notification->data['priority'] ?? 'normal') }} Priority
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Notification Preview -->
                    <div class="alert alert-{{ $notification->type === 'error' ? 'danger' : ($notification->type === 'warning' ? 'warning' : ($notification->type === 'success' ? 'success' : 'info')) }} mb-4">
                        <div class="d-flex align-items-start">
                            <div class="me-3">
                                <i class="fas {{ $notification->type === 'error' ? 'fa-times-circle' : ($notification->type === 'warning' ? 'fa-exclamation-triangle' : ($notification->type === 'success' ? 'fa-check-circle' : 'fa-info-circle')) }} fa-lg"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="alert-heading mb-1">{{ $notification->title }}</h6>
                                <p class="mb-2">{!! nl2br(e($notification->message)) !!}</p>
                                @if(!empty($notification->data['action_url']) && !empty($notification->data['action_text']))
                                <div>
                                    <a href="{{ $notification->data['action_url'] }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                        {{ $notification->data['action_text'] }}
                                        <i class="fas fa-external-link-alt ms-1"></i>
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Details Table -->
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td class="fw-bold" style="width: 200px;">Notification ID:</td>
                                    <td>{{ $notification->id }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Type:</td>
                                    <td>
                                        <span class="badge bg-{{ $notification->type === 'error' ? 'danger' : ($notification->type === 'warning' ? 'warning' : ($notification->type === 'success' ? 'success' : 'info')) }}">
                                            {{ ucfirst($notification->type) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Priority:</td>
                                    <td>
                                        <span class="badge bg-{{ $notification->data['priority'] === 'urgent' ? 'danger' : ($notification->data['priority'] === 'high' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($notification->data['priority'] ?? 'normal') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Target Audience:</td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ ucfirst(str_replace('_', ' ', $notification->data['target_type'] ?? 'all')) }}
                                        </span>
                                        @if(($notification->data['target_type'] ?? '') === 'specific')
                                            <small class="text-muted d-block mt-1">
                                                {{ count($notification->data['specific_users'] ?? []) }} specific users selected
                                            </small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Created:</td>
                                    <td>{{ $notification->created_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                                @if(!empty($notification->data['scheduled_at']))
                                <tr>
                                    <td class="fw-bold">Scheduled For:</td>
                                    <td>{{ \Carbon\Carbon::parse($notification->data['scheduled_at'])->format('M d, Y H:i:s') }}</td>
                                </tr>
                                @endif
                                @if(!empty($notification->data['expires_at']))
                                <tr>
                                    <td class="fw-bold">Expires At:</td>
                                    <td>{{ \Carbon\Carbon::parse($notification->data['expires_at'])->format('M d, Y H:i:s') }}</td>
                                </tr>
                                @endif
                                @if(!empty($notification->data['action_url']))
                                <tr>
                                    <td class="fw-bold">Action URL:</td>
                                    <td>
                                        <a href="{{ $notification->data['action_url'] }}" target="_blank" class="text-decoration-none">
                                            {{ $notification->data['action_url'] }}
                                            <i class="fas fa-external-link-alt ms-1"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Raw Data -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Raw Notification Data</h6>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3 rounded"><code>{{ json_encode($notification->data, JSON_PRETTY_PRINT) }}</code></pre>
                </div>
            </div>
        </div>

        <!-- Statistics Sidebar -->
        <div class="col-lg-4">
            <!-- Delivery Statistics -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Delivery Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border-end">
                                <h4 class="text-primary mb-0" id="total-recipients">{{ $statistics['total_recipients'] ?? 0 }}</h4>
                                <small class="text-muted">Total Recipients</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <h4 class="text-success mb-0" id="delivered-count">{{ $statistics['delivered'] ?? 0 }}</h4>
                            <small class="text-muted">Delivered</small>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border-end">
                                <h4 class="text-info mb-0" id="read-count">{{ $statistics['read'] ?? 0 }}</h4>
                                <small class="text-muted">Read</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <h4 class="text-warning mb-0" id="pending-count">{{ $statistics['pending'] ?? 0 }}</h4>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>

                    <!-- Delivery Rate Progress -->
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Delivery Rate</span>
                            <span class="text-muted small" id="delivery-rate">
                                {{ $statistics['total_recipients'] > 0 ? round(($statistics['delivered'] / $statistics['total_recipients']) * 100, 1) : 0 }}%
                            </span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ $statistics['total_recipients'] > 0 ? round(($statistics['delivered'] / $statistics['total_recipients']) * 100, 1) : 0 }}%"
                                 id="delivery-progress"></div>
                        </div>
                    </div>

                    <!-- Read Rate Progress -->
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Read Rate</span>
                            <span class="text-muted small" id="read-rate">
                                {{ $statistics['delivered'] > 0 ? round(($statistics['read'] / $statistics['delivered']) * 100, 1) : 0 }}%
                            </span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-info" role="progressbar" 
                                 style="width: {{ $statistics['delivered'] > 0 ? round(($statistics['read'] / $statistics['delivered']) * 100, 1) : 0 }}%"
                                 id="read-progress"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($notification->data['is_active'] ?? true)
                            <button type="button" class="btn btn-warning btn-sm" onclick="toggleNotificationStatus({{ $notification->id }}, false)">
                                <i class="fas fa-pause me-2"></i>Deactivate
                            </button>
                        @else
                            <button type="button" class="btn btn-success btn-sm" onclick="toggleNotificationStatus({{ $notification->id }}, true)">
                                <i class="fas fa-play me-2"></i>Activate
                            </button>
                        @endif
                        
                        <button type="button" class="btn btn-info btn-sm" onclick="refreshStatistics()">
                            <i class="fas fa-sync-alt me-2"></i>Refresh Stats
                        </button>
                        
                        <button type="button" class="btn btn-secondary btn-sm" onclick="duplicateNotification({{ $notification->id }})">
                            <i class="fas fa-copy me-2"></i>Duplicate
                        </button>
                        
                        <hr>
                        
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteNotification({{ $notification->id }})">
                            <i class="fas fa-trash me-2"></i>Delete Notification
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                </div>
                <div class="card-body">
                    <div class="timeline" id="activity-timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Notification Created</h6>
                                <p class="timeline-text">{{ $notification->created_at->format('M d, Y H:i:s') }}</p>
                            </div>
                        </div>
                        <!-- Additional activity items will be loaded via AJAX -->
                    </div>
                </div>
            </div>
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
                Are you sure you want to delete this system notification? This action cannot be undone and will remove all associated delivery records.
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

function toggleNotificationStatus(id, isActive) {
    fetch(`/admin/system-notifications/${id}/toggle-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ is_active: isActive })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error updating notification status: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating notification status');
    });
}

function refreshStatistics() {
    fetch(`/admin/system-notifications/{{ $notification->id }}/statistics`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateStatistics(data.statistics);
            }
        })
        .catch(error => console.error('Error refreshing statistics:', error));
}

function updateStatistics(stats) {
    document.getElementById('total-recipients').textContent = stats.total_recipients || 0;
    document.getElementById('delivered-count').textContent = stats.delivered || 0;
    document.getElementById('read-count').textContent = stats.read || 0;
    document.getElementById('pending-count').textContent = stats.pending || 0;
    
    const deliveryRate = stats.total_recipients > 0 ? Math.round((stats.delivered / stats.total_recipients) * 100 * 10) / 10 : 0;
    const readRate = stats.delivered > 0 ? Math.round((stats.read / stats.delivered) * 100 * 10) / 10 : 0;
    
    document.getElementById('delivery-rate').textContent = deliveryRate + '%';
    document.getElementById('read-rate').textContent = readRate + '%';
    document.getElementById('delivery-progress').style.width = deliveryRate + '%';
    document.getElementById('read-progress').style.width = readRate + '%';
}

function duplicateNotification(id) {
    if (confirm('Create a copy of this notification?')) {
        fetch(`/admin/system-notifications/${id}/duplicate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = `/admin/system-notifications/${data.notification_id}`;
            } else {
                alert('Error duplicating notification: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error duplicating notification');
        });
    }
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
                window.location.href = '/admin/system-notifications';
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

// Auto-refresh statistics every 30 seconds
setInterval(refreshStatistics, 30000);
</script>
@endpush

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -31px;
    top: 15px;
    width: 2px;
    height: calc(100% + 5px);
    background-color: #e3e6f0;
}

.timeline-title {
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.timeline-text {
    font-size: 0.75rem;
    color: #6c757d;
    margin-bottom: 0;
}
</style>
@endpush