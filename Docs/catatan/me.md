<!-- Jika nanti port-port itu hidup lagi, kemungkinan besar ada:
Service systemd baru di-enable kembali, atau
Proses manual dijalankan (python/Flask).
Cek cepat jika itu terjadi:
```bash
sudo ss -tulpn | grep -E ':5000|:5001|:5002|:8080' | cat
ps -eo pid,cmd | grep -E 'remote_camera_service|remote_gui_service|remote_access_controller|web_config_gui|run_web_gui|run_gui_client' | grep -v grep
sudo systemctl list-unit-files | grep -E '^(rvm|myrvm).*\\.service'
``` -->

anda jangan membuat file dari dalam docker ya. jika dilakukan maka akan ada masalah dengan permission file.

api_key_expires_at apakah auto generate? jika tolong jadikan 1 bulan.
kemudian ubah dokumentasi ini /home/my/MySuperApps/Docs/01_SERVER/Requirements

Jangan lupa ubah file-file yang sekiranya berhubungan dengan proses pengembangan sekarang. pada folder /home/my/MySuperApps/Docs/01_SERVER
ini adalah folder untuk dokumentasi pengembangan dari sudut pandang anda. anda ada Server.

---

Jadi status RVM itu ada 3.
Status: Aktif, Tidak Aktif, Maintenance 
Status Koneksi: Terhubung, Tidak Terhubung
Status API: Valid, Tidak Valid
====
1. Status Aktif Jika: current_load >=0 dan current_load <=100
Status Non Aktif Jika: current_load >=100 lalu muncul notifikasi (notifikasi belum di buat!)
Status Maintenance Jika: admin menekan action menu bar pada list RVM Maintenance akan mengubah status di basis data tabel reverse_vending_machine ke maintenance kemudian akan mengarah ke Halaman Maintenance Mode (Halaman belum di buat!)
2. Status Koneksi Aktif Jika: berhasil terhubung dengan IP dari RVM atau Jetson atau Mesin RVM contohnya: http://100.117.234.2:5000/api/health
jadi pengecekan melalui Pinging setiap beberapa detik dari si RVM atau si Jetson.
3. Status API Valid Jika: berhasil terhubung API Endpoint dari IP si RVM atau si Jetson atau Mesin RVM contohnya: http://100.117.234.2:5000/api/health
lakukan setiap beberapa detik juga.