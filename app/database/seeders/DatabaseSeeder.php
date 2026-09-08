<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use App\Models\CutiJenis;
use App\Models\CutiHariLibur;
use App\Models\CutiPemetaanAtasan;
use App\Models\CutiPemetaanPejabatBerwenang;
use App\Models\CutiSaldoTahunan;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Inisialisasi Jenis Cuti (Wajib)
        $jenisCuti = [
            ['kode' => CutiJenis::TAHUNAN, 'nama' => 'Cuti Tahunan', 'deskripsi' => 'Cuti tahunan PNS', 'aktif' => true],
            ['kode' => CutiJenis::BESAR, 'nama' => 'Cuti Besar', 'deskripsi' => 'Cuti besar PNS (kerja >= 5 tahun)', 'aktif' => true],
            ['kode' => CutiJenis::SAKIT, 'nama' => 'Cuti Sakit', 'deskripsi' => 'Cuti sakit dengan surat keterangan dokter', 'aktif' => true],
            ['kode' => CutiJenis::MELAHIRKAN, 'nama' => 'Cuti Melahirkan', 'deskripsi' => 'Cuti melahirkan anak ke-1, 2, dan 3', 'aktif' => true],
            ['kode' => CutiJenis::ALASAN_PENTING, 'nama' => 'Cuti Karena Alasan Penting', 'deskripsi' => 'Cuti karena keluarga sakit keras/meninggal, menikah, dll', 'aktif' => true],
            ['kode' => CutiJenis::BERSAMA, 'nama' => 'Cuti Bersama', 'deskripsi' => 'Cuti bersama yang ditetapkan Presiden', 'aktif' => true],
            ['kode' => CutiJenis::CLTN, 'nama' => 'Cuti di Luar Tanggungan Negara', 'deskripsi' => 'Cuti khusus di luar tanggungan negara', 'aktif' => true],
        ];

        foreach ($jenisCuti as $jc) {
            CutiJenis::updateOrCreate(['kode' => $jc['kode']], $jc);
        }

        // 2. Hari Libur Nasional (Wajib)
        CutiHariLibur::updateOrCreate(['tanggal' => '2026-08-17'], ['keterangan' => 'Hari Kemerdekaan RI']);
        CutiHariLibur::updateOrCreate(['tanggal' => '2026-12-25'], ['keterangan' => 'Hari Raya Natal']);

        // 3. User Admin default untuk login pengelolaan awal
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@cuti.test'],
            [
                'name' => 'Admin Kepegawaian',
                'password' => bcrypt('password'),
                'role' => 'admin_cuti',
                'bisa_beri_izin_sementara' => false
            ]
        );

        // 4. Buat Unit Kerja Master Organisasi Dasar
        $unitMaster = [
            'SEKRETARIAT' => 'Sekretariat',
            'IRBAN-I' => 'Inspektur Pembantu Wilayah I',
            'IRBAN-II' => 'Inspektur Pembantu Wilayah II',
            'IRBAN-III' => 'Inspektur Pembantu Wilayah III',
            'IRBAN-IV' => 'Inspektur Pembantu Wilayah IV',
        ];

        $createdUnits = [];
        foreach ($unitMaster as $kode => $nama) {
            $createdUnits[$kode] = UnitKerja::updateOrCreate(
                ['kode' => $kode],
                ['nama' => $nama, 'aktif' => true]
            );
        }

        // Buat Subbagian Kepegawaian di bawah Sekretariat
        $createdUnits['SUB-KEPEG'] = UnitKerja::updateOrCreate(
            ['kode' => 'SUB-KEPEG'],
            [
                'nama' => 'Subbagian Kepegawaian',
                'parent_id' => $createdUnits['SEKRETARIAT']->id,
                'aktif' => true
            ]
        );

        // 5. Baca berkas CSV data pegawai
        $csvPath = base_path('../docs/Data Pegawai Inspektorat Trenggalek.csv');
        if (!file_exists($csvPath)) {
            $this->command->error("File CSV tidak ditemukan di: {$csvPath}");
            return;
        }

        $file = fopen($csvPath, 'r');
        $header = fgetcsv($file); // Skip header baris 1

        $pegawaiList = [];
        $pimpinanList = []; // Untuk penampung Irban / Sekretaris

        while (($row = fgetcsv($file)) !== false) {
            // Pemetaan Kolom CSV:
            // 0: NO, 1: NAMA TANPA GELAR, 2: NAMA, 3: NOMOR HP AKTIF, 4: NIP, 
            // 5: TEMPAT LAHIR, 6: TANGGAL LAHIR, 7: PANGKAT, 8: BIDANG, 9: GOLONGAN, 10: JABATAN, 11: ALAMAT EMAIL AKTIF
            
            $namaLengkap = trim($row[2]);
            $nip = preg_replace('/\s+/', '', trim($row[4]));
            $nomorHp = preg_replace('/[^0-9]/', '', trim($row[3]));
            $email = trim($row[11]);
            $pangkat = trim($row[7]);
            $bidang = trim(strtoupper($row[8]));
            $golongan = trim($row[9]);
            $jabatan = trim($row[10]);

            if (empty($nip)) {
                continue;
            }

            $namaTanpaGelar = trim($row[1] ?? '');
            if (empty($email)) {
                $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $namaTanpaGelar ?: $namaLengkap));
                $email = ($cleanName ?: $nip) . '@inspektorat.trenggalekkab.go.id';
            }

            // Normalisasi Unit Kerja berdasarkan Kolom BIDANG
            $unitKerjaId = $createdUnits['SEKRETARIAT']->id; // default
            if (Str::contains($bidang, ['IRBAN I', 'PEMBANTU I'])) {
                $unitKerjaId = $createdUnits['IRBAN-I']->id;
            } elseif (Str::contains($bidang, ['IRBAN II', 'PEMBANTU II'])) {
                $unitKerjaId = $createdUnits['IRBAN-II']->id;
            } elseif (Str::contains($bidang, ['IRBAN III', 'PEMBANTU III'])) {
                $unitKerjaId = $createdUnits['IRBAN-III']->id;
            } elseif (Str::contains($bidang, ['IRBAN IV', 'PEMBANTU IV'])) {
                $unitKerjaId = $createdUnits['IRBAN-IV']->id;
            } elseif (Str::contains($bidang, ['KASUBBAG', 'UMUM', 'KEPEGAWAIAN'])) {
                $unitKerjaId = $createdUnits['SUB-KEPEG']->id;
            }

            // Hitung Jenis Kelamin dari NIP (digit ke-15: 1 = L, 2 = P)
            $jenisKelamin = 'L';
            if (strlen($nip) >= 15) {
                $digit15 = $nip[14];
                if ($digit15 == '2') {
                    $jenisKelamin = 'P';
                }
            }

            // Tentukan jenis pegawai (PPPK jika digit 13-14 adalah 21/kontrak atau sesuai pola)
            $isPppk = false;
            if (strlen($nip) >= 14 && substr($nip, 12, 2) === '21') {
                $isPppk = true;
            }

            // Hitung TMT CPNS / Pengangkatan dari NIP (digit 9-14: YYYYMM)
            $tmtCpnsDate = Carbon::parse('2020-01-01'); // fallback default
            if (strlen($nip) >= 14) {
                $tahunMasuk = substr($nip, 8, 4);
                $bulanMasuk = substr($nip, 12, 2);
                if (is_numeric($tahunMasuk)) {
                    $bulanInt = (int)$bulanMasuk;
                    if ($bulanInt >= 1 && $bulanInt <= 12) {
                        $tmtCpnsDate = Carbon::createFromDate((int)$tahunMasuk, $bulanInt, 1)->startOfMonth();
                    } else {
                        $tmtCpnsDate = Carbon::createFromDate((int)$tahunMasuk, 1, 1)->startOfMonth();
                    }
                }
            }

            // Aturan Role Sistem & Izin Sementara
            $role = 'pegawai';
            $bisaBeriIzinSementara = false;

            // Jika KASUBBAG Umum & Kepegawaian -> Admin Kepegawaian
            if (Str::contains(strtoupper($jabatan), ['KASUBBAG', 'UMUM AND KEPEGAWAIAN', 'KEPALA SUB BAGIAN'])) {
                $role = 'admin_cuti';
            }

            // Jika Sekretaris / Inspektur / Irban -> Bisa memberi izin darurat sementara
            if (Str::contains(strtoupper($jabatan), ['SECRETARIS', 'SEKRETARIS', 'INSPEKTUR', 'IRBAN'])) {
                $bisaBeriIzinSementara = true;
            }

            // 1. Buat/Update User
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $namaLengkap,
                    'password' => bcrypt('password'), // password default
                    'role' => $role,
                    'bisa_beri_izin_sementara' => $bisaBeriIzinSementara
                ]
            );

            $pangkatGolongan = !empty($pangkat) ? "{$golongan} - {$pangkat}" : $golongan;

            // 2. Buat/Update Profil Pegawai
            $pegawai = Pegawai::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nip' => $nip,
                    'nama_lengkap' => $namaLengkap,
                    'jenis_kelamin' => $jenisKelamin,
                    'tmt_cpns' => $tmtCpnsDate,
                    'tmt_pns' => $isPppk ? null : $tmtCpnsDate->copy()->addYear(), // PNS diasumsikan +1 tahun dari CPNS
                    'pangkat_golongan' => $pangkatGolongan,
                    'jabatan' => $jabatan,
                    'unit_kerja_id' => $unitKerjaId,
                    'jenis_pegawai' => $isPppk ? 'PPPK' : 'PNS',
                    'nomor_hp' => $nomorHp,
                    'aktif' => true
                ]
            );

            // 3. Inisialisasi Saldo Cuti Tahunan awal (Tahun Berjalan = 12 hari, Carry Over = 6 hari)
            CutiSaldoTahunan::updateOrCreate(
                ['pegawai_id' => $pegawai->id, 'tahun' => now()->year],
                [
                    'jatah_tahun_berjalan' => 12,
                    'carry_over_n1' => 6,
                    'carry_over_n2' => 0,
                    'tambahan_cuti_bersama' => 0,
                    'terpakai' => 0,
                    'jatah_dibekukan' => false,
                    'ditangguhkan' => false
                ]
            );

            // Klasifikasi pimpinan untuk relasi atasan nanti
            if (Str::contains(strtoupper($jabatan), 'INSPEKTUR') && !Str::contains(strtoupper($jabatan), 'PEMBANTU')) {
                $pimpinanList['inspektur'] = $pegawai;
            } elseif (Str::contains(strtoupper($jabatan), 'SEKRETARIS')) {
                $pimpinanList['sekretaris'] = $pegawai;
            } elseif (Str::contains(strtoupper($jabatan), 'KASUBBAG')) {
                $pimpinanList['kasubbag'] = $pegawai;
            } elseif (Str::contains(strtoupper($jabatan), 'IRBAN I') || (Str::contains(strtoupper($bidang), 'IRBAN I') && Str::contains(strtoupper($jabatan), 'AHLI MADYA'))) {
                $pimpinanList['irban_1'] = $pegawai;
            } elseif (Str::contains(strtoupper($jabatan), 'IRBAN II') || (Str::contains(strtoupper($bidang), 'IRBAN II') && Str::contains(strtoupper($jabatan), 'AHLI MADYA'))) {
                $pimpinanList['irban_2'] = $pegawai;
            } elseif (Str::contains(strtoupper($jabatan), 'IRBAN III') || (Str::contains(strtoupper($bidang), 'IRBAN III') && Str::contains(strtoupper($jabatan), 'AHLI MADYA'))) {
                $pimpinanList['irban_3'] = $pegawai;
            } elseif (Str::contains(strtoupper($jabatan), 'IRBAN IV') || (Str::contains(strtoupper($bidang), 'IRBAN IV') && Str::contains(strtoupper($jabatan), 'AHLI MADYA'))) {
                $pimpinanList['irban_4'] = $pegawai;
            }

            $pegawaiList[] = $pegawai;
        }

        fclose($file);

        // 6. Pemetaan Atasan Langsung secara Dinamis berdasarkan Struktur Organisasi
        $inspektur = $pimpinanList['inspektur'] ?? ($pimpinanList['sekretaris'] ?? null);
        $sekretaris = $pimpinanList['sekretaris'] ?? null;
        $kasubbag = $pimpinanList['kasubbag'] ?? null;
        
        foreach ($pegawaiList as $peg) {
            $atasanId = null;

            // Jika dia adalah pimpinan tertinggi (Inspektur) -> Atasannya langsung adalah Bupati (kita set null/sistem)
            if ($inspektur && $peg->id === $inspektur->id) {
                continue;
            }

            // Jika dia adalah Sekretaris -> Atasannya adalah Inspektur
            if ($sekretaris && $peg->id === $sekretaris->id) {
                $atasanId = $inspektur?->id;
            }
            // Jika dia adalah salah satu dari Irban -> Atasannya adalah Sekretaris
            elseif (Str::contains(strtoupper($peg->jabatan), 'IRBAN')) {
                $atasanId = $sekretaris?->id ?? $inspektur?->id;
            }
            // Jika dia adalah Kasubbag -> Atasannya adalah Sekretaris
            elseif ($kasubbag && $peg->id === $kasubbag->id) {
                $atasanId = $sekretaris?->id ?? $inspektur?->id;
            }
            // Jika dia adalah staf umum/kepegawaian di Sekretariat -> Atasannya adalah Kasubbag
            elseif ($peg->unit_kerja_id === $createdUnits['SEKRETARIAT']->id || $peg->unit_kerja_id === $createdUnits['SUB-KEPEG']->id) {
                $atasanId = $kasubbag?->id ?? ($sekretaris?->id ?? $inspektur?->id);
            }
            // Jika dia adalah staf auditor di Irban I -> Atasannya adalah Irban I
            elseif ($peg->unit_kerja_id === $createdUnits['IRBAN-I']->id) {
                $atasanId = $pimpinanList['irban_1']?->id ?? ($sekretaris?->id ?? $inspektur?->id);
            }
            // Jika dia adalah staf auditor di Irban II -> Atasannya adalah Irban II
            elseif ($peg->unit_kerja_id === $createdUnits['IRBAN-II']->id) {
                $atasanId = $pimpinanList['irban_2']?->id ?? ($sekretaris?->id ?? $inspektur?->id);
            }
            // Jika dia adalah staf auditor di Irban III -> Atasannya adalah Irban III
            elseif ($peg->unit_kerja_id === $createdUnits['IRBAN-III']->id) {
                $atasanId = $pimpinanList['irban_3']?->id ?? ($sekretaris?->id ?? $inspektur?->id);
            }
            // Jika dia adalah staf auditor di Irban IV -> Atasannya adalah Irban IV
            elseif ($peg->unit_kerja_id === $createdUnits['IRBAN-IV']->id) {
                $atasanId = $pimpinanList['irban_4']?->id ?? ($sekretaris?->id ?? $inspektur?->id);
            }

            if ($atasanId) {
                CutiPemetaanAtasan::updateOrCreate(
                    ['pegawai_id' => $peg->id],
                    [
                        'atasan_id' => $atasanId,
                        'berlaku_mulai' => '2026-01-01',
                        'berlaku_sampai' => null
                    ]
                );
            }
        }

        // 7. Pemetaan Pejabat Berwenang (PyBMC) untuk Cuti Tahunan & Sakit
        // Inspektur/Sekretaris (pimpinan tertinggi) didelegasikan wewenang PyBMC untuk seluruh Unit Kerja
        $pybmc = $inspektur ?? ($sekretaris ?? null);
        if ($pybmc) {
            $tahunan = CutiJenis::where('kode', CutiJenis::TAHUNAN)->first();
            $sakit = CutiJenis::where('kode', CutiJenis::SAKIT)->first();

            foreach ($createdUnits as $unit) {
                foreach ([$tahunan->id, $sakit->id] as $jenisId) {
                    CutiPemetaanPejabatBerwenang::updateOrCreate(
                        [
                            'unit_kerja_id' => $unit->id,
                            'jenis_cuti_id' => $jenisId,
                        ],
                        [
                            'pejabat_id' => $pybmc->id,
                            'nomor_sk_delegasi' => 'SK-800/12/406.012/2026',
                            'berlaku_mulai' => '2026-01-01',
                            'berlaku_sampai' => null
                        ]
                    );
                }
            }
        }
    }
}

/**
 * Helper check numeric.
 */
function numeric_check($value) {
    return preg_match('/^[0-9]+$/', $value);
}
