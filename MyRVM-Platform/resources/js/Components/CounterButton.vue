<script setup>
import { ref } from 'vue';
const count = ref(0);
</script>
<template>
    <button @click="count++" class="px-4 py-2 bg-blue-500 text-white rounded">
        You clicked me {{ count }} times.
    </button>
</template>