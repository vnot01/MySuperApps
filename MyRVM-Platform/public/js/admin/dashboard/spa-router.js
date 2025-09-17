/**
 * SPA Router for MyRVM Platform Dashboard
 * Handles client-side routing and view management
 */

class SPARouter {
    constructor() {
        this.routes = new Map();
        this.currentRoute = null;
        this.currentView = null;
        this.breadcrumbs = [];
        this.history = [];
        this.maxHistorySize = 10;
        
        this.init();
    }

    /**
     * Initialize the SPA Router
     */
    init() {
        // Define routes
        this.defineRoutes();
        
        // Listen for browser navigation
        window.addEventListener('popstate', (e) => {
            this.handleRouteChange(e.state?.route || '/dashboard');
        });
        
        // Listen for custom navigation events
        document.addEventListener('spa-navigate', (e) => {
            this.navigate(e.detail.route, e.detail.data);
        });
        
        // Initial route
        const currentPath = window.location.pathname;
        console.log('SPA Router initializing with path:', currentPath);
        
        // Handle initial route
        if (currentPath === '/' || currentPath === '/dashboard' || currentPath === '/admin/rvm-dashboard') {
            this.handleRouteChange('/dashboard');
        } else if (currentPath.startsWith('/edge-vision/')) {
            // Handle edge vision routes
            this.handleRouteChange(currentPath);
        } else if (currentPath.startsWith('/dashboard/')) {
            // Handle dashboard sub-routes
            this.handleRouteChange(currentPath);
        } else {
            // Default to dashboard for unknown routes
            console.log('Unknown route, defaulting to dashboard:', currentPath);
            this.handleRouteChange('/dashboard');
        }
    }

    /**
     * Define all available routes
     */
    defineRoutes() {
        // Dashboard routes
        this.routes.set('/dashboard', {
            title: 'RVM Dashboard',
            component: 'dashboard-main',
            breadcrumb: 'Dashboard',
            permissions: ['admin', 'operator', 'viewer']
        });

        this.routes.set('/', {
            title: 'RVM Dashboard',
            component: 'dashboard-main',
            breadcrumb: 'Dashboard',
            permissions: ['admin', 'operator', 'viewer']
        });

        this.routes.set('/dashboard/remote-access/:id', {
            title: 'Remote Access',
            component: 'remote-access',
            breadcrumb: 'Remote Access',
            permissions: ['admin', 'operator']
        });

        this.routes.set('/dashboard/status-update/:id', {
            title: 'Update RVM Status',
            component: 'status-update',
            breadcrumb: 'Status Update',
            permissions: ['admin', 'operator']
        });

        // Edge Vision routes
        this.routes.set('/edge-vision/live-camera', {
            title: 'Live Camera - Jetson Orin',
            component: 'live-camera',
            breadcrumb: 'Live Camera',
            permissions: ['admin', 'operator', 'viewer']
        });

        this.routes.set('/edge-vision/image-upload', {
            title: 'Upload & Process Image',
            component: 'image-upload',
            breadcrumb: 'Image Upload',
            permissions: ['admin', 'operator']
        });

        this.routes.set('/edge-vision/engine-config', {
            title: 'Engine Configuration',
            component: 'engine-config',
            breadcrumb: 'Engine Config',
            permissions: ['admin']
        });

        // RVM Management routes
        this.routes.set('/rvm-management', {
            title: 'RVM Management',
            component: 'rvm-management',
            breadcrumb: 'RVM Management',
            permissions: ['admin', 'operator', 'viewer']
        });

        this.routes.set('/rvm-management/add', {
            title: 'Add New RVM',
            component: 'rvm-add',
            breadcrumb: 'Add RVM',
            permissions: ['admin']
        });

        this.routes.set('/rvm-management/edit/:id', {
            title: 'Edit RVM',
            component: 'rvm-edit',
            breadcrumb: 'Edit RVM',
            permissions: ['admin', 'operator']
        });

        // Monitoring routes
        this.routes.set('/monitoring/real-time', {
            title: 'Real-time Monitoring',
            component: 'real-time-monitoring',
            breadcrumb: 'Real-time',
            permissions: ['admin', 'operator', 'viewer']
        });

        this.routes.set('/monitoring/analytics', {
            title: 'Analytics Dashboard',
            component: 'analytics',
            breadcrumb: 'Analytics',
            permissions: ['admin', 'operator', 'viewer']
        });

        this.routes.set('/monitoring/reports', {
            title: 'Reports',
            component: 'reports',
            breadcrumb: 'Reports',
            permissions: ['admin', 'operator']
        });

        // Settings routes
        this.routes.set('/settings/system', {
            title: 'System Settings',
            component: 'system-settings',
            breadcrumb: 'System Settings',
            permissions: ['admin']
        });

        this.routes.set('/settings/users', {
            title: 'User Management',
            component: 'user-management',
            breadcrumb: 'User Management',
            permissions: ['admin']
        });
    }

    /**
     * Find parameterized route that matches the given path
     * @param {string} path - The path to match
     * @returns {object|null} - Route config or null if not found
     */
    findParameterizedRoute(path) {
        for (const [routePattern, config] of this.routes) {
            if (routePattern.includes(':')) {
                // Convert route pattern to regex
                const regexPattern = routePattern
                    .replace(/:\w+/g, '([^/]+)') // Replace :param with regex group
                    .replace(/\//g, '\\/'); // Escape forward slashes
                
                const regex = new RegExp(`^${regexPattern}$`);
                if (regex.test(path)) {
                    // Extract parameters
                    const matches = path.match(regex);
                    const params = {};
                    const paramNames = routePattern.match(/:\w+/g) || [];
                    
                    paramNames.forEach((paramName, index) => {
                        const key = paramName.substring(1); // Remove ':'
                        params[key] = matches[index + 1];
                    });
                    
                    // Return config with parameters
                    return {
                        ...config,
                        params: params
                    };
                }
            }
        }
        return null;
    }

    /**
     * Navigate to a specific route
     * @param {string} route - The route to navigate to
     * @param {object} data - Optional data to pass to the view
     */
    navigate(route, data = {}) {
        // Check permissions
        if (!this.checkPermissions(route)) {
            this.showNotification('Access denied. Insufficient permissions.', 'error');
            return;
        }

        // Add to history
        this.addToHistory(this.currentRoute);
        
        // Update URL
        window.history.pushState({ route, data }, '', route);
        
        // Handle route change
        this.handleRouteChange(route, data);
    }

    /**
     * Handle route changes
     * @param {string} route - The route to handle
     * @param {object} data - Optional data
     */
    async handleRouteChange(route, data = {}) {
        try {
            // Show loading
            this.showLoading();
            
            // Get route config
            let routeConfig = this.routes.get(route);
            
            // If no exact match, try to find parameterized route
            if (!routeConfig) {
                routeConfig = this.findParameterizedRoute(route);
            }
            
            if (!routeConfig) {
                // If no route config found, try to load dashboard-main as default
                if (route === '/dashboard' || route === '/') {
                    await this.loadComponent('dashboard-main', data);
                    this.currentRoute = route;
                    this.hideLoading();
                    return;
                }
                this.show404();
                return;
            }

            // Update current route
            this.currentRoute = route;
            
            // Update breadcrumbs
            this.updateBreadcrumbs(route, routeConfig);
            
            // Load and render component
            await this.loadComponent(routeConfig.component, data);
            
            // Update page title
            document.title = `${routeConfig.title} - MyRVM Platform`;
            
            // Update active menu item
            this.updateActiveMenuItem(route);
            
            // Hide loading
            this.hideLoading();
            
            // Emit route change event
            this.emitRouteChange(route, routeConfig);
            
        } catch (error) {
            console.error('Error handling route change:', error);
            this.showError('Failed to load page');
        }
    }

    /**
     * Load and render a component
     * @param {string} componentName - Name of the component to load
     * @param {object} data - Data to pass to component
     */
    async loadComponent(componentName, data = {}) {
        // Use SPA content container if available, otherwise fallback to main-content
        let container = document.getElementById('spa-content-container');
        if (!container) {
            container = document.getElementById('main-content');
        }
        if (!container) {
            throw new Error('Content container not found');
        }

        // Hide current view
        if (this.currentView) {
            this.currentView.style.display = 'none';
        }

        // Check if component already exists
        let component = document.getElementById(componentName);
        
        if (!component) {
            // Create new component
            component = await this.createComponent(componentName, data);
            container.appendChild(component);
        } else {
            // Update existing component
            await this.updateComponent(component, data);
        }

        // Show component
        component.style.display = 'block';
        this.currentView = component;

        // Initialize component
        this.initializeComponent(component, data);
    }

    /**
     * Create a new component
     * @param {string} componentName - Name of the component
     * @param {object} data - Data for the component
     */
    async createComponent(componentName, data) {
        const component = document.createElement('div');
        component.id = componentName;
        component.className = 'spa-view';
        component.style.display = 'none';

        // Load component template
        const template = await this.loadComponentTemplate(componentName);
        component.innerHTML = template;

        return component;
    }

    /**
     * Load component template
     * @param {string} componentName - Name of the component
     */
    async loadComponentTemplate(componentName) {
        try {
            const response = await fetch(`/js/admin/dashboard/components/${componentName}.html`);
            if (!response.ok) {
                throw new Error(`Failed to load component: ${componentName}`);
            }
            return await response.text();
        } catch (error) {
            console.error('Error loading component template:', error);
            return this.getDefaultTemplate(componentName);
        }
    }

    /**
     * Get default template for a component
     * @param {string} componentName - Name of the component
     */
    getDefaultTemplate(componentName) {
        return `
            <div class="container-xxl">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">${this.capitalizeFirst(componentName.replace('-', ' '))}</h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Component "${componentName}" is loading...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Update component with new data
     * @param {HTMLElement} component - Component element
     * @param {object} data - New data
     */
    async updateComponent(component, data) {
        // Update component data attributes
        Object.keys(data).forEach(key => {
            component.setAttribute(`data-${key}`, data[key]);
        });

        // Trigger component update event
        component.dispatchEvent(new CustomEvent('component-update', {
            detail: data
        }));
    }

    /**
     * Initialize component
     * @param {HTMLElement} component - Component element
     * @param {object} data - Component data
     */
    initializeComponent(component, data) {
        // Trigger component init event
        component.dispatchEvent(new CustomEvent('component-init', {
            detail: data
        }));

        // Initialize dashboard data if this is dashboard-main component
        if (component.id === 'dashboard-main') {
            console.log('Initializing dashboard data for SPA component...');
            // Only call loadMonitoringData to avoid double initialization
            if (typeof loadMonitoringData === 'function') {
                setTimeout(() => {
                    loadMonitoringData();
                }, 100);
            }
            // Don't call initializeDashboard to prevent conflicts
        }

        // Add animation
        component.style.opacity = '0';
        component.style.transform = 'translateY(20px)';
        
        requestAnimationFrame(() => {
            component.style.transition = 'all 0.3s ease-out';
            component.style.opacity = '1';
            component.style.transform = 'translateY(0)';
        });
    }

    /**
     * Update breadcrumbs
     * @param {string} route - Current route
     * @param {object} routeConfig - Route configuration
     */
    updateBreadcrumbs(route, routeConfig) {
        this.breadcrumbs = [];
        
        // Add home
        this.breadcrumbs.push({
            title: 'Dashboard',
            route: '/dashboard',
            active: false
        });

        // Parse route segments
        const segments = route.split('/').filter(segment => segment);
        
        let currentPath = '';
        segments.forEach((segment, index) => {
            currentPath += `/${segment}`;
            
            // Check if this is a parameter
            if (segment.startsWith(':')) {
                return; // Skip parameters
            }
            
            // Get route config for this path
            const config = this.routes.get(currentPath);
            if (config) {
                this.breadcrumbs.push({
                    title: config.breadcrumb,
                    route: currentPath,
                    active: index === segments.length - 1
                });
            }
        });

        // Update breadcrumb UI
        this.renderBreadcrumbs();
    }

    /**
     * Render breadcrumbs in the UI
     */
    renderBreadcrumbs() {
        const breadcrumbContainer = document.getElementById('breadcrumb-container');
        if (!breadcrumbContainer) return;

        const breadcrumbHTML = this.breadcrumbs.map((crumb, index) => {
            if (crumb.active) {
                return `<li class="breadcrumb-item active" aria-current="page">${crumb.title}</li>`;
            } else {
                return `<li class="breadcrumb-item"><a href="#" onclick="spaRouter.navigate('${crumb.route}'); return false;">${crumb.title}</a></li>`;
            }
        }).join('');

        breadcrumbContainer.innerHTML = `
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="#" onclick="spaRouter.navigate('/dashboard'); return false;">
                            <i class="fas fa-home"></i>
                        </a>
                    </li>
                    ${breadcrumbHTML}
                </ol>
            </nav>
        `;
    }

    /**
     * Update active menu item
     * @param {string} route - Current route
     */
    updateActiveMenuItem(route) {
        // Remove active class from all menu items
        document.querySelectorAll('.menu-item').forEach(item => {
            item.classList.remove('active');
        });

        // Add active class to current menu item
        const menuItem = document.querySelector(`[data-route="${route}"]`);
        if (menuItem) {
            menuItem.classList.add('active');
        }
    }

    /**
     * Check user permissions for a route
     * @param {string} route - Route to check
     */
    checkPermissions(route) {
        const routeConfig = this.routes.get(route);
        if (!routeConfig) return false;

        // Get current user role (this would come from your auth system)
        const userRole = this.getCurrentUserRole();
        
        return routeConfig.permissions.includes(userRole);
    }

    /**
     * Get current user role
     */
    getCurrentUserRole() {
        // This would integrate with your authentication system
        // For now, return 'admin' as default
        return window.userRole || 'admin';
    }

    /**
     * Add route to history
     * @param {string} route - Route to add
     */
    addToHistory(route) {
        if (route && route !== this.currentRoute) {
            this.history.unshift(route);
            if (this.history.length > this.maxHistorySize) {
                this.history.pop();
            }
        }
    }

    /**
     * Go back in history
     */
    goBack() {
        if (this.history.length > 0) {
            const previousRoute = this.history.shift();
            this.navigate(previousRoute);
        } else {
            this.navigate('/dashboard');
        }
    }

    /**
     * Show loading state
     */
    showLoading() {
        const loadingOverlay = document.getElementById('loadingOverlay');
        if (loadingOverlay) {
            loadingOverlay.style.display = 'flex';
            loadingOverlay.style.opacity = '1';
            loadingOverlay.style.visibility = 'visible';
        }
    }

    /**
     * Hide loading state
     */
    hideLoading() {
        const loadingOverlay = document.getElementById('loadingOverlay');
        if (loadingOverlay) {
            loadingOverlay.style.opacity = '0';
            loadingOverlay.style.visibility = 'hidden';
            setTimeout(() => {
                if (loadingOverlay.style.opacity === '0') {
                    loadingOverlay.style.display = 'none';
                }
            }, 300);
        }
    }

    /**
     * Show 404 page
     */
    show404() {
        // Use SPA content container if available, otherwise fallback to main-content
        let container = document.getElementById('spa-content-container');
        if (!container) {
            container = document.getElementById('main-content');
        }
        if (container) {
            container.innerHTML = `
                <div class="container-xxl">
                    <div class="row">
                        <div class="col-12">
                            <div class="text-center py-5">
                                <h1 class="display-1 text-muted">404</h1>
                                <h2 class="mb-4">Page Not Found</h2>
                                <p class="text-muted mb-4">The page you're looking for doesn't exist.</p>
                                <button class="btn btn-primary" onclick="spaRouter.navigate('/dashboard')">
                                    <i class="fas fa-home me-2"></i>Go to Dashboard
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
    }

    /**
     * Show error message
     * @param {string} message - Error message
     */
    showError(message) {
        this.showNotification(message, 'error');
    }

    /**
     * Show notification
     * @param {string} message - Notification message
     * @param {string} type - Notification type
     */
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        const icons = { 
            success: 'fas fa-check-circle', 
            error: 'fas fa-times-circle', 
            warning: 'fas fa-exclamation-triangle', 
            info: 'fas fa-info-circle' 
        };
        const icon = icons[type] || icons.info;
        
        notification.innerHTML = `<div class="d-flex align-items-center"><i class="${icon} me-2"></i> ${message}</div>`;
        document.body.appendChild(notification);
        
        setTimeout(() => notification.classList.add('show'), 10);
        setTimeout(() => {
            notification.classList.remove('show');
            notification.addEventListener('transitionend', () => notification.remove());
        }, 3000);
    }

    /**
     * Emit route change event
     * @param {string} route - Current route
     * @param {object} routeConfig - Route configuration
     */
    emitRouteChange(route, routeConfig) {
        document.dispatchEvent(new CustomEvent('route-changed', {
            detail: { route, routeConfig }
        }));
    }

    /**
     * Utility: Capitalize first letter
     * @param {string} str - String to capitalize
     */
    capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
}

// Initialize SPA Router
let spaRouter;
document.addEventListener('DOMContentLoaded', () => {
    spaRouter = new SPARouter();
    window.spaRouter = spaRouter; // Make globally available
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SPARouter;
}
