# Build Brief untuk AI Coding Agent

Salin/tempel isi berikut sebagai prompt awal ke AI coding agent (mis. Claude Code) untuk memulai proyek. Empat dokumen lain di folder ini (`01-PRD.md`, `02-BLUEPRINT.md`, `03-DATABASE-SCHEMA.md`, `05-CONTOH-HITUNG-SALDO.md`) adalah rujukan detail — beri agent akses ke semuanya sebagai context.

---

## Prompt

Saya ingin membangun aplikasi **e-Cuti Pegawai** untuk Inspektorat Kabupaten Trenggalek menggunakan **Laravel 11 + Blade + Tailwind + Alpine.js + MySQL**. Aplikasi ini berdiri **mandiri (standalone)** dengan database sendiri — tidak ada koneksi atau ketergantungan pada database aplikasi lain.

Spesifikasi mengikuti dokumen berikut:

- `01-PRD.md` — kebutuhan bisnis & aturan cuti sesuai Perka BKN No. 24 Tahun 2017.
- `02-BLUEPRINT.md` — arsitektur teknis. Satu koneksi database, tidak ada koneksi sekunder.
- `03-DATABASE-SCHEMA.md` — skema tabel lengkap yang harus dijadikan dasar migration, **termasuk** tabel `unit_kerja`, `users`, dan `pegawai` yang dibangun dari nol.
- `05-CONTOH-HITUNG-SALDO.md` — contoh perhitungan carry-over step-by-step; jadikan ini dasar unit test untuk `SaldoCutiService`.

**Urutan pengerjaan yang saya minta (jangan loncat urutan):**

1. Setup project Laravel baru dengan **satu** koneksi database (`mysql`) sesuai §2 `02-BLUEPRINT.md`. Buat `.env.example` yang mencantumkan semua variabel yang dibutuhkan (termasuk `WAHA_BASE_URL`).
2. Buat semua migration sesuai `03-DATABASE-SCHEMA.md`. Tabel `unit_kerja`, `users`, `pegawai` tanpa prefix. Semua tabel cuti gunakan prefix `cuti_` secara konsisten.
3. Buat semua Model beserta relasinya. Tidak ada model yang menunjuk ke koneksi selain `mysql` (default).
4. Bangun service layer dulu sebelum controller/view:
   - `HariKerjaService` — hitung hari kerja exclude weekend + tabel hari libur.
   - `SaldoCutiService` — kalkulasi saldo & carry-over sesuai §4.1 PRD. **Wajib tulis unit test untuk semua skenario A–G di `05-CONTOH-HITUNG-SALDO.md` sebelum lanjut.**
   - `ValidasiPengajuanService` — validasi syarat & dokumen wajib per jenis cuti, berbasis `config/cuti-rules.php`.
   - `ApprovalWorkflowService` — state machine; tulis test yang memastikan transisi ilegal ditolak.
5. Implementasikan **MVP** sesuai `01-PRD.md` §9: Cuti Tahunan + Cuti Sakit, alur approval 2 tingkat lengkap sampai generate PDF surat izin.
6. Setelah MVP lulus semua test di `02-BLUEPRINT.md` §7, lanjutkan ke 5 jenis cuti lainnya.
7. Notifikasi WhatsApp via WAHA dikerjakan paling akhir. Buat `WhatsAppNotificationService` dengan interface yang jelas tapi boleh di-mock sampai saya berikan endpoint WAHA yang aktif.

**Batasan yang wajib dipatuhi:**
- **Tidak ada koneksi ke database lain.** Semua data (pegawai, unit kerja, dll.) diinput manual oleh Admin melalui antarmuka aplikasi ini.
- Jangan hardcode angka aturan cuti (12 hari, 3 bulan, dll.) di controller — semua lewat `config/cuti-rules.php`.
- Setiap perubahan status pengajuan wajib tercatat di `cuti_approval_log` (immutable, append-only).
- Lampiran dokumen sensitif **wajib** disimpan di `storage/app/private/` — bukan di public.
- Ikuti prioritas rilis PRD §9 — bangun MVP dulu, jangan semua fitur sekaligus.

Mulai dari langkah 1. Setelah setup selesai, tunjukkan struktur folder yang dihasilkan sebelum lanjut ke migration.

---

## Tips Penggunaan

- Setelah MVP jadi, minta agent membuat seed data dummy (5–10 pegawai dari berbagai unit kerja, beberapa pengajuan di berbagai status) untuk kemudahan uji manual di browser.
- Untuk template PDF surat izin, sediakan foto/scan formulir asli (Anak Lampiran 1.b Perka BKN 24/2017) sebagai referensi visual — ini membantu hasil PDF terasa resmi.
- **Konfirmasikan format nomor surat** resmi Inspektorat ke Admin kepegawaian sebelum agent men-hardcode format nomor surat — Perka BKN tidak mengatur ini, setiap instansi berbeda.
- Sebelum go-live, sepakati berapa tahun riwayat cuti manual yang perlu dimigrasi. Gunakan fitur "Koreksi Saldo" (`cuti_saldo_koreksi`) untuk input saldo awal — **jangan** buat pengajuan fiktif di `cuti_pengajuan` untuk merepresentasikan histori.
