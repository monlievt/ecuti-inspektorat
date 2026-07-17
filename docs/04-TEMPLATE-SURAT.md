# Template Surat — Breakdown Field & Layout (sumber: Anak Lampiran Perka BKN 24/2017)

Dokumen ini menerjemahkan setiap Anak Lampiran di PDF sumber (`PERATURAN-BKN-NOMOR-24-TAHUN-2017-TATA-CARA-PEMBERIAN-CUTI-PNS.pdf`) menjadi breakdown field yang siap dijadikan Blade view + `SuratCutiPdfService`. Prioritas implementasi mengikuti roadmap di `01-PRD.md` §9: **1.b dan 1.c dulu (MVP)**, sisanya di Fase 3 (CLTN).

Kop surat, format tanggal Indonesia ("Trenggalek, 14 Juli 2026"), dan penomoran surat mengikuti standar penomoran naskah dinas Inspektorat — **konfirmasikan format nomor surat resmi Inspektorat ke Admin sebelum hardcode**, karena Perka BKN sendiri tidak mengatur format nomor surat, hanya isi/struktur formulirnya.

---

## PRIORITAS 1 — Anak Lampiran 1.b: Formulir Permintaan dan Pemberian Cuti

**Ini adalah dokumen paling penting** — dipakai untuk 5 dari 7 jenis cuti (Tahunan, Besar, Sakit, Melahirkan, Alasan Penting). Satu template, isinya sedikit berbeda tergantung jenis cuti yang dicentang.

### Header
```
[Kota/Kab, tanggal surat dibuat]

Kepada
Yth. [nama & jabatan Pejabat Yang Berwenang Memberikan Cuti]
di
[tempat]

FORMULIR PERMINTAAN DAN PEMBERIAN CUTI
```

### Bagian I — Data Pegawai (auto-fill dari tabel `siadig.users` via `pegawai_id`)
| Field | Sumber data |
|---|---|
| Nama | `pegawai.nama` |
| NIP | `pegawai.nip` |
| Jabatan | `pegawai.jabatan` |
| Masa Kerja | dihitung dari `pegawai.tmt_cpns` s.d. tanggal pengajuan |
| Unit Kerja | `pegawai.unit_kerja` |

### Bagian II — Jenis Cuti yang Diambil
Checkbox 6 pilihan (render sebagai daftar dengan tanda ✓ pada yang dipilih, sisanya kosong — **bukan dropdown di versi cetak**, tapi tetap dropdown di form input):
1. Cuti Tahunan
2. Cuti Besar
3. Cuti Sakit
4. Cuti Melahirkan
5. Cuti Karena Alasan Penting
6. Cuti di Luar Tanggungan Negara *(catatan: sesuai §4.7 PRD, CLTN sebenarnya pakai alur & lampiran sendiri (1.d–1.h), formulir 1.b ini mencantumkannya sebagai opsi tapi praktiknya CLTN tidak lewat jalur 1.b biasa — tampilkan pilihan ini nonaktif/disabled di form aplikasi, arahkan ke alur CLTN terpisah)*

### Bagian III — Alasan Cuti
Textarea bebas — isi bergantung jenis cuti (lihat `alasan` & `alasan_kategori` di `cuti_pengajuan`).

### Bagian IV — Lamanya Cuti
```
Selama [jumlah] (hari/bulan/tahun)*   mulai tanggal [tanggal_mulai]   s/d [tanggal_selesai]
```
- Satuan (hari/bulan/tahun) mengikuti `satuan_hari` dari tabel `cuti_pengajuan` — untuk Cuti Tahunan & Sakit pakai "hari", Cuti Besar & Melahirkan bisa pakai "bulan".

### Bagian V — Catatan Cuti (diisi oleh pejabat kepegawaian sebelum PNS mengajukan — di versi digital ini otomatis terisi sistem, bukan manual)
Tabel 2 kolom:
```
1. CUTI TAHUNAN                          2. CUTI BESAR       [tanggal terakhir dipakai]
   Tahun | Sisa | Keterangan             3. CUTI SAKIT       [tanggal terakhir dipakai]
   N-2   | ..   |                        4. CUTI MELAHIRKAN  [tanggal terakhir dipakai]
   N-1   | ..   |                        5. CUTI ALASAN PENTING [tanggal terakhir dipakai]
   N     | ..   |                        6. CUTI DI LUAR TANGGUNGAN NEGARA [tanggal terakhir dipakai]
```
- Kolom "Sisa" untuk N-2/N-1/N diambil langsung dari `SaldoCutiService::breakdown($pegawai, $tahun)` — **jangan dihitung ulang manual di view**, panggil service yang sama dipakai untuk validasi saldo supaya angka di surat selalu konsisten dengan angka yang dipakai sistem untuk approve/reject.

### Bagian VI — Alamat Selama Menjalankan Cuti
```
[alamat_selama_cuti]                    TELP [telp_selama_cuti]

                                          Hormat saya,

                                          (___________________)
                                          NIP. ________________
```
- Tanda tangan digital: render nama pegawai + NIP + teks "Ditandatangani secara elektronik pada [timestamp]" — bukan tanda tangan basah, kecuali Inspektorat punya kebijakan wajib print & tanda tangan fisik (konfirmasikan ke Admin; jika iya, sediakan tombol "download untuk ditandatangani manual" sebagai fallback).

### Bagian VII — Pertimbangan Atasan Langsung
```
[ ] DISETUJUI   [ ] PERUBAHAN, yaitu: ____   [ ] DITANGGUHKAN, alasan: ____   [ ] TIDAK DISETUJUI, alasan: ____

Ttd. [nama & NIP Atasan Langsung]   [timestamp approval]
```

### Bagian VIII — Keputusan Pejabat Yang Berwenang Memberikan Cuti
```
[ ] DISETUJUI   [ ] PERUBAHAN, yaitu: ____   [ ] DITANGGUHKAN, alasan: ____   [ ] TIDAK DISETUJUI, alasan: ____

Ttd. [nama & NIP PyBMC]   [timestamp approval]
```

**Field mapping ke `SuratCutiPdfService`:**
```php
[
    'pegawai' => $pengajuan->pegawai, // dari koneksi siadig
    'jenis_cuti' => $pengajuan->jenisCuti,
    'alasan' => $pengajuan->alasan,
    'lama' => $pengajuan->jumlah_hari_kerja . ' ' . $satuanLabel,
    'tanggal_mulai' => $pengajuan->tanggal_mulai,
    'tanggal_selesai' => $pengajuan->tanggal_selesai,
    'saldo_breakdown' => app(SaldoCutiService::class)->breakdown($pengajuan->pegawai_id, now()->year),
    'alamat_cuti' => $pengajuan->alamat_selama_cuti,
    'telp_cuti' => $pengajuan->telp_selama_cuti,
    'approval_atasan' => $pengajuan->approvalLogs()->where('peran_aktor', 'atasan_langsung')->latest()->first(),
    'approval_pybmc' => $pengajuan->approvalLogs()->where('peran_aktor', 'pyBMC')->latest()->first(),
    'nomor_surat' => $pengajuan->suratTerbit->nomor_surat,
]
```

---

## PRIORITAS 2 — Anak Lampiran 1.c: Izin Sementara Pelaksanaan Cuti Karena Alasan Penting

Dipakai untuk jalur darurat (§6.3 PRD & BLUEPRINT). Lebih sederhana dari 1.b, satu pihak (pejabat tertinggi di tempat kerja) langsung memutuskan.

```
[Kota/Kab, tanggal]

IZIN SEMENTARA PELAKSANAAN CUTI KARENA ALASAN PENTING
NOMOR [nomor_surat]

1. Diberikan izin sementara untuk melaksanakan cuti karena alasan penting kepada Pegawai Negeri Sipil:
   Nama                    : [pegawai.nama]
   NIP                     : [pegawai.nip]
   Pangkat/golongan ruang  : [pegawai.pangkat_golongan]
   Jabatan                 : [pegawai.jabatan]
   Unit Kerja              : [pegawai.unit_kerja]

   Selama [jumlah] hari, terhitung mulai tanggal [tanggal_mulai] sampai dengan
   tanggal [tanggal_selesai], dengan ketentuan sebagai berikut:
   a. Sebelum menjalankan cuti karena alasan penting, wajib menyerahkan
      pekerjaannya kepada atasan langsungnya atau pejabat lain yang ditunjuk.
   b. Setelah selesai menjalankan cuti karena alasan penting, wajib melaporkan
      diri kepada atasan langsungnya dan bekerja kembali sebagaimana biasa.

2. Demikian izin sementara melaksanakan cuti karena alasan penting ini dibuat
   untuk dapat digunakan sebagaimana mestinya.

                                          ( [nama pejabat pemberi izin sementara] )
                                          NIP. [nip pejabat]

TEMBUSAN:
1. [PyBMC resmi — wajib, untuk keperluan ratifikasi]
2. [Admin Kepegawaian]
```

**Catatan implementasi:** field "pejabat pemberi izin sementara" **tidak selalu sama** dengan PyBMC resmi yang terdaftar di `cuti_pemetaan_pejabat_berwenang` — ini opsi login user manapun yang punya role dengan flag `bisa_beri_izin_sementara` (tambahkan flag ini ke sistem role, bukan disamakan dengan PyBMC). Setelah surat ini terbit, status pengajuan otomatis masuk `menunggu_ratifikasi` dan mengirim notifikasi ke PyBMC resmi.

---

## FASE 3 — Formulir Terkait CLTN (Anak Lampiran 1.a, 1.d s.d. 1.l)

Karena semuanya dipakai di alur yang jarang terjadi dan melibatkan korespondensi manual dengan BKN, cukup dibuat sebagai **template generate-dokumen sederhana** (bukan bagian dari alur approval otomatis seperti 1.b), yang datanya diambil dari tabel `cuti_luar_tanggungan_negara`. Ringkasan field tiap lampiran:

### 1.a — Keputusan Pendelegasian Wewenang Pemberian Cuti
*(Bukan per-pengajuan, sekali dibuat saat setup oleh Admin — lihat §7.5 PRD)*
- Nomor & tanggal keputusan
- Nama jabatan yang didelegasikan wewenang
- Daftar jenis cuti yang didelegasikan (checkbox a–e: Tahunan, Besar, Sakit, Melahirkan, Alasan Penting — **CLTN sengaja tidak bisa didelegasikan**, sesuai Perka BKN pasal G angka 17)
- Tembusan

### 1.d — Permintaan Persetujuan CLTN ke Kepala BKN
Tabel data: Nama, NIP, Pangkat/Golongan, Jabatan, Unit Kerja, Masa Kerja Golongan, Gaji Pokok, Telah Bekerja Sejak, Alasan Permintaan Cuti, Lamanya Cuti, Wilayah Pembayaran. **Dibuat rangkap 3 (cetak manual)** — cukup 1 tombol "Cetak untuk BKN" di aplikasi.

### 1.e — Keputusan CLTN
Struktur keputusan formal (Menimbang/Mengingat/Memutuskan) dengan data pegawai + jangka waktu cuti. Diisi setelah `status_bkn = disetujui_bkn` di tabel `cuti_luar_tanggungan_negara`.

### 1.f — Permintaan Perpanjangan CLTN
Surat pribadi dari pegawai (bukan format keputusan) — isi: identitas, referensi keputusan CLTN awal, alasan perpanjangan, lama perpanjangan diminta, alamat selama cuti.

### 1.g — Persetujuan Perpanjangan dari Kepala BKN
Sama struktur dengan 1.d tapi untuk perpanjangan — tambahan field: Nomor & Tanggal Keputusan CLTN awal, Lamanya Cuti yang Telah Diberikan.

### 1.h — Keputusan Perpanjangan CLTN
Sama struktur dengan 1.e, tambahan field lama perpanjangan.

### 1.i — Laporan Telah Selesai Menjalankan CLTN
Surat pribadi dari pegawai: identitas + referensi keputusan CLTN + tanggal selesai + permohonan diaktifkan kembali. Field ini mengisi `cuti_luar_tanggungan_negara.tanggal_lapor_diri`.

### 1.j — Permohonan Persetujuan Pengaktifan Kembali
Dibuat Admin/PPK ke Kepala BKN — isi identitas pegawai + referensi seluruh riwayat keputusan CLTN & perpanjangannya.

### 1.k — Keputusan Pengaktifan Kembali PNS
Struktur keputusan formal, field: Nama, NIP, Pangkat/Golongan Ruang, Jabatan, Masa Kerja Golongan, Gaji Pokok. Mengubah `cuti_luar_tanggungan_negara.status_pengaktifan_kembali` jadi `disetujui`.

### 1.l — Permintaan Penyaluran Pegawai ke Instansi Lain
*(Kasus paling jarang — hanya jika tidak ada lowongan jabatan di instansi asal)* Surat dari PPK ke Kepala BKN berisi identitas pegawai & permintaan bantuan penyaluran.

---

## Rekomendasi Implementasi PDF

- Gunakan satu Blade layout dasar (`resources/views/pdf/layout.blade.php`) berisi kop surat, margin, font — semua 12 template di atas extend dari layout ini supaya konsisten dan mudah diubah sekali untuk semuanya (mis. kalau kop surat Inspektorat berubah).
- Simpan setiap template sebagai `resources/views/pdf/lampiran-1b.blade.php`, `lampiran-1c.blade.php`, dst — penamaan file mengikuti kode Anak Lampiran supaya gampang ditelusuri baliknya ke dokumen sumber Perka BKN.
- Untuk MVP, cukup buat `lampiran-1b.blade.php` dan `lampiran-1c.blade.php`. Sisanya boleh dibuat sebagai stub kosong dulu (return "belum diimplementasikan") sampai Fase 3 dikerjakan — jangan biarkan AI agent menghabiskan waktu di 10 template CLTN sebelum MVP selesai.
