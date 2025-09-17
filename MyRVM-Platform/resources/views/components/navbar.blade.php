<!-- Modern Navbar with Glassmorphism -->
<nav class="layout-navbar navbar navbar-expand-xl align-items-center modern-navbar" id="layout-navbar">
    <div class="container-xxl">
        <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4 ms-0">
            <a href="{{ url('/admin/rvm-dashboard') }}" class="app-brand-link">
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

        <!-- Menu -->
        <div class="layout-menu-toggle navbar-nav align-items-xl-center me-auto me-xl-0 d-xl-none">
            <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                <i class="icon-base ti tabler-menu-2 icon-lg"></i>
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
                        <span class="badge bg-danger rounded-pill badge-notifications">5</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end py-0">
                        <li class="dropdown-menu-header border-bottom">
                            <div class="dropdown-header d-flex align-items-center py-3">
                                <h5 class="mb-0 me-auto">Notification</h5>
                                <a href="javascript:void(0)" class="dropdown-notifications-all text-body" data-bs-toggle="tooltip" data-bs-placement="top" title="Mark all as read"><i class="ti ti-mail-opened fs-4"></i></a>
                            </div>
                        </li>
                        <li class="dropdown-notifications-list scrollable-container">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item list-group-item-action dropdown-notifications-item">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar">
                                                <span class="avatar-initial rounded-circle bg-label-danger">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">RVM-001 Storage Full</h6>
                                            <p class="mb-0">Storage capacity has reached 100%</p>
                                            <small class="text-muted">2 minutes ago</small>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <li class="dropdown-menu-footer border-top">
                            <a href="javascript:void(0);" class="dropdown-item d-flex justify-content-center text-primary p-2 h-px-40 mb-1 mx-3">
                                View all notifications
                            </a>
                        </li>
                    </ul>
                </li>
                <!--/ Notification -->

                <!-- User -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                        <div class="avatar avatar-online">
                            <img src="../../assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle">
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="pages-account-settings-account.html">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar avatar-online">
                                            <img src="../../assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle">
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">John Doe</h6>
                                        <small class="text-muted">Admin</small>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <div class="dropdown-divider my-1"></div>
                        </li>
                        <li>
                            <a class="dropdown-item" href="pages-account-settings-account.html">
                                <i class="ti ti-user-check me-2 ti-sm"></i>
                                <span class="align-middle">My Profile</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="pages-account-settings-notifications.html">
                                <i class="ti ti-bell me-2 ti-sm"></i>
                                <span class="align-middle">Notifications</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="pages-account-settings-connections.html">
                                <i class="ti ti-link me-2 ti-sm"></i>
                                <span class="align-middle">Connections</span>
                            </a>
                        </li>
                        <li>
                            <div class="dropdown-divider my-1"></div>
                        </li>
                        <li>
                            <a class="dropdown-item" href="auth-login-cover.html" target="_blank">
                                <i class="ti ti-logout me-2 ti-sm"></i>
                                <span class="align-middle">Log Out</span>
                            </a>
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
