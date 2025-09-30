#!/usr/bin/env bash
set -euo pipefail

echo "=== STOP ALL ZRAM SERVICES ==="
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

echo "=== WAIT 3 SECONDS ==="
sleep 3

echo "=== START ZRAM SERVICES ==="
echo "[6/8] Reload systemd"
sudo systemctl daemon-reload

echo "[7/8] Enable and start zram0 service"
sudo systemctl enable systemd-zram-setup@zram0.service
sudo systemctl start systemd-zram-setup@zram0.service

echo "[8/8] Verify zram status"
echo "=== swapon --show ==="
swapon --show || true
echo "=== /proc/swaps ==="
cat /proc/swaps || true
echo "=== free -h ==="
free -h || true
echo "=== zram devices ==="
ls -la /dev/zram* 2>/dev/null || echo "No zram devices found"

echo "Done! Zram services restarted cleanly."
