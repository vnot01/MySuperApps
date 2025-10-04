#!/bin/bash

echo "🚀 MyRVM Ecosystem - Simple Development Mode"
echo "============================================="
echo ""

# Build assets first
echo "📦 Building assets..."
docker compose exec app npm run build

echo ""
echo "✅ Assets built successfully!"
echo "🌐 Access your app at: http://100.123.143.87:8001"
echo ""
echo "💡 For real-time development:"
echo "   1. Run: docker compose exec app npm run dev:docker"
echo "   2. Edit your Vue/JS files"
echo "   3. Changes will auto-reload in browser"
echo ""
echo "🔄 To rebuild after changes:"
echo "   docker compose exec app npm run build"
