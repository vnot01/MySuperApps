#!/usr/bin/env bash
set -euo pipefail

echo "=== SWAP MEMORY STATUS CHECK ==="

echo "[1/6] Current swap usage"
echo "swapon --show:"
swapon --show || echo "No swap active"
echo ""

echo "[2/6] Memory usage"
echo "free -h:"
free -h || echo "No memory info"
echo ""

echo "[3/6] Swap details"
echo "/proc/swaps:"
cat /proc/swaps || echo "No swap info"
echo ""

echo "[4/6] Zram devices"
echo "Available zram devices:"
ls -la /dev/zram* 2>/dev/null || echo "No zram devices found"
echo ""

echo "[5/6] Zram0 configuration"
if [ -e "/sys/block/zram0/disksize" ]; then
  echo "zram0 size: $(cat /sys/block/zram0/disksize) bytes"
  echo "zram0 compression: $(cat /sys/block/zram0/comp_algorithm)"
  echo "zram0 memory used: $(cat /sys/block/zram0/mem_used_total) bytes"
  echo "zram0 memory limit: $(cat /sys/block/zram0/mem_limit) bytes"
else
  echo "zram0 not found"
fi
echo ""

echo "[6/6] Systemd zram services"
echo "systemd-zram-setup@zram0.service status:"
systemctl status systemd-zram-setup@zram0.service --no-pager -l || echo "Service not found"
echo ""

echo "=== SWAP MEMORY SUMMARY ==="
TOTAL_SWAP=$(free -h | awk '/^Swap:/ {print $2}')
USED_SWAP=$(free -h | awk '/^Swap:/ {print $3}')
FREE_SWAP=$(free -h | awk '/^Swap:/ {print $4}')

echo "Total Swap: $TOTAL_SWAP"
echo "Used Swap: $USED_SWAP" 
echo "Free Swap: $FREE_SWAP"

if [ "$TOTAL_SWAP" != "0B" ] && [ "$TOTAL_SWAP" != "0" ]; then
  echo "✅ Swap memory is active"
else
  echo "❌ No swap memory active"
fi
