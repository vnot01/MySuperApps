<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div class="flex items-center">
            <button 
              @click="goBack"
              class="mr-4 p-2 text-gray-400 hover:text-gray-600 transition-colors"
            >
              <i class="fas fa-arrow-left text-xl"></i>
            </button>
            <h1 class="text-2xl font-bold text-gray-900">RVM Details</h1>
          </div>
          <div class="flex items-center space-x-3">
            <button 
              @click="enterMaintenance"
              :disabled="rvm.status === 'maintenance'"
              class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 disabled:bg-gray-400 text-white rounded-lg transition-colors flex items-center"
            >
              <i class="fas fa-wrench mr-2"></i>
              {{ rvm.status === 'maintenance' ? 'In Maintenance' : 'Enter Maintenance' }}
            </button>
            <button 
              @click="enterPlayground"
              class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center"
            >
              <i class="fas fa-play mr-2"></i>
              Enter Playground
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Basic Information -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-lg font-semibold text-gray-900">Basic Information</h2>
              <button 
                @click="toggleEditMode"
                :disabled="isSaving"
                :class="[
                  'px-4 py-2 text-sm font-medium rounded-lg transition-colors flex items-center',
                  isEditing ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-blue-600 hover:bg-blue-700 text-white',
                  isSaving ? 'opacity-50 cursor-not-allowed' : ''
                ]"
              >
                <i :class="[
                  'fas mr-2', 
                  isSaving ? 'fa-spinner fa-spin' : (isEditing ? 'fa-check' : 'fa-edit')
                ]"></i>
                {{ isSaving ? 'Saving...' : (isEditing ? 'Finish' : 'Edit') }}
              </button>
            </div>
            
            <form @submit.prevent="saveChanges">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- RVM Name -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">RVM Name</label>
                  <input 
                    v-if="isEditing"
                    v-model="editForm.name"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    required
                  />
                  <div v-else class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg">
                    {{ rvm.name }}
                  </div>
                </div>

                <!-- Location -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                  <input 
                    v-if="isEditing"
                    v-model="editForm.location"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    required
                  />
                  <div v-else class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg">
                    {{ rvm.location }}
                  </div>
                </div>

                <!-- Address (Readonly) -->
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                  <div class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg">
                    {{ rvm.address || 'Not specified' }}
                  </div>
                </div>

                <!-- IP Address -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">IP Address</label>
                  <input 
                    v-if="isEditing"
                    v-model="editForm.ip_address"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="e.g., 192.168.1.100"
                  />
                  <div v-else class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg">
                    {{ rvm.ip_address || 'Not configured' }}
                  </div>
                </div>

                <!-- Status -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                  <div class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg">
                    <span :class="getStatusBadgeClass(rvm.status)">
                      {{ rvm.status }}
                    </span>
                  </div>
                </div>

                <!-- Latitude (Readonly) -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Latitude</label>
                  <div class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg">
                    {{ rvm.latitude || 'Not set' }}
                  </div>
                </div>

                <!-- Longitude (Readonly) -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Longitude</label>
                  <div class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg">
                    {{ rvm.longitude || 'Not set' }}
                  </div>
                </div>
              </div>
            </form>
          </div>

          <!-- Capacity & Load Information -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-lg font-semibold text-gray-900">Capacity & Load Information</h2>
              <div class="flex items-center space-x-2">
                <button 
                  @click="toggleCapacityEditMode"
                  :disabled="isCapacitySaving"
                  :class="[
                    'px-4 py-2 text-sm font-medium rounded-lg transition-colors flex items-center',
                    isCapacityEditing ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-blue-600 hover:bg-blue-700 text-white',
                    isCapacitySaving ? 'opacity-50 cursor-not-allowed' : ''
                  ]"
                >
                  <i :class="[
                    'fas mr-2', 
                    isCapacitySaving ? 'fa-spinner fa-spin' : (isCapacityEditing ? 'fa-check' : 'fa-edit')
                  ]"></i>
                  {{ isCapacitySaving ? 'Saving...' : (isCapacityEditing ? 'Finish' : 'Edit') }}
                </button>
                <button 
                  @click="resetCapacityLoad"
                  :disabled="isCapacitySaving"
                  class="px-4 py-2 text-sm font-medium text-white bg-gray-600 hover:bg-gray-700 rounded-lg transition-colors flex items-center disabled:opacity-50"
                >
                  <i class="fas fa-undo mr-2"></i>
                  Reset
                </button>
              </div>
            </div>
            
            <form @submit.prevent="saveCapacityChanges">
              <div class="grid grid-cols-1 gap-6">
                <!-- Current Load -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Current Load</label>
                  <input 
                    v-if="isCapacityEditing"
                    v-model="capacityEditForm.current_load"
                    type="number"
                    min="0"
                    max="100"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    required
                  />
                  <div v-else class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg">
                    {{ rvm.current_load }}
                  </div>
                </div>

                <!-- Usage Percentage (Auto-calculated) -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Usage Percentage</label>
                  <div class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg">
                    <div class="flex items-center">
                      <div class="flex-1 bg-gray-200 rounded-full h-2 mr-3">
                        <div 
                          :class="[
                            'h-2 rounded-full transition-all duration-300',
                            calculatedUsagePercentage > 100 ? 'bg-red-500' : 'bg-blue-500'
                          ]"
                          :style="{ width: Math.min(calculatedUsagePercentage, 100) + '%' }"
                        ></div>
                      </div>
                      <span class="text-sm font-medium text-gray-700">
                        {{ calculatedUsagePercentage.toFixed(1) }}%
                      </span>
                    </div>
                    <div v-if="calculatedUsagePercentage > 100" class="text-sm text-red-600 mt-1">
                      ⚠️ Over capacity by {{ (calculatedUsagePercentage - 100).toFixed(1) }}%
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                      Auto-calculated: (Current Load ÷ 100) × 100%
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>

          <!-- API Information -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-lg font-semibold text-gray-900">API Information</h2>
              <div class="flex items-center space-x-2">
                <button 
                  @click="toggleApiEditMode"
                  :disabled="isApiSaving"
                  :class="[
                    'px-4 py-2 text-sm font-medium rounded-lg transition-colors flex items-center',
                    isApiEditing ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-blue-600 hover:bg-blue-700 text-white',
                    isApiSaving ? 'opacity-50 cursor-not-allowed' : ''
                  ]"
                >
                  <i :class="[
                    'fas mr-2', 
                    isApiSaving ? 'fa-spinner fa-spin' : (isApiEditing ? 'fa-check' : 'fa-edit')
                  ]"></i>
                  {{ isApiSaving ? 'Saving...' : (isApiEditing ? 'Finish' : 'Edit') }}
                </button>
                <button 
                  @click="regenerateApiKey"
                  :disabled="isApiSaving"
                  class="px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors flex items-center disabled:opacity-50"
                >
                  <i class="fas fa-sync-alt mr-2"></i>
                  Regenerate
                </button>
              </div>
            </div>
            
            <form @submit.prevent="saveApiChanges">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- API Key -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">API Key</label>
                  <div class="flex items-center">
                    <div class="flex-1 px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg font-mono text-sm">
                      {{ rvm.api_key ? '••••••••••••••••' : 'Not generated' }}
                    </div>
                    <button 
                      v-if="rvm.api_key"
                      @click="copyApiKey"
                      type="button"
                      class="ml-2 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors"
                    >
                      <i class="fas fa-copy"></i>
                    </button>
                  </div>
                </div>

                <!-- API Key Expires -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">API Key Expires</label>
                  <select 
                    v-if="isApiEditing"
                    v-model="apiEditForm.expiration_period"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    required
                  >
                    <option value="30">+30 days</option>
                    <option value="90">+3 months</option>
                    <option value="270">+9 months</option>
                    <option value="365">+1 year</option>
                  </select>
                  <div v-else class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg">
                    {{ rvm.api_key_expires_at ? formatDate(rvm.api_key_expires_at) : 'Not set' }}
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Status & Connection Info -->
        <div class="space-y-6">
          <!-- Status Indicators -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Status Indicators</h2>
            
            <div class="space-y-4">
              <!-- Operational Status -->
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">Operational Status</span>
                <span :class="getStatusBadgeClass(rvm.status)">
                  {{ rvm.status }}
                </span>
              </div>

              <!-- Connection Status -->
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">Connection Status</span>
                <div class="flex items-center">
                  <div :class="[
                    'w-3 h-3 rounded-full mr-2',
                    rvm.connection_status === 'connected' ? 'bg-green-500 status-pulse' : 'bg-red-500'
                  ]"></div>
                  <span :class="[
                    'text-sm font-medium',
                    rvm.connection_status === 'connected' ? 'text-green-700' : 'text-red-700'
                  ]">
                    {{ rvm.connection_status === 'connected' ? 'Connected' : 'Disconnected' }}
                  </span>
                </div>
              </div>

              <!-- API Status -->
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">API Status</span>
                <div class="flex items-center">
                  <div :class="[
                    'w-3 h-3 rounded-full mr-2',
                    rvm.api_status === 'valid' ? 'bg-blue-500 status-pulse' : 'bg-orange-500'
                  ]"></div>
                  <span :class="[
                    'text-sm font-medium',
                    rvm.api_status === 'valid' ? 'text-blue-700' : 'text-orange-700'
                  ]">
                    {{ rvm.api_status === 'valid' ? 'Valid' : 'Invalid' }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Last Activity -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-lg font-semibold text-gray-900">Last Activity</h2>
              <div class="flex items-center text-xs text-gray-500">
                <div :class="[
                  'w-2 h-2 rounded-full mr-2',
                  isRefreshing ? 'bg-yellow-500 animate-pulse' : 'bg-green-500'
                ]"></div>
                <span>{{ isRefreshing ? 'Checking...' : 'Auto-refresh: 30s' }}</span>
              </div>
            </div>
            
            <div class="space-y-4">
              <!-- Last Ping -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Last Ping</label>
                <div class="text-sm text-gray-600">
                  {{ getLastPingInfo(rvm) }}
                </div>
              </div>

              <!-- Last Connection Check -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Last Connection Check</label>
                <div class="text-sm text-gray-600">
                  {{ rvm.last_connection_check ? formatDate(rvm.last_connection_check) : 'Never' }}
                </div>
              </div>

              <!-- Last API Check -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Last API Check</label>
                <div class="text-sm text-gray-600">
                  {{ rvm.last_api_check ? formatDate(rvm.last_api_check) : 'Never' }}
                </div>
              </div>
            </div>
          </div>

          <!-- System Information -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">System Information</h2>
            
            <div class="space-y-4">
              <!-- Created At -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Created At</label>
                <div class="text-sm text-gray-600">
                  {{ formatDate(rvm.created_at) }}
                </div>
              </div>

              <!-- Updated At -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Updated At</label>
                <div class="text-sm text-gray-600">
                  {{ formatDate(rvm.updated_at) }}
                </div>
              </div>

              <!-- RVM ID -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">RVM ID</label>
                <div class="text-sm text-gray-600 font-mono">
                  {{ rvm.id }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Regenerate API Key Modal -->
  <div v-if="showRegenerateApiKeyModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
      <!-- Modal Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">API Key for Jetson Integration</h3>
        <button @click="closeRegenerateApiKeyModal" class="text-gray-400 hover:text-gray-600 transition-colors">
          <i class="fas fa-times text-xl"></i>
        </button>
      </div>
      <!-- Modal Body -->
      <div class="p-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          API Key for Jetson Integration
        </label>
        <div class="flex items-center space-x-2">
          <input
            :value="regeneratedApiKey"
            readonly
            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 font-mono text-sm"
          />
          <button
            @click="copyRegeneratedApiKey"
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
      <!-- Modal Footer -->
      <div class="flex justify-end space-x-3 p-6 border-t border-gray-200">
        <button
          @click="closeRegenerateApiKeyModal"
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
        >
          Close
        </button>
        <button
          @click="finishRegenerateApiKey"
          class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors flex items-center"
        >
          <i class="fas fa-check mr-2"></i>
          Done
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  rvm: Object,
  csrf_token: String
})

// Edit mode state
const isEditing = ref(false)
const isSaving = ref(false)
const editForm = ref({
  name: '',
  location: '',
  ip_address: ''
})

// Capacity edit mode state
const isCapacityEditing = ref(false)
const isCapacitySaving = ref(false)
const capacityEditForm = ref({
  current_load: 0
})

// API edit mode state
const isApiEditing = ref(false)
const isApiSaving = ref(false)
const apiEditForm = ref({
  expiration_period: '30'
})

// Regenerate API Key Modal state
const showRegenerateApiKeyModal = ref(false)
const regeneratedApiKey = ref('')

// Auto-refresh state
const isRefreshing = ref(false)
const lastRefreshTime = ref(null)
const refreshInterval = ref(null)

// Computed property for usage percentage (fixed capacity = 100)
const calculatedUsagePercentage = computed(() => {
  if (isCapacityEditing.value) {
    return (capacityEditForm.value.current_load / 100) * 100
  }
  return props.rvm.capacity_percentage || 0
})

// Helper functions
const formatDate = (dateString) => {
  if (!dateString) return 'Not available'
  const date = new Date(dateString)
  return date.toLocaleString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatTimeAgo = (dateString) => {
  if (!dateString) return 'No data'
  const date = new Date(dateString)
  const now = new Date()
  const diffInSeconds = Math.floor((now - date) / 1000)
  
  if (diffInSeconds < 60) return `${diffInSeconds} seconds ago`
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`
  return `${Math.floor(diffInSeconds / 86400)} days ago`
}

const getLastPingInfo = (rvm) => {
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

const getStatusBadgeClass = (status) => {
  const classes = {
    active: 'px-2 py-1 text-xs rounded-full font-medium bg-green-100 text-green-800',
    inactive: 'px-2 py-1 text-xs rounded-full font-medium bg-gray-100 text-gray-800',
    maintenance: 'px-2 py-1 text-xs rounded-full font-medium bg-yellow-100 text-yellow-800',
    error: 'px-2 py-1 text-xs rounded-full font-medium bg-red-100 text-red-800'
  }
  return classes[status] || classes.inactive
}

// Actions
const goBack = () => {
  router.get('/dashboard')
}

const enterMaintenance = () => {
  if (confirm('Are you sure you want to put this RVM into maintenance mode?')) {
    // Navigate directly to maintenance page
    // The maintenance page will automatically set status to 'maintenance'
    router.get(`/maintenance/${props.rvm.id}`)
  }
}

const enterPlayground = () => {
  // TODO: Implement playground functionality
  alert('Playground functionality will be implemented in future updates')
}

const copyApiKey = () => {
  if (navigator.clipboard && window.isSecureContext) {
    navigator.clipboard.writeText(props.rvm.api_key).then(() => {
      alert('API key copied to clipboard!')
    }).catch(err => {
      console.error('Failed to copy API key:', err)
      alert('Failed to copy API key')
    })
  } else {
    // Fallback for older browsers
    const textArea = document.createElement("textarea")
    textArea.value = props.rvm.api_key
    textArea.style.position = "fixed"
    textArea.style.opacity = "0"
    document.body.appendChild(textArea)
    textArea.focus()
    textArea.select()
    try {
      document.execCommand('copy')
      alert('API key copied to clipboard!')
    } catch (err) {
      console.error('Fallback: Could not copy API key:', err)
      alert('Failed to copy API key')
    }
    document.body.removeChild(textArea)
  }
}

// Edit mode functions
const toggleEditMode = () => {
  if (isEditing.value) {
    // If currently editing, save changes
    saveChanges()
  } else {
    // Enter edit mode
    isEditing.value = true
    editForm.value = {
      name: props.rvm.name,
      location: props.rvm.location,
      ip_address: props.rvm.ip_address || ''
    }
  }
}

const saveChanges = () => {
  isSaving.value = true
  
  // Add CSRF token to the request
  const formData = {
    ...editForm.value,
    _token: props.csrf_token
  }
  
  router.put(`/rvms/${props.rvm.id}`, formData, {
    onSuccess: (page) => {
      isEditing.value = false
      isSaving.value = false
      // Show success message
      alert('RVM updated successfully!')
      // The page will be automatically updated with new data from Inertia response
    },
    onError: (errors) => {
      isSaving.value = false
      console.error('Failed to update RVM:', errors)
      
      // Check if it's an authentication error
      if (errors.message && errors.message.includes('Unauthenticated')) {
        alert('Session expired. Please login again.')
        window.location.href = '/login'
        return
      }
      
      // Check if it's a CSRF error
      if (errors.message && errors.message.includes('419')) {
        alert('CSRF token expired. Please refresh the page and try again.')
        window.location.reload()
        return
      }
      
      // Check if it's a redirect to login (status 302)
      if (errors.status === 302 || (errors.message && errors.message.includes('login'))) {
        alert('Authentication required. Redirecting to login...')
        window.location.href = '/login'
        return
      }
      
      // Check if response is HTML (redirect to login)
      if (typeof errors === 'string' && errors.includes('login')) {
        alert('Authentication required. Redirecting to login...')
        window.location.href = '/login'
        return
      }
      
      // Display specific validation errors
      let errorMessage = 'Failed to update RVM:\n'
      for (const [field, messages] of Object.entries(errors)) {
        if (Array.isArray(messages)) {
          errorMessage += `${field}: ${messages.join(', ')}\n`
        } else {
          errorMessage += `${field}: ${messages}\n`
        }
      }
      alert(errorMessage)
    }
  })
}

// Capacity edit mode functions
const toggleCapacityEditMode = () => {
  if (isCapacityEditing.value) {
    // If currently editing, save changes
    saveCapacityChanges()
  } else {
    // Enter edit mode
    isCapacityEditing.value = true
    capacityEditForm.value = {
      current_load: props.rvm.current_load
    }
  }
}

const saveCapacityChanges = () => {
  isCapacitySaving.value = true
  
  // Add CSRF token to the request
  const formData = {
    current_load: capacityEditForm.value.current_load,
    _token: props.csrf_token
  }
  
  router.put(`/rvms/${props.rvm.id}`, formData, {
    onSuccess: (page) => {
      isCapacityEditing.value = false
      isCapacitySaving.value = false
      // Show success message
      alert('Capacity & Load updated successfully!')
      // The page will be automatically updated with new data from Inertia response
    },
    onError: (errors) => {
      isCapacitySaving.value = false
      console.error('Failed to update capacity & load:', errors)
      
      // Check if it's an authentication error
      if (errors.message && errors.message.includes('Unauthenticated')) {
        alert('Session expired. Please login again.')
        window.location.href = '/login'
        return
      }
      
      // Check if it's a CSRF error
      if (errors.message && errors.message.includes('419')) {
        alert('CSRF token expired. Please refresh the page and try again.')
        window.location.reload()
        return
      }
      
      // Check if it's a redirect to login (status 302)
      if (errors.status === 302 || (errors.message && errors.message.includes('login'))) {
        alert('Authentication required. Redirecting to login...')
        window.location.href = '/login'
        return
      }
      
      // Check if response is HTML (redirect to login)
      if (typeof errors === 'string' && errors.includes('login')) {
        alert('Authentication required. Redirecting to login...')
        window.location.href = '/login'
        return
      }
      
      // Display specific validation errors
      let errorMessage = 'Failed to update Capacity & Load:\n'
      for (const [field, messages] of Object.entries(errors)) {
        if (Array.isArray(messages)) {
          errorMessage += `${field}: ${messages.join(', ')}\n`
        } else {
          errorMessage += `${field}: ${messages}\n`
        }
      }
      alert(errorMessage)
    }
  })
}

const resetCapacityLoad = () => {
  if (confirm('Are you sure you want to reset Current Load to 0? This will set Usage Percentage to 0%.')) {
    isCapacitySaving.value = true
    
    // Add CSRF token to the request
    const formData = {
      current_load: 0,
      _token: props.csrf_token
    }
    
    router.put(`/rvms/${props.rvm.id}`, formData, {
      onSuccess: (page) => {
        isCapacityEditing.value = false
        isCapacitySaving.value = false
        // Show success message
        alert('Capacity & Load reset successfully!')
        // The page will be automatically updated with new data from Inertia response
      },
      onError: (errors) => {
        isCapacitySaving.value = false
        console.error('Failed to reset capacity & load:', errors)
        alert('Failed to reset capacity & load. Please try again.')
      }
    })
  }
}

// API edit mode functions
const toggleApiEditMode = () => {
  if (isApiEditing.value) {
    // If currently editing, save changes
    saveApiChanges()
  } else {
    // Enter edit mode
    isApiEditing.value = true
    apiEditForm.value = {
      expiration_period: '30' // Default to 30 days
    }
  }
}

const saveApiChanges = () => {
  isApiSaving.value = true
  
  // Add CSRF token to the request
  const formData = {
    api_expiration_period: parseInt(apiEditForm.value.expiration_period),
    _token: props.csrf_token
  }
  
  router.put(`/rvms/${props.rvm.id}/api`, formData, {
    onSuccess: (page) => {
      isApiEditing.value = false
      isApiSaving.value = false
      // Show success message
      alert('API settings updated successfully!')
      // The page will be automatically updated with new data from Inertia response
    },
    onError: (errors) => {
      isApiSaving.value = false
      console.error('Failed to update API settings:', errors)
      
      // Check if it's an authentication error
      if (errors.message && errors.message.includes('Unauthenticated')) {
        alert('Session expired. Please login again.')
        window.location.href = '/login'
        return
      }
      
      // Check if it's a CSRF error
      if (errors.message && errors.message.includes('419')) {
        alert('CSRF token expired. Please refresh the page and try again.')
        window.location.reload()
        return
      }
      
      // Check if it's a redirect to login (status 302)
      if (errors.status === 302 || (errors.message && errors.message.includes('login'))) {
        alert('Authentication required. Redirecting to login...')
        window.location.href = '/login'
        return
      }
      
      // Check if response is HTML (redirect to login)
      if (typeof errors === 'string' && errors.includes('login')) {
        alert('Authentication required. Redirecting to login...')
        window.location.href = '/login'
        return
      }
      
      // Display specific validation errors
      let errorMessage = 'Failed to update API settings:\n'
      for (const [field, messages] of Object.entries(errors)) {
        if (Array.isArray(messages)) {
          errorMessage += `${field}: ${messages.join(', ')}\n`
        } else {
          errorMessage += `${field}: ${messages}\n`
        }
      }
      alert(errorMessage)
    }
  })
}

const regenerateApiKey = () => {
  if (confirm('Are you sure you want to regenerate the API key? The current API key will be invalidated immediately.')) {
    isApiSaving.value = true
    
    // Add CSRF token to the request
    const formData = {
      regenerate_api_key: true,
      api_expiration_period: parseInt(apiEditForm.value.expiration_period) || 30,
      _token: props.csrf_token
    }
    
    router.put(`/rvms/${props.rvm.id}/api`, formData, {
      onSuccess: (page) => {
        isApiEditing.value = false
        isApiSaving.value = false
        
        // Extract new API key from response
        const newRvmData = page.props.rvm
        if (newRvmData && newRvmData.api_key) {
          regeneratedApiKey.value = newRvmData.api_key
          showRegenerateApiKeyModal.value = true
        } else {
          // Fallback: reload page if API key not found in response
          alert('API key regenerated successfully!')
          router.reload()
        }
      },
      onError: (errors) => {
        isApiSaving.value = false
        console.error('Failed to regenerate API key:', errors)
        
        // Check if it's an authentication error
        if (errors.message && errors.message.includes('Unauthenticated')) {
          alert('Session expired. Please login again.')
          window.location.href = '/login'
          return
        }
        
        // Check if it's a CSRF error
        if (errors.message && errors.message.includes('419')) {
          alert('CSRF token expired. Please refresh the page and try again.')
          window.location.reload()
          return
        }
        
        // Check if it's a redirect to login (status 302)
        if (errors.status === 302 || (errors.message && errors.message.includes('login'))) {
          alert('Authentication required. Redirecting to login...')
          window.location.href = '/login'
          return
        }
        
        // Check if response is HTML (redirect to login)
        if (typeof errors === 'string' && errors.includes('login')) {
          alert('Authentication required. Redirecting to login...')
          window.location.href = '/login'
          return
        }
        
        alert('Failed to regenerate API key. Please try again.')
      }
    })
  }
}

// Regenerate API Key Modal functions
const closeRegenerateApiKeyModal = () => {
  showRegenerateApiKeyModal.value = false
  regeneratedApiKey.value = ''
}

const copyRegeneratedApiKey = () => {
  if (navigator.clipboard && window.isSecureContext) {
    navigator.clipboard.writeText(regeneratedApiKey.value).then(() => {
      alert('API Key copied to clipboard!')
    }).catch(err => {
      console.error('Failed to copy API Key:', err)
      fallbackCopyTextToClipboard(regeneratedApiKey.value)
    })
  } else {
    fallbackCopyTextToClipboard(regeneratedApiKey.value)
  }
}

const finishRegenerateApiKey = () => {
  closeRegenerateApiKeyModal()
  // Navigate back to the same RVM details page to refresh data
  router.get(`/rvms/${props.rvm.id}`)
}

// Format time for display
const formatTime = (date) => {
  if (!date) return 'Never'
  const now = new Date()
  const diff = Math.floor((now - date) / 1000)
  
  if (diff < 60) return `${diff}s ago`
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`
  return date.toLocaleTimeString()
}

// Auto-refresh functions
const startAutoRefresh = () => {
  if (refreshInterval.value) {
    clearInterval(refreshInterval.value)
  }
  
  refreshInterval.value = setInterval(async () => {
    await performStatusCheck()
  }, 30000) // 30 seconds
}

const stopAutoRefresh = () => {
  if (refreshInterval.value) {
    clearInterval(refreshInterval.value)
    refreshInterval.value = null
  }
}

const performStatusCheck = async () => {
  if (isRefreshing.value) return
  
  try {
    isRefreshing.value = true
    
    // Trigger status check API
    const response = await fetch('/api/rvm/check-status', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({
        rvm_id: props.rvm.id
      })
    })
    
    if (response.ok) {
      // Reload RVM data after status check
      router.reload({
        only: ['rvm'],
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          lastRefreshTime.value = new Date()
        }
      })
    } else {
      console.error('Status check failed:', response.statusText)
    }
  } catch (error) {
    console.error('Error during status check:', error)
  } finally {
    isRefreshing.value = false
  }
}

// Lifecycle hooks
onMounted(() => {
  startAutoRefresh()
})

onUnmounted(() => {
  stopAutoRefresh()
})

// Fallback for copying text
const fallbackCopyTextToClipboard = (text) => {
  const textArea = document.createElement("textarea")
  textArea.value = text
  textArea.style.position = "fixed"
  textArea.style.opacity = "0"
  document.body.appendChild(textArea)
  textArea.focus()
  textArea.select()
  try {
    const successful = document.execCommand('copy')
    if (successful) {
      alert('API Key copied to clipboard (fallback)!')
    } else {
      console.error('Fallback: Could not copy text')
    }
  } catch (err) {
    console.error('Fallback: Could not copy text: ', err)
  }
  document.body.removeChild(textArea)
}
</script>

<style scoped>
/* Custom styles if needed */

/* Pulse animation for status indicators */
.status-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}
</style>
