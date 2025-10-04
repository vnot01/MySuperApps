#!/bin/bash

echo "🚀 MyRVM Ecosystem - Development Mode"
echo "====================================="
echo ""
echo "Starting Vite dev server with Hot Module Replacement..."
echo "✅ Changes to Vue/JS files will auto-reload"
echo "✅ No need to run 'npm run build' manually"
echo "✅ Access your app at: http://localhost:8001"
echo ""
echo "Press Ctrl+C to stop the dev server"
echo ""

# Start Vite dev server
docker compose exec app npm run dev:docker
