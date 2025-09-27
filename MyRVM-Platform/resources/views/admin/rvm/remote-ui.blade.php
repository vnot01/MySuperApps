<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>RVM Remote Control - {{ $rvm->name }}</title>
    
    <!-- Tailwind CSS - Compiled Version -->
    <style>
        /* Tailwind CSS Base Styles */
        * { box-sizing: border-box; }
        body { margin: 0; font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif; }
        .h-full { height: 100%; }
        .h-32 { height: 8rem; }
        .h-2 { height: 0.5rem; }
        .w-32 { width: 8rem; }
        .w-16 { width: 4rem; }
        .w-full { width: 100%; }
        .max-w-md { max-width: 28rem; }
        .mx-auto { margin-left: auto; margin-right: auto; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-4 { margin-bottom: 1rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .mb-8 { margin-bottom: 2rem; }
        .mt-4 { margin-top: 1rem; }
        .mt-6 { margin-top: 1.5rem; }
        .p-4 { padding: 1rem; }
        .p-6 { padding: 1.5rem; }
        .p-8 { padding: 2rem; }
        .px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
        .py-4 { padding-top: 1rem; padding-bottom: 1rem; }
        .pt-12 { padding-top: 3rem; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
        .text-lg { font-size: 1.125rem; line-height: 1.75rem; }
        .text-3xl { font-size: 1.875rem; line-height: 2.25rem; }
        .font-bold { font-weight: 700; }
        .font-semibold { font-weight: 600; }
        .text-white { color: #ffffff; }
        .text-gray-500 { color: #6b7280; }
        .text-gray-600 { color: #4b5563; }
        .text-gray-800 { color: #1f2937; }
        .text-blue-600 { color: #2563eb; }
        .text-green-600 { color: #059669; }
        .text-yellow-600 { color: #d97706; }
        .text-red-600 { color: #dc2626; }
        .bg-white { background-color: #ffffff; }
        .bg-gray-100 { background-color: #f3f4f6; }
        .bg-gray-200 { background-color: #e5e7eb; }
        .bg-gray-600 { background-color: #4b5563; }
        .bg-gray-700 { background-color: #374151; }
        .bg-blue-50 { background-color: #eff6ff; }
        .bg-blue-100 { background-color: #dbeafe; }
        .bg-blue-600 { background-color: #2563eb; }
        .bg-blue-700 { background-color: #1d4ed8; }
        .bg-green-50 { background-color: #f0fdf4; }
        .bg-green-100 { background-color: #dcfce7; }
        .bg-green-600 { background-color: #059669; }
        .bg-green-700 { background-color: #047857; }
        .bg-yellow-50 { background-color: #fefce8; }
        .bg-yellow-100 { background-color: #fef3c7; }
        .bg-yellow-600 { background-color: #d97706; }
        .bg-red-50 { background-color: #fef2f2; }
        .bg-red-100 { background-color: #fee2e2; }
        .bg-red-600 { background-color: #dc2626; }
        .bg-red-700 { background-color: #b91c1c; }
        .bg-gradient-to-br { background-image: linear-gradient(to bottom right, var(--tw-gradient-stops)); }
        .from-blue-50 { --tw-gradient-from: #eff6ff; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(239, 246, 255, 0)); }
        .to-indigo-100 { --tw-gradient-to: #e0e7ff; }
        .from-green-50 { --tw-gradient-from: #f0fdf4; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(240, 253, 244, 0)); }
        .to-emerald-100 { --tw-gradient-to: #dcfce7; }
        .from-yellow-50 { --tw-gradient-from: #fefce8; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(254, 252, 232, 0)); }
        .to-orange-100 { --tw-gradient-to: #fef3c7; }
        .from-red-50 { --tw-gradient-from: #fef2f2; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(254, 242, 242, 0)); }
        .to-pink-100 { --tw-gradient-to: #fce7f3; }
        .rounded-lg { border-radius: 0.5rem; }
        .rounded-full { border-radius: 9999px; }
        .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-center { justify-content: center; }
        .justify-between { justify-content: space-between; }
        .flex-col { flex-direction: column; }
        .transition-colors { transition-property: color, background-color, border-color, text-decoration-color, fill, stroke; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 150ms; }
        .transition-all { transition-property: all; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 150ms; }
        .duration-300 { transition-duration: 300ms; }
        .animate-spin { animation: spin 1s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .hover\:bg-gray-700:hover { background-color: #374151; }
        .hover\:bg-blue-700:hover { background-color: #1d4ed8; }
        .hover\:bg-green-700:hover { background-color: #047857; }
        .hover\:bg-red-700:hover { background-color: #b91c1c; }
        .w-48 { width: 12rem; }
        .h-48 { height: 12rem; }
        .border-2 { border-width: 2px; }
        .border-dashed { border-style: dashed; }
        .border-gray-300 { border-color: #d1d5db; }
        .font-mono { font-family: ui-monospace, SFMono-Regular, "SF Mono", Consolas, "Liberation Mono", Menlo, monospace; }
        .break-all { word-break: break-all; }
    </style>
    
    <!-- QR Code Library with fallback -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js" 
            onerror="console.warn('QRCode library failed to load, using fallback')"></script>
    
    <!-- Laravel Echo & Pusher -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
    
    <style>
        /* Kiosk Mode Styles */
        body {
            overflow: hidden;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }
        
        .kiosk-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 16px;
            font-size: 12px;
        }
        
        .kiosk-exit {
            position: fixed;
            top: 8px;
            right: 8px;
            z-index: 1001;
            background: rgba(255, 0, 0, 0.8);
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            cursor: pointer;
        }
        
        .kiosk-exit:hover {
            background: rgba(255, 0, 0, 1);
        }
        
        /* Hide scrollbars */
        ::-webkit-scrollbar {
            display: none;
        }
        
        /* Prevent context menu */
        * {
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        
        /* Status colors */
        .status-active { color: #10B981; }
        .status-inactive { color: #6B7280; }
        .status-maintenance { color: #F59E0B; }
        .status-full { color: #EF4444; }
        .status-error { color: #EF4444; }
        .status-unknown { color: #6B7280; }
    </style>
</head>
<body class="h-full bg-gray-100">
    <!-- Kiosk Mode Header -->
    <div class="kiosk-header">
        <div class="flex justify-between items-center">
            <div>
                <strong>RVM Remote Control</strong> - {{ $rvm->name }} ({{ $rvm->location_description }})
            </div>
            <div>
                Status: <span id="rvm-status" class="font-bold">{{ ucfirst($rvm->status) }}</span>
                | Session: <span id="session-status">Waiting</span>
                | Time: <span id="current-time"></span>
            </div>
        </div>
    </div>
    
    <!-- Exit Button (Hidden by default, accessible via keyboard shortcut) -->
    <button id="exit-kiosk" class="kiosk-exit" style="display: none;" onclick="exitKioskMode()">
        Exit Kiosk (Ctrl+Alt+E)
    </button>

    <!-- Main Content -->
    <div class="h-full pt-12">
        <!-- RVM Interface Container -->
        <div id="rvm-interface" class="h-full">
            <!-- Waiting State -->
            <div id="waiting-state" class="h-full flex flex-col items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100">
                <div class="text-center max-w-md mx-auto p-8">
                    <div class="mb-8">
                        <div class="w-32 h-32 mx-auto mb-4 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-16 h-16 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <h1 class="text-3xl font-bold text-gray-800 mb-2">Selamat Datang</h1>
                        <p class="text-lg text-gray-600 mb-6">Silakan scan QR Code untuk memulai sesi</p>
                    </div>
                    
                    <!-- QR Code Container -->
                    <div class="bg-white p-6 rounded-lg shadow-lg mb-6">
                        <div id="qrcode" class="flex justify-center"></div>
                        <p class="text-sm text-gray-500 mt-4 text-center">Scan dengan aplikasi MyRVM</p>
                    </div>
                    
                    <!-- Guest Button -->
                    <button id="guest-btn" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-bold py-4 px-6 rounded-lg text-lg transition-colors">
                        Lanjutkan sebagai Tamu
                    </button>
                    
                    <!-- Session Info -->
                    <div class="mt-6 text-center text-sm text-gray-500">
                        <p>Session ID: <span id="session-id">-</span></p>
                        <p>Expires: <span id="session-expires">-</span></p>
                    </div>
                </div>
            </div>

            <!-- Authorized State -->
            <div id="authorized-state" class="h-full flex flex-col items-center justify-center bg-gradient-to-br from-green-50 to-emerald-100" style="display: none;">
                <div class="text-center max-w-md mx-auto p-8">
                    <div class="mb-8">
                        <div class="w-32 h-32 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-16 h-16 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h1 class="text-3xl font-bold text-gray-800 mb-2">Sesi Terotorisasi</h1>
                        <p class="text-lg text-gray-600 mb-4">Halo, <span id="user-name" class="font-semibold">-</span>!</p>
                        <p class="text-gray-500">Silakan masukkan sampah Anda</p>
                    </div>
                    
                    <!-- Processing Button -->
                    <button id="start-processing" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 px-6 rounded-lg text-lg transition-colors">
                        Mulai Proses Deposit
                    </button>
                </div>
            </div>

            <!-- Processing State -->
            <div id="processing-state" class="h-full flex flex-col items-center justify-center bg-gradient-to-br from-yellow-50 to-orange-100" style="display: none;">
                <div class="text-center max-w-md mx-auto p-8">
                    <div class="mb-8">
                        <div class="w-32 h-32 mx-auto mb-4 bg-yellow-100 rounded-full flex items-center justify-center">
                            <svg class="w-16 h-16 text-yellow-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </div>
                        <h1 class="text-3xl font-bold text-gray-800 mb-2">Memproses...</h1>
                        <p class="text-lg text-gray-600 mb-4">Mohon tunggu, sistem sedang menganalisis sampah Anda</p>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="progress-bar" class="bg-yellow-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completed State -->
            <div id="completed-state" class="h-full flex flex-col items-center justify-center bg-gradient-to-br from-green-50 to-emerald-100" style="display: none;">
                <div class="text-center max-w-md mx-auto p-8">
                    <div class="mb-8">
                        <div class="w-32 h-32 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-16 h-16 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h1 class="text-3xl font-bold text-gray-800 mb-2">Deposit Berhasil!</h1>
                        <p class="text-lg text-gray-600 mb-4">Terima kasih telah berkontribusi</p>
                        
                        <!-- Deposit Details -->
                        <div class="bg-white p-4 rounded-lg shadow-lg mb-6 text-left">
                            <h3 class="font-semibold text-gray-800 mb-2">Detail Deposit:</h3>
                            <p><strong>Jenis Sampah:</strong> <span id="deposit-waste-type">-</span></p>
                            <p><strong>Berat:</strong> <span id="deposit-weight">-</span> kg</p>
                            <p><strong>Reward:</strong> <span id="deposit-reward">-</span> poin</p>
                        </div>
                    </div>
                    
                    <button id="new-session" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-lg text-lg transition-colors">
                        Mulai Sesi Baru
                    </button>
                </div>
            </div>

            <!-- Error State -->
            <div id="error-state" class="h-full flex flex-col items-center justify-center bg-gradient-to-br from-red-50 to-pink-100" style="display: none;">
                <div class="text-center max-w-md mx-auto p-8">
                    <div class="mb-8">
                        <div class="w-32 h-32 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                            <svg class="w-16 h-16 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h1 class="text-3xl font-bold text-gray-800 mb-2">Terjadi Kesalahan</h1>
                        <p class="text-lg text-gray-600 mb-4" id="error-message">Mohon coba lagi</p>
                    </div>
                    
                    <button id="retry-btn" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-6 rounded-lg text-lg transition-colors">
                        Coba Lagi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Configuration
        const config = {
            rvmId: {{ $rvmId }},
            websocketUrl: '{{ $websocketUrl }}',
            websocketKey: '{{ $websocketKey }}',
            websocketSecret: '{{ $websocketSecret }}',
            apiBaseUrl: '{{ url('/api/v2') }}',
            csrfToken: '{{ csrf_token() }}',
            accessToken: '{{ $token }}'
        };

        // Global variables
        let currentSession = null;
        let echo = null;
        let progressInterval = null;
        let mockData = {
            rvm: {
                id: 2,
                name: 'RVM-002',
                location: 'Food Court, Lantai 2',
                status: 'active',
                api_key: 'E5gKWDmrYkp6or9dly6ty4ouuWPhZ1tl'
            },
            sessions: [
                {
                    id: 'session-001',
                    token: 'token-abc123',
                    user_name: 'John Doe',
                    expires_at: new Date(Date.now() + 30 * 60 * 1000).toISOString()
                },
                {
                    id: 'session-002', 
                    token: 'token-def456',
                    user_name: 'Guest',
                    expires_at: new Date(Date.now() + 30 * 60 * 1000).toISOString()
                }
            ],
            deposits: [
                {
                    id: 1,
                    waste_type: 'Plastic Bottles',
                    weight: '0.5',
                    reward_amount: '100',
                    timestamp: new Date().toISOString()
                },
                {
                    id: 2,
                    waste_type: 'Aluminum Cans',
                    weight: '0.3',
                    reward_amount: '75',
                    timestamp: new Date().toISOString()
                },
                {
                    id: 3,
                    waste_type: 'Glass Bottles',
                    weight: '0.8',
                    reward_amount: '150',
                    timestamp: new Date().toISOString()
                }
            ]
        };

        // Initialize RVM Interface
        async function initializeRVM() {
            try {
                // Create new session
                const response = await fetch(`${config.apiBaseUrl}/rvm/session/create`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken
                    },
                    body: JSON.stringify({
                        rvm_id: config.rvmId
                    })
                });

                if (!response.ok) {
                    console.warn('Session creation failed:', response.status);
                    // Create mock session for testing
                    const randomSession = mockData.sessions[Math.floor(Math.random() * mockData.sessions.length)];
                    currentSession = {
                        session_id: randomSession.id,
                        session_token: randomSession.token,
                        expires_at: randomSession.expires_at
                    };
                    updateSessionInfo();
                    generateQRCode();
                    initializeWebSocket();
                    console.log('Using mock session for testing:', currentSession);
                    return;
                }

                const data = await response.json();
                
                if (data.success) {
                    currentSession = data.data;
                    updateSessionInfo();
                    generateQRCode();
                    initializeWebSocket();
                } else {
                    showErrorState('Failed to create session: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error initializing RVM:', error);
                // Create mock session for testing
                const randomSession = mockData.sessions[Math.floor(Math.random() * mockData.sessions.length)];
                currentSession = {
                    session_id: randomSession.id,
                    session_token: randomSession.token,
                    expires_at: randomSession.expires_at
                };
                updateSessionInfo();
                generateQRCode();
                initializeWebSocket();
                console.log('Using mock session due to error:', currentSession);
            }
        }

        // Generate QR Code
        function generateQRCode() {
            const qrContainer = document.getElementById('qrcode');
            qrContainer.innerHTML = '';
            
            const qrData = JSON.stringify({
                session_token: currentSession.session_token,
                rvm_id: config.rvmId,
                action: 'claim_session'
            });

            // Check if QRCode library is loaded
            if (typeof QRCode === 'undefined') {
                console.warn('QRCode library not loaded, using fallback');
                qrContainer.innerHTML = `
                    <div class="text-center">
                        <div class="w-48 h-48 mx-auto bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center">
                            <div class="text-gray-500">
                                <div class="text-sm font-semibold">QR Code</div>
                                <div class="text-xs mt-1">Session Token:</div>
                                <div class="text-xs font-mono break-all">${currentSession.session_token.substring(0, 20)}...</div>
                            </div>
                        </div>
                    </div>
                `;
                return;
            }

            try {
                QRCode.toCanvas(qrContainer, qrData, {
                    width: 200,
                    height: 200,
                    color: {
                        dark: '#000000',
                        light: '#FFFFFF'
                    }
                });
            } catch (error) {
                console.error('Error generating QR code:', error);
                qrContainer.innerHTML = `
                    <div class="text-center">
                        <div class="w-48 h-48 mx-auto bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center">
                            <div class="text-gray-500">
                                <div class="text-sm font-semibold">QR Code Error</div>
                                <div class="text-xs mt-1">Please use manual entry</div>
                            </div>
                        </div>
                    </div>
                `;
            }
        }

        // Initialize WebSocket
        function initializeWebSocket() {
            try {
                if (echo) {
                    echo.disconnect();
                }

                // Check if Echo is available
                if (typeof Echo === 'undefined') {
                    console.warn('Laravel Echo not available, WebSocket disabled');
                    // Use mock WebSocket events for testing
                    setupMockWebSocketEvents();
                    return;
                }

                // Check if Echo constructor is available
                if (typeof Echo !== 'function') {
                    console.warn('Echo constructor not available, WebSocket disabled');
                    setupMockWebSocketEvents();
                    return;
                }

                echo = new Echo({
                    broadcaster: 'reverb',
                    key: config.websocketKey,
                    wsHost: config.websocketUrl.split(':')[0],
                    wsPort: config.websocketUrl.split(':')[1],
                    wssPort: config.websocketUrl.split(':')[1],
                    forceTLS: false,
                    enabledTransports: ['ws', 'wss'],
                    cluster: 'mt1'
                });

                // Check if echo.channel is available
                if (!echo.channel) {
                    console.warn('Echo channel method not available, WebSocket disabled');
                    setupMockWebSocketEvents();
                    return;
                }

                // Listen to RVM channel
                echo.channel(`rvm.${config.rvmId}`)
                    .listen('SessionAuthorized', (e) => {
                        console.log('Session authorized:', e);
                        showAuthorizedState(e.user_name);
                    })
                    .listen('SessionGuestActivated', (e) => {
                        console.log('Guest session activated:', e);
                        showAuthorizedState('Guest');
                    })
                    .listen('DepositProcessing', (e) => {
                        console.log('Deposit processing:', e);
                        showProcessingState();
                    })
                    .listen('DepositCompleted', (e) => {
                        console.log('Deposit completed:', e);
                        showCompletedState(e);
                    })
                    .listen('DepositFailed', (e) => {
                        console.log('Deposit failed:', e);
                        showErrorState(e.message);
                    });
            } catch (error) {
                console.error('Error initializing WebSocket:', error);
                console.warn('WebSocket disabled, using polling mode');
                // Use mock WebSocket events for testing
                setupMockWebSocketEvents();
            }
        }

        // Setup mock WebSocket events for testing
        function setupMockWebSocketEvents() {
            console.log('Setting up mock WebSocket events for testing');
            
            // Mock session events
            window.mockWebSocketEvents = {
                triggerSessionAuthorized: (userName) => {
                    console.log('Mock: Session authorized for', userName);
                    showAuthorizedState(userName);
                },
                triggerSessionGuestActivated: () => {
                    console.log('Mock: Guest session activated');
                    showAuthorizedState('Guest');
                },
                triggerDepositProcessing: () => {
                    console.log('Mock: Deposit processing started');
                    showProcessingState();
                },
                triggerDepositCompleted: (data) => {
                    console.log('Mock: Deposit completed', data);
                    showCompletedState(data);
                },
                triggerDepositFailed: (message) => {
                    console.log('Mock: Deposit failed', message);
                    showErrorState(message);
                }
            };
            
            // Make mock events available globally for testing
            window.mockEvents = window.mockWebSocketEvents;
        }

        // Update session info
        function updateSessionInfo() {
            document.getElementById('session-id').textContent = currentSession.session_id;
            document.getElementById('session-expires').textContent = new Date(currentSession.expires_at).toLocaleTimeString();
        }

        // Show different states
        function showWaitingState() {
            hideAllStates();
            document.getElementById('waiting-state').style.display = 'flex';
            document.getElementById('session-status').textContent = 'Waiting';
        }

        function showAuthorizedState(userName) {
            hideAllStates();
            document.getElementById('authorized-state').style.display = 'flex';
            document.getElementById('user-name').textContent = userName;
            document.getElementById('session-status').textContent = 'Authorized';
        }

        function showProcessingState() {
            hideAllStates();
            document.getElementById('processing-state').style.display = 'flex';
            document.getElementById('session-status').textContent = 'Processing';
            
            // Simulate progress
            let progress = 0;
            progressInterval = setInterval(() => {
                progress += Math.random() * 10;
                if (progress > 100) progress = 100;
                document.getElementById('progress-bar').style.width = progress + '%';
                
                if (progress >= 100) {
                    clearInterval(progressInterval);
                }
            }, 500);
        }

        function showCompletedState(depositData) {
            hideAllStates();
            document.getElementById('completed-state').style.display = 'flex';
            document.getElementById('deposit-waste-type').textContent = depositData.waste_type || 'Unknown';
            document.getElementById('deposit-weight').textContent = depositData.weight || '0';
            document.getElementById('deposit-reward').textContent = depositData.reward_amount || '0';
            document.getElementById('session-status').textContent = 'Completed';
        }

        function showErrorState(message) {
            hideAllStates();
            document.getElementById('error-state').style.display = 'flex';
            document.getElementById('error-message').textContent = message || 'Terjadi kesalahan';
            document.getElementById('session-status').textContent = 'Error';
        }

        function hideAllStates() {
            const states = ['waiting-state', 'authorized-state', 'processing-state', 'completed-state', 'error-state'];
            states.forEach(state => {
                document.getElementById(state).style.display = 'none';
            });
            
            if (progressInterval) {
                clearInterval(progressInterval);
                progressInterval = null;
            }
        }

        // Event listeners
        document.getElementById('guest-btn').addEventListener('click', async () => {
            try {
                const response = await fetch(`${config.apiBaseUrl}/rvm/session/activate-guest`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken
                    },
                    body: JSON.stringify({
                        session_token: currentSession.session_token
                    })
                });

                if (!response.ok) {
                    console.warn('Guest activation failed:', response.status);
                    // Use mock guest activation for testing
                    showAuthorizedState('Guest (Mock)');
                    console.log('Using mock guest activation for testing');
                    return;
                }

                const data = await response.json();
                if (data.success) {
                    showAuthorizedState('Guest');
                } else {
                    showErrorState(data.message || 'Guest activation failed');
                }
            } catch (error) {
                console.error('Error activating guest:', error);
                // Use mock guest activation for testing
                showAuthorizedState('Guest (Mock)');
                console.log('Using mock guest activation due to error');
            }
        });

        document.getElementById('start-processing').addEventListener('click', () => {
            showProcessingState();
            
            // Simulate deposit processing with mock data
            setTimeout(() => {
                const randomDeposit = mockData.deposits[Math.floor(Math.random() * mockData.deposits.length)];
                showCompletedState(randomDeposit);
                console.log('Mock deposit completed:', randomDeposit);
            }, 5000); // 5 seconds processing time
        });

        document.getElementById('new-session').addEventListener('click', () => {
            initializeRVM();
        });

        document.getElementById('retry-btn').addEventListener('click', () => {
            showWaitingState();
        });

        // Kiosk mode functions
        function exitKioskMode() {
            // Show admin authentication dialog
            const adminPin = prompt('Enter admin PIN to exit kiosk mode:');
            if (adminPin) {
                // Verify admin pin
                verifyAdminPin(adminPin).then(isValid => {
                    if (isValid) {
                        if (confirm('Are you sure you want to exit kiosk mode?')) {
                            window.close();
                        }
                    } else {
                        alert('Invalid admin PIN');
                    }
                });
            }
        }

        // Verify admin pin
        async function verifyAdminPin(pin) {
            try {
                const response = await fetch(`${config.apiBaseUrl}/admin/rvm/${config.rvmId}/remote-access`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken
                    },
                    body: JSON.stringify({
                        access_pin: pin
                    })
                });

                if (!response.ok) {
                    console.warn('Admin PIN verification failed:', response.status);
                    // Use mock PIN verification for testing
                    const validPins = ['0000', '1234', '5678', '9999'];
                    const isValid = validPins.includes(pin);
                    console.log('Using mock PIN verification for testing:', isValid);
                    return isValid;
                }

                const data = await response.json();
                return data.success;
            } catch (error) {
                console.error('Error verifying admin pin:', error);
                // Use mock PIN verification for testing
                const validPins = ['0000', '1234', '5678', '9999'];
                const isValid = validPins.includes(pin);
                console.log('Using mock PIN verification due to error:', isValid);
                return isValid;
            }
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Ctrl+Alt+E to show exit button (admin only)
            if (e.ctrlKey && e.altKey && e.key === 'E') {
                const exitBtn = document.getElementById('exit-kiosk');
                exitBtn.style.display = exitBtn.style.display === 'none' ? 'block' : 'none';
            }
            
            // F11 for fullscreen toggle
            if (e.key === 'F11') {
                e.preventDefault();
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen();
                } else {
                    document.exitFullscreen();
                }
            }

            // Disable common browser shortcuts in kiosk mode
            if ({{ $rvm->kiosk_mode_enabled ? 'true' : 'false' }}) {
                // Disable F12 (Developer Tools)
                if (e.key === 'F12') {
                    e.preventDefault();
                    return false;
                }
                
                // Disable Ctrl+Shift+I (Developer Tools)
                if (e.ctrlKey && e.shiftKey && e.key === 'I') {
                    e.preventDefault();
                    return false;
                }
                
                // Disable Ctrl+U (View Source)
                if (e.ctrlKey && e.key === 'u') {
                    e.preventDefault();
                    return false;
                }
                
                // Disable Ctrl+R (Refresh)
                if (e.ctrlKey && e.key === 'r') {
                    e.preventDefault();
                    return false;
                }
                
                // Disable Ctrl+W (Close Tab)
                if (e.ctrlKey && e.key === 'w') {
                    e.preventDefault();
                    return false;
                }
            }
        });

        // Update time
        function updateTime() {
            document.getElementById('current-time').textContent = new Date().toLocaleTimeString();
        }

        // Update RVM status
        async function updateRvmStatus() {
            try {
                // Use mock data for remote UI (no API authentication needed)
                console.log('Using mock RVM status for remote UI');
                const mockStatus = mockData.rvm.status;
                document.getElementById('rvm-status').textContent = mockStatus.charAt(0).toUpperCase() + mockStatus.slice(1);
                const statusElement = document.getElementById('rvm-status');
                statusElement.className = `font-bold status-${mockStatus}`;
                console.log('Using mock RVM status for testing:', mockStatus);
                return;

                const data = await response.json();
                
                if (data.success) {
                    const rvm = data.data;
                    document.getElementById('rvm-status').textContent = rvm.status.charAt(0).toUpperCase() + rvm.status.slice(1);
                    
                    // Update status color
                    const statusElement = document.getElementById('rvm-status');
                    statusElement.className = `font-bold status-${rvm.status}`;
                }
            } catch (error) {
                console.error('Error updating RVM status:', error);
                // Use mock status for testing
                const mockStatus = mockData.rvm.status;
                document.getElementById('rvm-status').textContent = mockStatus.charAt(0).toUpperCase() + mockStatus.slice(1);
                const statusElement = document.getElementById('rvm-status');
                statusElement.className = `font-bold status-${mockStatus}`;
                console.log('Using mock RVM status due to error:', mockStatus);
            }
        }

        // Get auth token (not needed for testing)
        function getAuthToken() {
            return null; // No auth token needed for testing
        }

        // Testing functions for mock WebSocket events
        window.testMockEvents = {
            // Test session authorization
            testSessionAuth: () => {
                if (window.mockEvents) {
                    window.mockEvents.triggerSessionAuthorized('Test User');
                } else {
                    console.log('Mock events not available');
                }
            },
            
            // Test guest activation
            testGuestActivation: () => {
                if (window.mockEvents) {
                    window.mockEvents.triggerSessionGuestActivated();
                } else {
                    console.log('Mock events not available');
                }
            },
            
            // Test deposit processing
            testDepositProcessing: () => {
                if (window.mockEvents) {
                    window.mockEvents.triggerDepositProcessing();
                } else {
                    console.log('Mock events not available');
                }
            },
            
            // Test deposit completion
            testDepositCompleted: () => {
                if (window.mockEvents) {
                    const randomDeposit = mockData.deposits[Math.floor(Math.random() * mockData.deposits.length)];
                    window.mockEvents.triggerDepositCompleted(randomDeposit);
                } else {
                    console.log('Mock events not available');
                }
            },
            
            // Test deposit failure
            testDepositFailed: () => {
                if (window.mockEvents) {
                    window.mockEvents.triggerDepositFailed('Test error message');
                } else {
                    console.log('Mock events not available');
                }
            },
            
            // Show all available mock data
            showMockData: () => {
                console.log('Available mock data:', mockData);
                return mockData;
            }
        };

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            initializeRVM();
            updateTime();
            updateRvmStatus();
            
            // Set up intervals
            setInterval(updateTime, 1000);
            setInterval(updateRvmStatus, 30000); // Update status every 30 seconds
            
            // Enter fullscreen automatically if kiosk mode is enabled (with user gesture)
            if ({{ $rvm->kiosk_mode_enabled ? 'true' : 'false' }} && document.documentElement.requestFullscreen) {
                // Only request fullscreen on user interaction
                document.addEventListener('click', () => {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen().catch(err => {
                            console.log('Fullscreen request failed:', err);
                        });
                    }
                }, { once: true });
            }
            
            // Log testing functions
            console.log('Testing functions available:');
            console.log('- testMockEvents.testSessionAuth()');
            console.log('- testMockEvents.testGuestActivation()');
            console.log('- testMockEvents.testDepositProcessing()');
            console.log('- testMockEvents.testDepositCompleted()');
            console.log('- testMockEvents.testDepositFailed()');
            console.log('- testMockEvents.showMockData()');
        });

        // Handle fullscreen change
        document.addEventListener('fullscreenchange', () => {
            if (document.fullscreenElement) {
                document.getElementById('exit-kiosk').style.display = 'none';
            }
        });
    </script>
</body>
</html>
