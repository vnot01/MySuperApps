<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Edit RVM</h1>
            <p class="mt-2 text-gray-600">Update RVM information and settings</p>
          </div>
          <button
            @click="goBack"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
          >
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Dashboard
          </button>
        </div>
      </div>

      <!-- Form -->
      <div class="bg-white shadow rounded-lg">
        <form @submit.prevent="submitForm" class="p-6">
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <!-- RVM Name -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700">
                RVM Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                id="name"
                required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm"
                :class="{ 'border-red-300': errors.name }"
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
            </div>

            <!-- Location -->
            <div>
              <label for="location" class="block text-sm font-medium text-gray-700">
                Location <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.location"
                type="text"
                id="location"
                required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm"
                :class="{ 'border-red-300': errors.location }"
              />
              <p v-if="errors.location" class="mt-1 text-sm text-red-600">{{ errors.location }}</p>
            </div>

            <!-- Address -->
            <div>
              <label for="address" class="block text-sm font-medium text-gray-700">
                Address
              </label>
              <textarea
                v-model="form.address"
                id="address"
                rows="3"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm"
                :class="{ 'border-red-300': errors.address }"
              ></textarea>
              <p v-if="errors.address" class="mt-1 text-sm text-red-600">{{ errors.address }}</p>
            </div>

            <!-- IP Address -->
            <div>
              <label for="ip_address" class="block text-sm font-medium text-gray-700">
                IP Address
              </label>
              <input
                v-model="form.ip_address"
                type="text"
                id="ip_address"
                placeholder="192.168.1.100"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm"
                :class="{ 'border-red-300': errors.ip_address }"
              />
              <p v-if="errors.ip_address" class="mt-1 text-sm text-red-600">{{ errors.ip_address }}</p>
            </div>

            <!-- Status -->
            <div>
              <label for="status" class="block text-sm font-medium text-gray-700">
                Status <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.status"
                id="status"
                required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm"
                :class="{ 'border-red-300': errors.status }"
              >
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="maintenance">Maintenance</option>
                <option value="error">Error</option>
              </select>
              <p v-if="errors.status" class="mt-1 text-sm text-red-600">{{ errors.status }}</p>
            </div>

            <!-- Capacity -->
            <div>
              <label for="capacity" class="block text-sm font-medium text-gray-700">
                Capacity <span class="text-red-500">*</span>
              </label>
              <input
                v-model.number="form.capacity"
                type="number"
                id="capacity"
                min="1"
                max="1000"
                required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm"
                :class="{ 'border-red-300': errors.capacity }"
              />
              <p v-if="errors.capacity" class="mt-1 text-sm text-red-600">{{ errors.capacity }}</p>
            </div>

            <!-- Current Load -->
            <div>
              <label for="current_load" class="block text-sm font-medium text-gray-700">
                Current Load <span class="text-red-500">*</span>
              </label>
              <input
                v-model.number="form.current_load"
                type="number"
                id="current_load"
                min="0"
                required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm"
                :class="{ 'border-red-300': errors.current_load }"
              />
              <p v-if="errors.current_load" class="mt-1 text-sm text-red-600">{{ errors.current_load }}</p>
            </div>

            <!-- Latitude -->
            <div>
              <label for="latitude" class="block text-sm font-medium text-gray-700">
                Latitude
              </label>
              <input
                v-model.number="form.latitude"
                type="number"
                id="latitude"
                step="0.00000001"
                min="-90"
                max="90"
                placeholder="0.000000"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm"
                :class="{ 'border-red-300': errors.latitude }"
              />
              <p v-if="errors.latitude" class="mt-1 text-sm text-red-600">{{ errors.latitude }}</p>
            </div>

            <!-- Longitude -->
            <div>
              <label for="longitude" class="block text-sm font-medium text-gray-700">
                Longitude
              </label>
              <input
                v-model.number="form.longitude"
                type="number"
                id="longitude"
                step="0.00000001"
                min="-180"
                max="180"
                placeholder="0.000000"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm"
                :class="{ 'border-red-300': errors.longitude }"
              />
              <p v-if="errors.longitude" class="mt-1 text-sm text-red-600">{{ errors.longitude }}</p>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="mt-8 flex justify-end space-x-3">
            <button
              type="button"
              @click="goBack"
              :disabled="isSubmitting"
              class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50 flex items-center"
            >
              <i v-if="isSubmitting" class="fas fa-spinner fa-spin mr-2"></i>
              {{ isSubmitting ? 'Updating...' : 'Update RVM' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { router } from "@inertiajs/vue3"
import { ref, reactive, onMounted } from "vue"

const props = defineProps({
  rvm: Object
})

const isSubmitting = ref(false)
const errors = ref({})

const form = reactive({
  name: '',
  location: '',
  address: '',
  ip_address: '',
  status: 'inactive',
  capacity: 100,
  current_load: 0,
  latitude: null,
  longitude: null
})

onMounted(() => {
  // Populate form with RVM data
  form.name = props.rvm.name || ''
  form.location = props.rvm.location || ''
  form.address = props.rvm.address || ''
  form.ip_address = props.rvm.ip_address || ''
  form.status = props.rvm.status || 'inactive'
  form.capacity = props.rvm.capacity || 100
  form.current_load = props.rvm.current_load || 0
  form.latitude = props.rvm.latitude || null
  form.longitude = props.rvm.longitude || null
})

const submitForm = () => {
  isSubmitting.value = true
  errors.value = {}

  router.put(`/rvms/${props.rvm.id}`, form, {
    onSuccess: () => {
      console.log('RVM updated successfully')
    },
    onError: (errorResponse) => {
      console.error('Failed to update RVM:', errorResponse)
      if (errorResponse.errors) {
        errors.value = errorResponse.errors
      }
    },
    onFinish: () => {
      isSubmitting.value = false
    }
  })
}

const goBack = () => {
  router.get('/dashboard')
}
</script>

<style scoped>
/* Custom styles if needed */
</style>
