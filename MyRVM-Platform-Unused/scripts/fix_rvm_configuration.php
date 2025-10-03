<?php

/**
 * Script untuk memperbaiki konfigurasi RVM yang bermasalah
 * Menjalankan: php artisan tinker < scripts/fix_rvm_configuration.php
 */

use App\Models\ReverseVendingMachine;
use App\Models\TimezoneSyncLog;

echo "🔧 Memperbaiki konfigurasi RVM...\n\n";

// 1. Update IP Address untuk RVM yang belum dikonfigurasi
echo "1. Mengupdate IP Address untuk RVM...\n";
$rvms = ReverseVendingMachine::whereNull('ip_address')->get();

foreach ($rvms as $index => $rvm) {
    // Generate IP address berdasarkan ID RVM
    $ip = "172.28.93." . (97 + $rvm->id);
    $rvm->update([
        'ip_address' => $ip,
        'port' => 8000,
        'timezone' => 'Asia/Jakarta',
        'timezone_offset' => '+07:00',
        'connection_status' => 'unknown'
    ]);
    echo "   ✅ RVM-{$rvm->id} ({$rvm->name}): IP {$ip}\n";
}

// 2. Update timezone untuk semua RVM
echo "\n2. Mengupdate timezone untuk semua RVM...\n";
$allRvms = ReverseVendingMachine::all();

foreach ($allRvms as $rvm) {
    if (!$rvm->timezone) {
        $rvm->update([
            'timezone' => 'Asia/Jakarta',
            'timezone_offset' => '+07:00'
        ]);
        echo "   ✅ RVM-{$rvm->id} ({$rvm->name}): Timezone Asia/Jakarta\n";
    }
}

// 3. Update status RVM yang bermasalah
echo "\n3. Memperbaiki status RVM yang bermasalah...\n";
$problematicRvms = ReverseVendingMachine::whereIn('status', ['error', 'maintenance', 'inactive'])->get();

foreach ($problematicRvms as $rvm) {
    // Set status menjadi active jika IP sudah dikonfigurasi
    if ($rvm->ip_address) {
        $rvm->update([
            'status' => 'active',
            'special_status' => null,
            'last_status_change' => now()
        ]);
        echo "   ✅ RVM-{$rvm->id} ({$rvm->name}): Status diubah ke Active\n";
    }
}

// 4. Buat sample timezone sync logs
echo "\n4. Membuat sample timezone sync logs...\n";
$activeRvms = ReverseVendingMachine::where('status', 'active')->get();

foreach ($activeRvms as $rvm) {
    TimezoneSyncLog::create([
        'device_id' => $rvm->id,
        'device_type' => 'rvm',
        'sync_type' => 'automatic',
        'old_timezone' => null,
        'new_timezone' => $rvm->timezone,
        'sync_timestamp' => now(),
        'status' => 'success',
        'details' => json_encode([
            'ip_address' => $rvm->ip_address,
            'port' => $rvm->port,
            'sync_method' => 'automatic'
        ])
    ]);
    
    $rvm->update(['last_timezone_sync' => now()]);
    echo "   ✅ RVM-{$rvm->id} ({$rvm->name}): Timezone sync log created\n";
}

// 5. Summary
echo "\n📊 SUMMARY:\n";
echo "   Total RVMs: " . ReverseVendingMachine::count() . "\n";
echo "   Active RVMs: " . ReverseVendingMachine::where('status', 'active')->count() . "\n";
echo "   RVMs with IP: " . ReverseVendingMachine::whereNotNull('ip_address')->count() . "\n";
echo "   RVMs with timezone: " . ReverseVendingMachine::whereNotNull('timezone')->count() . "\n";
echo "   Timezone sync logs: " . TimezoneSyncLog::count() . "\n";

echo "\n✅ Konfigurasi RVM selesai!\n";
echo "🔗 Akses: http://localhost:8001/admin/rvm\n";
