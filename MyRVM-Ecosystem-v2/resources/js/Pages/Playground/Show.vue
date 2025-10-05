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
                  {{ jetsonCameraInfo?.camera_ready ? 'Camera Ready' : 'Camera Not Available' }}
                </span>
              </div>
            </div>
            
            <!-- Camera Feed Display -->
            <div class="bg-black rounded-lg aspect-video flex items-center justify-center relative overflow-hidden">
              <div v-if="!cameraActive" class="text-center text-white">
                <i class="fas fa-video-slash text-4xl mb-4 opacity-50"></i>
                <p class="text-lg">Camera Feed Not Active</p>
                <p class="text-sm opacity-75">Click "Start Camera" to begin</p>
              </div>
              <div v-else class="w-full h-full bg-gray-800 flex items-center justify-center">
                <div class="text-center text-white">
                  <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                  <p>Connecting to camera...</p>
                </div>
              </div>
            </div>

            <!-- Camera Controls -->
            <div class="mt-4 flex flex-wrap gap-2">
              <button 
                @click="startCamera"
                :disabled="!jetsonCameraInfo?.camera_ready || cameraActive"
                class="px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white rounded-lg transition-colors flex items-center"
              >
                <i class="fas fa-play mr-2"></i>
                Start Camera
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
              <!-- Jetson Status -->
              <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-3">
                  <i class="fas fa-microchip text-blue-500"></i>
                  <span class="font-medium">Jetson Edge</span>
                </div>
                <div class="flex items-center space-x-2">
                  <div :class="[
                    'w-2 h-2 rounded-full',
                    jetsonCameraInfo?.camera_ready ? 'bg-green-500' : 'bg-red-500'
                  ]"></div>
                  <span class="text-sm">{{ jetsonCameraInfo?.total_cameras || 0 }} cameras</span>
                </div>
              </div>

              <!-- GPU Server Status -->
              <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-3">
                  <i class="fas fa-server text-green-500"></i>
                  <span class="font-medium">GPU Server</span>
                </div>
                <div class="flex items-center space-x-2">
                  <div :class="[
                    'w-2 h-2 rounded-full',
                    gpuServerInfo?.gpu_available ? 'bg-green-500' : 'bg-red-500'
                  ]"></div>
                  <span class="text-sm">{{ gpuServerInfo?.gpu_name || 'Unknown' }}</span>
                </div>
              </div>

              <!-- Detection Status -->
              <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-3">
                  <i class="fas fa-eye text-yellow-500"></i>
                  <span class="font-medium">Detection</span>
                </div>
                <div class="flex items-center space-x-2">
                  <div :class="[
                    'w-2 h-2 rounded-full',
                    detectionActive ? 'bg-green-500' : 'bg-gray-400'
                  ]"></div>
                  <span class="text-sm">{{ detectionActive ? 'Active' : 'Stopped' }}</span>
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
import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  rvm: Object,
  jetsonCameraInfo: Object,
  gpuServerInfo: Object,
  availableModels: Array,
})

// Reactive state
const isRefreshing = ref(false)
const cameraActive = ref(false)
const detectionActive = ref(false)
const selectedModel = ref(null)
const detectionResults = ref([])

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

const startCamera = () => {
  cameraActive.value = true
  // TODO: Implement actual camera connection
  console.log('Starting camera...')
}

const stopCamera = () => {
  cameraActive.value = false
  // TODO: Implement camera disconnection
  console.log('Stopping camera...')
}

const captureImage = () => {
  if (cameraActive.value) {
    // TODO: Implement image capture
    console.log('Capturing image...')
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
