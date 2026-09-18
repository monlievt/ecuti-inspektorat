<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Formulir Cuti - {{ $pengajuan->nomor_pengajuan }}</title>
    <style>
        @page {
            size: 215mm 330mm;
            margin: 8mm 12mm 8mm 12mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 9.5pt;
            line-height: 1.25;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .header-table td {
            vertical-align: top;
        }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            margin-top: 4px;
            margin-bottom: 6px;
            text-decoration: underline;
        }
        .section-title {
            font-weight: bold;
            font-size: 9pt;
            background-color: #f2f2f2;
            padding: 2.5px 6px;
            border: 1px solid #000;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #000;
            padding: 2.5px 5px;
            vertical-align: top;
            font-size: 9pt;
        }
        .checkbox-cell {
            text-align: center;
            width: 32px;
            font-weight: bold;
            vertical-align: middle;
        }
        .check {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10.5pt;
            font-weight: bold;
            color: #000;
            line-height: 1;
        }
        .check-bracket {
            font-family: 'Times New Roman', Times, serif;
            font-weight: bold;
        }
        .signature-box {
            text-align: center;
            margin-top: 4px;
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
        .keep-together {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>

    <!-- Header Dokumen BKN -->
    <table class="header-table">
        <tr>
            <td style="width: 58%;"></td>
            <td style="width: 42%; font-size: 8.5pt;">
                Trenggalek, {{ $tanggalSurat }}<br>
                Kepada Yth.<br>
                {{ $tujuanSurat ?? ($approvalPybmc ? $approvalPybmc->aktor->name : 'Inspektur Kabupaten Trenggalek') }}<br>
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
                    $years = $pegawai->tmt_cpns ? floor($pegawai->masa_kerja_bulan / 12) : 0;
                    $months = $pegawai->tmt_cpns ? ($pegawai->masa_kerja_bulan % 12) : 0;
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
            <td style="width: 42%;">1. Cuti Tahunan</td>
            <td class="checkbox-cell">{!! $jenisCuti->kode === 'tahunan' ? '<span class="check">&#10003;</span>' : '&nbsp;' !!}</td>
            <td style="width: 42%;">2. Cuti Besar</td>
            <td class="checkbox-cell">{!! $jenisCuti->kode === 'besar' ? '<span class="check">&#10003;</span>' : '&nbsp;' !!}</td>
        </tr>
        <tr>
            <td>3. Cuti Sakit</td>
            <td class="checkbox-cell">{!! $jenisCuti->kode === 'sakit' ? '<span class="check">&#10003;</span>' : '&nbsp;' !!}</td>
            <td>4. Cuti Melahirkan</td>
            <td class="checkbox-cell">{!! $jenisCuti->kode === 'melahirkan' ? '<span class="check">&#10003;</span>' : '&nbsp;' !!}</td>
        </tr>
        <tr>
            <td>5. Cuti Karena Alasan Penting</td>
            <td class="checkbox-cell">{!! $jenisCuti->kode === 'alasan_penting' ? '<span class="check">&#10003;</span>' : '&nbsp;' !!}</td>
            <td>6. Cuti Di Luar Tanggungan Negara</td>
            <td class="checkbox-cell">{!! $jenisCuti->kode === 'cltn' ? '<span class="check">&#10003;</span>' : '&nbsp;' !!}</td>
        </tr>
    </table>

    <!-- III. ALASAN CUTI -->
    <div class="section-title">III. ALASAN CUTI</div>
    <table class="data-table">
        <tr>
            <td style="min-height: 25px; padding: 4px 6px;">{{ $pengajuan->alasan }}</td>
        </tr>
    </table>

    <!-- IV. LAMANYA CUTI -->
    <div class="section-title">IV. LAMANYA CUTI</div>
    <table class="data-table">
        <tr>
            <td style="width: 25%;">Selama</td>
            <td style="width: 75%;">
                {{ $pengajuan->jumlah_hari_kerja }} ({{ $durasiTerbilang }}) {{ $satuanLabel }}
                &nbsp;&nbsp;&nbsp;&nbsp;Mulai Tanggal: {{ $tanggalMulai }} s.d {{ $tanggalSelesai }}
            </td>
        </tr>
    </table>

    <!-- V. CATATAN CUTI -->
    <div class="section-title">V. CATATAN CUTI</div>
    <table class="data-table">
        <tr>
            <td style="width: 50%; font-weight: bold; background-color: #fafafa;">1. CUTI TAHUNAN</td>
            <td style="width: 50%; font-weight: bold; background-color: #fafafa;">2. CUTI BESAR</td>
        </tr>
        <tr>
            <td style="padding: 2px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr style="background-color: #f7f7f7;">
                        <th style="border: 1px solid #000; padding: 2px 4px; text-align: left; font-size: 8.5pt;">Tahun</th>
                        <th style="border: 1px solid #000; padding: 2px 4px; text-align: center; font-size: 8.5pt; width: 60px;">Sisa Hak</th>
                        <th style="border: 1px solid #000; padding: 2px 4px; text-align: left; font-size: 8.5pt;">Keterangan</th>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 2px 4px; font-size: 8.5pt;">N-2 ({{ $detailSaldo['tahun_n2'] }})</td>
                        <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; font-size: 8.5pt;">{{ $detailSaldo['n2']['sisa_sebelum'] }} Hari</td>
                        <td style="border: 1px solid #000; padding: 2px 4px; font-size: 8pt;">
                            @if($detailSaldo['n2']['potong'] > 0)
                                Dipotong {{ $detailSaldo['n2']['potong'] }} hari (Sisa: {{ $detailSaldo['n2']['sisa_akhir'] }} hr)
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 2px 4px; font-size: 8.5pt;">N-1 ({{ $detailSaldo['tahun_n1'] }})</td>
                        <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; font-size: 8.5pt;">{{ $detailSaldo['n1']['sisa_sebelum'] }} Hari</td>
                        <td style="border: 1px solid #000; padding: 2px 4px; font-size: 8pt;">
                            @if($detailSaldo['n1']['potong'] > 0)
                                Dipotong {{ $detailSaldo['n1']['potong'] }} hari (Sisa: {{ $detailSaldo['n1']['sisa_akhir'] }} hr)
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 2px 4px; font-size: 8.5pt;">N ({{ $detailSaldo['tahun_n'] }})</td>
                        <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; font-size: 8.5pt;">{{ $detailSaldo['n']['sisa_sebelum'] }} Hari</td>
                        <td style="border: 1px solid #000; padding: 2px 4px; font-size: 8pt;">
                            @if($detailSaldo['n']['potong'] > 0)
                                Dipotong {{ $detailSaldo['n']['potong'] }} hari (Sisa: {{ $detailSaldo['n']['sisa_akhir'] }} hr)
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr style="font-weight: bold; background-color: #f2f2f2;">
                        <td style="border: 1px solid #000; padding: 2px 4px; font-size: 8.5pt;">Total Sisa Saldo</td>
                        <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; font-size: 8.5pt;">{{ $detailSaldo['total_sisa_sebelum'] }} Hari</td>
                        <td style="border: 1px solid #000; padding: 2px 4px; font-size: 8pt;">Saldo sebelum permohonan ini</td>
                    </tr>
                    @if($jenisCuti->kode === 'tahunan')
                    <tr style="color: #900; font-weight: bold;">
                        <td style="border: 1px solid #000; padding: 2px 4px; font-size: 8.5pt;">Cuti Yang Diambil</td>
                        <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; font-size: 8.5pt;">{{ $pengajuan->jumlah_hari_kerja }} Hari</td>
                        <td style="border: 1px solid #000; padding: 2px 4px; font-size: 8pt;">{{ $detailSaldo['sudah_dipotong'] ? 'Telah disetujui & memotong saldo' : 'Dipotong jika disetujui' }}</td>
                    </tr>
                    <tr style="font-weight: bold; background-color: #eaf2f8;">
                        <td style="border: 1px solid #000; padding: 2px 4px; font-size: 8.5pt;">Sisa Saldo Akhir</td>
                        <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; font-size: 8.5pt;">{{ $detailSaldo['total_sisa_akhir'] }} Hari</td>
                        <td style="border: 1px solid #000; padding: 2px 4px; font-size: 8pt;">Sisa hak cuti tahunan aktif</td>
                    </tr>
                    @endif
                </table>
            </td>
            <td style="padding: 4px 6px;">
                Masa kerja memenuhi syarat: {{ ($pegawai->tmt_cpns && $pegawai->tmt_cpns->diffInYears(now()) >= 5) ? 'Ya' : 'Tidak' }}
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold; background-color: #fafafa;">3. CUTI SAKIT</td>
            <td style="font-weight: bold; background-color: #fafafa;">4. CUTI MELAHIRKAN</td>
        </tr>
        <tr>
            <td style="padding: 4px 6px;">{{ $jenisCuti->kode === 'sakit' ? ($pengajuan->jumlah_hari_kerja . ' Hari (Permohonan ini)') : '-' }}</td>
            <td style="padding: 4px 6px;">{{ $jenisCuti->kode === 'melahirkan' ? ($pengajuan->jumlah_hari_kerja . ' Hari (Permohonan ini)') : 'Anak ke-1 s.d 3 memenuhi' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; background-color: #fafafa;">5. CUTI ALASAN PENTING</td>
            <td style="font-weight: bold; background-color: #fafafa;">6. CUTI DI LUAR TANGGUNGAN NEGARA</td>
        </tr>
        <tr>
            <td style="padding: 4px 6px;">{{ $jenisCuti->kode === 'alasan_penting' ? ($pengajuan->jumlah_hari_kerja . ' Hari (Permohonan ini)') : '-' }}</td>
            <td style="padding: 4px 6px;">{{ $jenisCuti->kode === 'cltn' ? ($pengajuan->jumlah_hari_kerja . ' Hari (Permohonan ini)') : '-' }}</td>
        </tr>
    </table>

    <!-- VI. ALAMAT SELAMA MENJALANKAN CUTI -->
    <div class="section-title">VI. ALAMAT SELAMA MENJALANKAN CUTI</div>
    <table class="data-table">
        <tr>
            <td style="width: 50%; padding: 4px 6px;">
                Alamat: {{ $pengajuan->alamat_selama_cuti ?: '-' }}
            </td>
            <td style="width: 50%; padding: 4px 6px;">
                Telepon: {{ $pengajuan->telp_selama_cuti ?: '-' }}
                <div class="signature-box" style="margin-top: 6px;">
                    Hormat Pemohon,<br>
                    <span style="font-size: 7.5pt; color: #555;">[Ditandatangani Secara Elektronik]</span><br>
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
            <td style="width: 50%; padding: 4px 6px; line-height: 1.4;">
                <span class="check-bracket">[{!! $isAtasanSetuju ? '<span class="check">&#10003;</span>' : '&nbsp;&nbsp;' !!}]</span> DISETUJUI<br>
                <span class="check-bracket">[{!! $isAtasanRevisi ? '<span class="check">&#10003;</span>' : '&nbsp;&nbsp;' !!}]</span> MINTA REVISI<br>
                <span class="check-bracket">[{!! $isAtasanTolak ? '<span class="check">&#10003;</span>' : '&nbsp;&nbsp;' !!}]</span> TIDAK DISETUJUI
            </td>
            <td style="width: 50%; padding: 4px 6px;">
                Catatan: {{ $approvalAtasan?->catatan ?: '-' }}
                <div class="signature-box" style="margin-top: 3px;">
                    @if(!empty($isInspektur))
                        Sekretaris Daerah Kabupaten Trenggalek,<br><br><br>
                        (.......................................................)
                    @elseif($approvalAtasan)
                        Atasan Langsung,<br>
                        <span style="font-size: 7.5pt; color: #555;">[Disetujui Elektronik: {{ $approvalAtasan->created_at->format('d/m/Y') }}]</span><br>
                        <span style="font-weight: bold; text-decoration: underline;">{{ $atasanNama ?? $approvalAtasan->aktor->name }}</span><br>
                        @if(!empty($atasanNip))
                            NIP. {{ $atasanNip }}
                        @endif
                    @elseif($atasanNama)
                        Atasan Langsung,<br><br>
                        <span style="font-weight: bold; text-decoration: underline;">{{ $atasanNama }}</span><br>
                        @if(!empty($atasanNip))
                            NIP. {{ $atasanNip }}
                        @endif
                    @else
                        Atasan Langsung,<br><br>
                        (.......................................................)
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI -->
    <div class="keep-together">
        <div class="section-title">VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI</div>
        <table class="data-table">
            <tr>
                <td style="width: 50%; padding: 4px 6px; line-height: 1.4;">
                    <span class="check-bracket">[{!! $isPybmcSetuju ? '<span class="check">&#10003;</span>' : '&nbsp;&nbsp;' !!}]</span> DISETUJUI<br>
                    <span class="check-bracket">[{!! $isPybmcTangguh ? '<span class="check">&#10003;</span>' : '&nbsp;&nbsp;' !!}]</span> DITANGGUHKAN<br>
                    <span class="check-bracket">[{!! $isPybmcTolak ? '<span class="check">&#10003;</span>' : '&nbsp;&nbsp;' !!}]</span> TIDAK DISETUJUI
                </td>
                <td style="width: 50%; padding: 4px 6px;">
                    Catatan: {{ $approvalPybmc?->catatan ?: '-' }}
                    <div class="signature-box" style="margin-top: 3px;">
                        @if(!empty($isInspektur))
                            Bupati Trenggalek,<br><br><br>
                            (.......................................................)
                        @elseif($approvalPybmc)
                            Pejabat Yang Berwenang Memberikan Cuti,<br>
                            <span style="font-size: 7.5pt; color: #555;">[Disetujui Elektronik: {{ $approvalPybmc->created_at->format('d/m/Y') }}]</span><br>
                            <span style="font-weight: bold; text-decoration: underline;">{{ $pybmcNama ?? $approvalPybmc->aktor->name }}</span><br>
                            @if(!empty($pybmcNip))
                                NIP. {{ $pybmcNip }}
                            @endif
                        @elseif($pybmcNama)
                            Pejabat Yang Berwenang Memberikan Cuti,<br><br>
                            <span style="font-weight: bold; text-decoration: underline;">{{ $pybmcNama }}</span><br>
                            @if(!empty($pybmcNip))
                                NIP. {{ $pybmcNip }}
                            @endif
                        @else
                            Pejabat Yang Berwenang Memberikan Cuti,<br><br>
                            (.......................................................)
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
