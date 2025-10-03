-- MyRVM v2.1 Database Schema
-- Generated: 2025-09-07
-- Version: 2.1

-- =============================================
-- TABLES
-- =============================================

-- Users table
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Roles table
CREATE TABLE roles (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    display_name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Permissions table
CREATE TABLE permissions (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    display_name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Role-Permission pivot table
CREATE TABLE permission_role (
    id BIGSERIAL PRIMARY KEY,
    permission_id BIGINT NOT NULL,
    role_id BIGINT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- User Balances table
CREATE TABLE user_balances (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    balance DECIMAL(15,2) DEFAULT 0.00,
    currency VARCHAR(3) DEFAULT 'IDR',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE(user_id)
);

-- Transactions table
CREATE TABLE transactions (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    user_balance_id BIGINT NOT NULL,
    type VARCHAR(10) NOT NULL CHECK (type IN ('credit', 'debit')),
    amount DECIMAL(15,4) NOT NULL,
    balance_before DECIMAL(15,4) NOT NULL,
    balance_after DECIMAL(15,4) NOT NULL,
    description TEXT NOT NULL,
    sourceable_type VARCHAR(255) NULL,
    sourceable_id BIGINT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user_balance_id) REFERENCES user_balances(id) ON DELETE CASCADE
);

-- Vouchers table
CREATE TABLE vouchers (
    id BIGSERIAL PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    discount_type VARCHAR(20) NOT NULL CHECK (discount_type IN ('percentage', 'fixed')),
    discount_value DECIMAL(10,2) NOT NULL,
    min_purchase DECIMAL(15,2) DEFAULT 0.00,
    max_discount DECIMAL(15,2) NULL,
    usage_limit INTEGER NULL,
    used_count INTEGER DEFAULT 0,
    valid_from TIMESTAMP NOT NULL,
    valid_until TIMESTAMP NOT NULL,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Voucher Redemptions table
CREATE TABLE voucher_redemptions (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    voucher_id BIGINT NOT NULL,
    redeemed_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (voucher_id) REFERENCES vouchers(id) ON DELETE CASCADE
);

-- Reverse Vending Machines table
CREATE TABLE reverse_vending_machines (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    address TEXT NULL,
    latitude DECIMAL(10,8) NULL,
    longitude DECIMAL(11,8) NULL,
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive', 'maintenance')),
    capacity INTEGER DEFAULT 100,
    current_load INTEGER DEFAULT 0,
    last_maintenance TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Deposits table (Main table with AI/CV fields)
CREATE TABLE deposits (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    rvm_id BIGINT NOT NULL,
    session_token VARCHAR(255) NULL,
    
    -- Legacy fields (nullable for backward compatibility)
    item_type_detected VARCHAR(255) NULL,
    item_condition VARCHAR(255) NULL,
    confidence_score DECIMAL(5,2) NULL,
    local_ai_result JSON NULL,
    gemini_validated BOOLEAN DEFAULT false,
    gemini_response JSON NULL,
    reward_value DECIMAL(15,2) NULL,
    image_path VARCHAR(500) NULL,
    deposited_at TIMESTAMP NULL,
    
    -- New fields for AI analysis
    waste_type VARCHAR(100) NULL,
    weight DECIMAL(8,3) NULL,
    quantity INTEGER NULL,
    quality_grade VARCHAR(1) NULL,
    ai_confidence DECIMAL(5,2) NULL,
    ai_analysis JSON NULL,
    
    -- Computer Vision fields (YOLO + SAM)
    cv_confidence DECIMAL(5,2) NULL,
    cv_analysis JSON NULL,
    cv_waste_type VARCHAR(100) NULL,
    cv_weight DECIMAL(8,3) NULL,
    cv_quantity INTEGER NULL,
    cv_quality_grade VARCHAR(1) NULL,
    
    -- AI fields (Gemini/Agent AI)
    ai_waste_type VARCHAR(100) NULL,
    ai_weight DECIMAL(8,3) NULL,
    ai_quantity INTEGER NULL,
    ai_quality_grade VARCHAR(1) NULL,
    
    -- Processing fields
    reward_amount DECIMAL(15,2) NULL,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'processing', 'completed', 'rejected')),
    rejection_reason TEXT NULL,
    processed_at TIMESTAMP NULL,
    
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (rvm_id) REFERENCES reverse_vending_machines(id) ON DELETE CASCADE
);

-- Sessions table (for RVM session management)
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT NULL,
    rvm_id BIGINT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'claimed', 'expired')),
    expires_at TIMESTAMP NOT NULL,
    claimed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (rvm_id) REFERENCES reverse_vending_machines(id) ON DELETE CASCADE
);

-- Personal Access Tokens table (Laravel Sanctum)
CREATE TABLE personal_access_tokens (
    id BIGSERIAL PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Cache table
CREATE TABLE cache (
    key VARCHAR(255) PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration INTEGER NOT NULL
);

-- Cache locks table
CREATE TABLE cache_locks (
    key VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INTEGER NOT NULL
);

-- Jobs table
CREATE TABLE jobs (
    id BIGSERIAL PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload TEXT NOT NULL,
    attempts SMALLINT NOT NULL,
    reserved_at INTEGER NULL,
    available_at INTEGER NOT NULL,
    created_at INTEGER NOT NULL
);

-- Job batches table
CREATE TABLE job_batches (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INTEGER NOT NULL,
    pending_jobs INTEGER NOT NULL,
    failed_jobs INTEGER NOT NULL,
    failed_job_ids TEXT NOT NULL,
    options MEDIUMTEXT NULL,
    cancelled_at INTEGER NULL,
    created_at INTEGER NOT NULL,
    finished_at INTEGER NULL
);

-- Failed jobs table
CREATE TABLE failed_jobs (
    id BIGSERIAL PRIMARY KEY,
    uuid VARCHAR(255) UNIQUE NOT NULL,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload TEXT NOT NULL,
    exception TEXT NOT NULL,
    failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- INDEXES
-- =============================================

-- Users indexes
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_created_at ON users(created_at);

-- Deposits indexes
CREATE INDEX idx_deposits_user_id ON deposits(user_id);
CREATE INDEX idx_deposits_rvm_id ON deposits(rvm_id);
CREATE INDEX idx_deposits_status ON deposits(status);
CREATE INDEX idx_deposits_created_at ON deposits(created_at);
CREATE INDEX idx_deposits_session_token ON deposits(session_token);
CREATE INDEX idx_deposits_ai_confidence ON deposits(ai_confidence);
CREATE INDEX idx_deposits_cv_confidence ON deposits(cv_confidence);
CREATE INDEX idx_deposits_waste_type ON deposits(waste_type);
CREATE INDEX idx_deposits_cv_waste_type ON deposits(cv_waste_type);
CREATE INDEX idx_deposits_ai_waste_type ON deposits(ai_waste_type);

-- Transactions indexes
CREATE INDEX idx_transactions_user_id ON transactions(user_id);
CREATE INDEX idx_transactions_user_balance_id ON transactions(user_balance_id);
CREATE INDEX idx_transactions_created_at ON transactions(created_at);
CREATE INDEX idx_transactions_sourceable ON transactions(sourceable_type, sourceable_id);

-- Sessions indexes
CREATE INDEX idx_sessions_user_id ON sessions(user_id);
CREATE INDEX idx_sessions_rvm_id ON sessions(rvm_id);
CREATE INDEX idx_sessions_status ON sessions(status);
CREATE INDEX idx_sessions_expires_at ON sessions(expires_at);
CREATE INDEX idx_sessions_session_token ON sessions(session_token);

-- Personal access tokens indexes
CREATE INDEX idx_personal_access_tokens_tokenable ON personal_access_tokens(tokenable_type, tokenable_id);

-- Jobs indexes
CREATE INDEX idx_jobs_queue ON jobs(queue);
CREATE INDEX idx_jobs_reserved_at ON jobs(reserved_at);
CREATE INDEX idx_jobs_available_at ON jobs(available_at);

-- =============================================
-- SAMPLE DATA
-- =============================================

-- Insert sample roles
INSERT INTO roles (name, display_name, description) VALUES
('admin', 'Administrator', 'Full system access'),
('user', 'User', 'Regular user access'),
('operator', 'Operator', 'RVM operator access');

-- Insert sample permissions
INSERT INTO permissions (name, display_name, description) VALUES
('create_deposits', 'Create Deposits', 'Can create new deposits'),
('view_deposits', 'View Deposits', 'Can view deposits'),
('process_deposits', 'Process Deposits', 'Can process deposits'),
('manage_users', 'Manage Users', 'Can manage users'),
('manage_rvms', 'Manage RVMs', 'Can manage RVMs');

-- Insert sample users
INSERT INTO users (name, email, password, created_at, updated_at) VALUES
('John Doe', 'john@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Jane Smith', 'jane@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Admin User', 'admin@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW());

-- Insert sample RVMs
INSERT INTO reverse_vending_machines (name, location, address, latitude, longitude, status, capacity, created_at, updated_at) VALUES
('RVM-001', 'Mall Central', 'Jl. Sudirman No. 1, Jakarta', -6.2088, 106.8456, 'active', 100, NOW(), NOW()),
('RVM-002', 'Station Plaza', 'Jl. Thamrin No. 2, Jakarta', -6.1944, 106.8229, 'active', 150, NOW(), NOW()),
('RVM-003', 'University Campus', 'Jl. Salemba No. 3, Jakarta', -6.1944, 106.8500, 'maintenance', 80, NOW(), NOW());

-- Insert sample vouchers
INSERT INTO vouchers (code, name, description, discount_type, discount_value, min_purchase, valid_from, valid_until, is_active, created_at, updated_at) VALUES
('WELCOME10', 'Welcome Discount', '10% discount for new users', 'percentage', 10.00, 0.00, NOW(), NOW() + INTERVAL '30 days', true, NOW(), NOW()),
('SAVE20', 'Save More', '20% discount for purchases above 50k', 'percentage', 20.00, 50000.00, NOW(), NOW() + INTERVAL '15 days', true, NOW(), NOW()),
('FIXED5K', 'Fixed Discount', '5k discount for any purchase', 'fixed', 5000.00, 0.00, NOW(), NOW() + INTERVAL '7 days', true, NOW(), NOW());

-- =============================================
-- VIEWS
-- =============================================

-- View for deposit statistics
CREATE VIEW deposit_statistics AS
SELECT 
    d.user_id,
    COUNT(*) as total_deposits,
    COUNT(CASE WHEN d.status = 'completed' THEN 1 END) as completed_deposits,
    COUNT(CASE WHEN d.status = 'pending' THEN 1 END) as pending_deposits,
    COUNT(CASE WHEN d.status = 'processing' THEN 1 END) as processing_deposits,
    COUNT(CASE WHEN d.status = 'rejected' THEN 1 END) as rejected_deposits,
    COALESCE(SUM(d.reward_amount), 0) as total_rewards,
    COALESCE(AVG(d.ai_confidence), 0) as avg_ai_confidence,
    COALESCE(AVG(d.cv_confidence), 0) as avg_cv_confidence,
    COUNT(DISTINCT d.waste_type) as waste_types_count
FROM deposits d
GROUP BY d.user_id;

-- View for user balance summary
CREATE VIEW user_balance_summary AS
SELECT 
    u.id as user_id,
    u.name,
    u.email,
    COALESCE(ub.balance, 0) as current_balance,
    COALESCE(SUM(CASE WHEN t.type = 'credit' THEN t.amount ELSE 0 END), 0) as total_credits,
    COALESCE(SUM(CASE WHEN t.type = 'debit' THEN t.amount ELSE 0 END), 0) as total_debits,
    COUNT(t.id) as total_transactions
FROM users u
LEFT JOIN user_balances ub ON u.id = ub.user_id
LEFT JOIN transactions t ON u.id = t.user_id
GROUP BY u.id, u.name, u.email, ub.balance;

-- =============================================
-- FUNCTIONS
-- =============================================

-- Function to calculate System Reward amount
CREATE OR REPLACE FUNCTION calculate_reward_amount(
    p_waste_type VARCHAR(100),
    p_weight DECIMAL(8,3),
    p_quality_grade VARCHAR(1),
    p_confidence DECIMAL(5,2)
) RETURNS DECIMAL(15,2) AS $$
DECLARE
    base_rate DECIMAL(10,2);
    quality_multiplier DECIMAL(3,2);
    confidence_factor DECIMAL(3,2);
    reward DECIMAL(15,2);
BEGIN
    -- Base rate per kg by waste type
    CASE p_waste_type
        WHEN 'plastic' THEN base_rate := 5000.00;
        WHEN 'glass' THEN base_rate := 3000.00;
        WHEN 'metal' THEN base_rate := 8000.00;
        WHEN 'paper' THEN base_rate := 2000.00;
        ELSE base_rate := 1000.00;
    END CASE;
    
    -- Quality multiplier
    CASE p_quality_grade
        WHEN 'A' THEN quality_multiplier := 1.2;
        WHEN 'B' THEN quality_multiplier := 1.0;
        WHEN 'C' THEN quality_multiplier := 0.8;
        WHEN 'D' THEN quality_multiplier := 0.5;
        ELSE quality_multiplier := 0.3;
    END CASE;
    
    -- Confidence factor
    confidence_factor := COALESCE(p_confidence, 50) / 100;
    
    -- Calculate reward
    reward := base_rate * p_weight * quality_multiplier * confidence_factor;
    
    RETURN ROUND(reward, 2);
END;
$$ LANGUAGE plpgsql;

-- =============================================
-- TRIGGERS
-- =============================================

-- Trigger to update user balance after transaction
CREATE OR REPLACE FUNCTION update_user_balance_after_transaction()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE user_balances 
    SET balance = NEW.balance_after,
        updated_at = NOW()
    WHERE id = NEW.user_balance_id;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_update_user_balance_after_transaction
    AFTER INSERT ON transactions
    FOR EACH ROW
    EXECUTE FUNCTION update_user_balance_after_transaction();

-- Trigger to update RVM current load
CREATE OR REPLACE FUNCTION update_rvm_load()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.status = 'completed' AND OLD.status != 'completed' THEN
        UPDATE reverse_vending_machines 
        SET current_load = current_load + 1,
            updated_at = NOW()
        WHERE id = NEW.rvm_id;
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_update_rvm_load
    AFTER UPDATE ON deposits
    FOR EACH ROW
    EXECUTE FUNCTION update_rvm_load();

-- =============================================
-- COMMENTS
-- =============================================

COMMENT ON TABLE deposits IS 'Main table for waste deposits with AI and Computer Vision analysis';
COMMENT ON COLUMN deposits.cv_confidence IS 'Computer Vision confidence score from YOLO + SAM';
COMMENT ON COLUMN deposits.ai_confidence IS 'AI confidence score from Gemini/Agent AI';
COMMENT ON COLUMN deposits.cv_analysis IS 'Computer Vision analysis result in JSON format';
COMMENT ON COLUMN deposits.ai_analysis IS 'AI analysis result in JSON format';
COMMENT ON COLUMN deposits.session_token IS 'RVM session token for tracking';

COMMENT ON TABLE user_balances IS 'User balance tracking for rewards and transactions';
COMMENT ON TABLE transactions IS 'Transaction history for all user balance changes';
COMMENT ON TABLE sessions IS 'RVM session management for user interactions';

-- =============================================
-- END OF SCHEMA
-- =============================================
