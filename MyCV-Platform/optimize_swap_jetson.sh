#!/usr/bin/env bash
set -euo pipefail

echo "[1/6] Write zram-tools config (16G zstd, priority 150)"
sudo tee /etc/default/zramswap >/dev/null <<'EOF'
ALGO=zstd
ZRAM_SIZE=16G
PRIORITY=150
EOF

echo "[2/6] Restart zramswap cleanly"
sudo systemctl stop zramswap || true
sudo swapoff /dev/zram* 2>/dev/null || true
sudo modprobe -r zram 2>/dev/null || true
if ! sudo systemctl start zramswap; then
  echo "[info] systemctl zramswap failed, try service ..."
  sudo service zramswap restart || true
fi

echo "[3/6] Ensure NVMe /swapfile active with priority 60 (lower than zram)"
sudo swapoff /swapfile 2>/dev/null || true
sudo swapon -p 60 /swapfile
sudo sed -i.bak -E 's#^(/swapfile\s+none\s+swap\s+).*#\1sw,pri=60 0 0#' /etc/fstab
grep -qE '^/swapfile\s+none\s+swap' /etc/fstab || echo '/swapfile none swap sw,pri=60 0 0' | sudo tee -a /etc/fstab >/dev/null

echo "[4/6] Persist kernel tunables"
sudo sed -i -E 's/^vm.swappiness=.*/vm.swappiness=80/' /etc/sysctl.conf || true
sudo sed -i -E 's/^vm.vfs_cache_pressure=.*/vm.vfs_cache_pressure=50/' /etc/sysctl.conf || true
grep -q '^vm.swappiness=' /etc/sysctl.conf || echo 'vm.swappiness=80' | sudo tee -a /etc/sysctl.conf >/dev/null
grep -q '^vm.vfs_cache_pressure=' /etc/sysctl.conf || echo 'vm.vfs_cache_pressure=50' | sudo tee -a /etc/sysctl.conf >/dev/null
sudo sysctl -p

echo "[5/6] Fallback to zram-generator if zram size still small"
need_generator=false
if swapon --show | awk '$1 ~ /zram/ {sum+=$3} END{print (sum < 8000000 ? "yes":"no")}' | grep -q yes; then
  need_generator=true
fi

if $need_generator; then
  echo "[info] Install and configure zram-generator (16G zstd, priority 150)"
  sudo apt update -y
  sudo apt install -y zram-generator
  sudo tee /etc/systemd/zram-generator.conf >/dev/null <<'EOF'
[zram0]
zram-size = 16G
compression-algorithm = zstd
swap-priority = 150
fs-type = swap
EOF
  sudo systemctl daemon-reload
  sudo systemctl stop zramswap || true
  sudo swapoff /dev/zram* 2>/dev/null || true
  sudo modprobe -r zram 2>/dev/null || true
  sudo systemctl restart systemd-zram-setup@zram0.service
fi

echo "[6/6] Verify"
echo "=== swapon --show ==="; swapon --show || true
echo "=== /proc/swaps ==="; cat /proc/swaps || true
echo "=== free -h ==="; free -h || true
echo "Done. Note: CUDA VRAM tidak bisa di-swap; swap hanya membantu RAM host."