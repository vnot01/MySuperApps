<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyRVM - {{ $rvm->name }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- QR Code Library -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    
    <!-- Laravel Echo & Pusher -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            overflow: hidden;
        }
        
        .rvm-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
        }
        
        .status-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            min-width: 400px;
            min-height: 300px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .qr-container {
            margin: 1rem 0;
            padding: 1rem;
            background: white;
            border-radius: 10px;
        }
        
        .status-indicator {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 10px;
        }
        
        .status-waiting { background-color: #fbbf24; }
        .status-authorized { background-color: #10b981; }
        .status-processing { background-color: #3b82f6; }
        .status-completed { background-color: #059669; }
        .status-error { background-color: #ef4444; }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 0.5rem;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .hidden {
            display: none !important;
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</head>
<body>
    <div id="rvm-ui" class="rvm-container">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold mb-2">MyRVM</h1>
            <p class="text-xl opacity-90">{{ $rvm->name }}</p>
            <p class="text-sm opacity-75">{{ $rvm->location }}</p>
        </div>
        
        <!-- Main Status Card -->
        <div class="status-card fade-in">
            <!-- Waiting for Authorization -->
            <div id="waiting-state" class="state-container">
                <div class="mb-4">
                    <span class="status-indicator status-waiting pulse"></span>
                    <span class="text-lg font-semibold">Menunggu Otorisasi</span>
                </div>
                <p class="text-sm opacity-90 mb-4">Pindai QR Code dengan aplikasi MyRVM untuk memulai</p>
                <div class="qr-container">
                    <canvas id="qr-code"></canvas>
                </div>
                <p class="text-xs opacity-75 mt-2">Session ID: <span id="session-id"></span></p>
                <button id="guest-btn" class="btn btn-secondary mt-4">
                    Lanjutkan sebagai Tamu
                </button>
            </div>
            
            <!-- Authorized State -->
            <div id="authorized-state" class="state-container hidden">
                <div class="mb-4">
                    <span class="status-indicator status-authorized"></span>
                    <span class="text-lg font-semibold">Sesi Diotorisasi</span>
                </div>
                <p class="text-sm opacity-90 mb-2">Selamat datang,</p>
                <p class="text-xl font-bold mb-4" id="user-name">Loading...</p>
                <p class="text-sm opacity-75">Silakan masukkan sampah ke dalam mesin</p>
            </div>
            
            <!-- Processing State -->
            <div id="processing-state" class="state-container hidden">
                <div class="mb-4">
                    <span class="status-indicator status-processing pulse"></span>
                    <span class="text-lg font-semibold">Memproses Sampah</span>
                </div>
                <p class="text-sm opacity-90 mb-4">Sedang menganalisis sampah yang dimasukkan...</p>
                <div class="loading-spinner">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-white mx-auto"></div>
                </div>
            </div>
            
            <!-- Completed State -->
            <div id="completed-state" class="state-container hidden">
                <div class="mb-4">
                    <span class="status-indicator status-completed"></span>
                    <span class="text-lg font-semibold">Selesai!</span>
                </div>
                <div class="text-center">
                    <p class="text-sm opacity-90 mb-2">Jenis Sampah:</p>
                    <p class="text-xl font-bold mb-2" id="waste-type">-</p>
                    <p class="text-sm opacity-90 mb-2">Berat:</p>
                    <p class="text-lg font-semibold mb-4" id="waste-weight">-</p>
                    <p class="text-sm opacity-90 mb-2">Reward:</p>
                    <p class="text-2xl font-bold text-green-300" id="reward-amount">-</p>
                </div>
                <button id="new-session-btn" class="btn btn-primary mt-4">
                    Mulai Sesi Baru
                </button>
            </div>
            
            <!-- Error State -->
            <div id="error-state" class="state-container hidden">
                <div class="mb-4">
                    <span class="status-indicator status-error"></span>
                    <span class="text-lg font-semibold">Terjadi Kesalahan</span>
                </div>
                <p class="text-sm opacity-90 mb-4" id="error-message">Silakan coba lagi</p>
                <button id="retry-btn" class="btn btn-primary">
                    Coba Lagi
                </button>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-sm opacity-75">Powered by MyRVM v2.1</p>
        </div>
    </div>

    <script>
        // Configuration
        const config = {
            rvmId: '{{ $rvmId }}',
            websocketUrl: '{{ $websocketUrl }}',
            websocketKey: '{{ $websocketKey }}',
            websocketSecret: '{{ $websocketSecret }}',
            apiBaseUrl: '/api/v2',
            csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        };
        
        // Global state
        let currentSession = null;
        let websocketConnection = null;
        let qrCodeCanvas = null;
        
        // Initialize RVM UI
        document.addEventListener('DOMContentLoaded', function() {
            initializeRVM();
        });
        
        async function initializeRVM() {
            try {
                // Create new session
                await createNewSession();
                
                // Initialize WebSocket connection
                initializeWebSocket();
                
                // Setup event listeners
                setupEventListeners();
                
            } catch (error) {
                console.error('Failed to initialize RVM:', error);
                showError('Gagal menginisialisasi RVM');
            }
        }
        
        async function createNewSession() {
            try {
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
                    throw new Error('Failed to create session');
                }
                
                const data = await response.json();
                currentSession = data.data;
                
                // Generate QR Code
                generateQRCode(currentSession.session_token);
                
                // Update UI
                document.getElementById('session-id').textContent = currentSession.id;
                showState('waiting');
                
            } catch (error) {
                console.error('Failed to create session:', error);
                throw error;
            }
        }
        
        function generateQRCode(sessionToken) {
            const canvas = document.getElementById('qr-code');
            const qrData = {
                type: 'rvm_session',
                token: sessionToken,
                rvm_id: config.rvmId,
                timestamp: Date.now()
            };
            
            QRCode.toCanvas(canvas, JSON.stringify(qrData), {
                width: 200,
                height: 200,
                color: {
                    dark: '#000000',
                    light: '#FFFFFF'
                }
            }, function (error) {
                if (error) {
                    console.error('Failed to generate QR code:', error);
                }
            });
        }
        
        function initializeWebSocket() {
            // Initialize Pusher connection
            const pusher = new Pusher(config.websocketKey, {
                cluster: 'ap1',
                wsHost: config.websocketUrl.split(':')[0],
                wsPort: parseInt(config.websocketUrl.split(':')[1]),
                wssPort: parseInt(config.websocketUrl.split(':')[1]),
                forceTLS: false,
                enabledTransports: ['ws', 'wss']
            });
            
            // Subscribe to RVM channel
            const channel = pusher.subscribe(`rvm.${config.rvmId}`);
            
            // Listen for session events
            channel.bind('session.authorized', function(data) {
                console.log('Session authorized:', data);
                handleSessionAuthorized(data);
            });
            
            channel.bind('session.guest-activated', function(data) {
                console.log('Guest session activated:', data);
                handleGuestSessionActivated(data);
            });
            
            channel.bind('deposit.processing', function(data) {
                console.log('Deposit processing:', data);
                handleDepositProcessing(data);
            });
            
            channel.bind('deposit.completed', function(data) {
                console.log('Deposit completed:', data);
                handleDepositCompleted(data);
            });
            
            channel.bind('deposit.failed', function(data) {
                console.log('Deposit failed:', data);
                handleDepositFailed(data);
            });
            
            websocketConnection = pusher;
        }
        
        function setupEventListeners() {
            // Guest button
            document.getElementById('guest-btn').addEventListener('click', async function() {
                await activateGuestSession();
            });
            
            // New session button
            document.getElementById('new-session-btn').addEventListener('click', async function() {
                await createNewSession();
            });
            
            // Retry button
            document.getElementById('retry-btn').addEventListener('click', async function() {
                await createNewSession();
            });
        }
        
        async function activateGuestSession() {
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
                    throw new Error('Failed to activate guest session');
                }
                
                const data = await response.json();
                console.log('Guest session activated:', data);
                
            } catch (error) {
                console.error('Failed to activate guest session:', error);
                showError('Gagal mengaktifkan sesi tamu');
            }
        }
        
        function handleSessionAuthorized(data) {
            document.getElementById('user-name').textContent = data.user_name || 'User';
            showState('authorized');
        }
        
        function handleGuestSessionActivated(data) {
            document.getElementById('user-name').textContent = 'Tamu';
            showState('authorized');
        }
        
        function handleDepositProcessing(data) {
            showState('processing');
        }
        
        function handleDepositCompleted(data) {
            document.getElementById('waste-type').textContent = data.waste_type || '-';
            document.getElementById('waste-weight').textContent = data.weight ? `${data.weight} kg` : '-';
            document.getElementById('reward-amount').textContent = data.reward_amount ? `Rp ${data.reward_amount}` : '-';
            showState('completed');
        }
        
        function handleDepositFailed(data) {
            showError(data.message || 'Gagal memproses sampah');
        }
        
        function showState(state) {
            // Hide all states
            const states = ['waiting', 'authorized', 'processing', 'completed', 'error'];
            states.forEach(s => {
                document.getElementById(`${s}-state`).classList.add('hidden');
            });
            
            // Show target state
            document.getElementById(`${state}-state`).classList.remove('hidden');
        }
        
        function showError(message) {
            document.getElementById('error-message').textContent = message;
            showState('error');
        }
        
        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (websocketConnection) {
                websocketConnection.disconnect();
            }
        });
    </script>
</body>
</html>
