@extends('components.admin-layout')

@section('title', 'Create System Notification')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Create System Notification</h1>
            <p class="text-muted">Broadcast important messages to users and tenants</p>
        </div>
        <div>
            <a href="{{ route('admin.system-notifications.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to List
            </a>
        </div>
    </div>

    <!-- Create Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Notification Details</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.system-notifications.store') }}" method="POST" id="notification-form">
                        @csrf
                        
                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title') }}" 
                                   placeholder="Enter notification title" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Message -->
                        <div class="mb-3">
                            <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('message') is-invalid @enderror" 
                                      id="message" name="message" rows="4" 
                                      placeholder="Enter notification message" required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">You can use basic HTML tags for formatting.</div>
                        </div>

                        <!-- Type and Priority Row -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('type') is-invalid @enderror" 
                                        id="type" name="type" required>
                                    <option value="">Select notification type</option>
                                    <option value="info" {{ old('type') === 'info' ? 'selected' : '' }}>
                                        <i class="fas fa-info-circle"></i> Info
                                    </option>
                                    <option value="success" {{ old('type') === 'success' ? 'selected' : '' }}>
                                        <i class="fas fa-check-circle"></i> Success
                                    </option>
                                    <option value="warning" {{ old('type') === 'warning' ? 'selected' : '' }}>
                                        <i class="fas fa-exclamation-triangle"></i> Warning
                                    </option>
                                    <option value="error" {{ old('type') === 'error' ? 'selected' : '' }}>
                                        <i class="fas fa-times-circle"></i> Error
                                    </option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                <select class="form-select @error('priority') is-invalid @enderror" 
                                        id="priority" name="priority" required>
                                    <option value="">Select priority level</option>
                                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="normal" {{ old('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Target Audience -->
                        <div class="mb-3">
                            <label for="target_type" class="form-label">Target Audience <span class="text-danger">*</span></label>
                            <select class="form-select @error('target_type') is-invalid @enderror" 
                                    id="target_type" name="target_type" required>
                                <option value="">Select target audience</option>
                                <option value="all" {{ old('target_type') === 'all' ? 'selected' : '' }}>
                                    All Users (Tenants + Regular Users)
                                </option>
                                <option value="tenants" {{ old('target_type') === 'tenants' ? 'selected' : '' }}>
                                    Tenants Only
                                </option>
                                <option value="users" {{ old('target_type') === 'users' ? 'selected' : '' }}>
                                    Regular Users Only
                                </option>
                                <option value="specific" {{ old('target_type') === 'specific' ? 'selected' : '' }}>
                                    Specific Users
                                </option>
                            </select>
                            @error('target_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Specific Users (shown when target_type is 'specific') -->
                        <div class="mb-3" id="specific-users-section" style="display: none;">
                            <label for="specific_users" class="form-label">Select Specific Users</label>
                            <select class="form-select" id="specific_users" name="specific_users[]" multiple>
                                <!-- Users will be loaded via AJAX -->
                            </select>
                            <div class="form-text">Hold Ctrl/Cmd to select multiple users.</div>
                        </div>

                        <!-- Action URL (Optional) -->
                        <div class="mb-3">
                            <label for="action_url" class="form-label">Action URL (Optional)</label>
                            <input type="url" class="form-control @error('action_url') is-invalid @enderror" 
                                   id="action_url" name="action_url" value="{{ old('action_url') }}" 
                                   placeholder="https://example.com/action">
                            @error('action_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">URL to redirect users when they click on the notification.</div>
                        </div>

                        <!-- Action Text (Optional) -->
                        <div class="mb-3">
                            <label for="action_text" class="form-label">Action Button Text (Optional)</label>
                            <input type="text" class="form-control @error('action_text') is-invalid @enderror" 
                                   id="action_text" name="action_text" value="{{ old('action_text') }}" 
                                   placeholder="Learn More">
                            @error('action_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Schedule Options -->
                        <div class="mb-3">
                            <label class="form-label">Delivery Options</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="delivery_type" 
                                       id="immediate" value="immediate" 
                                       {{ old('delivery_type', 'immediate') === 'immediate' ? 'checked' : '' }}>
                                <label class="form-check-label" for="immediate">
                                    Send Immediately
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="delivery_type" 
                                       id="scheduled" value="scheduled" 
                                       {{ old('delivery_type') === 'scheduled' ? 'checked' : '' }}>
                                <label class="form-check-label" for="scheduled">
                                    Schedule for Later
                                </label>
                            </div>
                        </div>

                        <!-- Scheduled Time (shown when delivery_type is 'scheduled') -->
                        <div class="mb-3" id="scheduled-time-section" style="display: none;">
                            <label for="scheduled_at" class="form-label">Schedule Date & Time</label>
                            <input type="datetime-local" class="form-control @error('scheduled_at') is-invalid @enderror" 
                                   id="scheduled_at" name="scheduled_at" value="{{ old('scheduled_at') }}">
                            @error('scheduled_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Expiration -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="has_expiration" 
                                       name="has_expiration" value="1" 
                                       {{ old('has_expiration') ? 'checked' : '' }}>
                                <label class="form-check-label" for="has_expiration">
                                    Set Expiration Date
                                </label>
                            </div>
                        </div>

                        <!-- Expiration Date (shown when has_expiration is checked) -->
                        <div class="mb-3" id="expiration-section" style="display: none;">
                            <label for="expires_at" class="form-label">Expiration Date & Time</label>
                            <input type="datetime-local" class="form-control @error('expires_at') is-invalid @enderror" 
                                   id="expires_at" name="expires_at" value="{{ old('expires_at') }}">
                            @error('expires_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.system-notifications.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                <i class="fas fa-paper-plane me-2"></i>Create Notification
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Preview Panel -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Preview</h6>
                </div>
                <div class="card-body">
                    <div id="notification-preview" class="alert alert-info">
                        <div class="d-flex align-items-start">
                            <div class="me-3">
                                <i class="fas fa-info-circle fa-lg" id="preview-icon"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="alert-heading mb-1" id="preview-title">Notification Title</h6>
                                <p class="mb-2" id="preview-message">Your notification message will appear here...</p>
                                <div id="preview-action" style="display: none;">
                                    <button class="btn btn-sm btn-outline-primary" id="preview-action-btn">
                                        Action Button
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <h6 class="text-muted">Notification Details:</h6>
                        <ul class="list-unstyled small">
                            <li><strong>Type:</strong> <span id="preview-type">-</span></li>
                            <li><strong>Priority:</strong> <span id="preview-priority">-</span></li>
                            <li><strong>Target:</strong> <span id="preview-target">-</span></li>
                            <li><strong>Delivery:</strong> <span id="preview-delivery">-</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide specific users section
    document.getElementById('target_type').addEventListener('change', function() {
        const specificSection = document.getElementById('specific-users-section');
        if (this.value === 'specific') {
            specificSection.style.display = 'block';
            loadUsers();
        } else {
            specificSection.style.display = 'none';
        }
        updatePreview();
    });

    // Show/hide scheduled time section
    document.querySelectorAll('input[name="delivery_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const scheduledSection = document.getElementById('scheduled-time-section');
            if (this.value === 'scheduled') {
                scheduledSection.style.display = 'block';
            } else {
                scheduledSection.style.display = 'none';
            }
            updatePreview();
        });
    });

    // Show/hide expiration section
    document.getElementById('has_expiration').addEventListener('change', function() {
        const expirationSection = document.getElementById('expiration-section');
        if (this.checked) {
            expirationSection.style.display = 'block';
        } else {
            expirationSection.style.display = 'none';
        }
    });

    // Update preview on form changes
    document.getElementById('notification-form').addEventListener('input', updatePreview);
    document.getElementById('notification-form').addEventListener('change', updatePreview);

    // Load users for specific targeting
    function loadUsers() {
        fetch('/admin/system-notifications/users')
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('specific_users');
                select.innerHTML = '';
                data.users.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = `${user.name} (${user.email})`;
                    select.appendChild(option);
                });
            })
            .catch(error => console.error('Error loading users:', error));
    }

    // Update preview
    function updatePreview() {
        const title = document.getElementById('title').value || 'Notification Title';
        const message = document.getElementById('message').value || 'Your notification message will appear here...';
        const type = document.getElementById('type').value || 'info';
        const priority = document.getElementById('priority').value || 'normal';
        const targetType = document.getElementById('target_type').value || 'all';
        const deliveryType = document.querySelector('input[name="delivery_type"]:checked')?.value || 'immediate';
        const actionText = document.getElementById('action_text').value;
        const actionUrl = document.getElementById('action_url').value;

        // Update preview content
        document.getElementById('preview-title').textContent = title;
        document.getElementById('preview-message').textContent = message;
        document.getElementById('preview-type').textContent = type.charAt(0).toUpperCase() + type.slice(1);
        document.getElementById('preview-priority').textContent = priority.charAt(0).toUpperCase() + priority.slice(1);
        document.getElementById('preview-target').textContent = getTargetText(targetType);
        document.getElementById('preview-delivery').textContent = deliveryType.charAt(0).toUpperCase() + deliveryType.slice(1);

        // Update preview styling
        const preview = document.getElementById('notification-preview');
        const icon = document.getElementById('preview-icon');
        
        preview.className = `alert alert-${getAlertClass(type)}`;
        icon.className = `fas ${getIconClass(type)} fa-lg`;

        // Show/hide action button
        const actionDiv = document.getElementById('preview-action');
        const actionBtn = document.getElementById('preview-action-btn');
        if (actionText && actionUrl) {
            actionBtn.textContent = actionText;
            actionDiv.style.display = 'block';
        } else {
            actionDiv.style.display = 'none';
        }
    }

    function getAlertClass(type) {
        const classes = {
            'info': 'info',
            'success': 'success',
            'warning': 'warning',
            'error': 'danger'
        };
        return classes[type] || 'info';
    }

    function getIconClass(type) {
        const icons = {
            'info': 'fa-info-circle',
            'success': 'fa-check-circle',
            'warning': 'fa-exclamation-triangle',
            'error': 'fa-times-circle'
        };
        return icons[type] || 'fa-info-circle';
    }

    function getTargetText(targetType) {
        const targets = {
            'all': 'All Users',
            'tenants': 'Tenants Only',
            'users': 'Regular Users Only',
            'specific': 'Specific Users'
        };
        return targets[targetType] || 'All Users';
    }

    // Initialize preview
    updatePreview();
});
</script>
@endpush