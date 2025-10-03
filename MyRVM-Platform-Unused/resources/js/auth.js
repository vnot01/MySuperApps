/**
 * Authentication Helper for MyRVM Platform
 * Handles API authentication, token management, and user session
 */

class AuthManager {
    constructor() {
        this.token = localStorage.getItem('auth_token');
        this.user = JSON.parse(localStorage.getItem('auth_user') || 'null');
        this.baseUrl = '/api/v2';
    }

    /**
     * Login user with email and password
     */
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
                
                // Store in localStorage
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

    /**
     * Logout user and clear session
     */
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
            // Clear local storage
            this.token = null;
            this.user = null;
            localStorage.removeItem('auth_token');
            localStorage.removeItem('auth_user');
        }
    }

    /**
     * Get authenticated user info
     */
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
                // Token might be invalid, clear it
                this.logout();
                return { success: false, message: data.message };
            }
        } catch (error) {
            console.error('Get user info error:', error);
            return { success: false, message: 'Network error occurred' };
        }
    }

    /**
     * Make authenticated API request
     */
    async apiRequest(url, options = {}) {
        const defaultOptions = {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        };

        // Add authorization header if token exists
        if (this.token) {
            defaultOptions.headers['Authorization'] = `Bearer ${this.token}`;
        }

        // Merge options
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
            
            // Check if token is expired
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

    /**
     * Check if user is authenticated
     */
    isAuthenticated() {
        return !!this.token && !!this.user;
    }

    /**
     * Check if user has specific role
     */
    hasRole(role) {
        if (!this.user) return false;
        return this.user.role === role;
    }

    /**
     * Check if user has any of the specified roles
     */
    hasAnyRole(roles) {
        if (!this.user) return false;
        return roles.includes(this.user.role);
    }

    /**
     * Get current user
     */
    getCurrentUser() {
        return this.user;
    }

    /**
     * Get current token
     */
    getToken() {
        return this.token;
    }
}

// Create global instance
window.authManager = new AuthManager();

// Auto-refresh user info on page load
document.addEventListener('DOMContentLoaded', async () => {
    if (window.authManager.isAuthenticated()) {
        await window.authManager.getMe();
    }
});
