import './bootstrap';
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();
// Pastikan semua kode Alpine sudah dihapus/dikomentari

import { createApp } from 'vue';

import Dropdown from './Components/Dropdown.vue';
import DropdownLink from './Components/DropdownLink.vue';
import CounterButton from './components/CounterButton.vue'; // Komponen contoh Anda
import NavLink from './Components/NavLink.vue';
/**
 * Buat aplikasi Vue utama.
 * Alih-alih createApp({}), kita bisa berikan "root component" kosong
 * agar lebih jelas, meskipun createApp({}) juga valid.
 */
const app = createApp({
    // Opsi root component bisa ditambahkan di sini jika perlu
});

// Daftarkan komponen
app.component('nav-link', NavLink);
app.component('dropdown', Dropdown);
app.component('dropdown-link', DropdownLink);
app.component('counter-button', CounterButton);
/**
 * Daftarkan komponen Vue global yang ingin Anda gunakan di file Blade.
 * Buat folder 'components' di dalam 'resources/js/'
 * Contoh: Buat file 'resources/js/components/UserProfile.vue'
 */
// import UserProfile from './components/UserProfile.vue';
// app.component('user-profile', UserProfile);

/**
 * Mount aplikasi Vue ke elemen #app.
 * Dengan "full build" Vue, ia sekarang akan mengenali dan bisa berinteraksi
 * dengan komponen Vue yang Anda tempatkan di dalam file Blade.
 */
const vueRootElement = document.getElementById('vue-root');
if (vueRootElement) {
    app.mount('#vue-root');
}