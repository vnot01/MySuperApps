<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Timezone Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Devices</dt>
                                    <dd class="text-lg font-medium text-gray-900" id="total-devices">-</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Active Devices</dt>
                                    <dd class="text-lg font-medium text-gray-900" id="active-devices">-</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Syncs Today</dt>
                                    <dd class="text-lg font-medium text-gray-900" id="syncs-today">-</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Unique Timezones</dt>
                                    <dd class="text-lg font-medium text-gray-900" id="unique-timezones">-</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Device Timezone Widget -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Device Timezone Status</h3>
                    <div id="timezone-widget" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Timezone widgets will be loaded here -->
                    </div>
                </div>
            </div>

            <!-- Recent Sync Activity -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Recent Sync Activity</h3>
                        <button id="refresh-btn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Refresh
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Device ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timezone</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sync Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="sync-activity-table" class="bg-white divide-y divide-gray-200">
                                <!-- Sync activity will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Timezone Widget Template -->
    <template id="timezone-widget-template">
        <div class="border rounded-lg p-4 bg-gray-50">
            <div class="flex justify-between items-start mb-2">
                <h4 class="font-medium text-gray-900" data-device-id></h4>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800" data-sync-status>
                    Active
                </span>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Local Time:</span>
                    <span class="text-sm font-medium" data-local-time></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Timezone:</span>
                    <span class="text-sm font-medium" data-timezone></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Location:</span>
                    <span class="text-sm font-medium" data-location></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Last Sync:</span>
                    <span class="text-sm font-medium" data-last-sync></span>
                </div>
            </div>
            <div class="mt-3">
                <button class="w-full bg-blue-500 hover:bg-blue-700 text-white text-sm font-bold py-2 px-4 rounded" data-sync-btn>
                    Sync Now
                </button>
            </div>
        </div>
    </template>

    @push('scripts')
    <script>
        class TimezoneDashboard {
            constructor() {
                this.init();
            }

            init() {
                this.loadDashboardData();
                this.setupEventListeners();
                this.startAutoRefresh();
            }

            setupEventListeners() {
                document.getElementById('refresh-btn').addEventListener('click', () => {
                    this.loadDashboardData();
                });
            }

            startAutoRefresh() {
                // Refresh every 30 seconds
                setInterval(() => {
                    this.loadDashboardData();
                }, 30000);
            }

            async loadDashboardData() {
                try {
                    const response = await fetch('/admin/timezone/dashboard-data');
                    const result = await response.json();

                    if (result.success) {
                        this.updateStatistics(result.data.statistics);
                        this.updateTimezoneWidgets(result.data.devices);
                        this.updateSyncActivity(result.data.recent_syncs);
                    }
                } catch (error) {
                    console.error('Failed to load dashboard data:', error);
                }
            }

            updateStatistics(stats) {
                document.getElementById('total-devices').textContent = stats.total_devices;
                document.getElementById('active-devices').textContent = stats.active_devices;
                document.getElementById('syncs-today').textContent = stats.total_syncs_today;
                document.getElementById('unique-timezones').textContent = stats.unique_timezones;
            }

            updateTimezoneWidgets(devices) {
                const container = document.getElementById('timezone-widget');
                const template = document.getElementById('timezone-widget-template');
                
                container.innerHTML = '';

                devices.forEach(device => {
                    const widget = template.content.cloneNode(true);
                    
                    // Update widget data
                    widget.querySelector('[data-device-id]').textContent = device.device_id;
                    widget.querySelector('[data-sync-status]').textContent = device.sync_status;
                    widget.querySelector('[data-timezone]').textContent = device.current_timezone;
                    widget.querySelector('[data-location]').textContent = `${device.city || 'Unknown'}, ${device.country || 'Unknown'}`;
                    widget.querySelector('[data-last-sync]').textContent = this.formatDateTime(device.last_sync);
                    
                    // Calculate local time
                    const localTime = this.calculateLocalTime(device.current_timezone);
                    widget.querySelector('[data-local-time]').textContent = localTime;
                    
                    // Setup sync button
                    const syncBtn = widget.querySelector('[data-sync-btn]');
                    syncBtn.addEventListener('click', () => {
                        this.triggerManualSync(device.device_id);
                    });
                    
                    container.appendChild(widget);
                });
            }

            updateSyncActivity(syncs) {
                const tbody = document.getElementById('sync-activity-table');
                tbody.innerHTML = '';

                syncs.forEach(sync => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${sync.device_id}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${sync.timezone}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${sync.city || 'Unknown'}, ${sync.country || 'Unknown'}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${sync.sync_method === 'automatic' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800'}">
                                ${sync.sync_method}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${this.formatDateTime(sync.sync_timestamp)}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button class="text-blue-600 hover:text-blue-900" onclick="timezoneDashboard.triggerManualSync('${sync.device_id}')">
                                Sync
                            </button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            }

            async triggerManualSync(deviceId) {
                try {
                    const response = await fetch('/admin/timezone/manual-sync', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            device_id: deviceId
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.showNotification('Manual sync triggered successfully!', 'success');
                        this.loadDashboardData(); // Refresh data
                    } else {
                        this.showNotification('Failed to trigger manual sync', 'error');
                    }
                } catch (error) {
                    console.error('Failed to trigger manual sync:', error);
                    this.showNotification('Failed to trigger manual sync', 'error');
                }
            }

            calculateLocalTime(timezone) {
                try {
                    const now = new Date();
                    const localTime = new Date(now.toLocaleString("en-US", {timeZone: timezone}));
                    return localTime.toLocaleString();
                } catch (error) {
                    return 'Unknown';
                }
            }

            formatDateTime(dateString) {
                if (!dateString) return 'Never';
                const date = new Date(dateString);
                return date.toLocaleString();
            }

            showNotification(message, type = 'info') {
                // Simple notification - you can enhance this with a proper notification library
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 p-4 rounded-md text-white z-50 ${type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500'}`;
                notification.textContent = message;
                
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 3000);
            }
        }

        // Initialize dashboard when page loads
        let timezoneDashboard;
        document.addEventListener('DOMContentLoaded', () => {
            timezoneDashboard = new TimezoneDashboard();
        });
    </script>
    @endpush
</x-app-layout>
