<!-- Modern Navbar with Glassmorphism -->
<nav class="layout-navbar navbar navbar-expand-xl align-items-center modern-navbar" id="layout-navbar">
    <div class="container-xxl">
        <div class="navbar-brand app-brand demo d-flex py-0 me-4 ms-0">
            <a href="{{ url('/admin/dashboard') }}" class="app-brand-link">
                <span class="app-brand-logo demo">
                    <span class="text-primary">
                        <svg width="32" height="22" viewBox="0 0 32 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z" fill="currentColor" />
                        </svg>
                    </span>
                </span>
                <span class="app-brand-text demo menu-text fw-bold ms-2">MyRVM Platform</span>
            </a>
        </div>

        <!-- Mobile Menu Toggle -->
        <div class="layout-menu-toggle navbar-nav align-items-xl-center me-auto me-xl-0 d-xl-none">
            <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu">
                <i class="fas fa-bars icon-lg"></i>
            </a>
        </div>

        <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
            <!-- Search -->
            <div class="navbar-nav align-items-center">
                <div class="nav-item navbar-search-wrapper mb-0">
                    <a class="nav-item nav-link search-toggler px-0" href="javascript:void(0);">
                        <i class="fas fa-search icon-lg"></i>
                    </a>
                </div>
            </div>
            <!-- /Search -->

            <ul class="navbar-nav flex-row align-items-center ms-auto">
                <!-- Quick links -->
                <li class="nav-item dropdown-shortcuts navbar-dropdown dropdown me-2 me-xl-0">
                    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        <i class="fas fa-bolt icon-lg"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end py-0">
                        <div class="dropdown-menu-header border-bottom">
                            <div class="dropdown-header d-flex align-items-center py-3">
                                <h5 class="mb-0 me-auto">Quick Links</h5>
                                <a href="javascript:void(0)" class="dropdown-shortcuts-add text-body" data-bs-toggle="tooltip" data-bs-placement="top" title="Add shortcuts"><i class="ti ti-sm ti-apps"></i></a>
                            </div>
                        </div>
                        <div class="dropdown-shortcuts-list scrollable-container">
                            <div class="row row-bordered overflow-visible g-0">
                                <div class="dropdown-shortcuts-item col">
                                    <span class="dropdown-shortcuts-icon rounded-circle bg-label-primary d-flex align-items-center justify-content-center">
                                        <i class="fas fa-video icon-sm"></i>
                                    </span>
                                    <a href="#" class="stretched-link" onclick="openEdgeVisionModal()">Live Camera</a>
                                </div>
                                <div class="dropdown-shortcuts-item col">
                                    <span class="dropdown-shortcuts-icon rounded-circle bg-label-success d-flex align-items-center justify-content-center">
                                        <i class="fas fa-upload icon-sm"></i>
                                    </span>
                                    <a href="#" class="stretched-link" onclick="openImageUploadModal()">Upload & Process</a>
                                </div>
                            </div>
                            <div class="row row-bordered overflow-visible g-0">
                                <div class="dropdown-shortcuts-item col">
                                    <span class="dropdown-shortcuts-icon rounded-circle bg-label-info d-flex align-items-center justify-content-center">
                                        <i class="fas fa-cogs icon-sm"></i>
                                    </span>
                                    <a href="#" class="stretched-link" onclick="openEngineConfigModal()">Engine Config</a>
                                </div>
                                <div class="dropdown-shortcuts-item col">
                                    <span class="dropdown-shortcuts-icon rounded-circle bg-label-warning d-flex align-items-center justify-content-center">
                                        <i class="fas fa-desktop icon-sm"></i>
                                    </span>
                                    <a href="#" class="stretched-link" onclick="openRemoteAccessModal()">Remote Access</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <!-- Quick links -->

                <!-- Notification -->
                <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-1">
                    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        <i class="fas fa-bell icon-lg"></i>
                        <span class="badge bg-danger rounded-pill badge-notifications" @if($unreadNotificationCount == 0) style="display: none;" @endif>{{ $unreadNotificationCount }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end py-0">
                        <li class="dropdown-menu-header border-bottom">
                            <div class="dropdown-header d-flex align-items-center py-3">
                                <h5 class="mb-0 me-auto">Notification</h5>
                                <a href="{{ route('admin.notifications.read-all') }}" class="dropdown-notifications-all text-body" data-bs-toggle="tooltip" data-bs-placement="top" title="Mark all as read" onclick="markAllAsRead(event)">
                                    <i class="fas fa-check-double fs-5 text-primary"></i>
                                </a>
                            </div>
                        </li>
                        <li class="dropdown-notifications-list scrollable-container">
                            <ul class="list-group list-group-flush">
                                @forelse($recentNotifications as $notification)
                                    <li class="list-group-item list-group-item-action dropdown-notifications-item {{ !$notification->isRead() ? 'bg-light border-start border-primary border-3' : '' }} py-2">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-shrink-0 me-2">
                                                <div class="avatar avatar-sm">
                                                    @if($notification->type == 'success')
                                                        <span class="avatar-initial rounded-circle bg-success text-white">
                                                            <i class="fas fa-check fs-6"></i>
                                                        </span>
                                                    @elseif($notification->type == 'warning')
                                                        <span class="avatar-initial rounded-circle bg-warning text-white">
                                                            <i class="fas fa-exclamation-triangle fs-6"></i>
                                                        </span>
                                                    @elseif($notification->type == 'error')
                                                        <span class="avatar-initial rounded-circle bg-danger text-white">
                                                            <i class="fas fa-times fs-6"></i>
                                                        </span>
                                                    @else
                                                        <span class="avatar-initial rounded-circle bg-info text-white">
                                                            <i class="fas fa-info fs-6"></i>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 min-w-0">
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <h6 class="mb-0 text-truncate {{ !$notification->isRead() ? 'fw-semibold' : 'fw-normal' }}" style="font-size: 0.7rem;">
                                                        {{ $notification->title }}
                                                    </h6>
                                                    @if(!$notification->isRead())
                                                        <span class="ms-2" style="display: inline-block; width: 8px; height: 8px; background-color: #696cff; border-radius: 50%;"></span>
                                                    @endif
                                                </div>
                                                <p class="mb-1 text-muted text-truncate" style="font-size: 0.6rem; line-height: 1.2;">
                                                    {{ Str::limit($notification->message, 50) }}
                                                </p>
                                                <small class="text-muted" style="font-size: 0.55rem;">
                                                    {{ $notification->created_at->diffForHumans() }}
                                                </small>
                                            </div>
                                        </div>
                                    </li>
                                @empty
                                    <li class="list-group-item text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-bell-slash fs-4 mb-2 opacity-50"></i>
                                            <p class="mb-0 small">No new notifications</p>
                                        </div>
                                    </li>
                                @endforelse
                            </ul>
                        </li>
                        <li class="dropdown-menu-footer border-top">
                            <a href="{{ route('admin.notifications') }}" class="dropdown-item d-flex justify-content-center text-primary p-2 h-px-40 mb-1 mx-3">
                                View all notifications
                            </a>
                        </li>
                    </ul>
                </li>
                <!--/ Notification -->

                <!-- Custom CSS for modern notification dropdown -->
                <style>
                    .dropdown-notifications-item {
                        transition: all 0.2s ease-in-out;
                        border: none !important;
                    }
                    .dropdown-notifications-item:hover {
                        background-color: #f8f9fa !important;
                        transform: translateX(2px);
                    }
                    .dropdown-notifications-item.bg-light {
                        background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%) !important;
                    }
                    .avatar-sm {
                        width: 32px;
                        height: 32px;
                    }
                    .avatar-sm .avatar-initial {
                        width: 32px;
                        height: 32px;
                        font-size: 0.75rem;
                    }
                    .dropdown-notifications-list {
                        max-height: 350px;
                    }
                    .badge.rounded-pill {
                        padding: 0.25em 0.5em;
                    }
                    .text-truncate {
                         overflow: hidden;
                         text-overflow: ellipsis;
                         white-space: nowrap;
                     }
                     .dropdown-notifications-all:hover i {
                         transform: scale(1.1);
                         transition: transform 0.2s ease;
                     }
                 </style>

                 <script>
                     function markAllAsRead(event) {
                         event.preventDefault();
                         
                         // Show loading state
                         const icon = event.target.closest('a').querySelector('i');
                         const originalClass = icon.className;
                         icon.className = 'fas fa-spinner fa-spin fs-5 text-primary';
                         
                         fetch('{{ route("admin.notifications.read-all") }}', {
                             method: 'POST',
                             headers: {
                                 'Content-Type': 'application/json',
                                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                             }
                         })
                         .then(response => response.json())
                         .then(data => {
                             if (data.success) {
                                 // Update badge count to 0
                                 const badge = document.querySelector('.badge-notifications');
                                 if (badge) {
                                     badge.style.display = 'none';
                                 }
                                 
                                 // Remove unread styling from notifications
                                 document.querySelectorAll('.dropdown-notifications-item.bg-light').forEach(item => {
                                     item.classList.remove('bg-light', 'border-start', 'border-primary', 'border-3');
                                     const badge = item.querySelector('.badge.bg-primary');
                                     if (badge) badge.remove();
                                     const title = item.querySelector('h6.fw-semibold');
                                     if (title) title.classList.replace('fw-semibold', 'fw-normal');
                                 });
                                 
                                 // Show success message
                                 showToast('All notifications marked as read', 'success');
                             } else {
                                 showToast('Failed to mark notifications as read', 'error');
                             }
                         })
                         .catch(error => {
                             console.error('Error:', error);
                             showToast('An error occurred', 'error');
                         })
                         .finally(() => {
                             // Restore original icon
                             icon.className = originalClass;
                         });
                     }
                     
                     function showToast(message, type) {
                         // Simple toast notification
                         const toast = document.createElement('div');
                         toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
                         toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                         toast.innerHTML = `
                             <div class="d-flex align-items-center">
                                 <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                                 ${message}
                                 <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
                             </div>
                         `;
                         document.body.appendChild(toast);
                         
                         // Auto remove after 3 seconds
                         setTimeout(() => {
                             if (toast.parentElement) {
                                 toast.remove();
                             }
                         }, 3000);
                     }
                 </script>

                <!-- User -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                        <div class="avatar avatar-online">
                            @if(auth()->user()->avatar)
                                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-px-40 h-auto rounded-circle">
                            @else
                                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-px-40 h-auto rounded-circle">
                            @endif
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.profile') }}">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar avatar-online">
                                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-px-40 h-auto rounded-circle">
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">{{ auth()->user()->name }}</h6>
                                        <small class="text-muted">{{ auth()->user()->role ? auth()->user()->role->name : 'Admin' }}</small>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <div class="dropdown-divider my-1"></div>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.profile') }}">
                                <i class="fas fa-user me-2"></i>
                                <span class="align-middle">My Profile</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.notifications') }}">
                                <i class="fas fa-bell me-2"></i>
                                <span class="align-middle">Notifications</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.connections') }}">
                                <i class="fas fa-link me-2"></i>
                                <span class="align-middle">Connections</span>
                            </a>
                        </li>
                        <li>
                            <div class="dropdown-divider my-1"></div>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <a class="dropdown-item" href="#" onclick="event.preventDefault(); this.closest('form').submit();">
                                    <i class="fas fa-sign-out-alt me-2"></i>
                                    <span class="align-middle">Log Out</span>
                                </a>
                            </form>
                        </li>
                    </ul>
                </li>
                <!--/ User -->
            </ul>
        </div>

        <!-- Search Small Screen -->
        <div class="navbar-search-wrapper search-input-wrapper d-none">
            <input type="text" class="form-control search-input container-xxl border-0" placeholder="Search..." aria-label="Search...">
            <i class="ti ti-x search-toggler cursor-pointer"></i>
        </div>
    </div>
</nav>

<!-- Mobile Offcanvas Menu -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
    <div class="offcanvas-header">
        <div class="app-brand">
            <span class="app-brand-logo demo">
                <span class="text-primary">
                    <svg width="32" height="22" viewBox="0 0 32 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z" fill="currentColor" />
                    </svg>
                </span>
            </span>
            <span class="app-brand-text demo menu-text fw-bold ms-2">MyRVM Platform</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="navbar-nav">
            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}" href="{{ url('/admin/dashboard') }}">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
            </li>

            <!-- RVM Management -->
             <li class="nav-item dropdown">
                 <a class="nav-link dropdown-toggle {{ request()->is('admin/rvm*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="rvmDropdown">
                     <i class="fas fa-recycle me-2"></i>
                     RVM Management
                 </a>
                 <ul class="dropdown-menu" aria-labelledby="rvmDropdown">
                     <li><a class="dropdown-item" href="{{ route('admin.rvm.index') }}">All RVMs</a></li>
                     <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#addRvmModal">Add New RVM</a></li>
                     <li><a class="dropdown-item" href="{{ route('admin.rvm.maintenance') }}">Maintenance</a></li>
                 </ul>
             </li>

            <!-- Monitoring -->
             <li class="nav-item dropdown">
                 <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="monitoringDropdown">
                     <i class="fas fa-eye me-2"></i>
                     Monitoring
                 </a>
                 <ul class="dropdown-menu" aria-labelledby="monitoringDropdown">
                     <li><a class="dropdown-item" href="{{ url('/admin/monitoring/real-time') }}">Real-time Status</a></li>
                     <li><a class="dropdown-item" href="{{ url('/admin/monitoring/analytics') }}">Analytics</a></li>
                     <li><a class="dropdown-item" href="{{ url('/admin/monitoring/reports') }}">Reports</a></li>
                 </ul>
             </li>

             <!-- Playground CV -->
              <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="playgroundDropdown">
                      <i class="fas fa-brain me-2"></i>
                      Playground CV
                  </a>
                 <ul class="dropdown-menu" aria-labelledby="playgroundDropdown">
                     <li><a class="dropdown-item" href="http://localhost:8001/gemini/dashboard" target="_blank">
                         <i class="fas fa-robot me-2"></i>My Vision
                     </a></li>
                     <li><hr class="dropdown-divider"></li>
                     <li><h6 class="dropdown-header">Edge Vision</h6></li>
                     <li><a class="dropdown-item" href="{{ url('/admin/dashboard/live-camera') }}">
                         <i class="fas fa-video me-2"></i>Live Camera
                     </a></li>
                     <li><a class="dropdown-item" href="{{ url('/admin/dashboard/image-upload') }}">
                         <i class="fas fa-upload me-2"></i>Upload & Process
                     </a></li>
                     <li><a class="dropdown-item" href="{{ url('/admin/dashboard/engine-config') }}">
                         <i class="fas fa-cogs me-2"></i>Engine Configuration
                     </a></li>
                 </ul>
             </li>

             <!-- Settings -->
             <li class="nav-item dropdown">
                 <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="settingsDropdown">
                     <i class="fas fa-cog me-2"></i>
                     Settings
                 </a>
                 <ul class="dropdown-menu" aria-labelledby="settingsDropdown">
                     <li><a class="dropdown-item" href="{{ url('/admin/settings/general') }}">General Settings</a></li>
                     <li><a class="dropdown-item" href="{{ url('/admin/settings/users') }}">User Management</a></li>
                     <li><a class="dropdown-item" href="{{ url('/admin/settings/system') }}">System Configuration</a></li>
                 </ul>
             </li>
        </ul>
    </div>
</div>
