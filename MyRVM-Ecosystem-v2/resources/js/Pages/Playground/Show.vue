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
              <h4 class="text-sm font-medium text-gray-700 mb-3">Camera & Streaming Settings:</h4>
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
                
                <!-- Streaming Mode Selection -->
                <div class="space-y-2">
                  <label class="text-sm font-medium text-gray-600">Streaming Mode:</label>
                  <div class="grid grid-cols-3 gap-2">
                    <button 
                      @click="streamMode = 'mjpeg'"
                      :class="[
                        'px-3 py-2 text-xs rounded-lg border transition-all',
                        streamMode === 'mjpeg' 
                          ? 'bg-blue-500 text-white border-blue-500' 
                          : 'bg-white text-gray-700 border-gray-300 hover:border-blue-300'
                      ]"
                    >
                      MJPEG
                      <div class="text-xs opacity-75">10-30 FPS</div>
                    </button>
                    <button 
                      @click="streamMode = 'base64'"
                      :class="[
                        'px-3 py-2 text-xs rounded-lg border transition-all',
                        streamMode === 'base64' 
                          ? 'bg-green-500 text-white border-green-500' 
                          : 'bg-white text-gray-700 border-gray-300 hover:border-green-300'
                      ]"
                    >
                      Base64
                      <div class="text-xs opacity-75">5 FPS</div>
                    </button>
                    <button 
                      @click="streamMode = 'auto'"
                      :class="[
                        'px-3 py-2 text-xs rounded-lg border transition-all',
                        streamMode === 'auto' 
                          ? 'bg-purple-500 text-white border-purple-500' 
                          : 'bg-white text-gray-700 border-gray-300 hover:border-purple-300'
                      ]"
                    >
                      Auto
                      <div class="text-xs opacity-75">Smart</div>
                    </button>
                  </div>
                  <div class="text-xs text-gray-500">
                    <span v-if="streamMode === 'mjpeg'">🎥 Direct MJPEG stream - Best performance</span>
                    <span v-else-if="streamMode === 'base64'">🔄 Base64 polling - Reliable fallback</span>
                    <span v-else>🤖 Auto-detect best method</span>
                  </div>
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
              <div v-else-if="isStreaming && streamUrl" class="w-full h-full relative">
                <!-- Live Video Stream - MJPEG or Base64 -->
                <img 
                  :src="streamUrl" 
                  alt="Live Camera Feed"
                  class="w-full h-full object-cover"
                  @error="handleStreamError"
                  @load="handleStreamLoad"
                  @loadstart="handleStreamLoadStart"
                  style="background: #000; min-height: 100%; min-width: 100%;"
                  :key="streamUrl"
                />
                <!-- Debug overlay -->
                <div class="absolute top-2 left-2 bg-black/50 text-white px-2 py-1 rounded text-xs">
                  <div>Mode: {{ streamMode }}</div>
                  <div>FPS: {{ streamFPS }}</div>
                  <div>URL: {{ streamUrl.substring(0, 30) }}...</div>
                </div>
                <!-- Live Indicator -->
                <div class="absolute top-2 right-2 flex items-center space-x-1 bg-red-600 text-white px-2 py-1 rounded-full text-xs">
                  <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                  <span>LIVE</span>
                </div>
                <!-- Streaming Mode Indicator -->
                <div class="absolute top-2 left-2 flex items-center space-x-1 bg-black/70 text-white px-2 py-1 rounded text-xs">
                  <span v-if="streamMode === 'mjpeg'">🎥 MJPEG</span>
                  <span v-else-if="streamMode === 'base64'">🔄 Base64</span>
                  <span v-else>🤖 Auto</span>
                  <span v-if="streamFPS > 0" class="ml-1 opacity-75">{{ streamFPS }} FPS</span>
                </div>
                <!-- Camera Info Overlay -->
                <div class="absolute bottom-2 left-2 bg-black/70 text-white px-2 py-1 rounded text-xs">
                  {{ selectedCamera?.name || 'Camera' }}
                </div>
              </div>
              <div v-else class="w-full h-full bg-gray-800 flex items-center justify-center">
                <div class="text-center text-white">
                  <i class="fas fa-video text-4xl mb-4 text-green-500"></i>
                  <p class="text-lg">Camera Active</p>
                  <p class="text-sm opacity-75">{{ selectedCamera?.name || 'Camera' }} is ready</p>
                  <div class="mt-4 flex items-center justify-center space-x-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-xs">Ready for streaming</span>
                  </div>
                  <div v-if="streamUrl && !isStreaming" class="mt-2 text-xs text-yellow-400">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Stream URL set but not active
                  </div>
                  <div class="mt-2 text-xs text-gray-400">
                    Debug: isStreaming={{ isStreaming }}, hasUrl={{ !!streamUrl }}
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
              <span v-if="cvResults.length > 0" class="ml-2 bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                {{ cvResults.length }} results
              </span>
            </h3>
            
            <div v-if="cvResults.length === 0" class="text-center py-8 text-gray-500">
              <i class="fas fa-search text-3xl mb-3 opacity-50"></i>
              <p>No detection results yet</p>
              <p class="text-sm">Start detection to see results</p>
            </div>
            
            <div v-else class="space-y-4 max-h-96 overflow-y-auto">
              <div v-for="result in cvResults" :key="result.id" 
                   class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-center justify-between mb-2">
                  <span class="font-medium text-gray-900">Detection #{{ result.id }}</span>
                  <span class="text-sm text-gray-500">{{ new Date(result.timestamp).toLocaleTimeString() }}</span>
                </div>
                
                <div class="grid grid-cols-2 gap-4 text-sm mb-3">
                  <div>
                    <span class="text-gray-600">Objects:</span>
                    <span class="font-medium">{{ result.objects.length }}</span>
                  </div>
                  <div>
                    <span class="text-gray-600">Confidence:</span>
                    <span class="font-medium">{{ result.confidence }}%</span>
                  </div>
                  <div>
                    <span class="text-gray-600">Model:</span>
                    <span class="font-medium">{{ result.model }}</span>
                  </div>
                  <div>
                    <span class="text-gray-600">Time:</span>
                    <span class="font-medium">{{ result.processing_time }}ms</span>
                  </div>
                </div>
                
                <!-- Detected Objects -->
                <div v-if="result.objects.length > 0" class="mt-3">
                  <h5 class="text-sm font-medium text-gray-700 mb-2">Detected Objects:</h5>
                  <div class="flex flex-wrap gap-2">
                    <span v-for="(obj, index) in result.objects" :key="index"
                          class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                      {{ obj.class }} ({{ Math.round(obj.confidence * 100) }}%)
                    </span>
                  </div>
                </div>
                
                <!-- Processed Image -->
                <div v-if="result.image_url" class="mt-3">
                  <img :src="result.image_url" 
                       :alt="`Detection result ${result.id}`"
                       class="w-full h-32 object-cover rounded border cursor-pointer"
                       @click="viewFullImage(result.image_url)" />
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
                  @click="captureSingleImage"
                  :disabled="!isStreaming || !selectedModel"
                  class="px-4 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white rounded-lg transition-colors flex items-center justify-center"
                >
                  <i class="fas fa-camera mr-2"></i>
                  Capture & Process
                </button>
                <button 
                  @click="downloadResults"
                  :disabled="cvResults.length === 0"
                  class="px-4 py-3 bg-purple-600 hover:bg-purple-700 disabled:bg-gray-400 text-white rounded-lg transition-colors flex items-center justify-center"
                >
                  <i class="fas fa-download mr-2"></i>
                  Download Results
                </button>
              </div>
              
              <!-- CV Processing Status -->
              <div v-if="cvProcessing" class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-center">
                  <i class="fas fa-spinner fa-spin text-blue-500 mr-2"></i>
                  <span class="text-blue-700 font-medium">Processing image with Computer Vision...</span>
                </div>
              </div>
              
              <!-- CV Statistics -->
              <div v-if="cvStats.totalProcessed > 0" class="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700 mb-2">CV Processing Stats:</h4>
                <div class="grid grid-cols-3 gap-2 text-sm">
                  <div>
                    <span class="text-gray-600">Processed:</span>
                    <span class="font-medium">{{ cvStats.totalProcessed }}</span>
                  </div>
                  <div>
                    <span class="text-gray-600">Avg Time:</span>
                    <span class="font-medium">{{ Math.round(cvStats.avgProcessingTime) }}ms</span>
                  </div>
                  <div>
                    <span class="text-gray-600">Success:</span>
                    <span class="font-medium">{{ Math.round(cvStats.successRate) }}%</span>
                  </div>
                </div>
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

// Live streaming
const isStreaming = ref(false)
const streamUrl = ref('')
const streamInterval = ref(null)
const isUpdating = ref(false)
const streamFPS = ref(0)
const lastUpdateTime = ref(Date.now())
const streamMode = ref('auto') // 'mjpeg', 'base64', 'websocket'
const websocket = ref(null)

// Computer Vision variables
const continuousCVInterval = ref(null)
const cvProcessing = ref(false)
const cvResults = ref([])
const lastCVResult = ref(null)
const cvStats = ref({
  totalProcessed: 0,
  avgProcessingTime: 0,
  successRate: 0
})

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
        
        // Start live streaming immediately
        setTimeout(() => {
          startLiveStream()
        }, 500) // Small delay to ensure camera is ready
        
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
    // Stop live streaming first
    stopLiveStream()
    
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
        console.log('📸 Capture successful - Image is real-time!')
        
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

// Live streaming methods - USER CONTROLLED MODES
const startLiveStream = () => {
  if (!selectedCameraId.value) return

  console.log(`🎬 Starting ${streamMode.value} live stream for camera:`, selectedCameraId.value)
  isStreaming.value = true
  lastUpdateTime.value = Date.now()
  
  // Stop any existing stream first
  stopLiveStream()
  
  // Start based on selected mode
  if (streamMode.value === 'mjpeg') {
    startMjpegStream()
  } else if (streamMode.value === 'base64') {
    startBase64Stream()
  } else { // auto mode
    startAutoStream()
  }
}

// MJPEG streaming (best performance)
const startMjpegStream = () => {
  console.log('🎥 Starting MJPEG streaming...')
  streamUrl.value = `http://${props.rvm.ip_address}:5000/api/cameras/${selectedCameraId.value}/stream/mjpeg`
  isStreaming.value = true  // FIX: Set streaming state to true
  startFPSMonitoring()
}

// Base64 streaming (reliable fallback)
const startBase64Stream = () => {
  console.log('🔄 Starting Base64 streaming...')
  
  const updateStream = async () => {
    if (isUpdating.value) return
    
    isUpdating.value = true
    const startTime = Date.now()
    
    try {
      const response = await fetch(`http://${props.rvm.ip_address}:5000/api/cameras/${selectedCameraId.value}/capture/base64`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
      })
      
      if (response.ok) {
        const data = await response.json()
        if (data.success && data.image_base64) {
          const newStreamUrl = `data:image/jpeg;base64,${data.image_base64}`
          
          // Force image update by changing URL slightly
          const timestamp = Date.now()
          streamUrl.value = `${newStreamUrl}#t=${timestamp}`
          
          // Ensure streaming state is active
          if (!isStreaming.value) {
            isStreaming.value = true
            console.log('🔄 Streaming state activated')
          }
          
          // Calculate FPS
          const elapsed = Date.now() - lastUpdateTime.value
          streamFPS.value = Math.round(1000 / elapsed)
          lastUpdateTime.value = Date.now()
          
          console.log(`📊 Base64 Stream FPS: ${streamFPS.value} | Latency: ${Date.now() - startTime}ms | Image size: ${data.image_base64.length} chars | URL: ${streamUrl.value.substring(0, 50)}...`)
        } else {
          console.warn('⚠️ Base64 capture failed:', data)
        }
      } else {
        console.error('❌ Base64 request failed:', response.status, response.statusText)
      }
    } catch (error) {
      console.error('❌ Stream update error:', error)
    } finally {
      isUpdating.value = false
    }
  }
  
  // Initial update
  updateStream()
  
  // Update every 200ms for smoother experience (5 FPS)
  streamInterval.value = setInterval(updateStream, 200)
}

// Auto mode (try MJPEG first, fallback to Base64)
const startAutoStream = () => {
  console.log('🤖 Starting auto mode streaming...')
  
  // Try MJPEG first
  const mjpegUrl = `http://${props.rvm.ip_address}:5000/api/cameras/${selectedCameraId.value}/stream/mjpeg`
  streamUrl.value = mjpegUrl
  
  // Test MJPEG connection immediately
  const testMjpeg = () => {
    fetch(mjpegUrl, { method: 'HEAD' })
      .then(response => {
        if (response.ok) {
          console.log('✅ MJPEG streaming active')
          isStreaming.value = true  // FIX: Set streaming state to true
          startFPSMonitoring()
        } else {
          throw new Error('MJPEG not available')
        }
      })
      .catch(error => {
        console.log('⚠️ MJPEG failed, falling back to Base64:', error.message)
        startBase64Stream()
      })
  }
  
  // Test after 1 second
  setTimeout(testMjpeg, 1000)
}

// FPS monitoring for MJPEG
const startFPSMonitoring = () => {
  const monitorFPS = () => {
    const now = Date.now()
    const elapsed = now - lastUpdateTime.value
    if (elapsed > 0) {
      streamFPS.value = Math.round(1000 / elapsed)
      lastUpdateTime.value = now
      console.log(`📊 MJPEG Stream FPS: ${streamFPS.value}`)
    }
  }
  streamInterval.value = setInterval(monitorFPS, 2000)
}

const stopLiveStream = () => {
  console.log('🛑 Stopping live stream')
  isStreaming.value = false
  streamUrl.value = ''
  
  if (streamInterval.value) {
    clearInterval(streamInterval.value)
    streamInterval.value = null
  }
}

const handleStreamError = () => {
  console.error('❌ MJPEG Stream error - retrying...')
  // Retry stream after 2 seconds
  setTimeout(() => {
    if (isStreaming.value && selectedCameraId.value) {
      const timestamp = Date.now()
      streamUrl.value = `http://${props.rvm.ip_address}:5000/api/cameras/${selectedCameraId.value}/stream/mjpeg?t=${timestamp}`
    }
  }, 2000)
}

const handleStreamLoad = () => {
  console.log('✅ Stream loaded successfully')
  lastUpdateTime.value = Date.now()
}

const handleStreamLoadStart = () => {
  console.log('🔄 Stream loading started...')
}

    // Computer Vision Capture Functions
    const captureImageForCV = async () => {
      if (!isStreaming.value || !selectedCameraId.value) return

      try {
        cvProcessing.value = true
        const startTime = Date.now()

        // Capture high-quality image for CV using Laravel proxy
        const response = await fetch(`/api/cameras/${selectedCameraId.value}/capture/cv`, {
          method: 'POST',
          headers: { 
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({
            quality: 95,
            resolution: '1920x1080',
            rvm_id: props.rvm.id
          })
        })
    
    if (response.ok) {
      const data = await response.json()
      if (data.success && data.image_base64) {
        // Process with Computer Vision
        await processImageForCV(data.image_base64)
        
        // Update stats
        const processingTime = Date.now() - startTime
        updateCVStats(processingTime, true)
      }
    } else {
      console.error('❌ CV capture failed:', response.status)
      updateCVStats(0, false)
    }
  } catch (error) {
    console.error('❌ CV capture error:', error)
    updateCVStats(0, false)
  } finally {
    cvProcessing.value = false
  }
}

// Process captured image with CV models
const processImageForCV = async (imageBase64) => {
  if (!selectedModel.value) {
    console.warn('⚠️ No model selected for CV processing')
    return
  }
  
  try {
    const response = await fetch('/api/cv/process', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        image: imageBase64,
        model: selectedModel.value.id,
        rvm_id: props.rvm.id,
        timestamp: new Date().toISOString()
      })
    })
    
    if (response.ok) {
      const results = await response.json()
      displayCVResults(results)
    } else {
      console.error('❌ CV processing failed:', response.status)
    }
  } catch (error) {
    console.error('❌ CV processing error:', error)
  }
}

// Display CV results
const displayCVResults = (results) => {
  const cvResult = {
    id: Date.now(),
    timestamp: new Date().toISOString(),
    objects: results.detections || [],
    confidence: results.avg_confidence || 0,
    model: results.model_used || selectedModel.value?.name,
    processing_time: results.processing_time || 0,
    image_url: results.processed_image_url || null
  }
  
  // Add to results array
  cvResults.value.unshift(cvResult)
  lastCVResult.value = cvResult
  
  // Keep only last 50 results
  if (cvResults.value.length > 50) {
    cvResults.value = cvResults.value.slice(0, 50)
  }
  
  console.log(`🎯 CV Result: ${cvResult.objects.length} objects detected with ${cvResult.confidence}% confidence`)
}

// Update CV statistics
const updateCVStats = (processingTime, success) => {
  cvStats.value.totalProcessed++
  
  if (success) {
    // Update average processing time
    const currentAvg = cvStats.value.avgProcessingTime
    const total = cvStats.value.totalProcessed
    cvStats.value.avgProcessingTime = ((currentAvg * (total - 1)) + processingTime) / total
    
    // Update success rate
    const successCount = cvStats.value.totalProcessed * (cvStats.value.successRate / 100)
    cvStats.value.successRate = ((successCount + 1) / cvStats.value.totalProcessed) * 100
  } else {
    // Update success rate for failure
    const successCount = cvStats.value.totalProcessed * (cvStats.value.successRate / 100)
    cvStats.value.successRate = (successCount / cvStats.value.totalProcessed) * 100
  }
}

// Continuous CV processing
const startContinuousCV = () => {
  if (continuousCVInterval.value) return
  
  console.log('🎯 Starting continuous CV processing...')
  continuousCVInterval.value = setInterval(async () => {
    if (isStreaming.value && detectionActive.value && selectedModel.value) {
      await captureImageForCV()
    }
  }, 2000) // Capture every 2 seconds for CV processing
}

const stopContinuousCV = () => {
  if (continuousCVInterval.value) {
    console.log('🛑 Stopping continuous CV processing...')
    clearInterval(continuousCVInterval.value)
    continuousCVInterval.value = null
  }
}

// Manual CV capture
const captureSingleImage = async () => {
  if (!isStreaming.value) {
    cameraError.value = 'Please start camera first'
    return
  }
  
  if (!selectedModel.value) {
    cameraError.value = 'Please select a model first'
    return
  }
  
  await captureImageForCV()
}

// Download CV results
const downloadResults = () => {
  if (cvResults.value.length === 0) return
  
  const dataStr = JSON.stringify(cvResults.value, null, 2)
  const dataBlob = new Blob([dataStr], { type: 'application/json' })
  const url = URL.createObjectURL(dataBlob)
  
  const link = document.createElement('a')
  link.href = url
  link.download = `cv_results_${new Date().toISOString().split('T')[0]}.json`
  link.click()
  
  URL.revokeObjectURL(url)
}

// View full image
const viewFullImage = (imageUrl) => {
  window.open(imageUrl, '_blank')
}

const startDetection = () => {
  if (selectedModel.value && props.jetsonCameraInfo?.camera_ready) {
    detectionActive.value = true
    console.log('🎯 Starting detection with model:', selectedModel.value.name)
    
    // Start continuous CV processing
    startContinuousCV()
  }
}

const stopDetection = () => {
  detectionActive.value = false
  console.log('🛑 Stopping detection...')
  
  // Stop continuous CV processing
  stopContinuousCV()
}

const uploadImage = () => {
  // TODO: Implement image upload
  console.log('Uploading image...')
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
  // Cleanup streaming
  stopLiveStream()
})
</script>

<style scoped>
/* Custom styles for playground */
.aspect-video {
  aspect-ratio: 16 / 9;
}
</style>
