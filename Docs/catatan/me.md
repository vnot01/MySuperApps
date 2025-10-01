<!-- Jika nanti port-port itu hidup lagi, kemungkinan besar ada:
Service systemd baru di-enable kembali, atau
Proses manual dijalankan (python/Flask).
Cek cepat jika itu terjadi:
```bash
sudo ss -tulpn | grep -E ':5000|:5001|:5002|:8080' | cat
ps -eo pid,cmd | grep -E 'remote_camera_service|remote_gui_service|remote_access_controller|web_config_gui|run_web_gui|run_gui_client' | grep -v grep
sudo systemctl list-unit-files | grep -E '^(rvm|myrvm).*\\.service'
``` -->

hardware_info => camera_info => usb_cameras
hasilnya masih empty atau null
# Hardware Info => Camera Info:
1. install v4l-utils ``sudo apt install v4l-utils``
```bash
(venv) my@orin1:~/MySuperApps/MyCV-Platform/direct$ ls /dev/video*
/dev/video0  /dev/video1
(venv) my@orin1:~/MySuperApps/MyCV-Platform/direct$ v4l2-ctl --list-devices
NVIDIA Tegra Video Input Device (platform:tegra-camrtc-ca):
        /dev/media0

Integrated_Webcam_HD: Integrate (usb-3610000.usb-2.4):
        /dev/video0
        /dev/video1
        /dev/media1

(venv) my@orin1:~/MySuperApps/MyCV-Platform/direct$ v4l2-ctl -d /dev/video0 --list-formats-ext
ioctl: VIDIOC_ENUM_FMT
        Type: Video Capture

        [0]: 'MJPG' (Motion-JPEG, compressed)
                Size: Discrete 1280x720
                        Interval: Discrete 0.033s (30.000 fps)
                Size: Discrete 640x480
                        Interval: Discrete 0.040s (25.000 fps)
        [1]: 'YUYV' (YUYV 4:2:2)
                Size: Discrete 1280x720
                        Interval: Discrete 0.100s (10.000 fps)
                Size: Discrete 640x480
                        Interval: Discrete 0.040s (25.000 fps)
(venv) my@orin1:~/MySuperApps/MyCV-Platform/direct$ v4l2-ctl -d /dev/video1 --list-formats-ext
ioctl: VIDIOC_ENUM_FMT
        Type: Video Capture
```