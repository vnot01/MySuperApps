<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Tidak perlu Inertia::render
        return view('admin.dashboard'); // Merender file di resources/views/admin/dashboard.blade.php
    }
}
