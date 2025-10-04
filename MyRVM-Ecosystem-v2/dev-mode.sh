#!/bin/bash

echo "🚀 MyRVM Ecosystem - Development Mode"
echo "====================================="
echo ""
echo "Starting development environment..."
echo ""

# Stop any existing Vite processes
echo "🛑 Stopping existing Vite processes..."
docker compose exec app pkill -f vite || true

# Build assets first
echo "📦 Building assets..."
docker compose exec app npm run build

# Start Vite dev server in background
echo "⚡ Starting Vite dev server..."
docker compose exec -d app npm run dev:docker

# Wait a moment for Vite to start
sleep 3

echo ""
echo "✅ Development mode started!"
echo "🌐 Access your app at: http://100.123.143.87:8001"
echo "⚡ Vite dev server running on http://100.123.143.87:5173"
echo "🔄 HMR configured for external IP: 100.123.143.87"
echo ""
echo "📝 To stop development mode:"
echo "   docker compose exec app pkill -f vite"
echo ""
echo "🔄 To see Vite logs:"
echo "   docker compose logs -f app"
