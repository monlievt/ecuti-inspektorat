# Database Schema — Aplikasi e-Cuti Pegawai

Semua tabel di bawah ini berada dalam **satu database milik aplikasi ini** (koneksi default `mysql`). Tidak ada ketergantungan pada database eksternal — seluruh relasi antar tabel menggunakan foreign key constraint standar MySQL dalam satu database.

---

## 0. Tabel Inti — Organisasi, Akun, & Profil Pegawai

### 0.1 `unit_kerja` (master unit/bagian organisasi — hierarkis)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| kode | varchar(30) unique | mis. `IRBAN-I`, `SEKRETARIAT`, `SUB-KEPEG` |
| nama | varchar(150) | mis. "Inspektur Pembantu Wilayah I" |
| parent_id | bigint nullable FK → unit_kerja.id | untuk hierarki sub-bagian; null = level teratas |
| aktif | boolean default true | |
| created_at, updated_at | timestamp | |

### 0.2 `users` (akun login)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| name | varchar(100) | nama tampil (bisa diisi otomatis dari `pegawai.nama_lengkap`) |
| email | varchar(150) unique | dipakai sebagai username login |
| email_verified_at | timestamp nullable | |
| password | varchar(255) | bcrypt hash |
| role | enum | `super_admin`, `admin_cuti`, `pegawai` |
| bisa_beri_izin_sementara | boolean default false | flag khusus untuk jalur darurat §6.3 PRD (pejabat tertinggi di tempat kerja yang bisa memberi izin sementara — bisa berbeda dari PyBMC resmi) |
| remember_token | varchar(100) nullable | |
| created_at, updated_at | timestamp | |

> **Catatan role:** wewenang sebagai Atasan Langsung dan PyBMC **tidak** ditentukan oleh kolom `role` — melainkan oleh pemetaan di `cuti_pemetaan_atasan` dan `cuti_pemetaan_pejabat_berwenang`. Seorang pegawai bisa sekaligus menjadi atasan bagi pegawai lain dan tetap ber-role `pegawai`.

### 0.3 `pegawai` (profil & data kepegawaian — one-to-one dengan `users`)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| user_id | bigint unique FK → users.id | |
| nip | varchar(18) unique | Nomor Induk Pegawai (18 digit) |
| nip_lama | varchar(9) nullable | NIP lama 9 digit untuk pegawai lama |
| nama_lengkap | varchar(150) | |
| jenis_kelamin | enum | `L`, `P` |
| tmt_cpns | date | Tanggal Mulai Tugas CPNS — dasar hitung masa kerja ≥ 1/5 tahun untuk syarat cuti |
| tmt_pns | date nullable | Tanggal diangkat PNS (relevan untuk cuti besar & CLTN) |
| pangkat_golongan | varchar(30) | mis. `III/b`, `IV/a` |
| jabatan | varchar(150) | |
| unit_kerja_id | bigint FK → unit_kerja.id | |
| jenis_pegawai | enum | `PNS`, `CPNS`, `PPPK` |
| nomor_hp | varchar(20) nullable | untuk notifikasi WhatsApp — konfirmasi format nomor WA aktif |
| alamat | text nullable | |
| foto | varchar(255) nullable | path ke foto pegawai (di storage/app/private) |
| aktif | boolean default true | false jika pegawai sudah tidak aktif/pensiun |
| created_at, updated_at | timestamp | |

---

## 1. `cuti_jenis` (master jenis cuti — bisa juga full-config di file, tapi disarankan tetap ada tabel agar bisa tampil di dropdown & diaudit)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| kode | varchar(30) unique | `tahunan`, `besar`, `sakit`, `melahirkan`, `alasan_penting`, `bersama`, `cltn` |
| nama | varchar(100) | "Cuti Tahunan", dst |
| deskripsi | text nullable | |
| aktif | boolean default true | |

---

## 2. `cuti_saldo_tahunan` (saldo cuti tahunan per pegawai per tahun)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| pegawai_id | bigint FK → pegawai.id | |
| tahun | year | tahun saldo |
| jatah_tahun_berjalan | tinyint default 12 | |
| carry_over_n1 | tinyint default 0 | sisa dari tahun N-1 (max 6 hari — lihat `05-CONTOH-HITUNG-SALDO.md`) |
| carry_over_n2 | tinyint default 0 | sisa dari tahun N-2 (max 6 hari — hangus di akhir tahun ini jika tidak dipakai) |
| tambahan_cuti_bersama | tinyint default 0 | dari pengecualian cuti bersama §4.1 PRD; **tidak carry-over** ke tahun berikutnya |
| terpakai | tinyint default 0 | akumulasi hari yang sudah dipotong dari pengajuan disetujui |
| jatah_dibekukan | boolean default false | diset true jika cuti besar/CLTN disetujui tahun ini — jatah_tahun_berjalan tidak bisa dipakai lagi (carry-over dari tahun sebelumnya tetap bisa dipakai) |
| ditangguhkan | boolean default false | flag jika PyBMC menangguhkan cuti tahunan tahun ini karena kepentingan dinas |
| created_at, updated_at | timestamp | |

Index: unique (`pegawai_id`, `tahun`).

> Kolom `sisa` **tidak disimpan langsung** — selalu dihitung real-time oleh `SaldoCutiService::hitungSisa()`:
> `sisa = jatah_tahun_berjalan + carry_over_n1 + carry_over_n2 + tambahan_cuti_bersama - terpakai`
> Lihat `05-CONTOH-HITUNG-SALDO.md` untuk aturan deduction dan algoritma year-end.

---

## 3. `cuti_saldo_koreksi` (log penyesuaian manual saldo — audit trail wajib)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| pegawai_id | bigint FK → pegawai.id | |
| tahun | year | |
| jenis_koreksi | enum | `tambah`, `kurang` |
| jumlah_hari | tinyint | |
| alasan | text | wajib diisi — termasuk untuk input saldo awal migrasi data historis |
| dikoreksi_oleh | bigint FK → users.id | admin yang melakukan koreksi |
| created_at | timestamp | tidak ada `updated_at` — tabel ini immutable |

---

## 4. `cuti_pengajuan` (tabel inti — satu baris per pengajuan cuti)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| nomor_pengajuan | varchar(50) unique | auto-generated, format dikonfigurasi Admin |
| pegawai_id | bigint FK → pegawai.id | pemohon |
| jenis_cuti_id | bigint FK → cuti_jenis.id | |
| alasan | text | |
| alasan_kategori | varchar(50) nullable | khusus cuti alasan penting: `keluarga_sakit_keras`, `menikah`, dst |
| tanggal_mulai | date | |
| tanggal_selesai | date | |
| jumlah_hari_kerja | smallint | dihitung otomatis, exclude weekend & libur (kecuali melahirkan/besar/CLTN yang pakai hari kalender) |
| satuan_hari | enum | `hari_kerja`, `hari_kalender` |
| alamat_selama_cuti | varchar(255) nullable | |
| telp_selama_cuti | varchar(30) nullable | |
| status | enum | lihat state machine di §5 |
| dibuat_via | enum | `normal`, `izin_sementara` |
| created_at, updated_at | timestamp | |

---

## 5. State Machine `status` pada `cuti_pengajuan`

```
diajukan
  → menunggu_atasan   (default setelah submit & lolos validasi otomatis)

menunggu_atasan
  → disetujui_atasan  → menunggu_pyBMC
  → ditolak_atasan    (final; pegawai bisa ajukan ulang sebagai pengajuan baru)
  → direvisi          (dikembalikan ke pegawai untuk diedit)

menunggu_pyBMC
  → disetujui_pyBMC   → diterbitkan  (setelah PDF & potong saldo berhasil)
  → ditangguhkan_pyBMC (final untuk periode ini; simpan tanggal_boleh_ajukan_ulang)
  → ditolak_pyBMC     (final)

izin_sementara_aktif  (jalur darurat — lihat §6.3 PRD)
  → menunggu_ratifikasi
  → diratifikasi      → diterbitkan
  → ditolak_ratifikasi (kasus jarang; eskalasi manual, sistem hanya menandai)

diterbitkan
  → dipanggil_kembali (§6.4 PRD — sisa hari cuti dikembalikan ke saldo)
```

---

## 6. `cuti_approval_log` (append-only — jejak audit setiap transisi status)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| pengajuan_id | bigint FK → cuti_pengajuan.id | |
| status_sebelum | varchar(50) | |
| status_sesudah | varchar(50) | |
| aktor_id | bigint FK → users.id | user yang melakukan aksi (atasan/pyBMC/admin/sistem) |
| peran_aktor | varchar(50) | `atasan_langsung`, `pyBMC`, `admin`, `sistem` |
| catatan | text nullable | wajib diisi untuk aksi tolak/tangguhkan/revisi |
| created_at | timestamp | tidak ada `updated_at` — tabel ini **immutable** |

---

## 7. `cuti_dokumen` (lampiran per pengajuan)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| pengajuan_id | bigint FK → cuti_pengajuan.id | |
| jenis_dokumen | varchar(50) | `surat_dokter`, `surat_rawat_inap`, `surat_keterangan_rt`, `jadwal_haji`, `surat_pendukung_cltn`, dll |
| kategori_dokter | enum nullable | `pns` / `faskes_pemerintah` / `swasta` — khusus validasi cuti sakit > 14 hari (§4.3 PRD) |
| path_file | varchar(255) | path di `storage/app/` (private) |
| uploaded_at | timestamp | |

---

## 8. `cuti_pemetaan_atasan` (siapa atasan langsung siapa — dikelola admin, bukan diasumsikan dari hierarki unit kerja)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| pegawai_id | bigint FK → pegawai.id | bawahan |
| atasan_id | bigint FK → pegawai.id | atasan langsung |
| berlaku_mulai | date | |
| berlaku_sampai | date nullable | null = masih berlaku |

---

## 9. `cuti_pemetaan_pejabat_berwenang` (delegasi PyBMC per unit kerja & per jenis cuti — sesuai Anak Lampiran 1.a)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| unit_kerja_id | bigint FK → unit_kerja.id | atau bisa per individu: `pegawai_id` nullable jika delegasi individual |
| pejabat_id | bigint FK → pegawai.id | pegawai yang didelegasikan wewenang PyBMC |
| jenis_cuti_id | bigint FK → cuti_jenis.id | delegasi bisa dibatasi hanya jenis cuti tertentu (mis. hanya Tahunan & Sakit) |
| nomor_sk_delegasi | varchar(100) nullable | nomor SK pendelegasian wewenang |
| berlaku_mulai | date | |
| berlaku_sampai | date nullable | |

---

## 10. `cuti_bersama` (event cuti bersama yang ditetapkan Presiden)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| tanggal | date | |
| keterangan | varchar(255) | mis. "Cuti Bersama Idul Fitri 1447 H" |
| nomor_keppres | varchar(100) nullable | |

---

## 11. `cuti_bersama_pengecualian` (pegawai yang tidak diberi cuti bersama karena piket/jaga)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| cuti_bersama_id | bigint FK → cuti_bersama.id | |
| pegawai_id | bigint FK → pegawai.id | |
| keterangan | varchar(255) nullable | mis. "tugas piket lebaran" |

> Setelah dicatat di sini, `SaldoCutiService::tambahTambahanCutiBersama()` menambahkan 1 hari ke `cuti_saldo_tahunan.tambahan_cuti_bersama` pegawai bersangkutan (§4.1 PRD). Tambahan ini **tidak carry-over** ke tahun berikutnya.

---

## 12. `cuti_hari_libur` (kalender hari libur nasional untuk kalkulasi hari kerja)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| tanggal | date unique | |
| keterangan | varchar(255) | mis. "Hari Raya Idul Fitri 1447 H" |

---

## 13. `cuti_luar_tanggungan_negara` (data spesifik CLTN — one-to-one dengan `cuti_pengajuan` berjenis CLTN)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| pengajuan_id | bigint FK unique → cuti_pengajuan.id | |
| status_bkn | enum | `menunggu_diajukan_ke_bkn`, `menunggu_jawaban_bkn`, `disetujui_bkn`, `ditolak_bkn` |
| nomor_surat_ke_bkn | varchar(100) nullable | |
| tanggal_surat_ke_bkn | date nullable | |
| dokumen_persetujuan_bkn_path | varchar(255) nullable | scan surat balasan BKN (di storage/app private) |
| tanggal_lapor_diri | date nullable | setelah CLTN selesai |
| status_pengaktifan_kembali | enum nullable | `belum`, `diajukan`, `disetujui`, `tidak_ada_lowongan` |

---

## 14. `cuti_surat_terbit` (rekap surat yang sudah digenerate — untuk reprint & penomoran)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| pengajuan_id | bigint FK unique → cuti_pengajuan.id | |
| nomor_surat | varchar(100) unique | format dikonfirmasi ke Admin Inspektorat (Perka BKN tidak mengatur format ini) |
| ditandatangani_oleh | bigint FK → pegawai.id | pejabat yang menandatangani |
| tanggal_terbit | date | |
| path_pdf | varchar(255) | path PDF di storage/app private |

---

## Catatan Migrasi Data Historis

Jika Inspektorat ingin memasukkan riwayat cuti manual dari tahun-tahun sebelumnya (supaya perhitungan carry-over di tahun pertama pakai sistem ini akurat), gunakan `cuti_saldo_koreksi` untuk mencatat **saldo awal migrasi** per pegawai. **Jangan** membuat data pengajuan fiktif di `cuti_pengajuan` — itu akan mencemari approval log yang seharusnya hanya mencerminkan proses nyata di sistem.

Sepakati terlebih dahulu dengan Admin kepegawaian: berapa tahun ke belakang data historis yang perlu diinput, dan siapa yang bertanggung jawab menginputnya sebelum go-live.
