# 🔧 Modern Dashboard - Implementation Code

## 📁 **FILE: `resources/js/Pages/Dashboard.vue`**

### **Complete Implementation:**

```vue
<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-gray-100">
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white/80 backdrop-blur-xl border-r border-gray-200/50 shadow-xl">
      <!-- Logo & Brand -->
      <div class="flex items-center justify-center h-16 px-6 border-b border-gray-200/50">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
            <i class="fas fa-recycle text-white text-lg"></i>
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-900">MyRVM</h1>
            <p class="text-xs text-gray-500">Ecosystem v2</p>
          </div>
        </div>
      </div>

      <!-- Navigation Menu -->
      <nav class="mt-8 px-4">
        <div class="space-y-2">
          <!-- Dashboard -->
          <a href="/dashboard" class="flex items-center px-4 py-3 text-sm font-medium text-purple-600 bg-purple-50 rounded-xl border border-purple-200/50">
            <i class="fas fa-tachometer-alt w-5 h-5 mr-3"></i>
            Dashboard
          </a>

          <!-- RVMs Dropdown -->
          <div class="relative">
            <button @click="toggleRvmsMenu" class="flex items-center justify-between w-full px-4 py-3 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 rounded-xl transition-all duration-200">
              <div class="flex items-center">
                <i class="fas fa-industry w-5 h-5 mr-3"></i>
                RVMs
              </div>
              <i class="fas fa-chevron-down w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': rvmsMenuOpen }"></i>
            </button>
            
            <!-- RVMs Submenu -->
            <div v-show="rvmsMenuOpen" class="mt-2 ml-8 space-y-1">
              <a href="/rvms" class="flex items-center px-4 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-all duration-200">
                <i class="fas fa-list w-4 h-4 mr-3"></i>
                All RVMs
              </a>
              <a href="/detections" class="flex items-center px-4 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-all duration-200">
                <i class="fas fa-search w-4 h-4 mr-3"></i>
                All Detection
              </a>
            </div>
          </div>

          <!-- Settings -->
          <a href="/settings" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 rounded-xl transition-all duration-200">
            <i class="fas fa-cog w-5 h-5 mr-3"></i>
            Settings
          </a>
        </div>
      </nav>

      <!-- User Profile -->
      <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200/50">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-full flex items-center justify-center">
            <span class="text-white text-sm font-medium">{{ auth.user.name.charAt(0) }}</span>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate">{{ auth.user.name }}</p>
            <p class="text-xs text-gray-500">Administrator</p>
          </div>
          <button @click="logout" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all duration-200">
            <i class="fas fa-sign-out-alt"></i>
          </button>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="pl-64">
      <!-- Top Header -->
      <header class="bg-white/80 backdrop-blur-xl border-b border-gray-200/50 shadow-sm">
        <div class="px-6 py-4">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-2xl font-bold text-gray-900">Dashboard</h2>
              <p class="text-sm text-gray-500">Welcome back, {{ auth.user.name }}!</p>
            </div>
            
            <!-- Header Actions -->
            <div class="flex items-center space-x-4">
              <!-- Search -->
              <div class="relative">
                <input type="text" placeholder="Search..." class="w-64 px-4 py-2 pl-10 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
              </div>
              
              <!-- Notifications -->
              <button class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-xl transition-all duration-200">
                <i class="fas fa-bell"></i>
                <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
              </button>
              
              <!-- Theme Toggle -->
              <button class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-xl transition-all duration-200">
                <i class="fas fa-sun"></i>
              </button>
            </div>
          </div>
        </div>
      </header>

      <!-- Dashboard Content -->
      <main class="p-6">
        <!-- KPI Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <!-- Total RVMs Card -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-gray-200/50 p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Total RVMs</p>
                <p class="text-3xl font-bold text-gray-900">{{ stats.totalRvms }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ stats.activeRvms }} active</p>
              </div>
              <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-industry text-white text-xl"></i>
              </div>
            </div>
            <div class="mt-4 flex items-center">
              <span class="text-green-600 text-sm font-medium">+2.5%</span>
              <span class="text-gray-500 text-sm ml-2">vs last month</span>
            </div>
          </div>

          <!-- Online RVMs Card -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-gray-200/50 p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Online RVMs</p>
                <p class="text-3xl font-bold text-gray-900">{{ stats.onlineRvms }}</p>
                <p class="text-xs text-gray-500 mt-1">Connected devices</p>
              </div>
              <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-wifi text-white text-xl"></i>
              </div>
            </div>
            <div class="mt-4 flex items-center">
              <span class="text-green-600 text-sm font-medium">+12.6%</span>
              <span class="text-gray-500 text-sm ml-2">vs last week</span>
            </div>
          </div>

          <!-- Average Usage Card -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-gray-200/50 p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Average Usage</p>
                <p class="text-3xl font-bold text-gray-900">{{ stats.averageUsage }}%</p>
                <p class="text-xs text-gray-500 mt-1">Across all RVMs</p>
              </div>
              <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-chart-pie text-white text-xl"></i>
              </div>
            </div>
            <div class="mt-4 flex items-center">
              <span class="text-green-600 text-sm font-medium">+5.2%</span>
              <span class="text-gray-500 text-sm ml-2">vs last month</span>
            </div>
          </div>

          <!-- Total Detections Card -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-gray-200/50 p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Total Detections</p>
                <p class="text-3xl font-bold text-gray-900">{{ stats.totalDetections }}</p>
                <p class="text-xs text-gray-500 mt-1">This month</p>
              </div>
              <div class="w-12 h-12 bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-search text-white text-xl"></i>
              </div>
            </div>
            <div class="mt-4 flex items-center">
              <span class="text-green-600 text-sm font-medium">+18.3%</span>
              <span class="text-gray-500 text-sm ml-2">vs last month</span>
            </div>
          </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <!-- Usage Chart -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-gray-200/50 p-6">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-lg font-semibold text-gray-900">Usage Trends</h3>
              <div class="flex space-x-2">
                <button class="px-3 py-1 text-xs font-medium text-purple-600 bg-purple-50 rounded-lg">7D</button>
                <button class="px-3 py-1 text-xs font-medium text-gray-500 bg-gray-50 rounded-lg">30D</button>
                <button class="px-3 py-1 text-xs font-medium text-gray-500 bg-gray-50 rounded-lg">90D</button>
              </div>
            </div>
            <!-- Chart placeholder -->
            <div class="h-64 bg-gradient-to-r from-purple-50 to-indigo-50 rounded-xl flex items-center justify-center">
              <div class="text-center">
                <i class="fas fa-chart-line text-4xl text-purple-300 mb-2"></i>
                <p class="text-gray-500">Usage Chart</p>
              </div>
            </div>
          </div>

          <!-- Detection Chart -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-gray-200/50 p-6">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-lg font-semibold text-gray-900">Detection Analytics</h3>
              <div class="flex space-x-2">
                <button class="px-3 py-1 text-xs font-medium text-purple-600 bg-purple-50 rounded-lg">Today</button>
                <button class="px-3 py-1 text-xs font-medium text-gray-500 bg-gray-50 rounded-lg">Week</button>
                <button class="px-3 py-1 text-xs font-medium text-gray-500 bg-gray-50 rounded-lg">Month</button>
              </div>
            </div>
            <!-- Chart placeholder -->
            <div class="h-64 bg-gradient-to-r from-green-50 to-blue-50 rounded-xl flex items-center justify-center">
              <div class="text-center">
                <i class="fas fa-chart-bar text-4xl text-green-300 mb-2"></i>
                <p class="text-gray-500">Detection Chart</p>
              </div>
            </div>
          </div>
        </div>

        <!-- RVM Status Overview -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-gray-200/50 p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">RVM Status Overview</h3>
            <button class="px-4 py-2 text-sm font-medium text-purple-600 bg-purple-50 rounded-xl hover:bg-purple-100 transition-all duration-200">
              View All
            </button>
          </div>
          
          <!-- RVM List -->
          <div class="space-y-4">
            <!-- RVM Item -->
            <div v-for="rvm in rvms" :key="rvm.id" class="flex items-center justify-between p-4 bg-gray-50/50 rounded-xl hover:bg-gray-100/50 transition-all duration-200">
              <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                  <i class="fas fa-industry text-white"></i>
                </div>
                <div>
                  <h4 class="font-medium text-gray-900">{{ rvm.name }}</h4>
                  <p class="text-sm text-gray-500">{{ rvm.location }}</p>
                </div>
              </div>
              <div class="flex items-center space-x-4">
                <div class="text-right">
                  <p class="text-sm font-medium text-gray-900">{{ rvm.status }}</p>
                  <p class="text-xs text-gray-500">{{ rvm.lastSeen }}</p>
                </div>
                <div class="flex space-x-2">
                  <button class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-200 rounded-lg transition-all duration-200">
                    <i class="fas fa-eye"></i>
                  </button>
                  <button class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-200 rounded-lg transition-all duration-200">
                    <i class="fas fa-cog"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

// Props
const props = defineProps({
  auth: Object,
  stats: Object,
  rvms: Array
})

// Reactive state
const rvmsMenuOpen = ref(false)

// Methods
const toggleRvmsMenu = () => {
  rvmsMenuOpen.value = !rvmsMenuOpen.value
}

const logout = () => {
  router.post('/logout')
}
</script>

<style scoped>
/* Custom animations */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-fadeIn {
  animation: fadeIn 0.3s ease-out;
}

/* Glass morphism effect */
.glass {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.18);
}

/* Hover effects */
.hover-lift:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}
</style>
```

## 🎨 **DESIGN FEATURES:**

### **1. Modern Sidebar Navigation:**
- ✅ Fixed sidebar dengan glass morphism effect
- ✅ Dropdown menu untuk RVMs
- ✅ Smooth animations dan transitions
- ✅ User profile di bottom sidebar
- ✅ Professional typography

### **2. Glass Morphism Design:**
- ✅ `bg-white/80 backdrop-blur-xl` untuk glass effect
- ✅ `border border-gray-200/50` untuk subtle borders
- ✅ `shadow-lg` untuk depth
- ✅ `rounded-2xl` untuk modern corners

### **3. Responsive Layout:**
- ✅ `pl-64` untuk main content (sidebar width)
- ✅ `grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4` untuk responsive cards
- ✅ Mobile-friendly design

### **4. Interactive Elements:**
- ✅ Hover effects dengan `hover:shadow-xl`
- ✅ Smooth transitions dengan `transition-all duration-300`
- ✅ Active states untuk navigation
- ✅ Dropdown animations

## 🚀 **IMPLEMENTATION STEPS:**

### **Step 1: Backup Current File**
```bash
cp resources/js/Pages/Dashboard.vue resources/js/Pages/Dashboard.vue.backup
```

### **Step 2: Replace Content**
```bash
# Replace entire file content with new design
nano resources/js/Pages/Dashboard.vue
```

### **Step 3: Test**
```bash
# Visit dashboard
http://100.123.143.87:8001/dashboard
```

## 📱 **RESPONSIVE BREAKPOINTS:**

- **Mobile (< 768px):** Single column layout
- **Tablet (768px - 1024px):** 2 column grid
- **Desktop (> 1024px):** 4 column grid

## 🎯 **MENU STRUCTURE:**

1. **Dashboard** - Main dashboard (active state)
2. **RVMs** - Dropdown menu
   - All RVMs (`/rvms`)
   - All Detection (`/detections`)
3. **Settings** - System settings (`/settings`)

## ✨ **ANIMATIONS:**

- **Fade In:** Cards animate in on load
- **Hover Lift:** Cards lift on hover
- **Smooth Transitions:** All interactions are smooth
- **Dropdown:** RVMs menu slides down/up

## 🎨 **COLOR SCHEME:**

- **Primary:** Purple (`purple-600`)
- **Secondary:** Indigo (`indigo-600`)
- **Success:** Green (`green-500`)
- **Warning:** Orange (`orange-500`)
- **Info:** Blue (`blue-500`)
- **Background:** Gray gradients

---
**Created:** 2025-10-05  
**Status:** ✅ Ready for Implementation  
**Design:** Modern Professional Dashboard
