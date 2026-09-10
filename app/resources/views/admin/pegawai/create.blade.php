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
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3" 
                 x-data="{ 
                     selectedGolongan: '{{ old('pangkat_golongan') }}',
                     customGolongan: '',
                     isCustomGolongan: false,
                     selectedJabatan: '{{ old('jabatan') }}',
                     customJabatan: '',
                     isCustomJabatan: false
                 }">
                <div>
                    <label for="pangkat_golongan" class="block text-sm font-semibold text-slate-700">Pangkat / Golongan</label>
                    <select id="pangkat_golongan_select" 
                            @change="if($event.target.value === '__custom__') { isCustomGolongan = true; selectedGolongan = customGolongan; } else { isCustomGolongan = false; selectedGolongan = $event.target.value; }"
                            class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">-- Pilih Golongan --</option>
                        <optgroup label="PNS Golongan IV (Pembina)">
                            <option value="IV/e - Pembina Utama">IV/e - Pembina Utama</option>
                            <option value="IV/d - Pembina Utama Madya">IV/d - Pembina Utama Madya</option>
                            <option value="IV/c - Pembina Utama Muda">IV/c - Pembina Utama Muda</option>
                            <option value="IV/b - Pembina Tingkat I">IV/b - Pembina Tingkat I</option>
                            <option value="IV/a - Pembina">IV/a - Pembina</option>
                        </optgroup>
                        <optgroup label="PNS Golongan III (Penata)">
                            <option value="III/d - Penata Tingkat I">III/d - Penata Tingkat I</option>
                            <option value="III/c - Penata">III/c - Penata</option>
                            <option value="III/b - Penata Muda Tingkat I">III/b - Penata Muda Tingkat I</option>
                            <option value="III/a - Penata Muda">III/a - Penata Muda</option>
                        </optgroup>
                        <optgroup label="PNS Golongan II (Pengatur)">
                            <option value="II/d - Pengatur Tingkat I">II/d - Pengatur Tingkat I</option>
                            <option value="II/c - Pengatur">II/c - Pengatur</option>
                            <option value="II/b - Pengatur Muda Tingkat I">II/b - Pengatur Muda Tingkat I</option>
                            <option value="II/a - Pengatur Muda">II/a - Pengatur Muda</option>
                        </optgroup>
                        <optgroup label="PNS Golongan I (Juru)">
                            <option value="I/d - Juru Tingkat I">I/d - Juru Tingkat I</option>
                            <option value="I/c - Juru">I/c - Juru</option>
                            <option value="I/b - Juru Muda Tingkat I">I/b - Juru Muda Tingkat I</option>
                            <option value="I/a - Juru Muda">I/a - Juru Muda</option>
                        </optgroup>
                        <optgroup label="PPPK (Pegawai Perjanjian Kerja)">
                            <option value="PPPK - Golongan X">PPPK - Golongan X (S1 / Profesi)</option>
                            <option value="PPPK - Golongan IX">PPPK - Golongan IX (S1 / D4)</option>
                            <option value="PPPK - Golongan VII">PPPK - Golongan VII (D3)</option>
                            <option value="PPPK - Golongan V">PPPK - Golongan V (SMA/SMK)</option>
                        </optgroup>
                        <option value="__custom__">+ Lainnya (Input Manual)</option>
                    </select>
                    
                    <input type="text" name="pangkat_golongan" id="pangkat_golongan" required 
                           x-model="selectedGolongan"
                           x-show="isCustomGolongan"
                           placeholder="Ketikkan Golongan Manual"
                           class="mt-2 block w-full rounded-xl border-indigo-300 bg-indigo-50/50 py-2.5 px-3 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="jabatan" class="block text-sm font-semibold text-slate-700">Jabatan</label>
                    <select id="jabatan_select"
                            @change="if($event.target.value === '__custom__') { isCustomJabatan = true; selectedJabatan = customJabatan; } else { isCustomJabatan = false; selectedJabatan = $event.target.value; }"
                            class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">-- Pilih Jabatan --</option>
                        <optgroup label="Pimpinan &amp; Struktural">
                            <option value="Inspektur">Inspektur</option>
                            <option value="Plt. Inspektur">Plt. Inspektur</option>
                            <option value="Sekretaris">Sekretaris</option>
                            <option value="Sekretaris (Plt. Inspektur)">Sekretaris (Plt. Inspektur)</option>
                            <option value="Inspektur Pembantu Wilayah I">Inspektur Pembantu Wilayah I</option>
                            <option value="Inspektur Pembantu Wilayah II">Inspektur Pembantu Wilayah II</option>
                            <option value="Inspektur Pembantu Wilayah III">Inspektur Pembantu Wilayah III</option>
                            <option value="Inspektur Pembantu Wilayah IV">Inspektur Pembantu Wilayah IV</option>
                            <option value="Kepala Sub Bagian Umum dan Kepegawaian">Kepala Sub Bagian Umum dan Kepegawaian</option>
                        </optgroup>
                        <optgroup label="Jabatan Fungsional Auditor">
                            <option value="Auditor Ahli Utama">Auditor Ahli Utama</option>
                            <option value="Auditor Ahli Madya">Auditor Ahli Madya</option>
                            <option value="Auditor Ahli Muda">Auditor Ahli Muda</option>
                            <option value="Auditor Ahli Pertama">Auditor Ahli Pertama</option>
                            <option value="Auditor Terampil">Auditor Terampil</option>
                        </optgroup>
                        <optgroup label="Jabatan Fungsional PPUPD">
                            <option value="Pengawas Penyelenggaraan Urusan Pemerintahan Daerah (PPUPD) Ahli Madya">PPUPD Ahli Madya</option>
                            <option value="Pengawas Penyelenggaraan Urusan Pemerintahan Daerah (PPUPD) Ahli Muda">PPUPD Ahli Muda</option>
                            <option value="Pengawas Penyelenggaraan Urusan Pemerintahan Daerah (PPUPD) Ahli Pertama">PPUPD Ahli Pertama</option>
                        </optgroup>
                        <optgroup label="Fungsional &amp; Pelaksana Lainnya">
                            <option value="Perencana Ahli Muda">Perencana Ahli Muda</option>
                            <option value="Analis Kebijakan">Analis Kebijakan</option>
                            <option value="Penelaah Teknis Kebijakan">Penelaah Teknis Kebijakan</option>
                            <option value="Pranata Komputer Ahli Pertama">Pranata Komputer Ahli Pertama</option>
                            <option value="Pranata Komputer Terampil">Pranata Komputer Terampil</option>
                            <option value="Pengadministrasi Perkantoran">Pengadministrasi Perkantoran</option>
                            <option value="Pengemudi">Pengemudi</option>
                            <option value="Petugas Keamanan">Petugas Keamanan</option>
                            <option value="Pramubakti">Pramubakti</option>
                        </optgroup>
                        <option value="__custom__">+ Lainnya (Input Manual)</option>
                    </select>

                    <input type="text" name="jabatan" id="jabatan" required 
                           x-model="selectedJabatan"
                           x-show="isCustomJabatan"
                           placeholder="Ketikkan Jabatan Manual"
                           class="mt-2 block w-full rounded-xl border-indigo-300 bg-indigo-50/50 py-2.5 px-3 text-sm focus:border-indigo-500 focus:ring-indigo-500">
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
