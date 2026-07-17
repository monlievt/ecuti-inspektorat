<?php

/**
 * Konfigurasi Aturan Bisnis Cuti PNS
 *
 * Dasar hukum: Perka BKN No. 24 Tahun 2017 (diubah No. 7/2021)
 *
 * PENTING: Jangan hardcode angka-angka ini di controller atau service lain.
 * Semua referensi ke aturan BKN harus menggunakan config('cuti-rules.xxx').
 * Jika ada revisi Perka BKN, cukup ubah di file ini satu kali.
 */

return [

    // ─────────────────────────────────────────────────────────────────────────
    // CUTI TAHUNAN (§4.1 PRD)
    // ─────────────────────────────────────────────────────────────────────────
    'cuti_tahunan' => [
        'hak_tahunan_hari'        => 12,  // Hak per tahun
        'carry_over_max_1_tahun'  => 18,  // Total max saldo jika 1 tahun tidak pakai
        'carry_over_max_2_tahun'  => 24,  // Total max saldo jika 2 tahun tidak pakai
        'sisa_max_dibawa'         => 6,   // Max hari dari 1 tahun yang bisa carry ke berikutnya
        'syarat_masa_kerja_bulan' => 12,  // Min masa kerja terus-menerus (bulan)
        'tambahan_kalender_maks'  => 12,  // Max hari kalender tambahan jika lokasi terpencil
        'dokumen_wajib'           => [],
    ],

    // ─────────────────────────────────────────────────────────────────────────
    // CUTI BESAR (§4.2 PRD)
    // ─────────────────────────────────────────────────────────────────────────
    'cuti_besar' => [
        'syarat_masa_kerja_tahun'        => 5,   // Min masa kerja (tahun)
        'lama_maks_bulan'                => 3,   // Durasi maksimum
        'siklus_ulang_tahun'             => 5,   // Jeda sebelum bisa ajukan lagi
        'mengurangi_hak_tahunan'         => true, // Ya — tidak berhak cuti tahunan tahun berjalan
        'pengecualian_syarat_masa_kerja' => ['ibadah_haji_pertama'], // Bebas syarat 5 thn
        'tidak_bisa_ditangguhkan_untuk'  => ['ibadah_haji_pertama'], // Tidak bisa ditunda
        'dokumen_wajib'                  => [],
    ],

    // ─────────────────────────────────────────────────────────────────────────
    // CUTI SAKIT (§4.3 PRD)
    // ─────────────────────────────────────────────────────────────────────────
    'cuti_sakit' => [
        'ambang_perlu_dokter_hari'            => 1,   // > 1 hari wajib surat dokter
        'ambang_perlu_dokter_pemerintah_hari' => 14,  // > 14 hari wajib dokter pemerintah
        'lama_maks_tahun'                     => 1,   // Max durasi awal
        'perpanjangan_maks_bulan'             => 6,   // Bisa diperpanjang berdasarkan Tim Penguji
        'gugur_kandungan_maks_bulan'          => 1.5, // Khusus gugur kandungan
        'dokumen_wajib'                       => ['surat_keterangan_dokter'],
    ],

    // ─────────────────────────────────────────────────────────────────────────
    // CUTI MELAHIRKAN (§4.4 PRD)
    // ─────────────────────────────────────────────────────────────────────────
    'cuti_melahirkan' => [
        'lama_hari'       => 90,       // 3 bulan kalender
        'berlaku_anak_ke' => [1, 2, 3], // Anak ke-4+ → pakai skema Cuti Besar
        'satuan'          => 'hari_kalender',
        'dokumen_wajib'   => [],
    ],

    // ─────────────────────────────────────────────────────────────────────────
    // CUTI KARENA ALASAN PENTING (§4.5 PRD)
    // ─────────────────────────────────────────────────────────────────────────
    'cuti_alasan_penting' => [
        'lama_maks_bulan'          => 1,
        'satuan'                   => 'hari_kalender',
        'dokumen_wajib_per_alasan' => [
            'keluarga_sakit_keras'    => ['surat_rawat_inap'],
            'keluarga_meninggal'      => [],
            'menikah'                 => [],
            'istri_melahirkan_caesar' => ['surat_rawat_inap'],
            'musibah_bencana'         => ['surat_keterangan_rt'],
        ],
        'ada_jalur_izin_sementara' => true, // §6.3 PRD — jalur darurat
    ],

    // ─────────────────────────────────────────────────────────────────────────
    // CUTI BERSAMA (§4.6 PRD)
    // ─────────────────────────────────────────────────────────────────────────
    'cuti_bersama' => [
        'dikelola_admin'              => true,  // Input oleh Admin dari Keppres
        'mengurangi_cuti_tahunan'     => false, // Tidak mengurangi hak tahunan
        'pengecualian_piket_menambah' => true,  // Pegawai piket yang dikecualikan → +1 hari tahunan
        'tambahan_hanya_tahun_berjalan' => true, // Tambahan tidak carry-over
        'dokumen_wajib'               => [],
    ],

    // ─────────────────────────────────────────────────────────────────────────
    // CUTI DI LUAR TANGGUNGAN NEGARA / CLTN (§4.7 PRD)
    // ─────────────────────────────────────────────────────────────────────────
    'cltn' => [
        'syarat_masa_kerja_tahun' => 5,
        'lama_maks_tahun'         => 3,
        'perpanjangan_maks_tahun' => 1,
        'butuh_persetujuan_bkn'   => true,    // Wajib surat ke BKN, tidak bisa didelegasikan
        'tidak_dihitung_masa_kerja' => true,  // Masa CLTN tidak dihitung sebagai masa kerja
        'tidak_terima_penghasilan'  => true,
        'dokumen_wajib'           => ['surat_pendukung_alasan'],
        'reminder_sebelum_berakhir_bulan' => 3, // Kirim notif H-3 bulan sebelum berakhir
    ],

];
