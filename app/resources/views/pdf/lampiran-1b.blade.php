<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Formulir Cuti - {{ $pengajuan->nomor_pengajuan }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 4mm 7mm 4mm 7mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 7pt;
            line-height: 1.15;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .header-table td {
            vertical-align: top;
            font-size: 6.8pt;
            line-height: 1.15;
        }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 8.5pt;
            margin-top: 2px;
            margin-bottom: 5px;
            text-decoration: underline;
            letter-spacing: 0.5px;
        }
        .section-title {
            font-weight: bold;
            font-size: 7.2pt;
            background-color: #f2f2f2;
            padding: 1.5px 4px;
            border: 0.5pt solid #000;
            margin-top: 6px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        table.data-table th, table.data-table td {
            border: 0.5pt solid #000;
            padding: 1.5px 3.5px;
            vertical-align: top;
            font-size: 6.8pt;
        }
        .checkbox-cell {
            text-align: center;
            width: 24px;
            font-weight: bold;
            vertical-align: middle;
            padding: 0 !important;
        }
        .check {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8pt;
            font-weight: bold;
            color: #000;
            line-height: 1;
        }
        .signature-box {
            text-align: center;
            line-height: 1.15;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .footnote-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            font-size: 6.2pt;
            line-height: 1.15;
        }
        .footnote-table td {
            vertical-align: top;
            padding: 0.5px 1px;
        }
    </style>
</head>
<body>

    <!-- Header Dokumen BKN -->
    <table class="header-table">
        <tr>
            <td style="width: 48%;"></td>
            <td style="width: 52%;">
                ANAK LAMPIRAN 1.b<br>
                PERATURAN BADAN KEPEGAWAIAN NEGARA REPUBLIK INDONESIA<br>
                NOMOR 24 TAHUN 2017<br>
                TENTANG TATA CARA PEMBERIAN CUTI PEGAWAI NEGERI SIPIL<br><br>
                Trenggalek, {{ $tanggalSurat }}<br>
                Kepada<br>
                Yth. {!! $tujuanSurat !!}
            </td>
        </tr>
    </table>

    <div class="title">FORMULIR PERMINTAAN DAN PEMBERIAN CUTI</div>

    <!-- I. DATA PEGAWAI -->
    <div class="section-title">I. DATA PEGAWAI</div>
    <table class="data-table">
        <tr>
            <td style="width: 15%;">Nama</td>
            <td style="width: 35%; font-weight: bold;">{{ $pegawai->nama_lengkap }}</td>
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
    <div class="section-title">II. JENIS CUTI YANG DIAMBIL **</div>
    <table class="data-table">
        <tr>
            <td style="width: 43%;">1. Cuti Tahunan</td>
            <td class="checkbox-cell">{!! $jenisCuti->kode === 'tahunan' ? '<span class="check">&#10003;</span>' : '&nbsp;' !!}</td>
            <td style="width: 43%;">2. Cuti Besar</td>
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
            <td>6. Cuti di Luar Tanggungan Negara</td>
            <td class="checkbox-cell">{!! $jenisCuti->kode === 'cltn' ? '<span class="check">&#10003;</span>' : '&nbsp;' !!}</td>
        </tr>
    </table>

    <!-- III. ALASAN CUTI -->
    <div class="section-title">III. ALASAN CUTI</div>
    <table class="data-table">
        <tr>
            <td style="padding: 4px 6px; min-height: 48px; vertical-align: top;">
                {{ $pengajuan->alasan }}
                <br><br><br><br>
            </td>
        </tr>
    </table>

    <!-- IV. LAMANYA CUTI -->
    <div class="section-title">IV. LAMANYA CUTI</div>
    <table class="data-table">
        <tr>
            <td style="width: 12%;">Selama</td>
            <td style="width: 28%; font-weight: bold;">{{ $pengajuan->jumlah_hari_kerja }} ({{ $durasiTerbilang }}) {{ $satuanLabel }} *</td>
            <td style="width: 16%; text-align: center;">Mulai Tanggal</td>
            <td style="width: 20%; text-align: center;">{{ $tanggalMulai }}</td>
            <td style="width: 6%; text-align: center;">s/d</td>
            <td style="width: 18%; text-align: center;">{{ $tanggalSelesai }}</td>
        </tr>
    </table>

    <!-- V. CATATAN CUTI (Format BKN Asli: Kiri Cuti Tahunan, Kanan Cuti 2 s.d 6 Bersusun) -->
    <div class="section-title">V. CATATAN CUTI ***</div>
    <table class="data-table">
        <tr>
            <!-- Kolom Kiri: 1. Cuti Tahunan -->
            <td style="width: 50%; padding: 0; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td colspan="3" style="border-bottom: 0.5pt solid #000; padding: 1.5px 3px; text-align: left; font-size: 6.5pt;">
                            1. CUTI TAHUNAN
                        </td>
                    </tr>
                    <tr>
                        <td style="border-bottom: 0.5pt solid #000; border-right: 0.5pt solid #000; padding: 1px 2px; text-align: left; font-size: 6.5pt; width: 28%;">Tahun</td>
                        <td style="border-bottom: 0.5pt solid #000; border-right: 0.5pt solid #000; padding: 1px 2px; text-align: center; font-size: 6.5pt; width: 22%;">Sisa</td>
                        <td style="border-bottom: 0.5pt solid #000; padding: 1px 2px; text-align: left; font-size: 6.5pt; width: 50%;">Keterangan</td>
                    </tr>
                    <tr>
                        <td style="border-bottom: 0.5pt solid #000; border-right: 0.5pt solid #000; padding: 1px 2px; font-size: 6.5pt;">N-2 ({{ $detailSaldo['tahun_n2'] }})</td>
                        <td style="border-bottom: 0.5pt solid #000; border-right: 0.5pt solid #000; padding: 1px 2px; text-align: center; font-size: 6.5pt;">{{ $detailSaldo['n2']['sisa_akhir'] }} Hari</td>
                        <td style="border-bottom: 0.5pt solid #000; padding: 1px 2px; font-size: 6.2pt;">
                            {{ $detailSaldo['n2']['potong'] > 0 ? ('Sudah diambil ' . $detailSaldo['n2']['potong'] . ' hari') : '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border-bottom: 0.5pt solid #000; border-right: 0.5pt solid #000; padding: 1px 2px; font-size: 6.5pt;">N-1 ({{ $detailSaldo['tahun_n1'] }})</td>
                        <td style="border-bottom: 0.5pt solid #000; border-right: 0.5pt solid #000; padding: 1px 2px; text-align: center; font-size: 6.5pt;">{{ $detailSaldo['n1']['sisa_akhir'] }} Hari</td>
                        <td style="border-bottom: 0.5pt solid #000; padding: 1px 2px; font-size: 6.2pt;">
                            {{ $detailSaldo['n1']['potong'] > 0 ? ('Sudah diambil ' . $detailSaldo['n1']['potong'] . ' hari') : '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border-bottom: 0.5pt solid #000; border-right: 0.5pt solid #000; padding: 1px 2px; font-size: 6.5pt;">N ({{ $detailSaldo['tahun_n'] }})</td>
                        <td style="border-bottom: 0.5pt solid #000; border-right: 0.5pt solid #000; padding: 1px 2px; text-align: center; font-size: 6.5pt;">{{ $detailSaldo['n']['sisa_akhir'] }} Hari</td>
                        <td style="border-bottom: 0.5pt solid #000; padding: 1px 2px; font-size: 6.2pt;">
                            {{ $detailSaldo['n']['potong'] > 0 ? ('Sudah diambil ' . $detailSaldo['n']['potong'] . ' hari') : '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border-right: 0.5pt solid #000; padding: 1px 2px; font-size: 6.5pt;">Total Sisa</td>
                        <td style="border-right: 0.5pt solid #000; padding: 1px 2px; text-align: center; font-size: 6.5pt;">{{ $detailSaldo['total_sisa_akhir'] }} Hari</td>
                        <td style="padding: 1px 2px; font-size: 6.2pt;">Sisa cuti aktif</td>
                    </tr>
                </table>
            </td>

            <!-- Kolom Kanan: 2 s.d 6 Bersusun ke bawah -->
            <td style="width: 50%; padding: 0; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="border-bottom: 0.5pt solid #000; border-right: 0.5pt solid #000; padding: 1.5px 3px; font-size: 6.5pt; width: 68%;">2. CUTI BESAR</td>
                        <td style="border-bottom: 0.5pt solid #000; padding: 1.5px 3px; font-size: 6.5pt; text-align: center; width: 32%;">
                            {{ $jenisCuti->kode === 'besar' ? ($pengajuan->jumlah_hari_kerja . ' ' . $satuanLabel) : '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border-bottom: 0.5pt solid #000; border-right: 0.5pt solid #000; padding: 1.5px 3px; font-size: 6.5pt;">3. CUTI SAKIT</td>
                        <td style="border-bottom: 0.5pt solid #000; padding: 1.5px 3px; font-size: 6.5pt; text-align: center;">
                            {{ $jenisCuti->kode === 'sakit' ? ($pengajuan->jumlah_hari_kerja . ' ' . $satuanLabel) : '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border-bottom: 0.5pt solid #000; border-right: 0.5pt solid #000; padding: 1.5px 3px; font-size: 6.5pt;">4. CUTI MELAHIRKAN</td>
                        <td style="border-bottom: 0.5pt solid #000; padding: 1.5px 3px; font-size: 6.5pt; text-align: center;">
                            {{ $jenisCuti->kode === 'melahirkan' ? ($pengajuan->jumlah_hari_kerja . ' ' . $satuanLabel) : '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border-bottom: 0.5pt solid #000; border-right: 0.5pt solid #000; padding: 1.5px 3px; font-size: 6.5pt;">5. CUTI KARENA ALASAN PENTING</td>
                        <td style="border-bottom: 0.5pt solid #000; padding: 1.5px 3px; font-size: 6.5pt; text-align: center;">
                            {{ $jenisCuti->kode === 'alasan_penting' ? ($pengajuan->jumlah_hari_kerja . ' ' . $satuanLabel) : '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border-right: 0.5pt solid #000; padding: 1.5px 3px; font-size: 6.5pt;">6. CUTI DI LUAR TANGGUNGAN NEGARA</td>
                        <td style="padding: 1.5px 3px; font-size: 6.5pt; text-align: center;">
                            {{ $jenisCuti->kode === 'cltn' ? ($pengajuan->jumlah_hari_kerja . ' ' . $satuanLabel) : '-' }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- VI. ALAMAT SELAMA MENJALANKAN CUTI (Format BKN Asli) -->
    <div class="section-title">VI. ALAMAT SELAMA MENJALANKAN CUTI</div>
    <table class="data-table">
        <tr>
            <th style="width: 45%; text-align: center; font-size: 6.5pt; background-color: #fafafa;">Alamat Lengkap</th>
            <th style="width: 25%; text-align: center; font-size: 6.5pt; background-color: #fafafa;">Telpon</th>
            <th style="width: 30%; text-align: center; font-size: 6.5pt; background-color: #fafafa;">Hormat Saya,</th>
        </tr>
        <tr>
            <td style="padding: 3px 4px;">{{ $pengajuan->alamat_selama_cuti ?: '-' }}</td>
            <td style="padding: 3px 4px; text-align: center;">{{ $pengajuan->telp_selama_cuti ?: '-' }}</td>
            <td style="padding: 3px 4px; text-align: center;">
                <br><br><br><br>
                ( <span style="font-weight: bold; text-decoration: underline;">{{ $pegawai->nama_lengkap }}</span> )<br>
                NIP. {{ $pegawai->nip }}
            </td>
        </tr>
    </table>

    <!-- VII. PERTIMBANGAN ATASAN LANGSUNG (Format Kotak BKN Asli) -->
    <div class="section-title">VII. PERTIMBANGAN ATASAN LANGSUNG **</div>
    <table class="data-table">
        <tr style="text-align: center; background-color: #fafafa; font-weight: bold;">
            <td style="width: 15%; padding: 2px 1px;">DISETUJUI</td>
            <td style="width: 20%; padding: 2px 1px;">PERUBAHAN ****</td>
            <td style="width: 20%; padding: 2px 1px;">DITANGGUHKAN ****</td>
            <td style="width: 45%; padding: 2px 1px;">TIDAK DISETUJUI ****</td>
        </tr>
        <tr>
            <td style="text-align: center; font-size: 9pt; font-weight: bold; vertical-align: middle;">
                {!! $isAtasanSetuju ? '<span class="check">&#10003;</span>' : '&nbsp;' !!}
            </td>
            <td style="text-align: center; font-size: 9pt; font-weight: bold; vertical-align: middle;">
                {!! $isAtasanRevisi ? '<span class="check">&#10003;</span>' : '&nbsp;' !!}
            </td>
            <td style="text-align: center; font-size: 9pt; font-weight: bold; vertical-align: middle;">
                &nbsp;
            </td>
            <td style="padding: 2px 4px; vertical-align: top;">
                <div style="font-size: 6.2pt; color: #444; min-height: 10px;">
                    @if($isAtasanTolak)
                        <span class="check">&#10003;</span> Alasan: {{ $approvalAtasan?->catatan ?: '-' }}
                    @elseif($approvalAtasan?->catatan)
                        Catatan: {{ $approvalAtasan->catatan }}
                    @else
                        &nbsp;
                    @endif
                </div>
                <div class="signature-box" style="margin-top: 3px;">
                    Atasan Langsung,<br>
                    <br><br><br><br>
                    @if(!empty($isInspektur))
                        ( <span style="font-weight: bold;">Sekretaris Daerah Kabupaten Trenggalek</span> )<br>
                        NIP. .......................................................
                    @elseif(!empty($atasanNama))
                        ( <span style="font-weight: bold; text-decoration: underline;">{{ $atasanNama }}</span> )<br>
                        NIP. {{ $atasanNip ?: '.......................................................' }}
                    @else
                        ( ....................................................... )<br>
                        NIP. .......................................................
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI (Format Kotak BKN Asli) -->
    <div class="section-title">VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI **</div>
    <table class="data-table">
        <tr style="text-align: center; background-color: #fafafa; font-weight: bold;">
            <td style="width: 15%; padding: 2px 1px;">DISETUJUI</td>
            <td style="width: 20%; padding: 2px 1px;">PERUBAHAN ****</td>
            <td style="width: 20%; padding: 2px 1px;">DITANGGUHKAN ****</td>
            <td style="width: 45%; padding: 2px 1px;">TIDAK DISETUJUI ****</td>
        </tr>
        <tr>
            <td style="text-align: center; font-size: 9pt; font-weight: bold; vertical-align: middle;">
                {!! $isPybmcSetuju ? '<span class="check">&#10003;</span>' : '&nbsp;' !!}
            </td>
            <td style="text-align: center; font-size: 9pt; font-weight: bold; vertical-align: middle;">
                &nbsp;
            </td>
            <td style="text-align: center; font-size: 9pt; font-weight: bold; vertical-align: middle;">
                {!! $isPybmcTangguh ? '<span class="check">&#10003;</span>' : '&nbsp;' !!}
            </td>
            <td style="padding: 2px 4px; vertical-align: top;">
                <div style="font-size: 6.2pt; color: #444; min-height: 10px;">
                    @if($isPybmcTolak)
                        <span class="check">&#10003;</span> Alasan: {{ $approvalPybmc?->catatan ?: '-' }}
                    @elseif($approvalPybmc?->catatan)
                        Catatan: {{ $approvalPybmc->catatan }}
                    @else
                        &nbsp;
                    @endif
                </div>
                <div class="signature-box" style="margin-top: 3px;">
                    @if(!empty($isCutiKhususBkpsdm))
                        a.n. BUPATI TRENGGALEK<br>
                        Kepala BKPSDM Kabupaten Trenggalek,<br>
                        <br><br><br><br>
                        ( <span style="font-weight: bold; text-decoration: underline;">{{ $pybmcNama }}</span> )<br>
                        NIP. {{ $pybmcNip }}
                    @elseif(!empty($isInspektur))
                        Bupati Trenggalek,<br>
                        <br><br><br><br>
                        ( ....................................................... )<br>
                        NIP. .......................................................
                    @else
                        Inspektur Kabupaten Trenggalek,<br>
                        <br><br><br><br>
                        ( <span style="font-weight: bold; text-decoration: underline;">{{ $pybmcNama }}</span> )<br>
                        NIP. {{ $pybmcNip }}
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- Catatan Kaki BKN Resmi -->
    <table class="footnote-table">
        <tr>
            <td colspan="2" class="font-bold">Catatan :</td>
        </tr>
        <tr>
            <td style="width: 4%;">*</td>
            <td style="width: 96%;">Coret yang tidak perlu</td>
        </tr>
        <tr>
            <td>**</td>
            <td>Pilih salah satu dengan memberi tanda centang ( <span class="check" style="font-size: 7pt;">&#10003;</span> )</td>
        </tr>
        <tr>
            <td>***</td>
            <td>diisi oleh pejabat yang menangani bidang kepegawaian sebelum PNS mengajukan Cuti</td>
        </tr>
        <tr>
            <td>****</td>
            <td>diberi tanda centang dan alasannya</td>
        </tr>
        <tr>
            <td>N</td>
            <td>= Cuti tahun berjalan</td>
        </tr>
        <tr>
            <td>N-1</td>
            <td>= Sisa cuti 1 tahun sebelumnya</td>
        </tr>
        <tr>
            <td>N-2</td>
            <td>= Sisa cuti 2 tahun sebelumnya</td>
        </tr>
    </table>

</body>
</html>
