# Matriks & Skenario Pengetesan Aplikasi e-Cuti
**Inspektorat Kabupaten Trenggalek**

Dokumen ini memuat matriks skenario pengujian komprehensif (*End-to-End Test Plan & Scenario Matrix*) untuk memvalidasi seluruh fungsionalitas, alur persetujuan, batas toleransi, dan kepatuhan terhadap regulasi **Perka BKN No. 24/2017**, **Perka BKN No. 7/2021**, serta **PP No. 49/2018**.

---

## 1. Modul Autentikasi & Akses Akun

| ID Skenario | Deskripsi Kasus Uji | Langkah Pengujian | Data Uji | Hasil yang Diharapkan |
|---|---|---|---|---|
| **AUTH-01** | Login sukses menggunakan Email | 1. Buka `/login`<br>2. Masukkan email, password, dan jawaban captcha yang benar<br>3. Klik Masuk | `admin@cuti.test` / `password` | Berhasil masuk ke Dashboard sesuai perannya. |
| **AUTH-02** | Login sukses menggunakan NIP | 1. Masukkan NIP 18 digit pegawai, password, dan captcha benar<br>2. Klik Masuk | `199011272015031003` / `password` | Berhasil masuk ke Dashboard Pegawai. |
| **AUTH-03** | Login gagal: Password Salah | Masukkan email terdaftar dengan password salah | Password: `wrongpassword` | Muncul kotak peringatan merah: *"Kata sandi yang Anda masukkan salah..."* |
| **AUTH-04** | Login gagal: NIP/Email Tidak Ada | Masukkan NIP fiktif yang tidak ada di database | NIP: `199999999999999999` | Muncul pesan: *"Akun dengan Email atau NIP '...' tidak ditemukan..."* |
| **AUTH-05** | Login gagal: Captcha Salah | Masukkan email & password benar, tapi jawaban matematika salah | Captcha: `999` | Muncul pesan: *"Jawaban perhitungan matematika salah..."* dan soal di-refresh. |
| **AUTH-06** | Ubah Password Mandiri | 1. Buka menu Ubah Password<br>2. Masukkan password lama dan password baru >= 8 karakter<br>3. Simpan | Baru: `P@ssw0rdBaru123` | Password berhasil diperbarui dan dapat digunakan untuk login berikutnya. |

---

## 2. Modul Saldo Cuti Tahunan & Carry-Over (BKN Rule)

| ID Skenario | Deskripsi Kasus Uji | Langkah & Aturan Uji | Kondisi Saldo Awal | Hasil yang Diharapkan |
|---|---|---|---|---|
| **SALDO-01** | Urutan Konsumsi Saldo (FIFO) | Pegawai mengambil cuti 8 hari kerja | N=12, N-1=6, N-2=3 | Sistem memotong N-2 (3 hari) -> lalu N-1 (5 hari). Sisa saldo: N-2=0, N-1=1, N=12 (Total sisa = 13 hari). |
| **SALDO-02** | Hard-Block: Saldo Tidak Mencukupi | Pegawai memiliki total saldo 5 hari, tetapi mengajukan cuti 7 hari | Total Saldo = 5 hari | Form ditolak sistem dengan pesan: *"Saldo cuti tahunan tidak mencukupi. Anda mengajukan 7 hari..."* |
| **SALDO-03** | Penangguhan Cuti (*Suspension*) | Cuti tahunan pegawai ditangguhkan oleh PyBMC karena tugas mendesak | Sisa N=12 ditangguhkan | Saldo tahun berjalan ditandai `ditangguhkan=true`. Saat rollover tahun baru, saldo N dapat dibawa penuh 12 hari (total hak N+1 menjadi 24 hari). |
| **SALDO-04** | Kompensasi Piket Cuti Bersama | Pegawai ditugaskan piket saat cuti bersama nasional (2 hari) | Jatah N=12 | Admin menginput pengecualian cuti bersama -> Saldo tahun berjalan bertambah menjadi 14 hari (hanya berlaku di tahun berjalan). |
| **SALDO-05** | Rollover Tutup Tahun Otomatis | Jalankan artisan command: `php artisan cuti:rollover-saldo` | PNS dengan sisa N=8, N-1=4, N-2=2 | Sisa N-2 lama (2 hari) hangus 100%, N-1 menjadi N-2 (4 hari), sisa N menjadi N-1 (maks 6 hari), dan dibuka jatah N baru 12 hari (Total hak = 12+6+4=22 hari). |

---

## 3. Modul Pengajuan & Validasi Bisnis 7 Jenis Cuti

| ID Skenario | Jenis Cuti | Kasus / Kondisi Pengujian | Dokumen Lampiran | Hasil Validasi Sistem |
|---|---|---|---|---|
| **CUTI-01** | **Cuti Tahunan** | PNS masa kerja >1 tahun mengajukan 3 hari kerja | Tanpa lampiran | **Lolos Validasi** -> Diteruskan ke Atasan Langsung. |
| **CUTI-02** | **Cuti Tahunan** | Masa kerja <1 tahun (<12 bulan) | - | **Ditolak Otomatis** dengan pesan: *"Masa kerja belum mencukupi minimal 12 bulan..."* |
| **CUTI-03** | **Cuti Sakit** | Sakit 1 hari kerja | Tanpa surat PyBMC | Cukup persetujuan Atasan Langsung. |
| **CUTI-04** | **Cuti Sakit** | Sakit 3 hari kerja tanpa melampirkan surat dokter | Kosong | **Ditolak** dengan pesan: *"Dokumen lampiran wajib 'surat_keterangan_dokter' belum diunggah."* |
| **CUTI-05** | **Cuti Sakit** | Sakit 20 hari kerja (>14 hari) dengan surat dokter klinik swasta | Surat dokter swasta | **Ditolak** dengan pesan: *"Cuti sakit lebih dari 14 hari memerlukan surat keterangan dari dokter pemerintah / faskes pemerintah."* |
| **CUTI-06** | **Cuti Melahirkan** | Kelahiran anak ke-1, 2, atau 3 (durasi 90 hari kalender) | - | **Lolos Validasi**. |
| **CUTI-07** | **Cuti Melahirkan** | Pengajuan cuti melahirkan untuk kelahiran anak ke-4 | - | **Ditolak** dengan pesan: *"Cuti melahirkan hanya berlaku untuk anak ke-1 s.d 3. Untuk anak ke-4 dst silakan gunakan Cuti Besar."* |
| **CUTI-08** | **Cuti Alasan Penting** | Keluarga inti sakit keras tanpa surat rawat inap | Kosong | **Ditolak** dengan pesan: *"Dokumen lampiran wajib 'surat_rawat_inap' belum diunggah."* |
| **CUTI-09** | **Cuti Besar** | Masa kerja PNS <5 tahun untuk alasan umum | - | **Ditolak** (Masa kerja belum mencapai 5 tahun). |
| **CUTI-10** | **Cuti Besar** | Masa kerja PNS <5 tahun khusus **Ibadah Haji Pertama Kali** | Surat porsi haji | **Lolos Validasi** (Dikecualikan dari syarat 5 tahun sesuai Perka BKN 24/2017). |

---

## 4. Modul Pembatasan Khusus PPPK vs PNS (PP No. 49/2018)

| ID Skenario | Deskripsi Kasus Uji | Data Akun Penguji | Tindakan Pengujian | Hasil yang Diharapkan |
|---|---|---|---|---|
| **PPPK-01** | PPPK dilarang mengajukan Cuti Besar | Akun PPPK (mis. Supriyadi / Apriliyan) | Pilih Cuti Besar pada formulir | Opsi Cuti Besar berstatus disabled. Jika dipaksa submit, sistem memblokir dengan rujukan PP 49/2018. |
| **PPPK-02** | PPPK dilarang mengajukan CLTN | Akun PPPK | Pilih CLTN pada formulir | Opsi CLTN berstatus disabled dan ditolak oleh backend. |
| **PPPK-03** | PPPK berhak Cuti Tahunan | Akun PPPK | Ajukan Cuti Tahunan (mis. 2 hari) | Pengajuan berhasil dikirim dan diverifikasi secara normal. |
| **PPPK-04** | Saldo PPPK Hangus saat Tutup Tahun | Akun PPPK dengan sisa cuti 4 hari | Eksekusi `cuti:rollover-saldo` | Sisa 4 hari hangus 100% (Carry-over N-1=0, N-2=0). Dibuka jatah baru 12 hari. |

---

## 5. Modul Alur Persetujuan & State Machine (Approval Workflow)

| ID Skenario | Status Awal | Aksi & Peran Penguji | Status Akhir | Efek Samping Sistem |
|---|---|---|---|---|
| **WF-01** | `diajukan` | Atasan Langsung klik **Setujui** | `disetujui_atasan` | Log approval tercatat, permohonan berpindah ke antrian PyBMC, kirim WA ke PyBMC. |
| **WF-02** | `diajukan` | Atasan Langsung klik **Minta Revisi** | `revisi` | Form terbuka kembali untuk diedit pegawai dengan catatan dari atasan. |
| **WF-03** | `diajukan` | Atasan Langsung klik **Tolak** | `ditolak_atasan` | Status selesai (ditolak), saldo tidak dipotong, notifikasi penolakan ke pegawai. |
| **WF-04** | `disetujui_atasan` | PyBMC klik **Setujui** | `disetujui_pybmc` | **Nomor surat izin otomatis terbit**, **saldo cuti tahunan terpotong**, dokumen PDF siap cetak. |
| **WF-05** | `disetujui_atasan` | PyBMC klik **Tangguhkan** | `ditangguhkan` | Cuti ditunda, flag penangguhan aktif untuk perlindungan saldo carry-over di tahun depan. |
| **WF-06** | Non-Atasan | Pegawai biasa mencoba approve pengajuan rekan kerja via URL langsung | `403 Forbidden` | Sistem memblokir akses ilegal melalui middleware otorisasi pemetaan atasan. |

---

## 6. Modul Jalur Darurat (Izin Sementara & Ratifikasi)

| ID Skenario | Kondisi Darurat | Tindakan Pengujian | Hasil Alur Sistem |
|---|---|---|---|
| **EMG-01** | Musibah keluarga sakit keras mendadak | Pegawai memicu toggle **Izin Sementara** -> disetujui Pejabat Tertinggi di tempat kerja saat itu | Status langsung aktif `izin_sementara_berjalan` tanpa menunggu antrian normal PyBMC. |
| **EMG-02** | Ratifikasi Izin Sementara oleh PyBMC | PyBMC memeriksa izin sementara yang sedang berjalan -> klik **Ratifikasi Keputusan** | Status berubah menjadi `disetujui_pybmc`, nomor SK resmi terbit mengesahkan izin sementara yang telah berjalan. |

---

## 7. Modul Dokumen Resmi PDF & Keamanan Berkas

| ID Skenario | Item Pengujian | Langkah Pengujian | Kriteria Keberhasilan |
|---|---|---|---|
| **DOC-01** | Format Formulir BKN Anak Lampiran 1.b | Unduh PDF pengajuan yang telah disetujui PyBMC | PDF berukuran Legal/F4 dengan struktur baku 8 tabel: Data Pegawai, Jenis Cuti, Alasan, Durasi, Catatan Cuti (N/N-1/N-2), Alamat, Pertimbangan Atasan, dan Keputusan PyBMC. |
| **DOC-02** | Keamanan Dokumen Lampiran Medis (*Private Storage*) | Akses URL berkas lampiran tanpa sesi login | Sistem memblokir dengan `403/401 Unauthorized`. File hanya dapat diunduh oleh Pegawai ybs, Atasannya, PyBMC, dan Admin Kepegawaian. |

---

## 8. Modul Rekapitulasi Laporan & Monitoring Administrator

| ID Skenario | Fitur yang Diuji | Langkah Pengujian | Kriteria Keberhasilan |
|---|---|---|---|
| **RPT-01** | Filter Rekapitulasi Multi-Kriteria | Filter berdasarkan Unit Kerja "Inspektur Pembantu I" + Jenis "Cuti Tahunan" + Bulan Berjalan | Menampilkan data yang tepat sesuai filter beserta kartu ringkasan KPI. |
| **RPT-02** | Ekspor Lembar Kerja Excel / CSV | Klik tombol **Ekspor Excel / CSV** | Berkas `.csv` terunduh dengan encoding UTF-8 BOM yang rapi saat dibuka langsung di Microsoft Excel. |
| **RPT-03** | Cetak Rekapitulasi Format PDF | Klik tombol **Cetak PDF Resmi** | Menghasilkan dokumen cetak lanskap legal dengan kops surat resmi Inspektorat dan ttd Kasubbag Kepegawaian. |
| **RPT-04** | Early Warning & WhatsApp Reminder | Buka menu Early Warning -> Klik **Kirim Pengingat WA** pada pegawai yang memiliki sisa saldo N-2 | Pesan pengingat resmi otomatis terkirim via gateway WAHA ke nomor WhatsApp pegawai terkait. |
