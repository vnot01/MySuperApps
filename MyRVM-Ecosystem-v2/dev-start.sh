#!/bin/bash

echo "🚀 Starting MyRVM Ecosystem Development Mode..."
echo "================================================"

# Start Laravel server in background
echo "📡 Starting Laravel server..."
docker compose exec -d app php artisan serve --host=0.0.0.0 --port=8000

# Start Vite dev server with HMR
echo "⚡ Starting Vite dev server with Hot Module Replacement..."
echo "   - Frontend changes will auto-reload"
echo "   - No need to run 'npm run build' manually"
echo "   - Access: http://localhost:8000"
echo ""

docker compose exec app npm run dev:docker
