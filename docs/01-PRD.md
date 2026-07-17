# PRD — Aplikasi e-Cuti Pegawai (Inspektorat Kabupaten Trenggalek)

Status: Draft v1.0
Dasar hukum: Peraturan Badan Kepegawaian Negara (Perka BKN) Nomor 24 Tahun 2017 tentang Tata Cara Pemberian Cuti Pegawai Negeri Sipil, sebagaimana diubah dengan Perka BKN Nomor 7 Tahun 2021.
Aplikasi terkait (bukan bagian sistem ini, tapi berbagi data pegawai): SIADIG (github.com/monlievt/siadig-improved) — sistem administrasi digital Inspektorat.

---

## 1. Latar Belakang & Tujuan

Inspektorat Kabupaten Trenggalek saat ini mengelola pengajuan cuti pegawai secara manual (formulir kertas, tanda tangan berjenjang). Ini menimbulkan masalah yang lazim ditemukan di instansi lain (lihat riset banding di §3):

- Pegawai tidak tahu sisa cuti tahunannya secara real-time.
- Staf kepegawaian mencatat ulang secara manual dari formulir kertas → rawan salah hitung sisa cuti.
- Surat izin cuti sering baru ditandatangani setelah pegawai selesai menjalani cuti (proses persetujuan lambat).
- Tidak ada kontrol otomatis yang mencegah pegawai mengajukan cuti melebihi hak yang tersisa.

**Tujuan aplikasi:**
1. Digitalisasi penuh alur pengajuan → verifikasi atasan langsung → persetujuan pejabat berwenang → penerbitan surat izin cuti.
2. Perhitungan otomatis sisa hak cuti tahunan (termasuk carry-over sesuai aturan BKN) sehingga pengajuan yang melebihi hak otomatis ditolak/diberi peringatan.
3. Riwayat dan jejak audit (siapa mengajukan, siapa menyetujui, kapan) untuk setiap jenis cuti.
4. Surat izin cuti yang bisa dicetak/diunduh sebagai PDF resmi begitu disetujui.
5. Notifikasi WhatsApp di setiap perubahan status (dipakai dari infrastruktur yang sama dengan SIADIG: WAHA self-hosted).

**Di luar cakupan (out of scope) v1:**
- Integrasi otomatis dengan payroll/gaji.
- Integrasi dengan SIASN/MyASN milik BKN (bisa jadi fase 2, sifatnya pelaporan manual dulu).
- Perhitungan tunjangan kinerja terpotong akibat cuti (mengikuti aturan instansi terpisah).

---

## 2. Keputusan Arsitektur: Aplikasi Mandiri dengan Database Sendiri

Aplikasi ini **dibangun sebagai codebase & deployment mandiri (standalone)** dengan database miliknya sendiri. Tidak ada ketergantungan pada database atau skema aplikasi lain.

Konsekuensi untuk PRD:
- Modul manajemen pegawai (CRUD data pegawai, jabatan, unit kerja) **dibangun di dalam aplikasi ini** dan dikelola oleh Admin. Data pegawai diinput langsung ke sistem ini.
- Autentikasi menggunakan sistem login mandiri berbasis tabel `users` milik aplikasi ini — tidak berbagi session atau akun dengan sistem lain. Detail teknis di `02-BLUEPRINT.md`.

---

## 3. Hasil Riset Banding (Existing Solutions)

Sebelum menulis requirement, berikut pola yang konsisten muncul di berbagai aplikasi e-cuti ASN pemerintah daerah di Indonesia (Kab. Pati, Kab. Buleleng, Kab. Bangka Barat, Kota Samarinda, dll):

| Pola yang ditemukan | Implikasi untuk desain kita |
|---|---|
| E-Cuti sering terintegrasi dengan aplikasi e-Presensi/e-Office yang sudah ada, bukan berdiri sendiri total | Selaras dengan keputusan kita: berbagi database pegawai dengan SIADIG |
| Sistem otomatis menolak pengajuan cuti tahunan jika sisa cuti sudah habis | **Wajib** — jadi validasi hard-block, bukan cuma warning |
| Alur baku: pegawai ajukan → atasan langsung memberi pertimbangan/rekomendasi → pejabat yang berwenang memberi keputusan final | Alur approval 2 tingkat minimal (lihat §6) |
| Ada peran "operator/admin cuti" yang menentukan pejabat mana yang harus memproses & memantau permohonan yang masuk | Role "Admin Cuti / Verifikator" terpisah dari "Atasan Langsung" dan "Pejabat Berwenang" |
| Dashboard menampilkan: jumlah cuti tahunan, sisa cuti, status permohonan (diajukan/disetujui/ditolak/proses) | Jadi requirement dashboard wajib, lihat §7.1 |
| Formulir & surat keputusan bisa dicetak/diunduh mandiri setelah ditandatangani pejabat berwenang | Jadi requirement generate PDF, lihat §7.6 |
| Masalah umum yang coba diatasi: surat cuti baru ditandatangani setelah pegawai selesai cuti (proses manual lambat) | Jadi requirement: notifikasi real-time + SLA approval yang terlihat di dashboard admin |

---

## 4. Jenis Cuti & Aturan Bisnis (sumber: Perka BKN 24/2017)

Semua angka di bawah ini adalah **aturan bisnis yang harus di-hardcode sebagai konfigurasi**, bukan ditulis bebas di kode — supaya mudah diaudit dan disesuaikan bila ada revisi peraturan (mis. Perka BKN 7/2021).

### 4.1 Cuti Tahunan
- Syarat: PNS/CPNS sudah bekerja terus-menerus ≥ 1 tahun.
- Hak: **12 hari kerja/tahun**.
- Minimal pengajuan: 1 hari kerja.
- **Carry-over:**
  - Tidak dipakai 1 tahun → bisa dipakai tahun berikutnya, maksimum **18 hari kerja** (termasuk jatah tahun berjalan).
  - Tidak dipakai 2 tahun berturut-turut → maksimum **24 hari kerja** di tahun berikutnya.
  - Sisa cuti tahun sebelumnya yang boleg dibawa maksimum **6 hari kerja** dari 1 tahun ke tahun berikutnya (lihat lampiran contoh perhitungan di dokumen sumber).
  - Cuti tahunan bisa ditambah hari kalender (maks 12 hari kalender) jika dipakai di lokasi terpencil/sulit transportasi.
  - Cuti tahunan bisa **ditangguhkan** oleh pejabat berwenang maks 1 tahun karena kepentingan dinas mendesak; jika ditangguhkan, hak tahun berikutnya jadi maks 24 hari kerja.
  - Cuti bersama yang ditetapkan Presiden **tidak mengurangi** hak cuti tahunan; PNS yang karena tugas jaga/piket tidak diberi cuti bersama, hak cuti tahunannya ditambah sejumlah hari cuti bersama yang tidak diberikan (hanya berlaku di tahun berjalan).
- **Implikasi teknis:** perlu tabel/kalkulasi saldo cuti tahunan per-pegawai per-tahun dengan komponen: jatah tahun berjalan (12), carry-over tahun N-1, carry-over tahun N-2 (kadaluarsa setelahnya), penambahan dari cuti bersama yang tidak diberikan. Lihat detail rumus di `03-DATABASE-SCHEMA.md`.

### 4.2 Cuti Besar
- Syarat: bekerja terus-menerus ≥ 5 tahun (dikecualikan untuk ibadah haji pertama kali meski masa kerja < 5 tahun).
- Hak: maksimum **3 bulan**.
- Menggunakan cuti besar → **tidak berhak cuti tahunan** di tahun bersangkutan (kecuali sisa cuti tahunan tahun sebelumnya yang belum kadaluarsa, itu tetap boleh dipakai).
- Jika cuti besar dipakai kurang dari 3 bulan, sisanya **hangus** (tidak bisa disambung nanti).
- Siklus berikutnya: 5 tahun sejak akhir cuti besar sebelumnya (bukan sejak pengajuan).
- Bisa ditangguhkan maks 1 tahun karena kepentingan dinas mendesak (kecuali untuk kepentingan ibadah/agama, tidak boleh ditangguhkan).
- Untuk kelahiran anak ke-4 dan seterusnya, cuti besar dipakai sebagai pengganti cuti melahirkan dengan ketentuan khusus (tidak bisa ditangguhkan, tidak perlu syarat 5 tahun masa kerja, lama = lama cuti melahirkan).

### 4.3 Cuti Sakit
- Sakit 1 hari: cukup surat keterangan sakit ke atasan langsung + surat keterangan dokter (tidak perlu ke Pejabat Berwenang Memberikan Cuti / PyBMC).
- Sakit >1–14 hari: butuh surat keterangan dokter, permohonan tertulis ke PyBMC.
- Sakit >14 hari: butuh surat keterangan **dokter pemerintah** (PNS atau bekerja di faskes pemerintah).
- Maksimum diberikan 1 tahun, bisa diperpanjang 6 bulan berdasarkan hasil uji Tim Penguji Kesehatan.
- Jika setelah itu belum sembuh → diberhentikan dengan hormat karena sakit (di luar cakupan aplikasi ini, hanya perlu status/flag).
- Gugur kandungan: hak cuti sakit maks 1,5 bulan.
- Kecelakaan kerja: cuti sakit sampai sembuh (tanpa batas 1 tahun).

### 4.4 Cuti Melahirkan
- Berlaku untuk kelahiran anak ke-1 s.d. ke-3.
- Lama: **3 bulan** (bisa diajukan kurang dari itu untuk kasus tertentu).
- Anak ke-4 dst → pakai skema cuti besar (lihat §4.2).

### 4.5 Cuti Karena Alasan Penting
Berlaku untuk: keluarga inti (ortu, istri/suami, anak, adik, kakak, mertua, menantu) sakit keras/meninggal dan PNS harus mengurus; atau PNS menikah.
- Sakit keras dibuktikan surat rawat inap.
- PNS laki-laki bisa cuti karena istri melahirkan/operasi caesar (dengan surat rawat inap).
- Musibah kebakaran/bencana alam → cuti dengan surat keterangan minimal dari Ketua RT.
- Lama maksimum: **1 bulan**, ditentukan oleh PyBMC.
- Ada mekanisme **izin sementara** oleh pejabat tertinggi di tempat kerja jika mendesak dan tidak bisa menunggu keputusan PyBMC — lalu dilaporkan & disahkan oleh PyBMC setelahnya. **Ini penting untuk desain approval workflow (lihat §6.3).**

### 4.6 Cuti Bersama
- Ditetapkan Presiden (Keppres), bukan diajukan pegawai — bersifat massal/broadcast.
- Tidak mengurangi cuti tahunan.
- Admin bisa menambahkan "cuti bersama" sebagai event kalender yang berlaku ke semua pegawai; pegawai yang dikecualikan (misal piket) dicatat manual oleh admin agar cuti tahunannya ditambah.

### 4.7 Cuti di Luar Tanggungan Negara (CLTN)
- Syarat: masa kerja ≥ 5 tahun, alasan pribadi mendesak (daftar alasan baku ada di dokumen sumber pasal G angka 2).
- Lama: maksimum **3 tahun**, bisa diperpanjang 1 tahun.
- **Tidak menerima penghasilan**, **tidak dihitung sebagai masa kerja**.
- Alur persetujuannya **berbeda dari jenis cuti lain**: PPK tidak bisa mendelegasikan wewenangnya, dan harus mendapat persetujuan tertulis dari Kepala BKN/Kepala Kanreg BKN terlebih dahulu (proses 3 rangkap surat). Setelah selesai, wajib lapor diri tertulis maks 1 bulan setelah cuti berakhir, lalu diproses pengaktifan kembali.
- **Implikasi teknis:** CLTN butuh alur/status khusus yang berbeda dari 6 jenis cuti lain karena melibatkan pihak eksternal (BKN) — di v1 aplikasi ini, langkah "menunggu approval BKN" cukup dicatat sebagai status manual dengan upload dokumen persetujuan BKN (bukan integrasi API, karena BKN tidak menyediakan API publik untuk ini).

---

## 5. Peran Pengguna (Roles)

Selaras dengan role yang sudah ada di SIADIG, ditambah kebutuhan spesifik cuti:

| Role | Kewenangan di aplikasi cuti |
|---|---|
| **Pegawai** | Mengajukan cuti, melihat sisa saldo, melihat riwayat & status, mengunduh surat izin setelah disetujui |
| **Atasan Langsung** | Memberi pertimbangan (setuju/tolak/minta revisi) sebelum diteruskan ke PyBMC. Wewenang ini ditentukan oleh mapping di `cuti_pemetaan_atasan` — bukan oleh role akun |
| **Pejabat Yang Berwenang Memberikan Cuti (PyBMC)** | Keputusan final: menyetujui, menangguhkan, atau menolak; menandatangani surat izin cuti (digital). Wewenang ditentukan oleh `cuti_pemetaan_pejabat_berwenang` — di level Inspektorat biasanya Sekretaris/Inspektur |
| **Admin Cuti / Kepegawaian** | Mengelola data pegawai, master saldo cuti, memverifikasi dokumen, mencatat cuti bersama, generate rekap & laporan, mengelola pemetaan atasan & PyBMC |
| **Super Admin** | Akses penuh, kelola role & konfigurasi aturan cuti |

**Catatan penting:** hubungan "siapa atasan langsung siapa" dan "siapa PyBMC untuk unit kerja mana" harus dikonfigurasi eksplisit (bukan diasumsikan dari struktur organisasi), karena Perka BKN memungkinkan pendelegasian wewenang yang berbeda-beda per jenis cuti (lihat Anak Lampiran 1.a di dokumen sumber — delegasi bisa dibatasi hanya untuk jenis cuti tertentu, mis. hanya cuti tahunan & cuti sakit).

---

## 6. Alur Kerja (Workflow)

### 6.1 Alur Standar (Cuti Tahunan, Besar, Sakit, Melahirkan, Alasan Penting, Bersama)

```
Pegawai mengajukan cuti (isi formulir digital)
        │
        ▼
Sistem validasi otomatis: cek sisa saldo, cek dokumen wajib
   (mis. surat dokter untuk cuti sakit) sudah diunggah
        │
   ┌────┴────┐
 lolos      gagal → dikembalikan ke pegawai dengan catatan
   │
   ▼
Atasan Langsung menerima notifikasi (WA + in-app)
Atasan Langsung: Setuju / Tolak / Minta Revisi
        │
   ┌────┴─────────┐
 setuju          ditolak/revisi → kembali ke pegawai
   │
   ▼
Pejabat Berwenang (PyBMC) menerima notifikasi
PyBMC: Setuju / Tangguhkan / Tolak
        │
   ┌────┴──────────────────┐
 disetujui            ditangguhkan/ditolak
   │                        │
   ▼                        ▼
Surat Izin Cuti digenerate   Pegawai & Atasan diberi notifikasi
otomatis (PDF, nomor surat   alasan + (jika ditangguhkan) tanggal
otomatis, siap cetak)        boleh diajukan ulang
   │
   ▼
Saldo cuti pegawai dipotong otomatis
Notifikasi WA ke pegawai: cuti disetujui, surat siap diunduh
```

### 6.2 Alur Cuti di Luar Tanggungan Negara (berbeda)

```
Pegawai/Admin ajukan CLTN → Atasan Langsung → PPK/Pejabat delegasi
   → Admin Cuti generate surat permintaan persetujuan ke BKN (3 rangkap, manual print)
   → [PROSES EKSTERNAL: menunggu balasan tertulis BKN — dicatat manual di sistem
      dengan upload scan surat persetujuan/penolakan BKN]
   → Jika disetujui BKN → PPK terbitkan Keputusan CLTN (generate PDF dari sistem)
   → Status pegawai berubah jadi "Cuti Luar Tanggungan Negara" sampai tanggal berakhir
   → H-3 bulan sebelum berakhir: sistem kirim reminder ke pegawai & admin
     (untuk perpanjangan atau lapor diri)
   → Pegawai lapor diri tertulis (upload surat) maks 1 bulan setelah CLTN berakhir
   → Admin proses pengaktifan kembali (perlu persetujuan BKN lagi)
```

### 6.3 Jalur Darurat: Izin Sementara

Untuk cuti karena alasan penting yang mendesak (pasal III.E angka 10–13), sistem harus menyediakan jalur cepat:
- Pegawai atau atasan bisa memicu "Izin Sementara" yang langsung aktif dengan approval dari **pejabat tertinggi di tempat kerja saat itu** (bisa berbeda dari PyBMC resmi).
- Begitu izin sementara diberikan, status pegawai langsung "cuti" tanpa menunggu proses normal.
- Sistem otomatis membuat notifikasi ke PyBMC resmi untuk **ratifikasi** dalam waktu tertentu (mis. 3 hari kerja) — ini bukan approval baru, hanya pengesahan atas izin sementara yang sudah berjalan.

### 6.4 Pemanggilan Kembali Bekerja

Perka BKN mengatur bahwa pegawai yang sedang cuti tahunan/besar/alasan penting/bersama bisa dipanggil kembali bekerja karena kepentingan dinas mendesak, dan sisa cuti yang belum dijalani tetap menjadi haknya. Sistem perlu fitur "Panggil Kembali" oleh Admin/PyBMC yang:
- Menghentikan periode cuti pegawai pada tanggal tertentu.
- Mengembalikan sisa hari cuti yang belum dipakai ke saldo pegawai.
- Mencatat sebagai riwayat terpisah (bukan menghapus pengajuan asli).

---

## 7. Kebutuhan Fungsional Detail

### 7.1 Dashboard Pegawai
- Kartu ringkasan: sisa cuti tahunan tahun berjalan (dengan breakdown: jatah tahun ini / carry-over N-1 / carry-over N-2), status pengajuan terakhir, riwayat cuti tahun berjalan.
- Kalender visual menandai hari yang sudah/akan dijalani cuti serta cuti bersama.

### 7.2 Formulir Pengajuan
- Field wajib mengikuti struktur formulir resmi BKN (Anak Lampiran 1.b): data pegawai (auto-fill dari data user), jenis cuti (dropdown 7 jenis), alasan, lama cuti (tanggal mulai–selesai, sistem hitung otomatis jumlah hari kerja dikecualikan akhir pekan/libur nasional), alamat selama cuti & no. telp, upload lampiran sesuai jenis cuti (surat dokter, surat rawat inap, jadwal keberangkatan haji, dll — validasi jenis lampiran wajib berbeda per jenis cuti sesuai §4).
- Validasi real-time saldo sebelum submit (mencegah pengajuan yang pasti ditolak sistem).

### 7.3 Antarmuka Approval (Atasan Langsung & PyBMC)
- Daftar pengajuan masuk dengan filter status & jenis cuti.
- Detail pengajuan + riwayat saldo cuti pegawai terkait (supaya atasan bisa menilai konteks, mis. pola cuti mendadak berulang).
- Tombol Setuju/Tolak/Tangguhkan/Minta Revisi dengan kolom catatan wajib untuk tolak/tangguhkan.
- Tanda tangan digital (minimal: nama + NIP + timestamp yang tercatat di log, bisa ditingkatkan ke e-signature bersertifikat di fase berikutnya).

### 7.4 Perhitungan Saldo Otomatis (Cuti Tahunan)
Modul terpisah yang menjalankan job tahunan (mis. setiap 1 Januari) untuk:
- Menutup saldo tahun berjalan, memindahkan sisa ke saldo carry-over sesuai aturan §4.1.
- Menghanguskan carry-over yang sudah melewati batas waktu.
- Menyediakan endpoint/preview simulasi "berapa sisa cuti saya jika saya ambil X hari mulai tanggal Y" agar pegawai bisa cek sebelum mengajukan.

### 7.5 Manajemen Master Data (Admin)
- Pemetaan Atasan Langsung ↔ Pegawai (per unit kerja/individual override).
- Pemetaan PyBMC & lingkup delegasinya (jenis cuti apa saja yang boleh diputuskan pejabat tsb — sesuai konsep Anak Lampiran 1.a).
- Input Cuti Bersama (tanggal, keterangan, daftar pengecualian pegawai yang tetap piket).
- Kalender hari libur nasional (untuk perhitungan hari kerja).
- Penyesuaian manual saldo cuti (dengan wajib isi alasan, untuk kasus koreksi/migrasi data historis dari sistem manual).

### 7.6 Generate Dokumen
- Surat Izin Cuti (PDF) — format mengikuti **Anak Lampiran 1.b** Perka BKN 24/2017 (Formulir Permintaan dan Pemberian Cuti).
- Untuk CLTN: Surat Permintaan Persetujuan (Anak Lampiran 1.d), Keputusan CLTN (Anak Lampiran 1.e), dan seterusnya — gunakan template resmi yang ada di lampiran dokumen sumber.
- Nomor surat otomatis mengikuti format penomoran surat instansi (dikonfigurasi Admin).

### 7.7 Notifikasi
- WhatsApp via WAHA (reuse infrastruktur SIADIG) untuk: pengajuan baru (ke atasan), hasil keputusan atasan (ke pegawai & PyBMC), hasil keputusan PyBMC (ke pegawai & atasan), reminder CLTN mendekati berakhir, reminder saldo cuti tahunan akan hangus.
- In-app notification/badge sebagai cadangan bila WA gagal terkirim.

### 7.8 Laporan
- Rekap cuti per unit kerja/per periode (untuk keperluan pelaporan ke BKD/BKN).
- Rekap pegawai dengan saldo cuti tahunan yang akan hangus dalam N hari (early warning untuk mendorong pegawai mengambil haknya).
- Ekspor Excel/PDF.

---

## 8. Kebutuhan Non-Fungsional

- **Integritas data pegawai**: perubahan data pegawai (mutasi, jabatan baru, pensiun) diinput oleh Admin langsung ke aplikasi ini. Admin bertanggung jawab memastikan data tetap mutakhir — tidak ada sinkronisasi otomatis dengan sistem lain.
- **Auditability**: setiap perubahan status pengajuan cuti harus punya log immutable (siapa, kapan, aksi apa) — penting karena ini dokumen kepegawaian resmi yang bisa diperiksa BKN. Ironisnya, aplikasi ini dipakai oleh Inspektorat yang tugasnya sendiri mengawasi kepatuhan!
- **Keamanan dokumen**: lampiran (surat dokter, dll) berisi data sensitif kesehatan — perlu akses terbatas (hanya pegawai ybs, atasan langsung terkait, PyBMC terkait, dan admin). File disimpan di storage private, diakses lewat route yang dilindungi middleware.
- **Cetak resmi**: PDF surat izin harus konsisten look-and-feel dengan format resmi BKN agar bisa diterima sebagai dokumen sah tanpa perlu diketik ulang manual.
- **Precision waktu**: perhitungan "hari kerja" harus benar-benar mengecualikan Sabtu/Minggu dan hari libur nasional + cuti bersama — kesalahan di sini langsung berdampak ke hak pegawai.

---

## 9. Prioritas Rilis (disarankan)

**MVP (fase 1):**
- Login terintegrasi (shared users table).
- Pengajuan & approval 2 tingkat untuk Cuti Tahunan & Cuti Sakit (2 jenis cuti paling sering dipakai).
- Perhitungan saldo cuti tahunan otomatis + carry-over.
- Generate PDF surat izin.
- Notifikasi WA dasar.

**Fase 2:**
- Jenis cuti lain (besar, melahirkan, alasan penting, bersama).
- Jalur izin sementara (§6.3).
- Laporan & rekap.

**Fase 3:**
- Cuti di Luar Tanggungan Negara (alur khusus, paling jarang dipakai tapi paling kompleks).
- Dashboard analitik untuk pimpinan (pola cuti, beban kerja unit).
