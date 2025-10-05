<template>
  <div class="min-h-screen bg-gradient-to-br from-purple-50 to-blue-50">
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
              <div class="w-10 h-10 bg-gradient-to-r from-purple-500 via-blue-500 to-cyan-500 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-play text-white text-lg"></i>
              </div>
              <div>
                <h1 class="text-2xl font-bold bg-gradient-to-r from-purple-900 to-blue-700 bg-clip-text text-transparent">Computer Vision Playground</h1>
                <p class="text-sm text-gray-500">Remote Camera Detection & Model Testing</p>
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
              {{ isRefreshing ? 'Refreshing...' : 'Refresh Status' }}
            </button>
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
      <!-- RVM Information Card -->
      <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-gray-200/50 p-8 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-purple-50/50 to-blue-50/50"></div>
        <div class="relative">
          <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-blue-500 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-robot text-white text-xl"></i>
              </div>
              <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ rvm.name }}</h2>
                <p class="text-gray-600">{{ rvm.location }}</p>
                <p class="text-sm text-gray-500">RVM ID: #{{ rvm.id }}</p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <span :class="[
                'px-3 py-1 rounded-full text-sm font-medium',
                rvm.status === 'active' ? 'bg-green-100 text-green-800' : 
                rvm.status === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : 
                'bg-red-100 text-red-800'
              ]">
                {{ rvm.status }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Playground Content -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Left Column: Camera & Detection -->
        <div class="space-y-6">
          <!-- Live Camera Feed -->
          <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-gray-200/50 p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-video mr-2 text-blue-500"></i>
                Live Camera Feed
              </h3>
              <div class="flex items-center space-x-2">
                <div :class="[
                  'w-3 h-3 rounded-full',
                  jetsonCameraInfo?.camera_ready ? 'bg-green-500' : 'bg-red-500'
                ]"></div>
                <span class="text-sm text-gray-600">
                  {{ jetsonCameraInfo?.camera_ready ? `Camera Ready (${jetsonCameraInfo?.total_cameras || 0} cameras)` : 'Camera Not Available' }}
                </span>
              </div>
            </div>
            
            <!-- Camera Selection -->
            <div v-if="jetsonCameraInfo?.cameras_available?.length > 0" class="mb-4 p-4 bg-gray-50 rounded-lg">
              <h4 class="text-sm font-medium text-gray-700 mb-3">Select Camera:</h4>
              <div class="space-y-3 max-w-full overflow-hidden">
                <!-- Camera Dropdown -->
                <div class="space-y-2">
                  <label class="text-sm font-medium text-gray-600">Camera:</label>
                  <select 
                    v-model="selectedCameraId" 
                    @change="onCameraSelectionChange"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm truncate"
                  >
                    <option value="">Select a camera...</option>
                    <option v-for="camera in jetsonCameraInfo.cameras_available" :key="camera.id" :value="camera.id" :title="`${camera.name} (${camera.path})`">
                      {{ camera.name.length > 30 ? camera.name.substring(0, 30) + '...' : camera.name }} ({{ camera.path }})
                    </option>
                  </select>
                </div>
                
                <!-- Selected Camera Info -->
                <div v-if="selectedCamera" class="p-3 bg-white rounded-lg border">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                      <div :class="[
                        'w-2 h-2 rounded-full',
                        selectedCamera.status === 'active' ? 'bg-green-500' : 'bg-yellow-500'
                      ]"></div>
                      <span class="text-sm font-medium">{{ selectedCamera.name }}</span>
                      <span class="text-xs text-gray-500">({{ selectedCamera.path }})</span>
                    </div>
                    <div class="flex items-center space-x-1">
                      <span v-if="selectedCamera.is_streaming" class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Streaming</span>
                      <span v-else class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">Idle</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Camera Feed Display -->
            <div class="bg-black rounded-lg aspect-video flex items-center justify-center relative overflow-hidden">
              <div v-if="!cameraActive" class="text-center text-white">
                <i class="fas fa-video-slash text-4xl mb-4 opacity-50"></i>
                <p class="text-lg">Camera Feed Not Active</p>
                <p class="text-sm opacity-75">Click "Start Camera" to begin</p>
              </div>
              <div v-else-if="cameraLoading" class="w-full h-full bg-gray-800 flex items-center justify-center">
                <div class="text-center text-white">
                  <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                  <p>Starting camera...</p>
                </div>
              </div>
              <div v-else class="w-full h-full bg-gray-800 flex items-center justify-center">
                <div class="text-center text-white">
                  <i class="fas fa-video text-4xl mb-4 text-green-500"></i>
                  <p class="text-lg">Camera Active</p>
                  <p class="text-sm opacity-75">{{ selectedCamera?.name || 'Camera' }} is ready</p>
                  <div class="mt-4 flex items-center justify-center space-x-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-xs">Live</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Camera Error Display -->
            <div v-if="cameraError" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
              <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                <span class="text-red-700 font-medium">Camera Error:</span>
              </div>
              <p class="text-red-600 text-sm mt-1">{{ cameraError }}</p>
              <div v-if="cameraError.includes('OpenCV not installed')" class="mt-2 text-xs text-red-500">
                <p>To fix this issue:</p>
                <ul class="list-disc list-inside ml-2">
                  <li>Install OpenCV on the Jetson device</li>
                  <li>Run: <code class="bg-red-100 px-1 rounded">pip install opencv-python</code></li>
                  <li>Restart the Jetson API service</li>
                </ul>
              </div>
            </div>

            <!-- Camera Controls -->
            <div class="mt-4 flex flex-wrap gap-2">
              <button 
                @click="startCamera"
                :disabled="!selectedCameraId || cameraActive || cameraLoading"
                class="px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white rounded-lg transition-colors flex items-center"
              >
                <i v-if="cameraLoading" class="fas fa-spinner fa-spin mr-2"></i>
                <i v-else class="fas fa-play mr-2"></i>
                {{ cameraLoading ? 'Initializing...' : 'Start Camera' }}
              </button>
              <button 
                @click="stopCamera"
                :disabled="!cameraActive"
                class="px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white rounded-lg transition-colors flex items-center"
              >
                <i class="fas fa-stop mr-2"></i>
                Stop Camera
              </button>
              <button 
                @click="captureImage"
                :disabled="!cameraActive"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white rounded-lg transition-colors flex items-center"
              >
                <i class="fas fa-camera mr-2"></i>
                Capture Image
              </button>
            </div>
            
            <!-- Camera Selection Help -->
            <div v-if="!selectedCameraId" class="mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
              <div class="flex items-center">
                <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                <span class="text-sm text-yellow-700">Please select a camera from the dropdown above to enable camera controls.</span>
              </div>
            </div>
          </div>

          <!-- Detection Results -->
          <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-gray-200/50 p-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center mb-4">
              <i class="fas fa-search mr-2 text-green-500"></i>
              Detection Results
            </h3>
            
            <div v-if="detectionResults.length === 0" class="text-center py-8 text-gray-500">
              <i class="fas fa-search text-3xl mb-3 opacity-50"></i>
              <p>No detection results yet</p>
              <p class="text-sm">Start detection to see results</p>
            </div>
            
            <div v-else class="space-y-4">
              <div v-for="(result, index) in detectionResults" :key="index" 
                   class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                  <span class="font-medium text-gray-900">Detection {{ index + 1 }}</span>
                  <span class="text-sm text-gray-500">{{ result.timestamp }}</span>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm">
                  <div>
                    <span class="text-gray-600">Objects:</span>
                    <span class="font-medium">{{ result.objects_count }}</span>
                  </div>
                  <div>
                    <span class="text-gray-600">Confidence:</span>
                    <span class="font-medium">{{ result.avg_confidence }}%</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column: Controls & Models -->
        <div class="space-y-6">
          <!-- System Status -->
          <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-gray-200/50 p-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center mb-4">
              <i class="fas fa-chart-bar mr-2 text-purple-500"></i>
              System Status
            </h3>
            
            <div class="space-y-4">
              <!-- Jetson Edge Status -->
              <div class="p-4 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg border border-blue-200">
                <div class="flex items-center justify-between mb-2">
                  <div class="flex items-center space-x-3">
                    <i class="fas fa-microchip text-blue-500"></i>
                    <span class="font-medium text-gray-900">Jetson Edge</span>
                  </div>
                  <div class="flex items-center space-x-2">
                    <div :class="[
                      'w-3 h-3 rounded-full',
                      jetsonCameraInfo?.jetson_status === 'online' ? 'bg-green-500' : 'bg-red-500'
                    ]"></div>
                    <span class="text-sm font-medium">{{ jetsonCameraInfo?.jetson_status || 'offline' }}</span>
                  </div>
                </div>
                <div class="grid grid-cols-2 gap-2 text-sm text-gray-600">
                  <div>Cameras: {{ jetsonCameraInfo?.total_cameras || 0 }}</div>
                  <div>NVArgus: {{ jetsonCameraInfo?.nvargus_status || 'unknown' }}</div>
                </div>
                <div v-if="jetsonCameraStatus" class="mt-2 text-xs text-gray-500">
                  <div>Initialized: {{ jetsonCameraStatus.camera_initialized ? 'Yes' : 'No' }}</div>
                  <div>Streaming: {{ jetsonCameraStatus.camera_streaming ? 'Yes' : 'No' }}</div>
                </div>
              </div>

              <!-- GPU Server Status (Optional) -->
              <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border border-green-200">
                <div class="flex items-center justify-between mb-2">
                  <div class="flex items-center space-x-3">
                    <i class="fas fa-server text-green-500"></i>
                    <span class="font-medium text-gray-900">GPU Server</span>
                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Optional</span>
                  </div>
                  <div class="flex items-center space-x-2">
                    <div :class="[
                      'w-3 h-3 rounded-full',
                      gpuServerInfo?.server_status === 'online' ? 'bg-green-500' : 'bg-red-500'
                    ]"></div>
                    <span class="text-sm font-medium">{{ gpuServerInfo?.server_status || 'offline' }}</span>
                  </div>
                </div>
                <div class="grid grid-cols-2 gap-2 text-sm text-gray-600">
                  <div>GPU: {{ gpuServerInfo?.gpu_name || 'Unknown' }}</div>
                  <div>Memory: {{ gpuServerInfo?.gpu_memory || 0 }}GB</div>
                </div>
                <div class="text-xs text-gray-500 mt-1">
                  Available for all Jetson devices
                </div>
              </div>

              <!-- Detection Status -->
              <div class="p-4 bg-gradient-to-r from-yellow-50 to-orange-50 rounded-lg border border-yellow-200">
                <div class="flex items-center justify-between mb-2">
                  <div class="flex items-center space-x-3">
                    <i class="fas fa-eye text-yellow-500"></i>
                    <span class="font-medium text-gray-900">Detection</span>
                  </div>
                  <div class="flex items-center space-x-2">
                    <div :class="[
                      'w-3 h-3 rounded-full',
                      detectionActive ? 'bg-green-500' : 'bg-gray-400'
                    ]"></div>
                    <span class="text-sm font-medium">{{ detectionActive ? 'Active' : 'Stopped' }}</span>
                  </div>
                </div>
                <div class="text-sm text-gray-600">
                  Model: {{ selectedModel?.name || 'None selected' }}
                </div>
              </div>

              <!-- Hardware Info (if available) -->
              <div v-if="jetsonCameraInfo?.system_info" class="p-4 bg-gradient-to-r from-gray-50 to-slate-50 rounded-lg border border-gray-200">
                <div class="flex items-center space-x-3 mb-2">
                  <i class="fas fa-cogs text-gray-500"></i>
                  <span class="font-medium text-gray-900">Hardware Info</span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-sm text-gray-600">
                  <div v-if="jetsonCameraInfo.system_info.cpu_info">
                    CPU: {{ jetsonCameraInfo.system_info.cpu_info.model || 'Unknown' }}
                  </div>
                  <div v-if="jetsonCameraInfo.system_info.memory_info">
                    RAM: {{ jetsonCameraInfo.system_info.memory_info.total_gb || 0 }}GB
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Model Selection -->
          <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-gray-200/50 p-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center mb-4">
              <i class="fas fa-brain mr-2 text-indigo-500"></i>
              Available Models
            </h3>
            
            <div class="space-y-3">
              <div v-for="model in availableModels" :key="model.id" 
                   :class="[
                     'p-3 rounded-lg border-2 cursor-pointer transition-all',
                     selectedModel?.id === model.id 
                       ? 'border-blue-500 bg-blue-50' 
                       : 'border-gray-200 hover:border-gray-300'
                   ]"
                   @click="selectModel(model)">
                <div class="flex items-center justify-between">
                  <div>
                    <h4 class="font-medium text-gray-900">{{ model.name }}</h4>
                    <p class="text-sm text-gray-600">{{ model.description }}</p>
                  </div>
                  <div class="text-right">
                    <span class="text-xs text-gray-500">{{ model.size }}</span>
                    <div :class="[
                      'w-2 h-2 rounded-full mt-1',
                      model.status === 'available' ? 'bg-green-500' : 'bg-yellow-500'
                    ]"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Detection Controls -->
          <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-gray-200/50 p-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center mb-4">
              <i class="fas fa-gamepad mr-2 text-orange-500"></i>
              Detection Controls
            </h3>
            
            <div class="space-y-4">
              <div class="grid grid-cols-2 gap-3">
                <button 
                  @click="startDetection"
                  :disabled="!selectedModel || !jetsonCameraInfo?.camera_ready"
                  class="px-4 py-3 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white rounded-lg transition-colors flex items-center justify-center"
                >
                  <i class="fas fa-play mr-2"></i>
                  Start Detection
                </button>
                <button 
                  @click="stopDetection"
                  :disabled="!detectionActive"
                  class="px-4 py-3 bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white rounded-lg transition-colors flex items-center justify-center"
                >
                  <i class="fas fa-stop mr-2"></i>
                  Stop Detection
                </button>
              </div>
              
              <div class="grid grid-cols-2 gap-3">
                <button 
                  @click="uploadImage"
                  class="px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center justify-center"
                >
                  <i class="fas fa-upload mr-2"></i>
                  Upload Image
                </button>
                <button 
                  @click="downloadResults"
                  :disabled="detectionResults.length === 0"
                  class="px-4 py-3 bg-purple-600 hover:bg-purple-700 disabled:bg-gray-400 text-white rounded-lg transition-colors flex items-center justify-center"
                >
                  <i class="fas fa-download mr-2"></i>
                  Download Results
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  rvm: Object,
  jetsonCameraInfo: Object,
  jetsonCameraStatus: Object,
  gpuServerInfo: Object,
  availableModels: Array,
})

// Reactive state
const isRefreshing = ref(false)
const cameraActive = ref(false)
const detectionActive = ref(false)
const selectedModel = ref(null)
const cameraError = ref('')
const cameraLoading = ref(false)
const detectionResults = ref([])

// Camera selection
const selectedCameraId = ref('')

// Computed properties
const selectedCamera = computed(() => {
  if (!selectedCameraId.value || !props.jetsonCameraInfo?.cameras_available) {
    return null
  }
  return props.jetsonCameraInfo.cameras_available.find(camera => camera.id === selectedCameraId.value)
})

// Actions
const goBack = () => {
  router.get('/dashboard')
}

const refreshData = async () => {
  isRefreshing.value = true
  try {
    router.reload()
  } finally {
    isRefreshing.value = false
  }
}

const onCameraSelectionChange = () => {
  console.log('📷 Camera selection changed:', selectedCameraId.value)
  console.log('📷 Selected camera:', selectedCamera.value)
  
  // Reset camera state when selection changes
  if (cameraActive.value) {
    cameraActive.value = false
  }
  cameraError.value = ''
}

const startCamera = async () => {
  if (!selectedCameraId.value) {
    cameraError.value = 'Please select a camera first'
    return
  }
  
  try {
    cameraLoading.value = true
    cameraError.value = ''
    console.log('🎥 Attempting to start camera:', selectedCameraId.value)
    
    // Start camera using the selected camera ID
    const startResponse = await fetch(`http://${props.rvm.ip_address}:5000/api/cameras/${selectedCameraId.value}/start`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    })
    
    if (startResponse.ok) {
      const startData = await startResponse.json()
      console.log('📋 Camera start response:', startData)
      
      if (startData.success) {
        cameraActive.value = true
        cameraLoading.value = false
        console.log('✅ Camera started successfully')
        refreshData() // Refresh to get latest status
      } else {
        console.error('❌ Failed to start camera:', startData.error)
        cameraError.value = startData.error || 'Failed to start camera'
        cameraLoading.value = false
      }
    } else {
      console.error('❌ Camera start failed - HTTP error')
      cameraError.value = 'Camera start failed - HTTP error'
      cameraLoading.value = false
    }
  } catch (error) {
    console.error('❌ Error starting camera:', error)
    cameraError.value = 'Network error: ' + error.message
    cameraLoading.value = false
  }
}

const stopCamera = async () => {
  if (!selectedCameraId.value) {
    return
  }
  
  try {
    const response = await fetch(`http://${props.rvm.ip_address}:5000/api/cameras/${selectedCameraId.value}/stop`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    })
    
    if (response.ok) {
      cameraActive.value = false
      console.log('✅ Camera stopped successfully')
      refreshData() // Refresh to get latest status
    } else {
      console.error('❌ Failed to stop camera')
    }
  } catch (error) {
    console.error('❌ Error stopping camera:', error)
  }
}

const captureImage = async () => {
  if (!cameraActive.value || !selectedCameraId.value) {
    return
  }
  
  try {
    console.log('📸 Attempting to capture image from camera:', selectedCameraId.value)
    
    // Use Laravel endpoint that saves to storage
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    const response = await fetch(`/playground/${props.rvm.id}/cameras/${selectedCameraId.value}/capture-save`, {
      method: 'POST',
      headers: { 
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      console.log('✅ Image captured and saved successfully:', data)
      if (data.success) {
        console.log('📁 Image saved to storage:', data.storage_path)
        console.log('🌐 Public URL:', data.public_url)
        console.log('📂 File path:', data.file_path)
        console.log('⏰ Timestamp:', data.timestamp)
        
        // TODO: Display captured image or add to results
        // You can now access the image via data.public_url
      } else {
        console.error('❌ Capture failed:', data.error)
        cameraError.value = data.error || 'Failed to capture image'
      }
    } else {
      console.error('❌ Failed to capture image - HTTP error')
      cameraError.value = 'Capture failed - HTTP error'
    }
  } catch (error) {
    console.error('❌ Error capturing image:', error)
    cameraError.value = 'Network error: ' + error.message
  }
}

const selectModel = (model) => {
  selectedModel.value = model
  console.log('Selected model:', model.name)
}

const startDetection = () => {
  if (selectedModel.value && jetsonCameraInfo.value?.camera_ready) {
    detectionActive.value = true
    // TODO: Implement actual detection
    console.log('Starting detection with model:', selectedModel.value.name)
  }
}

const stopDetection = () => {
  detectionActive.value = false
  // TODO: Implement detection stop
  console.log('Stopping detection...')
}

const uploadImage = () => {
  // TODO: Implement image upload
  console.log('Uploading image...')
}

const downloadResults = () => {
  if (detectionResults.value.length > 0) {
    // TODO: Implement results download
    console.log('Downloading results...')
  }
}

// Lifecycle
onMounted(() => {
  console.log('Playground mounted for RVM:', props.rvm.name)
})

onUnmounted(() => {
  if (cameraActive.value) {
    stopCamera()
  }
  if (detectionActive.value) {
    stopDetection()
  }
})
</script>

<style scoped>
/* Custom styles for playground */
.aspect-video {
  aspect-ratio: 16 / 9;
}
</style>
