@extends('layouts.app')

@section('title', 'Rekapitulasi Pengajuan Cuti - e-Cuti')

@section('content')
<div class="space-y-6">
    <!-- Header & Action Buttons -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Rekapitulasi Pengajuan Cuti</h2>
            <p class="mt-1 text-sm text-slate-500">Laporan pemanfaatan dan riwayat pengajuan cuti {{ !empty($isScopeBawahan) ? 'bawahan di lingkungan kerja Anda.' : 'seluruh pegawai di Inspektorat Kabupaten Trenggalek.' }}</p>
        </div>
        <div class="mt-4 sm:ml-4 sm:mt-0" style="display: flex !important; flex-wrap: wrap !important; align-items: center !important; gap: 12px !important;">
            <a href="{{ route('admin.laporan.ekspor-excel', request()->query()) }}" 
               style="background-color: #059669 !important; color: #ffffff !important; display: inline-flex !important; align-items: center !important; gap: 8px !important; margin: 2px !important; padding: 10px 18px !important; border-radius: 12px !important;"
               class="text-sm font-semibold shadow-sm hover:opacity-90 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Ekspor Excel / CSV
            </a>
            <a href="{{ route('admin.laporan.ekspor-pdf', array_merge(request()->query(), ['download' => 1])) }}"
               style="background-color: #e11d48 !important; color: #ffffff !important; display: inline-flex !important; align-items: center !important; gap: 8px !important; margin: 2px !important; padding: 10px 18px !important; border-radius: 12px !important;"
               class="text-sm font-semibold shadow-sm hover:opacity-90 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Unduh PDF
            </a>
            <a href="{{ route('admin.laporan.ekspor-pdf', request()->query()) }}" target="_blank"
               style="background-color: #475569 !important; color: #ffffff !important; display: inline-flex !important; align-items: center !important; gap: 8px !important; margin: 2px !important; padding: 10px 18px !important; border-radius: 12px !important;"
               class="text-sm font-semibold shadow-sm hover:opacity-90 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak / Pratinjau
            </a>
        </div>
    </div>

    <!-- Statistik KPI Card -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Pengajuan</dt>
            <dd class="mt-2 text-3xl font-extrabold text-slate-900">{{ $totalPengajuan }}</dd>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <dt class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Disetujui (Terbit Surat)</dt>
            <dd class="mt-2 text-3xl font-extrabold text-emerald-600">{{ $disetujuiCount }}</dd>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <dt class="text-xs font-semibold uppercase tracking-wider text-amber-600">Sedang Diproses</dt>
            <dd class="mt-2 text-3xl font-extrabold text-amber-600">{{ $prosesCount }}</dd>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <dt class="text-xs font-semibold uppercase tracking-wider text-rose-600">Ditolak</dt>
            <dd class="mt-2 text-3xl font-extrabold text-rose-600">{{ $ditolakCount }}</dd>
        </div>
    </div>

    <!-- Filter Form Bar -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.laporan.rekapitulasi') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-4 items-end">
            <div class="sm:col-span-2">
                <label for="search" class="block text-xs font-semibold text-slate-700">Cari Pegawai (Nama / NIP)</label>
                <div class="relative mt-1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" name="search" id="search" value="{{ $search }}" placeholder="Ketik nama atau NIP pegawai..." 
                           class="block w-full rounded-xl border-slate-300 pl-9 pr-3 py-2 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label for="tahun" class="block text-xs font-semibold text-slate-700">Tahun Anggaran</label>
                <select name="tahun" id="tahun" class="mt-1 block w-full rounded-xl border-slate-300 py-2 px-3 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-semibold text-indigo-900 bg-indigo-50/40">
                    <option value="">Semua Tahun</option>
                    @foreach($tahunList as $t)
                        <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>Tahun {{ $t }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="bulan" class="block text-xs font-semibold text-slate-700">Bulan Pelaksanaan</label>
                <select name="bulan" id="bulan" class="mt-1 block w-full rounded-xl border-slate-300 py-2 px-3 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-semibold text-indigo-900 bg-indigo-50/40">
                    <option value="">Semua Bulan</option>
                    @foreach($bulanList as $num => $namaBulan)
                        <option value="{{ $num }}" {{ $bulan == $num ? 'selected' : '' }}>{{ $namaBulan }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="unit_kerja_id" class="block text-xs font-medium text-slate-700">Unit Kerja / Bidang</label>
                <select name="unit_kerja_id" id="unit_kerja_id" class="mt-1 block w-full rounded-xl border-slate-300 py-2 px-3 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Semua Unit Kerja</option>
                    @foreach($unitKerjaList as $uk)
                        <option value="{{ $uk->id }}" {{ $unitKerjaId == $uk->id ? 'selected' : '' }}>{{ $uk->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="jenis_cuti_id" class="block text-xs font-medium text-slate-700">Jenis Cuti</label>
                <select name="jenis_cuti_id" id="jenis_cuti_id" class="mt-1 block w-full rounded-xl border-slate-300 py-2 px-3 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Semua Jenis Cuti</option>
                    @foreach($jenisCutiList as $jc)
                        <option value="{{ $jc->id }}" {{ $jenisCutiId == $jc->id ? 'selected' : '' }}>{{ $jc->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-xs font-medium text-slate-700">Status Alur Cuti</label>
                <select name="status" id="status" class="mt-1 block w-full rounded-xl border-slate-300 py-2 px-3 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="menunggu_atasan" {{ $status == 'menunggu_atasan' ? 'selected' : '' }}>Menunggu Persetujuan Atasan</option>
                    <option value="menunggu_pyBMC" {{ in_array($status, ['menunggu_pyBMC', 'disetujui_atasan']) ? 'selected' : '' }}>Disetujui Atasan (Menunggu PyBMC)</option>
                    <option value="diterbitkan" {{ in_array($status, ['diterbitkan', 'disetujui_pybmc']) ? 'selected' : '' }}>Disetujui PyBMC (Terbit Surat)</option>
                    <option value="ditolak_pyBMC" {{ $status == 'ditolak_pyBMC' ? 'selected' : '' }}>Ditolak PyBMC (Plt. Inspektur)</option>
                    <option value="ditolak_atasan" {{ $status == 'ditolak_atasan' ? 'selected' : '' }}>Ditolak Atasan Langsung</option>
                    <option value="direvisi" {{ $status == 'direvisi' ? 'selected' : '' }}>Perlu Revisi</option>
                    <option value="izin_sementara_aktif" {{ $status == 'izin_sementara_aktif' ? 'selected' : '' }}>Izin Sementara (Darurat)</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 rounded-xl bg-slate-900 py-2 px-3 text-xs font-semibold text-white shadow-sm hover:bg-slate-800 transition-colors">
                    Filter
                </button>
                <a href="{{ route('admin.laporan.rekapitulasi') }}" class="rounded-xl border border-slate-300 py-2 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel Data Rekapitulasi -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="py-3.5 px-4">Pegawai</th>
                        <th class="py-3.5 px-4">Unit Kerja</th>
                        <th class="py-3.5 px-4">Jenis Cuti</th>
                        <th class="py-3.5 px-4">Tanggal Pelaksanaan</th>
                        <th class="py-3.5 px-4">Durasi</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($pengajuanList as $p)
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="py-4 px-4">
                                <div class="font-medium text-slate-900">{{ $p->pegawai->nama_lengkap }}</div>
                                <div class="text-xs text-slate-500">NIP. {{ $p->pegawai->nip }} &bull; <span class="font-semibold text-indigo-600">{{ $p->pegawai->jenis_pegawai }}</span></div>
                            </td>
                            <td class="py-4 px-4 text-slate-600 text-xs">
                                {{ $p->pegawai->unitKerja?->nama ?? '-' }}
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700">
                                    {{ $p->jenisCuti->nama }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-slate-600 text-xs">
                                {{ $p->tanggal_mulai->format('d M Y') }} s/d {{ $p->tanggal_selesai->format('d M Y') }}
                            </td>
                            <td class="py-4 px-4 text-xs font-semibold text-slate-700">
                                {{ $p->jumlah_hari }} {{ str_replace('_', ' ', $p->satuan_hari) }}
                            </td>
                            <td class="py-4 px-4">
                                @if(in_array($p->status, ['diterbitkan', 'disetujui_pybmc', 'disetujui_pyBMC', 'diratifikasi']))
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                        Disetujui Pimpinan (Terbit SK)
                                    </span>
                                @elseif(in_array($p->status, ['disetujui_atasan', 'menunggu_pyBMC', 'menunggu_pybmc', 'menunggu_ratifikasi']))
                                    <span class="inline-flex items-center rounded-full bg-indigo-50 border border-indigo-200 px-2.5 py-0.5 text-xs font-semibold text-indigo-700">
                                        Disetujui Irban (Menunggu Plt. Inspektur)
                                    </span>
                                @elseif(in_array($p->status, ['diajukan', 'menunggu_atasan']))
                                    <span class="inline-flex items-center rounded-full bg-blue-50 border border-blue-200 px-2.5 py-0.5 text-xs font-semibold text-blue-700">
                                        Menunggu Atasan (Irban)
                                    </span>
                                @elseif(in_array($p->status, ['ditolak_pyBMC', 'ditolak_pybmc', 'ditolak_ratifikasi']))
                                    <span class="inline-flex items-center rounded-full bg-rose-50 border border-rose-200 px-2.5 py-0.5 text-xs font-semibold text-rose-700" title="Ditolak oleh Pejabat Yang Berwenang (Plt. Inspektur)">
                                        Ditolak Plt. Inspektur (PyBMC)
                                    </span>
                                @elseif($p->status === 'ditolak_atasan')
                                    <span class="inline-flex items-center rounded-full bg-rose-50 border border-rose-200 px-2.5 py-0.5 text-xs font-semibold text-rose-700" title="Ditolak oleh Atasan Langsung">
                                        Ditolak Atasan (Irban)
                                    </span>
                                @elseif($p->status === 'izin_sementara_aktif')
                                    <span class="inline-flex items-center rounded-full bg-amber-50 border border-amber-200 px-2.5 py-0.5 text-xs font-semibold text-amber-700">
                                        Izin Sementara Aktif (Darurat)
                                    </span>
                                @elseif($p->status === 'direvisi')
                                    <span class="inline-flex items-center rounded-full bg-amber-50 border border-amber-200 px-2.5 py-0.5 text-xs font-semibold text-amber-700">
                                        Perlu Revisi
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-100 border border-slate-200 px-2.5 py-0.5 text-xs font-semibold text-slate-700">
                                        {{ str_replace('_', ' ', ucfirst($p->status)) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right text-xs">
                                <a href="{{ route('pengajuan.show', $p->id) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold mr-2">Detail</a>
                                @if(in_array($p->status, ['disetujui_pybmc', 'disetujui_pyBMC', 'diterbitkan']))
                                    <a href="{{ route('pengajuan.surat-izin-pdf', $p->id) }}" target="_blank" class="text-emerald-600 hover:text-emerald-900 font-semibold mr-1.5" title="Surat Izin Cuti Dinas">Izin Dinas</a>
                                    <a href="{{ route('pengajuan.pdf', $p->id) }}" target="_blank" class="text-slate-500 hover:text-slate-800 font-semibold" title="Formulir BKN 1.b">BKN 1.b</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-sm">
                                Tidak ada data pengajuan cuti yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pengajuanList->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $pengajuanList->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
