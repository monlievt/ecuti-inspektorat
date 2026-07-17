<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Cuti - {{ $pengajuan->nomor_pengajuan }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.3;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .header-table td {
            vertical-align: top;
        }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            margin-top: 15px;
            margin-bottom: 15px;
            text-decoration: underline;
        }
        .section-title {
            font-weight: bold;
            background-color: #f2f2f2;
            padding: 4px 8px;
            border: 1px solid #000;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #000;
            padding: 5px 8px;
            vertical-align: top;
            font-size: 10pt;
        }
        .checkbox-cell {
            text-align: center;
            width: 35px;
            font-weight: bold;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .signature-table td {
            padding: 5px;
            font-size: 10pt;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .uppercase {
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    <!-- Header Dokumen BKN -->
    <table class="header-table">
        <tr>
            <td style="width: 60%;"></td>
            <td style="width: 40%; font-size: 9pt;">
                Trenggalek, {{ $tanggalSurat }}<br>
                Kepada Yth.<br>
                {{ $approvalPybmc ? $approvalPybmc->aktor->name : 'Inspektur Kabupaten Trenggalek' }}<br>
                di -<br>
                <span style="font-weight: bold;">TRENGGALEK</span>
            </td>
        </tr>
    </table>

    <div class="title">FORMULIR PERMINTAAN DAN PEMBERIAN CUTI</div>

    <!-- I. DATA PEGAWAI -->
    <div class="section-title">I. DATA PEGAWAI</div>
    <table class="data-table">
        <tr>
            <td style="width: 15%;">Nama</td>
            <td style="width: 35%;">{{ $pegawai->nama_lengkap }}</td>
            <td style="width: 15%;">NIP</td>
            <td style="width: 35%;">{{ $pegawai->nip }}</td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>{{ $pegawai->jabatan }}</td>
            <td>Masa Kerja</td>
            <td>
                @php
                    $years = floor($pegawai->masa_kerja_bulan / 12);
                    $months = $pegawai->masa_kerja_bulan % 12;
                @endphp
                {{ $years }} Tahun {{ $months }} Bulan
            </td>
        </tr>
        <tr>
            <td>Unit Kerja</td>
            <td colspan="3">{{ $pegawai->unitKerja->nama }}</td>
        </tr>
    </table>

    <!-- II. JENIS CUTI YANG DIAMBIL -->
    <div class="section-title">II. JENIS CUTI YANG DIAMBIL</div>
    <table class="data-table">
        <tr>
            <td style="width: 40%;">1. Cuti Tahunan</td>
            <td class="checkbox-cell">{{ $jenisCuti->kode === 'tahunan' ? '✓' : '' }}</td>
            <td style="width: 40%;">2. Cuti Besar</td>
            <td class="checkbox-cell">{{ $jenisCuti->kode === 'besar' ? '✓' : '' }}</td>
        </tr>
        <tr>
            <td>3. Cuti Sakit</td>
            <td class="checkbox-cell">{{ $jenisCuti->kode === 'sakit' ? '✓' : '' }}</td>
            <td>4. Cuti Melahirkan</td>
            <td class="checkbox-cell">{{ $jenisCuti->kode === 'melahirkan' ? '✓' : '' }}</td>
        </tr>
        <tr>
            <td>5. Cuti Karena Alasan Penting</td>
            <td class="checkbox-cell">{{ $jenisCuti->kode === 'alasan_penting' ? '✓' : '' }}</td>
            <td>6. Cuti Di Luar Tanggungan Negara</td>
            <td class="checkbox-cell">{{ $jenisCuti->kode === 'cltn' ? '✓' : '' }}</td>
        </tr>
    </table>

    <!-- III. ALASAN CUTI -->
    <div class="section-title">III. ALASAN CUTI</div>
    <table class="data-table">
        <tr>
            <td style="height: 40px;">{{ $pengajuan->alasan }}</td>
        </tr>
    </table>

    <!-- IV. LAMANYA CUTI -->
    <div class="section-title">IV. LAMANYA CUTI</div>
    <table class="data-table">
        <tr>
            <td style="width: 30%;">Selama</td>
            <td style="width: 70%;">
                {{ $pengajuan->jumlah_hari_kerja }} ({{ $satuanLabel }})
                &nbsp;&nbsp;Mulai Tanggal: {{ $tanggalMulai }} s.d {{ $tanggalSelesai }}
            </td>
        </tr>
    </table>

    <!-- V. CATATAN CUTI -->
    <div class="section-title">V. CATATAN CUTI</div>
    <table class="data-table">
        <tr>
            <td style="width: 50%; font-weight: bold;">1. CUTI TAHUNAN</td>
            <td style="width: 50%; font-weight: bold;">2. CUTI BESAR</td>
        </tr>
        <tr>
            <td>
                Tahun N-2: {{ $saldoBreakdown['carry_over_n2'] }} Hari<br>
                Tahun N-1: {{ $saldoBreakdown['carry_over_n1'] }} Hari<br>
                Tahun N (Berjalan): {{ $saldoBreakdown['jatah_tahun_berjalan'] }} Hari<br>
                <span style="font-weight: bold;">Total Sisa Saldo: {{ $saldoBreakdown['sisa'] }} Hari</span>
            </td>
            <td>
                Masa kerja memenuhi syarat: {{ $pegawai->tmt_cpns->diffInYears(now()) >= 5 ? 'Ya' : 'Tidak' }}
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold;">3. CUTI SAKIT</td>
            <td style="font-weight: bold;">4. CUTI MELAHIRKAN</td>
        </tr>
        <tr>
            <td>-</td>
            <td>Anak ke-1 s.d 3 memenuhi</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">5. CUTI ALASAN PENTING</td>
            <td style="font-weight: bold;">6. CUTI DI LUAR TANGGUNGAN NEGARA</td>
        </tr>
        <tr>
            <td>-</td>
            <td>-</td>
        </tr>
    </table>

    <!-- VI. ALAMAT SELAMA MENJALANKAN CUTI -->
    <div class="section-title">VI. ALAMAT SELAMA MENJALANKAN CUTI</div>
    <table class="data-table">
        <tr>
            <td style="width: 50%;">
                Alamat: {{ $pengajuan->alamat_selama_cuti ?: '-' }}
            </td>
            <td style="width: 50%;">
                Telepon: {{ $pengajuan->telp_selama_cuti ?: '-' }}
                <br><br>
                <div class="text-center" style="margin-top: 15px;">
                    Hormat Pemohon,<br><br>
                    <span style="font-size: 8pt; color: #555;">[Ditandatangani Secara Elektronik]</span><br>
                    <span style="font-weight: bold; text-decoration: underline;">{{ $pegawai->nama_lengkap }}</span><br>
                    NIP. {{ $pegawai->nip }}
                </div>
            </td>
        </tr>
    </table>

    <!-- VII. PERTIMBANGAN ATASAN LANGSUNG -->
    <div class="section-title">VII. PERTIMBANGAN ATASAN LANGSUNG</div>
    <table class="data-table">
        <tr>
            <td style="width: 50%;">
                [ {{ $pengajuan->status !== 'ditolak_atasan' && $pengajuan->status !== 'direvisi' ? '✓' : ' ' }} ] DISETUJUI<br>
                [ {{ $pengajuan->status === 'direvisi' ? '✓' : ' ' }} ] MINTA REVISI<br>
                [ {{ $pengajuan->status === 'ditolak_atasan' ? '✓' : ' ' }} ] TIDAK DISETUJUI
            </td>
            <td style="width: 50%;">
                Catatan: {{ $approvalAtasan?->catatan ?: '-' }}
                <br><br>
                <div class="text-center" style="margin-top: 15px;">
                    Atasan Langsung,<br><br>
                    @if($approvalAtasan)
                        <span style="font-size: 8pt; color: #555;">[Disetujui Elektronik: {{ $approvalAtasan->created_at->format('d/m/Y') }}]</span><br>
                        <span style="font-weight: bold; text-decoration: underline;">{{ $approvalAtasan->aktor->name }}</span>
                    @else
                        <br>
                        (.......................................................)
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI -->
    <div class="section-title">VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI</div>
    <table class="data-table">
        <tr>
            <td style="width: 50%;">
                [ {{ $pengajuan->status === 'diterbitkan' ? '✓' : ' ' }} ] DISETUJUI<br>
                [ {{ $pengajuan->status === 'ditangguhkan_pyBMC' ? '✓' : ' ' }} ] DITANGGUHKAN<br>
                [ {{ $pengajuan->status === 'ditolak_pyBMC' ? '✓' : ' ' }} ] TIDAK DISETUJUI
            </td>
            <td style="width: 50%;">
                Catatan: {{ $approvalPybmc?->catatan ?: '-' }}
                <br><br>
                <div class="text-center" style="margin-top: 15px;">
                    Pejabat Yang Berwenang Memberikan Cuti,<br><br>
                    @if($approvalPybmc)
                        <span style="font-size: 8pt; color: #555;">[Disetujui Elektronik: {{ $approvalPybmc->created_at->format('d/m/Y') }}]</span><br>
                        <span style="font-weight: bold; text-decoration: underline;">{{ $approvalPybmc->aktor->name }}</span>
                    @else
                        <br>
                        (.......................................................)
                    @endif
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
