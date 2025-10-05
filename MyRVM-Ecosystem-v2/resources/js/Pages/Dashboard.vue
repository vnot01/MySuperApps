<template>
  <div class="min-h-screen bg-gray-50" @click="closeMenus">
    <!-- Modern Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div class="flex items-center space-x-4">
            <div class="flex items-center space-x-2">
              <div class="w-8 h-8 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-recycle text-white text-sm"></i>
              </div>
              <h1 class="text-xl font-bold text-gray-900">MyRVM</h1>
            </div>
            <nav class="hidden md:flex space-x-6">
              <a href="#" class="text-purple-600 font-medium">Dashboard</a>
              <a href="#" class="text-gray-600 hover:text-gray-900">RVMs</a>
              <a href="#" class="text-gray-600 hover:text-gray-900">Reports</a>
              <a href="#" class="text-gray-600 hover:text-gray-900">Settings</a>
            </nav>
          </div>
          <div class="flex items-center space-x-4">
            <button class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
              <i class="fas fa-search"></i>
            </button>
            <button class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
              <i class="fas fa-sun"></i>
            </button>
            <button class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg relative">
              <i class="fas fa-bell"></i>
              <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
            </button>
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-full flex items-center justify-center">
                <span class="text-white text-sm font-medium">{{ auth.user.name.charAt(0) }}</span>
              </div>
              <div class="hidden md:block">
                <p class="text-sm font-medium text-gray-900">{{ auth.user.name }}</p>
                <p class="text-xs text-gray-500">Administrator</p>
              </div>
              <button @click="logout" class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                <i class="fas fa-sign-out-alt"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Modern KPI Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <!-- Total RVMs Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 mb-1">Total RVMs</p>
              <p class="text-3xl font-bold text-gray-900">{{ stats.totalRvms }}</p>
              <p class="text-xs text-gray-500 mt-1">{{ stats.activeRvms }} active</p>
            </div>
            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
              <i class="fas fa-industry text-white text-xl"></i>
            </div>
          </div>
          <div class="mt-4 flex items-center">
            <span class="text-green-600 text-sm font-medium">+2.5%</span>
            <span class="text-gray-500 text-sm ml-2">vs last month</span>
          </div>
        </div>

        <!-- Online RVMs Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 mb-1">Online RVMs</p>
              <p class="text-3xl font-bold text-gray-900">{{ stats.onlineRvms }}</p>
              <p class="text-xs text-gray-500 mt-1">Connected devices</p>
            </div>
            <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-green-600 rounded-xl flex items-center justify-center">
              <i class="fas fa-wifi text-white text-xl"></i>
            </div>
          </div>
          <div class="mt-4 flex items-center">
            <span class="text-green-600 text-sm font-medium">+12.6%</span>
            <span class="text-gray-500 text-sm ml-2">vs last week</span>
          </div>
        </div>

        <!-- Average Usage Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 mb-1">Average Usage</p>
              <p class="text-3xl font-bold text-gray-900">{{ stats.averageUsage }}%</p>
              <p class="text-xs text-gray-500 mt-1">Across all RVMs</p>
            </div>
            <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
              <i class="fas fa-chart-pie text-white text-xl"></i>
            </div>
          </div>
          <div class="mt-4 flex items-center">
            <span class="text-green-600 text-sm font-medium">+5.2%</span>
            <span class="text-gray-500 text-sm ml-2">vs last week</span>
          </div>
        </div>

        <!-- Maintenance Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 mb-1">Maintenance</p>
              <p class="text-3xl font-bold text-gray-900">{{ stats.maintenanceRvms }}</p>
              <p class="text-xs text-gray-500 mt-1">Needs attention</p>
            </div>
            <div class="w-12 h-12 bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl flex items-center justify-center">
              <i class="fas fa-tools text-white text-xl"></i>
            </div>
          </div>
          <div class="mt-4 flex items-center">
            <span class="text-red-600 text-sm font-medium">-16.2%</span>
            <span class="text-gray-500 text-sm ml-2">vs last month</span>
          </div>
        </div>

        <!-- Total Detections Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 mb-1">Total Detections</p>
              <p class="text-3xl font-bold text-gray-900">{{ stats.totalDetections }}</p>
              <p class="text-xs text-gray-500 mt-1">All time</p>
            </div>
            <div class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center">
              <i class="fas fa-eye text-white text-xl"></i>
            </div>
          </div>
          <div class="mt-4 flex items-center">
            <span class="text-green-600 text-sm font-medium">+24.5%</span>
            <span class="text-gray-500 text-sm ml-2">vs last week</span>
          </div>
        </div>
      </div>

      <!-- Charts and Analytics Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Detection Analytics Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Detection Analytics</h3>
              <p class="text-sm text-gray-600">Last 6 months overview</p>
            </div>
            <div class="flex space-x-2">
              <button class="px-3 py-1 text-xs bg-purple-100 text-purple-700 rounded-full">Detections</button>
              <button class="px-3 py-1 text-xs text-gray-500 hover:bg-gray-100 rounded-full">Success Rate</button>
              <button class="px-3 py-1 text-xs text-gray-500 hover:bg-gray-100 rounded-full">Errors</button>
            </div>
          </div>
          <div class="h-64 flex items-end space-x-2">
            <div class="flex-1 bg-gradient-to-t from-purple-500 to-purple-300 rounded-t-lg" style="height: 60%"></div>
            <div class="flex-1 bg-gradient-to-t from-purple-500 to-purple-300 rounded-t-lg" style="height: 80%"></div>
            <div class="flex-1 bg-gradient-to-t from-purple-500 to-purple-300 rounded-t-lg" style="height: 45%"></div>
            <div class="flex-1 bg-gradient-to-t from-purple-500 to-purple-300 rounded-t-lg" style="height: 70%"></div>
            <div class="flex-1 bg-gradient-to-t from-purple-500 to-purple-300 rounded-t-lg" style="height: 90%"></div>
            <div class="flex-1 bg-gradient-to-t from-purple-500 to-purple-300 rounded-t-lg" style="height: 55%"></div>
          </div>
          <div class="flex justify-between text-xs text-gray-500 mt-2">
            <span>Jan</span>
            <span>Feb</span>
            <span>Mar</span>
            <span>Apr</span>
            <span>May</span>
            <span>Jun</span>
          </div>
        </div>

        <!-- RVM Performance Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">RVM Performance</h3>
              <p class="text-sm text-gray-600">Efficiency metrics</p>
            </div>
            <div class="w-8 h-8 bg-gradient-to-r from-teal-500 to-teal-600 rounded-lg flex items-center justify-center">
              <i class="fas fa-chart-radar text-white text-sm"></i>
            </div>
          </div>
          <div class="h-64 flex items-center justify-center">
            <div class="w-48 h-48 relative">
              <!-- Radar Chart Placeholder -->
              <div class="absolute inset-0 border-2 border-teal-200 rounded-full"></div>
              <div class="absolute inset-4 border-2 border-teal-300 rounded-full"></div>
              <div class="absolute inset-8 border-2 border-teal-400 rounded-full"></div>
              <div class="absolute inset-12 border-2 border-teal-500 rounded-full"></div>
              <div class="absolute inset-16 border-2 border-teal-600 rounded-full"></div>
              <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                <div class="w-4 h-4 bg-teal-500 rounded-full"></div>
              </div>
            </div>
          </div>
          <div class="grid grid-cols-3 gap-4 mt-4">
            <div class="text-center">
              <p class="text-2xl font-bold text-gray-900">98.5%</p>
              <p class="text-xs text-gray-500">Uptime</p>
            </div>
            <div class="text-center">
              <p class="text-2xl font-bold text-gray-900">15.2%</p>
              <p class="text-xs text-gray-500">Efficiency</p>
            </div>
            <div class="text-center">
              <p class="text-2xl font-bold text-gray-900">2.1%</p>
              <p class="text-xs text-gray-500">Error Rate</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom Row: Recent Detections & RVM Status -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Recent Detections -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Recent Detections</h3>
              <p class="text-sm text-gray-500">Page {{ pagination?.detections?.current_page || 1 }} of {{ pagination?.detections?.total_pages || 1 }}</p>
            </div>
            <button class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
              View All
            </button>
          </div>

          <!-- Additional Pagination Controls for Recent Detections -->
          <div v-if="props.pagination?.detections?.total_pages > 1" class="mb-6 flex items-center justify-between">
            <div class="text-sm text-gray-500">
              Showing {{ ((props.pagination?.detections?.current_page - 1) * props.pagination?.detections?.per_page) + 1 }} to 
              {{ Math.min(props.pagination?.detections?.current_page * props.pagination?.detections?.per_page, props.pagination?.detections?.total) }} 
              of {{ props.pagination?.detections?.total }} detections
            </div>
            <div class="flex space-x-2">
              <button 
                @click="changeDetectionPage(props.pagination?.detections?.current_page - 1)"
                :disabled="props.pagination?.detections?.current_page <= 1"
                class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg transition-colors"
              >
                Previous
              </button>
              <span class="px-3 py-1 text-sm bg-indigo-100 text-indigo-700 rounded-lg">
                {{ props.pagination?.detections?.current_page }}
              </span>
              <button 
                @click="changeDetectionPage(props.pagination?.detections?.current_page + 1)"
                :disabled="props.pagination?.detections?.current_page >= props.pagination?.detections?.total_pages"
                class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg transition-colors"
              >
                Next
              </button>
            </div>
          </div>
          
          <div v-if="recentDetections.length === 0" class="text-center text-gray-500 py-8 min-h-[400px] flex items-center justify-center">
            <div>
              <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-search text-gray-400 text-2xl"></i>
              </div>
              <p>No recent detections</p>
            </div>
          </div>
          
          <div v-else class="space-y-3 min-h-[400px]">
            <div v-for="detection in recentDetections" :key="detection.id" 
                 class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
              <div class="flex items-center space-x-3">
                <div :class="['w-3 h-3 rounded-full', getDetectionStatusColor(detection.status)]"></div>
                <div>
                  <p class="font-medium text-gray-900">{{ detection.rvm_name }}</p>
                  <p class="text-sm text-gray-600">Session: {{ detection.session_id }}</p>
                </div>
              </div>
              <div class="text-right">
                <p class="text-sm text-gray-600">{{ detection.detected_at }}</p>
                <p class="text-sm font-medium text-gray-900">{{ detection.detection_summary }}</p>
              </div>
            </div>
          </div>

          <!-- Detection Pagination -->
          <div v-if="props.pagination?.detections?.total_pages > 1" class="mt-6 flex items-center justify-between">
            <div class="text-sm text-gray-500">
              Showing {{ ((props.pagination?.detections?.current_page - 1) * props.pagination?.detections?.per_page) + 1 }} to 
              {{ Math.min(props.pagination?.detections?.current_page * props.pagination?.detections?.per_page, props.pagination?.detections?.total) }} 
              of {{ props.pagination?.detections?.total }} detections
            </div>
            <div class="flex space-x-2">
              <button 
                @click="changeDetectionPage(props.pagination?.detections?.current_page - 1)"
                :disabled="props.pagination?.detections?.current_page <= 1"
                class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg transition-colors"
              >
                Previous
              </button>
              <span class="px-3 py-1 text-sm bg-indigo-100 text-indigo-700 rounded-lg">
                {{ props.pagination?.detections?.current_page }}
              </span>
              <button 
                @click="changeDetectionPage(props.pagination?.detections?.current_page + 1)"
                :disabled="props.pagination?.detections?.current_page >= props.pagination?.detections?.total_pages"
                class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg transition-colors"
              >
                Next
              </button>
            </div>
          </div>
        </div>

        <!-- RVM Status Overview -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">RVM Status Overview</h3>
              <p class="text-sm text-gray-500">Page {{ pagination.rvms.current_page }} of {{ pagination.rvms.total_pages }}</p>
              <div class="flex items-center text-xs text-gray-500 mt-1">
                <div :class="[
                  'w-2 h-2 rounded-full mr-2',
                  isRefreshing ? 'bg-blue-500 animate-spin' : 'bg-green-500 animate-pulse'
                ]"></div>
                <span>{{ isRefreshing ? 'Refreshing...' : 'Auto-refresh: 30s' }}</span>
              </div>
            </div>
            <button 
              @click="openAddRvmModal"
              class="px-4 py-2 text-sm bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors"
            >
              Add New RVM
            </button>
          </div>

          <!-- Additional Pagination Controls for RVM Status Overview -->
          <div v-if="props.pagination.rvms.total_pages > 1" class="mb-6 flex items-center justify-between">
            <div class="text-sm text-gray-500">
              Showing {{ ((props.pagination.rvms.current_page - 1) * props.pagination.rvms.per_page) + 1 }} to 
              {{ Math.min(props.pagination.rvms.current_page * props.pagination.rvms.per_page, props.pagination.rvms.total) }} 
              of {{ props.pagination.rvms.total }} RVMs
            </div>
            <div class="flex space-x-2">
              <button 
                @click="changeRvmPage(props.pagination.rvms.current_page - 1)"
                :disabled="props.pagination.rvms.current_page <= 1"
                class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg transition-colors"
              >
                Previous
              </button>
              <span class="px-3 py-1 text-sm bg-purple-100 text-purple-700 rounded-lg">
                {{ props.pagination.rvms.current_page }}
              </span>
              <button 
                @click="changeRvmPage(props.pagination.rvms.current_page + 1)"
                :disabled="props.pagination.rvms.current_page >= props.pagination.rvms.total_pages"
                class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg transition-colors"
              >
                Next
              </button>
            </div>
          </div>

          <div class="space-y-4 min-h-[400px]">
            <div v-for="rvm in rvms" :key="rvm.id" class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
              <div class="flex items-center justify-between mb-3">
                <h4 class="font-semibold text-gray-900">{{ rvm.name }}</h4>
                <div class="flex items-center space-x-2">
                  <!-- Status Badge -->
                  <span :class="[
                    'px-2 py-1 text-xs rounded-full font-medium',
                    rvm.status === 'active' ? 'bg-green-100 text-green-800' : 
                    rvm.status === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : 
                    'bg-red-100 text-red-800'
                  ]">
                    {{ rvm.status }}
                  </span>
                  
                <!-- Connection Status with Pulse Animation -->
                <div class="relative group cursor-pointer">
                  <div :class="[
                    'w-3 h-3 rounded-full status-pulse',
                    rvm.connection_status === 'connected' 
                      ? 'bg-green-500' 
                      : 'bg-red-500'
                  ]" 
                  :title="rvm.connection_status === 'connected' ? 'Terhubung' : 'Tidak Terhubung'"></div>
                  <!-- Tooltip -->
                  <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                    {{ rvm.connection_status === 'connected' ? 'Terhubung' : 'Tidak Terhubung' }}
                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-800"></div>
                  </div>
                </div>
                  
                  <!-- API Status with Pulse Animation -->
                  <div class="relative group cursor-pointer">
                    <div :class="[
                      'w-3 h-3 rounded-full status-pulse',
                      rvm.api_status === 'valid' 
                        ? 'bg-blue-500' 
                        : 'bg-orange-500'
                    ]" 
                    :title="rvm.api_status === 'valid' ? 'API Valid' : 'API Invalid'"></div>
                    <!-- Tooltip -->
                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                      {{ rvm.api_status === 'valid' ? 'API Valid' : 'API Invalid' }}
                      <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-800"></div>
                    </div>
                  </div>
                  
                  <!-- Action Menu Dropdown -->
                  <div class="relative">
                    <button 
                      @click.stop="toggleRvmMenu(rvm.id)"
                      class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors"
                    >
                      <i class="fas fa-ellipsis-v text-sm"></i>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div 
                      v-if="activeRvmMenu === rvm.id"
                      class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200 z-10"
                    >
                      <div class="py-1">
                        <button 
                          @click.stop="viewRvmDetails(rvm.id)"
                          class="flex items-center justify-start w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors text-left"
                        >
                          <i class="fas fa-eye mr-3 text-blue-500"></i>
                          <span class="text-left">Lihat Details</span>
                        </button>
                        <button 
                          @click.stop="editRvm(rvm.id)"
                          class="flex items-center justify-start w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors text-left"
                        >
                          <i class="fas fa-edit mr-3 text-green-500"></i>
                          <span class="text-left">Edit</span>
                        </button>
                        <button 
                          @click.stop="toggleMaintenance(rvm.id, rvm.status)"
                          class="flex items-center justify-start w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors text-left"
                        >
                          <i class="fas fa-wrench mr-3 text-yellow-500"></i>
                          <span class="text-left">{{ rvm.status === 'maintenance' ? 'End Maintenance' : 'Maintenance' }}</span>
                        </button>
                        <button 
                          @click.stop="deleteRvm(rvm.id, rvm.name)"
                          class="flex items-center justify-start w-full px-4 py-2 text-sm text-gray-700 hover:bg-red-100 transition-colors text-left"
                        >
                          <i class="fas fa-trash mr-3 text-red-500"></i>
                          <span class="text-left text-red-600">Delete</span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <p class="text-sm text-gray-600 mb-3">{{ rvm.location }}</p>
              
              <!-- Usage Bar -->
              <div class="mb-3">
                <div class="flex justify-between text-sm mb-1">
                  <span class="text-gray-500">Usage</span>
                  <span class="font-medium">{{ Math.min(rvm.capacityPercentage, 100) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                  <div :class="[
                    'h-2 rounded-full transition-all duration-300',
                    rvm.capacityPercentage > 80 ? 'bg-red-500' : 
                    rvm.capacityPercentage > 60 ? 'bg-yellow-500' : 'bg-green-500'
                  ]" :style="{ width: Math.min(rvm.capacityPercentage, 100) + '%' }"></div>
                </div>
                <div v-if="rvm.capacityPercentage > 100" class="text-xs text-red-600 mt-1">
                  ⚠️ Over capacity: {{ rvm.currentLoad }}/{{ rvm.capacity }}
                </div>
              </div>

              
              <!-- Additional Info -->
              <div class="mt-2 text-xs text-gray-500">
                <div>
                  Last ping: {{ getLastPingInfo(rvm) }}
                </div>
              </div>
            </div>
          </div>

          <!-- RVM Pagination -->
          <div v-if="props.pagination.rvms.total_pages > 1" class="mt-6 flex items-center justify-between">
            <div class="text-sm text-gray-500">
              Showing {{ ((props.pagination.rvms.current_page - 1) * props.pagination.rvms.per_page) + 1 }} to 
              {{ Math.min(props.pagination.rvms.current_page * props.pagination.rvms.per_page, props.pagination.rvms.total) }} 
              of {{ props.pagination.rvms.total }} RVMs
            </div>
            <div class="flex space-x-2">
              <button 
                @click="changeRvmPage(props.pagination.rvms.current_page - 1)"
                :disabled="props.pagination.rvms.current_page <= 1"
                class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg transition-colors"
              >
                Previous
              </button>
              <span class="px-3 py-1 text-sm bg-purple-100 text-purple-700 rounded-lg">
                {{ props.pagination.rvms.current_page }}
              </span>
              <button 
                @click="changeRvmPage(props.pagination.rvms.current_page + 1)"
                :disabled="props.pagination.rvms.current_page >= props.pagination.rvms.total_pages"
                class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg transition-colors"
              >
                Next
              </button>
            </div>
          </div>
        </div>
      </div>

    </main>

    <!-- Add New RVM Modal -->
    <div v-if="showAddRvmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Add New RVM</h3>
          <button 
            @click="closeAddRvmModal"
            class="text-gray-400 hover:text-gray-600 transition-colors"
          >
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>

        <!-- Modal Body -->
        <form @submit.prevent="submitNewRvm" class="p-6">
          <div class="space-y-4">
            <!-- RVM Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                RVM Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="newRvmForm.name"
                type="text"
                required
                placeholder="e.g., RVM-007"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
              />
            </div>

            <!-- Location -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Location <span class="text-red-500">*</span>
              </label>
              <input
                v-model="newRvmForm.location"
                type="text"
                required
                placeholder="e.g., Mall Central Jakarta"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
              />
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
            <button
              type="button"
              @click="closeAddRvmModal"
              :disabled="isSubmitting"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors disabled:opacity-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="isSubmitting || !newRvmForm.name || !newRvmForm.location"
              class="px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors disabled:opacity-50 flex items-center"
            >
              <i v-if="isSubmitting" class="fas fa-spinner fa-spin mr-2"></i>
              {{ isSubmitting ? 'Creating...' : 'Create RVM' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- API Key Modal -->
    <div v-if="showApiKeyModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">RVM Created Successfully!</h3>
          <button 
            @click="showApiKeyModal = false"
            class="text-gray-400 hover:text-gray-600 transition-colors"
          >
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
          <div class="mb-6">
            <div class="flex items-center mb-4">
              <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                <i class="fas fa-check text-green-600 text-xl"></i>
              </div>
              <div>
                <h4 class="text-lg font-semibold text-gray-900">RVM Ready for Installation</h4>
                <p class="text-sm text-gray-600">Your RVM has been created and is ready for Jetson integration</p>
              </div>
            </div>
          </div>

          <!-- RVM Info -->
          <div v-if="newRvmData" class="mb-6 p-4 bg-gray-50 rounded-lg">
            <h5 class="font-medium text-gray-900 mb-2">RVM Information</h5>
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <span class="text-gray-500">Name:</span>
                <span class="font-medium ml-2">{{ newRvmData.name }}</span>
              </div>
              <div>
                <span class="text-gray-500">Location:</span>
                <span class="font-medium ml-2">{{ newRvmData.location }}</span>
              </div>
              <div>
                <span class="text-gray-500">Status:</span>
                <span class="font-medium ml-2 text-yellow-600">{{ newRvmData.status }}</span>
              </div>
              <div>
                <span class="text-gray-500">RVM ID:</span>
                <span class="font-medium ml-2">{{ newRvmData.id }}</span>
              </div>
            </div>
          </div>

          <!-- API Key Section -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              API Key for Jetson Integration
            </label>
            <div class="flex items-center space-x-2">
              <input
                :value="newRvmApiKey"
                readonly
                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 font-mono text-sm"
              />
              <button
                @click="copyApiKey"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center"
              >
                <i class="fas fa-copy mr-2"></i>
                Copy
              </button>
            </div>
            <p class="text-xs text-gray-500 mt-2">
              Use this API key when sending requests to the Jetson device
            </p>
          </div>

        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end space-x-3 p-6 border-t border-gray-200">
          <button
            @click="showApiKeyModal = false"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
          >
            Close
          </button>
          <button
            @click="finishRvmCreation"
            class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors flex items-center"
          >
            <i class="fas fa-check mr-2"></i>
            Finish
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { router } from "@inertiajs/vue3"
import { ref, onMounted, onUnmounted } from "vue"

const props = defineProps({
  auth: Object,
  stats: Object,
  rvms: Array,
  recentDetections: Array,
  pagination: Object,
  csrf_token: String,
  rvm_created: Object
})

// Reactive variables for action menus
const activeRvmMenu = ref(null)

// Modal state for Add New RVM
const showAddRvmModal = ref(false)
const isSubmitting = ref(false)
const showApiKeyModal = ref(false)
const newRvmApiKey = ref('')
const newRvmData = ref(null)

// Auto refresh state
const isRefreshing = ref(false)
const lastRefreshTime = ref(new Date())

// Form data for new RVM
const newRvmForm = ref({
  name: '',
  location: ''
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat("id-ID").format(amount)
}

const formatTimeAgo = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffInSeconds = Math.floor((now - date) / 1000)
  
  if (diffInSeconds < 60) return `${diffInSeconds} seconds ago`
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`
  return `${Math.floor(diffInSeconds / 86400)} days ago`
}

const getLastPingInfo = (rvm) => {
  // Priority: last_ping > last_connection_check > last_api_check
  if (rvm.last_ping) {
    return formatTimeAgo(rvm.last_ping)
  } else if (rvm.last_connection_check) {
    return formatTimeAgo(rvm.last_connection_check) + ' (connection check)'
  } else if (rvm.last_api_check) {
    return formatTimeAgo(rvm.last_api_check) + ' (API check)'
  } else {
    return 'No ping data'
  }
}

const getStatusColor = (status, isOnline) => {
  if (status === 'active') {
    return isOnline ? "bg-green-500" : "bg-yellow-500"
  }
  
  const colors = {
    inactive: "bg-gray-400",
    maintenance: "bg-yellow-500",
    error: "bg-red-500"
  }
  return colors[status] || "bg-gray-400"
}

const getStatusTextColor = (status) => {
  const colors = {
    active: "bg-green-100 text-green-800",
    inactive: "bg-gray-100 text-gray-800",
    maintenance: "bg-yellow-100 text-yellow-800",
    error: "bg-red-100 text-red-800"
  }
  return colors[status] || "bg-gray-100 text-gray-800"
}

const getDetectionStatusColor = (status) => {
  const colors = {
    completed: "bg-green-500",
    processing: "bg-yellow-500",
    failed: "bg-red-500",
    pending: "bg-gray-500"
  }
  return colors[status] || "bg-gray-500"
}

const logout = () => {
  router.post("/logout")
}

// Pagination functions
const changeRvmPage = (page) => {
  // Validate page number
  if (page < 1 || page > props.pagination.rvms.total_pages) {
    console.warn('Invalid RVM page number:', page);
    return;
  }
  
  const currentParams = new URLSearchParams(window.location.search);
  currentParams.set('rvm_page', page);
  // Preserve detection_page if it exists
  if (props.pagination?.detections?.current_page > 1) {
    currentParams.set('detection_page', props.pagination?.detections?.current_page);
  }
  
  router.get('/dashboard?' + currentParams.toString(), {}, { 
    preserveState: true, 
    preserveScroll: true 
  })
}

const changeDetectionPage = (page) => {
  // Validate page number
  if (page < 1 || page > (props.pagination?.detections?.total_pages || 1)) {
    console.warn('Invalid Detection page number:', page);
    return;
  }
  
  const currentParams = new URLSearchParams(window.location.search);
  currentParams.set('detection_page', page);
  // Preserve rvm_page if it exists
  if ((props.pagination?.rvms?.current_page || 1) > 1) {
    currentParams.set('rvm_page', props.pagination?.rvms?.current_page);
  }
  
  router.get('/dashboard?' + currentParams.toString(), {}, { 
    preserveState: true, 
    preserveScroll: true 
  })
}

// Action menu functions
const toggleRvmMenu = (rvmId) => {
  console.log('Toggle menu for RVM:', rvmId, 'Current active:', activeRvmMenu.value)
  activeRvmMenu.value = activeRvmMenu.value === rvmId ? null : rvmId
  console.log('New active menu:', activeRvmMenu.value)
}

const viewRvmDetails = (rvmId) => {
  // Close menu
  activeRvmMenu.value = null
  // Navigate to RVM details page
  router.get(`/rvms/${rvmId}`)
}

const editRvm = (rvmId) => {
  // Close menu
  activeRvmMenu.value = null
  // Navigate to RVM edit page
  router.get(`/rvms/${rvmId}/edit`)
}

const toggleMaintenance = (rvmId, currentStatus) => {
  // Close menu
  activeRvmMenu.value = null
  
  if (currentStatus === 'maintenance') {
    // End maintenance - use new maintenance endpoint
    router.post(`/maintenance/${rvmId}/end`, {}, {
      onSuccess: () => {
        // Show success message
        console.log(`RVM ${rvmId} maintenance ended successfully`)
      },
      onError: (errors) => {
        console.error('Failed to update RVM status:', errors)
      }
    })
  } else {
    // Start maintenance - navigate to maintenance page
    router.get(`/maintenance/${rvmId}`)
  }
}

const deleteRvm = (rvmId, rvmName) => {
  // Close menu
  activeRvmMenu.value = null
  
  // Confirm deletion
  if (confirm(`Are you sure you want to delete RVM "${rvmName}"? This action cannot be undone.`)) {
    // Delete RVM using web route
    router.delete(`/rvms/${rvmId}`, {
      onSuccess: () => {
        console.log(`RVM ${rvmName} deleted successfully`)
        // Refresh the page to show updated RVM list
        router.reload()
      },
      onError: (errors) => {
        console.error('Failed to delete RVM:', errors)
        alert('Failed to delete RVM. Please try again.')
      }
    })
  }
}

// Close menu when clicking outside
const closeMenus = (event) => {
  // Check if click is outside menu dropdown
  if (!event.target.closest('.relative')) {
    activeRvmMenu.value = null
  }
}

// Global click handler for better menu closing
const handleGlobalClick = (event) => {
  if (!event.target.closest('.relative')) {
    activeRvmMenu.value = null
  }
}

// Add global event listener
onMounted(() => {
  document.addEventListener('click', handleGlobalClick)
  
  // Auto refresh RVM data every 30 seconds
  const refreshInterval = setInterval(() => {
    if (!isRefreshing.value) {
      console.log('Auto refreshing RVM status...')
      isRefreshing.value = true
      lastRefreshTime.value = new Date()
      
      // First trigger status check, then reload data
      fetch('/api/rvm/check-status', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
      }).then(() => {
        // After status check, reload the data
        router.reload({ 
          only: ['rvms', 'pagination'],
          preserveState: true,
          preserveScroll: true,
          onFinish: () => {
            isRefreshing.value = false
          }
        })
      }).catch((error) => {
        console.error('Status check failed:', error)
        // Still reload data even if status check fails
        router.reload({ 
          only: ['rvms', 'pagination'],
          preserveState: true,
          preserveScroll: true,
          onFinish: () => {
            isRefreshing.value = false
          }
        })
      })
    }
  }, 30000) // 30 seconds
  
  // Store interval ID for cleanup
  window.dashboardRefreshInterval = refreshInterval
})

// Remove event listener on unmount
onUnmounted(() => {
  document.removeEventListener('click', handleGlobalClick)
  
  // Clear auto refresh interval
  if (window.dashboardRefreshInterval) {
    clearInterval(window.dashboardRefreshInterval)
    window.dashboardRefreshInterval = null
  }
})

// API Key Modal Functions
const copyApiKey = () => {
  if (navigator.clipboard && window.isSecureContext) {
    navigator.clipboard.writeText(newRvmApiKey.value).then(() => {
      console.log('API key copied to clipboard')
    }).catch(err => {
      console.error('Failed to copy API key:', err)
      fallbackCopyTextToClipboard(newRvmApiKey.value)
    })
  } else {
    fallbackCopyTextToClipboard(newRvmApiKey.value)
  }
}

const finishRvmCreation = () => {
  // Close the API key modal
  showApiKeyModal.value = false
  
  // Clear the RVM creation data
  newRvmApiKey.value = ''
  newRvmData.value = null
  
  // Refresh the RVM data to show the new RVM in the table
  router.reload()
}


// Add New RVM Modal Functions
const openAddRvmModal = () => {
  showAddRvmModal.value = true
  // Reset form
  newRvmForm.value = {
    name: '',
    location: ''
  }
}

const closeAddRvmModal = () => {
  showAddRvmModal.value = false
  isSubmitting.value = false
}

const submitNewRvm = () => {
  isSubmitting.value = true
  
  // Submit new RVM using web route (with CSRF token)
  router.post('/rvms', {
    ...newRvmForm.value,
    _token: props.csrf_token
  }, {
    onSuccess: (page) => {
      console.log('RVM created successfully')
      closeAddRvmModal()
      
      // Check if RVM was created and API key is available
      if (page.props.rvm_created) {
        newRvmApiKey.value = page.props.rvm_created.api_key
        newRvmData.value = page.props.rvm_created.rvm
        showApiKeyModal.value = true
      } else {
        // Just reload the page
        router.reload()
      }
    },
    onError: (errors) => {
      console.error('Failed to create RVM:', errors)
      isSubmitting.value = false
    }
  })
}
</script>

<style scoped>
/* Modern gradient backgrounds */
.gradient-bg {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Custom pulse animation for status indicators */
@keyframes status-pulse {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.5;
    transform: scale(1.1);
  }
}

.status-pulse {
  animation: status-pulse 2s ease-in-out infinite;
}

/* Tooltip styles */
.tooltip {
  position: relative;
}

.tooltip::before {
  content: attr(data-tooltip);
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.8);
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  white-space: nowrap;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.2s;
  z-index: 10;
}

.tooltip::after {
  content: '';
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%) translateY(100%);
  border: 4px solid transparent;
  border-top-color: rgba(0, 0, 0, 0.8);
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.2s;
  z-index: 10;
}

.tooltip:hover::before,
.tooltip:hover::after {
  opacity: 1;
}

/* Smooth animations */
.transition-all {
  transition: all 0.3s ease;
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

/* Hover effects */
.hover-lift:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

/* Status indicators */
.status-online {
  background: linear-gradient(45deg, #10b981, #34d399);
}

.status-offline {
  background: linear-gradient(45deg, #ef4444, #f87171);
}

.status-maintenance {
  background: linear-gradient(45deg, #f59e0b, #fbbf24);
}
</style>

