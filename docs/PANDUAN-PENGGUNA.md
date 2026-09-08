# Panduan Pengguna Aplikasi e-Cuti Pegawai
**Inspektorat Kabupaten Trenggalek**

Aplikasi **e-Cuti Inspektorat** adalah sistem informasi manajemen cuti kepegawaian mandiri berbasis web yang dibangun sesuai regulasi **Peraturan Badan Kepegawaian Negara (Perka BKN) Nomor 24 Tahun 2017** (sebagaimana diubah dengan **Perka BKN Nomor 7 Tahun 2021**) dan **Peraturan Pemerintah Nomor 49 Tahun 2018** tentang Manajemen PPPK.

---

## Daftar Isi
1. [Akses & Autentikasi Sistem](#1-akses--autentikasi-sistem)
2. [Panduan Peran Pegawai](#2-panduan-peran-pegawai)
   - [2.1 Dashboard & Informasi Saldo Cuti](#21-dashboard--informasi-saldo-cuti)
   - [2.2 Formulir Pengajuan Cuti](#22-formulir-pengajuan-cuti)
   - [2.3 Cetak Dokumen Resmi PDF (Anak Lampiran 1.b)](#23-cetak-dokumen-resmi-pdf)
3. [Panduan Peran Atasan Langsung](#3-panduan-peran-atasan-langsung)
   - [3.1 Verifikasi & Pertimbangan Cuti](#31-verifikasi--pertimbangan-cuti)
4. [Panduan Peran Pejabat Yang Berwenang Memberikan Cuti (PyBMC)](#4-panduan-peran-pejabat-yang-berwenang-memberikan-cuti-pybmc)
   - [4.1 Keputusan Final & Ratifikasi](#41-keputusan-final--ratifikasi)
5. [Panduan Peran Admin Kepegawaian](#5-panduan-peran-admin-kepegawaian)
   - [5.1 Dashboard Administrator](#51-dashboard-administrator)
   - [5.2 Manajemen Data Pegawai & Unit Kerja](#52-manajemen-data-pegawai--unit-kerja)
   - [5.3 Master Pemetaan Atasan & Delegasi PyBMC](#53-master-pemetaan-atasan--delegasi-pybmc)
   - [5.4 Laporan Rekapitulasi & Ekspor Excel/PDF](#54-laporan-rekapitulasi--ekspor-excelpdf)
   - [5.5 Early Warning Saldo Cuti Hangus](#55-early-warning-saldo-cuti-hangus)

---

## 1. Akses & Autentikasi Sistem

Aplikasi dapat diakses melalui browser dengan alamat:
🌐 **`https://cuti.inspektorat.trenggalekkab.go.id`**

### Cara Masuk (Login):
1. **Email atau NIP Pegawai:** Masukkan **NIP 18 Digit** Anda (misal `199011272015031003`) atau **Alamat Email Resmi** Anda.
2. **Kata Sandi (Password):** Masukkan kata sandi akun Anda (default: `password`).
3. **Keamanan Captcha:** Tuliskan hasil angka perhitungan matematika yang muncul di layar (misal 4 + 2 = **6**).
4. Klik tombol **Masuk Ke Akun**.

> **Tips Keamanan:** Setelah berhasil masuk pertama kali, segera perbarui kata sandi Anda melalui menu **Profil -> Ubah Kata Sandi** di pojok kanan atas.

---

## 2. Panduan Peran Pegawai

### 2.1 Dashboard & Informasi Saldo Cuti
Setelah berhasil masuk, pegawai akan diarahkan ke halaman **Dashboard**. Halaman ini menampilkan:
* **Kartu Sisa Saldo Cuti Tahunan Real-Time:** Rincian jatah tahun berjalan (N), sisa tahun lalu (N-1), dan sisa 2 tahun lalu (N-2).
* **Status Pengajuan Cuti Terbaru:** Melacak apakah permohonan sedang menunggu verifikasi Atasan, menunggu PyBMC, atau telah diterbitkan surat izin cutinya.
* **Riwayat Cuti:** Daftar seluruh permohonan cuti yang pernah diajukan beserta status dan lampiran dokumen.

---

### 2.2 Formulir Pengajuan Cuti
Untuk mengajukan cuti baru, klik menu **Ajukan Cuti** pada navigasi atas.

#### Langkah Pengisian Formulir:
1. **Pilih Jenis Cuti:**
   - *Cuti Tahunan* (12 hari kerja/tahun).
   - *Cuti Sakit* (1 hari s.d >14 hari).
   - *Cuti Karena Alasan Penting* (Keluarga sakit keras/meninggal, menikah, bencana, dsb.).
   - *Cuti Melahirkan* (Kelahiran anak ke-1, 2, dan 3).
   - *Cuti Besar* (Masa kerja >= 5 tahun, atau ibadah haji pertama kali). *(Khusus PNS)*
   - *Cuti di Luar Tanggungan Negara / CLTN* (Masa kerja >= 5 tahun). *(Khusus PNS)*
2. **Tanggal Mulai & Tanggal Selesai:** Masukkan rentang tanggal pelaksanaan cuti. Sistem akan **menghitung otomatis jumlah hari kerja** dengan mengecualikan hari Sabtu, Minggu, dan Hari Libur Nasional.
3. **Alasan Cuti:** Jelaskan keperluan cuti secara jelas dan ringkas.
4. **Alamat Selama Menjalankan Cuti & Nomor HP Aktif:** Pastikan nomor telepon/WhatsApp aktif untuk keperluan koordinasi kedinasan darurat.
5. **Unggah Dokumen Lampiran:**
   - *Cuti Sakit:* Unggah Surat Keterangan Dokter (jika sakit >14 hari wajib dari dokter pemerintah/faskes pemerintah).
   - *Cuti Alasan Penting (Keluarga Sakit):* Unggah Surat Keterangan Rawat Inap Rumah Sakit.
   - *Cuti Alasan Penting (Bencana):* Unggah Surat Keterangan dari RT/Kelurahan setempat.
6. Klik **Kirim Pengajuan Cuti**. Pengajuan akan otomatis diteruskan ke Atasan Langsung Anda disertai notifikasi WhatsApp.

---

### 2.3 Cetak Dokumen Resmi PDF
Begitu pengajuan disetujui secara final oleh PyBMC:
1. Buka menu **Dashboard** atau klik detail pengajuan.
2. Klik tombol **Cetak Formulir PDF (Anak Lampiran 1.b)**.
3. Dokumen resmi berformat **Legal/F4** siap diunduh dan dicetak lengkap dengan data pegawai, pertimbangan atasan, nomor surat, dan tanda tangan keputusan pejabat.

---

## 3. Panduan Peran Atasan Langsung

Atasan Langsung (Kasubbag, Irban I s.d IV, Sekretaris) bertugas memeriksa permohonan staf di bawah unit kerjanya.

### 3.1 Verifikasi & Pertimbangan Cuti
1. Masuk ke menu **Persetujuan Atasan** (`/approval/atasan`).
2. Periksa daftar pengajuan yang berstatus **Menunggu Persetujuan Atasan**.
3. Klik tombol **Detail & Verifikasi** untuk melihat alasan, durasi hari kerja, dan dokumen pendukung.
4. Berikan aksi:
   - **Setujui (Disetujui):** Meneruskan permohonan ke Pejabat Yang Berwenang (PyBMC).
   - **Minta Revisi (Perubahan):** Mengembalikan form ke pegawai dengan catatan revisi (misal tanggal perlu disesuaikan).
   - **Tolak (Tidak Disetujui):** Menolak pengajuan dengan mencantumkan alasan kedinasan yang jelas.

---

## 4. Panduan Peran PyBMC (Pejabat Berwenang Memberikan Cuti)

PyBMC (Inspektur / Pejabat yang menerima delegasi wewenang SK Bupati) memegang wewenang keputusan hukum final.

### 4.1 Keputusan Final & Ratifikasi
1. Masuk ke menu **Persetujuan PyBMC** (`/approval/pejabat`).
2. Tinjau permohonan yang telah disetujui oleh Atasan Langsung.
3. Berikan keputusan:
   - **Setujui (Disetujui):** Menerbitkan nomor surat cuti resmi, memotong saldo cuti tahunan pegawai secara otomatis, dan mengirim notifikasi WhatsApp bahwa surat izin siap diunduh.
   - **Tangguhkan (Ditangguhkan):** Menunda pelaksanaan cuti karena beban tugas dinas mendesak. Hak cuti yang ditangguhkan dilindungi dan dapat dibawa penuh ke tahun berikutnya.
   - **Tolak (Tidak Disetujui):** Menolak permohonan secara permanen.
   - **Ratifikasi (Izin Sementara):** Mengesahkan izin darurat yang sebelumnya telah diberikan oleh pimpinan di tempat kerja.

---

## 5. Panduan Peran Admin Kepegawaian

Akun dengan peran **Admin Cuti** memiliki akses penuh untuk mengelola master data organisasi, monitoring, dan pelaporan.

### 5.1 Dashboard Administrator
Menampilkan metrik komprehensif organisasi: total 69 pegawai aktif, statistik pengajuan bulanan, dan status kepegawaian.

### 5.2 Manajemen Data Pegawai & Unit Kerja
Dapat diakses melalui menu **Manajemen Pegawai** dan **Unit Kerja**.
* **Tambah/Edit Pegawai:** Mengelola NIP, Nama Lengkap, Pangkat/Golongan, Jabatan, Unit Kerja, Jenis Pegawai (PNS / PPPK), dan Nomor HP WhatsApp.
* **Nonaktifkan Akun:** Menonaktifkan akses login untuk pegawai yang pensiun atau mutasi keluar dari Inspektorat.

### 5.3 Master Pemetaan Atasan & Delegasi PyBMC
Dapat diakses melalui menu **Data Master & Saldo -> Pemetaan Atasan** dan **Delegasi PyBMC**.
* Mengatur dan memperbarui siapa atasan langsung masing-masing pegawai saat terjadi promosi atau rotasi internal.
* Menginput Nomor SK Delegasi Wewenang Pemberian Cuti untuk jenis cuti tertentu per unit kerja.

### 5.4 Laporan Rekapitulasi & Ekspor Excel/PDF
Dapat diakses melalui menu **Laporan & Monitoring -> Rekapitulasi Cuti**.
* **Filter Fleksibel:** Menyaring data berdasarkan Unit Kerja, Jenis Cuti, Status Pengajuan, dan Rentang Periode Tanggal.
* **Ekspor Excel / CSV:** Mengunduh seluruh rincian riwayat pengajuan dalam format lembar kerja Excel siap olah.
* **Cetak PDF Resmi:** Menghasilkan laporan berformat PDF lanskap ukuran legal lengkap dengan tanda tangan Kepala Subbagian Umum dan Kepegawaian.

### 5.5 Early Warning Saldo Cuti Hangus
Dapat diakses melalui menu **Laporan & Monitoring -> Early Warning Saldo Hangus**.
* Menampilkan daftar pegawai yang masih memiliki sisa saldo N-2 (pasti hangus per 31 Desember) atau N-1 (terancam hangus).
* **Tombol Cepat Pengingat WhatsApp:** Mengirimkan pesan otomatis ke nomor WhatsApp pegawai terkait agar segera memanfaatkan hak cutinya sebelum akhir tahun.
