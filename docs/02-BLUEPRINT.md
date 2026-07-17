# Technical Blueprint — Aplikasi e-Cuti Pegawai

Dokumen ini adalah pegangan teknis untuk AI coding agent yang akan membangun aplikasi. Baca bersamaan dengan `01-PRD.md` (kebutuhan bisnis) dan `03-DATABASE-SCHEMA.md` (skema detail).

---

## 1. Stack Teknologi

| Layer | Pilihan | Alasan |
|---|---|---|
| Backend | PHP 8.2+, Laravel 11 | Mature, ekosistem luas, cocok untuk skala instansi internal |
| Frontend | Blade + Alpine.js + Tailwind CSS | Tidak perlu build step SPA terpisah, cukup untuk kebutuhan ini |
| Database | MySQL 8 — **satu instance, satu database mandiri** milik aplikasi ini |
| Queue/Job | Laravel Queue (database driver) — untuk notifikasi WA & job tutup-tahun saldo |
| PDF generation | `barryvdh/laravel-dompdf` atau `spatie/laravel-pdf` — render surat dari template Blade |
| Notifikasi WA | WAHA (WhatsApp HTTP API) — dikonfigurasi via `.env`, independen |
| Auth | Laravel session-based auth (standar bawaan), tanpa custom UserProvider |
| Deployment | Container/subdomain sendiri, mis. `cuti.trenggalekkab.go.id` |

---

## 2. Database & Autentikasi — Mandiri (Standalone)

Aplikasi ini **berdiri sendiri** dengan satu database miliknya. Semua data — autentikasi, profil pegawai, struktur organisasi, hingga data cuti — tersimpan dalam satu database ini.

### 2.1 Satu Koneksi Database

```php
// config/database.php — hanya satu koneksi, tidak ada koneksi sekunder
'connections' => [
    'mysql' => [
        'driver'   => 'mysql',
        'host'     => env('DB_HOST', '127.0.0.1'),
        'database' => env('DB_DATABASE', 'ecuti_db'),
        'username' => env('DB_USERNAME'),
        'password' => env('DB_PASSWORD'),
        'charset'  => 'utf8mb4',
        'collation'=> 'utf8mb4_unicode_ci',
    ],
],
```

### 2.2 Autentikasi — Standard Laravel

Gunakan Laravel's built-in authentication. Tabel `users` dan `pegawai` dibangun dari nol (skema lengkap di `03-DATABASE-SCHEMA.md` §0). Tidak ada custom UserProvider.

```php
// Model User — koneksi default, standard Laravel Authenticatable
class User extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password', 'role', 'bisa_beri_izin_sementara'];

    // role: 'super_admin' | 'admin_cuti' | 'pegawai'
    // Wewenang sebagai Atasan Langsung / PyBMC ditentukan oleh mapping di
    // cuti_pemetaan_atasan & cuti_pemetaan_pejabat_berwenang, bukan kolom role ini.

    public function pegawai(): HasOne
    {
        return $this->hasOne(Pegawai::class);
    }
}
```

### 2.3 Data Pegawai — Diinput Manual oleh Admin

Data pegawai (NIP, jabatan, unit kerja, TMT CPNS, dll.) diinput langsung oleh Admin melalui antarmuka manajemen pegawai di aplikasi ini. Tidak ada sinkronisasi otomatis dengan sistem eksternal.

```php
class Pegawai extends Model
{
    // Koneksi default ('mysql') — satu database, tidak ada koneksi lain
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class);
    }
}
```

### 2.4 Variabel Environment yang Dibutuhkan

```env
# Database (satu koneksi)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecuti_db
DB_USERNAME=
DB_PASSWORD=

# Aplikasi
APP_NAME="e-Cuti Inspektorat"
APP_URL=https://cuti.trenggalekkab.go.id
SESSION_LIFETIME=480

# WhatsApp (WAHA)
WAHA_BASE_URL=https://waha.trenggalekkab.go.id
WAHA_SESSION=ecuti

# Storage lampiran — gunakan 'local' (private), BUKAN 'public'
FILESYSTEM_DISK=local
```

---

## 3. Struktur Folder Aplikasi (Laravel)

```
cuti-app/
├── app/
│   ├── Models/
│   │   ├── User.php                         # auth standar Laravel
│   │   ├── Pegawai.php                      # profil pegawai, one-to-one dengan User
│   │   ├── UnitKerja.php                    # master unit kerja (hierarkis)
│   │   ├── PengajuanCuti.php
│   │   ├── SaldoCutiTahunan.php
│   │   ├── JenisCuti.php
│   │   ├── ApprovalLog.php
│   │   ├── PemetaanAtasan.php
│   │   ├── PemetaanPejabatBerwenang.php
│   │   ├── CutiBersama.php
│   │   └── HariLibur.php
│   ├── Services/
│   │   ├── SaldoCutiService.php             # kalkulasi saldo & carry-over (+ semua skenario 05-CONTOH-HITUNG-SALDO.md)
│   │   ├── HariKerjaService.php             # hitung hari kerja exclude weekend + libur
│   │   ├── ValidasiPengajuanService.php     # validasi per jenis cuti (dokumen, masa kerja, dll.)
│   │   ├── ApprovalWorkflowService.php      # state machine status pengajuan
│   │   ├── SuratCutiPdfService.php          # generate PDF sesuai template Anak Lampiran
│   │   └── WhatsAppNotificationService.php  # wrapper ke WAHA API
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Pegawai/PengajuanCutiController.php
│   │   │   ├── Approval/AtasanController.php
│   │   │   ├── Approval/PejabatBerwenangController.php
│   │   │   ├── Admin/PegawaiController.php      # CRUD data pegawai & akun login
│   │   │   ├── Admin/UnitKerjaController.php    # CRUD unit kerja
│   │   │   ├── Admin/MasterDataController.php
│   │   │   ├── Admin/LaporanController.php
│   │   │   └── DashboardController.php
│   │   └── Middleware/
│   │       └── EnsureRole.php
│   └── Providers/
│       └── AppServiceProvider.php           # tidak ada custom UserProvider
├── database/
│   └── migrations/                          # unit_kerja, users, pegawai, cuti_* (semua di satu DB)
├── resources/views/
│   ├── pegawai/
│   ├── approval/
│   ├── admin/
│   └── pdf/                                 # template Blade untuk surat resmi (lampiran-1b, 1c, dst.)
├── config/
│   └── cuti-rules.php                       # konfigurasi aturan bisnis §4 PRD — satu-satunya tempat angka BKN
├── storage/
│   └── app/
│       └── (private — default)              # lampiran dokumen sensitif; JANGAN di storage/app/public
└── routes/
    └── web.php
```

---

## 4. Prinsip Desain Kode

- **Aturan bisnis dari Perka BKN harus hidup di satu tempat** (`config/cuti-rules.php` + `SaldoCutiService`), tidak tersebar di controller. Perubahan aturan cukup di satu titik.
- **State machine eksplisit** untuk status pengajuan, jangan pakai string bebas:
  `diajukan → menunggu_atasan → menunggu_pyBMC → disetujui → diterbitkan` (jalur normal)
  dengan cabang: `ditolak_atasan`, `ditolak_pyBMC`, `ditangguhkan`, `direvisi`, `izin_sementara_aktif → menunggu_ratifikasi → diratifikasi`.
- **Setiap transisi status wajib ditulis ke `cuti_approval_log`** (immutable, append-only) — jangan hanya update kolom `status`.
- **Validasi dokumen wajib per jenis cuti** didefinisikan sebagai data, bukan if-else panjang. Contoh struktur `config/cuti-rules.php`:

```php
return [
    'cuti_tahunan' => [
        'hak_tahunan_hari'         => 12,
        'carry_over_max_1_tahun'   => 18,   // total max saldo jika 1 tahun tidak pakai
        'carry_over_max_2_tahun'   => 24,   // total max saldo jika 2 tahun tidak pakai
        'sisa_max_dibawa'          => 6,    // max hari dari satu tahun yang bisa carry ke berikutnya
        'syarat_masa_kerja_bulan'  => 12,
        'dokumen_wajib'            => [],
    ],
    'cuti_besar' => [
        'syarat_masa_kerja_tahun'        => 5,
        'lama_maks_bulan'                => 3,
        'siklus_ulang_tahun'             => 5,
        'dokumen_wajib'                  => [],
        'pengecualian_syarat_masa_kerja' => ['ibadah_haji_pertama'],
    ],
    'cuti_sakit' => [
        'ambang_perlu_dokter_hari'            => 1,
        'ambang_perlu_dokter_pemerintah_hari' => 14,
        'lama_maks_tahun'                     => 1,
        'perpanjangan_maks_bulan'             => 6,
        'dokumen_wajib'                       => ['surat_keterangan_dokter'],
    ],
    'cuti_melahirkan' => [
        'lama_hari'       => 90,    // 3 bulan kalender
        'berlaku_anak_ke' => [1, 2, 3],
        'dokumen_wajib'   => [],
    ],
    'cuti_alasan_penting' => [
        'lama_maks_bulan'          => 1,
        'dokumen_wajib_per_alasan' => [
            'keluarga_sakit_keras'    => ['surat_rawat_inap'],
            'keluarga_meninggal'      => [],
            'menikah'                 => [],
            'istri_melahirkan_caesar' => ['surat_rawat_inap'],
            'bencana'                 => ['surat_keterangan_rt'],
        ],
    ],
    'cuti_bersama' => [
        'dikelola_admin'          => true,
        'mengurangi_cuti_tahunan' => false,
    ],
    'cltn' => [
        'syarat_masa_kerja_tahun' => 5,
        'lama_maks_tahun'         => 3,
        'perpanjangan_maks_tahun' => 1,
        'butuh_persetujuan_bkn'   => true,
        'dokumen_wajib'           => ['surat_pendukung_alasan'],
    ],
];
```

- **Keamanan file lampiran:** simpan di `storage/app/` (private default), akses hanya via route yang dilindungi middleware. **Jangan** simpan di `storage/app/public/` — lampiran berisi data kesehatan sensitif.
- **Hari kerja**, bukan hari kalender, dipakai untuk cuti tahunan — `HariKerjaService` wajib mengecualikan Sabtu/Minggu dan tabel `cuti_hari_libur`.

---

## 5. API/Route Sketch

```
GET  /dashboard                                → ringkasan saldo & status pegawai login
GET  /pengajuan/create                         → form pengajuan cuti baru
POST /pengajuan                                → submit (validasi saldo + dokumen)
GET  /pengajuan/{id}                           → detail pengajuan
GET  /pengajuan/{id}/pdf                       → download surat izin (hanya jika diterbitkan)

GET  /approval/atasan                          → antrian pengajuan menunggu approval atasan
POST /approval/atasan/{id}/setujui
POST /approval/atasan/{id}/tolak
POST /approval/atasan/{id}/minta-revisi

GET  /approval/pejabat                         → antrian pengajuan menunggu keputusan PyBMC
POST /approval/pejabat/{id}/setujui            → trigger generate PDF + potong saldo
POST /approval/pejabat/{id}/tangguhkan
POST /approval/pejabat/{id}/tolak

POST /pengajuan/{id}/izin-sementara            → jalur darurat §6.3 PRD
POST /pengajuan/{id}/ratifikasi                → PyBMC mengesahkan izin sementara

GET  /admin/pegawai                            → CRUD data pegawai & akun login
GET  /admin/unit-kerja                         → CRUD unit kerja (hierarkis)
GET  /admin/master-data/atasan                 → CRUD pemetaan atasan langsung
GET  /admin/master-data/pejabat-berwenang      → CRUD pemetaan PyBMC + lingkup delegasi
GET  /admin/master-data/cuti-bersama           → CRUD event cuti bersama
GET  /admin/master-data/hari-libur             → CRUD kalender hari libur
POST /admin/saldo/{pegawai}/koreksi            → penyesuaian manual saldo (wajib isi alasan)

GET  /admin/laporan/rekap                      → rekap per unit/periode, export Excel/PDF
GET  /admin/laporan/saldo-akan-hangus          → early warning carry-over akan hangus

GET  /dokumen/{id}/unduh                       → download lampiran (protected, cek hak akses)
```

---

## 6. Notifikasi WhatsApp (WAHA)

```php
class WhatsAppNotificationService
{
    public function __construct(
        private string $wahaBaseUrl,
        private string $session
    ) {}

    public function kirim(string $nomorTujuan, string $pesan): void
    {
        Http::post("{$this->wahaBaseUrl}/api/sendText", [
            'session' => $this->session,
            'chatId'  => $nomorTujuan . '@c.us',
            'text'    => $pesan,
        ]);
    }
}
```

Trigger pengiriman dilakukan lewat **Laravel Event/Listener** setiap transisi status — bukan dipanggil langsung dari controller, agar mudah menambah channel notifikasi lain (email, in-app) tanpa mengubah controller:

```
PengajuanDiajukan       → notif ke Atasan Langsung
DisetujuiAtasan         → notif ke PyBMC & Pegawai
DitolakAtasan           → notif ke Pegawai
DisetujuiPyBMC          → notif ke Pegawai & Atasan (+ link unduh PDF)
DitolakOrDitangguhkan   → notif ke Pegawai & Atasan
IzinSementaraDiberikan  → notif ke PyBMC resmi (untuk ratifikasi)
SaldoAkanHangusReminder → notif berkala (scheduled job) ke Pegawai
```

---

## 7. Testing & Validasi yang Wajib Ada

Karena ini aplikasi kepegawaian resmi, uji kasus berikut **wajib** ada di test suite:

1. Pegawai dengan sisa saldo 0 hari tidak bisa mengajukan cuti tahunan (hard block).
2. **Semua skenario A–G** di `05-CONTOH-HITUNG-SALDO.md` harus lulus — termasuk skenario carry-over kadaluarsa dan pembekuan jatah karena cuti besar.
3. Cuti besar menghapus hak cuti tahunan tahun berjalan (Skenario G di `05-CONTOH-HITUNG-SALDO.md`).
4. Pengajuan cuti sakit >14 hari ditolak jika dokumen yang diunggah bukan dari dokter pemerintah.
5. Status pengajuan tidak bisa "meloncat" (mis. langsung `diterbitkan` tanpa approval) — state machine menolak transisi ilegal.
6. Hari kerja yang dihitung antara dua tanggal benar mengecualikan Sabtu/Minggu/hari libur terdaftar.
7. Izin sementara yang belum diratifikasi tidak menghasilkan PDF surat resmi.

---

## 8. Catatan untuk AI Agent yang Mengeksekusi Build

- **Satu database, satu koneksi** — tidak ada koneksi `siadig` atau koneksi kedua apapun. Jika agent mulai menambahkan koneksi kedua, hentikan.
- Mulai dari migration (termasuk `unit_kerja`, `users`, `pegawai`), lalu model, lalu service layer, baru controller/view.
- Data pegawai diinput oleh Admin — pastikan ada antarmuka CRUD pegawai yang lengkap di panel admin (bukan hanya seed data).
- **Tulis unit test untuk semua skenario A–G di `05-CONTOH-HITUNG-SALDO.md`** sebelum menandai `SaldoCutiService` selesai — ini adalah logika paling kritis dan paling mudah salah implementasi.
- Ikuti prioritas rilis `01-PRD.md` §9 — MVP (cuti tahunan + cuti sakit) dulu.
- Gunakan `php artisan make:test` untuk setiap skenario di §7 sebelum menandai fitur selesai.
