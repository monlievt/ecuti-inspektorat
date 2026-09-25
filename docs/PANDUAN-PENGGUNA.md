# BUKU PANDUAN PENGGUNA APLIKASI e-CUTI
## Inspektorat Daerah Kabupaten Trenggalek

---

## DAFTAR ISI

1. [Tentang Aplikasi e-Cuti](#1-tentang-aplikasi-e-cuti)
2. [Dasar Hukum & Kebijakan Cuti ASN](#2-dasar-hukum--kebijakan-cuti-asn)
3. [Panduan Akses & Login Sistem](#3-panduan-akses--login-sistem)
4. [Panduan Pegawai (Pemohon Cuti)](#4-panduan-pegawai-pemohon-cuti)
   - 4.1 [Memahami Kartu Saldo Cuti (N, N-1, N-2)](#41-memahami-kartu-saldo-cuti-n-n-1-n-2)
   - 4.2 [Langkah Pengajuan Cuti Mandiri](#42-langkah-pengajuan-cuti-mandiri)
   - 4.3 [Persyaratan Dokumen Lampiran per Jenis Cuti](#43-persyaratan-dokumen-lampiran-per-jenis-cuti)
   - 4.4 [Memantau Status & Mengunduh Dokumen Resmi](#44-memantau-status--mengunduh-dokumen-resmi)
5. [Panduan Atasan Langsung (Verifikator)](#5-panduan-atasan-langsung-verifikator)
   - 5.1 [Pemeriksaan & Pemberian Pertimbangan](#51-pemeriksaan--pemberian-pertimbangan)
   - 5.2 [Pilihan Keputusan Atasan (Setujui / Revisi / Tolak)](#52-pilihan-keputusan-atasan)
6. [Panduan Pejabat Berwenang Memberikan Cuti (PyBMC)](#6-panduan-pejabat-berwenang-memberikan-cuti-pybmc)
   - 6.1 [Wewenang Persetujuan Final & Penerbitan Izin](#61-wewenang-persetujuan-final--penerbitan-izin)
   - 6.2 [Pilihan Keputusan PyBMC (Setujui / Tangguhkan / Tolak)](#62-pilihan-keputusan-pybmc)
   - 6.3 [Alur Ratifikasi Izin Sementara (Jalur Darurat)](#63-alur-ratifikasi-izin-sementara-jalur-darurat)
7. [Alur Khusus Pengajuan Cuti Pimpinan Tertinggi (Inspektur / Plt. Inspektur)](#7-alur-khusus-cuti-inspektur)
8. [Spesifikasi Dokumen Cetak Resmi PDF](#8-spesifikasi-dokumen-cetak-resmi-pdf)
   - 8.1 [Formulir Permintaan & Pemberian Cuti (Anak Lampiran 1.b BKN)](#81-formulir-anak-lampiran-1b-bkn)
   - 8.2 [Surat Izin Cuti Dinas Inspektorat](#82-surat-izin-cuti-dinas-inspektorat)
   - 8.3 [Surat Pengantar Permohonan Cuti ke Bupati Trenggalek](#83-surat-pengantar-ke-bupati)
9. [Panduan Administrator Kepegawaian (Admin Cuti)](#9-panduan-administrator-kepegawaian)
   - 9.1 [Manajemen Pegawai & Pangkat/Golongan](#91-manajemen-pegawai--pangkatgolongan)
   - 9.2 [Pemetaan Atasan Langsung & Delegasi PyBMC](#92-pemetaan-atasan-langsung--delegasi-pybmc)
   - 9.3 [Sinkronisasi Real-Time Google Spreadsheet](#93-sinkronisasi-real-time-google-spreadsheet)
   - 9.4 [Early Warning System Saldo Cuti Hangus](#94-early-warning-system-saldo-cuti-hangus)
   - 9.5 [Laporan & Rekapitulasi Cuti (Excel & PDF)](#95-laporan--rekapitulasi-cuti)
   - 9.6 [Pengaturan Integrasi (WhatsApp, Telegram Backup, Pejabat BKPSDM)](#96-pengaturan-integrasi)
10. [Aturan Perhitungan Saldo & Pergantian Tahun (Rollover)](#10-aturan-perhitungan-saldo--pergantian-tahun)
11. [Tanya Jawab & Troubleshooting Pra-Sosialisasi (FAQ)](#11-tanya-jawab--troubleshooting-pra-sosialisasi)

---

## 1. Tentang Aplikasi e-Cuti

Aplikasi **e-Cuti Inspektorat Daerah Kabupaten Trenggalek** adalah platform pelayanan kepegawaian mandiri (*employee self-service*) berbasis web yang dirancang untuk memodernisasi tata kelola administrasi cuti ASN (PNS & PPPK), mewujudkan proses tanpa kertas (*paperless*), perhitungan saldo cuti yang presisi dan transparan, serta integrasi pelaporan otomatis.

Aplikasi dapat diakses 24/7 melalui tautan resmi:  
🌐 **`https://cuti.inspektorat.trenggalekkab.go.id`**

---

## 2. Dasar Hukum & Kebijakan Cuti ASN

Pengembangan logika sistem dan hak cuti ASN dalam e-Cuti merujuk sepenuhnya pada:
1. **Undang-Undang Nomor 20 Tahun 2023** tentang Aparatur Sipil Negara.
2. **Peraturan Pemerintah Nomor 11 Tahun 2017** jo. **PP Nomor 17 Tahun 2020** tentang Manajemen Pegawai Negeri Sipil.
3. **Peraturan Pemerintah Nomor 49 Tahun 2018** tentang Manajemen Pegawai Pemerintah dengan Perjanjian Kerja (PPPK).
4. **Peraturan Badan Kepegawaian Negara (BKN) Nomor 24 Tahun 2017** tentang Tata Cara Pemberian Cuti Pegawai Negeri Sipil.
5. **Peraturan Badan Kepegawaian Negara (BKN) Nomor 7 Tahun 2021** tentang Perubahan atas Peraturan BKN Nomor 24 Tahun 2017.
6. **Surat Edaran Bupati Trenggalek** terkait pedoman administrasi kepegawaian di lingkungan Pemerintah Kabupaten Trenggalek.

---

## 3. Panduan Akses & Login Sistem

### Langkah Masuk Akun:
1. Buka browser (Google Chrome, Mozilla Firefox, Microsoft Edge, atau Safari) pada Laptop/PC maupun Smartphone.
2. Akses alamat: **`https://cuti.inspektorat.trenggalekkab.go.id`**.
3. Masukkan kredensial login:
   - **ID Pengguna / Email**: Masukkan **NIP 18 Digit** Anda (contoh: `198501012010011001`) atau **Alamat Email Resmi**.
   - **Kata Sandi (Password)**: Masukkan kata sandi default yang telah dibagikan admin saat sosialisasi.
   - **Pertanyaan Keamanan (Captcha)**: Ketikkan hasil perhitungan matematika sederhana yang tertera pada layar (misal `5 + 3 = 8`).
4. Klik tombol **Masuk Ke Akun**.

> 💡 **PENTING SETELAH LOGIN PERTAMA KALI:**  
> Sangat disarankan bagi setiap pegawai untuk segera mengganti kata sandi default melalui menu **Profil Akun** (ikon inisial di pojok kanan atas) demi menjaga privasi dan keamanan akun pribadi.

---

## 4. Panduan Pegawai (Pemohon Cuti)

### 4.1 Memahami Kartu Saldo Cuti (N, N-1, N-2)
Pada halaman beranda (**Dashboard**), setiap pegawai dapat memantau rincian saldo cuti tahunan berjalan secara transparan:
- **Jatah Tahun Ini (N)**: Hak cuti tahun berjalan (maksimal 12 hari kerja).
- **Sisa Tahun Lalu (N-1)**: Sisa cuti tahun sebelumnya yang terbawa ke tahun ini (maksimal 6 hari kerja).
- **Sisa 2 Tahun Lalu (N-2)**: Sisa cuti 2 tahun lalu yang wajib dihabiskan pada tahun ini sebelum tanggal 31 Desember (jika tidak digunakan akan hangus otomatis).
- **Total Saldo Aktif**: Jumlah keseluruhan saldo yang dapat diajukan saat ini.

### 4.2 Langkah Pengajuan Cuti Mandiri
1. Klik menu **Ajukan Cuti** pada bilah navigasi atas.
2. **Pilih Jenis Cuti**:
   - Cuti Tahunan
   - Cuti Besar *(Khusus PNS masa kerja minimal 5 tahun terus-menerus)*
   - Cuti Sakit
   - Cuti Melahirkan *(Kelahiran anak ke-1, ke-2, dan ke-3)*
   - Cuti Karena Alasan Penting
   - Cuti di Luar Tanggungan Negara (CLTN) *(Khusus PNS)*
3. **Pilih Tanggal Mulai dan Tanggal Selesai**:
   - Sistem secara cerdas **hanya menghitung hari kerja efektif** (Senin s.d Jumat).
   - Hari Sabtu, Minggu, dan **Hari Libur Nasional / Cuti Bersama resmi tidak dihitung memotong saldo cuti tahunan**.
4. **Isi Alasan Cuti**:
   - Tuliskan alasan pelaksanaan cuti secara jelas dan ringkas.
5. **Alamat & Nomor Telepon Selama Cuti**:
   - Cantumkan alamat domisili sementara dan nomor WhatsApp aktif yang dapat dihubungi untuk koordinasi kedinasan darurat.
6. **Unggah Dokumen Bukti Pendukung**:
   - Lampirkan berkas bukti sesuai ketentuan (format PDF, JPG, atau PNG maksimal 2MB).
7. Klik tombol **Kirim Pengajuan Cuti**.
   - Notifikasi otomatis akan dikirim ke WhatsApp Atasan Langsung untuk diverifikasi.

### 4.3 Persyaratan Dokumen Lampiran per Jenis Cuti
| Jenis Cuti | Dokumen Wajib yang Diunggah | Ketentuan Regulasi |
| :--- | :--- | :--- |
| **Cuti Sakit (1 - 14 hari)** | Surat Keterangan Sakit dari Dokter / Faskes | Boleh dokter klinik / puskesmas swasta / pemerintah |
| **Cuti Sakit (> 14 hari)** | Surat Keterangan Sakit Tim Penguji Kesehatan PNS / RS Pemerintah | Wajib faskes pemerintah / RSUD |
| **Cuti Alasan Penting (Keluarga Sakit Keras)** | Surat Keterangan Rawat Inap Rumah Sakit | Berlaku untuk orang tua, mertua, suami/istri, anak, kandung |
| **Cuti Alasan Penting (Keluarga Meninggal)** | Surat Kematian dari RS / Kelurahan / Desa | Untuk pengurusan jenazah / waris |
| **Cuti Alasan Penting (Menikah)** | Surat Pengantar KUA / Undangan Pernikahan | Pernikahan pertama bagi PNS/PPPK |
| **Cuti Melahirkan** | Surat Keterangan Perkiraan Lahir dari Dokter Kandungan / Bidan | Menjelaskan HPL (Hari Perkiraan Lahir) |
| **Cuti Besar (Ibadah Haji Pertama)** | Surat Penetapan Porsi / Keberangkatan Kemenag | Khusus ibadah haji yang pertama kali |

### 4.4 Memantau Status & Mengunduh Dokumen Resmi
Pegawai dapat memantau linimasa pengajuan pada menu **Riwayat Cuti**:
- 🟡 **Menunggu Persetujuan Atasan**: Berkas berada di meja verifikasi Atasan Langsung.
- 🔵 **Menunggu Persetujuan PyBMC**: Telah disetujui Atasan, menunggu penerbitan dari Inspektur / PyBMC.
- 🟠 **Perlu Revisi**: Atasan meminta perbaikan tanggal/alasan (klik tombol *Edit* untuk memperbaiki).
- 🔴 **Ditolak**: Pengajuan ditolak disertai alasan tertulis.
- 🟢 **Disetujui & Diterbitkan**: Surat izin cuti telah terbit resmi.
  - Tombol **Cetak Formulir BKN 1.b** aktif.
  - Tombol **Cetak Surat Izin Cuti Dinas** aktif.

---

## 5. Panduan Atasan Langsung (Verifikator)

Atasan Langsung (Kasubbag, Irban I s.d IV, Sekretaris) bertindak sebagai pemeriksa pertama kelayakan permohonan staf di unit kerjanya.

### 5.1 Pemeriksaan & Pemberian Pertimbangan
1. Begitu bawahan mengirimkan permohonan, Atasan Langsung menerima notifikasi WhatsApp.
2. Masuk ke aplikasi dan buka menu **Persetujuan Atasan** (`/approval/atasan`).
3. Klik tombol **Tinjau** pada pengajuan yang berstatus *Menunggu Persetujuan Atasan*.
4. Periksa alasan, kesesuaian hari kerja, riwayat sisa saldo staf, serta kelengkapan dokumen lampiran.

### 5.2 Pilihan Keputusan Atasan
- **Setujui**: Menyetujui usulan cuti dan meneruskannya ke meja PyBMC.
- **Minta Perubahan (Revisi)**: Memberikan instruksi kepada staf untuk mengubah durasi atau tanggal cuti (misal karena ada agenda audit mendesak).
- **Tolak**: Menolak permohonan dengan menyertakan alasan pertimbangan kedinasan yang jelas.

---

## 6. Panduan Pejabat Berwenang Memberikan Cuti (PyBMC)

PyBMC (Inspektur Kabupaten Trenggalek atau Pejabat yang ditunjuk secara sah) memegang wewenang penerbitan izin cuti final.

### 6.1 Wewenang Persetujuan Final & Penerbitan Izin
1. Masuk ke menu **Persetujuan PyBMC** (`/approval/pejabat`).
2. Tinjau permohonan yang telah memperoleh pertimbangan *"Disetujui"* dari Atasan Langsung.
3. Klik tombol **Beri Keputusan**.

### 6.2 Pilihan Keputusan PyBMC
- **Setujui (Disetujui)**:
  - Menerbitkan nomor surat keputusan resmi secara otomatis.
  - Memotong saldo cuti tahunan pegawai dari urutan tahun saldo tertua (N-2, lalu N-1, lalu N).
  - Mengirim notifikasi WhatsApp ke pegawai bahwa izin cuti telah sah dan berkas PDF siap diunduh.
  - Merekam data transaksi secara real-time ke **Google Spreadsheet**.
- **Tangguhkan (Ditangguhkan)**:
  - Digunakan apabila kepentingan dinas tidak memungkinkan pegawai cuti saat ini.
  - **Keuntungan Hukum**: Hak cuti yang ditangguhkan oleh PyBMC dijamin regulasi untuk **dapat digunakan penuh di tahun berikutnya** dan tidak hangus.
- **Tolak (Tidak Disetujui)**:
  - Menolak permohonan cuti secara permanen.

### 6.3 Alur Ratifikasi Izin Sementara (Jalur Darurat)
Untuk kondisi darurat di mana pegawai harus segera meninggalkan tugas sebelum surat izin resmi selesai ditandatangani:
1. Pimpinan yang memiliki wewenang (Sekretaris / Irban) dapat mengaktifkan **Izin Darurat Sementara**.
2. Pegawai terlindungi secara absensi kedinasan.
3. Setelah kondisi normal, PyBMC memproses **Ratifikasi** untuk meresmikan izin darurat menjadi Surat Izin Cuti definitif.

---

## 7. Alur Khusus Pengajuan Cuti Inspektur / Plt. Inspektur

Berdasarkan regulasi kepegawaian, Pimpinan Tertinggi Instansi (Inspektur Daerah) tidak menandatangani surat izin cutinya sendiri, melainkan memohon kepada **Bupati Trenggalek**:

1. Saat Inspektur / Plt. Inspektur mengajukan permohonan cuti di sistem:
   - Alur verifikasi internal otomatis selesai (*auto-validated*).
2. Sistem tidak menerbitkan Surat Izin Cuti Dinas, melainkan menerbitkan:  
   👉 **Surat Pengantar Permohonan Cuti kepada Bupati Trenggalek cq. Kepala BKPSDM**.
3. Dokumen pengantar dilengkapi nomor klasifikasi resmi instansi, identitas pimpinan, alasan cuti, dan lampiran Formulir BKN Anak Lampiran 1.b siap kirim ke BKPSDM.

---

## 8. Spesifikasi Dokumen Cetak Resmi PDF

Aplikasi e-Cuti menghasilkan 3 dokumen resmi berstandar tata naskah dinas Pemerintah Kabupaten Trenggalek:

### 8.1 Formulir Anak Lampiran 1.b BKN
- **Ukuran Kertas**: A4 Portrait.
- **Format**: Sesuai template baku Anak Lampiran 1.b Perka BKN No. 24 Tahun 2017.
- **Spesifikasi Khusus**:
  - **Tabel III (Alasan Cuti)**: Berjarak vertikal lega setara 4 baris enter.
  - **Bagian V (Catatan Cuti)**: Menggunakan kalimat standar *"Sudah diambil ... hari"*.
  - **Tujuan Surat**:
    - *Cuti Tahunan & Sakit*: Ditujukan kepada **Inspektur / Plt. Inspektur Kabupaten Trenggalek**.
    - *Cuti Lainnya (Besar, Melahirkan, Alasan Penting, CLTN)*: Ditujukan kepada **Kepala Badan Kepegawaian dan Pengembangan Sumber Daya Manusia Kabupaten Trenggalek**.

### 8.2 Surat Izin Cuti Dinas Inspektorat
- **Ukuran Kertas**: F4 / Folio (215 mm x 330 mm) Portrait.
- **Tipografi**: **Arial ukuran 12pt** dengan spasi rapi.
- **Kop Surat**:
  - Logo Resmi Pemkab Trenggalek proporsional (lebar `85px`).
  - Alamat Kantor: *Jl. KH. Wachid Hasyim No. 5 Ngantru, Kec. Trenggalek, Kabupaten Trenggalek, Jawa Timur 66311*.
  - Telepon: *(0355) 791444* | Email: *inspektorat@trenggalekkab.go.id*.
- **Format Penomoran Surat**:
  - Format klasifikasi kearsipan: **`{kode_klasifikasi}/     /406.008/{tahun}`**.
  - Kode klasifikasi otomatis: `800.1.11.4` (Tahunan), `800.1.11.2` (Sakit), `800.1.11.3` (Melahirkan), `800.1.11.5` (Alasan Penting), `800.1.11.6` (Besar), `800.1.11.7` (CLTN).

### 8.3 Surat Pengantar Permohonan Cuti ke Bupati
- Khusus digunakan untuk permohonan cuti Inspektur Daerah kepada Bupati Trenggalek cq. Kepala BKPSDM dengan tipografi dan kop naskah dinas resmi.

---

## 9. Panduan Administrator Kepegawaian

### 9.1 Manajemen Pegawai & Pangkat/Golongan
- Akses menu **Manajemen Pegawai** untuk menambah, mengedit, atau menonaktifkan pegawai yang mutasi/pensiun.
- Format Pangkat dan Golongan Ruang telah distandarisasi otomatis menjadi **Title Case** (contoh: `IV/b - Pembina Tingkat I`), menjaga estetika database dan cetakan.

### 9.2 Pemetaan Atasan Langsung & Delegasi PyBMC
- Akses menu **Data Master & Saldo -> Pemetaan Atasan** untuk mengatur relasi staf dengan atasan langsung di tiap subbagian/irban.
- Akses menu **Delegasi PyBMC** untuk mencatat pendelegasian wewenang sesuai Surat Keputusan (SK).

### 9.3 Sinkronisasi Real-Time Google Spreadsheet
Aplikasi dilengkapi fitur integrasi cloud spreadsheet tanpa Google Cloud Console yang rumit:
- **Sheet 1 (`Rekap Pengajuan Cuti`)**: Setiap ada cuti yang disetujui, ditolak, atau dibatalkan, data terisi otomatis ke spreadsheet detik itu juga.
- **Sheet 2 (`Master Pegawai & Saldo`)**: Berisi seluruh master 69 pegawai beserta rincian jatah N, sisa N, sisa N-1, sisa N-2, cuti terpakai, dan sisa aktif.
- **Tombol Sinkron Massal**: Di menu **Pengaturan -> Google Spreadsheet**, admin cukup klik tombol *"Sinkron Master Data"* untuk memperbarui lembar kerja spreadsheet secara menyeluruh.

### 9.4 Early Warning System Saldo Cuti Hangus
- Buka menu **Laporan & Monitoring -> Early Warning Saldo Hangus**.
- Menampilkan daftar pegawai yang memiliki sisa saldo N-2 (pasti hangus jika tidak dipakai tahun ini).
- Dilengkapi tombol cepat **Kirim Notifikasi WhatsApp** untuk mengingatkan pegawai yang bersangkutan secara personal.

### 9.5 Laporan & Rekapitulasi Cuti
- Filter fleksibel berdasarkan unit kerja, rentang tanggal, jenis cuti, dan status.
- Ekspor lembar kerja **Microsoft Excel / CSV**.
- Cetak **Laporan PDF Lanskap** resmi lengkap dengan tanda tangan pengesahan Kasubbag Kepegawaian.

### 9.6 Pengaturan Integrasi
Dikelola melalui menu **Pengaturan**:
- **WhatsApp Gateway (WAHA)**: Kredensial endpoint untuk pengiriman pesan otomatis.
- **Telegram Backup**: Bot Token & Chat ID untuk penerimaan file backup database otomatis harian (`.sql.gz`).
- **Google reCAPTCHA**: Konfigurasi keamanan login.
- **Profil Instansi & Pejabat BKPSDM**: Pengaturan nama pejabat BKPSDM, NIP, pangkat, dan jabatan untuk penandatanganan dokumen cuti selain tahunan dan sakit.

---

## 10. Aturan Perhitungan Saldo & Pergantian Tahun (Rollover)

Sistem mengadopsi rumus otomatis sesuai ketentuan Pasal 9 s.d 11 Perka BKN No. 24/2017:

1. **Jatah Cuti Tahunan (Tahun N)**:  
   Diberikan sebesar **12 hari kerja** per 1 Januari bagi PNS/PPPK yang telah bekerja minimal 1 tahun terus-menerus.
2. **Carry-Over Cuti N-1**:  
   Sisa hak cuti tahun N-1 dapat dibawa ke tahun berjalan maksimal **6 hari kerja**.
3. **Penyimpanan Cuti N-2**:  
   Sisa cuti dari 2 tahun lalu yang belum digunakan dapat digabung dengan jatah tahun berjalan, dengan ketentuan **harus dihabiskan pada tahun berjalan**.
4. **Kadaluarsa Saldo (Hangus)**:  
   Tepat tanggal 31 Desember pukul 23:59:59, sisa saldo N-2 yang tidak terpakai akan **dihanguskan otomatis** oleh sistem, sisa saldo N bergeser menjadi N-1 (maks. 6 hari), dan sistem mengisikan jatah 12 hari untuk tahun baru.
5. **Perintah Rollover Otomatis**:  
   Dapat dijalankan kapan saja oleh admin server melalui CLI:  
   `php artisan cuti:rollover-tahunan {tahun}`

---

## 11. Tanya Jawab & Troubleshooting Pra-Sosialisasi (FAQ)

### Q1: Bagaimana jika pegawai lupa kata sandinya?
> **Jawaban:** Pegawai dapat melapor ke Admin Kepegawaian. Admin dapat mereset kata sandi pegawai melalui menu *Manajemen Pegawai -> Reset Password*, atau Admin Server dapat mereset massal secara aman via terminal: `php artisan user:reset-passwords`.

### Q2: Mengapa hari libur nasional tidak memotong saldo cuti saya?
> **Jawaban:** Sesuai regulasi Perka BKN, perhitungan durasi cuti hanya menghitung **hari kerja aktif instansi**. Tanggal merah dan cuti bersama yang ditetapkan keputusan Presiden secara otomatis dikecualikan oleh sistem kalkulator hari kerja.

### Q3: Siapa yang menandatangani permohonan Cuti Besar atau Cuti Melahirkan?
> **Jawaban:** Sesuai tata naskah Pemkab Trenggalek, Cuti Tahunan dan Cuti Sakit diputuskan oleh **Inspektur Daerah**. Sedangkan Cuti Besar, Cuti Melahirkan, Cuti Alasan Penting, dan CLTN ditujukan kepada **Kepala BKPSDM Kabupaten Trenggalek**, dan sistem otomatis menyesuaikan tujuan serta pihak penandatangan pada dokumen formulir BKN 1.b.

### Q4: Apakah pegawai dapat membatalkan pengajuan cuti yang sudah disetujui?
> **Jawaban:** Pegawai dapat mengajukan pembatalan cuti atau melapor ke admin kepegawaian untuk proses *Recall / Pengembalian Saldo* sebelum masa cuti tersebut berjalan. Saldo cuti yang telah dipotong akan otomatis dikembalikan ke akun pegawai.

### Q5: Apakah data di Google Spreadsheet aman?
> **Jawaban:** Sangat aman. Integrasi menggunakan tautan Webhook terenkripsi satu arah langsung dari server Inspektorat ke Google Apps Script Spreadsheet resmi instansi, tanpa mengekspos kata sandi atau database keluar.

---
*Dokumen ini diterbitkan oleh Subbagian Umum dan Kepegawaian, Inspektorat Daerah Kabupaten Trenggalek sebagai panduan resmi implementasi sistem e-Cuti.*
