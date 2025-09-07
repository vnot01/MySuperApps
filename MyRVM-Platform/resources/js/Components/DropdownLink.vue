<!-- resources/js/Components/DropdownLink.vue -->
<script setup>
defineProps({
    href: {
        type: String,
        // href tidak selalu required jika 'as' adalah 'button'
        // tapi kita biarkan untuk link
    },
    as: {
        type: String,
        default: 'a', // Defaultnya adalah link biasa
    },
});
</script>

<template>
    <div>
        <button
            v-if="as === 'button'"
            type="submit"
            class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out"
        >
            <slot />
        </button>

        <a
            v-else
            :href="href"
            class="block px-4 py-2 text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out"
        >
            <slot />
        </a>
    </div>
</template>