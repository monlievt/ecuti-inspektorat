@extends('layouts.app')

@section('title', 'Tambah Pegawai - Admin')

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 bg-slate-50/50">
            <h3 class="text-lg font-semibold leading-6 text-slate-900">Tambah Pegawai Baru</h3>
            <p class="mt-1 text-sm text-slate-500">Buat data kepegawaian dan akun login pengguna baru secara bersamaan.</p>
        </div>

        <form action="{{ route('admin.pegawai.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <!-- NIP & NIP Lama -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="nip" class="block text-sm font-semibold text-slate-700">NIP (18 Digit)</label>
                    <input type="text" name="nip" id="nip" required maxlength="18" placeholder="mis. 199501012020011001" value="{{ old('nip') }}"
                           class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('nip')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="nip_lama" class="block text-sm font-semibold text-slate-700">NIP Lama (Opsional)</label>
                    <input type="text" name="nip_lama" id="nip_lama" maxlength="9" placeholder="mis. 123456789" value="{{ old('nip_lama') }}"
                           class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('nip_lama')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Nama Lengkap & Email -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="nama_lengkap" class="block text-sm font-semibold text-slate-700">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" required placeholder="Nama lengkap tanpa gelar" value="{{ old('nama_lengkap') }}"
                           class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('nama_lengkap')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700">Email Utama (Untuk Login)</label>
                    <input type="email" name="email" id="email" required placeholder="username@mail.com" value="{{ old('email') }}"
                           class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('email')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Jenis Kelamin & No HP -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="jenis_kelamin" class="block text-sm font-semibold text-slate-700">Jenis Kelamin</label>
                    <select id="jenis_kelamin" name="jenis_kelamin" required
                            class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="L" {{ old('jenis_kelamin') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin') === 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div>
                    <label for="nomor_hp" class="block text-sm font-semibold text-slate-700">No. HP Aktif (Untuk Notifikasi WA)</label>
                    <input type="text" name="nomor_hp" id="nomor_hp" placeholder="mis. 62812345678" value="{{ old('nomor_hp') }}"
                           class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
            </div>

            <!-- TMT CPNS & TMT PNS -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="tmt_cpns" class="block text-sm font-semibold text-slate-700">TMT CPNS</label>
                    <input type="date" name="tmt_cpns" id="tmt_cpns" required value="{{ old('tmt_cpns') }}"
                           class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('tmt_cpns')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="tmt_pns" class="block text-sm font-semibold text-slate-700">TMT PNS (Jika Sudah PNS)</label>
                    <input type="date" name="tmt_pns" id="tmt_pns" value="{{ old('tmt_pns') }}"
                           class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
            </div>

            <!-- Golongan, Jabatan, Unit -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <div>
                    <label for="pangkat_golongan" class="block text-sm font-semibold text-slate-700">Golongan</label>
                    <input type="text" name="pangkat_golongan" id="pangkat_golongan" required placeholder="mis. III/b" value="{{ old('pangkat_golongan') }}"
                           class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label for="jabatan" class="block text-sm font-semibold text-slate-700">Jabatan</label>
                    <input type="text" name="jabatan" id="jabatan" required placeholder="mis. Auditor Pertama" value="{{ old('jabatan') }}"
                           class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label for="unit_kerja_id" class="block text-sm font-semibold text-slate-700">Unit Kerja</label>
                    <select id="unit_kerja_id" name="unit_kerja_id" required
                            class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">-- Pilih Unit Kerja --</option>
                        @foreach($unitKerja as $uk)
                            <option value="{{ $uk->id }}" {{ old('unit_kerja_id') == $uk->id ? 'selected' : '' }}>{{ $uk->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Jenis Pegawai, Role Akun, & Flag Izin Sementara -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <div>
                    <label for="jenis_pegawai" class="block text-sm font-semibold text-slate-700">Jenis Kepegawaian</label>
                    <select id="jenis_pegawai" name="jenis_pegawai" required
                            class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="PNS" {{ old('jenis_pegawai') === 'PNS' ? 'selected' : '' }}>PNS</option>
                        <option value="CPNS" {{ old('jenis_pegawai') === 'CPNS' ? 'selected' : '' }}>CPNS</option>
                        <option value="PPPK" {{ old('jenis_pegawai') === 'PPPK' ? 'selected' : '' }}>PPPK</option>
                    </select>
                </div>
                <div>
                    <label for="role" class="block text-sm font-semibold text-slate-700">Role Sistem e-Cuti</label>
                    <select id="role" name="role" required
                            class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="pegawai" {{ old('role') === 'pegawai' ? 'selected' : '' }}>Pegawai / Pengguna Biasa</option>
                        <option value="admin_cuti" {{ old('role') === 'admin_cuti' ? 'selected' : '' }}>Admin Kepegawaian</option>
                        <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    </select>
                </div>
                <div>
                    <label for="bisa_beri_izin_sementara" class="block text-sm font-semibold text-slate-700">Bisa Beri Izin Darurat</label>
                    <select id="bisa_beri_izin_sementara" name="bisa_beri_izin_sementara" required
                            class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="0" {{ old('bisa_beri_izin_sementara') == '0' ? 'selected' : '' }}>Tidak</option>
                        <option value="1" {{ old('bisa_beri_izin_sementara') == '1' ? 'selected' : '' }}>Ya (Pejabat Tinggi Tempat Kerja)</option>
                    </select>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end pt-4 border-t border-slate-200 space-x-3">
                <a href="{{ route('admin.pegawai.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Batal</a>
                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition shadow-sm">Buat Pegawai &amp; Akun</button>
            </div>
        </form>
    </div>
</div>
@endsection
