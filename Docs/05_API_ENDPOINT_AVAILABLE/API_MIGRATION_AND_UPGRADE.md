# 🔄 API Migration & Upgrade - MyRVM-Ecosystem v2.0

## 📍 Migration Overview

### Migration Types
- **Version Migration**: Upgrading from v1.x to v2.0
- **Data Migration**: Moving data between systems
- **Configuration Migration**: Updating configuration files
- **Code Migration**: Updating client code and integrations

---

## 🔄 Version Migration Guide

### From v1.5.0 to v2.0.0

#### 1. API Base URL Changes
```bash
# Old v1.5.0 URLs
OLD_BASE_URL="http://100.123.143.87:8001/api"

# New v2.0.0 URLs
NEW_BASE_URL="http://100.123.143.87:8001/api/v2"
```

#### 2. Authentication Changes
```javascript
// Old v1.5.0 - Session-based authentication
fetch('/api/rvms', {
    credentials: 'include'
});

// New v2.0.0 - API key-based authentication
fetch('/api/v2/rvms', {
    headers: {
        'Authorization': 'Bearer your-api-key',
        'Content-Type': 'application/json'
    }
});
```

#### 3. Response Format Changes
```javascript
// Old v1.5.0 - Direct data response
const rvms = await response.json();
// rvms = [{ id: 1, name: 'RVM 1', ... }]

// New v2.0.0 - Wrapped response
const result = await response.json();
if (result.success) {
    const rvms = result.data;
    // rvms = [{ id: 1, name: 'RVM 1', ... }]
} else {
    console.error(result.message);
}
```

#### 4. Error Handling Changes
```javascript
// Old v1.5.0 - HTTP status only
if (response.status === 404) {
    console.error('Not found');
}

// New v2.0.0 - Structured error response
const result = await response.json();
if (!result.success) {
    console.error(result.message);
    if (result.details) {
        console.error(result.details);
    }
}
```

---

## 🔄 Data Migration

### Database Migration Script
```php
<?php
// database/migrations/2025_01_02_000000_migrate_to_v2.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MigrateToV2 extends Migration
{
    public function up()
    {
        // 1. Create new tables
        $this->createNewTables();
        
        // 2. Migrate existing data
        $this->migrateExistingData();
        
        // 3. Update data formats
        $this->updateDataFormats();
        
        // 4. Create indexes
        $this->createIndexes();
    }
    
    private function createNewTables()
    {
        // Create user_balances table
        Schema::create('user_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('balance', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->timestamps();
        });
        
        // Create transactions table
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['reward', 'purchase', 'refund', 'bonus']);
            $table->decimal('amount', 10, 2);
            $table->decimal('new_balance', 10, 2);
            $table->text('description')->nullable();
            $table->morphs('source');
            $table->timestamps();
        });
        
        // Create vouchers table
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed']);
            $table->decimal('value', 10, 2);
            $table->integer('usage_limit_per_user')->nullable();
            $table->integer('total_usage_limit')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->decimal('min_purchase_amount', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        // Create voucher_redemptions table
        Schema::create('voucher_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('voucher_id')->constrained()->onDelete('cascade');
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->decimal('redeemed_amount', 10, 2);
            $table->timestamps();
        });
    }
    
    private function migrateExistingData()
    {
        // Migrate users to have API keys
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            $apiKey = hash('sha256', Str::random(64) . time());
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'api_key' => $apiKey,
                    'api_key_expires_at' => now()->addDays(30)
                ]);
        }
        
        // Migrate RVMs to have API keys
        $rvms = DB::table('reverse_vending_machines')->get();
        foreach ($rvms as $rvm) {
            $apiKey = hash('sha256', Str::random(64) . time());
            DB::table('reverse_vending_machines')
                ->where('id', $rvm->id)
                ->update([
                    'api_key' => $apiKey,
                    'api_key_expires_at' => now()->addDays(30)
                ]);
        }
        
        // Create initial user balances
        foreach ($users as $user) {
            DB::table('user_balances')->insert([
                'user_id' => $user->id,
                'balance' => 0,
                'currency' => 'USD',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
    
    private function updateDataFormats()
    {
        // Update detection_results to use JSON format
        $detections = DB::table('detection_results')->get();
        foreach ($detections as $detection) {
            if (is_string($detection->detection_results)) {
                $decoded = json_decode($detection->detection_results, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    DB::table('detection_results')
                        ->where('id', $detection->id)
                        ->update(['detection_results' => $decoded]);
                }
            }
        }
    }
    
    private function createIndexes()
    {
        // Create indexes for performance
        Schema::table('reverse_vending_machines', function (Blueprint $table) {
            $table->index('status');
            $table->index('created_at');
            $table->index('ip_address');
            $table->index(['status', 'created_at']);
        });
        
        Schema::table('detection_results', function (Blueprint $table) {
            $table->index('rvm_id');
            $table->index('created_at');
            $table->index(['rvm_id', 'created_at']);
        });
        
        Schema::table('user_balances', function (Blueprint $table) {
            $table->index('user_id');
        });
        
        Schema::table('transactions', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('type');
            $table->index('created_at');
            $table->index(['user_id', 'created_at']);
        });
    }
    
    public function down()
    {
        // Rollback migration
        Schema::dropIfExists('voucher_redemptions');
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('user_balances');
        
        // Remove API keys
        DB::table('users')->update([
            'api_key' => null,
            'api_key_expires_at' => null
        ]);
        
        DB::table('reverse_vending_machines')->update([
            'api_key' => null,
            'api_key_expires_at' => null
        ]);
    }
}
```

### Data Migration Script
```bash
#!/bin/bash
# migrate_data.sh

echo "🔄 Starting Data Migration"
echo "========================="

# Set variables
DB_HOST="localhost"
DB_PORT="5432"
DB_NAME="myrvm_ecosystem"
DB_USER="myrvm_user"
DB_PASSWORD="myrvm_password"

# 1. Backup existing database
echo "1. Creating database backup..."
pg_dump -h $DB_HOST -p $DB_PORT -U $DB_USER -d $DB_NAME > backup_$(date +%Y%m%d_%H%M%S).sql

# 2. Run migration
echo "2. Running database migration..."
docker-compose exec app php artisan migrate

# 3. Migrate configuration files
echo "3. Migrating configuration files..."
cp .env.example .env.backup
cp .env.v2 .env

# 4. Update API keys in configuration
echo "4. Updating API keys..."
# This would be done by the migration script

# 5. Test migration
echo "5. Testing migration..."
curl -f http://100.123.143.87:8001/api/v2/health
if [ $? -eq 0 ]; then
    echo "✅ Migration successful!"
else
    echo "❌ Migration failed!"
    exit 1
fi

echo "✅ Data migration completed!"
```

---

## 🔄 Configuration Migration

### Environment Configuration
```bash
# .env.v2 - New v2.0.0 configuration
APP_NAME="MyRVM Ecosystem v2.0"
APP_ENV=production
APP_KEY=base64:your-32-character-secret-key
APP_DEBUG=false
APP_URL=http://100.123.143.87:8001

# Database configuration
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=myrvm_ecosystem
DB_USERNAME=myrvm_user
DB_PASSWORD=myrvm_password

# Redis configuration
REDIS_HOST=redis
REDIS_PASSWORD=myrvm_redis_password
REDIS_PORT=6379

# API configuration
API_RATE_LIMIT=1000
API_RATE_LIMIT_WINDOW=60
API_KEY_LENGTH=64
API_KEY_EXPIRATION_DAYS=30

# WebSocket configuration
PUSHER_APP_ID=myrvm
PUSHER_APP_KEY=myrvm_key
PUSHER_APP_SECRET=myrvm_secret
PUSHER_APP_CLUSTER=mt1

# Monitoring configuration
MONITORING_ENABLED=true
ALERT_EMAIL=admin@myrvm.com
ALERT_SLACK_WEBHOOK=https://hooks.slack.com/services/...

# Jetson configuration
JETSON_API_BASE_URL=http://100.117.234.2:5000
JETSON_API_KEY=38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1
```

### Jetson Configuration Migration
```bash
# rvm_config.env.v2 - New Jetson configuration
# MyRVM-Ecosystem-v2 RVM Configuration
# Update these values according to your setup

# MyRVM-Ecosystem-v2 Integration
RVM_API_BASE_URL=http://100.123.143.87:8001/api/v2
RVM_API_KEY=38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1

# API Configuration
API_HOST=100.117.234.2
API_PORT=5000
API_DEBUG=false

# Data Directories
BASE_DATA_DIR=../../data-jetson
UPLOAD_FOLDER=../../data-jetson/input
OUTPUT_FOLDER=../../data-jetson/output

# File Upload Settings
MAX_CONTENT_LENGTH=16777216  # 16MB
ALLOWED_EXTENSIONS=png,jpg,jpeg,gif,bmp

# Cache Settings
RVM_CACHE_TTL=300  # 5 minutes

# GPU Settings
CUDA_VISIBLE_DEVICES=0

# RVM IDs (comma-separated)
RVM_IDS=1,2,3

# Monitoring Settings
MONITORING_ENABLED=true
MONITORING_INTERVAL=60
ALERT_THRESHOLD_CPU=90
ALERT_THRESHOLD_MEMORY=90
ALERT_THRESHOLD_GPU=90
```

---

## 🔄 Code Migration

### JavaScript Client Migration
```javascript
// Old v1.5.0 client
class OldAPIClient {
    constructor(baseURL) {
        this.baseURL = baseURL;
    }
    
    async getRvms() {
        const response = await fetch(`${this.baseURL}/api/rvms`, {
            credentials: 'include'
        });
        return response.json();
    }
}

// New v2.0.0 client
class NewAPIClient {
    constructor(baseURL, apiKey) {
        this.baseURL = baseURL;
        this.apiKey = apiKey;
        this.headers = {
            'Authorization': `Bearer ${apiKey}`,
            'Content-Type': 'application/json'
        };
    }
    
    async getRvms() {
        const response = await fetch(`${this.baseURL}/api/v2/rvms`, {
            headers: this.headers
        });
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message);
        }
        
        return result.data;
    }
}

// Migration helper
class APIMigrationHelper {
    static migrateClient(oldClient, apiKey) {
        return new NewAPIClient(oldClient.baseURL, apiKey);
    }
    
    static migrateResponse(oldResponse) {
        // Convert old response format to new format
        if (Array.isArray(oldResponse)) {
            return {
                success: true,
                data: oldResponse,
                timestamp: new Date().toISOString()
            };
        }
        
        return oldResponse;
    }
}
```

### Python Client Migration
```python
# Old v1.5.0 client
class OldAPIClient:
    def __init__(self, base_url):
        self.base_url = base_url
        self.session = requests.Session()
    
    def get_rvms(self):
        response = self.session.get(f"{self.base_url}/api/rvms")
        return response.json()

# New v2.0.0 client
class NewAPIClient:
    def __init__(self, base_url, api_key):
        self.base_url = base_url
        self.api_key = api_key
        self.session = requests.Session()
        self.session.headers.update({
            'Authorization': f'Bearer {api_key}',
            'Content-Type': 'application/json'
        })
    
    def get_rvms(self):
        response = self.session.get(f"{self.base_url}/api/v2/rvms")
        result = response.json()
        
        if not result.get('success'):
            raise Exception(result.get('message', 'API error'))
        
        return result['data']

# Migration helper
class APIMigrationHelper:
    @staticmethod
    def migrate_client(old_client, api_key):
        return NewAPIClient(old_client.base_url, api_key)
    
    @staticmethod
    def migrate_response(old_response):
        if isinstance(old_response, list):
            return {
                'success': True,
                'data': old_response,
                'timestamp': datetime.now().isoformat()
            }
        return old_response
```

---

## 🔄 Testing Migration

### Migration Test Suite
```php
<?php
// tests/Feature/MigrationTest.php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\ReverseVendingMachine;
use App\Models\DetectionResult;

class MigrationTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_user_api_key_migration()
    {
        // Create user without API key
        $user = User::factory()->create([
            'api_key' => null,
            'api_key_expires_at' => null
        ]);
        
        // Run migration
        $this->artisan('migrate');
        
        // Refresh user
        $user->refresh();
        
        // Assert API key was created
        $this->assertNotNull($user->api_key);
        $this->assertNotNull($user->api_key_expires_at);
        $this->assertEquals(64, strlen($user->api_key));
    }
    
    public function test_rvm_api_key_migration()
    {
        // Create RVM without API key
        $rvm = ReverseVendingMachine::factory()->create([
            'api_key' => null,
            'api_key_expires_at' => null
        ]);
        
        // Run migration
        $this->artisan('migrate');
        
        // Refresh RVM
        $rvm->refresh();
        
        // Assert API key was created
        $this->assertNotNull($rvm->api_key);
        $this->assertNotNull($rvm->api_key_expires_at);
        $this->assertEquals(64, strlen($rvm->api_key));
    }
    
    public function test_user_balance_creation()
    {
        // Create user
        $user = User::factory()->create();
        
        // Run migration
        $this->artisan('migrate');
        
        // Assert user balance was created
        $this->assertDatabaseHas('user_balances', [
            'user_id' => $user->id,
            'balance' => 0,
            'currency' => 'USD'
        ]);
    }
    
    public function test_detection_results_json_migration()
    {
        // Create detection result with string data
        $detection = DetectionResult::factory()->create([
            'detection_results' => '{"class": "bottle", "confidence": 0.95}'
        ]);
        
        // Run migration
        $this->artisan('migrate');
        
        // Refresh detection
        $detection->refresh();
        
        // Assert data was converted to JSON
        $this->assertIsArray($detection->detection_results);
        $this->assertEquals('bottle', $detection->detection_results['class']);
        $this->assertEquals(0.95, $detection->detection_results['confidence']);
    }
}
```

### Integration Test Suite
```php
<?php
// tests/Feature/IntegrationMigrationTest.php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\ReverseVendingMachine;

class IntegrationMigrationTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_api_v2_endpoints_work_after_migration()
    {
        // Run migration
        $this->artisan('migrate');
        
        // Create test data
        $user = User::factory()->create();
        $rvm = ReverseVendingMachine::factory()->create();
        
        // Test health endpoint
        $response = $this->get('/api/v2/health');
        $response->assertStatus(200);
        
        // Test RVM endpoints
        $response = $this->get('/api/v2/rvms', [
            'Authorization' => 'Bearer ' . $user->api_key
        ]);
        $response->assertStatus(200);
        
        // Test detection results endpoint
        $response = $this->get('/api/v2/detection-results', [
            'Authorization' => 'Bearer ' . $user->api_key
        ]);
        $response->assertStatus(200);
        
        // Test analytics endpoint
        $response = $this->get('/api/v2/analytics/dashboard', [
            'Authorization' => 'Bearer ' . $user->api_key
        ]);
        $response->assertStatus(200);
    }
    
    public function test_old_api_endpoints_redirect_to_v2()
    {
        // Run migration
        $this->artisan('migrate');
        
        $user = User::factory()->create();
        
        // Test old RVM endpoint redirects
        $response = $this->get('/api/rvms', [
            'Authorization' => 'Bearer ' . $user->api_key
        ]);
        $response->assertStatus(301); // Redirect to v2
        
        // Test old detection results endpoint redirects
        $response = $this->get('/api/detection-results', [
            'Authorization' => 'Bearer ' . $user->api_key
        ]);
        $response->assertStatus(301); // Redirect to v2
    }
}
```

---

## 🔄 Rollback Procedures

### Database Rollback
```bash
#!/bin/bash
# rollback_database.sh

echo "🔄 Rolling back database migration"
echo "================================="

# 1. Stop services
echo "1. Stopping services..."
docker-compose down

# 2. Restore from backup
echo "2. Restoring from backup..."
BACKUP_FILE="backup_$(date +%Y%m%d_%H%M%S).sql"
if [ -f "$BACKUP_FILE" ]; then
    psql -h localhost -U myrvm_user -d myrvm_ecosystem < "$BACKUP_FILE"
    echo "✅ Database restored from backup"
else
    echo "❌ No backup file found: $BACKUP_FILE"
    exit 1
fi

# 3. Rollback migrations
echo "3. Rolling back migrations..."
docker-compose exec app php artisan migrate:rollback --step=5

# 4. Restore configuration
echo "4. Restoring configuration..."
cp .env.backup .env

# 5. Start services
echo "5. Starting services..."
docker-compose up -d

# 6. Test rollback
echo "6. Testing rollback..."
sleep 30
curl -f http://100.123.143.87:8001/api/health
if [ $? -eq 0 ]; then
    echo "✅ Rollback successful!"
else
    echo "❌ Rollback failed!"
    exit 1
fi

echo "✅ Database rollback completed!"
```

### Configuration Rollback
```bash
#!/bin/bash
# rollback_config.sh

echo "🔄 Rolling back configuration"
echo "============================"

# 1. Restore environment file
echo "1. Restoring environment file..."
if [ -f ".env.backup" ]; then
    cp .env.backup .env
    echo "✅ Environment file restored"
else
    echo "❌ No backup environment file found"
    exit 1
fi

# 2. Restore Jetson configuration
echo "2. Restoring Jetson configuration..."
if [ -f "rvm_config.env.backup" ]; then
    cp rvm_config.env.backup rvm_config.env
    echo "✅ Jetson configuration restored"
else
    echo "❌ No backup Jetson configuration found"
fi

# 3. Clear caches
echo "3. Clearing caches..."
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear

# 4. Restart services
echo "4. Restarting services..."
docker-compose restart

echo "✅ Configuration rollback completed!"
```

---

## 🔄 Migration Checklist

### Pre-Migration Checklist
- [ ] Backup database
- [ ] Backup configuration files
- [ ] Test migration on staging environment
- [ ] Notify users of maintenance window
- [ ] Prepare rollback plan
- [ ] Update documentation
- [ ] Train support team

### Migration Checklist
- [ ] Stop services
- [ ] Run database migration
- [ ] Update configuration files
- [ ] Update client code
- [ ] Test all endpoints
- [ ] Verify data integrity
- [ ] Start services
- [ ] Monitor system health

### Post-Migration Checklist
- [ ] Verify all functionality works
- [ ] Check performance metrics
- [ ] Monitor error logs
- [ ] Update user documentation
- [ ] Notify users of completion
- [ ] Clean up old files
- [ ] Update monitoring alerts

---

## 🔄 Migration Timeline

### Phase 1: Preparation (Week 1)
- Backup all data
- Test migration on staging
- Update documentation
- Prepare rollback procedures

### Phase 2: Migration (Week 2)
- Execute database migration
- Update configuration files
- Deploy new code
- Test all functionality

### Phase 3: Validation (Week 3)
- Monitor system performance
- Verify data integrity
- Test all integrations
- Address any issues

### Phase 4: Cleanup (Week 4)
- Remove old code
- Clean up old files
- Update monitoring
- Complete documentation

---

**Last Updated**: 2025-01-02  
**Version**: 2.0.0  
**Status**: ✅ COMPLETE MIGRATION & UPGRADE DOCUMENTATION
