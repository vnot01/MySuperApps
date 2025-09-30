#!/usr/bin/env bash
set -euo pipefail

echo "=== RESTART SWAP MEMORY SERVICES ==="

echo "[1/8] Stop all zram services"
sudo systemctl stop systemd-zram-setup@zram0.service 2>/dev/null || true
sudo systemctl stop systemd-zram-setup@zram1.service 2>/dev/null || true
sudo systemctl stop systemd-zram-setup@zram2.service 2>/dev/null || true
sudo systemctl stop systemd-zram-setup@zram3.service 2>/dev/null || true
sudo systemctl stop systemd-zram-setup@zram4.service 2>/dev/null || true
sudo systemctl stop systemd-zram-setup@zram5.service 2>/dev/null || true

echo "[2/8] Disable all zram services"
sudo systemctl disable systemd-zram-setup@zram0.service 2>/dev/null || true
sudo systemctl disable systemd-zram-setup@zram1.service 2>/dev/null || true
sudo systemctl disable systemd-zram-setup@zram2.service 2>/dev/null || true
sudo systemctl disable systemd-zram-setup@zram3.service 2>/dev/null || true
sudo systemctl disable systemd-zram-setup@zram4.service 2>/dev/null || true
sudo systemctl disable systemd-zram-setup@zram5.service 2>/dev/null || true

echo "[3/8] Swap off all zram devices"
for dev in /dev/zram*; do
  if [ -b "$dev" ]; then
    echo "Swapping off $dev"
    sudo swapoff "$dev" 2>/dev/null || true
  fi
done

echo "[4/8] Reset all zram devices"
for i in {0..5}; do
  if [ -e "/sys/block/zram$i/reset" ]; then
    echo "Resetting zram$i"
    echo 1 | sudo tee "/sys/block/zram$i/reset" >/dev/null 2>&1 || true
  fi
done

echo "[5/8] Unload zram module"
sudo modprobe -r zram 2>/dev/null || true

echo "[6/8] Wait 5 seconds for complete cleanup"
sleep 5

echo "[7/8] Reload systemd and start zram0 service"
sudo systemctl daemon-reload
sudo systemctl enable systemd-zram-setup@zram0.service
sudo systemctl start systemd-zram-setup@zram0.service

echo "[8/8] Verify swap memory status"
echo "=== Current Swap Status ==="
echo "swapon --show:"
swapon --show || echo "No swap active"
echo ""
echo "/proc/swaps:"
cat /proc/swaps || echo "No swap info"
echo ""
echo "free -h:"
free -h || echo "No memory info"
echo ""
echo "zram devices:"
ls -la /dev/zram* 2>/dev/null || echo "No zram devices found"
echo ""
echo "zram0 size:"
cat /sys/block/zram0/disksize 2>/dev/null || echo "zram0 not found"
echo ""
echo "zram0 compression:"
cat /sys/block/zram0/comp_algorithm 2>/dev/null || echo "zram0 not found"

echo ""
echo "=== SWAP SERVICES RESTARTED ==="
echo "Target: /dev/zram0 ~14-16G (zstd, prio 150), /swapfile 16G (prio 60)"
