<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ReverseVendingMachine;
use App\Helpers\TimezoneHelper;

class RvmDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // RVM data with capacity and special status
        $rvmData = [
            ['id' => 1, 'name' => 'RVM-001', 'location' => 'Mall Central', 'capacity' => 85, 'special_status' => null],
            ['id' => 2, 'name' => 'RVM-002', 'location' => 'Shopping Plaza', 'capacity' => 60, 'special_status' => 'maintenance'],
            ['id' => 3, 'name' => 'RVM-003', 'location' => 'City Center', 'capacity' => 30, 'special_status' => 'inactive'],
            ['id' => 4, 'name' => 'RVM-004', 'location' => 'Airport Terminal', 'capacity' => 92, 'special_status' => null],
            ['id' => 5, 'name' => 'RVM-005', 'location' => 'University Campus', 'capacity' => 100, 'special_status' => null],
            ['id' => 6, 'name' => 'RVM-006', 'location' => 'Hospital Lobby', 'capacity' => 45, 'special_status' => 'error'],
            ['id' => 7, 'name' => 'RVM-007', 'location' => 'Office Complex', 'capacity' => 78, 'special_status' => null],
            ['id' => 8, 'name' => 'RVM-008', 'location' => 'Train Station', 'capacity' => 65, 'special_status' => null],
            ['id' => 9, 'name' => 'RVM-009', 'location' => 'Shopping Mall', 'capacity' => 95, 'special_status' => null],
            ['id' => 10, 'name' => 'RVM-010', 'location' => 'Bus Station', 'capacity' => 0, 'special_status' => null],
            ['id' => 11, 'name' => 'RVM-011', 'location' => 'Library', 'capacity' => 15, 'special_status' => null],
            ['id' => 12, 'name' => 'RVM-012', 'location' => 'Park', 'capacity' => 100, 'special_status' => null],
            ['id' => 13, 'name' => 'RVM-013', 'location' => 'Market', 'capacity' => 55, 'special_status' => null],
            ['id' => 14, 'name' => 'RVM-014', 'location' => 'School', 'capacity' => 25, 'special_status' => null],
            ['id' => 15, 'name' => 'RVM-015', 'location' => 'Hotel', 'capacity' => 88, 'special_status' => null],
            ['id' => 16, 'name' => 'RVM-016', 'location' => 'Restaurant', 'capacity' => 72, 'special_status' => null],
            ['id' => 17, 'name' => 'RVM-017', 'location' => 'Gym', 'capacity' => 40, 'special_status' => null],
            ['id' => 18, 'name' => 'RVM-018', 'location' => 'Cinema', 'capacity' => 90, 'special_status' => null],
            ['id' => 19, 'name' => 'RVM-019', 'location' => 'Bank', 'capacity' => 35, 'special_status' => null],
            ['id' => 20, 'name' => 'RVM-020', 'location' => 'Pharmacy', 'capacity' => 68, 'special_status' => null],
        ];

        foreach ($rvmData as $data) {
            $rvm = ReverseVendingMachine::find($data['id']);
            if ($rvm) {
                $rvm->update([
                    'capacity' => $data['capacity'],
                    'special_status' => $data['special_status'],
                    'last_capacity_update' => now(),
                    'location_description' => $data['location']
                ]);
                
                echo "Updated {$rvm->name} - Capacity: {$data['capacity']}%, Special Status: " . ($data['special_status'] ?? 'None') . ", Calculated Status: {$rvm->calculated_status}\n";
            }
        }

        echo "\nRVM data seeding completed!\n";
        echo "Total RVMs updated: " . count($rvmData) . "\n";
        
        // Show statistics
        $totalRvms = ReverseVendingMachine::count();
        $activeRvms = ReverseVendingMachine::all()->filter(fn($r) => $r->calculated_status === 'active')->count();
        $fullRvms = ReverseVendingMachine::all()->filter(fn($r) => $r->calculated_status === 'full')->count();
        $maintenanceRvms = ReverseVendingMachine::all()->filter(fn($r) => $r->calculated_status === 'maintenance')->count();
        $inactiveRvms = ReverseVendingMachine::all()->filter(fn($r) => $r->calculated_status === 'inactive')->count();
        $errorRvms = ReverseVendingMachine::all()->filter(fn($r) => $r->calculated_status === 'error')->count();
        
        echo "\nStatus Statistics:\n";
        echo "Total RVMs: {$totalRvms}\n";
        echo "Active: {$activeRvms}\n";
        echo "Full: {$fullRvms}\n";
        echo "Maintenance: {$maintenanceRvms}\n";
        echo "Inactive: {$inactiveRvms}\n";
        echo "Error: {$errorRvms}\n";
    }
}
