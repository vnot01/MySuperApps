<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes for reverse_vending_machines table
        Schema::table('reverse_vending_machines', function (Blueprint $table) {
            // Status index for filtering
            $table->index('status', 'idx_rvm_status');
            
            // Location index for searching
            $table->index('location_description', 'idx_rvm_location');
            
            // Created at index for date filtering
            $table->index('created_at', 'idx_rvm_created_at');
            
            // Last status change index for monitoring
            $table->index('last_status_change', 'idx_rvm_last_status_change');
            
            // Composite index for status and location
            $table->index(['status', 'location_description'], 'idx_rvm_status_location');
            
            // Composite index for status and created_at
            $table->index(['status', 'created_at'], 'idx_rvm_status_created');
        });

        // Add indexes for rvm_sessions table
        Schema::table('rvm_sessions', function (Blueprint $table) {
            // User ID index for user sessions
            $table->index('user_id', 'idx_sessions_user_id');
            
            // RVM ID index for RVM sessions
            $table->index('rvm_id', 'idx_sessions_rvm_id');
            
            // Status index for active sessions
            $table->index('status', 'idx_sessions_status');
            
            // Created at index for date filtering
            $table->index('created_at', 'idx_sessions_created_at');
            
            // Composite index for user and status
            $table->index(['user_id', 'status'], 'idx_sessions_user_status');
            
            // Composite index for RVM and status
            $table->index(['rvm_id', 'status'], 'idx_sessions_rvm_status');
            
            // Composite index for RVM and created_at (for daily stats)
            $table->index(['rvm_id', 'created_at'], 'idx_sessions_rvm_created');
        });

        // Add indexes for deposits table
        Schema::table('deposits', function (Blueprint $table) {
            // User ID index for user deposits
            $table->index('user_id', 'idx_deposits_user_id');
            
            // RVM ID index for RVM deposits
            $table->index('rvm_id', 'idx_deposits_rvm_id');
            
            // Status index for deposit status
            $table->index('status', 'idx_deposits_status');
            
            // Created at index for date filtering
            $table->index('created_at', 'idx_deposits_created_at');
            
            // Reward amount index for analytics
            $table->index('reward_amount', 'idx_deposits_reward_amount');
            
            // Composite index for user and status
            $table->index(['user_id', 'status'], 'idx_deposits_user_status');
            
            // Composite index for RVM and status
            $table->index(['rvm_id', 'status'], 'idx_deposits_rvm_status');
            
            // Composite index for RVM and created_at (for daily stats)
            $table->index(['rvm_id', 'created_at'], 'idx_deposits_rvm_created');
        });

        // Add indexes for users table
        Schema::table('users', function (Blueprint $table) {
            // Email index for authentication
            $table->index('email', 'idx_users_email');
            
            // Role ID index for role-based queries
            $table->index('role_id', 'idx_users_role_id');
            
            // Tenant ID index for tenant-based queries
            $table->index('tenant_id', 'idx_users_tenant_id');
            
            // Created at index for user analytics
            $table->index('created_at', 'idx_users_created_at');
            
            // Email verified at index for active users
            $table->index('email_verified_at', 'idx_users_email_verified');
            
            // Composite index for role and tenant
            $table->index(['role_id', 'tenant_id'], 'idx_users_role_tenant');
        });

        // Add indexes for transactions table (if exists)
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                // User ID index
                $table->index('user_id', 'idx_transactions_user_id');
                
                // User Balance ID index
                $table->index('user_balance_id', 'idx_transactions_user_balance_id');
                
                // Type index
                $table->index('type', 'idx_transactions_type');
                
                // Created at index
                $table->index('created_at', 'idx_transactions_created_at');
                
                // Sourceable indexes
                $table->index('sourceable_id', 'idx_transactions_sourceable_id');
                $table->index('sourceable_type', 'idx_transactions_sourceable_type');
                
                // Composite indexes
                $table->index(['user_id', 'type'], 'idx_transactions_user_type');
                $table->index(['type', 'created_at'], 'idx_transactions_type_created');
            });
        }

        // Add indexes for vouchers table (if exists)
        if (Schema::hasTable('vouchers')) {
            Schema::table('vouchers', function (Blueprint $table) {
                // Tenant ID index
                $table->index('tenant_id', 'idx_vouchers_tenant_id');
                
                // Active status index
                $table->index('is_active', 'idx_vouchers_is_active');
                
                // Created at index
                $table->index('created_at', 'idx_vouchers_created_at');
                
                // Valid until index
                $table->index('valid_until', 'idx_vouchers_valid_until');
                
                // Valid from index
                $table->index('valid_from', 'idx_vouchers_valid_from');
                
                // Composite index for tenant and active status
                $table->index(['tenant_id', 'is_active'], 'idx_vouchers_tenant_active');
            });
        }

        // Add indexes for voucher_redemptions table (if exists)
        if (Schema::hasTable('voucher_redemptions')) {
            Schema::table('voucher_redemptions', function (Blueprint $table) {
                // User ID index
                $table->index('user_id', 'idx_voucher_redemptions_user_id');
                
                // Voucher ID index
                $table->index('voucher_id', 'idx_voucher_redemptions_voucher_id');
                
                // Created at index
                $table->index('created_at', 'idx_voucher_redemptions_created_at');
            });
        }

        // Enable pg_stat_statements extension for query monitoring
        try {
            DB::statement('CREATE EXTENSION IF NOT EXISTS pg_stat_statements');
        } catch (\Exception $e) {
            // Extension might already exist or require superuser privileges
            \Log::info('pg_stat_statements extension setup skipped: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes for reverse_vending_machines table
        Schema::table('reverse_vending_machines', function (Blueprint $table) {
            $table->dropIndex('idx_rvm_status');
            $table->dropIndex('idx_rvm_location');
            $table->dropIndex('idx_rvm_created_at');
            $table->dropIndex('idx_rvm_last_status_change');
            $table->dropIndex('idx_rvm_status_location');
            $table->dropIndex('idx_rvm_status_created');
        });

        // Drop indexes for rvm_sessions table
        Schema::table('rvm_sessions', function (Blueprint $table) {
            $table->dropIndex('idx_sessions_user_id');
            $table->dropIndex('idx_sessions_rvm_id');
            $table->dropIndex('idx_sessions_status');
            $table->dropIndex('idx_sessions_created_at');
            $table->dropIndex('idx_sessions_user_status');
            $table->dropIndex('idx_sessions_rvm_status');
            $table->dropIndex('idx_sessions_rvm_created');
        });

        // Drop indexes for deposits table
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropIndex('idx_deposits_user_id');
            $table->dropIndex('idx_deposits_rvm_id');
            $table->dropIndex('idx_deposits_status');
            $table->dropIndex('idx_deposits_created_at');
            $table->dropIndex('idx_deposits_reward_amount');
            $table->dropIndex('idx_deposits_user_status');
            $table->dropIndex('idx_deposits_rvm_status');
            $table->dropIndex('idx_deposits_rvm_created');
        });

        // Drop indexes for users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_email');
            $table->dropIndex('idx_users_role_id');
            $table->dropIndex('idx_users_tenant_id');
            $table->dropIndex('idx_users_created_at');
            $table->dropIndex('idx_users_email_verified');
            $table->dropIndex('idx_users_role_tenant');
        });

        // Drop indexes for transactions table (if exists)
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropIndex('idx_transactions_user_id');
                $table->dropIndex('idx_transactions_user_balance_id');
                $table->dropIndex('idx_transactions_type');
                $table->dropIndex('idx_transactions_created_at');
                $table->dropIndex('idx_transactions_sourceable_id');
                $table->dropIndex('idx_transactions_sourceable_type');
                $table->dropIndex('idx_transactions_user_type');
                $table->dropIndex('idx_transactions_type_created');
            });
        }

        // Drop indexes for vouchers table (if exists)
        if (Schema::hasTable('vouchers')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->dropIndex('idx_vouchers_tenant_id');
                $table->dropIndex('idx_vouchers_is_active');
                $table->dropIndex('idx_vouchers_created_at');
                $table->dropIndex('idx_vouchers_valid_until');
                $table->dropIndex('idx_vouchers_valid_from');
                $table->dropIndex('idx_vouchers_tenant_active');
            });
        }

        // Drop indexes for voucher_redemptions table (if exists)
        if (Schema::hasTable('voucher_redemptions')) {
            Schema::table('voucher_redemptions', function (Blueprint $table) {
                $table->dropIndex('idx_voucher_redemptions_user_id');
                $table->dropIndex('idx_voucher_redemptions_voucher_id');
                $table->dropIndex('idx_voucher_redemptions_created_at');
            });
        }
    }
};
