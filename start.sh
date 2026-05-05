#!/bin/bash
# Fultala Flower Shop – PHP built-in server
echo "🌸 Fultala Flower Shop ажиллаж байна..."
echo "   http://localhost:8000"
echo "   Зогсоохын тулд Ctrl+C дарна уу"
echo ""
cd "$(dirname "$0")"
php -S localhost:8000
