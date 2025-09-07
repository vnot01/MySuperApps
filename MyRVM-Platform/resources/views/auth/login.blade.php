<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MyRVM Platform - Admin Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            color: #1f2937;
            font-size: 1.875rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: #6b7280;
            font-size: 0.875rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            color: #374151;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-group input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .login-button {
            width: 100%;
            background: #3b82f6;
            color: white;
            padding: 0.75rem;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.15s ease-in-out;
        }

        .login-button:hover {
            background: #2563eb;
        }

        .login-button:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }

        .error-message {
            background: #fef2f2;
            color: #dc2626;
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            display: none;
        }

        .success-message {
            background: #f0fdf4;
            color: #16a34a;
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            display: none;
        }

        .loading {
            display: none;
            text-align: center;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .loading.show {
            display: block;
        }

        .demo-credentials {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 1rem;
            margin-top: 1.5rem;
        }

        .demo-credentials h3 {
            color: #374151;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .demo-credentials p {
            color: #6b7280;
            font-size: 0.75rem;
            margin-bottom: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>MyRVM Platform</h1>
            <p>Admin Dashboard Login</p>
        </div>

        <div class="error-message" id="errorMessage"></div>
        <div class="success-message" id="successMessage"></div>

        <form id="loginForm">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
        </div>

            <button type="submit" class="login-button" id="loginButton">
                Sign In
            </button>
        </form>

        <div class="loading" id="loading">
            Signing in...
        </div>

        <div class="demo-credentials">
            <h3>Demo Credentials</h3>
            <p><strong>Super Admin:</strong> admin@myrvm.com / password</p>
            <p><strong>Admin:</strong> admin2@myrvm.com / password</p>
            <p><strong>Operator:</strong> operator@myrvm.com / password</p>
        </div>
    </div>

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
                        
                        // Force token to be available immediately
                        console.log('Token stored:', this.token);
                        console.log('User stored:', this.user);
                        
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

            isAuthenticated() {
                return !!this.token && !!this.user;
            }

            getToken() {
                return this.token;
            }

            getCurrentUser() {
                return this.user;
            }
        }

        // Create global instance
        window.authManager = new AuthManager();
        
        // Global error handler untuk catch uncaught errors
        window.addEventListener('error', function(event) {
            console.error('Global error caught:', event.error);
            // Jangan biarkan error mengganggu redirect
            if (event.error && event.error.message && event.error.message.includes('Could not establish connection')) {
                console.log('Browser extension error detected, ignoring...');
                return;
            }
        });
        
        // Handle unhandled promise rejections
        window.addEventListener('unhandledrejection', function(event) {
            console.error('Unhandled promise rejection:', event.reason);
            // Jangan biarkan promise rejection mengganggu redirect
            if (event.reason && event.reason.message && event.reason.message.includes('Could not establish connection')) {
                console.log('Browser extension promise rejection detected, ignoring...');
                event.preventDefault();
                return;
            }
        });
    </script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const loginButton = document.getElementById('loginButton');
            const loading = document.getElementById('loading');
            const errorMessage = document.getElementById('errorMessage');
            const successMessage = document.getElementById('successMessage');

            // Clear previous messages completely
            errorMessage.style.display = 'none';
            errorMessage.textContent = '';
            successMessage.style.display = 'none';
            successMessage.textContent = '';
            successMessage.innerHTML = '';

            // Show loading
            loginButton.disabled = true;
            loading.classList.add('show');

            try {
                const result = await window.authManager.login(email, password);
                
                if (result.success) {
                    successMessage.innerHTML = 'Login successful! Redirecting...<br><small>If redirect fails, <a href="/admin/rvm-dashboard" class="text-blue-600 underline">click here</a></small>';
                    successMessage.style.display = 'block';
                    
                    // Redirect to admin dashboard immediately
                    console.log('Login successful, redirecting to dashboard...');
                    console.log('Token:', window.authManager.getToken());
                    
                    // Force redirect with error handling
                    try {
                        // Method 1: Direct redirect
                        console.log('Attempting direct redirect...');
                        window.location.href = '/admin/rvm-dashboard';
                    } catch (error) {
                        console.error('Direct redirect failed:', error);
                        try {
                            // Method 2: Replace current location
                            console.log('Attempting replace redirect...');
                            window.location.replace('/admin/rvm-dashboard');
                        } catch (error2) {
                            console.error('Replace redirect failed:', error2);
                            // Method 3: Force redirect with assignment
                            console.log('Attempting assignment redirect...');
                            window.location = '/admin/rvm-dashboard';
                        }
                    }
                    
                    // Force redirect after a short delay to ensure token is stored
                    setTimeout(() => {
                        if (window.location.pathname === '/admin/login') {
                            console.log('Force redirect after delay...');
                            window.location.href = '/admin/rvm-dashboard';
                        }
                    }, 100);
                    
                    // Fallback: Show manual redirect button after 2 seconds
                    setTimeout(() => {
                        if (window.location.pathname === '/admin/login') {
                            console.log('Redirect failed, showing manual link...');
                            successMessage.innerHTML = 'Login successful! <a href="/admin/rvm-dashboard" class="text-blue-600 underline font-semibold">Click here to continue to dashboard</a>';
                        }
                    }, 2000);
                } else {
                    // Clear success message first
                    successMessage.style.display = 'none';
                    successMessage.textContent = '';
                    successMessage.innerHTML = '';
                    
                    errorMessage.textContent = result.message || 'Login failed';
                    errorMessage.style.display = 'block';
                }
            } catch (error) {
                console.error('Login error:', error);
                
                // Clear success message first
                successMessage.style.display = 'none';
                successMessage.textContent = '';
                successMessage.innerHTML = '';
                
                errorMessage.textContent = 'An error occurred during login';
                errorMessage.style.display = 'block';
            } finally {
                loginButton.disabled = false;
                loading.classList.remove('show');
            }
        });

        // Check if already logged in
        if (window.authManager.isAuthenticated()) {
            window.location.href = '/admin/rvm-dashboard';
        }
    </script>
</body>
</html>