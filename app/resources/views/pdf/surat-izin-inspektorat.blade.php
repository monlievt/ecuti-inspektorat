<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Izin Cuti - {{ $pegawai->nama_lengkap }}</title>
    <style>
        body {
            font-family: 'Bookman Old Style', 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 20px 30px;
        }
        .header-container {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 20px;
            position: relative;
        }
        .header-logo {
            position: absolute;
            left: 5px;
            top: 0px;
            width: 70px;
            height: auto;
        }
        .header-text {
            margin-left: 50px;
            margin-right: 20px;
        }
        .header-text h3 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .header-text h2 {
            margin: 2px 0 0 0;
            font-size: 16pt;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }
        .header-text p {
            margin: 4px 0 0 0;
            font-size: 9pt;
            font-family: Arial, Helvetica, sans-serif;
        }
        .title-container {
            text-align: center;
            margin-top: 15px;
            margin-bottom: 20px;
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
            line-height: 1.5;
        }
        .identity-table {
            width: 100%;
            margin: 10px 0 15px 0;
            border-collapse: collapse;
        }
        .identity-table td {
            padding: 3px 0;
            vertical-align: top;
            font-size: 11pt;
        }
        .terms-list {
            margin: 5px 0 15px 0;
            padding-left: 20px;
        }
        .terms-list li {
            margin-bottom: 5px;
            text-align: justify;
        }
        .signature-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        .signature-table td {
            vertical-align: top;
            font-size: 11pt;
        }
        .tembusan {
            margin-top: 40px;
            font-size: 9.5pt;
        }
        .tembusan ol {
            margin: 2px 0 0 0;
            padding-left: 18px;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT RESMI PEMKAB TRENGGALEK -->
    <div class="header-container">
        @php
            $logoPath = public_path('images/logo-trenggalek.png');
            $logoData = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
        @endphp
        @if($logoData)
            <img src="data:image/png;base64,{{ $logoData }}" class="header-logo" alt="Logo Pemkab Trenggalek">
        @endif
        <div class="header-text">
            <h3>PEMERINTAH KABUPATEN TRENGGALEK</h3>
            <h2>INSPEKTORAT DAERAH</h2>
            <p>Jl. Veteran No. 27 Trenggalek, Jawa Timur 66311<br>Telepon: (0355) 791444 | Email: inspektorat@trenggalekkab.go.id</p>
        </div>
    </div>

    <!-- JUDUL SURAT -->
    <div class="title-container">
        <div class="title-text">SURAT IJIN CUTI {{ strtoupper($jenisCuti->nama) }}</div>
        <div class="nomor-surat">Nomor : {{ $nomorSurat }}</div>
    </div>

    <!-- ISI SURAT -->
    <div class="content">
        <p style="margin-bottom: 10px;">
            Diberikan {{ $jenisCuti->nama }} untuk Tahun {{ $tahun }} kepada Pegawai Negeri Sipil / Pegawai Pemerintah dengan Perjanjian Kerja :
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

        <p style="margin-top: 10px; margin-bottom: 10px;">
            Selama <strong>{{ $durasiAngka }} ({{ $durasiTerbilang }}) {{ $satuanLabel }}</strong>
            @if($durasiAngka > 1)
                terhitung mulai tanggal <strong>{{ $tanggalMulai }}</strong> sampai dengan <strong>{{ $tanggalSelesai }}</strong>
            @else
                terhitung tanggal <strong>{{ $tanggalMulai }}</strong>
            @endif
            dengan ketentuan sebagai berikut :
        </p>

        <ol type="a" class="terms-list">
            <li>Sebelum menjalankan cuti tahunan wajib menyerahkan pekerjaannya kepada atasan langsungnya;</li>
            <li>Setelah selesai menjalankan cuti tahunan wajib melaporkan diri kepada atasan langsungnya dan bekerja kembali sebagaimana mestinya.</li>
        </ol>

        <p style="margin-top: 15px;">
            Demikian surat izin cuti ini dibuat untuk dapat dipergunakan sebagaimana mestinya.
        </p>
    </div>

    <!-- TANDA TANGAN & TEMBUSAN -->
    <table class="signature-table">
        <tr>
            <td style="width: 50%;">
                <div class="tembusan">
                    <strong><u>Tembusan kepada :</u></strong><br>
                    Yth. Sdr. Kepala Badan Kepegawaian Dan<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pengembangan Sumber Daya Manusia<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Kab. Trenggalek
                </div>
            </td>
            <td style="width: 50%; text-align: center;">
                Trenggalek, {{ $tanggalSurat }}<br><br>
                <strong>{{ $pybmcJabatan }}</strong><br>
                <br><br><br><br>
                <strong style="text-decoration: underline;">{{ $pybmcNama }}</strong><br>
                {{ $pybmcPangkat }}<br>
                NIP. {{ $pybmcNip }}
            </td>
        </tr>
    </table>

</body>
</html>
