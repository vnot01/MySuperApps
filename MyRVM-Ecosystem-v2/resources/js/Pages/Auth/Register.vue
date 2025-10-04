<script setup>
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
  <Head title="Register - MyRVM Ecosystem" />
  
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-purple-600 to-indigo-700">
    <div class="w-full max-w-md">
      <div class="bg-white rounded-lg shadow-2xl overflow-hidden p-8">
        <h2 class="text-2xl font-bold text-center mb-6">Registration</h2>
        <p class="text-center text-gray-600 mb-6">Registration is currently disabled. Please contact administrator.</p>
        <Link href="/login" class="block w-full bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-2 px-4 rounded text-center">
          Back to Login
        </Link>
      </div>
    </div>
  </div>
</template>
