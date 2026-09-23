<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Surat Izin Cuti - {{ $pegawai->nama_lengkap }}</title>
    <style>
        @page {
            size: 215mm 330mm; /* Standar F4 / Folio Pemerintahan */
            margin: 20mm;      /* Margin atas, bawah, kanan, kiri sama persis 20mm (2 cm) */
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
            margin-bottom: 18px;
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
        .title-container {
            text-align: center;
            margin-top: 15px;
            margin-bottom: 18px;
        }
        .title-text {
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .nomor-surat {
            font-size: 11pt;
            margin-top: 3px;
        }
        .content {
            text-align: justify;
            font-size: 11pt;
            line-height: 1.45;
        }
        .identity-table {
            width: 100%;
            margin: 8px 0 14px 0;
            border-collapse: collapse;
        }
        .identity-table td {
            padding: 2.5px 0;
            vertical-align: top;
            font-size: 11pt;
        }
        .terms-list {
            margin: 6px 0 14px 0;
            padding-left: 20px;
        }
        .terms-list li {
            margin-bottom: 4px;
            text-align: justify;
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
            margin-top: 10px;
            font-size: 9.5pt;
            line-height: 1.35;
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

    <!-- JUDUL SURAT -->
    <div class="title-container">
        @php
            $cleanJenisNama = strtoupper($jenisCuti->nama);
            if (str_starts_with($cleanJenisNama, 'CUTI ')) {
                $cleanJenisNama = substr($cleanJenisNama, 5);
            }
        @endphp
        <div class="title-text">SURAT IJIN CUTI {{ $cleanJenisNama }}</div>
        <div class="nomor-surat">Nomor : {!! $nomorSurat !!}</div>
    </div>

    <!-- ISI SURAT -->
    <div class="content">
        <p style="margin-bottom: 8px;">
            Diberikan {{ $jenisCuti->nama }} untuk Tahun {{ $tahunCutiLabel ?? $tahun }} kepada Pegawai Negeri Sipil / Pegawai Pemerintah dengan Perjanjian Kerja :
        </p>

        <table class="identity-table">
            <tr>
                <td style="width: 25%; font-weight: bold;">NAMA</td>
                <td style="width: 3%;">:</td>
                <td style="width: 72%; font-weight: bold;">{{ $pegawai->nama_lengkap }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">NIP</td>
                <td>:</td>
                <td>{{ $pegawai->nip }}</td>
            </tr>
            <tr>
                <td>Pangkat / Gol. Ruang</td>
                <td>:</td>
                <td>{{ $pegawai->pangkat_golongan ?: '-' }}</td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>:</td>
                <td>{{ $pegawai->jabatan }}</td>
            </tr>
            <tr>
                <td>Satuan Organisasi</td>
                <td>:</td>
                <td>Inspektorat Kabupaten Trenggalek</td>
            </tr>
        </table>

        <p style="margin-top: 8px; margin-bottom: 8px;">
            Selama <strong>{{ $durasiAngka }} ({{ $durasiTerbilang }}) {{ $satuanLabel }}</strong>
            @if($durasiAngka > 1)
                terhitung mulai tanggal <strong>{{ $tanggalMulai }}</strong> sampai dengan <strong>{{ $tanggalSelesai }}</strong>
            @else
                terhitung tanggal <strong>{{ $tanggalMulai }}</strong>
            @endif
            dengan ketentuan sebagai berikut :
        </p>

        <ol type="a" class="terms-list">
            <li>Sebelum menjalankan {{ strtolower($jenisCuti->nama) }} wajib menyerahkan pekerjaannya kepada atasan langsungnya;</li>
            <li>Setelah selesai menjalankan {{ strtolower($jenisCuti->nama) }} wajib melaporkan diri kepada atasan langsungnya dan bekerja kembali sebagaimana mestinya.</li>
        </ol>

        <p style="margin-top: 12px; margin-bottom: 0;">
            Demikian surat izin cuti ini dibuat untuk dapat dipergunakan sebagaimana mestinya.
        </p>
    </div>

    <!-- TANDA TANGAN & TEMBUSAN -->
    <table class="signature-table">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <div class="tembusan">
                    <strong><u>Tembusan kepada :</u></strong><br>
                    Yth. Sdr. Kepala Badan Kepegawaian Dan<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pengembangan Sumber Daya Manusia<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Kab. Trenggalek
                </div>
            </td>
            <td style="width: 50%; vertical-align: top; text-align: left; padding-left: 35px;">
                Trenggalek, {{ $tanggalSurat }}<br>
                <strong>{{ $pybmcJabatan }}</strong>
                <div style="height: 45px;"></div>
                <strong style="text-decoration: underline;">{{ $pybmcNama }}</strong><br>
                {{ $pybmcPangkat }}<br>
                NIP. {{ $pybmcNip }}
            </td>
        </tr>
    </table>

</body>
</html>
