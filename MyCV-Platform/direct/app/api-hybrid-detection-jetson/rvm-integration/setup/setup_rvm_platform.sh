#!/bin/bash
# MyRVM-Platform Integration Setup Script
# Run this script in your MyRVM-Platform root directory

echo "🚀 Setting up MyRVM-Platform for MyCV-Platform integration..."

# Check if we're in Laravel project
if [ ! -f "artisan" ]; then
    echo "❌ Error: Not in Laravel project root. Please run this script from MyRVM-Platform root directory."
    exit 1
fi

# Create necessary directories
echo "📁 Creating directories..."
mkdir -p app/Http/Controllers/Api
mkdir -p app/Models
mkdir -p database/migrations

# Copy files from MyCV-Platform
echo "📋 Copying integration files..."

# Copy migration
if [ -f "../MyCV-Platform/direct/app/api-hybrid-detection-jetson/detection_results_migration.php" ]; then
    cp "../MyCV-Platform/direct/app/api-hybrid-detection-jetson/detection_results_migration.php" "database/migrations/$(date +%Y_%m_%d_%H%M%S)_create_detection_results_table.php"
    echo "✅ Migration file copied"
else
    echo "⚠️  Migration file not found. Please copy manually."
fi

# Copy model
if [ -f "../MyCV-Platform/direct/app/api-hybrid-detection-jetson/DetectionResult_model.php" ]; then
    cp "../MyCV-Platform/direct/app/api-hybrid-detection-jetson/DetectionResult_model.php" "app/Models/DetectionResult.php"
    echo "✅ Model file copied"
else
    echo "⚠️  Model file not found. Please copy manually."
fi

# Copy controller
if [ -f "../MyCV-Platform/direct/app/api-hybrid-detection-jetson/rvm_platform_endpoints_example.php" ]; then
    cp "../MyCV-Platform/direct/app/api-hybrid-detection-jetson/rvm_platform_endpoints_example.php" "app/Http/Controllers/Api/RvmIntegrationController.php"
    echo "✅ Controller file copied"
else
    echo "⚠️  Controller file not found. Please copy manually."
fi

# Add routes to api.php
echo "🛣️  Adding routes to api.php..."
if [ -f "routes/api.php" ]; then
    # Check if routes already exist
    if ! grep -q "RVM Integration API Routes" routes/api.php; then
        echo "" >> routes/api.php
        echo "// RVM Integration API Routes" >> routes/api.php
        echo "// Add these routes for MyCV-Platform integration" >> routes/api.php
        echo "Route::prefix('rvm')->group(function () {" >> routes/api.php
        echo "    Route::post('validate-api-key', [App\Http\Controllers\Api\RvmIntegrationController::class, 'validateApiKey']);" >> routes/api.php
        echo "    Route::get('{id}', [App\Http\Controllers\Api\RvmIntegrationController::class, 'getRvm']);" >> routes/api.php
        echo "    Route::get('{id}/stats', [App\Http\Controllers\Api\RvmIntegrationController::class, 'getRvmStats']);" >> routes/api.php
        echo "});" >> routes/api.php
        echo "" >> routes/api.php
        echo "Route::prefix('detections')->group(function () {" >> routes/api.php
        echo "    Route::post('store', [App\Http\Controllers\Api\RvmIntegrationController::class, 'storeDetection']);" >> routes/api.php
        echo "});" >> routes/api.php
        echo "✅ Routes added to api.php"
    else
        echo "⚠️  Routes already exist in api.php"
    fi
else
    echo "❌ routes/api.php not found"
fi

# Run migrations
echo "🗄️  Running migrations..."
php artisan migrate

echo "🎉 Setup completed!"
echo ""
echo "Next steps:"
echo "1. Update your RVM API keys in the database"
echo "2. Test the integration with MyCV-Platform"
echo "3. Configure your RVM Platform URL in MyCV-Platform"
