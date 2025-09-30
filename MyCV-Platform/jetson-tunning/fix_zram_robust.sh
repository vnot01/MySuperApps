#!/usr/bin/env bash
set -euo pipefail

echo "[1/8] Install systemd-zram-generator if not present"
if ! dpkg -l | grep -q systemd-zram-generator; then
  sudo apt update -y
  sudo apt install -y systemd-zram-generator
fi

echo "[2/8] Remove old zram-tools to avoid conflicts"
sudo apt remove -y zram-tools 2>/dev/null || true

echo "[3/8] Stop all zram services and clean up completely"
sudo systemctl stop systemd-zram-setup@zram0.service 2>/dev/null || true
sudo systemctl stop systemd-zram-setup@zram1.service 2>/dev/null || true
sudo systemctl stop systemd-zram-setup@zram2.service 2>/dev/null || true
sudo systemctl stop systemd-zram-setup@zram3.service 2>/dev/null || true
sudo systemctl stop systemd-zram-setup@zram4.service 2>/dev/null || true
sudo systemctl stop systemd-zram-setup@zram5.service 2>/dev/null || true

# Swap off all zram devices
for dev in /dev/zram*; do
  if [ -b "$dev" ]; then
    echo "Swapping off $dev"
    sudo swapoff "$dev" 2>/dev/null || true
  fi
done

# Reset all zram devices
for i in {0..5}; do
  if [ -e "/sys/block/zram$i/reset" ]; then
    echo "Resetting zram$i"
    echo 1 | sudo tee "/sys/block/zram$i/reset" >/dev/null 2>&1 || true
  fi
done

# Unload zram module
sudo modprobe -r zram 2>/dev/null || true

echo "[4/8] Write correct zram-generator config"
sudo tee /etc/systemd/zram-generator.conf >/dev/null <<'EOF'
[zram0]
zram-fraction = 2.0
max-zram-size = 16G
compression-algorithm = zstd
swap-priority = 150
fs-type = swap
EOF

echo "[5/8] Reload systemd and test config"
sudo systemctl daemon-reload

# Test the config manually first
echo "Testing zram-generator config..."
if sudo /lib/systemd/system-generators/zram-generator --setup-device zram0; then
  echo "Config test successful"
else
  echo "Config test failed, trying alternative approach..."
  
  # Alternative: Create zram device manually
  sudo modprobe zram
  echo 1 | sudo tee /sys/block/zram0/reset >/dev/null
  echo 16G | sudo tee /sys/block/zram0/disksize >/dev/null
  echo zstd | sudo tee /sys/block/zram0/comp_algorithm >/dev/null
  sudo mkswap /dev/zram0
  sudo swapon -p 150 /dev/zram0
  echo "Manual zram setup completed"
fi

echo "[6/8] Start zram service"
if ! sudo systemctl start systemd-zram-setup@zram0.service; then
  echo "Service start failed, but manual setup may have worked"
fi

echo "[7/8] Ensure NVMe /swapfile active with priority 60"
sudo swapoff /swapfile 2>/dev/null || true
sudo swapon -p 60 /swapfile
sudo sed -i.bak -E 's#^(/swapfile\s+none\s+swap\s+).*#\1sw,pri=60 0 0#' /etc/fstab
grep -qE '^/swapfile\s+none\s+swap' /etc/fstab || echo '/swapfile none swap sw,pri=60 0 0' | sudo tee -a /etc/fstab >/dev/null

echo "[8/8] Persist kernel tunables and verify"
sudo sed -i -E 's/^vm.swappiness=.*/vm.swappiness=80/' /etc/sysctl.conf || true
sudo sed -i -E 's/^vm.vfs_cache_pressure=.*/vm.vfs_cache_pressure=50/' /etc/sysctl.conf || true
grep -q '^vm.swappiness=' /etc/sysctl.conf || echo 'vm.swappiness=80' | sudo tee -a /etc/sysctl.conf >/dev/null
grep -q '^vm.vfs_cache_pressure=' /etc/sysctl.conf || echo 'vm.vfs_cache_pressure=50' | sudo tee -a /etc/sysctl.conf >/dev/null
sudo sysctl -p

echo "=== FINAL VERIFICATION ==="
echo "=== swapon --show ==="; swapon --show || true
echo "=== /proc/swaps ==="; cat /proc/swaps || true
echo "=== free -h ==="; free -h || true
echo "=== zram devices ==="; ls -la /dev/zram* 2>/dev/null || echo "No zram devices"
echo "=== zram0 size ==="; cat /sys/block/zram0/disksize 2>/dev/null || echo "zram0 not found"
echo "Done. Target: /dev/zram0 ~14-16G prio 150, /swapfile 16G prio 60."
