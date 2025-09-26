<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RvmUIController;
use App\Http\Controllers\AdminRvmController;
use App\Http\Controllers\GeminiDashboardController;
use App\Http\Controllers\CvPlaygroundController;
use App\Http\Controllers\Admin\EdgeVisionController;
use App\Http\Controllers\Admin\ProcessingEngineController;
use App\Http\Controllers\Admin\RvmController;
use App\Http\Controllers\Admin\RemoteAccessController;
use App\Http\Controllers\Admin\ConfigurationController;
use App\Http\Controllers\Admin\SystemMonitoringController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\EnhancedMetricsController;
use App\Http\Controllers\Admin\RemoteCommandsController;
use App\Http\Controllers\Admin\OTAManagementController;
use App\Http\Controllers\Admin\EnhancedRemoteCommandsController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\Api\RvmAuthController;

// Route::get('/', function () {
//     return view('welcome');
// });


// --- TAMBAHKAN RUTE INI ---
Route::get('/', function () {
    // Cek jika user sudah login
    if (Auth::check()) {
        $user = Auth::user();
        // Cek jika user memiliki peran yang bisa mengakses dasbor admin
        if (in_array($user->role?->slug, ['super-admin', 'admin', 'tenant'])) {
            return redirect()->route('admin.dashboard');
        }
        // Jika user biasa, arahkan ke dasbor user biasa
        return redirect()->route('dashboard');
    }
    // Jika belum login, arahkan ke halaman login
    return redirect()->route('login');
})->name('home'); // <-- Memberi nama 'home' pada rute root
// -------------------------

// Rute dasbor user biasa (dari Breeze)
Route::get('/dashboard', [AdminDashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// Rute profil (dari Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Grup rute admin Anda
Route::middleware(['auth', 'verified']) // Anda bisa tambahkan 'role:...' di sini atau di grup/rute individu
    ->prefix('web')
    ->name('admin.')
    ->group(function () {
        // Contoh rute dasbor admin
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');
        
        // Timezone Management
        Route::prefix('timezone')->name('timezone.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\TimezoneController::class, 'index'])->name('index');
            Route::get('/dashboard-data', [App\Http\Controllers\Admin\TimezoneController::class, 'getDashboardData'])->name('dashboard-data');
            Route::get('/device/{deviceId}', [App\Http\Controllers\Admin\TimezoneController::class, 'getDeviceDetails'])->name('device-details');
            Route::post('/manual-sync', [App\Http\Controllers\Admin\TimezoneController::class, 'triggerManualSync'])->name('manual-sync');
            Route::get('/statistics', [App\Http\Controllers\Admin\TimezoneController::class, 'getStatistics'])->name('statistics');
        });
        
        // ... (rute admin lainnya: users, tenants, dll.)
        Route::get('rvm/pulse-check/{rvm?}', [RvmController::class, 'manualPulseCheck'])->name('rvm.pulse-check.get');
        Route::get('rvm/health-check/{rvm?}', [RvmController::class, 'manualHealthCheck'])->name('rvm.health-check');
    });


// RVM UI Routes (Public access for RVM displays)
Route::get('/rvm-ui/{rvm}', [RvmUIController::class, 'show'])->name('rvm.ui');

// Admin RVM API Routes (Protected with authentication)
Route::middleware(['auth:sanctum'])->group(function () {
    // Admin RVM API Management
    Route::prefix('api/admin/rvm')->name('api.admin.rvm.')->group(function () {
        Route::get('/list', [AdminRvmController::class, 'getRvmList'])->name('list');
        Route::get('/monitoring', [AdminRvmController::class, 'getRvmMonitoring'])->name('monitoring');
        Route::get('/{rvmId}/details', [AdminRvmController::class, 'getRvmDetails'])->name('details');
        Route::post('/{rvmId}/remote-access', [AdminRvmController::class, 'remoteAccess'])->name('remote-access');
        Route::post('/{rvmId}/status', [AdminRvmController::class, 'updateRvmStatus'])->name('update-status');
        Route::put('/{rvmId}/settings', [AdminRvmController::class, 'updateRvmSettings'])->name('update-settings');
    });
});

// Remote RVM UI Route (Public access with token validation)
Route::get('/admin/rvm/{rvm}/remote/{token}', [AdminRvmController::class, 'remoteRvmUI'])->name('admin.rvm.remote');


// Gemini Vision Dashboard Routes
Route::prefix('gemini/dashboard')->group(function () {
    Route::get('/', [GeminiDashboardController::class, 'index'])->name('gemini.dashboard');
    Route::post('/analyze', [GeminiDashboardController::class, 'analyzeImage'])->name('gemini.analyze');
    Route::post('/test-sample', [GeminiDashboardController::class, 'testSampleImage'])->name('gemini.test-sample');
    Route::post('/compare-models', [GeminiDashboardController::class, 'compareModels'])->name('gemini.compare-models');
    Route::get('/status', [GeminiDashboardController::class, 'getStatus'])->name('gemini.status');
    Route::post('/clear-results', [GeminiDashboardController::class, 'clearResults'])->name('gemini.clear-results');
    Route::get('/result/{id}', [GeminiDashboardController::class, 'getResult'])->name('gemini.get-result');
});

// Admin Login route (different from Laravel Breeze login)
Route::get('/admin/login', function () {
    return view('auth.login');
})->name('admin.login');

// Simple admin login for testing
Route::post('/admin/login', function (Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended('/admin/dashboard');
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
})->name('admin.login.post')->withoutMiddleware(['csrf']);

// Alternative login route without CSRF for testing
Route::post('/admin/login-test', function (Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'redirect' => '/admin/dashboard'
        ]);
    }

    return response()->json([
        'success' => false,
        'message' => 'The provided credentials do not match our records.'
    ], 401);
})->name('admin.login.test')->withoutMiddleware(['csrf']);

// Simple test route to check if CSRF is the issue
Route::get('/admin/test-csrf', function () {
    return response()->json([
        'success' => true,
        'message' => 'CSRF test successful',
        'csrf_token' => csrf_token()
    ]);
})->name('admin.test.csrf');

// Test login route to verify authentication works
Route::get('/admin/test-login', function () {
    if (Auth::attempt(['email' => 'admin@myrvm.com', 'password' => 'admin123'])) {
        return response()->json([
            'success' => true,
            'message' => 'Login test successful',
            'user' => Auth::user()
        ]);
    } else {
        return response()->json([
            'success' => false,
            'message' => 'Login test failed'
        ]);
    }
})->name('admin.test.login');

// Debug route to check what's happening with form submission
Route::post('/admin/debug-login', function (Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Log::info('Debug login attempt', [
        'email' => $request->email,
        'password_length' => strlen($request->password ?? ''),
        'has_csrf_token' => $request->has('_token'),
        'all_request_data' => $request->all()
    ]);
    
    $credentials = [
        'email' => $request->email,
        'password' => $request->password
    ];
    
    if (Auth::attempt($credentials)) {
        \Illuminate\Support\Facades\Log::info('Debug login SUCCESS');
        return redirect()->intended('/admin/dashboard');
    } else {
        \Illuminate\Support\Facades\Log::info('Debug login FAILED');
        return back()->withErrors([
            'email' => 'Debug: The provided credentials do not match our records.',
        ])->onlyInput('email');
    }
})->name('admin.debug.login');

// Create admin user for testing
Route::get('/admin/create-admin', function () {
    try {
        // Create Admin role if it doesn't exist
        $adminRole = \App\Models\Role::firstOrCreate(
            ['name' => 'Admin'],
            ['slug' => 'admin']
        );

        // Create admin user
        $adminUser = \App\Models\User::firstOrCreate(
            ['email' => 'admin@myrvm.com'],
            [
                'name' => 'Admin User',
                'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
                'role_id' => $adminRole->id,
                'email_verified_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Admin user created successfully!',
            'data' => [
                'email' => 'admin@myrvm.com',
                'password' => 'admin123',
                'user_id' => $adminUser->id
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to create admin user: ' . $e->getMessage()
        ], 500);
    }
})->name('admin.create-admin');

// Admin RVM Dashboard Route (Protected with authentication) - REDIRECT TO NEW DASHBOARD
Route::get('/admin/rvm-dashboard', function () {
    return redirect()->route('admin.dashboard.index');
})->name('admin.rvm.dashboard');

// Admin User Management Routes
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/profile', [ProfileController::class, 'adminProfile'])->name('profile');
    Route::get('/notifications', [ProfileController::class, 'notifications'])->name('notifications');
    Route::get('/notifications/refresh', [ProfileController::class, 'getNotificationsForRefresh'])->name('notifications.refresh');
    Route::post('/notifications/{notificationId}/read', [ProfileController::class, 'markNotificationAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [ProfileController::class, 'markAllNotificationsAsRead'])->name('notifications.read-all');
    Route::get('/connections', [ProfileController::class, 'connections'])->name('connections');
    
    // System Notification Management Routes
    Route::prefix('system-notifications')->name('system-notifications.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\SystemNotificationController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\SystemNotificationController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\SystemNotificationController::class, 'store'])->name('store');
        Route::get('/{notification}', [App\Http\Controllers\Admin\SystemNotificationController::class, 'show'])->name('show');
        Route::delete('/{notification}', [App\Http\Controllers\Admin\SystemNotificationController::class, 'destroy'])->name('destroy');
        Route::get('/statistics/overview', [App\Http\Controllers\Admin\SystemNotificationController::class, 'statistics'])->name('statistics');
    });
});

// New Dashboard Routes (Template Inheritance) - Protected with authentication
Route::middleware(['auth', 'verified'])->prefix('admin/dashboard')->name('admin.dashboard.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('index');
    Route::get('/status-config', [AdminDashboardController::class, 'getStatusConfig'])->name('status-config');
    Route::get('/live-camera', function () {
        return view('admin.dashboard.live-camera');
    })->name('live-camera');
    Route::get('/image-upload', function () {
        return view('admin.dashboard.image-upload');
    })->name('image-upload');
    Route::get('/engine-config', function () {
        return view('admin.dashboard.engine-config');
    })->name('engine-config');
    Route::get('/remote-control', function () {
        return view('admin.dashboard.remote-control');
    })->name('remote-control');
});

// RVM Management Routes - Protected with authentication
Route::middleware(['auth', 'verified'])->prefix('admin/rvm')->name('admin.rvm.')->group(function () {
    Route::get('/', [RvmController::class, 'index'])->name('index');
    Route::get('/maintenance', [RvmController::class, 'maintenance'])->name('maintenance');
    Route::post('/', [RvmController::class, 'store'])->name('store');
    Route::post('/test-connection', [RvmController::class, 'testConnection'])->name('test-connection');
    Route::post('/{id}/test-service-ports', [RvmController::class, 'testServicePorts'])->name('test-service-ports');
    Route::post('/ping/{id}', [RvmController::class, 'ping'])->name('ping');
    Route::post('/sync-timezone/{id}', [RvmController::class, 'syncTimezone'])->name('sync-timezone');
    Route::post('/set-global-timezone', [RvmController::class, 'setGlobalTimezone'])->name('set-global-timezone');
    
    // Remote Access Routes
    Route::post('/{id}/remote-access/start', [RemoteAccessController::class, 'start'])->name('remote-access.start');
    Route::post('/{id}/remote-access/stop', [RemoteAccessController::class, 'stop'])->name('remote-access.stop');
    Route::get('/{id}/remote-access/status', [RemoteAccessController::class, 'status'])->name('remote-access.status');
    Route::get('/{id}/remote-access/history', [RemoteAccessController::class, 'history'])->name('remote-access.history');
    Route::post('/{id}/remote-access/check-port', [RemoteAccessController::class, 'checkPort'])->name('remote-access.check-port');
    
    // Configuration Management Routes
    Route::get('/{id}/config', [ConfigurationController::class, 'index'])->name('config.index');
    Route::get('/{id}/config/{key}', [ConfigurationController::class, 'get'])->name('config.get');
    Route::put('/{id}/config/{key}', [ConfigurationController::class, 'update'])->name('config.update');
    Route::delete('/{id}/config/{key}', [ConfigurationController::class, 'delete'])->name('config.delete');
    Route::put('/{id}/config/bulk', [ConfigurationController::class, 'bulkUpdate'])->name('config.bulk-update');
    
    // System Monitoring Routes
    Route::get('/{id}/metrics', [SystemMonitoringController::class, 'index'])->name('metrics.index');
    Route::get('/{id}/metrics/latest', [SystemMonitoringController::class, 'latest'])->name('metrics.latest');
    Route::post('/{id}/metrics', [SystemMonitoringController::class, 'store'])->name('metrics.store');
    Route::get('/{id}/metrics/alerts', [SystemMonitoringController::class, 'alerts'])->name('metrics.alerts');
    Route::get('/{id}/metrics/statistics', [SystemMonitoringController::class, 'statistics'])->name('metrics.statistics');
    
    // Enhanced Metrics Routes
    Route::get('/{id}/enhanced-metrics', [EnhancedMetricsController::class, 'getComprehensiveMetrics'])->name('enhanced-metrics');
    Route::get('/{id}/metrics-history', [EnhancedMetricsController::class, 'getMetricsHistory'])->name('metrics-history');
    Route::post('/{id}/store-metrics', [EnhancedMetricsController::class, 'storeMetrics'])->name('store-metrics');
    
    // Remote Commands Routes
    Route::get('/{id}/remote-commands', [RemoteCommandsController::class, 'index'])->name('remote-commands');
    Route::get('/{id}/available-commands', [RemoteCommandsController::class, 'getAvailableCommands'])->name('available-commands');
    Route::post('/{id}/execute-command', [RemoteCommandsController::class, 'executeCommand'])->name('execute-command');
    Route::get('/{id}/command/{commandId}/status', [RemoteCommandsController::class, 'getCommandStatus'])->name('command-status');
    Route::put('/{id}/command/{commandId}/status', [RemoteCommandsController::class, 'updateCommandStatus'])->name('update-command-status');
    
    // OTA Management Routes
    Route::get('/{id}/ota-info', [OTAManagementController::class, 'index'])->name('ota-info');
    Route::get('/{id}/check-updates', [OTAManagementController::class, 'checkForUpdates'])->name('check-updates');
    
    // Maintenance Mode Routes
    Route::get('/{id}/maintenance-mode', [RvmController::class, 'maintenanceMode'])->name('maintenance-mode');
    Route::post('/{id}/toggle-maintenance', [RvmController::class, 'toggleMaintenanceMode'])->name('toggle-maintenance');
    Route::get('/{id}/test-connection', [RvmController::class, 'testRvmConnection'])->name('test-rvm-connection');
    
    // Enhanced Remote Commands Routes
    Route::post('/{id}/enhanced-execute-command', [EnhancedRemoteCommandsController::class, 'executeCommand'])->name('enhanced-execute-command');
    Route::get('/{id}/enhanced-command/{commandId}/status', [EnhancedRemoteCommandsController::class, 'getCommandStatus'])->name('enhanced-command-status');
    Route::get('/{id}/recent-commands', [EnhancedRemoteCommandsController::class, 'getRecentCommands'])->name('recent-commands');
    
    // Backup Operations Routes
    Route::get('/{id}/backups', [BackupController::class, 'index'])->name('backups.index');
    Route::get('/{id}/backups/latest', [BackupController::class, 'latest'])->name('backups.latest');
    Route::post('/{id}/backups', [BackupController::class, 'store'])->name('backups.store');
    Route::put('/{id}/backups/{backupId}', [BackupController::class, 'update'])->name('backups.update');
    Route::get('/{id}/backups/statistics', [BackupController::class, 'statistics'])->name('backups.statistics');
    Route::get('/{id}/backups/alerts', [BackupController::class, 'alerts'])->name('backups.alerts');
    
    // Basic CRUD Routes (must be last to avoid conflicts with specific routes above)
    Route::get('/{id}', [RvmController::class, 'show'])->name('show');
    Route::put('/{id}', [RvmController::class, 'update'])->name('update');
    Route::delete('/{id}', [RvmController::class, 'destroy'])->name('destroy');
});

// Debug route for testing
Route::post('/admin/rvm/debug', function(Illuminate\Http\Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'Debug route working',
        'data' => $request->all()
    ]);
});

// Dashboard Data Route (Public access for dashboard display)
Route::get('/api/admin/rvm-dashboard/data', [AdminRvmController::class, 'getRvmMonitoring'])->name('api.admin.rvm.dashboard.data');

// Test route to check if middleware is the issue
Route::get('/test-remote', function() {
    return response()->json(['message' => 'Test route works']);
});

// CV Playground V2 Routes
Route::prefix('cv-playground')->name('cv-playground.')->group(function () {
    Route::get('/', [CvPlaygroundController::class, 'index'])->name('index');
    Route::post('/run-test', [CvPlaygroundController::class, 'runTest'])->name('run-test');
    Route::get('/result/{filepath}', [CvPlaygroundController::class, 'serveResult'])->name('serve-result');
});

// Edge Vision Dashboard Routes (Protected with authentication)
Route::middleware(['auth', 'verified'])->prefix('admin/edge-vision')->name('admin.edge-vision.')->group(function () {
    Route::get('/', [EdgeVisionController::class, 'index'])->name('index');
    Route::get('/statistics', [EdgeVisionController::class, 'getStatistics'])->name('statistics');
    Route::get('/rvm-status', [EdgeVisionController::class, 'getRvmStatus'])->name('rvm-status');
    Route::post('/trigger-processing', [EdgeVisionController::class, 'triggerProcessing'])->name('trigger-processing');
    Route::get('/processing-history', [EdgeVisionController::class, 'getProcessingHistory'])->name('processing-history');
    Route::post('/upload-results', [EdgeVisionController::class, 'uploadResults'])->name('upload-results');
});

// Test route untuk Edge Vision
Route::get('/admin/edge-vision-test', function () {
    return 'Edge Vision Test Route Works!';
})->name('admin.edge-vision.test');

// Test route untuk Processing Engines
Route::get('/admin/processing-engines-test', function () {
    $engines = App\Models\ProcessingEngine::all();
    return response()->json([
        'success' => true,
        'data' => $engines
    ]);
})->name('admin.processing-engines.test');

// Processing Engine Management Routes
Route::middleware(['auth', 'verified'])->prefix('admin/processing-engines')->name('admin.processing-engines.')->group(function () {
    Route::get('/', [ProcessingEngineController::class, 'index'])->name('index');
    Route::get('/all', [ProcessingEngineController::class, 'getEngines'])->name('all');
    Route::get('/nvidia-cuda', [ProcessingEngineController::class, 'getNvidiaCudaEngines'])->name('nvidia-cuda');
    Route::get('/jetson-edge', [ProcessingEngineController::class, 'getJetsonEdgeEngines'])->name('jetson-edge');
    Route::get('/rvm-engines', [ProcessingEngineController::class, 'getRvmEngines'])->name('rvm-engines');
    Route::post('/', [ProcessingEngineController::class, 'store'])->name('store');
    Route::put('/{engine}', [ProcessingEngineController::class, 'update'])->name('update');
    Route::delete('/{engine}', [ProcessingEngineController::class, 'destroy'])->name('destroy');
    Route::post('/{engine}/toggle-activation', [ProcessingEngineController::class, 'toggleActivation'])->name('toggle-activation');
    Route::post('/{engine}/ping', [ProcessingEngineController::class, 'ping'])->name('ping');
    Route::post('/ping-all', [ProcessingEngineController::class, 'pingAll'])->name('ping-all');
    Route::post('/assign-rvm', [ProcessingEngineController::class, 'assignToRvm'])->name('assign-rvm');
    Route::post('/remove-rvm', [ProcessingEngineController::class, 'removeFromRvm'])->name('remove-rvm');
    });
    Route::post('/admin/rvm/pulse-check/{rvm?}', [RvmController::class, 'manualPulseCheck'])->name('admin.rvm.pulse-check');
    Route::post('/admin/rvm/health-check/{rvm?}', [RvmController::class, 'manualHealthCheck'])->name('admin.rvm.health-check');

    // Rute untuk Health Controller
    Route::get('/health', [HealthController::class, 'check']);
require __DIR__ . '/auth.php';
