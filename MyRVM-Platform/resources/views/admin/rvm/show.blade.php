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
    <div class="row mb-5" id="location-map">
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
    
    <!-- Location Map (Mapbox) -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-gradient-info text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="fas fa-map-marked-alt"></i>
                                </span>
                            </div>
                            <div>
                                <h5 class="mb-0">Location Map</h5>
                                <small>Search and pick precise coordinates</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <div id="mapbox-admin" style="height: 360px; border-radius: 8px; overflow: hidden;"></div>
                        </div>
                        <div class="col-md-4">
                            <form id="rvmLocationForm" method="POST" action="{{ route('admin.rvm.update', $rvm->id) }}">
                                @csrf
                                @method('PUT')
                                <div class="mb-3">
                                    <label for="searchPlace" class="form-label">Search place</label>
                                    <div class="position-relative">
                                        <input type="text" id="searchPlace" class="form-control" placeholder="Type place or address..." autocomplete="off">
                                        <div id="searchSuggestions" class="list-group position-absolute w-100" style="z-index: 3000; max-height: 260px; overflow:auto; display:none; box-shadow: 0 6px 12px rgba(0,0,0,0.15); background:#fff; border:1px solid #ddd;"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Address (auto from search/pin)</label>
                                    <input type="text" id="address" name="address" class="form-control" value="{{ $rvm->address }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Location Details (notes)</label>
                                    <input type="text" id="location_description" name="location_description" class="form-control" value="{{ $rvm->location_description }}" placeholder="e.g., Lobby Building A, near elevator">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Latitude</label>
                                    <input type="text" id="latitude" name="latitude" class="form-control" value="{{ $rvm->latitude }}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Longitude</label>
                                    <input type="text" id="longitude" name="longitude" class="form-control" value="{{ $rvm->longitude }}" readonly>
                                </div>
                                <button type="submit" class="btn btn-info text-white">
                                    <i class="fas fa-save me-2"></i>Save Coordinates
                                </button>
                                <a target="_blank" id="openInMaps" href="#" class="btn btn-outline-secondary ms-2">
                                    <i class="fas fa-external-link-alt me-2"></i>Open in Maps
                                </a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php($mapboxToken = env('MAPBOX_ACCESS_TOKEN'))
    <link href="{{ asset('assets/vendor/libs/mapbox-gl/mapbox-gl.css') }}" rel="stylesheet">
    <script src="{{ asset('assets/vendor/libs/mapbox-gl/mapbox-gl.js') }}"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.mapboxgl) return;
        mapboxgl.accessToken = '{{ $mapboxToken }}';
        const centerLng = {{ $rvm->longitude ?? 110.366 }};
        const centerLat = {{ $rvm->latitude ?? -7.795 }};
        const map = new mapboxgl.Map({
            container: 'mapbox-admin',
            style: 'mapbox://styles/mapbox/streets-v12',
            center: [centerLng, centerLat],
            zoom: 12
        });
        const marker = new mapboxgl.Marker({draggable: true}).setLngLat([centerLng, centerLat]).addTo(map);
        async function fetchAddressFromRetrieveOrReverse(lat, lng, retrieveFeature){
            try {
                let address = '';
                if (retrieveFeature?.properties?.full_address) {
                    address = retrieveFeature.properties.full_address;
                } else if (retrieveFeature?.place_formatted) {
                    address = retrieveFeature.place_formatted;
                }
                if (!address) {
                    const rev = await fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/${lng},${lat}.json?access_token=${mapboxgl.accessToken}&limit=1&language=id`);
                    const rj = await rev.json();
                    address = rj?.features?.[0]?.place_name || '';
                }
                const addrEl = document.getElementById('address');
                if (addrEl && address) addrEl.value = address;
            } catch(_) {}
        }

        function updateLatLng(lat, lng, retrieveFeature){
            const latEl = document.getElementById('latitude');
            const lngEl = document.getElementById('longitude');
            if (latEl) latEl.value = lat.toFixed(7);
            if (lngEl) lngEl.value = lng.toFixed(7);
            const link = document.getElementById('openInMaps');
            if (link) link.href = `https://www.google.com/maps?q=${lat},${lng}`;
            fetchAddressFromRetrieveOrReverse(lat, lng, retrieveFeature);
        }
        marker.on('dragend', () => {
            const {lng, lat} = marker.getLngLat();
            updateLatLng(lat, lng, null);
        });
        map.on('click', (e) => {
            marker.setLngLat(e.lngLat);
            updateLatLng(e.lngLat.lat, e.lngLat.lng, null);
        });

        // Lightweight suggest using Mapbox Search Box API
        let typingTimer;
        let sessionToken = (window.crypto && crypto.randomUUID) ? crypto.randomUUID() : (Date.now()+''+Math.random());
        const input = document.getElementById('searchPlace');
        const sugg = document.getElementById('searchSuggestions');
        function hideSuggest(){ if (sugg) { sugg.style.display='none'; sugg.innerHTML=''; } }
        document.addEventListener('click', (e) => { if (sugg && !sugg.contains(e.target) && e.target!==input) hideSuggest(); });
        input && input.addEventListener('input', () => {
            clearTimeout(typingTimer);
            const q = input.value.trim();
            if (!q) return;
            typingTimer = setTimeout(async () => {
                try {
                    const center = map.getCenter();
                    const url = `https://api.mapbox.com/search/searchbox/v1/suggest?q=${encodeURIComponent(q)}&language=id&limit=5&country=id&proximity=${center.lng},${center.lat}&session_token=${sessionToken}&access_token=${mapboxgl.accessToken}`;
                    const res = await fetch(url);
                    const data = await res.json();
                    const suggestions = data?.suggestions || [];
                    if (!sugg) return;
                    if (!suggestions.length) { hideSuggest(); return; }
                    sugg.innerHTML = '';
                    // Build list items
                    suggestions.forEach((s) => {
                        const a = document.createElement('a');
                        a.href = '#';
                        a.className = 'list-group-item list-group-item-action';
                        a.textContent = s.name || s.place_formatted || s.full_address || s.feature_name || (s.address?.street + ' ' + s.address?.name) || 'Result';
                        a.setAttribute('data-mapbox-id', s.mapbox_id);
                        sugg.appendChild(a);
                    });
                    // Delegate handler to parent to avoid event loss
                    const handlePick = async (ev) => {
                        const item = ev.target && ev.target.closest('a.list-group-item');
                        if (!item) return;
                        ev.preventDefault(); ev.stopPropagation();
                        const id = item.getAttribute('data-mapbox-id');
                        if (!id) return;
                        try {
                            // Close suggestions immediately for better UX
                            hideSuggest();
                            input.value = item.textContent || '';
                            const rurl = `https://api.mapbox.com/search/searchbox/v1/retrieve?mapbox_id=${encodeURIComponent(id)}&session_token=${sessionToken}&access_token=${mapboxgl.accessToken}`;
                            let feat = null;
                            try {
                                const rres = await fetch(rurl);
                                if (rres.ok) {
                                    const rdata = await rres.json();
                                    feat = (rdata && rdata.features && rdata.features[0]) ? rdata.features[0] : null;
                                }
                            } catch(inner) { /* fallthrough to geocode */ }
                            if (!feat) {
                                // Fallback to forward geocoding by query text if retrieve 404
                                const center = map.getCenter();
                                const gurl = `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(input.value)}.json?limit=1&language=id&country=ID&proximity=${center.lng},${center.lat}&access_token=${mapboxgl.accessToken}`;
                                const gres = await fetch(gurl);
                                if (gres.ok) {
                                    const gj = await gres.json();
                                    const gf = gj && gj.features && gj.features[0] ? gj.features[0] : null;
                                    if (gf) {
                                        feat = { geometry: { coordinates: gf.center }, properties: { full_address: gf.place_name } };
                                    }
                                }
                            }
                            if (!feat || !feat.geometry || !feat.geometry.coordinates) return;
                            const [lng, lat] = feat.geometry.coordinates;
                            map.flyTo({center: [lng, lat], zoom: 17});
                            marker.setLngLat([lng, lat]);
                            updateLatLng(lat, lng, feat);
                            sessionToken = (window.crypto && crypto.randomUUID) ? crypto.randomUUID() : (Date.now()+''+Math.random());
                        } catch(err) { try { console.error('mapbox retrieve/geocode failed', err); } catch(_) {} }
                    };
                    sugg.onmousedown = handlePick;
                    sugg.onclick = handlePick;
                    sugg.style.display = 'block';
                } catch(e) { /* noop */ }
            }, 400);
        });

        // Initialize link
        updateLatLng(centerLat, centerLng, null);

        // Handle AJAX submit to avoid raw JSON page
        const form = document.getElementById('rvmLocationForm');
        form && form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            const original = btn.innerHTML;
            btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
            try {
                const payload = {
                    latitude: parseFloat(document.getElementById('latitude').value || '0') || null,
                    longitude: parseFloat(document.getElementById('longitude').value || '0') || null,
                    address: document.getElementById('address').value || null,
                    location_description: document.getElementById('location_description').value || null
                };
                const res = await fetch(form.getAttribute('action'), {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok || data.success === false) {
                    showAlert('danger', data.message || 'Failed to update RVM location');
                } else {
                    showAlert('success', 'Location updated successfully');
                }
            } catch(_) {
                showAlert('danger', 'Network error while saving');
            } finally {
                btn.disabled = false; btn.innerHTML = original;
            }
        });
    });
    </script>
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
@endsection
