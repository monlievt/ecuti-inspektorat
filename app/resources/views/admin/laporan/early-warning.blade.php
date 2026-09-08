@extends('layouts.app')

@section('title', 'Early Warning Saldo Hangus - e-Cuti')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Early Warning: Saldo Cuti Akan Hangus</h2>
                <span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-bold text-rose-700">Tahun {{ $tahun }}</span>
            </div>
            <p class="mt-1 text-sm text-slate-500">Daftar pegawai yang memiliki sisa saldo carry-over (N-1 atau N-2) yang terancam hangus pada akhir tahun 31 Desember jika tidak dimanfaatkan.</p>
        </div>
    </div>

    <!-- Alert Edukasi Aturan BKN -->
    <div class="rounded-2xl border border-amber-200 bg-amber-50/75 p-5">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-semibold text-amber-900">Ketentuan Kadaluarsa Saldo Cuti (Perka BKN 24/2017)</h3>
                <div class="mt-1 text-xs text-amber-800 space-y-1">
                    <p>&bull; <strong>Saldo N-2 (2 tahun lalu):</strong> Wajib dihabiskan pada tahun berjalan. Sisa N-2 yang tidak terpakai per 31 Desember akan <strong>hangus 100%</strong>.</p>
                    <p>&bull; <strong>Saldo N-1 (1 tahun lalu):</strong> Maksimal yang dapat dibawa ke tahun depan adalah 6 hari. Kelebihannya akan hangus.</p>
                    <p>&bull; <strong>Pegawai PPPK:</strong> Seluruh sisa cuti tahun berjalan akan hangus per 31 Desember (tidak ada carry-over sesuai PP 49/2018).</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Unit Kerja -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.laporan.early-warning') }}" class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="flex-1">
                <label for="unit_kerja_id" class="block text-xs font-medium text-slate-700">Filter Unit Kerja</label>
                <select name="unit_kerja_id" id="unit_kerja_id" class="mt-1 block w-full rounded-xl border-slate-300 py-2 px-3 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Semua Unit Kerja</option>
                    @foreach($unitKerjaList as $uk)
                        <option value="{{ $uk->id }}" {{ $unitKerjaId == $uk->id ? 'selected' : '' }}>{{ $uk->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-36">
                <label for="tahun" class="block text-xs font-medium text-slate-700">Tahun Saldo</label>
                <input type="number" name="tahun" id="tahun" value="{{ $tahun }}" 
                       class="mt-1 block w-full rounded-xl border-slate-300 py-2 px-3 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <button type="submit" class="rounded-xl bg-slate-900 py-2 px-5 text-xs font-semibold text-white shadow-sm hover:bg-slate-800 transition-colors">
                Tampilkan
            </button>
        </form>
    </div>

    <!-- Tabel Daftar Pegawai Kritis -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="py-3.5 px-4">Pegawai</th>
                        <th class="py-3.5 px-4">Unit Kerja</th>
                        <th class="py-3.5 px-4 text-center">Sisa N-2 (Pasti Hangus)</th>
                        <th class="py-3.5 px-4 text-center">Sisa N-1 (Terancam)</th>
                        <th class="py-3.5 px-4 text-center">Total Saldo Aktif</th>
                        <th class="py-3.5 px-4 text-right">Pengingat WhatsApp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($daftarKritis as $s)
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="py-4 px-4">
                                <div class="font-medium text-slate-900">{{ $s->pegawai->nama_lengkap }}</div>
                                <div class="text-xs text-slate-500">NIP. {{ $s->pegawai->nip }} &bull; HP: {{ $s->pegawai->nomor_hp ?? '-' }}</div>
                            </td>
                            <td class="py-4 px-4 text-slate-600 text-xs">
                                {{ $s->pegawai->unitKerja?->nama ?? '-' }}
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if($s->sisa_n2_hangus > 0)
                                    <span class="inline-flex items-center rounded-lg bg-rose-100 px-3 py-1 text-xs font-bold text-rose-700">
                                        {{ $s->sisa_n2_hangus }} hari
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">0 hari</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if($s->sisa_n1_terancam > 0)
                                    <span class="inline-flex items-center rounded-lg bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">
                                        {{ $s->sisa_n1_terancam }} hari
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">0 hari</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center text-xs font-bold text-slate-800">
                                {{ $s->total_saldo_tersedia }} hari
                            </td>
                            <td class="py-4 px-4 text-right">
                                <form method="POST" action="{{ route('admin.laporan.kirim-reminder-wa', $s->pegawai->id) }}" class="inline-block">
                                    @csrf
                                    <input type="hidden" name="sisa_hari" value="{{ $s->sisa_n2_hangus + $s->sisa_n1_terancam }}">
                                    <button type="submit" 
                                            class="inline-flex items-center rounded-xl bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 transition-colors">
                                        <svg class="h-3.5 w-3.5 mr-1.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                        Kirim Pengingat WA
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-sm">
                                Tidak ada pegawai dengan saldo kadaluarsa / kritis pada tahun {{ $tahun }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
