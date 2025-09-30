#!/usr/bin/env bash
set -euo pipefail

echo "[1/6] Install and configure systemd-zram-generator (2x RAM, cap 16G, zstd, prio 150)"
sudo apt update -y
sudo apt install -y systemd-zram-generator

# Remove old zram-tools if exists to avoid conflicts
sudo apt remove -y zram-tools 2>/dev/null || true

# Configure zram-generator for 2x RAM (capped at 16G)
sudo tee /etc/systemd/zram-generator.conf >/dev/null <<'EOF'
[zram0]
zram-fraction = 2.0
max-zram-size = 16G
compression-algorithm = zstd
swap-priority = 150
fs-type = swap
EOF

echo "[2/6] Stop existing zram and clean up"
sudo systemctl stop systemd-zram-setup@zram0.service 2>/dev/null || true
sudo swapoff /dev/zram0 2>/dev/null || true
if [ -e /sys/block/zram0/reset ]; then
  echo 1 | sudo tee /sys/block/zram0/reset >/dev/null
fi
sudo modprobe -r zram 2>/dev/null || true

echo "[3/6] Start zram with new config"
sudo systemctl daemon-reload
sudo systemctl start systemd-zram-setup@zram0.service

echo "[4/6] Ensure NVMe /swapfile active with priority 60 (lower than zram)"
sudo swapoff /swapfile 2>/dev/null || true
sudo swapon -p 60 /swapfile
sudo sed -i.bak -E 's#^(/swapfile\s+none\s+swap\s+).*#\1sw,pri=60 0 0#' /etc/fstab
grep -qE '^/swapfile\s+none\s+swap' /etc/fstab || echo '/swapfile none swap sw,pri=60 0 0' | sudo tee -a /etc/fstab >/dev/null

echo "[5/6] Persist kernel tunables"
sudo sed -i -E 's/^vm.swappiness=.*/vm.swappiness=80/' /etc/sysctl.conf || true
sudo sed -i -E 's/^vm.vfs_cache_pressure=.*/vm.vfs_cache_pressure=50/' /etc/sysctl.conf || true
grep -q '^vm.swappiness=' /etc/sysctl.conf || echo 'vm.swappiness=80' | sudo tee -a /etc/sysctl.conf >/dev/null
grep -q '^vm.vfs_cache_pressure=' /etc/sysctl.conf || echo 'vm.vfs_cache_pressure=50' | sudo tee -a /etc/sysctl.conf >/dev/null
sudo sysctl -p

echo "[6/6] Verify"
echo "=== swapon --show ==="; swapon --show || true
echo "=== /proc/swaps ==="; cat /proc/swaps || true
echo "=== free -h ==="; free -h || true
echo "Done. Target: /dev/zram0 ~14-16G prio 150, /swapfile 16G prio 60. Note: CUDA VRAM cannot be swapped."