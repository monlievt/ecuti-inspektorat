# Design Direction: e-Cuti Inspektorat Daerah Kabupaten Trenggalek

Dokumen ini adalah acuan arah visual dan identitas antarmuka sistem e-Cuti Inspektorat. Antislop bertindak sebagai penyaring (filter anti-slop), sedangkan dokumen ini adalah sumber identitas (direction & soul).

---

## 1. Identitas & Karakter
- **Produk**: Sistem Informasi Pengajuan & Pengelolaan Cuti ASN Inspektorat Kabupaten Trenggalek.
- **Pengguna**: Pegawai Negeri Sipil / PPPK, Atasan Langsung (Pejabat Penilai), Pejabat Yang Berwenang Memberikan Cuti (PyBMC / Inspektur), dan Admin Kepegawaian.
- **Karakter Visual**: Bersih, Resmi, Akurat, Berwibawa, Mudah Dipahami (High Legibility & Usability).
- **Tone & Suara**: Bahasa Indonesia baku, sopan, lugas, dan jelas. Hindari jargon pemasaran atau bahasa AI yang berlebihan (*buzzwords* seperti "merevolusi pengalaman cuti", "solusi serba canggih", dsb).

---

## 2. Palet Warna (Color Palette)
Menggunakan Tailwind CSS Slate & Indigo/Emerald harmony:

- **Netral / Permukaan**:
  - Background Halaman: `bg-slate-50` (`#f8fafc`)
  - Background Kartu / Kontainer: `bg-white` (`#ffffff`)
  - Border / Garis Pemisah: `border-slate-200` (`#e2e8f0`) atau `border-slate-300`
  - Teks Utama: `text-slate-900` (`#0f172a`)
  - Teks Sekunder: `text-slate-600` (`#475569`)
  - Teks Redup / Caption: `text-slate-400` (`#94a3b8`)

- **Aksen & Brand**:
  - Brand Gradient: `from-indigo-600 to-violet-600` (khusus logo/header ringkas)
  - Tombol Utama / Primary Action: `bg-indigo-600 hover:bg-indigo-700 text-white`
  - Active Nav Link: `border-indigo-500 text-slate-900 font-semibold`

- **Status & Feedback (Badges & Alerts)**:
  - **Menunggu Persetujuan**: `bg-amber-50 text-amber-700 border-amber-200 ring-1 ring-amber-600/20`
  - **Disetujui**: `bg-emerald-50 text-emerald-700 border-emerald-200 ring-1 ring-emerald-600/20`
  - **Ditolak / Dibatalkan**: `bg-rose-50 text-rose-700 border-rose-200 ring-1 ring-rose-600/20`
  - **Perubahan Jadwal**: `bg-blue-50 text-blue-700 border-blue-200 ring-1 ring-blue-600/20`
  - **Izin Darurat / Sementara**: `bg-purple-50 text-purple-700 border-purple-200 ring-1 ring-purple-600/20`

---

## 3. Tipografi
- **Font Family**: `Outfit`, sans-serif (Google Fonts) didukung sistem fallback `sans-serif`.
- **Skala Ukuran**:
  - Judul Halaman: `text-2xl font-bold tracking-tight text-slate-900`
  - Judul Kartu / Bagian: `text-lg font-semibold text-slate-800`
  - Teks Tabel & Formulir: `text-sm text-slate-700`
  - Label Input: `text-xs font-semibold text-slate-500 uppercase tracking-wider` atau `text-sm font-medium text-slate-700`
  - Catatan / Hint: `text-xs text-slate-500`

---

## 4. Dials & Density (anti-slop parameters)
- **ENERGY**: 2 (Tenang, formal, profesional pemerintahan).
- **RHYTHM**: 2 (Keteraturan tata letak, grid konsisten, padding cards `p-6` atau `p-4 sm:p-6`).
- **MOTION**: 1 (Halus dan fungsional: transisi tombol `transition duration-150 ease-in-out`, tidak ada animasi melayang atau bouncing berlebihan).

---

## 5. Komponen & Pedoman Interaksi
- **Formulir**:
  - Input teks, select, dan datepicker harus memiliki kontras tinggi dengan `border-slate-300 focus:border-indigo-500 focus:ring-indigo-500`.
  - Pesan error validasi jelas di bawah input (`text-xs text-rose-600 mt-1`).
- **Tabel Data**:
  - Header tabel dengan background halus (`bg-slate-50 text-slate-600 text-xs uppercase font-medium`).
  - Baris tabel bersih dengan border bawah tipis dan hover state (`hover:bg-slate-50/60`).
  - Penyelarasan: Teks kiri (`text-left`), angka/saldo tengah atau kanan (`text-center` / `text-right`), aksi kanan (`text-right`).
- **Modal / Dialog**:
  - Menggunakan Alpine.js (`x-data`, `x-show`, `x-transition`), backdrop gelap transparan (`bg-slate-900/50`).
- **Aksesibilitas (WCAG AA)**:
  - Rasio kontras teks terhadap latar belakang minimal 4.5:1.
  - Target klik/tap minimal 44x44px untuk pengguna ponsel.
  - Setiap tombol aksi penting memiliki label atau tooltip yang jelas.
