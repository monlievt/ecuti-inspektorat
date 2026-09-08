<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Pengajuan Cuti Pegawai</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h2 {
            margin: 0;
            font-size: 15px;
            text-transform: uppercase;
        }
        .header h3 {
            margin: 2px 0 0 0;
            font-size: 13px;
            font-weight: normal;
        }
        .meta {
            margin-bottom: 12px;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        th, td {
            border: 1px solid #94a3b8;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #f1f5f9;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer {
            margin-top: 25px;
            float: right;
            width: 250px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>PEMERINTAH KABUPATEN TRENGGALEK</h2>
        <h2>INSPEKTORAT DAERAH</h2>
        <h3>Laporan Rekapitulasi Pengajuan &amp; Pemanfaatan Cuti Pegawai</h3>
    </div>

    <div class="meta">
        <strong>Unit Kerja:</strong> {{ $unitKerja ? $unitKerja->nama : 'Seluruh Unit Kerja / Bidang' }} |
        <strong>Jenis Cuti:</strong> {{ $jenisCuti ? $jenisCuti->nama : 'Semua Jenis Cuti' }} |
        <strong>Periode:</strong> {{ $tanggalMulai ? date('d/m/Y', strtotime($tanggalMulai)) : 'Awal' }} s/d {{ $tanggalSelesai ? date('d/m/Y', strtotime($tanggalSelesai)) : 'Sekarang' }} |
        <strong>Tanggal Cetak:</strong> {{ $tanggalCetak }}
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" width="4%">No</th>
                <th width="18%">Pegawai / NIP</th>
                <th width="15%">Unit Kerja</th>
                <th width="14%">Jenis Cuti</th>
                <th width="18%">Periode Pelaksanaan</th>
                <th class="text-center" width="8%">Durasi</th>
                <th width="13%">No. Surat / Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pengajuanList as $index => $p)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $p->pegawai->nama_lengkap }}</strong><br>
                        NIP. {{ $p->pegawai->nip }} ({{ $p->pegawai->jenis_pegawai }})
                    </td>
                    <td>{{ $p->pegawai->unitKerja?->nama ?? '-' }}</td>
                    <td>{{ $p->jenisCuti->nama }}</td>
                    <td>
                        {{ $p->tanggal_mulai->format('d/m/Y') }} s/d {{ $p->tanggal_selesai->format('d/m/Y') }}
                    </td>
                    <td class="text-center">
                        {{ $p->jumlah_hari }} {{ str_replace('_', ' ', $p->satuan_hari) }}
                    </td>
                    <td>
                        @if($p->suratTerbit)
                            <strong style="color: #059669;">{{ $p->suratTerbit->nomor_surat }}</strong><br>
                        @endif
                        <span style="font-size: 9px; color: #475569;">{{ ucwords(str_replace('_', ' ', $p->status)) }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px;">Tidak ada data cuti untuk kriteria ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Trenggalek, {{ $tanggalCetak }}</p>
        <p style="margin-top: -5px;"><strong>Kepala Subbagian Umum dan Kepegawaian</strong></p>
        <br><br><br>
        <p><u><strong>NUGRAHENI RAHAYU S, SE, M.Si</strong></u><br>Pembina / IV a<br>NIP. 197211141994022001</p>
    </div>
</body>
</html>
