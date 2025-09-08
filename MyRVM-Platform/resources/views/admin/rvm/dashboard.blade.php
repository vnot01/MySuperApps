<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>RVM Admin Dashboard - MyRVM Platform</title>
    
    <!-- Authentication Script - Inline untuk menghindari 404 error -->
    <script>
        // Inline AuthManager untuk menghindari 404 error
        class AuthManager {
            constructor() {
                this.token = localStorage.getItem('auth_token');
                this.user = JSON.parse(localStorage.getItem('auth_user') || 'null');
                this.baseUrl = '/api/v2';
            }

            async login(email, password) {
                try {
                    const response = await fetch(`${this.baseUrl}/auth/login`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify({ email, password })
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.token = data.data.token;
                        this.user = data.data.user;
                        
                        localStorage.setItem('auth_token', this.token);
                        localStorage.setItem('auth_user', JSON.stringify(this.user));
                        
                        return { success: true, data: data.data };
                    } else {
                        return { success: false, message: data.message };
                    }
                } catch (error) {
                    console.error('Login error:', error);
                    return { success: false, message: 'Network error occurred' };
                }
            }

            async logout() {
                try {
                    if (this.token) {
                        await fetch(`${this.baseUrl}/auth/logout`, {
                            method: 'POST',
                            headers: {
                                'Authorization': `Bearer ${this.token}`,
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            }
                        });
                    }
                } catch (error) {
                    console.error('Logout error:', error);
                } finally {
                    this.token = null;
                    this.user = null;
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('auth_user');
                }
            }

            async getMe() {
                try {
                    if (!this.token) {
                        return { success: false, message: 'No token available' };
                    }

                    const response = await fetch(`${this.baseUrl}/auth/me`, {
                        method: 'GET',
                        headers: {
                            'Authorization': `Bearer ${this.token}`,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.user = data.data.user;
                        localStorage.setItem('auth_user', JSON.stringify(this.user));
                        return { success: true, data: data.data };
                    } else {
                        this.logout();
                        return { success: false, message: data.message };
                    }
                } catch (error) {
                    console.error('Get user info error:', error);
                    return { success: false, message: 'Network error occurred' };
                }
            }

            async apiRequest(url, options = {}) {
                const defaultOptions = {
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                };

                if (this.token) {
                    defaultOptions.headers['Authorization'] = `Bearer ${this.token}`;
                }

                const finalOptions = {
                    ...defaultOptions,
                    ...options,
                    headers: {
                        ...defaultOptions.headers,
                        ...options.headers
                    }
                };

                try {
                    const response = await fetch(url, finalOptions);
                    
                    if (response.status === 401) {
                        this.logout();
                        window.location.href = '/admin/login';
                        return { success: false, message: 'Session expired' };
                    }

                    const data = await response.json();
                    return { success: response.ok, data, status: response.status };
                } catch (error) {
                    console.error('API request error:', error);
                    return { success: false, message: 'Network error occurred' };
                }
            }

            isAuthenticated() {
                return !!this.token && !!this.user;
            }

            getCurrentUser() {
                return this.user;
            }

            getToken() {
                return this.token;
            }
        }

         // Create global instance
         window.authManager = new AuthManager();
         
         // Initialize Laravel Echo with error handling
         try {
             if (typeof Echo !== 'undefined') {
                 window.Echo = new Echo({
                     broadcaster: 'reverb',
                     key: '{{ env('REVERB_APP_KEY') }}',
                     wsHost: '{{ env('REVERB_HOST') }}',
                     wsPort: {{ env('REVERB_PORT') }},
                     wssPort: {{ env('REVERB_PORT') }},
                     forceTLS: false,
                     enabledTransports: ['ws', 'wss'],
                     auth: {
                         headers: {
                             'Authorization': 'Bearer ' + window.authManager.getToken(),
                             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                         }
                     }
                 });
                 console.log('Echo initialized successfully');
             } else {
                 console.warn('Echo library not available, disabling real-time features');
                 window.Echo = null;
             }
         } catch (error) {
             console.error('Echo initialization failed:', error);
             // Fallback: disable real-time features
             window.Echo = null;
         }
    </script>
    
    <!-- Tailwind CSS - Compiled Version -->
    <style>
        /* Tailwind CSS Base Styles */
        * { box-sizing: border-box; }
        body { margin: 0; font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif; }
        .bg-white { background-color: #ffffff; }
        .bg-gray-50 { background-color: #f9fafb; }
        .bg-gray-100 { background-color: #f3f4f6; }
        .bg-gray-200 { background-color: #e5e7eb; }
        .bg-gray-300 { background-color: #d1d5db; }
        .bg-gray-500 { background-color: #6b7280; }
        .bg-gray-600 { background-color: #4b5563; }
        .bg-gray-700 { background-color: #374151; }
        .bg-gray-800 { background-color: #1f2937; }
        .bg-gray-900 { background-color: #111827; }
        .bg-blue-50 { background-color: #eff6ff; }
        .bg-blue-100 { background-color: #dbeafe; }
        .bg-blue-500 { background-color: #3b82f6; }
        .bg-blue-600 { background-color: #2563eb; }
        .bg-blue-700 { background-color: #1d4ed8; }
        .bg-green-50 { background-color: #f0fdf4; }
        .bg-green-100 { background-color: #dcfce7; }
        .bg-green-500 { background-color: #22c55e; }
        .bg-green-600 { background-color: #16a34a; }
        .bg-green-700 { background-color: #15803d; }
        .bg-green-800 { background-color: #166534; }
        .bg-yellow-50 { background-color: #fefce8; }
        .bg-yellow-100 { background-color: #fef3c7; }
        .bg-yellow-500 { background-color: #eab308; }
        .bg-yellow-600 { background-color: #ca8a04; }
        .bg-yellow-700 { background-color: #a16207; }
        .bg-yellow-800 { background-color: #854d0e; }
        .bg-red-50 { background-color: #fef2f2; }
        .bg-red-100 { background-color: #fee2e2; }
        .bg-red-500 { background-color: #ef4444; }
        .bg-red-600 { background-color: #dc2626; }
        .bg-red-700 { background-color: #b91c1c; }
        .bg-red-800 { background-color: #991b1b; }
        .text-white { color: #ffffff; }
        .text-gray-500 { color: #6b7280; }
        .text-gray-600 { color: #4b5563; }
        .text-gray-700 { color: #374151; }
        .text-gray-800 { color: #1f2937; }
        .text-gray-900 { color: #111827; }
        .text-blue-600 { color: #2563eb; }
        .text-blue-700 { color: #1d4ed8; }
        .text-green-500 { color: #22c55e; }
        .text-green-600 { color: #16a34a; }
        .text-green-700 { color: #15803d; }
        .text-green-800 { color: #166534; }
        .text-yellow-600 { color: #ca8a04; }
        .text-yellow-700 { color: #a16207; }
        .text-red-500 { color: #ef4444; }
        .text-red-600 { color: #dc2626; }
        .text-red-700 { color: #b91c1c; }
        .border { border-width: 1px; }
        .border-gray-200 { border-color: #e5e7eb; }
        .border-gray-300 { border-color: #d1d5db; }
        .border-blue-300 { border-color: #93c5fd; }
        .border-green-300 { border-color: #86efac; }
        .border-yellow-300 { border-color: #fde047; }
        .border-red-300 { border-color: #fca5a5; }
        .rounded { border-radius: 0.25rem; }
        .rounded-md { border-radius: 0.375rem; }
        .rounded-lg { border-radius: 0.5rem; }
        .rounded-full { border-radius: 9999px; }
        .shadow { box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); }
        .shadow-sm { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
        .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
        .p-2 { padding: 0.5rem; }
        .p-3 { padding: 0.75rem; }
        .p-4 { padding: 1rem; }
        .p-5 { padding: 1.25rem; }
        .p-6 { padding: 1.5rem; }
        .p-8 { padding: 2rem; }
        .px-3 { padding-left: 0.75rem; padding-right: 0.75rem; }
        .px-4 { padding-left: 1rem; padding-right: 1rem; }
        .px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
        .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
        .py-3 { padding-top: 0.75rem; padding-bottom: 0.75rem; }
        .py-4 { padding-top: 1rem; padding-bottom: 1rem; }
        .py-8 { padding-top: 2rem; padding-bottom: 2rem; }
        .m-0 { margin: 0; }
        .mx-auto { margin-left: auto; margin-right: auto; }
        .ml-3 { margin-left: 0.75rem; }
        .ml-4 { margin-left: 1rem; }
        .ml-5 { margin-left: 1.25rem; }
        .mr-1 { margin-right: 0.25rem; }
        .mr-2 { margin-right: 0.5rem; }
        .mt-1 { margin-top: 0.25rem; }
        .mt-3 { margin-top: 0.75rem; }
        .mt-4 { margin-top: 1rem; }
        .mt-6 { margin-top: 1.5rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-4 { margin-bottom: 1rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .mb-8 { margin-bottom: 2rem; }
        .w-0 { width: 0; }
        .w-10 { width: 2.5rem; }
        .w-32 { width: 8rem; }
        .w-96 { width: 24rem; }
        .w-full { width: 100%; }
        .h-2 { height: 0.5rem; }
        .h-10 { height: 2.5rem; }
        .h-32 { height: 8rem; }
        .h-full { height: 100%; }
        .max-w-7xl { max-width: 80rem; }
        .max-w-md { max-width: 28rem; }
        .flex { display: flex; }
        .flex-shrink-0 { flex-shrink: 0; }
        .flex-1 { flex: 1 1 0%; }
        .items-center { align-items: center; }
        .justify-center { justify-content: center; }
        .justify-between { justify-content: space-between; }
        .justify-end { justify-content: flex-end; }
        .space-x-2 > * + * { margin-left: 0.5rem; }
        .space-x-3 > * + * { margin-left: 0.75rem; }
        .space-x-4 > * + * { margin-left: 1rem; }
        .space-y-3 > * + * { margin-top: 0.75rem; }
        .grid { display: grid; }
        .grid-cols-1 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
        .grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .gap-6 { gap: 1.5rem; }
        .gap-8 { gap: 2rem; }
        .overflow-hidden { overflow: hidden; }
        .overflow-x-auto { overflow-x: auto; }
        .overflow-y-auto { overflow-y: auto; }
        .whitespace-nowrap { white-space: nowrap; }
        .text-left { text-align: left; }
        .text-center { text-center: center; }
        .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
        .text-lg { font-size: 1.125rem; line-height: 1.75rem; }
        .text-xl { font-size: 1.25rem; line-height: 1.75rem; }
        .text-2xl { font-size: 1.5rem; line-height: 2rem; }
        .text-3xl { font-size: 1.875rem; line-height: 2.25rem; }
        .font-medium { font-weight: 500; }
        .font-semibold { font-weight: 600; }
        .font-bold { font-weight: 700; }
        .uppercase { text-transform: uppercase; }
        .tracking-wider { letter-spacing: 0.05em; }
        .truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .inline-flex { display: inline-flex; }
        .inline-block { display: inline-block; }
        .block { display: block; }
        .hidden { display: none; }
        .relative { position: relative; }
        .fixed { position: fixed; }
        .inset-0 { top: 0; right: 0; bottom: 0; left: 0; }
        .top-20 { top: 5rem; }
        .right-8 { right: 2rem; }
        .z-1000 { z-index: 1000; }
        .z-1001 { z-index: 1001; }
        .border-b { border-bottom-width: 1px; }
        .border-t { border-top-width: 1px; }
        .divide-y > * + * { border-top-width: 1px; }
        .divide-gray-200 > * + * { border-color: #e5e7eb; }
        .bg-opacity-50 { background-color: rgba(0, 0, 0, 0.5); }
        .bg-opacity-80 { background-color: rgba(0, 0, 0, 0.8); }
        .hover\:bg-gray-50:hover { background-color: #f9fafb; }
        .hover\:bg-gray-100:hover { background-color: #f3f4f6; }
        .hover\:bg-gray-200:hover { background-color: #e5e7eb; }
        .hover\:bg-blue-50:hover { background-color: #eff6ff; }
        .hover\:bg-blue-100:hover { background-color: #dbeafe; }
        .hover\:bg-blue-700:hover { background-color: #1d4ed8; }
        .hover\:bg-green-50:hover { background-color: #f0fdf4; }
        .hover\:bg-green-100:hover { background-color: #dcfce7; }
        .hover\:bg-green-700:hover { background-color: #15803d; }
        .hover\:bg-yellow-50:hover { background-color: #fefce8; }
        .hover\:bg-yellow-100:hover { background-color: #fef3c7; }
        .hover\:bg-red-50:hover { background-color: #fef2f2; }
        .hover\:bg-red-100:hover { background-color: #fee2e2; }
        .hover\:bg-red-700:hover { background-color: #b91c1c; }
        .hover\:text-blue-900:hover { color: #1e3a8a; }
        .hover\:text-yellow-900:hover { color: #78350f; }
        .focus\:outline-none:focus { outline: 2px solid transparent; outline-offset: 2px; }
        .focus\:ring-2:focus { box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5); }
        .focus\:ring-offset-2:focus { box-shadow: 0 0 0 2px #ffffff, 0 0 0 4px rgba(59, 130, 246, 0.5); }
        .focus\:ring-blue-500:focus { box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5); }
        .focus\:ring-green-500:focus { box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.5); }
        .focus\:ring-yellow-500:focus { box-shadow: 0 0 0 3px rgba(234, 179, 8, 0.5); }
        .focus\:ring-red-500:focus { box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.5); }
        .focus\:ring-gray-500:focus { box-shadow: 0 0 0 3px rgba(107, 114, 128, 0.5); }
        .focus\:border-blue-500:focus { border-color: #3b82f6; }
        .transition-colors { transition-property: color, background-color, border-color, text-decoration-color, fill, stroke; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 150ms; }
        .transition-all { transition-property: all; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 150ms; }
        .duration-200 { transition-duration: 200ms; }
        .duration-300 { transition-duration: 300ms; }
        .cursor-pointer { cursor: pointer; }
        .cursor-not-allowed { cursor: not-allowed; }
        .opacity-50 { opacity: 0.5; }
        .disabled\:opacity-50:disabled { opacity: 0.5; }
        .disabled\:cursor-not-allowed:disabled { cursor: not-allowed; }
        @media (min-width: 768px) {
            .md\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (min-width: 1024px) {
            .lg\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .lg\:grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }
    </style>
    
    <!-- Chart.js - Compatible Version -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    
     <!-- Laravel Echo & Pusher -->
     <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
     <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
     
     <!-- Check if Echo loaded successfully -->
     <script>
         if (typeof Echo === 'undefined') {
             console.warn('Echo not loaded, WebSocket features will be disabled');
         } else {
             console.log('Echo loaded successfully');
         }
     </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Fix body height and allow proper scrolling */
        html, body {
            height: 100%;
            overflow-x: hidden;
            overflow-y: auto; /* Allow vertical scroll */
            margin: 0;
            padding: 0;
        }
        
        body {
            background-color: #f9fafb !important;
            position: relative; /* Allow scrolling */
            width: 100%;
        }
        
        main {
            min-height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            position: relative;
            background-color: #f9fafb;
        }
        
        /* Prevent layout shift and blinking */
        .dashboard-container {
            min-height: 100vh;
            position: relative;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        /* Prevent content jumping */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        /* RVM Monitoring Card Layout */
        .rvm-monitoring-container {
            padding: 1rem;
            max-height: 60vh; /* Set maximum height */
            overflow-y: auto; /* Enable vertical scrolling */
            overflow-x: hidden; /* Prevent horizontal scroll */
        }
        
        .rvm-card {
            background: #ffffff;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }
        
        .rvm-card:hover {
            background: #f9fafb;
            border-color: #d1d5db;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .rvm-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .rvm-card-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        
        .status-dot.active { background-color: #10b981; }
        .status-dot.inactive { background-color: #6b7280; }
        .status-dot.maintenance { background-color: #f59e0b; }
        .status-dot.full { background-color: #ef4444; }
        .status-dot.error { background-color: #dc2626; }
        .status-dot.unknown { background-color: #8b5cf6; }
        
        .rvm-title {
            color: #1f2937;
            font-weight: 600;
            font-size: 1rem;
        }
        
        .rvm-description {
            color: #6b7280;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
            line-height: 1.4;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        
        .rvm-details {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #9ca3af;
            font-size: 0.75rem;
        }
        
        .api-key-container {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .api-key-text {
            font-family: 'Courier New', monospace;
            background: #f3f4f6;
            color: #1f2937;
            padding: 0.125rem 0.25rem;
            border-radius: 4px;
            font-size: 0.7rem;
            border: 1px solid #e5e7eb;
        }
        
        .copy-icon {
            cursor: pointer;
            color: #6b7280;
            transition: color 0.2s ease;
        }
        
        .copy-icon:hover {
            color: #10b981;
        }
        
        .edit-button {
            background: #ffffff;
            color: #1f2937;
            border: 1px solid #d1d5db;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .edit-button:hover {
            background: #f9fafb;
            border-color: #9ca3af;
        }
        
        .edit-button i {
            font-size: 0.75rem;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .rvm-card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .rvm-details {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }
        
        .status-active { @apply bg-green-100 text-green-800 border-green-200; }
        .status-inactive { @apply bg-gray-100 text-gray-800 border-gray-200; }
        .status-maintenance { @apply bg-yellow-100 text-yellow-800 border-yellow-200; }
        .status-full { @apply bg-red-100 text-red-800 border-red-200; }
        .status-error { @apply bg-red-100 text-red-800 border-red-200; }
        .status-unknown { @apply bg-gray-100 text-gray-800 border-gray-200; }
        
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .loading-spinner {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Ensure main content has proper height */
        main {
            min-height: calc(100vh - 80px);
            overflow-y: auto;
            position: relative;
            background-color: #f9fafb;
            padding-top: 0; /* Remove top padding since header is sticky */
        }
        
        /* Sticky header styles */
        header {
            position: sticky;
            top: 0;
            z-index: 50;
            background-color: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }
        
        /* Fix table container */
        .overflow-x-auto {
            max-height: 400px;
            overflow-y: auto;
        }
        
        /* Prevent infinite scrolling */
        .bg-white {
            background-color: white !important;
        }
        
        /* Ensure proper container height */
        .max-w-7xl {
            max-width: 80rem;
            margin: 0 auto;
        }
    </style>
</head>
<body class="h-full bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-gray-900">RVM Admin Dashboard</h1>
                    <span class="ml-3 px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                        POS System
                    </span>
                </div>
                <div class="flex items-center space-x-4">
                    <button id="refresh-btn" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-sync-alt mr-2"></i>
                        Refresh
                    </button>
                    <div class="text-sm text-gray-500">
                        Last updated: <span id="last-updated">-</span>
                    </div>
                    <div class="flex items-center space-x-3 border-l pl-4">
                        <div class="text-sm">
                            <div class="font-medium text-gray-900" id="user-name">Loading...</div>
                            <div class="text-gray-500" id="user-role">Loading...</div>
                        </div>
                        <button id="logout-btn" class="inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 dashboard-container">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg card-hover transition-all duration-200">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-server text-2xl text-blue-600"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total RVM</dt>
                                <dd class="text-lg font-medium text-gray-900" id="total-rvms">-</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg card-hover transition-all duration-200">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-play-circle text-2xl text-green-600"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Active Sessions</dt>
                                <dd class="text-lg font-medium text-gray-900" id="active-sessions">-</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg card-hover transition-all duration-200">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-recycle text-2xl text-purple-600"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Deposits Today</dt>
                                <dd class="text-lg font-medium text-gray-900" id="deposits-today">-</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg card-hover transition-all duration-200">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Issues</dt>
                                <dd class="text-lg font-medium text-gray-900" id="total-issues">-</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RVM Status Overview -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Status Chart -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">RVM Status Distribution</h3>
                <div class="chart-container">
                    <canvas id="statusChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <button id="bulk-maintenance-btn" class="w-full flex items-center justify-center px-4 py-2 border border-yellow-300 rounded-md shadow-sm text-sm font-medium text-yellow-700 bg-yellow-50 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                        <i class="fas fa-wrench mr-2"></i>
                        Set All to Maintenance Mode
                    </button>
                    <button id="bulk-active-btn" class="w-full flex items-center justify-center px-4 py-2 border border-green-300 rounded-md shadow-sm text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <i class="fas fa-play mr-2"></i>
                        Set All to Active
                    </button>
                    <button id="export-data-btn" class="w-full flex items-center justify-center px-4 py-2 border border-blue-300 rounded-md shadow-sm text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-download mr-2"></i>
                        Export Monitoring Data
                    </button>
                </div>
            </div>
        </div>

        <!-- RVM List -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">RVM Monitoring</h3>
                        <p class="mt-1 text-sm text-gray-500">Real-time status monitoring and remote control</p>
                    </div>
                    <button onclick="loadMonitoringData()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                        <i class="fas fa-sync-alt mr-2"></i>Refresh Data
                    </button>
                </div>
            </div>
            
            <div class="rvm-monitoring-container">
                <div id="rvm-cards-container">
                    <!-- RVM cards will be populated here -->
                </div>
            </div>
        </div>
    </main>

    <!-- Remote Access Modal -->
    <div id="remote-access-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Remote Access</h3>
                    <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="mb-4">
                    <p class="text-sm text-gray-600">Enter access PIN to connect to RVM:</p>
                    <p class="text-sm font-medium text-gray-900" id="modal-rvm-name">-</p>
                </div>
                
                <div class="mb-4">
                    <label for="access-pin" class="block text-sm font-medium text-gray-700">Access PIN</label>
                    <input type="password" id="access-pin" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" placeholder="Enter PIN">
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button id="cancel-access" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Cancel
                    </button>
                    <button id="connect-rvm" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Connect
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div id="status-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Update RVM Status</h3>
                    <button id="close-status-modal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="mb-4">
                    <p class="text-sm text-gray-600">Update status for:</p>
                    <p class="text-sm font-medium text-gray-900" id="status-modal-rvm-name">-</p>
                </div>
                
                <div class="mb-4">
                    <label for="new-status" class="block text-sm font-medium text-gray-700">New Status</label>
                    <select id="new-status" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="full">Full</option>
                        <option value="error">Error</option>
                        <option value="unknown">Unknown</option>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button id="cancel-status" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Cancel
                    </button>
                    <button id="update-status" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Update
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Configuration
        const config = {
            apiBaseUrl: '{{ url('/api/v2') }}',
            csrfToken: '{{ csrf_token() }}',
            refreshInterval: 30000 // 30 seconds
        };

        // Global variables
        let monitoringData = null;
        let statusChart = null;
        let currentRvmId = null;
        let refreshInterval = null;
        let chartUpdateTimeout = null;
        let isChartUpdating = false;
        let eventListeners = new Map(); // Track event listeners for cleanup
        let performanceMetrics = {
            lastUpdate: Date.now(),
            updateCount: 0,
            averageUpdateTime: 0
        };

        // Initialize dashboard
        async function initializeDashboard() {
            console.log('Initializing dashboard...');
            try {
                // Initialize chart first
                initializeStatusChart();
                setupEventListeners();
                
                // Setup real-time listeners
                setupRealtimeListeners();
                
                startAutoRefresh();
                
                // Load data after everything is initialized
                await loadMonitoringData();
                
                console.log('Dashboard initialized successfully');
            } catch (error) {
                console.error('Error initializing dashboard:', error);
            }
        }

        // Load monitoring data
        async function loadMonitoringData() {
            try {
                showLoading(true);
                console.log('Loading monitoring data...');
                
                const result = await makeAuthenticatedRequest(`${config.apiBaseUrl}/admin/rvm/monitoring`);
                if (!result.success) {
                    throw new Error(result.message || 'Failed to load monitoring data');
                }
                const response = { ok: true, json: () => Promise.resolve(result.data) };

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('Monitoring data received:', data);
                
                if (data.success && data.data) {
                    monitoringData = data.data;
                    updateDashboard();
                    updateLastUpdated();
                    console.log('Dashboard updated successfully');
                } else {
                    console.error('Invalid response data:', data);
                    showError('Failed to load monitoring data: ' + (data.message || 'Invalid response'));
                }
            } catch (error) {
                console.error('Error loading monitoring data:', error);
                showError('Failed to load monitoring data: ' + error.message);
            } finally {
                showLoading(false);
            }
        }

        // Update dashboard with new data
        function updateDashboard() {
            console.log('Updating dashboard with data:', monitoringData);
            
            if (!monitoringData) {
                console.error('No monitoring data available');
                return;
            }

            try {
                // Update statistics
                const totalRvmsEl = document.getElementById('total-rvms');
                const activeSessionsEl = document.getElementById('active-sessions');
                const depositsTodayEl = document.getElementById('deposits-today');
                const totalIssuesEl = document.getElementById('total-issues');
                
                if (totalRvmsEl) totalRvmsEl.textContent = monitoringData.total_rvms || 0;
                if (activeSessionsEl) activeSessionsEl.textContent = monitoringData.active_sessions || 0;
                if (depositsTodayEl) depositsTodayEl.textContent = monitoringData.total_deposits_today || 0;
                
                // Calculate issues (error + full + unknown)
                const issues = (monitoringData.status_counts?.error || 0) + 
                              (monitoringData.status_counts?.full || 0) + 
                              (monitoringData.status_counts?.unknown || 0);
                if (totalIssuesEl) totalIssuesEl.textContent = issues;

                console.log('Statistics updated:', {
                    total_rvms: monitoringData.total_rvms,
                    active_sessions: monitoringData.active_sessions,
                    deposits_today: monitoringData.total_deposits_today,
                    issues: issues
                });

                // Update status chart
                updateStatusChart();

                // Update RVM table
                updateRvmTable();
                
                console.log('Dashboard update completed');
            } catch (error) {
                console.error('Error updating dashboard:', error);
            }
        }

        // Update status chart with debouncing
        function updateStatusChart() {
            console.log('Updating status chart...');
            
            if (!statusChart) {
                console.error('Status chart not initialized');
                return;
            }
            
            if (!monitoringData || !monitoringData.status_counts) {
                console.error('No status counts data available');
                return;
            }

            // Prevent multiple simultaneous updates
            if (isChartUpdating) {
                console.log('Chart update already in progress, skipping...');
                return;
            }

            // Clear any pending updates
            if (chartUpdateTimeout) {
                clearTimeout(chartUpdateTimeout);
            }

            // Debounce chart updates
            chartUpdateTimeout = setTimeout(() => {
                if (isChartUpdating) return;
                
                isChartUpdating = true;
                
                try {
                    const startTime = performance.now();
                    const statusCounts = monitoringData.status_counts;
                    console.log('Status counts:', statusCounts);
                    
                    const labels = Object.keys(statusCounts).map(status => 
                        status.charAt(0).toUpperCase() + status.slice(1)
                    );
                    const data = Object.values(statusCounts);
                    const colors = {
                        'active': '#10B981',
                        'inactive': '#6B7280',
                        'maintenance': '#F59E0B',
                        'full': '#EF4444',
                        'error': '#EF4444',
                        'unknown': '#6B7280'
                    };
                    const backgroundColors = Object.keys(statusCounts).map(status => colors[status] || '#6B7280');

                    // Update chart data without triggering animations
                    statusChart.data.labels = labels;
                    statusChart.data.datasets[0].data = data;
                    statusChart.data.datasets[0].backgroundColor = backgroundColors;
                    
                    // Update chart without animation using requestAnimationFrame
                    requestAnimationFrame(() => {
                        statusChart.update('none');
                        
                        // Performance monitoring
                        const endTime = performance.now();
                        const updateTime = endTime - startTime;
                        performanceMetrics.updateCount++;
                        performanceMetrics.averageUpdateTime = 
                            (performanceMetrics.averageUpdateTime * (performanceMetrics.updateCount - 1) + updateTime) / performanceMetrics.updateCount;
                        performanceMetrics.lastUpdate = Date.now();
                        
                        // Log performance if update takes too long
                        if (updateTime > 16) { // More than one frame (16ms)
                            console.warn(`Chart update took ${updateTime.toFixed(2)}ms`);
                        }
                    });
                    
                    console.log('Status chart updated successfully');
                } catch (error) {
                    console.error('Error updating status chart:', error);
                } finally {
                    isChartUpdating = false;
                }
            }, 200); // Debounce to 200ms
        }

        // Update RVM cards
        function updateRvmTable() {
            console.log('Updating RVM cards...');
            
            const container = document.getElementById('rvm-cards-container');
            if (!container) {
                console.error('RVM cards container not found');
                return;
            }
            
            container.innerHTML = '';

            if (!monitoringData || !monitoringData.rvms) {
                console.error('No monitoring data available for RVM cards');
                return;
            }

            console.log('RVM data:', monitoringData.rvms);
            console.log('Number of RVMs:', monitoringData.rvms.length);

            monitoringData.rvms.forEach((rvm, index) => {
                console.log(`Processing RVM ${index + 1}:`, rvm);
                
                const card = document.createElement('div');
                card.className = 'rvm-card';
                card.setAttribute('data-rvm-id', rvm.id);
                
                // Status dot color
                const statusDotClass = rvm.status || 'unknown';
                const statusText = rvm.status ? rvm.status.charAt(0).toUpperCase() + rvm.status.slice(1) : 'Unknown';
                
                // Format dates
                const createdDate = rvm.created_at ? new Date(rvm.created_at).toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                }) : 'Unknown';
                
                const lastUpdate = rvm.last_status_change ? new Date(rvm.last_status_change).toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                }) : 'Never';
                
                // API Key (truncated) - Use real data or generate if not exists
                let apiKey = rvm.api_key;
                if (!apiKey || apiKey === 'N/A') {
                    // Generate a simple API key for demo
                    apiKey = 'RVM_' + rvm.id + '_' + Math.random().toString(36).substr(2, 8).toUpperCase();
                }
                const truncatedApiKey = apiKey.length > 8 ? '...' + apiKey.slice(-8) : apiKey;

                card.innerHTML = `
                    <div class="rvm-card-header">
                        <div class="rvm-card-title">
                            <div class="status-dot ${statusDotClass}"></div>
                            <div class="rvm-title">${rvm.name || 'Unknown RVM'}</div>
                        </div>
                        <div class="flex gap-2">
                            <button class="edit-button" onclick="openRemoteAccess(${rvm.id}, \`${rvm.name}\`)" title="Remote Access">
                                <i class="fas fa-desktop mr-1"></i>Remote
                            </button>
                            <button class="edit-button" onclick="openStatusUpdate(${rvm.id}, \`${rvm.name}\`, \`${rvm.status}\`)" title="Edit Status">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                        </div>
                    </div>
                    <div class="rvm-description">
                        ${rvm.location || 'No location specified'}
                    </div>
                    <div class="rvm-details">
                        <span>Dibuat: ${createdDate}</span>
                        <span>•</span>
                        <span>Last Update: ${lastUpdate}</span>
                        <span>•</span>
                        <div class="api-key-container">
                            <span>API Key: </span>
                            <span class="api-key-text">${truncatedApiKey}</span>
                            <i class="fas fa-copy copy-icon" onclick="copyApiKey('${apiKey}')" title="Copy API Key"></i>
                        </div>
                    </div>
                `;

                container.appendChild(card);
            });
            
            console.log('RVM cards updated successfully');
        }

        // Get status icon
        function getStatusIcon(status) {
            const icons = {
                'active': 'check-circle',
                'inactive': 'pause-circle',
                'maintenance': 'wrench',
                'full': 'exclamation-triangle',
                'error': 'times-circle',
                'unknown': 'question-circle'
            };
            return icons[status] || 'question-circle';
        }

        // Copy API Key to clipboard
        function copyApiKey(apiKey) {
            if (!apiKey || apiKey === 'N/A') {
                showNotification('No API Key available', 'error');
                return;
            }

            navigator.clipboard.writeText(apiKey).then(() => {
                showNotification('API Key copied to clipboard!', 'success');
            }).catch(err => {
                console.error('Failed to copy API Key:', err);
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = apiKey;
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
                    showNotification('API Key copied to clipboard!', 'success');
                } catch (fallbackErr) {
                    console.error('Fallback copy failed:', fallbackErr);
                    showNotification('Failed to copy API Key', 'error');
                }
                document.body.removeChild(textArea);
            });
        }

        // Initialize status chart
        function initializeStatusChart() {
            console.log('Initializing status chart...');
            try {
                const canvas = document.getElementById('statusChart');
                if (!canvas) {
                    console.error('Status chart canvas not found');
                    return;
                }
                
                const ctx = canvas.getContext('2d');
                statusChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: [],
                        datasets: [{
                            data: [],
                            backgroundColor: [],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 0, // Disable animations for better performance
                            animateRotate: false,
                            animateScale: false
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20
                                }
                            },
                            tooltip: {
                                enabled: true,
                                mode: 'index',
                                intersect: false
                            }
                        },
                        elements: {
                            arc: {
                                borderWidth: 2
                            }
                        }
                    }
                });
                console.log('Status chart initialized successfully');
            } catch (error) {
                console.error('Error initializing status chart:', error);
            }
        }

        // Setup event listeners with cleanup tracking
        function setupEventListeners() {
            // Clear existing listeners first
            cleanupEventListeners();
            
            // Refresh button
            addEventListener('refresh-btn', 'click', loadMonitoringData);

            // Remote access modal
            addEventListener('close-modal', 'click', closeRemoteAccessModal);
            addEventListener('cancel-access', 'click', closeRemoteAccessModal);
            addEventListener('connect-rvm', 'click', connectToRvm);

            // Status update modal
            addEventListener('close-status-modal', 'click', closeStatusModal);
            addEventListener('cancel-status', 'click', closeStatusModal);
            addEventListener('update-status', 'click', updateRvmStatus);

            // Quick actions
            addEventListener('bulk-maintenance-btn', 'click', () => bulkUpdateStatus('maintenance'));
            addEventListener('bulk-active-btn', 'click', () => bulkUpdateStatus('active'));
            addEventListener('export-data-btn', 'click', exportData);
        }

        // Add event listener with tracking
        function addEventListener(elementId, event, handler) {
            const element = document.getElementById(elementId);
            if (element) {
                element.addEventListener(event, handler);
                eventListeners.set(`${elementId}-${event}`, { element, event, handler });
            }
        }

        // Cleanup event listeners
        function cleanupEventListeners() {
            eventListeners.forEach(({ element, event, handler }) => {
                element.removeEventListener(event, handler);
            });
            eventListeners.clear();
        }

        // Open remote access modal
        function openRemoteAccess(rvmId, rvmName) {
            console.log('Opening remote access for RVM:', rvmId, rvmName);
            currentRvmId = rvmId;
            document.getElementById('modal-rvm-name').textContent = rvmName;
            document.getElementById('access-pin').value = '';
            document.getElementById('remote-access-modal').classList.remove('hidden');
        }

        // Close remote access modal
        function closeRemoteAccessModal() {
            document.getElementById('remote-access-modal').classList.add('hidden');
            currentRvmId = null;
        }

        // Connect to RVM
        async function connectToRvm() {
            const pin = document.getElementById('access-pin').value;
            if (!pin) {
                alert('Please enter access PIN');
                return;
            }

            try {
                const result = await makeAuthenticatedRequest(`${config.apiBaseUrl}/admin/rvm/${currentRvmId}/remote-access`, {
                    method: 'POST',
                    body: JSON.stringify({
                        access_pin: pin
                    })
                });
                if (!result.success) {
                    throw new Error(result.message || 'Failed to access RVM');
                }
                const response = { ok: true, json: () => Promise.resolve(result.data) };

                const data = await response.json();
                
                if (data.success) {
                    // Open remote access in new window
                    window.open(data.data.access_url, '_blank', 'width=1024,height=768');
                    closeRemoteAccessModal();
                } else {
                    alert('Failed to connect: ' + data.message);
                }
            } catch (error) {
                console.error('Error connecting to RVM:', error);
                alert('Failed to connect to RVM');
            }
        }

        // Open status update modal
        function openStatusUpdate(rvmId, rvmName, currentStatus) {
            currentRvmId = rvmId;
            document.getElementById('status-modal-rvm-name').textContent = rvmName;
            document.getElementById('new-status').value = currentStatus;
            document.getElementById('status-modal').classList.remove('hidden');
        }

        // Close status modal
        function closeStatusModal() {
            document.getElementById('status-modal').classList.add('hidden');
            currentRvmId = null;
        }

        // Update RVM status
        async function updateRvmStatus() {
            const newStatus = document.getElementById('new-status').value;
            
            try {
                const result = await makeAuthenticatedRequest(`${config.apiBaseUrl}/admin/rvm/${currentRvmId}/status`, {
                    method: 'POST',
                    body: JSON.stringify({
                        status: newStatus
                    })
                });
                if (!result.success) {
                    throw new Error(result.message || 'Failed to update RVM status');
                }
                const data = result.data;
                
                if (data.success) {
                    closeStatusModal();
                    loadMonitoringData(); // Refresh data
                } else {
                    alert('Failed to update status: ' + data.message);
                }
            } catch (error) {
                console.error('Error updating status:', error);
                alert('Failed to update RVM status');
            }
        }

        // Bulk update status
        async function bulkUpdateStatus(status) {
            if (!confirm(`Are you sure you want to set all RVMs to ${status}?`)) {
                return;
            }

            try {
                const promises = monitoringData.rvms.map(rvm => 
                    makeAuthenticatedRequest(`${config.apiBaseUrl}/admin/rvm/${rvm.id}/status`, {
                        method: 'POST',
                        body: JSON.stringify({ status })
                    })
                );

                await Promise.all(promises);
                loadMonitoringData(); // Refresh data
            } catch (error) {
                console.error('Error bulk updating status:', error);
                alert('Failed to update RVM statuses');
            }
        }

        // Export data
        function exportData() {
            if (!monitoringData) return;

            const exportData = {
                timestamp: new Date().toISOString(),
                summary: {
                    total_rvms: monitoringData.total_rvms,
                    active_sessions: monitoringData.active_sessions,
                    total_sessions_today: monitoringData.total_sessions_today,
                    total_deposits_today: monitoringData.total_deposits_today,
                    status_counts: monitoringData.status_counts
                },
                rvms: monitoringData.rvms
            };

            const blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `rvm-monitoring-${new Date().toISOString().split('T')[0]}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        // Auto refresh
        function startAutoRefresh() {
            // Enable auto refresh for real-time updates
            refreshInterval = setInterval(loadMonitoringData, 30000); // 30 seconds
            console.log('Auto refresh enabled - refreshing every 30 seconds');
        }

        // Stop auto refresh
        function stopAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
                refreshInterval = null;
            }
        }

        // Setup real-time listeners
        function setupRealtimeListeners() {
            console.log('Setting up real-time listeners...');
            
            // Check if Echo is available
            if (!window.Echo) {
                console.warn('Echo not available, skipping real-time listeners');
                return;
            }
            
            try {
                // Listen for RVM status updates
                window.Echo.channel('rvm-status')
                    .listen('rvm.status.updated', (e) => {
                        console.log('RVM Status Update Received:', e);
                        handleRvmStatusUpdate(e);
                    })
                    .error((error) => {
                        console.error('RVM status channel error:', error);
                    });

                // Listen for dashboard data updates
                window.Echo.private('admin-dashboard')
                    .listen('dashboard.data.updated', (e) => {
                        console.log('Dashboard Data Update Received:', e);
                        handleDashboardDataUpdate(e);
                    })
                    .error((error) => {
                        console.error('Admin dashboard channel error:', error);
                    });

                console.log('Real-time listeners setup successfully');
            } catch (error) {
                console.error('Error setting up real-time listeners:', error);
            }
        }

        // Handle RVM status update
        function handleRvmStatusUpdate(data) {
            console.log('Handling RVM status update:', data);
            
            // Update the specific RVM card
            const rvmCard = document.querySelector(`.rvm-card[data-rvm-id="${data.rvm_id}"]`);
            if (rvmCard) {
                const statusDot = rvmCard.querySelector('.status-dot');
                if (statusDot) {
                    // Remove old status classes
                    statusDot.className = 'status-dot';
                    statusDot.classList.add(data.status);
                    
                    console.log(`Updated RVM ${data.rvm_id} status dot to ${data.status}`);
                } else {
                    console.warn(`Status dot not found for RVM ${data.rvm_id}`);
                }
            } else {
                console.warn(`RVM card not found for ID ${data.rvm_id}`);
            }
            
            // Show notification
            showNotification(`RVM ${data.rvm_name} status updated to ${data.status}`, 'info');
        }

        // Handle dashboard data update
        function handleDashboardDataUpdate(data) {
            console.log('Handling dashboard data update:', data);
            
            // Update monitoring data
            monitoringData = data;
            
            // Update statistics
            updateStatistics(data);
            
            // Update status chart
            updateStatusChart();
            
            // Show notification
            showNotification('Dashboard data updated', 'success');
        }

        // Show notification
        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500 text-white' :
                type === 'error' ? 'bg-red-500 text-white' :
                type === 'warning' ? 'bg-yellow-500 text-black' :
                'bg-blue-500 text-white'
            }`;
            notification.textContent = message;
            
            // Add to page
            document.body.appendChild(notification);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Update last updated time
        function updateLastUpdated() {
            document.getElementById('last-updated').textContent = new Date().toLocaleTimeString();
        }

        // Show loading state
        function showLoading(show) {
            const refreshBtn = document.getElementById('refresh-btn');
            if (show) {
                refreshBtn.innerHTML = '<div class="loading-spinner mr-2"></div>Loading...';
                refreshBtn.disabled = true;
            } else {
                refreshBtn.innerHTML = '<i class="fas fa-sync-alt mr-2"></i>Refresh';
                refreshBtn.disabled = false;
            }
        }

        // Show error
        function showError(message) {
            alert('Error: ' + message);
        }

        // Auth token not needed for testing

        // Cleanup function with comprehensive cleanup
        function cleanup() {
            console.log('Cleaning up dashboard resources...');
            
            // Clear intervals and timeouts
            if (chartUpdateTimeout) {
                clearTimeout(chartUpdateTimeout);
                chartUpdateTimeout = null;
            }
            if (refreshInterval) {
                clearInterval(refreshInterval);
                refreshInterval = null;
            }
            
            // Cleanup event listeners
            cleanupEventListeners();
            
            // Destroy chart
            if (statusChart) {
                statusChart.destroy();
                statusChart = null;
            }
            
            // Clear global variables
            monitoringData = null;
            currentRvmId = null;
            isChartUpdating = false;
            
            // Log performance metrics
            console.log('Performance Metrics:', {
                totalUpdates: performanceMetrics.updateCount,
                averageUpdateTime: `${performanceMetrics.averageUpdateTime.toFixed(2)}ms`,
                lastUpdate: new Date(performanceMetrics.lastUpdate).toLocaleTimeString()
            });
            
            console.log('Dashboard cleanup completed');
        }

        // Authentication and user management
        async function initializeAuth() {
            console.log('Initializing auth...');
            console.log('Token:', window.authManager.getToken());
            console.log('User:', window.authManager.getCurrentUser());
            
            // Check if user is authenticated
            if (!window.authManager.isAuthenticated()) {
                console.log('User not authenticated, redirecting to login...');
                window.location.href = '/admin/login';
                return;
            }
            
            console.log('User is authenticated, proceeding...');

            // Get user info
            const user = window.authManager.getCurrentUser();
            if (user) {
                document.getElementById('user-name').textContent = user.name;
                document.getElementById('user-role').textContent = user.role;
            } else {
                // Try to refresh user info
                const result = await window.authManager.getMe();
                if (result.success) {
                    document.getElementById('user-name').textContent = result.data.user.name;
                    document.getElementById('user-role').textContent = result.data.user.role;
                } else {
                    window.location.href = '/admin/login';
                    return;
                }
            }

            // Setup logout button
            document.getElementById('logout-btn').addEventListener('click', async () => {
                if (confirm('Are you sure you want to logout?')) {
                    await window.authManager.logout();
                    window.location.href = '/admin/login';
                }
            });
        }

        // Update API calls to use authentication
        async function makeAuthenticatedRequest(url, options = {}) {
            return await window.authManager.apiRequest(url, options);
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', async () => {
            await initializeAuth();
            initializeDashboard();
        });

        // Cleanup when page unloads
        window.addEventListener('beforeunload', cleanup);

        // Stop auto refresh when page is hidden
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                stopAutoRefresh();
            } else {
                startAutoRefresh();
            }
        });
    </script>
</body>
</html>
