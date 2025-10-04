<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const showPassword = ref(false)
const showCopyNotification = ref(false)

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  })
}

const copyCredentials = (email, password) => {
  // Fill the form with credentials
  form.email = email
  form.password = password
  
  // Copy to clipboard with fallback
  const credentials = `${email}\n${password}`
  
  // Check if clipboard API is available
  if (navigator.clipboard && window.isSecureContext) {
    // Use modern clipboard API
    navigator.clipboard.writeText(credentials).then(() => {
      showCopyNotification.value = true
      setTimeout(() => {
        showCopyNotification.value = false
      }, 2000)
    }).catch(err => {
      console.error('Failed to copy credentials:', err)
      // Fallback to old method
      fallbackCopyTextToClipboard(credentials)
    })
  } else {
    // Use fallback method
    fallbackCopyTextToClipboard(credentials)
  }
}

// Fallback copy function for older browsers or non-HTTPS
const fallbackCopyTextToClipboard = (text) => {
  const textArea = document.createElement("textarea")
  textArea.value = text
  
  // Avoid scrolling to bottom
  textArea.style.top = "0"
  textArea.style.left = "0"
  textArea.style.position = "fixed"
  textArea.style.opacity = "0"
  
  document.body.appendChild(textArea)
  textArea.focus()
  textArea.select()
  
  try {
    const successful = document.execCommand('copy')
    if (successful) {
      showCopyNotification.value = true
      setTimeout(() => {
        showCopyNotification.value = false
      }, 2000)
    } else {
      console.error('Fallback: Could not copy text')
    }
  } catch (err) {
    console.error('Fallback: Could not copy text: ', err)
  }
  
  document.body.removeChild(textArea)
}
</script>

<template>
  <Head title="Login - MyRVM Ecosystem" />
  
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-purple-600 to-indigo-700">
    <div class="w-full max-w-md">
      <!-- Login Card -->
      <div class="bg-white rounded-lg shadow-2xl overflow-hidden">
        <!-- Header -->
        <div class="px-8 pt-8 pb-6 bg-gradient-to-r from-purple-600 to-indigo-600 text-white">
          <a href="/" class="flex items-center justify-center mb-4 hover:opacity-80 transition">
            <i class="fas fa-recycle text-4xl"></i>
          </a>
          <h2 class="text-2xl font-bold text-center">Welcome Back!</h2>
          <p class="text-center text-purple-100 mt-2">Sign in to MyRVM Ecosystem</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="px-8 pt-6 pb-8">
          <!-- Email -->
          <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">
              Email or Username
            </label>
            <input
              type="text"
              v-model="form.email"
              id="email"
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent"
              :class="{ 'border-red-500': form.errors.email }"
              placeholder="Enter your email"
              autofocus
              required
            />
            <p v-if="form.errors.email" class="text-red-500 text-xs italic mt-1">
              {{ form.errors.email }}
            </p>
          </div>

          <!-- Password -->
          <div class="mb-6">
            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">
              Password
            </label>
            <div class="relative">
              <input
                :type="showPassword ? 'text' : 'password'"
                v-model="form.password"
                id="password"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent pr-10"
                :class="{ 'border-red-500': form.errors.password }"
                placeholder="············"
                required
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-gray-800"
              >
                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
              </button>
            </div>
            <p v-if="form.errors.password" class="text-red-500 text-xs italic mt-1">
              {{ form.errors.password }}
            </p>
          </div>

          <!-- Remember Me -->
          <div class="mb-6 flex items-center">
            <input
              type="checkbox"
              v-model="form.remember"
              id="remember"
              class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
            />
            <label for="remember" class="ml-2 block text-sm text-gray-900">
              Remember me
            </label>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="form.processing">
              <i class="fas fa-spinner fa-spin mr-2"></i>Signing in...
            </span>
            <span v-else>Sign In</span>
          </button>
        </form>

        <!-- Footer -->
        <div class="px-8 py-4 bg-gray-50 border-t border-gray-200">
          <p class="text-center text-gray-600 text-sm font-semibold mb-3">
            <i class="fas fa-info-circle text-blue-500 mr-1"></i>
            Demo Accounts Available
          </p>
          <div class="space-y-2">
            <!-- Admin Account -->
            <div class="flex items-center justify-between bg-white p-2 rounded border border-gray-200 hover:border-purple-300 transition">
              <div class="flex-1">
                <span class="text-xs font-semibold text-gray-700 block">Admin Account</span>
                <code class="text-xs text-gray-600">admin@myrvm.com / password</code>
              </div>
              <div class="flex gap-1">
                <button
                  type="button"
                  @click="copyCredentials('admin@myrvm.com', 'password')"
                  class="px-2 py-1 text-xs bg-purple-500 hover:bg-purple-600 text-white rounded transition"
                  title="Copy and fill credentials"
                >
                  <i class="fas fa-copy mr-1"></i>Fill
                </button>
              </div>
            </div>

            <!-- Demo Account -->
            <div class="flex items-center justify-between bg-white p-2 rounded border border-gray-200 hover:border-purple-300 transition">
              <div class="flex-1">
                <span class="text-xs font-semibold text-gray-700 block">Demo Account</span>
                <code class="text-xs text-gray-600">demo@myrvm.com / password</code>
              </div>
              <div class="flex gap-1">
                <button
                  type="button"
                  @click="copyCredentials('demo@myrvm.com', 'password')"
                  class="px-2 py-1 text-xs bg-purple-500 hover:bg-purple-600 text-white rounded transition"
                  title="Copy and fill credentials"
                >
                  <i class="fas fa-copy mr-1"></i>Fill
                </button>
              </div>
            </div>

            <!-- Operator Account -->
            <div class="flex items-center justify-between bg-white p-2 rounded border border-gray-200 hover:border-purple-300 transition">
              <div class="flex-1">
                <span class="text-xs font-semibold text-gray-700 block">Operator Account</span>
                <code class="text-xs text-gray-600">operator@myrvm.com / password</code>
              </div>
              <div class="flex gap-1">
                <button
                  type="button"
                  @click="copyCredentials('operator@myrvm.com', 'password')"
                  class="px-2 py-1 text-xs bg-purple-500 hover:bg-purple-600 text-white rounded transition"
                  title="Copy and fill credentials"
                >
                  <i class="fas fa-copy mr-1"></i>Fill
                </button>
              </div>
            </div>
          </div>

          <!-- Copy Notification -->
          <div
            v-if="showCopyNotification"
            class="mt-2 text-center text-xs text-green-600 font-medium animate-pulse"
          >
            <i class="fas fa-check-circle mr-1"></i>
            Credentials filled successfully!
          </div>
        </div>
      </div>

      <!-- Back to Home -->
      <div class="text-center mt-4">
        <a href="/" class="text-white hover:text-purple-200 text-sm transition-colors duration-150">
          <i class="fas fa-arrow-left mr-2"></i>Back to Home
        </a>
      </div>
    </div>
  </div>
</template>
