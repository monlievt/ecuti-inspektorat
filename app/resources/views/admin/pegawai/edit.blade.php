@extends('layouts.app')

@section('title', 'Edit Pegawai - Admin')

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 bg-slate-50/50">
            <h3 class="text-lg font-semibold leading-6 text-slate-900">Edit Pegawai</h3>
            <p class="mt-1 text-sm text-slate-500">Perbarui biodata pegawai beserta wewenang akun login e-Cuti.</p>
        </div>

        <form action="{{ route('admin.pegawai.update', $pegawai->id) }}" method="POST" class="p-6 space-y-6">
            @csrf

            <!-- NIP & NIP Lama -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="nip" class="block text-sm font-semibold text-slate-700">NIP (18 Digit)</label>
                    <input type="text" name="nip" id="nip" required maxlength="18" value="{{ old('nip', $pegawai->nip) }}"
                           class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('nip')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="nip_lama" class="block text-sm font-semibold text-slate-700">NIP Lama (Opsional)</label>
                    <input type="text" name="nip_lama" id="nip_lama" maxlength="9" value="{{ old('nip_lama', $pegawai->nip_lama) }}"
                           class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
            </div>

            <!-- Nama Lengkap, Email, & Password Baru -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <div>
                    <label for="nama_lengkap" class="block text-sm font-semibold text-slate-700">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" required value="{{ old('nama_lengkap', $pegawai->nama_lengkap) }}"
                           class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('nama_lengkap')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700">Email Utama (Login)</label>
                    <input type="email" name="email" id="email" required value="{{ old('email', $pegawai->user->email) }}"
                           class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('email')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700">Ganti Password (Opsional)</label>
                    <input type="password" name="password" id="password" placeholder="Kosongkan jika tak diubah"
                           class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('password')
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
                        <option value="L" {{ old('jenis_kelamin', $pegawai->jenis_kelamin) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin', $pegawai->jenis_kelamin) === 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div>
                    <label for="nomor_hp" class="block text-sm font-semibold text-slate-700">No. HP Aktif (Untuk Notifikasi WA)</label>
                    <input type="text" name="nomor_hp" id="nomor_hp" value="{{ old('nomor_hp', $pegawai->nomor_hp) }}"
                           class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
            </div>

            <!-- TMT CPNS & TMT PNS -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="tmt_cpns" class="block text-sm font-semibold text-slate-700">TMT CPNS</label>
                    <input type="date" name="tmt_cpns" id="tmt_cpns" required value="{{ old('tmt_cpns', $pegawai->tmt_cpns ? $pegawai->tmt_cpns->toDateString() : '') }}"
                           class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label for="tmt_pns" class="block text-sm font-semibold text-slate-700">TMT PNS</label>
                    <input type="date" name="tmt_pns" id="tmt_pns" value="{{ old('tmt_pns', $pegawai->tmt_pns ? $pegawai->tmt_pns->toDateString() : '') }}"
                           class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
            </div>

            <!-- Golongan, Jabatan, Unit -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <div>
                    <label for="pangkat_golongan" class="block text-sm font-semibold text-slate-700">Golongan</label>
                    <input type="text" name="pangkat_golongan" id="pangkat_golongan" required value="{{ old('pangkat_golongan', $pegawai->pangkat_golongan) }}"
                           class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label for="jabatan" class="block text-sm font-semibold text-slate-700">Jabatan</label>
                    <input type="text" name="jabatan" id="jabatan" required value="{{ old('jabatan', $pegawai->jabatan) }}"
                           class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label for="unit_kerja_id" class="block text-sm font-semibold text-slate-700">Unit Kerja</label>
                    <select id="unit_kerja_id" name="unit_kerja_id" required
                            class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        @foreach($unitKerja as $uk)
                            <option value="{{ $uk->id }}" {{ old('unit_kerja_id', $pegawai->unit_kerja_id) == $uk->id ? 'selected' : '' }}>{{ $uk->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Jenis Pegawai, Role Akun, & Flag Izin Sementara -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-4">
                <div>
                    <label for="jenis_pegawai" class="block text-sm font-semibold text-slate-700">Jenis Kepegawaian</label>
                    <select id="jenis_pegawai" name="jenis_pegawai" required
                            class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="PNS" {{ old('jenis_pegawai', $pegawai->jenis_pegawai) === 'PNS' ? 'selected' : '' }}>PNS</option>
                        <option value="CPNS" {{ old('jenis_pegawai', $pegawai->jenis_pegawai) === 'CPNS' ? 'selected' : '' }}>CPNS</option>
                        <option value="PPPK" {{ old('jenis_pegawai', $pegawai->jenis_pegawai) === 'PPPK' ? 'selected' : '' }}>PPPK</option>
                    </select>
                </div>
                <div>
                    <label for="role" class="block text-sm font-semibold text-slate-700">Role Sistem e-Cuti</label>
                    <select id="role" name="role" required
                            class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="pegawai" {{ old('role', $pegawai->user->role) === 'pegawai' ? 'selected' : '' }}>Pegawai / Pengguna Biasa</option>
                        <option value="admin_cuti" {{ old('role', $pegawai->user->role) === 'admin_cuti' ? 'selected' : '' }}>Admin Kepegawaian</option>
                        <option value="super_admin" {{ old('role', $pegawai->user->role) === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    </select>
                </div>
                <div>
                    <label for="bisa_beri_izin_sementara" class="block text-sm font-semibold text-slate-700">Bisa Beri Izin Darurat</label>
                    <select id="bisa_beri_izin_sementara" name="bisa_beri_izin_sementara" required
                            class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="0" {{ old('bisa_beri_izin_sementara', $pegawai->user->bisa_beri_izin_sementara) == '0' ? 'selected' : '' }}>Tidak</option>
                        <option value="1" {{ old('bisa_beri_izin_sementara', $pegawai->user->bisa_beri_izin_sementara) == '1' ? 'selected' : '' }}>Ya (Pejabat Tinggi Tempat Kerja)</option>
                    </select>
                </div>
                <div>
                    <label for="aktif" class="block text-sm font-semibold text-slate-700">Status Pegawai</label>
                    <select id="aktif" name="aktif" required
                            class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="1" {{ old('aktif', $pegawai->aktif) == '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('aktif', $pegawai->aktif) == '0' ? 'selected' : '' }}>Non-aktif / Resign</option>
                    </select>
                </div>
            </div>
            
            <!-- Manajemen Saldo Cuti (Tahun Berjalan) -->
            <div class="border-t border-slate-200 pt-6">
                <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Manajemen Saldo Cuti (Tahun {{ $tahun }})</h4>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-5">
                    <div>
                        <label for="jatah_tahun_berjalan" class="block text-xs font-semibold text-slate-700">Jatah Tahun Berjalan</label>
                        <input type="number" name="jatah_tahun_berjalan" id="jatah_tahun_berjalan" required min="0" 
                               value="{{ old('jatah_tahun_berjalan', $saldo ? $saldo->jatah_tahun_berjalan : 12) }}"
                               class="mt-1.5 block w-full rounded-xl border-slate-300 py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label for="carry_over_n1" class="block text-xs font-semibold text-slate-700">Carry Over N-1</label>
                        <input type="number" name="carry_over_n1" id="carry_over_n1" required min="0" 
                               value="{{ old('carry_over_n1', $saldo ? $saldo->carry_over_n1 : 0) }}"
                               class="mt-1.5 block w-full rounded-xl border-slate-300 py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label for="carry_over_n2" class="block text-xs font-semibold text-slate-700">Carry Over N-2</label>
                        <input type="number" name="carry_over_n2" id="carry_over_n2" required min="0" 
                               value="{{ old('carry_over_n2', $saldo ? $saldo->carry_over_n2 : 0) }}"
                               class="mt-1.5 block w-full rounded-xl border-slate-300 py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label for="tambahan_cuti_bersama" class="block text-xs font-semibold text-slate-700">Tambahan Cuti Bersama</label>
                        <input type="number" name="tambahan_cuti_bersama" id="tambahan_cuti_bersama" required min="0" 
                               value="{{ old('tambahan_cuti_bersama', $saldo ? $saldo->tambahan_cuti_bersama : 0) }}"
                               class="mt-1.5 block w-full rounded-xl border-slate-300 py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label for="terpakai" class="block text-xs font-semibold text-slate-700">Cuti Terpakai</label>
                        <input type="number" name="terpakai" id="terpakai" required min="0" 
                               value="{{ old('terpakai', $saldo ? $saldo->terpakai : 0) }}"
                               class="mt-1.5 block w-full rounded-xl border-slate-300 py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end pt-4 border-t border-slate-200 space-x-3">
                <a href="{{ route('admin.pegawai.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Batal</a>
                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition shadow-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
