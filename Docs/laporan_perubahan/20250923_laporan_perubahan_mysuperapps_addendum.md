# 📎 Addendum Laporan Perubahan - MySuperApps (2025-09-23)

Dokumen ini menambahkan rincian tanpa mengubah laporan sebelumnya.

## Tambahan Rincian
- Konfirmasi alur registrasi RVM:
  - Pre-register di Admin → API key tampil di modal “RVM Created”.
  - RVM UI melakukan claim/update menggunakan API key (X-API-Key) ke endpoint self-APIs.
- Endpoint baru (aktif):
  - `POST /api/v2/rvm/self/claim`
  - `PATCH /api/v2/rvm/self/update`
- Kolom baru: `latitude`, `longitude` di tabel `reverse_vending_machines`.
- Rekomendasi peta (opsional di Admin): Mapbox (implementasi cepat) atau Leaflet+MapTiler+LocationIQ (alternatif open/low-cost).

## Instruksi Operasional Cepat
- Migrasi: `docker compose exec app php artisan migrate --force`
- `.env`: siapkan `MAPBOX_ACCESS_TOKEN` jika aktivasi peta admin diperlukan.

## Status
- Backend siap mendukung flow konfirmasi di UI RVM.
