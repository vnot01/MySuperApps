<!-- Sidebar Menu -->
<div class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ url('/admin/dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <svg width="25" viewBox="0 0 25 42" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <defs>
                        <path d="M13.7918663,0.358365126 L3.39788168,7.44174259 C0.566865006,9.69408886 -0.379795268,12.4788597 0.557900856,15.7960551 C0.68998853,16.2305145 1.09562888,17.7872135 3.12357076,19.2293357 C3.8146334,19.7207684 5.32369333,20.3834223 7.65075054,21.2172976 L7.59773219,21.2525164 L2.63468769,24.5493413 C0.445452254,26.3002124 0.0884951797,28.5083815 1.56381646,31.1738486 C2.83770406,32.8170431 5.20850219,33.2640127 7.09180128,32.5391577 C8.347334,32.0559211 11.4559176,30.0014999 16.4175519,26.3747182 C18.0338572,24.4997857 18.6973423,22.4544883 18.4080071,20.2388261 C17.963753,17.5346866 16.1776345,15.5799961 13.0496516,14.3747546 L10.9194936,13.4715819 L18.6192054,7.984237 L13.7918663,0.358365126 Z" id="path-1"></path>
                        <path d="M5.47320593,6.00457225 C4.05321814,8.216144 4.36334763,10.0722806 6.40359441,11.5729822 C8.61520715,12.571656 10.0999176,13.2171421 10.8577257,13.5094407 L15.5088241,14.433041 L18.6192054,7.984237 C15.5364148,3.11535317 13.9273018,0.573395879 13.7918663,0.358365126 C13.5790555,0.511491653 10.8061687,2.3935607 5.47320593,6.00457225 Z" id="path-3"></path>
                        <path d="M7.50063644,21.2294429 L12.2034064,11.2294429 C12.2034064,11.2294429 12.2034064,11.2294429 12.2034064,11.2294429 C12.2034064,11.2294429 12.2034064,11.2294429 12.2034064,11.2294429 L7.50063644,21.2294429 Z" id="path-4"></path>
                        <path d="M12.2034064,11.2294429 L16.9061764,21.2294429 C16.9061764,21.2294429 16.9061764,21.2294429 16.9061764,21.2294429 C16.9061764,21.2294429 16.9061764,21.2294429 16.9061764,21.2294429 L12.2034064,11.2294429 Z" id="path-5"></path>
                    </defs>
                    <g id="g-app-brand" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                        <g id="Brand-Logo" transform="translate(-27.000000, -15.000000)">
                            <g id="Icon" transform="translate(27.000000, 15.000000)">
                                <g id="Mask" transform="translate(0.000000, 8.000000)">
                                    <mask id="mask-2" fill="white">
                                        <use xlink:href="#path-1"></use>
                                    </mask>
                                    <use fill="#696cff" xlink:href="#path-1"></use>
                                    <g id="Path-3" mask="url(#mask-2)">
                                        <use fill="#696cff" xlink:href="#path-3"></use>
                                        <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-3"></use>
                                    </g>
                                    <g id="Path-4" mask="url(#mask-2)">
                                        <use fill="#696cff" xlink:href="#path-4"></use>
                                        <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-4"></use>
                                    </g>
                                </g>
                                <g id="Triangle" transform="translate(19.000000, 11.000000) rotate(-300.000000) translate(-19.000000, -11.000000) ">
                                    <use fill="#696cff" xlink:href="#path-5"></use>
                                    <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-5"></use>
                                </g>
                            </g>
                        </g>
                    </g>
                </svg>
            </span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2">MyRVM</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="fas fa-chevron-left fa-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
            <a href="{{ url('/admin/dashboard') }}" class="menu-link">
                <i class="menu-icon fas fa-tachometer-alt"></i>
                <div data-i18n="Dashboard">Dashboard</div>
            </a>
        </li>

        <!-- RVM Management -->
        <li class="menu-item {{ request()->is('admin/rvm*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon fas fa-recycle"></i>
                <div data-i18n="RVM Management">RVM Management</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->is('admin/rvm') && !request()->is('admin/rvm/maintenance') ? 'active' : '' }}">
                    <a href="{{ route('admin.rvm.index') }}" class="menu-link">
                        <div data-i18n="All RVMs">All RVMs</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" class="menu-link" data-bs-toggle="modal" data-bs-target="#addRvmModal">
                        <div data-i18n="Add New RVM">Add New RVM</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->is('admin/rvm/maintenance') ? 'active' : '' }}">
                    <a href="{{ route('admin.rvm.maintenance') }}" class="menu-link">
                        <div data-i18n="Maintenance">Maintenance</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Monitoring -->
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon fas fa-eye"></i>
                <div data-i18n="Monitoring">Monitoring</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item">
                    <a href="{{ url('/admin/monitoring/real-time') }}" class="menu-link">
                        <div data-i18n="Real-time Status">Real-time Status</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="{{ url('/admin/monitoring/analytics') }}" class="menu-link">
                        <div data-i18n="Analytics">Analytics</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="{{ url('/admin/monitoring/reports') }}" class="menu-link">
                        <div data-i18n="Reports">Reports</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Playground Computer Vision -->
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon fas fa-brain"></i>
                <div data-i18n="Playground Computer Vision">Playground Computer Vision</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item">
                    <a href="http://localhost:8001/gemini/dashboard" class="menu-link" target="_blank">
                        <i class="menu-icon fas fa-robot"></i>
                        <div data-i18n="My Vision">My Vision</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon fas fa-microchip"></i>
                        <div data-i18n="Edge Vision">Edge Vision</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item">
                            <a href="{{ url('/admin/dashboard/live-camera') }}" class="menu-link">
                                <i class="menu-icon fas fa-video"></i>
                                <div data-i18n="Live Camera">Live Camera</div>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{ url('/admin/dashboard/image-upload') }}" class="menu-link">
                                <i class="menu-icon fas fa-upload"></i>
                                <div data-i18n="Upload & Process">Upload & Process</div>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{ url('/admin/dashboard/engine-config') }}" class="menu-link">
                                <i class="menu-icon fas fa-cogs"></i>
                                <div data-i18n="Engine Configuration">Engine Configuration</div>
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
                <div data-i18n="Settings">Settings</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item">
                    <a href="{{ url('/admin/settings/system') }}" class="menu-link">
                        <div data-i18n="System Settings">System Settings</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="{{ url('/admin/settings/users') }}" class="menu-link">
                        <div data-i18n="User Management">User Management</div>
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</div>
<!-- / Sidebar Menu -->
