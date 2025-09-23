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
    <script id="search-js" defer src="https://api.mapbox.com/search-js/v1.3.0/web.js"></script>
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
                            <h4 class="mb-0 text-success">{{ $rvm->status === 'active' ? '1' : '0' }}</h4>
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
                            <h4 class="mb-0 text-warning">{{ $rvm->connection_status === 'connected' ? '0' : '1' }}</h4>
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
                            <span class="badge bg-{{ $rvm->status === 'active' ? 'success' : 'danger' }} fs-6 px-3 py-2">
                                <i class="fas fa-{{ $rvm->status === 'active' ? 'check-circle' : 'times-circle' }} me-1"></i>
                                {{ ucfirst($rvm->status) }}
                            </span>
                            <span class="badge bg-{{ $rvm->connection_status === 'connected' ? 'success' : 'danger' }} fs-6 px-3 py-2">
                                <i class="fas fa-{{ $rvm->connection_status === 'connected' ? 'wifi' : 'wifi-slash' }} me-1"></i>
                                {{ ucfirst($rvm->connection_status) }}
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
                                        <div>
                                            <small class="text-muted d-block">Location</small>
                                            <span class="fw-medium">{{ $rvm->location ?? 'N/A' }}</span>
                                        </div>
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
                                        <div>
                                            <small class="text-muted d-block">Port</small>
                                            <span class="fw-medium">{{ $rvm->port ?? 'N/A' }}</span>
                                        </div>
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
                                        <div class="flex-grow-1">
                                            <small class="text-muted d-block">API Key</small>
                                            <code class="text-truncate d-block" style="max-width: 200px;">
                                                {{ $rvm->api_key ?? 'N/A' }}
                                            </code>
                                        </div>
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
                                            <small class="text-muted d-block">Last Ping</small>
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

<!-- Mapbox Location Picker -->
<div>
    <div class="row mb-5 mx-0">
        <div class="col-12 px-0">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient-info text-white d-flex align-items-center justify-content-between" style="border-top-left-radius: .5rem; border-top-right-radius: .5rem;">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-3">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="fas fa-map-marker-alt"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="mb-0">Location Picker</h5>
                            <small>Search and drop a pin to set latitude/longitude</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button id="btnUseMyLocation" class="btn btn-light btn-sm">
                            <i class="fas fa-location-arrow me-1"></i>Use My Location
                        </button>
                        <button id="btnRecenter" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-crosshairs me-1"></i>Recenter
                        </button>
                    </div>
                </div>
                <div class="card-body pt-4" style="border-bottom-left-radius: .5rem; border-bottom-right-radius: .5rem;">
                    <div class="mt-2 mb-3">
                        <mapbox-search-box id="mapbox-search-input" access-token="{{ env('MAPBOX_ACCESS_TOKEN') }}" options='{"language":"id","country":"ID"}'></mapbox-search-box>
                    </div>
                    <div id="mapbox-admin" style="width:100%; height:380px; border-radius: .5rem; overflow:hidden;"></div>
                    <div class="mt-3 row g-2">
                        <input id="latitude" name="latitude" type="hidden" value="{{ $rvm->latitude }}" />
                        <input id="longitude" name="longitude" type="hidden" value="{{ $rvm->longitude }}" />
                        <div class="col-md-8">
                            <label class="form-label">Address</label>
                            <input id="address" class="form-control" type="text" placeholder="Selected address will appear here" value="{{ $rvm->address }}" />
                        </div>
                        <div class="col-md-4 d-flex align-items-end justify-content-end">
                            <button id="saveLocationBtn" class="btn btn-primary me-2">
                                <i class="fas fa-save me-1"></i>Save Location
                            </button>
                            <a id="openInMapsBtn" class="btn btn-outline-secondary" target="_blank">
                                <i class="fas fa-external-link-alt me-1"></i>Open in Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
</script>
<script src="https://api.tiles.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js"></script>
<script>
(function initMapboxPicker(){
    const accessToken = '{{ env('MAPBOX_ACCESS_TOKEN') }}';
    if(!accessToken){ console.warn('MAPBOX_ACCESS_TOKEN not set'); return; }
    mapboxgl.accessToken = accessToken;
    const defaultLng = {{ $rvm->longitude ?? 110.366 }};
    const defaultLat = {{ $rvm->latitude ?? -7.795 }};
    const map = new mapboxgl.Map({
        container: 'mapbox-admin',
        style: 'mapbox://styles/mapbox/streets-v12',
        center: [defaultLng, defaultLat],
        zoom: {{ ($rvm->latitude && $rvm->longitude) ? 15 : 12 }}
    });
    map.addControl(new mapboxgl.NavigationControl({ visualizePitch: true }), 'top-right');
    const geolocate = new mapboxgl.GeolocateControl({ positionOptions: { enableHighAccuracy: true }, trackUserLocation: false, showUserLocation: true });
    map.addControl(geolocate, 'top-right');
    geolocate.on('geolocate', (e) => {
        const lat = e.coords.latitude; const lng = e.coords.longitude;
        map.flyTo({ center: [lng, lat], zoom: 16 });
        marker.setLngLat([lng, lat]);
        updateLatLng(lat, lng);
    });
    const marker = new mapboxgl.Marker({ draggable: true })
        .setLngLat([defaultLng, defaultLat])
        .addTo(map);
    function updateLatLng(lat, lng){
        const latEl = document.getElementById('latitude');
        const lngEl = document.getElementById('longitude');
        if(!latEl || !lngEl) return;
        latEl.value = Number(lat).toFixed(7);
        lngEl.value = Number(lng).toFixed(7);
        const latNum = Number(lat), lngNum = Number(lng);
        const openBtn = document.getElementById('openInMapsBtn');
        if(openBtn){ openBtn.href = `https://www.google.com/maps?q=${latNum},${lngNum}`; }
    }
    marker.on('dragend', () => {
        const {lng, lat} = marker.getLngLat();
        updateLatLng(lat, lng);
    });
    map.on('click', (e) => {
        marker.setLngLat(e.lngLat);
        updateLatLng(e.lngLat.lat, e.lngLat.lng);
    });
    updateLatLng(defaultLat, defaultLng);

    // Built-in Search Box events (better ranking & retrieve)
    const sb = document.getElementById('mapbox-search-input');
    if(sb){
        // Bias suggestions to current viewport
        sb.setAttribute('value', '');
        map.on('moveend', () => {
            const b = map.getBounds();
            const proximity = map.getCenter();
            sb.options = Object.assign({}, sb.options, {
                bbox: [b.getWest(), b.getSouth(), b.getEast(), b.getNorth()],
                proximity: { lng: proximity.lng, lat: proximity.lat }
            });
        });
        sb.addEventListener('retrieve', (e) => {
            const feat = e.detail && (e.detail.feature || (e.detail.features && e.detail.features[0]));
            if(!feat || !feat.geometry || !Array.isArray(feat.geometry.coordinates)) return;
            const coords = feat.geometry.coordinates;
            const lng = Number(coords[0]);
            const lat = Number(coords[1]);
            map.flyTo({center:[lng,lat], zoom:17});
            marker.setLngLat([lng,lat]);
            updateLatLng(lat, lng);
            const addr = (feat.properties && (feat.properties.full_address || feat.properties.name || feat.properties.place_formatted)) || '';
            const addrEl = document.getElementById('address');
            if(addrEl){ addrEl.value = addr; }
        });
    }

    const saveBtn = document.getElementById('saveLocationBtn');
    if(saveBtn){
        saveBtn.addEventListener('click', async () => {
            const latEl = document.getElementById('latitude');
            const lngEl = document.getElementById('longitude');
            if(!latEl || !lngEl) return;
            const lat = latEl.value; const lng = lngEl.value;
            const address = (document.getElementById('address') || {}).value || null;
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
                showToast(ok ? 'Location saved' : 'Failed to save location', ok ? 'success' : 'danger');
            }catch(err){
                showToast('Error saving location', 'danger');
            }
        });
    }

    // Toolbar actions
    const btnMy = document.getElementById('btnUseMyLocation');
    if(btnMy){ btnMy.addEventListener('click', () => geolocate.trigger()); }
    const btnRe = document.getElementById('btnRecenter');
    if(btnRe){ btnRe.addEventListener('click', () => map.flyTo({ center: [defaultLng, defaultLat], zoom: 15 })); }

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
})();
</script>
@endsection
