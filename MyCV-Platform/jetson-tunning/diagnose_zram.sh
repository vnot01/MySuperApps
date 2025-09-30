#!/usr/bin/env bash
set -euo pipefail

echo "=== ZRAM DIAGNOSTIC ==="
echo "1. Check current zram devices:"
ls -la /dev/zram* 2>/dev/null || echo "No zram devices found"

echo -e "\n2. Check zram module:"
lsmod | grep zram || echo "zram module not loaded"

echo -e "\n3. Check current swap:"
swapon --show || echo "No swap active"

echo -e "\n4. Check zram-generator config:"
cat /etc/systemd/zram-generator.conf 2>/dev/null || echo "No config found"

echo -e "\n5. Check systemd service status:"
systemctl status systemd-zram-setup@zram0.service || echo "Service not found"

echo -e "\n6. Check zram-generator logs:"
journalctl -u systemd-zram-setup@zram0.service --no-pager -n 20 || echo "No logs found"

echo -e "\n7. Check if zram0 exists in sysfs:"
ls -la /sys/block/zram0/ 2>/dev/null || echo "zram0 not in sysfs"

echo -e "\n8. Check zram-generator binary:"
which zram-generator || echo "zram-generator not found"
ls -la /lib/systemd/system-generators/zram-generator 2>/dev/null || echo "zram-generator binary not found"

echo -e "\n9. Test zram-generator manually:"
sudo /lib/systemd/system-generators/zram-generator --setup-device zram0 || echo "Manual test failed"
