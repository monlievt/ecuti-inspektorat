# Contoh Perhitungan Saldo & Carry-Over Cuti Tahunan

Dokumen ini menerjemahkan aturan carry-over cuti tahunan dari **Perka BKN Nomor 24 Tahun 2017** menjadi tabel langkah-per-langkah yang siap dijadikan **unit test** untuk `SaldoCutiService`. Semua skenario A–G di dokumen ini **wajib lulus** sebelum `SaldoCutiService` dianggap selesai.

---

## Prinsip Dasar (Ringkasan §4.1 PRD)

| Komponen | Nilai |
|---|---|
| Hak cuti tahunan | **12 hari kerja/tahun** |
| Syarat masa kerja minimal | 12 bulan terus-menerus sejak TMT CPNS |
| Carry-over dari satu tahun ke berikutnya | Maks **6 hari kerja** |
| Total saldo maksimum (1 tahun tidak pakai) | Maks **18 hari kerja** (12 + 6) |
| Total saldo maksimum (2 tahun tidak pakai) | Maks **24 hari kerja** (12 + 6 + 6) |
| carry_over_n2 | **Hangus** di akhir tahun berjalan jika belum dipakai — tidak bisa dibawa ke tahun ke-3 |

---

## Aturan Deduction (Urutan Pengurangan Saldo)

Saat pegawai mengambil cuti, saldo dikurangi dengan urutan **"terlama dulu"** untuk mencegah carry-over lama hangus karena terpakai lebih lambat:

1. `carry_over_n2` — paling tua, paling rentan hangus (didahulukan)
2. `carry_over_n1`
3. `jatah_tahun_berjalan`
4. `tambahan_cuti_bersama`

---

## Rumus Saldo Aktif

```
sisa = jatah_tahun_berjalan + carry_over_n1 + carry_over_n2 + tambahan_cuti_bersama - terpakai
```

Kolom `sisa` **tidak disimpan di database** — selalu dihitung real-time oleh `SaldoCutiService::hitungSisa()`.

---

## Algoritma Year-End (`SaldoCutiService::prosesYearEnd`)

Dijalankan saat menutup tahun N dan membuka rekord saldo tahun N+1:

1. Hitung `sisa_akhir_N` = saldo aktif per 31 Desember tahun N.
2. Hitung komponen yang belum terpakai (berdasarkan urutan deduction):
   - `sisa_carry_n2_N` = sisa carry_n2 yang tidak dipakai di tahun N → **hangus, tidak dibawa ke N+1**
   - `sisa_carry_n1_N` = sisa carry_n1 yang tidak dipakai di tahun N → menjadi `carry_n2` di tahun N+1
   - `sisa_jatah_N` = sisa jatah_tahun_berjalan yang tidak dipakai → menjadi `carry_n1` di tahun N+1 (capped at 6)
3. Buat rekord baru `cuti_saldo_tahunan` untuk tahun N+1:
   - `jatah_tahun_berjalan` = 12
   - `carry_over_n1` = min(`sisa_jatah_N`, 6)
   - `carry_over_n2` = min(`sisa_carry_n1_N`, 6)
   - `tambahan_cuti_bersama` = 0 (dimulai bersih; diupdate saat admin input pengecualian cuti bersama)
   - `terpakai` = 0

> [!CAUTION]
> `prosesYearEnd` harus **idempotent** — bisa dijalankan ulang tanpa menghasilkan data ganda. Implementasikan dengan `updateOrCreate` berdasarkan (`pegawai_id`, `tahun`). Sebaiknya dijalankan **manual oleh Admin** (bukan otomatis tengah malam) agar ada kontrol eksplisit sebelum saldo massal berubah.

---

## Skenario A — Normal: Semua Cuti Dipakai Habis

**Kondisi:** Pegawai disiplin mengambil semua jatah cuti setiap tahun.

| Tahun | jatah | carry_n1 | carry_n2 | tambahan | terpakai | **Sisa** |
|-------|-------|----------|----------|----------|----------|----------|
| 2023  | 12    | 0        | 0        | 0        | 12       | **0**    |
| 2024  | 12    | 0        | 0        | 0        | 12       | **0**    |
| 2025  | 12    | 0        | 0        | 0        | —        | **12**   |

**Year-end 2023→2024:** sisa_jatah_2023 = 0 → carry_n1(2024) = 0.
**Year-end 2024→2025:** sisa_jatah_2024 = 0 → carry_n1(2025) = 0.

**Unit test assertions:**
```php
$this->assertEquals(12, $service->hitungSisa($pegawai, 2025));
$this->assertEquals(0,  $saldo2025->carry_over_n1);
$this->assertEquals(0,  $saldo2025->carry_over_n2);
```

---

## Skenario B — Sebagian Sisa (Sisa < 6 Hari)

**Kondisi:** Tahun 2024, pegawai hanya pakai 8 hari dari jatah 12.

| Tahun | jatah | carry_n1 | carry_n2 | tambahan | terpakai | **Sisa** | carry ke berikutnya |
|-------|-------|----------|----------|----------|----------|----------|----------------------|
| 2024  | 12    | 0        | 0        | 0        | 8        | **4**    | carry_n1(2025) = **4** |
| 2025  | 12    | 4        | 0        | 0        | —        | **16**   | — |

**Year-end 2024→2025:** sisa_jatah_2024 = 4 → carry_n1(2025) = min(4, 6) = **4**.

**Unit test assertions:**
```php
$this->assertEquals(16, $service->hitungSisa($pegawai, 2025));
$this->assertEquals(4,  $saldo2025->carry_over_n1);
$this->assertEquals(0,  $saldo2025->carry_over_n2);
```

---

## Skenario C — Tidak Pakai Cuti 1 Tahun (Carry Mencapai Batas 6)

**Kondisi:** Tahun 2024 pegawai sama sekali tidak mengambil cuti (sisa 12 hari, tapi hanya 6 yang bisa dibawa).

| Tahun | jatah | carry_n1 | carry_n2 | tambahan | terpakai | **Sisa** | carry ke berikutnya |
|-------|-------|----------|----------|----------|----------|----------|----------------------|
| 2024  | 12    | 0        | 0        | 0        | 0        | **12**   | carry_n1(2025) = **6** *(6 hari hangus)* |
| 2025  | 12    | 6        | 0        | 0        | —        | **18**   | — |

**Year-end 2024→2025:** sisa_jatah_2024 = 12 → carry_n1(2025) = min(12, 6) = **6**. Sisa 6 hari lainnya **hangus**.

**Unit test assertions:**
```php
$this->assertEquals(18, $service->hitungSisa($pegawai, 2025));
$this->assertEquals(6,  $saldo2025->carry_over_n1);
$this->assertLessThanOrEqual(18, $service->hitungSisa($pegawai, 2025)); // tidak boleh > 18
```

---

## Skenario D — Tidak Pakai Cuti 2 Tahun Berturut-turut (Maks 24 Hari)

*Ini adalah skenario "Sdr. Saputra" yang disebutkan dalam dokumen sumber Perka BKN.*

**Kondisi:** Pegawai tidak mengambil cuti sama sekali di 2023 dan 2024.

| Tahun | jatah | carry_n1 | carry_n2 | tambahan | terpakai | **Sisa** | carry ke berikutnya |
|-------|-------|----------|----------|----------|----------|----------|----------------------|
| 2023  | 12    | 0        | 0        | 0        | 0        | **12**   | carry_n1(2024) = **6** *(6 hangus)* |
| 2024  | 12    | 6        | 0        | 0        | 0        | **18**   | carry_n1(2025) = **6**, carry_n2(2025) = **6** |
| 2025  | 12    | 6        | 6        | 0        | —        | **24**   | — |

**Year-end 2023→2024:**
- sisa_jatah_2023 = 12 → carry_n1(2024) = min(12, 6) = **6**. Sisa 6 hangus.

**Year-end 2024→2025:**
- sisa_jatah_2024 = 12 (belum ada yang terpakai dari jatah murni)
- carry_n1(2025) = min(12, 6) = **6** ← dari jatah murni 2024
- sisa_carry_n1_2024 = 6 (carry dari 2023 yang ada di 2024, tidak terpakai)
- carry_n2(2025) = min(6, 6) = **6** ← carry dari 2023 yang melewati 2024

**Unit test assertions:**
```php
$this->assertEquals(24, $service->hitungSisa($pegawai, 2025));
$this->assertEquals(6,  $saldo2025->carry_over_n1);
$this->assertEquals(6,  $saldo2025->carry_over_n2);
$this->assertLessThanOrEqual(24, $service->hitungSisa($pegawai, 2025)); // tidak boleh > 24
```

---

## Skenario E — Carry-over N2 Hangus di Akhir Tahun

**Kondisi:** Melanjutkan dari Skenario D. Di 2025, pegawai mengambil 15 hari cuti.

| Tahun | jatah | carry_n1 | carry_n2 | tambahan | terpakai | **Sisa** |
|-------|-------|----------|----------|----------|----------|----------|
| 2025  | 12    | 6        | 6        | 0        | 15       | **9**    |

**Simulasi deduction (urutan terlama dulu):**
| Langkah | Dikurangi dari | Jumlah | Sisa dikurangi |
|---------|---------------|--------|----------------|
| 1 | carry_n2 (6) | 6 | 9 tersisa |
| 2 | carry_n1 (6) | 6 | 3 tersisa |
| 3 | jatah (12) | 3 | selesai |

→ Sisa jatah murni 2025 yang tidak terpakai = 12 - 3 = **9**

**Year-end 2025→2026:**
- carry_n1(2026) = min(9, 6) = **6**
- carry_n2(2026) = 0 ← carry_n1_2024 sudah habis dipakai saat deduction 2025; carry_n2_2024 juga sudah dipakai

| Tahun | jatah | carry_n1 | carry_n2 | **Total Saldo** |
|-------|-------|----------|----------|-----------------|
| 2026  | 12    | 6        | 0        | **18**          |

**Unit test assertions:**
```php
$this->assertEquals(9,  $service->hitungSisa($pegawai, 2025));
$this->assertEquals(18, $service->hitungSisa($pegawai, 2026));
$this->assertEquals(6,  $saldo2026->carry_over_n1);
$this->assertEquals(0,  $saldo2026->carry_over_n2); // carry_n2 tidak lanjut ke 2026
```

---

## Skenario F — Pengecualian Cuti Bersama (Piket) Menambah Saldo

**Kondisi:** Tahun 2024 ada 2 hari cuti bersama (H+1 dan H+2 Lebaran). Sdr. Budi bertugas piket sehingga dikecualikan — hak cuti tahunannya ditambah 2 hari.

| Tahun | jatah | carry_n1 | carry_n2 | **tambahan** | terpakai | **Sisa** |
|-------|-------|----------|----------|--------------|----------|----------|
| 2024  | 12    | 0        | 0        | **2**        | 10       | **4**    |

**Year-end 2024→2025:**
- Deduction 10 dari: carry_n2(0) → carry_n1(0) → jatah(10) → sisa jatah murni = 2; tambahan(2) tidak terpakai
- `tambahan_cuti_bersama` **tidak carry-over** — hangus di akhir tahun (hanya berlaku tahun berjalan, sesuai §4.1 PRD)
- carry_n1(2025) = min(sisa_jatah_murni_2024, 6) = min(2, 6) = **2**

| Tahun | jatah | carry_n1 | carry_n2 | tambahan | **Total Saldo** |
|-------|-------|----------|----------|----------|-----------------|
| 2025  | 12    | 2        | 0        | 0        | **14**          |

**Unit test assertions:**
```php
$this->assertEquals(14, $service->hitungSisa($pegawai, 2024)); // 12 + 2 tambahan
$this->assertEquals(14, $service->hitungSisa($pegawai, 2025)); // 12 + 2 carry_n1
$this->assertEquals(2,  $saldo2025->carry_over_n1);
$this->assertEquals(0,  $saldo2025->tambahan_cuti_bersama); // tidak carry
```

---

## Skenario G — Cuti Besar Membekukan Hak Cuti Tahunan Tahun Berjalan

*Dasar: PRD §4.2 — "Menggunakan cuti besar → tidak berhak cuti tahunan di tahun bersangkutan, **kecuali** sisa cuti tahunan tahun sebelumnya yang belum kadaluarsa (carry-over)."*

Saat cuti besar disetujui, `SaldoCutiService::bekukanJatahTahunan()` dipanggil → set `jatah_dibekukan = true` di `cuti_saldo_tahunan`. Setelah ini:
- `jatah_tahun_berjalan` (12 hari) → **tidak bisa dipakai**
- `carry_over_n1` dan `carry_over_n2` → **tetap bisa dipakai** (hak dari tahun sebelumnya)

### G1 — "Sdr. Ahmad" (belum pakai cuti tahunan sama sekali sebelum ajukan cuti besar)

| Komponen | Nilai |
|---|---|
| jatah_tahun_berjalan 2024 | 12 hari |
| carry_over_n1 dari 2023 | 4 hari |
| Sudah pakai cuti tahunan di 2024 | 0 hari |
| **Cuti besar disetujui** | → `jatah_dibekukan = true` |

**Sesudah cuti besar disetujui:**
- Saldo yang **masih bisa dipakai** = carry_n1 (4) = **4 hari**
- Jatah 12 hari 2024 → dibekukan (tidak bisa digunakan)
- Pengajuan cuti tahunan baru yang membutuhkan `jatah_tahun_berjalan` → **ditolak sistem**

```php
$this->assertEquals(4,  $service->hitungSaldoBisaDipakai($pegawai, 2024)); // hanya carry
$this->expectException(SaldoTidakCukupException::class);
$service->ajukanCutiTahunan($pegawai, 2024, 5); // melebihi carry yang tersedia
```

### G2 — "Sdr. Aldi" (sudah pakai 6 hari cuti tahunan sebelum ajukan cuti besar)

| Komponen | Nilai |
|---|---|
| jatah_tahun_berjalan 2024 | 12 hari |
| carry_over_n1 dari 2023 | 2 hari |
| Sudah pakai cuti tahunan di 2024 (dari jatah) | 6 hari |
| **Cuti besar disetujui** | → `jatah_dibekukan = true` |

**Sesudah cuti besar disetujui:**
- 6 hari yang sudah dipakai: tetap tercatat (tidak bisa dikembalikan)
- Sisa jatah 2024 (6 hari) → dibekukan
- Saldo yang **masih bisa dipakai** = carry_n1 (2) = **2 hari**

```php
$this->assertEquals(2, $service->hitungSaldoBisaDipakai($pegawai, 2024)); // hanya sisa carry
```

---

## Mapping ke Method `SaldoCutiService`

| Method | Fungsi |
|---|---|
| `hitungSisa(pegawaiId, tahun)` | Hitung saldo total aktif real-time |
| `hitungSaldoBisaDipakai(pegawaiId, tahun)` | Hitung saldo yang benar-benar bisa digunakan (mempertimbangkan `jatah_dibekukan`) |
| `breakdown(pegawaiId, tahun)` | Return array lengkap semua komponen saldo |
| `simulasiAmbilCuti(pegawaiId, jumlahHari, tahun)` | Preview "sisa jika ambil X hari" tanpa commit ke DB |
| `prosesYearEnd(tahun)` | Job tutup-tahun: hitung carry baru, buat rekord saldo tahun depan |
| `bekukanJatahTahunan(pegawaiId, tahun)` | Dipanggil saat cuti besar/CLTN disetujui |
| `tambahTambahanCutiBersama(pegawaiId, tahun, jumlah)` | Dipanggil saat admin input pengecualian cuti bersama |
