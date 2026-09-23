<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Surat Pengantar Cuti Bupati - {{ $pegawai->nama_lengkap }}</title>
    <style>
        @page {
            size: 215mm 330mm;
            margin: 20mm;
        }
        body {
            font-family: 'Bookman Old Style', 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.45;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: middle;
        }
        .header-title-1 {
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-family: 'Bookman Old Style', serif;
            margin: 0;
        }
        .header-title-2 {
            font-size: 15pt;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin: 2px 0 0 0;
            font-family: 'Bookman Old Style', serif;
        }
        .header-address {
            margin: 4px 0 0 0;
            font-size: 8.5pt;
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.25;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .meta-table td {
            vertical-align: top;
            font-size: 11pt;
            padding: 2px 0;
        }
        .content {
            text-align: justify;
            font-size: 11pt;
            line-height: 1.45;
        }
        .identity-table {
            width: 100%;
            margin: 8px 0 12px 10px;
            border-collapse: collapse;
        }
        .identity-table td {
            padding: 2.5px 0;
            vertical-align: top;
            font-size: 11pt;
        }
        .signature-table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
        }
        .signature-table td {
            vertical-align: top;
            font-size: 11pt;
        }
        .tembusan {
            margin-top: 25px;
            font-size: 9.5pt;
            line-height: 1.35;
        }
        .tembusan ol {
            margin: 2px 0 0 0;
            padding-left: 18px;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT RESMI PEMKAB TRENGGALEK -->
    @php
        $logoPath = public_path('images/logo-trenggalek.png');
        $logoData = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
    @endphp
    <table class="header-table">
        <tr>
            <td style="width: 70px; text-align: left;">
                @if($logoData)
                    <img src="data:image/png;base64,{{ $logoData }}" style="width: 65px; height: auto;" alt="Logo Pemkab Trenggalek">
                @endif
            </td>
            <td style="text-align: center; padding-right: 65px;">
                <h3 class="header-title-1">PEMERINTAH KABUPATEN TRENGGALEK</h3>
                <h2 class="header-title-2">INSPEKTORAT DAERAH</h2>
                <p class="header-address">Jl. Veteran No. 27 Trenggalek, Jawa Timur 66311<br>Telepon: (0355) 791444 | Email: inspektorat@trenggalekkab.go.id</p>
            </td>
        </tr>
    </table>

    <!-- TANGGAL & TUJUAN SURAT -->
    <table class="meta-table">
        <tr>
            <td style="width: 12%;">Nomor</td>
            <td style="width: 2%;">:</td>
            <td style="width: 46%;">{{ $nomorSurat }}</td>
            <td style="width: 40%; text-align: right;">Trenggalek, {{ $tanggalSurat }}</td>
        </tr>
        <tr>
            <td>Sifat</td>
            <td>:</td>
            <td>Penting</td>
            <td></td>
        </tr>
        <tr>
            <td>Lampiran</td>
            <td>:</td>
            <td>1 (satu) berkas</td>
            <td>Kepada</td>
        </tr>
        <tr>
            <td>Hal</td>
            <td>:</td>
            <td style="font-weight: bold;">Permohonan {{ $jenisCuti->nama }}<br>a.n. {{ $pegawai->nama_lengkap }}</td>
            <td>
                Yth. <span style="font-weight: bold;">Bupati Trenggalek</span><br>
                cq. Kepala Badan Kepegawaian dan<br>
                Pengembangan SDM<br>
                di -<br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-weight: bold; text-decoration: underline;">TRENGGALEK</span>
            </td>
        </tr>
    </table>

    <div class="content">
        <p style="margin-top: 10px; margin-bottom: 8px;">
            Bersama ini kami sampaikan dengan hormat permohonan {{ $jenisCuti->nama }} {{ $pegawai->jenis_pegawai === 'PPPK' ? 'Pegawai Pemerintah dengan Perjanjian Kerja' : 'Pegawai Negeri Sipil' }} di lingkungan Pemerintah Kabupaten Trenggalek sebagai berikut:
        </p>

        <table class="identity-table">
            <tr>
                <td style="width: 28%;">Nama</td>
                <td style="width: 2%;">:</td>
                <td style="width: 70%; font-weight: bold;">{{ $pegawai->nama_lengkap }}</td>
            </tr>
            <tr>
                <td>NIP</td>
                <td>:</td>
                <td>{{ $pegawai->nip }}</td>
            </tr>
            <tr>
                <td>Pangkat / Gol. Ruang</td>
                <td>:</td>
                <td>{{ $pegawaiPangkatGolongan ?? ($pegawai->pangkat_golongan ?: '-') }}</td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>:</td>
                <td>{{ $pegawai->jabatan }}</td>
            </tr>
            <tr>
                <td>Unit Kerja</td>
                <td>:</td>
                <td>{{ $pegawai->unitKerja->nama }}</td>
            </tr>
            <tr>
                <td>Lamanya Cuti</td>
                <td>:</td>
                <td>{{ $durasiAngka }} ({{ $durasiTerbilang }}) {{ $satuanLabel }}</td>
            </tr>
            <tr>
                <td>Terhitung Mulai</td>
                <td>:</td>
                <td>{{ $tanggalMulai }} s.d {{ $tanggalSelesai }}</td>
            </tr>
            <tr>
                <td>Alasan Cuti</td>
                <td>:</td>
                <td>{{ $pengajuan->alasan }}</td>
            </tr>
            <tr>
                <td>Alamat Selama Cuti</td>
                <td>:</td>
                <td>{{ $pengajuan->alamat_selama_cuti ?: '-' }}</td>
            </tr>
            <tr>
                <td>Nomor Telepon / HP</td>
                <td>:</td>
                <td>{{ $pengajuan->telp_selama_cuti ?: '-' }}</td>
            </tr>
        </table>

        <p style="margin-top: 8px; margin-bottom: 8px;">
            Sebagai kelengkapan administrasi kepegawaian, terlampir kami sertakan Formulir Permintaan dan Pemberian Cuti (Anak Lampiran 1.b Peraturan BKN Nomor 24 Tahun 2017) beserta rekapitulasi catatan sisa hak cuti yang bersangkutan.
        </p>

        <p style="margin-top: 8px; margin-bottom: 8px;">
            Demikian surat permohonan ini kami sampaikan, atas perkenan dan penetapan Bapak Bupati disampaikan terima kasih.
        </p>
    </div>

    <!-- TANDA TANGAN PEMOHON / PIMPINAN OPD -->
    <table class="signature-table">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%; vertical-align: top; text-align: left; padding-left: 35px;">
                Pemohon,<br>
                <span style="font-weight: bold;">{{ $pegawai->jabatan }}</span>
                <div style="height: 50px;"></div>
                <span style="font-weight: bold; text-decoration: underline;">{{ $pegawai->nama_lengkap }}</span><br>
                {{ $pegawaiPangkat ?? $pegawai->pangkat_golongan }}<br>
                NIP. {{ $pegawai->nip }}
            </td>
        </tr>
    </table>

    <!-- TEMBUSAN -->
    <div class="tembusan">
        <span style="font-weight: bold;">Tembusan:</span>
        <ol>
            <li>Yth. Sekretaris Daerah Kabupaten Trenggalek;</li>
            <li>Arsip Kepegawaian Inspektorat Daerah.</li>
        </ol>
    </div>

</body>
</html>
