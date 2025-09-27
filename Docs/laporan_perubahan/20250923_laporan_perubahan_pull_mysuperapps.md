# Laporan Perubahan — MySuperApps (pull)
Tanggal: 2025-09-23

## Ringkasan
Pull terbaru menambahkan controller self-registration untuk RVM, route API v2, serta migration latitude/longitude pada tabel RVM.

## Perubahan Utama
- MyRVM-Platform/app/Http/Controllers/Api/V2/RvmSelfController.php — endpoint untuk operasi sisi RVM
- MyRVM-Platform/database/migrations/2025_09_23_000001_add_latitude_longitude_to_reverse_vending_machines_table.php — kolom lat/long
- MyRVM-Platform/routes/api-v2.php — pendaftaran route
- Docs/laporan_perubahan/20250123_laporan_perubahan_mysuperapps.md — dokumentasi

## Fungsi & Dampak
- Mempermudah RVM melakukan self-registration dan update koordinat.
- Menambah informasi lokasi pada data RVM (lat/long) untuk peta/analytics.

## Yang Harus Dikerjakan
- Server: pastikan policy & validasi API Key untuk endpoint self-registration aktif [MyRVM-Platform].
- RVM: gunakan header `X-API-Key` saat register (`POST /api/v2/rvms`) [RVM].

## Penanggung Jawab
- MyRVM-Platform: implement route/controller/migration.
- RVM/Jetson: integrasi client.

## Quick Links
- API Reference (di repo RVM): `test-cv-yolo11-sam2-camera/myrvm-integration/docs/API_REFERENCE.md`
- Network Configuration (di repo RVM): `test-cv-yolo11-sam2-camera/myrvm-integration/docs/NETWORK_CONFIGURATION.md`
