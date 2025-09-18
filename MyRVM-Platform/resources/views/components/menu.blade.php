<!-- Horizontal Menu -->
<div class="layout-menu menu-horizontal menu bg-menu-theme">
    <div class="container-xxl">
        <ul class="menu-inner">
            <!-- Dashboard -->
            <li class="menu-item active">
                <a href="{{ url('/admin/dashboard') }}" class="menu-link">
                    <i class="menu-icon fas fa-tachometer-alt"></i>
                    <span data-i18n="Dashboard">Dashboard 123</span>
                </a>
            </li>

            <!-- RVM Management -->
            <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon fas fa-recycle"></i>
                    <span data-i18n="RVM Management">RVM Management 123</span>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item">
                        <a href="{{ route('admin.rvm.index') }}" class="menu-link">
                            <span data-i18n="All RVMs">All RVMs</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="#" class="menu-link" data-bs-toggle="modal" data-bs-target="#addRvmModal">
                            <span data-i18n="Add New RVM">Add New RVM</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('admin.rvm.maintenance') }}" class="menu-link">
                            <span data-i18n="Maintenance">Maintenance</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Monitoring -->
            <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon fas fa-eye"></i>
                    <span data-i18n="Monitoring">Monitoring</span>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item">
                        <a href="{{ url('/admin/monitoring/real-time') }}" class="menu-link">
                            <span data-i18n="Real-time Status">Real-time Status</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ url('/admin/monitoring/analytics') }}" class="menu-link">
                            <span data-i18n="Analytics">Analytics</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ url('/admin/monitoring/reports') }}" class="menu-link">
                            <span data-i18n="Reports">Reports</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Playground Computer Vision -->
            <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon fas fa-brain"></i>
                    <span data-i18n="Playground Computer Vision">Playground Computer Vision</span>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item">
                        <a href="http://localhost:8001/gemini/dashboard" class="menu-link" target="_blank">
                            <i class="menu-icon fas fa-robot"></i>
                            <span data-i18n="My Vision">My Vision</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon fas fa-microchip"></i>
                            <span data-i18n="Edge Vision">Edge Vision</span>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item">
                                <a href="{{ url('/admin/dashboard/live-camera') }}" class="menu-link">
                                    <i class="menu-icon fas fa-video"></i>
                                    <span data-i18n="Live Camera">Live Camera</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a href="{{ url('/admin/dashboard/image-upload') }}" class="menu-link">
                                    <i class="menu-icon fas fa-upload"></i>
                                    <span data-i18n="Upload & Process">Upload & Process</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a href="{{ url('/admin/dashboard/engine-config') }}" class="menu-link">
                                    <i class="menu-icon fas fa-cogs"></i>
                                    <span data-i18n="Engine Configuration">Engine Configuration</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>

            <!-- Settings -->
            <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon fas fa-cogs"></i>
                    <span data-i18n="Settings">Settings</span>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item">
                        <a href="{{ url('/admin/settings/system') }}" class="menu-link">
                            <span data-i18n="System Settings">System Settings</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ url('/admin/settings/users') }}" class="menu-link">
                            <span data-i18n="User Management">User Management</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</div>
<!-- / Horizontal Menu -->
