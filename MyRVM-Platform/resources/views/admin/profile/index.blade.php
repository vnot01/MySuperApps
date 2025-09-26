@extends('components.admin-layout')

@section('title', 'My Profile - MyRVM Platform')
@section('description', 'Manage your profile settings and account information')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/animations.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/responsive.css') }}">
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ url('/admin/dashboard') }}">
            <i class="fas fa-home me-2"></i>Dashboard
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        <i class="fas fa-user me-2"></i>My Profile
    </li>
@endsection

@section('content')
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-1 fw-bold text-primary">
                        <i class="fas fa-user me-2"></i>My Profile
                    </h1>
                    <p class="text-muted mb-0">Manage your account settings and personal information</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Content -->
    <div class="row">
        <!-- Profile Info Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="avatar avatar-xl mx-auto mb-3">
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded-circle w-100 h-100" style="object-fit: cover;">
                    </div>
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <p class="text-muted mb-3">{{ $user->role ? $user->role->name : 'Admin' }}</p>
                    <div class="d-flex justify-content-center gap-2">
                        <span class="badge bg-success">Active</span>
                        <span class="badge bg-primary">Verified</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Settings -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="mb-0">Profile Information</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PATCH')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone_number" name="phone_number" value="{{ $user->phone_number ?? '' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">Role</label>
                                <input type="text" class="form-control" id="role" value="{{ $user->role ? $user->role->name : 'Admin' }}" readonly>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea class="form-control" id="bio" name="bio" rows="3" placeholder="Tell us about yourself...">{{ $user->bio ?? '' }}</textarea>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="mb-0">Security Settings</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Password</h6>
                            <p class="text-muted small">Last changed 30 days ago</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <button class="btn btn-outline-primary btn-sm">Change Password</button>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Two-Factor Authentication</h6>
                            <p class="text-muted small">Add an extra layer of security</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <button class="btn btn-outline-success btn-sm">Enable 2FA</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-js')
<script>
// Profile page specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Profile page loaded successfully');
});
</script>
@endsection