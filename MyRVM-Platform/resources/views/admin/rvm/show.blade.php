@extends('components.admin-layout')

@section('title', 'RVM Details - ' . $rvm->name)
@section('description', 'Detailed information and management for ' . $rvm->name)

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/animations.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/remote-access.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/rvm-modern.css') }}">
    <link href="https://api.tiles.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css" rel="stylesheet" />
    <script id="search-js" defer src="https://api.mapbox.com/search-js/v1.3.0/web.js">function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        // Use modern clipboard API
        navigator.clipboard.writeText(text).then(() => {
            showAlert('success', 'API Key copied to clipboard!');
        }).catch(() => {
            showAlert('danger', 'Failed to copy API Key');
        });
    } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            showAlert('success', 'API Key copied to clipboard!');
        } catch (err) {
            showAlert('danger', 'Failed to copy API Key');
        }
        textArea.remove();
    }
}

</script>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ url('/admin/dashboard') }}">
            <i class="fas fa-home me-2"></i>Dashboard
        </a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ url('/admin/dashboard') }}">
            <i class="fas fa-home me-2"></i>Dashboard
        </a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('admin.rvm.index') }}">
            <i class="fas fa-recycle me-2"></i>RVM Management
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        <i class="fas fa-microchip me-2"></i>{{ $rvm->name }}
    </li>
@endsection

@section('content')
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-1 fw-bold text-primary">
                        <i class="fas fa-recycle me-2"></i>RVM Details
                    </h1>
                    <p class="text-muted mb-0">Detailed information and management for {{ $rvm->name }}</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="window.history.back()">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </button>
                    <a href="{{ route('admin.rvm.maintenance-mode', $rvm->id) }}" class="btn btn-warning">
                        <i class="fas fa-wrench me-2"></i>Maintenance Mode
                    </a>
                    <button class="btn btn-primary" onclick="testConnection({{ $rvm->id }})">
                        <i class="fas fa-wifi me-2"></i>Test Connection
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-3 mb-3">
            <div class="card statistics-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div class="avatar avatar-sm">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="fas fa-recycle"></i>
                                </span>
                            </div>
                        </div>
                        <div>
                            <h6 class="mb-0">Total RVM</h6>
                            <h4 class="mb-0 text-primary">1</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card statistics-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div class="avatar avatar-sm">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="fas fa-check-circle"></i>
                                </span>
                            </div>
                        </div>
                        <div>
                            <h6 class="mb-0">Active</h6>
                            <h4 class="mb-0 text-success">{{ $rvm->status_data['status'] === 'active' ? '1' : '0' }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card statistics-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div class="avatar avatar-sm">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="fas fa-clock"></i>
                                </span>
                            </div>
                        </div>
                        <div>
                            <h6 class="mb-0">Timezone Synced</h6>
                            <h4 class="mb-0 text-info">{{ $rvm->timezone ? '1' : '0' }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card statistics-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div class="avatar avatar-sm">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </span>
                            </div>
                        </div>
                        <div>
                            <h6 class="mb-0">Needs Attention</h6>
                            <h4 class="mb-0 text-warning">{{ $rvm->status_data['needs_attention'] ? '1' : '0' }}</h4>
                            <!-- <h4 class="mb-0 text-warning">{ { $rvm->connection_status === 'connected' ? '0' : '1' } }</h4> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RVM Status and Last Transaction -->
    <div class="row mb-5">
        <!-- RVM Status Card -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-gradient-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="fas fa-recycle"></i>
                                </span>
                            </div>
                            <div>
                                <h5 class="card-title mb-0 text-white">{{ $rvm->name }}</h5>
                                <p class="text-white-50 mb-0 small">{{ $rvm->location ?? 'No location set' }}</p>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <!-- RVM Status Icon -->
                            <span class="status-pulse-icon status-icon-{{ $rvm->status_data['class'] }}" data-bs-toggle="tooltip" data-bs-placement="top" title="RVM Status: {{ $rvm->status_data['label'] }}">
                                <i class="fas fa-{{ $rvm->status_data['icon'] }}"></i>
                            </span>
                            
                            <!-- Connection Status Icon -->
                            <span class="status-pulse-icon connection-status-{{ $rvm->connection_status }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Connection: {{ ucfirst($rvm->connection_status) }}">
                                <i class="fas {{ $rvm->connection_status === 'connected' ? 'fa-wifi' : ($rvm->connection_status === 'local' ? 'fa-home' : 'fa-wifi-slash') }}"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-sm me-3">
                                    <div class="avatar-title bg-soft-info text-info rounded-circle">
                                        <i class="fas fa-info-circle"></i>
                                    </div>
                                </div>
                                <h6 class="mb-0 text-dark">Basic Information</h6>
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="fas fa-map-marker-alt text-primary me-3"></i>
                                        <div class="flex-grow-1">
                                            <small class="text-muted d-block">Location</small>
                                            <span class="fw-medium">{{ $rvm->location ?? 'N/A' }}</span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary ms-2 location-edit-btn" 
                                data-bs-toggle="modal" 
                                data-bs-target="#locationPickerModal"
                                title="Klik to Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="fas fa-building text-primary me-3"></i>
                                        <div>
                                            <small class="text-muted d-block">Address</small>
                                            <span class="fw-medium">{{ $rvm->address ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="fas fa-network-wired text-primary me-3"></i>
                                        <div>
                                            <small class="text-muted d-block">IP Address</small>
                                            <span class="fw-medium">{{ $rvm->ip_address ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="fas fa-plug text-primary me-3"></i>
                                        <div class="flex-grow-1">
                                            <small class="text-muted d-block">Port</small>
                                            <span class="fw-medium">{{ $rvm->port ?? 'N/A' }}</span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary ms-2 port-edit-btn" 
                                data-bs-toggle="modal" 
                                data-bs-target="#portEditModal"
                                title="Klik to Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="fas fa-clock text-primary me-3"></i>
                                        <div>
                                            <small class="text-muted d-block">Timezone</small>
                                            <span class="fw-medium">{{ $rvm->timezone ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-sm me-3">
                                    <div class="avatar-title bg-soft-success text-success rounded-circle">
                                        <i class="fas fa-cogs"></i>
                                    </div>
                                </div>
                                <h6 class="mb-0 text-dark">System Information</h6>
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="fas fa-key text-primary me-3"></i>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <small class="text-muted d-block">API Key</small>
                                            <code class="text-truncate d-block" style="max-width: 150px; font-size: 0.8rem;">
                                                {{ $rvm->api_key ?? 'N/A' }}
                                            </code>
                                        </div>
                                        @if($rvm->api_key)
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary ms-2" 
                                                onclick="copyToClipboard('{{ $rvm->api_key }}')"
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                title="Klik to Copy">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="fas fa-remote-control text-primary me-3"></i>
                                        <div class="flex-grow-1">
                                            <small class="text-muted d-block">Remote Access</small>
                                            <span class="badge bg-{{ $rvm->remote_access_enabled ? 'success' : 'secondary' }} px-2 py-1">
                                                <i class="fas fa-{{ $rvm->remote_access_enabled ? 'check' : 'times' }} me-1"></i>
                                                {{ $rvm->remote_access_enabled ? 'Enabled' : 'Disabled' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="fas fa-desktop text-primary me-3"></i>
                                        <div class="flex-grow-1">
                                            <small class="text-muted d-block">Kiosk Mode</small>
                                            <span class="badge bg-{{ $rvm->kiosk_mode_enabled ? 'success' : 'secondary' }} px-2 py-1">
                                                <i class="fas fa-{{ $rvm->kiosk_mode_enabled ? 'check' : 'times' }} me-1"></i>
                                                {{ $rvm->kiosk_mode_enabled ? 'Enabled' : 'Disabled' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="fas fa-heartbeat text-primary me-3"></i>
                                        <div>
                                            <small class="text-muted d-block">Last Seen</small>
                                            <span class="fw-medium">{{ $rvm->last_ping ? \Carbon\Carbon::parse($rvm->last_ping)->diffForHumans() : 'Never' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="fas fa-calendar-plus text-primary me-3"></i>
                                        <div>
                                            <small class="text-muted d-block">Created</small>
                                            <span class="fw-medium">{{ \Carbon\Carbon::parse($rvm->created_at)->format('M d, Y H:i') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Last Transaction Card -->
        @include('admin.rvm.last-transaction-card')
    </div>


    <!-- System Performance Chart -->
    @include('admin.rvm.chart-section')
    

</div>

<script>
function testConnection(rvmId) {
    // Show loading state
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Testing...';
    btn.disabled = true;

    // Make request to test connection
    fetch(`/admin/rvm/${rvmId}/test-connection`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showAlert('success', 'Connection test successful!');
            } else {
                // Show error message
                showAlert('danger', 'Connection test failed: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Connection test failed: ' + error.message);
        })
        .finally(() => {
            // Restore button state
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
}

function showAlert(type, message) {
    // Create alert element
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at top of container
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alert, container.firstChild);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}

function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        // Use modern clipboard API
        navigator.clipboard.writeText(text).then(() => {
            showAlert('success', 'API Key copied to clipboard!');
        }).catch(() => {
            showAlert('danger', 'Failed to copy API Key');
        });
    } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            showAlert('success', 'API Key copied to clipboard!');
        } catch (err) {
            showAlert('danger', 'Failed to copy API Key');
        }
        textArea.remove();
    }
}
</script>
<script src="https://api.tiles.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wait for Bootstrap to be available
    function waitForBootstrap(callback) {
        if (typeof bootstrap !== 'undefined') {
            callback();
        } else {
            setTimeout(() => waitForBootstrap(callback), 100);
        }
    }
    
    waitForBootstrap(function() {
        initModalLocationPicker();
    });
});

function initModalLocationPicker(){
    const accessToken = '{{ env('MAPBOX_ACCESS_TOKEN') }}';
    if(!accessToken){ console.warn('MAPBOX_ACCESS_TOKEN not set'); return; }
    mapboxgl.accessToken = accessToken;
    const defaultLng = {!! json_encode($rvm->longitude ?? 110.366) !!};
    const defaultLat = {!! json_encode($rvm->latitude ?? -7.795) !!};

    // Simple bootstrap toast
    function showToast(message, type){
        const container = document.body;
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-bg-${type} border-0 position-fixed top-0 end-0 m-3`;
        toast.setAttribute('role','alert');
        toast.innerHTML = `<div class=\"d-flex\"><div class=\"toast-body\">${message}</div><button type=\"button\" class=\"btn-close btn-close-white me-2 m-auto\" data-bs-dismiss=\"toast\"></button></div>`;
        container.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast, { delay: 2000 });
        bsToast.show();
        toast.addEventListener('hidden.bs.toast', () => toast.remove());
    }

    // Initialize tooltips for status icons
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize tooltip for edit buttons (special case for modal + tooltip)
    const portEditBtn = document.querySelector('.port-edit-btn');
    if (portEditBtn) {
        new bootstrap.Tooltip(portEditBtn, {
            placement: 'top'
        });
    }

    const locationEditBtn = document.querySelector('.location-edit-btn');
    if (locationEditBtn) {
        new bootstrap.Tooltip(locationEditBtn, {
            placement: 'top'
        });
    }

    // Modal Location Picker functionality
    let modalMap = null;
    let modalMarker = null;
    
    const locationModal = document.getElementById('locationPickerModal');
    if (locationModal) {
        locationModal.addEventListener('shown.bs.modal', function () {
            // Initialize modal map
            if (!modalMap) {
                modalMap = new mapboxgl.Map({
                    container: 'mapbox-modal',
                    style: 'mapbox://styles/mapbox/streets-v12',
                    center: [defaultLng, defaultLat],
                    zoom: {!! json_encode(($rvm->latitude && $rvm->longitude) ? 15 : 12) !!}
                });
                
                modalMap.addControl(new mapboxgl.NavigationControl({ visualizePitch: true }), 'top-right');
                
                const modalGeolocate = new mapboxgl.GeolocateControl({ 
                    positionOptions: { enableHighAccuracy: true }, 
                    trackUserLocation: false, 
                    showUserLocation: true 
                });
                modalMap.addControl(modalGeolocate, 'top-right');
                
                modalGeolocate.on('geolocate', (e) => {
                    const lat = e.coords.latitude; 
                    const lng = e.coords.longitude;
                    modalMap.flyTo({ center: [lng, lat], zoom: 16 });
                    modalMarker.setLngLat([lng, lat]);
                    updateModalLatLng(lat, lng);
                });
                
                modalMarker = new mapboxgl.Marker({ draggable: true })
                    .setLngLat([defaultLng, defaultLat])
                    .addTo(modalMap);
                
                modalMarker.on('dragend', () => {
                    const {lng, lat} = modalMarker.getLngLat();
                    updateModalLatLng(lat, lng);
                });
                
                modalMap.on('click', (e) => {
                    modalMarker.setLngLat(e.lngLat);
                    updateModalLatLng(e.lngLat.lat, e.lngLat.lng);
                });
                
                // Modal search box events
                const modalSb = document.getElementById('mapbox-search-input-modal');
                if(modalSb){
                    modalSb.setAttribute('value', '');
                    modalMap.on('moveend', () => {
                        const b = modalMap.getBounds();
                        const proximity = modalMap.getCenter();
                        modalSb.options = Object.assign({}, modalSb.options, {
                            bbox: [b.getWest(), b.getSouth(), b.getEast(), b.getNorth()],
                            proximity: { lng: proximity.lng, lat: proximity.lat }
                        });
                    });
                    modalSb.addEventListener('retrieve', (e) => {
                        const feat = e.detail && (e.detail.feature || (e.detail.features && e.detail.features[0]));
                        if(!feat || !feat.geometry || !Array.isArray(feat.geometry.coordinates)) return;
                        const coords = feat.geometry.coordinates;
                        const lng = Number(coords[0]);
                        const lat = Number(coords[1]);
                        modalMap.flyTo({center:[lng,lat], zoom:17});
                        modalMarker.setLngLat([lng,lat]);
                        updateModalLatLng(lat, lng);
                        const addr = (feat.properties && (feat.properties.full_address || feat.properties.name || feat.properties.place_formatted)) || '';
                        const addrEl = document.getElementById('address-modal');
                        if(addrEl){ addrEl.value = addr; }
                    });
                }
                
                // Modal toolbar actions
                const btnMyModal = document.getElementById('btnUseMyLocationModal');
                if(btnMyModal){ btnMyModal.addEventListener('click', () => modalGeolocate.trigger()); }
                const btnReModal = document.getElementById('btnRecenterModal');
                if(btnReModal){ btnReModal.addEventListener('click', () => modalMap.flyTo({ center: [defaultLng, defaultLat], zoom: 15 })); }
                
                updateModalLatLng(defaultLat, defaultLng);
            }
            
            // Resize map when modal is shown
            setTimeout(() => {
                if (modalMap) {
                    modalMap.resize();
                }
            }, 200);
        });
    }
    
    function updateModalLatLng(lat, lng){
        const latEl = document.getElementById('latitude-modal');
        const lngEl = document.getElementById('longitude-modal');
        if(!latEl || !lngEl) return;
        latEl.value = Number(lat).toFixed(7);
        lngEl.value = Number(lng).toFixed(7);
        const latNum = Number(lat), lngNum = Number(lng);
        const openBtn = document.getElementById('openInMapsModalBtn');
        if(openBtn){ openBtn.href = `https://www.google.com/maps?q=${latNum},${lngNum}`; }
    }
    
    // Save location from modal
    const saveModalBtn = document.getElementById('saveLocationModalBtn');
    if(saveModalBtn){
        saveModalBtn.addEventListener('click', async () => {
            const latEl = document.getElementById('latitude-modal');
            const lngEl = document.getElementById('longitude-modal');
            if(!latEl || !lngEl) return;
            const lat = latEl.value; 
            const lng = lngEl.value;
            const address = (document.getElementById('address-modal') || {}).value || null;
            try{
                const res = await fetch('{{ route('admin.rvm.update', $rvm->id) }}', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ latitude: lat, longitude: lng, address: address })
                });
                const data = await res.json();
                const ok = res.ok && data.success;
                showToast(ok ? 'Location saved successfully' : 'Failed to save location', ok ? 'success' : 'danger');
                if (ok) {
                    // Close modal and refresh page to show updated data
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const modal = bootstrap.Modal.getInstance(locationModal);
                        if (modal) {
                            modal.hide();
                        } else {
                            // Fallback: trigger close button click
                            const closeBtn = locationModal.querySelector('[data-bs-dismiss="modal"]');
                            if (closeBtn) closeBtn.click();
                        }
                    } else {
                        // Fallback: hide modal manually
                        locationModal.style.display = 'none';
                        locationModal.classList.remove('show');
                        document.body.classList.remove('modal-open');
                        const backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) backdrop.remove();
                    }
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            }catch(err){
                showToast('Error saving location', 'danger');
            }
        });
    }

    // Save port from modal
    const savePortBtn = document.getElementById('savePortBtn');
    if(savePortBtn){
        savePortBtn.addEventListener('click', async () => {
            const portEl = document.getElementById('port');
            if(!portEl) return;
            
            const port = portEl.value;
            
            // Validate port number
            if (!port || port < 1 || port > 65535) {
                showToast('Please enter a valid port number (1-65535)', 'danger');
                return;
            }
            
            try{
                const res = await fetch('{{ route('admin.rvm.update', $rvm->id) }}', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ port: port })
                });
                const data = await res.json();
                const ok = res.ok && data.success;
                showToast(ok ? 'Port updated successfully' : 'Failed to update port', ok ? 'success' : 'danger');
                if (ok) {
                    // Close modal and refresh page to show updated data
                    const portModal = document.getElementById('portEditModal');
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const modal = bootstrap.Modal.getInstance(portModal);
                        if (modal) {
                            modal.hide();
                        } else {
                            // Fallback: trigger close button click
                            const closeBtn = portModal.querySelector('[data-bs-dismiss="modal"]');
                            if (closeBtn) closeBtn.click();
                        }
                    } else {
                        // Fallback: hide modal manually
                        portModal.style.display = 'none';
                        portModal.classList.remove('show');
                        document.body.classList.remove('modal-open');
                        const backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) backdrop.remove();
                    }
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            }catch(err){
                showToast('Error updating port', 'danger');
            }
        });
     }

    // Copy to clipboard function
    function copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            // Use modern clipboard API
            navigator.clipboard.writeText(text).then(() => {
                showToast('API Key copied to clipboard!', 'success');
            }).catch(() => {
                showToast('Failed to copy API Key', 'danger');
            });
        } else {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
                showToast('API Key copied to clipboard!', 'success');
            } catch (err) {
                showToast('Failed to copy API Key', 'danger');
            }
            textArea.remove();
        }
    }
}
</script>

<style>
.status-icon {
    cursor: pointer;
    transition: transform 0.2s ease;
}

.status-icon:hover {
    transform: scale(1.1);
}

.status-icon-container {
    display: inline-block;
    padding: 10px;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    border: 2px solid rgba(255, 255, 255, 0.2);
}

.status-icon-container:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
}

/* Status Pulse Icon - Compact Version */
.status-pulse-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.status-pulse-icon i {
    font-size: 12px;
    color: white;
    z-index: 2;
    position: relative;
}

.status-pulse-icon:hover {
    transform: scale(1.1);
}

/* RVM Status Backgrounds */
.status-icon-success {
    background-color: #28a745;
}
.status-icon-secondary {
    background-color: #6c757d;
}
.status-icon-warning {
    background-color: #ffc107;
}
.status-icon-danger {
    background-color: #dc3545;
}
.status-icon-primary {
    background-color: #696cff;
}

.rvm-status-active {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    animation: pulse-active 2s infinite;
}

.rvm-status-active:hover {
    background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
}

.rvm-status-inactive {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
}

.rvm-status-inactive:hover {
    background: linear-gradient(135deg, #5a6268 0%, #6c757d 100%);
}

.rvm-status-maintenance {
    background: linear-gradient(135deg, #ffc107 0%, #ffca2c 100%);
    animation: pulse-maintenance 2s infinite;
}

.rvm-status-maintenance:hover {
    background: linear-gradient(135deg, #ffca2c 0%, #ffc107 100%);
}

.rvm-status-unknown, .rvm-status-error {
    background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
}

.rvm-status-unknown:hover, .rvm-status-error:hover {
    background: linear-gradient(135deg, #e74c3c 0%, #dc3545 100%);
}

/* Connection Status Backgrounds */
.connection-status-connected {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    animation: pulse-connected 2s infinite;
}

.connection-status-connected:hover {
    background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
}

.connection-status-local {
    background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
    animation: pulse-local 2s infinite;
}

.connection-status-local:hover {
    background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);
}

.connection-status-disconnected {
    background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
}

.connection-status-disconnected:hover {
    background: linear-gradient(135deg, #e74c3c 0%, #dc3545 100%);
}

/* Pulse Animations */
@keyframes pulse-active {
    0% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
        transform: scale(1);
    }
    50% {
        box-shadow: 0 0 0 8px rgba(40, 167, 69, 0);
        transform: scale(1.05);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
        transform: scale(1);
    }
}

@keyframes pulse-maintenance {
    0% {
        box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7);
        transform: scale(1);
    }
    50% {
        box-shadow: 0 0 0 8px rgba(255, 193, 7, 0);
        transform: scale(1.05);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(255, 193, 7, 0);
        transform: scale(1);
    }
}

@keyframes pulse-connected {
    0% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
        transform: scale(1);
    }
    50% {
        box-shadow: 0 0 0 8px rgba(40, 167, 69, 0);
        transform: scale(1.05);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
        transform: scale(1);
    }
}

@keyframes pulse-local {
    0% {
        box-shadow: 0 0 0 0 rgba(23, 162, 184, 0.7);
        transform: scale(1);
    }
    50% {
        box-shadow: 0 0 0 8px rgba(23, 162, 184, 0);
        transform: scale(1.05);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(23, 162, 184, 0);
        transform: scale(1);
    }
}
</style>

<!-- Location Picker Modal -->
<div class="modal fade" id="locationPickerModal" tabindex="-1" aria-labelledby="locationPickerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-gradient-info text-white">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-sm me-3">
                        <span class="avatar-initial rounded bg-label-info">
                            <i class="fas fa-map-marker-alt"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="locationPickerModalLabel">Edit Location</h5>
                        <small>Search and drop a pin to set latitude/longitude</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="locationForm" action="{{ route('admin.rvm.update', $rvm->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <div class="d-flex gap-2 mb-3">
                            <button type="button" id="btnUseMyLocationModal" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-location-arrow me-1"></i>Use My Location
                            </button>
                            <button type="button" id="btnRecenterModal" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-crosshairs me-1"></i>Recenter
                            </button>
                        </div>
                        <mapbox-search-box id="mapbox-search-input-modal" access-token="{{ env('MAPBOX_ACCESS_TOKEN') }}" options='{"language":"id","country":"ID"}'></mapbox-search-box>
                    </div>
                    
                    <div id="mapbox-modal" style="width:100%; height:400px; border-radius: .5rem; overflow:hidden; border: 1px solid #dee2e6;"></div>
                    
                    <div class="mt-3 row g-2">
                        <input id="latitude-modal" name="latitude" type="hidden" value="{{ $rvm->latitude }}" />
                        <input id="longitude-modal" name="longitude" type="hidden" value="{{ $rvm->longitude }}" />
                        <input id="location-modal" name="location" type="hidden" value="{{ $rvm->location }}" />
                        
                        <div class="col-md-12">
                            <label class="form-label">Address</label>
                            <input id="address-modal" name="address" class="form-control" type="text" placeholder="Selected address will appear here" value="{{ $rvm->address }}" />
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" id="saveLocationModalBtn" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Save Location
                </button>
                <a id="openInMapsModalBtn" class="btn btn-outline-info" target="_blank">
                    <i class="fas fa-external-link-alt me-1"></i>Open in Maps
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Port Edit Modal -->
<div class="modal fade" id="portEditModal" tabindex="-1" aria-labelledby="portEditModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-sm me-3">
                        <span class="avatar-initial rounded bg-label-primary">
                            <i class="fas fa-plug"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="portEditModalLabel">Edit Port</h5>
                        <small>Change the port number for {{ $rvm->name }}</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="portForm" action="{{ route('admin.rvm.update', $rvm->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="port" class="form-label">Port Number</label>
                        <input type="number" 
                               class="form-control" 
                               id="port" 
                               name="port" 
                               value="{{ $rvm->port }}" 
                               min="1" 
                               max="65535" 
                               required>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Port range: 1-65535. Common ports: 8000, 8001, 8080, 3000, 5000, 5001
                        </div>
                    </div>
                    
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <i class="fas fa-lightbulb me-2"></i>
                        <div>
                            <strong>Info:</strong> Changing the port will affect how the RVM communicates with the server. 
                            Make sure the new port is available and properly configured on the RVM device.
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" id="savePortBtn" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Save Port
                </button>
            </div>
        </div>
    </div>
</div>

@endsection
