<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Modern Header with Glass Effect -->
    <header class="bg-white/80 backdrop-blur-md shadow-lg border-b border-gray-200/50 sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div class="flex items-center space-x-4">
            <button 
              @click="goBack"
              class="p-3 text-gray-600 hover:text-gray-900 hover:bg-gray-100/80 rounded-xl transition-all duration-200 group"
            >
              <i class="fas fa-arrow-left group-hover:scale-110 transition-transform"></i>
            </button>
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 bg-gradient-to-r from-orange-500 via-red-500 to-pink-500 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-wrench text-white text-lg"></i>
              </div>
              <div>
                <h1 class="text-2xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">Maintenance Mode</h1>
                <p class="text-sm text-gray-500">Advanced RVM Management & Monitoring</p>
              </div>
            </div>
          </div>
          <div class="flex items-center space-x-3">
            <button 
              @click="refreshData"
              :disabled="isRefreshing"
              class="px-6 py-3 text-sm bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 disabled:opacity-50 text-white rounded-xl transition-all duration-200 flex items-center shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
            >
              <i v-if="isRefreshing" class="fas fa-spinner fa-spin mr-2"></i>
              <i v-else class="fas fa-sync-alt mr-2"></i>
              {{ isRefreshing ? 'Refreshing...' : 'Refresh Data' }}
            </button>
            <button 
              @click="endMaintenance"
              class="px-6 py-3 text-sm bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-xl transition-all duration-200 flex items-center shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
            >
              <i class="fas fa-check mr-2"></i>
              End Maintenance
            </button>
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
      <!-- Modern RVM Information Card -->
      <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-gray-200/50 p-8 relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 bg-gradient-to-br from-blue-50/50 to-purple-50/50"></div>
        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-100/30 to-purple-100/30 rounded-full -translate-y-16 translate-x-16"></div>
        
        <div class="relative z-10">
          <div class="flex items-center justify-between mb-8">
            <div class="flex items-center space-x-4">
              <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                <i class="fas fa-robot text-white text-2xl"></i>
              </div>
              <div>
                <h2 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">{{ rvm.name }}</h2>
                <p class="text-gray-600 text-lg">{{ rvm.location }}</p>
                <p class="text-sm text-gray-500">RVM ID: #{{ rvm.id }}</p>
              </div>
            </div>
            <div class="flex flex-col items-end space-y-2">
              <span 
                :class="[
                  'px-4 py-2 rounded-full text-sm font-semibold shadow-lg',
                  rvm.status === 'active' ? 'bg-gradient-to-r from-green-100 to-emerald-100 text-green-800 border border-green-200' : 'bg-gradient-to-r from-yellow-100 to-amber-100 text-yellow-800 border border-yellow-200'
                ]"
              >
                <i :class="rvm.status === 'active' ? 'fas fa-check-circle mr-2' : 'fas fa-tools mr-2'"></i>
                {{ rvm.status === 'active' ? 'Active' : 'Under Maintenance' }}
              </span>
              <div class="text-right">
                <p class="text-sm font-medium text-gray-600">Capacity</p>
                <div 
                  class="relative w-32 bg-gray-200 rounded-full h-3 mt-2 cursor-pointer"
                  @mouseenter="showTooltip = true"
                  @mouseleave="showTooltip = false"
                >
                  <div 
                    class="h-3 rounded-full transition-all duration-500 ease-out"
                    :class="{
                      'bg-gradient-to-r from-green-400 to-green-500': (rvm.current_load / rvm.capacity) <= 0.5,
                      'bg-gradient-to-r from-yellow-400 to-yellow-500': (rvm.current_load / rvm.capacity) > 0.5 && (rvm.current_load / rvm.capacity) <= 0.8,
                      'bg-gradient-to-r from-red-400 to-red-500': (rvm.current_load / rvm.capacity) > 0.8
                    }"
                    :style="{ width: Math.min(Math.max((rvm.current_load / rvm.capacity) * 100, 0), 100) + '%' }"
                  ></div>
                  
                  <!-- Tooltip -->
                  <div 
                    v-show="showTooltip"
                    class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg shadow-lg whitespace-nowrap z-50"
                    style="opacity: 1;"
                  >
                    <div class="text-center">
                      <div class="font-semibold">{{ Math.round((rvm.current_load / rvm.capacity) * 100) }}% capacity</div>
                    </div>
                    <!-- Arrow -->
                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Modern Info Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100/50 rounded-xl p-6 border border-blue-200/50 hover:shadow-lg transition-all duration-200 group">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                  <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                    <i class="fas fa-map-marker-alt text-white text-lg"></i>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-600">IP Address</p>
                    <p class="text-gray-900 font-mono text-lg">{{ rvm.ip_address }}</p>
                  </div>
                </div>
                <button 
                  @click="openEditIpModal"
                  class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-100 rounded-lg transition-all duration-200 group relative"
                  title="Edit IP Address"
                >
                  <i class="fas fa-edit text-sm"></i>
                  <!-- Tooltip -->
                  <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">
                    Edit IP Address
                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
                  </div>
                </button>
              </div>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-purple-100/50 rounded-xl p-6 border border-purple-200/50 hover:shadow-lg transition-all duration-200 group">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                  <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                    <i class="fas fa-key text-white text-lg"></i>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-600">API Key</p>
                    <p class="text-gray-900 font-mono text-sm">{{ rvm.api_key ? '********' + rvm.api_key.slice(-4) : 'N/A' }}</p>
                  </div>
                </div>
                <div class="flex items-center space-x-2">
                  <button 
                    @click="copyApiKey"
                    class="p-2 text-purple-600 hover:text-purple-800 hover:bg-purple-100 rounded-lg transition-all duration-200 group relative"
                    :title="'Copy API Key: ' + (rvm.api_key || 'N/A')"
                  >
                    <i class="fas fa-copy text-sm"></i>
                    <!-- Tooltip -->
                    <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">
                      Copy API Key
                      <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
                    </div>
                  </button>
                  <button 
                    @click="toggleApiKeyVisibility"
                    class="p-2 text-purple-600 hover:text-purple-800 hover:bg-purple-100 rounded-lg transition-all duration-200 group relative"
                    :title="showFullApiKey ? 'Hide API Key' : 'Show API Key'"
                  >
                    <i :class="showFullApiKey ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
                    <!-- Tooltip -->
                    <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">
                      {{ showFullApiKey ? 'Hide API Key' : 'Show API Key' }}
                      <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
                    </div>
                  </button>
                </div>
              </div>
              
              <!-- Full API Key Display (when visible) -->
              <div v-if="showFullApiKey && rvm.api_key" class="mt-4 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <p class="text-xs font-medium text-purple-700 mb-1">Full API Key:</p>
                    <p class="text-gray-900 font-mono text-sm break-all">{{ rvm.api_key }}</p>
                  </div>
                  <button 
                    @click="copyFullApiKey"
                    class="ml-3 p-2 text-purple-600 hover:text-purple-800 hover:bg-purple-100 rounded-lg transition-all duration-200"
                    title="Copy Full API Key"
                  >
                    <i class="fas fa-copy text-sm"></i>
                  </button>
                </div>
                <div class="mt-2 text-xs text-purple-600">
                  <i class="fas fa-info-circle mr-1"></i>
                  Keep this API key secure and don't share it with unauthorized users
                </div>
              </div>
            </div>

            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100/50 rounded-xl p-6 border border-yellow-200/50 hover:shadow-lg transition-all duration-200 group">
              <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                  <i class="fas fa-clock text-white text-lg"></i>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-600">Last Ping</p>
                  <p class="text-gray-900 text-sm">{{ rvm.last_ping ? new Date(rvm.last_ping).toLocaleString() : 'Never' }}</p>
                  <div class="mt-1 flex items-center space-x-2">
                    <div class="flex items-center space-x-1">
                      <div 
                        :class="[
                          'w-2 h-2 rounded-full',
                          isRefreshing ? 'bg-green-500 animate-pulse' : 'bg-gray-400'
                        ]"
                      ></div>
                      <span class="text-xs text-gray-500">
                        Last refresh: {{ lastRefreshAgo }}
                      </span>
                    </div>
                    <div v-if="isRefreshing" class="flex items-center space-x-1">
                      <i class="fas fa-sync-alt fa-spin text-xs text-green-600"></i>
                      <span class="text-xs text-green-600 font-medium">Refreshing...</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div 
              :class="[
                'bg-gradient-to-br from-green-50 to-green-100/50 rounded-xl p-6 border border-green-200/50 hover:shadow-lg transition-all duration-200 group',
                healthStatus && healthStatus.status === 'healthy' ? 'health-pulse-container' : ''
              ]"
            >
              <div class="flex items-center space-x-4">
                <div 
                  :class="[
                    'w-12 h-12 bg-gradient-to-r from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform',
                    healthStatus && healthStatus.status === 'healthy' ? 'health-pulse-icon' : ''
                  ]"
                >
                  <i 
                    :class="[
                      'fas fa-heartbeat text-white text-lg',
                      healthStatus && healthStatus.status === 'healthy' ? 'health-pulse-heartbeat' : ''
                    ]"
                  ></i>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-600">Health Status</p>
                  <p 
                    :class="[
                      'font-semibold text-lg',
                      healthStatus && healthStatus.status === 'healthy' ? 'text-green-600' : 'text-red-600'
                    ]"
                  >
                    <i 
                      :class="[
                        healthStatus && healthStatus.status === 'healthy' ? 'fas fa-check-circle mr-2' : 'fas fa-times-circle mr-2',
                        isRefreshing ? 'status-pulse' : ''
                      ]"
                    ></i>
                    {{ healthStatus ? healthStatus.status : 'Unknown' }}
                  </p>
                </div>
              </div>
            </div>

            <div 
              :class="[
                'bg-gradient-to-br from-red-50 to-red-100/50 rounded-xl p-6 border border-red-200/50 hover:shadow-lg transition-all duration-200 group',
                apiStatus && apiStatus.api_status === 'online' ? 'api-pulse-container' : ''
              ]"
            >
              <div class="flex items-center space-x-4">
                <div 
                  :class="[
                    'w-12 h-12 bg-gradient-to-r from-red-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform',
                    apiStatus && apiStatus.api_status === 'online' ? 'api-pulse-icon' : ''
                  ]"
                >
                  <i 
                    :class="[
                      'fas fa-wifi text-white text-lg',
                      apiStatus && apiStatus.api_status === 'online' ? 'api-pulse-wifi' : ''
                    ]"
                  ></i>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-600">API Status</p>
                  <p 
                    :class="[
                      'font-semibold text-lg',
                      apiStatus && apiStatus.api_status === 'online' ? 'text-green-600' : 'text-red-600'
                    ]"
                  >
                    <i 
                      :class="[
                        apiStatus && apiStatus.api_status === 'online' ? 'fas fa-check-circle mr-2' : 'fas fa-times-circle mr-2',
                        isRefreshing ? 'status-pulse' : ''
                      ]"
                    ></i>
                    {{ apiStatus ? apiStatus.api_status : 'Unknown' }}
                  </p>
                </div>
              </div>
            </div>

            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100/50 rounded-xl p-6 border border-indigo-200/50 hover:shadow-lg transition-all duration-200 group">
              <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                  <i class="fas fa-microchip text-white text-lg"></i>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-600">Hardware</p>
                  <p class="text-gray-900 text-sm">
                    {{ hardwareInfo && hardwareInfo.hardware_info ? 
                      `${hardwareInfo.hardware_info.jetson_info.model}` : 'N/A' }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Analytics and Monitoring Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- RVM Analytics Card -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-gray-200/50 p-8">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">RVM Analytics</h3>
            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
              <i class="fas fa-chart-line text-white text-lg"></i>
            </div>
          </div>
          
          <div v-if="analytics" class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
              <div class="bg-gradient-to-br from-blue-50 to-blue-100/50 rounded-xl p-6 border border-blue-200/50">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm font-medium text-gray-600">Total Detections</p>
                    <p class="text-3xl font-bold text-gray-900">{{ analytics.total_detections || 0 }}</p>
                  </div>
                  <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center">
                    <i class="fas fa-eye text-white"></i>
                  </div>
                </div>
              </div>
              
              <div class="bg-gradient-to-br from-green-50 to-green-100/50 rounded-xl p-6 border border-green-200/50">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm font-medium text-gray-600">Success Rate</p>
                    <p class="text-3xl font-bold text-gray-900">{{ analytics.success_rate || 0 }}%</p>
                  </div>
                  <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-white"></i>
                  </div>
                </div>
              </div>
              
              <div class="bg-gradient-to-br from-yellow-50 to-yellow-100/50 rounded-xl p-6 border border-yellow-200/50">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm font-medium text-gray-600">Avg Processing</p>
                    <p class="text-3xl font-bold text-gray-900">{{ analytics.average_processing_time ? analytics.average_processing_time.toFixed(2) + 's' : 'N/A' }}</p>
                  </div>
                  <div class="w-12 h-12 bg-yellow-500 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clock text-white"></i>
                  </div>
                </div>
              </div>
              
              <div class="bg-gradient-to-br from-purple-50 to-purple-100/50 rounded-xl p-6 border border-purple-200/50">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm font-medium text-gray-600">Confidence</p>
                    <p class="text-3xl font-bold text-gray-900">{{ analytics.average_confidence ? (analytics.average_confidence * 100).toFixed(1) + '%' : 'N/A' }}</p>
                  </div>
                  <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center">
                    <i class="fas fa-bullseye text-white"></i>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Multi-line Chart for Detection Trends -->
            <div class="bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-xl p-6 border border-gray-200/50">
              <div v-if="monitoringAnalytics && monitoringAnalytics.chart_data && monitoringAnalytics.chart_data.hourly.length > 0" class="h-48">
                <canvas ref="detectionChart" class="w-full h-full"></canvas>
              </div>
              <div v-else class="h-48 flex items-center justify-center text-gray-500">
                <div class="text-center">
                  <i class="fas fa-chart-area text-4xl mb-3"></i>
                  <p class="text-lg font-medium">Detection Trends Chart</p>
                  <p class="text-sm">Real-time detection trends will appear here</p>
                </div>
              </div>
            </div>
          </div>
          
          <div v-else class="text-center py-12">
            <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
              <i class="fas fa-chart-line text-gray-400 text-2xl"></i>
            </div>
            <p class="text-gray-500 text-lg">No analytics data available</p>
            <p class="text-gray-400 text-sm">Data will appear when detections are processed</p>
          </div>
        </div>

        <!-- Monitoring Status Card -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-gray-200/50 p-8">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">System Monitoring</h3>
            <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
              <i class="fas fa-heartbeat text-white text-lg"></i>
            </div>
          </div>
          
          <div v-if="monitoringStatus" class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
              <div class="bg-gradient-to-br from-blue-50 to-blue-100/50 rounded-xl p-6 border border-blue-200/50">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-sm font-medium text-gray-600">CPU Usage</p>
                  <i class="fas fa-microchip text-blue-500"></i>
                </div>
                <div class="flex items-center space-x-2">
                  <div class="flex-1 bg-gray-200 rounded-full h-2">
                    <div 
                      class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full transition-all duration-500"
                      :style="{ width: `${monitoringStatus.cpu_usage || 0}%` }"
                    ></div>
                  </div>
                  <span class="text-sm font-bold text-gray-900">{{ monitoringStatus.cpu_usage || 0 }}%</span>
                </div>
              </div>
              
              <div class="bg-gradient-to-br from-green-50 to-green-100/50 rounded-xl p-6 border border-green-200/50">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-sm font-medium text-gray-600">Memory Usage</p>
                  <i class="fas fa-memory text-green-500"></i>
                </div>
                <div class="flex items-center space-x-2">
                  <div class="flex-1 bg-gray-200 rounded-full h-2">
                    <div 
                      class="bg-gradient-to-r from-green-500 to-green-600 h-2 rounded-full transition-all duration-500"
                      :style="{ width: `${monitoringStatus.memory_usage || 0}%` }"
                    ></div>
                  </div>
                  <span class="text-sm font-bold text-gray-900">{{ monitoringStatus.memory_usage || 0 }}%</span>
                </div>
              </div>
              
              <div class="bg-gradient-to-br from-yellow-50 to-yellow-100/50 rounded-xl p-6 border border-yellow-200/50">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-sm font-medium text-gray-600">Disk Usage</p>
                  <i class="fas fa-hdd text-yellow-500"></i>
                </div>
                <div class="flex items-center space-x-2">
                  <div class="flex-1 bg-gray-200 rounded-full h-2">
                    <div 
                      class="bg-gradient-to-r from-yellow-500 to-yellow-600 h-2 rounded-full transition-all duration-500"
                      :style="{ width: `${monitoringStatus.disk_usage || 0}%` }"
                    ></div>
                  </div>
                  <span class="text-sm font-bold text-gray-900">{{ monitoringStatus.disk_usage || 0 }}%</span>
                </div>
              </div>
              
              <div class="bg-gradient-to-br from-purple-50 to-purple-100/50 rounded-xl p-6 border border-purple-200/50">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-sm font-medium text-gray-600">GPU Usage</p>
                  <i class="fas fa-microchip text-purple-500"></i>
                </div>
                <div class="flex items-center space-x-2">
                  <div class="flex-1 bg-gray-200 rounded-full h-2">
                    <div 
                      class="bg-gradient-to-r from-purple-500 to-purple-600 h-2 rounded-full transition-all duration-500"
                      :style="{ width: `${monitoringStatus.gpu_usage || 0}%` }"
                    ></div>
                  </div>
                  <span class="text-sm font-bold text-gray-900">{{ monitoringStatus.gpu_usage || 0 }}%</span>
                </div>
              </div>
            </div>
            
            <!-- Alerts Section -->
            <div v-if="monitoringStatus.alerts && monitoringStatus.alerts.length > 0" class="space-y-3">
              <h4 class="text-lg font-semibold text-gray-900">Active Alerts</h4>
              <div v-for="alert in monitoringStatus.alerts" :key="alert.timestamp" class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-start space-x-3">
                <i class="fas fa-exclamation-triangle text-red-500 mt-1"></i>
                <div>
                  <p class="text-sm font-medium text-red-800">{{ alert.message }}</p>
                  <p class="text-xs text-red-600">{{ new Date(alert.timestamp).toLocaleString() }}</p>
                </div>
              </div>
            </div>
          </div>
          
          <div v-else class="text-center py-12">
            <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
              <i class="fas fa-heartbeat text-gray-400 text-2xl"></i>
            </div>
            <p class="text-gray-500 text-lg">No monitoring data available</p>
            <p class="text-gray-400 text-sm">System metrics will appear here</p>
          </div>
        </div>
      </div>

      <!-- Performance Charts Section -->
      <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-gray-200/50 p-8">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-2xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">Performance Trends</h3>
          <div class="flex space-x-2">
            <button 
              @click="selectedPeriod = 'daily'"
              :class="[
                'px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200',
                selectedPeriod === 'daily' ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              ]"
            >
              Daily
            </button>
            <button 
              @click="selectedPeriod = 'monthly'"
              :class="[
                'px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200',
                selectedPeriod === 'monthly' ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              ]"
            >
              Monthly
            </button>
            <button 
              @click="selectedPeriod = 'yearly'"
              :class="[
                'px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200',
                selectedPeriod === 'yearly' ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              ]"
            >
              Yearly
            </button>
          </div>
        </div>

        <div v-if="monitoringAnalytics && monitoringAnalytics.chart_data" class="space-y-6">
          <!-- Multi-line Performance Chart -->
          <div class="bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-xl p-6 border border-gray-200/50">
            <div v-if="getChartData().length > 0" class="h-64">
              <canvas ref="performanceChart" class="w-full h-full"></canvas>
            </div>
            <div v-else class="h-64 flex items-center justify-center text-gray-500">
              <div class="text-center">
                <i class="fas fa-chart-area text-4xl mb-3"></i>
                <p class="text-lg font-medium">Performance Chart for {{ selectedPeriod }} view</p>
                <p class="text-sm">Real-time performance metrics will appear here</p>
              </div>
            </div>
          </div>
          
          <div v-if="monitoringSummary[selectedPeriod]" class="bg-gray-50 rounded-xl p-4">
            <h4 class="text-lg font-semibold text-gray-900 mb-3">Latest {{ selectedPeriod }} Data</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div v-for="(value, key) in monitoringSummary[selectedPeriod]" :key="key" class="bg-white rounded-lg p-4 border border-gray-200">
                <p class="text-sm font-medium text-gray-600 capitalize">{{ key.replace(/_/g, ' ') }}</p>
                <p class="text-lg font-bold text-gray-900">{{ value }}</p>
              </div>
            </div>
          </div>
        </div>
        
        <div v-else class="text-center py-12">
          <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-chart-area text-gray-400 text-2xl"></i>
          </div>
          <p class="text-gray-500 text-lg">No performance data available</p>
          <p class="text-gray-400 text-sm">Performance metrics will appear here</p>
        </div>
      </div>

      <!-- Recent Detection Results Table -->
      <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-gray-200/50 p-8">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-2xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">Recent Detection Results</h3>
          <div class="flex items-center space-x-3">
            <select 
              v-model="detectionFilter"
              class="block w-40 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-xl bg-white/80 backdrop-blur-sm"
            >
              <option value="all">All Status</option>
              <option value="success">Success</option>
              <option value="failed">Failed</option>
            </select>
            <button 
              @click="refreshData"
              class="p-3 text-gray-600 hover:text-gray-900 hover:bg-gray-100/80 rounded-xl transition-all duration-200"
            >
              <i class="fas fa-sync-alt"></i>
            </button>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/80 backdrop-blur-sm">
              <tr>
                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Session ID
                </th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Detected At
                </th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Summary
                </th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="filteredDetections.length === 0">
                <td colspan="5" class="px-6 py-12 text-center">
                  <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-search text-gray-400 text-2xl"></i>
                  </div>
                  <p class="text-gray-500 text-lg">No detection results found</p>
                  <p class="text-gray-400 text-sm">Detection results will appear here when available</p>
                </td>
              </tr>
              <tr v-for="detection in filteredDetections" :key="detection.id" class="hover:bg-gray-50/50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ detection.session_id }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ new Date(detection.detected_at).toLocaleString() }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <span 
                    :class="[
                      'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full',
                      detection.status === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                    ]"
                  >
                    <i :class="detection.status === 'success' ? 'fas fa-check-circle mr-1' : 'fas fa-times-circle mr-1'"></i>
                    {{ detection.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ detection.detection_summary || 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button 
                    @click="viewDetectionDetails(detection)" 
                    class="text-blue-600 hover:text-blue-900 px-3 py-1 rounded-lg hover:bg-blue-50 transition-colors"
                  >
                    View Details
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="detectionResults.links && detectionResults.links.length > 3" class="mt-6 flex justify-between items-center">
          <div class="flex-1 flex justify-between sm:hidden">
            <button 
              @click="goToPage(detectionResults.prev_page_url)" 
              :disabled="!detectionResults.prev_page_url" 
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
            >
              Previous
            </button>
            <button 
              @click="goToPage(detectionResults.next_page_url)" 
              :disabled="!detectionResults.next_page_url" 
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
            >
              Next
            </button>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Showing
                <span class="font-medium">{{ detectionResults.from }}</span>
                to
                <span class="font-medium">{{ detectionResults.to }}</span>
                of
                <span class="font-medium">{{ detectionResults.total }}</span>
                results
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-xl shadow-sm -space-x-px" aria-label="Pagination">
                <button 
                  v-for="(link, index) in detectionResults.links" 
                  :key="index"
                  @click="goToPage(link.url)"
                  :disabled="!link.url"
                  :class="[
                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                    link.active ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                    index === 0 ? 'rounded-l-xl' : '',
                    index === detectionResults.links.length - 1 ? 'rounded-r-xl' : ''
                  ]"
                  v-html="link.label"
                >
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>

      <!-- Detection Details Modal -->
      <div v-if="showDetectionDetailsModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden">
          <!-- Modal Header -->
          <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-purple-50">
            <h3 class="text-xl font-bold text-gray-900">Detection Details</h3>
            <button 
              @click="closeDetectionDetailsModal" 
              class="text-gray-400 hover:text-gray-600 transition-colors p-2 hover:bg-gray-100 rounded-xl"
            >
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>
          
          <!-- Modal Body -->
          <div class="p-6 max-h-[70vh] overflow-y-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
              <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-sm font-medium text-gray-500">Session ID</p>
                <p class="text-gray-900 font-mono">{{ selectedDetection.session_id }}</p>
              </div>
              <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-sm font-medium text-gray-500">RVM Name</p>
                <p class="text-gray-900">{{ selectedDetection.rvm_name }}</p>
              </div>
              <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-sm font-medium text-gray-500">Detected At</p>
                <p class="text-gray-900">{{ new Date(selectedDetection.detected_at).toLocaleString() }}</p>
              </div>
              <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-sm font-medium text-gray-500">Status</p>
                <p 
                  :class="[
                    'font-semibold',
                    selectedDetection.status === 'success' ? 'text-green-600' : 'text-red-600'
                  ]"
                >
                  <i :class="selectedDetection.status === 'success' ? 'fas fa-check-circle mr-2' : 'fas fa-times-circle mr-2'"></i>
                  {{ selectedDetection.status }}
                </p>
              </div>
            </div>
            
            <div class="mb-6">
              <p class="text-sm font-medium text-gray-500 mb-2">Detection Summary</p>
              <p class="text-gray-900 bg-gray-50 rounded-xl p-4">{{ selectedDetection.detection_summary || 'N/A' }}</p>
            </div>
            
            <div v-if="selectedDetection.detection_data" class="mb-6">
              <p class="text-sm font-medium text-gray-500 mb-2">Detection Data</p>
              <pre class="bg-gray-100 p-4 rounded-xl text-sm overflow-auto max-h-40">{{ JSON.stringify(selectedDetection.detection_data, null, 2) }}</pre>
            </div>
            
            <div v-if="selectedDetection.error_message" class="mb-6">
              <p class="text-sm font-medium text-gray-500 mb-2">Error Message</p>
              <p class="text-red-600 bg-red-50 p-4 rounded-xl text-sm">{{ selectedDetection.error_message }}</p>
            </div>
          </div>
          
          <!-- Modal Footer -->
          <div class="flex justify-end space-x-3 p-6 border-t border-gray-200 bg-gray-50">
            <button
              @click="closeDetectionDetailsModal"
              class="px-6 py-3 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 rounded-xl transition-colors border border-gray-300"
            >
              Close
            </button>
          </div>
        </div>
      </div>

      <!-- Edit IP Address Modal -->
      <div v-if="showEditIpModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
          <!-- Modal Header -->
          <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100">
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-edit text-white text-lg"></i>
              </div>
              <div>
                <h3 class="text-xl font-bold text-gray-900">Edit IP Address</h3>
                <p class="text-sm text-gray-600">Update RVM IP Address</p>
              </div>
            </div>
            <button 
              @click="closeEditIpModal" 
              class="text-gray-400 hover:text-gray-600 transition-colors p-2 hover:bg-gray-100 rounded-xl"
            >
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>
          
          <!-- Modal Body -->
          <div class="p-6">
            <form @submit.prevent="updateIpAddress" class="space-y-6">
              <div>
                <label for="ip_address" class="block text-sm font-medium text-gray-700 mb-2">
                  IP Address
                </label>
                <div class="flex space-x-3">
                  <input
                    id="ip_address"
                    v-model="editIpForm.ip_address"
                    type="text"
                    class="flex-1 px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors font-mono text-lg"
                    placeholder="Enter IP Address (e.g., 192.168.1.100)"
                    required
                  />
                  <button
                    type="button"
                    @click="testConnection"
                    :disabled="isTestingConnection || !editIpForm.ip_address.trim()"
                    class="px-6 py-3 text-sm font-medium text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl transition-all duration-200 flex items-center"
                  >
                    <i v-if="isTestingConnection" class="fas fa-spinner fa-spin mr-2"></i>
                    <i v-else class="fas fa-wifi mr-2"></i>
                    {{ isTestingConnection ? 'Testing...' : 'Test' }}
                  </button>
                </div>
                <p class="mt-2 text-xs text-gray-500">
                  <i class="fas fa-info-circle mr-1"></i>
                  Enter the new IP address for this RVM device
                </p>
                
                <!-- Connection Test Result -->
                <div v-if="connectionTestResult" class="mt-3 p-3 rounded-lg" :class="{
                  'bg-green-50 border border-green-200': connectionTestResult.success,
                  'bg-red-50 border border-red-200': !connectionTestResult.success
                }">
                  <div class="flex items-center">
                    <i :class="connectionTestResult.success ? 'fas fa-check-circle text-green-600' : 'fas fa-times-circle text-red-600'" class="mr-2"></i>
                    <span :class="connectionTestResult.success ? 'text-green-800' : 'text-red-800'" class="text-sm font-medium">
                      {{ connectionTestResult.message }}
                    </span>
                  </div>
                  <div v-if="connectionTestResult.details" class="mt-1 text-xs" :class="connectionTestResult.success ? 'text-green-600' : 'text-red-600'">
                    {{ connectionTestResult.details }}
                  </div>
                </div>
              </div>
            </form>
          </div>
          
          <!-- Modal Footer -->
          <div class="flex justify-end space-x-3 p-6 border-t border-gray-200 bg-gray-50">
            <button
              @click="closeEditIpModal"
              class="px-6 py-3 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 rounded-xl transition-colors border border-gray-300"
            >
              Cancel
            </button>
            <button
              @click="updateIpAddress"
              :disabled="isUpdatingIp || !connectionTestResult || !connectionTestResult.success"
              class="px-6 py-3 text-sm font-medium text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl transition-all duration-200 flex items-center"
            >
              <i v-if="isUpdatingIp" class="fas fa-spinner fa-spin mr-2"></i>
              <i v-else class="fas fa-save mr-2"></i>
              {{ isUpdatingIp ? 'Updating...' : 'Update IP' }}
            </button>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { router } from "@inertiajs/vue3"
import { ref, computed, onMounted, onUnmounted, nextTick } from "vue"
import Chart from 'chart.js/auto'

const props = defineProps({
  rvm: Object,
  detectionResults: Object,
  analytics: Object,
  healthStatus: Object,
  apiStatus: Object,
  hardwareInfo: Object,
  monitoringStatus: Object,
  monitoringSummary: Object,
  monitoringAnalytics: Object,
})

// Reactive variables
const isRefreshing = ref(false)
const selectedPeriod = ref('daily')
const detectionFilter = ref('all')
const showDetectionDetailsModal = ref(false)
const selectedDetection = ref(null)
const showFullApiKey = ref(false)
const showTooltip = ref(false)
const showEditIpModal = ref(false)
const isUpdatingIp = ref(false)
const isTestingConnection = ref(false)
const connectionTestResult = ref(null)
const lastRefreshTime = ref(new Date())
const currentTime = ref(new Date())
const editIpForm = ref({
  ip_address: ''
})

// Chart references
const detectionChart = ref(null)
const performanceChart = ref(null)
let detectionChartInstance = null
let performanceChartInstance = null

let refreshInterval = null
let timeUpdateInterval = null

// Computed properties
const filteredDetections = computed(() => {
  if (detectionFilter.value === 'all') {
    return props.detectionResults.data
  }
  return props.detectionResults.data.filter(detection => detection.status === detectionFilter.value)
})

const lastRefreshAgo = computed(() => {
  const diff = currentTime.value - lastRefreshTime.value
  const seconds = Math.floor(diff / 1000)
  const minutes = Math.floor(seconds / 60)
  const hours = Math.floor(minutes / 60)
  
  if (seconds < 60) {
    return `${seconds}s ago`
  } else if (minutes < 60) {
    return `${minutes}m ago`
  } else if (hours < 24) {
    return `${hours}h ago`
  } else {
    return lastRefreshTime.value.toLocaleDateString()
  }
})

// Chart data computed properties
const getChartData = () => {
  if (!props.monitoringAnalytics || !props.monitoringAnalytics.chart_data) {
    return []
  }
  
  const data = props.monitoringAnalytics.chart_data[selectedPeriod.value] || []
  return data.map(item => ({
    time: new Date(item.time).toLocaleTimeString(),
    cpu_percent: item.cpu_percent || 0,
    memory_percent: item.memory_percent || 0,
    gpu_memory_percent: item.gpu_memory_percent || 0,
    disk_usage_percent: item.disk_usage_percent || 0,
    processing_time_ms: item.processing_time_ms || 0,
    detections_count: item.detections_count || 0,
  }))
}

// Methods
const goBack = () => {
  router.get('/dashboard')
}

const refreshData = () => {
  isRefreshing.value = true
  lastRefreshTime.value = new Date()
  router.reload({
    onFinish: () => {
      isRefreshing.value = false
    }
  })
}

const endMaintenance = () => {
  if (confirm('Are you sure you want to end maintenance mode for this RVM?')) {
    router.patch(`/api/rvms/${props.rvm.id}/status`, {
      status: 'active'
    }, {
      onSuccess: () => {
        router.get('/dashboard')
      },
      onError: (errors) => {
        console.error('Failed to end maintenance:', errors)
        alert('Failed to end maintenance. Please check console for details.')
      }
    })
  }
}

const viewDetectionDetails = (detection) => {
  selectedDetection.value = detection
  showDetectionDetailsModal.value = true
}

const closeDetectionDetailsModal = () => {
  showDetectionDetailsModal.value = false
  selectedDetection.value = null
}

const goToPage = (url) => {
  if (url) {
    router.get(url, {
      filter: detectionFilter.value
    }, {
      preserveState: true,
      preserveScroll: true,
      only: ['detectionResults']
    })
  }
}

const copyApiKey = async () => {
  if (!props.rvm.api_key) {
    alert('No API key available to copy')
    return
  }
  
  try {
    await navigator.clipboard.writeText(props.rvm.api_key)
    // Show success feedback
    const button = event.target.closest('button')
    const originalIcon = button.querySelector('i')
    const originalClass = originalIcon.className
    
    originalIcon.className = 'fas fa-check text-sm text-green-600'
    setTimeout(() => {
      originalIcon.className = originalClass
    }, 2000)
  } catch (err) {
    console.error('Failed to copy API key:', err)
    alert('Failed to copy API key. Please try again.')
  }
}

const copyFullApiKey = async () => {
  if (!props.rvm.api_key) {
    alert('No API key available to copy')
    return
  }
  
  try {
    await navigator.clipboard.writeText(props.rvm.api_key)
    // Show success feedback
    const button = event.target.closest('button')
    const originalIcon = button.querySelector('i')
    const originalClass = originalIcon.className
    
    originalIcon.className = 'fas fa-check text-sm text-green-600'
    setTimeout(() => {
      originalIcon.className = originalClass
    }, 2000)
  } catch (err) {
    console.error('Failed to copy API key:', err)
    alert('Failed to copy API key. Please try again.')
  }
}

const toggleApiKeyVisibility = () => {
  showFullApiKey.value = !showFullApiKey.value
}

// Auto-refresh for detection results and RVM status
// Edit IP Address Modal Methods
const openEditIpModal = () => {
  editIpForm.value.ip_address = props.rvm.ip_address || ''
  showEditIpModal.value = true
}

const closeEditIpModal = () => {
  showEditIpModal.value = false
  editIpForm.value.ip_address = ''
  isUpdatingIp.value = false
  isTestingConnection.value = false
  connectionTestResult.value = null
}

const testConnection = async () => {
  if (!editIpForm.value.ip_address.trim()) {
    alert('Please enter an IP address to test')
    return
  }

  isTestingConnection.value = true
  connectionTestResult.value = null

  try {
    const ipAddress = editIpForm.value.ip_address.trim()
    
    // Test multiple endpoints to ensure comprehensive connectivity
    const testEndpoints = [
      `http://${ipAddress}:5000/api/health`,
      `http://${ipAddress}:5000/api/status`,
      `http://${ipAddress}:5000/api/hardware`
    ]

    let successCount = 0
    let totalTests = testEndpoints.length
    let lastError = null

    for (const endpoint of testEndpoints) {
      try {
        const controller = new AbortController()
        const timeoutId = setTimeout(() => controller.abort(), 5000) // 5 second timeout

        const response = await fetch(endpoint, {
          method: 'GET',
          signal: controller.signal,
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        })

        clearTimeout(timeoutId)

        if (response.ok) {
          successCount++
        } else {
          lastError = `HTTP ${response.status}: ${response.statusText}`
        }
      } catch (error) {
        if (error.name === 'AbortError') {
          lastError = 'Connection timeout (5s)'
        } else {
          lastError = error.message || 'Connection failed'
        }
      }
    }

    if (successCount > 0) {
      connectionTestResult.value = {
        success: true,
        message: `Connection successful! (${successCount}/${totalTests} endpoints reachable)`,
        details: successCount === totalTests 
          ? 'All RVM services are responding correctly' 
          : `${totalTests - successCount} endpoint(s) may be unavailable`
      }
    } else {
      connectionTestResult.value = {
        success: false,
        message: 'Connection failed - RVM not reachable',
        details: lastError || 'Unable to connect to any RVM services'
      }
    }

  } catch (error) {
    console.error('❌ Connection test error:', error)
    connectionTestResult.value = {
      success: false,
      message: 'Connection test failed',
      details: error.message || 'An unexpected error occurred'
    }
  } finally {
    isTestingConnection.value = false
  }
}

const updateIpAddress = async () => {
  if (!editIpForm.value.ip_address.trim()) {
    alert('Please enter a valid IP address')
    return
  }

  isUpdatingIp.value = true
  
  try {
    // Use dedicated IP address update endpoint
    await router.put(`/rvms/${props.rvm.id}/ip-address`, {
      ip_address: editIpForm.value.ip_address.trim()
    }, {
      onSuccess: () => {
        console.log('✅ IP address updated successfully')
        closeEditIpModal()
        // Refresh the page to show updated data
        router.reload({
          only: ['rvm'],
          preserveState: true,
          preserveScroll: true
        })
      },
      onError: (errors) => {
        console.error('❌ Failed to update IP address:', errors)
        alert('Failed to update IP address. Please try again.')
      },
      onFinish: () => {
        isUpdatingIp.value = false
      }
    })
  } catch (error) {
    console.error('❌ Error updating IP address:', error)
    alert('An error occurred while updating IP address')
    isUpdatingIp.value = false
  }
}

// Chart methods
const createDetectionChart = () => {
  try {
    if (!detectionChart.value) {
      console.warn('⚠️ Detection chart canvas not found')
      return
    }
    
    if (!props.monitoringAnalytics) {
      console.warn('⚠️ Monitoring analytics data not available')
      return
    }
    
    const data = props.monitoringAnalytics.chart_data?.hourly || []
    if (data.length === 0) {
      console.warn('⚠️ No hourly chart data available')
      return
    }
    
    console.log('📊 Creating detection chart with', data.length, 'data points')
  
  const ctx = detectionChart.value.getContext('2d')
  
  if (detectionChartInstance) {
    detectionChartInstance.destroy()
  }
  
  detectionChartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: data.map(item => new Date(item.time).toLocaleTimeString()),
      datasets: [
        {
          label: 'Detections Count',
          data: data.map(item => item.detections_count || 0),
          borderColor: 'rgb(59, 130, 246)',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          tension: 0.4,
          fill: true
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: 'top'
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: 'Detection Count'
          }
        },
        x: {
          title: {
            display: true,
            text: 'Time'
          }
        }
      }
    }
  })
  } catch (error) {
    console.error('❌ Error creating detection chart:', error)
    detectionChartInstance = null
  }
}

const createPerformanceChart = () => {
  try {
    if (!performanceChart.value) {
      console.warn('⚠️ Performance chart canvas not found')
      return
    }
    
    const data = getChartData()
    if (data.length === 0) {
      console.warn('⚠️ No performance chart data available')
      return
    }
    
    console.log('📊 Creating performance chart with', data.length, 'data points')
  
  const ctx = performanceChart.value.getContext('2d')
  
  if (performanceChartInstance) {
    performanceChartInstance.destroy()
  }
  
  performanceChartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: data.map(item => item.time),
      datasets: [
        {
          label: 'CPU Usage (%)',
          data: data.map(item => item.cpu_percent),
          borderColor: 'rgb(239, 68, 68)',
          backgroundColor: 'rgba(239, 68, 68, 0.1)',
          tension: 0.4,
          yAxisID: 'y'
        },
        {
          label: 'Memory Usage (%)',
          data: data.map(item => item.memory_percent),
          borderColor: 'rgb(34, 197, 94)',
          backgroundColor: 'rgba(34, 197, 94, 0.1)',
          tension: 0.4,
          yAxisID: 'y'
        },
        {
          label: 'GPU Usage (%)',
          data: data.map(item => item.gpu_memory_percent),
          borderColor: 'rgb(168, 85, 247)',
          backgroundColor: 'rgba(168, 85, 247, 0.1)',
          tension: 0.4,
          yAxisID: 'y'
        },
        {
          label: 'Disk Usage (%)',
          data: data.map(item => item.disk_usage_percent),
          borderColor: 'rgb(245, 158, 11)',
          backgroundColor: 'rgba(245, 158, 11, 0.1)',
          tension: 0.4,
          yAxisID: 'y'
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: 'top'
        }
      },
      scales: {
        y: {
          type: 'linear',
          display: true,
          position: 'left',
          beginAtZero: true,
          max: 100,
          title: {
            display: true,
            text: 'Usage Percentage (%)'
          }
        },
        x: {
          title: {
            display: true,
            text: 'Time'
          }
        }
      }
    }
  })
  } catch (error) {
    console.error('❌ Error creating performance chart:', error)
    performanceChartInstance = null
  }
}

const updateCharts = () => {
  nextTick(() => {
    createDetectionChart()
    createPerformanceChart()
  })
}

onMounted(() => {
  // Debug: Log monitoring analytics data
  console.log('🔍 Monitoring Analytics Data:', props.monitoringAnalytics)
  console.log('🔍 Chart Data Available:', props.monitoringAnalytics?.chart_data?.hourly?.length > 0)
  console.log('🔍 Hourly Data Count:', props.monitoringAnalytics?.chart_data?.hourly?.length || 0)
  
  // Initialize charts
  updateCharts()
  
  // Auto-refresh data every 30 seconds
  refreshInterval = setInterval(() => {
    console.log('🔄 Auto-refreshing maintenance data...')
    isRefreshing.value = true
    lastRefreshTime.value = new Date()
    router.reload({ 
      only: ['detectionResults', 'rvm', 'healthStatus', 'apiStatus', 'hardwareInfo', 'monitoringStatus', 'monitoringSummary', 'monitoringAnalytics'], 
      preserveState: true, 
      preserveScroll: true,
      onFinish: () => {
        console.log('✅ Maintenance data refreshed')
        isRefreshing.value = false
        updateCharts()
      }
    })
  }, 30000) // Refresh every 30 seconds

  // Update time display every second
  timeUpdateInterval = setInterval(() => {
    currentTime.value = new Date()
  }, 1000)

  // Initialize charts after component is mounted
  updateCharts()
})

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
  if (timeUpdateInterval) {
    clearInterval(timeUpdateInterval)
  }
  
  // Destroy chart instances
  if (detectionChartInstance) {
    detectionChartInstance.destroy()
  }
  if (performanceChartInstance) {
    performanceChartInstance.destroy()
  }
})
</script>

<style scoped>
/* Custom animations and styles */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.status-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Health Status Pulse Animations */
@keyframes healthPulse {
  0% {
    transform: scale(1);
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
  }
  50% {
    transform: scale(1.05);
    box-shadow: 0 0 0 10px rgba(34, 197, 94, 0.3);
  }
  100% {
    transform: scale(1);
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
  }
}

@keyframes heartbeat {
  0% {
    transform: scale(1);
  }
  25% {
    transform: scale(1.1);
  }
  50% {
    transform: scale(1);
  }
  75% {
    transform: scale(1.1);
  }
  100% {
    transform: scale(1);
  }
}

@keyframes containerGlow {
  0% {
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4);
  }
  50% {
    box-shadow: 0 0 20px 5px rgba(34, 197, 94, 0.2);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
  }
}

.health-pulse-container {
  animation: containerGlow 2s ease-in-out infinite;
}

.health-pulse-icon {
  animation: healthPulse 2s ease-in-out infinite;
}

.health-pulse-heartbeat {
  animation: heartbeat 1.5s ease-in-out infinite;
}

/* API Status Pulse Animations */
@keyframes apiPulse {
  0% {
    transform: scale(1);
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
  }
  50% {
    transform: scale(1.05);
    box-shadow: 0 0 0 10px rgba(34, 197, 94, 0.3);
  }
  100% {
    transform: scale(1);
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
  }
}

@keyframes wifiSignal {
  0% {
    transform: scale(1) rotate(0deg);
  }
  25% {
    transform: scale(1.1) rotate(2deg);
  }
  50% {
    transform: scale(1) rotate(0deg);
  }
  75% {
    transform: scale(1.1) rotate(-2deg);
  }
  100% {
    transform: scale(1) rotate(0deg);
  }
}

@keyframes apiContainerGlow {
  0% {
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4);
  }
  50% {
    box-shadow: 0 0 20px 5px rgba(34, 197, 94, 0.2);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
  }
}

.api-pulse-container {
  animation: apiContainerGlow 2s ease-in-out infinite;
}

.api-pulse-icon {
  animation: apiPulse 2s ease-in-out infinite;
}

.api-pulse-wifi {
  animation: wifiSignal 2s ease-in-out infinite;
}

/* Smooth transitions for all elements */
* {
  transition: all 0.2s ease-in-out;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>