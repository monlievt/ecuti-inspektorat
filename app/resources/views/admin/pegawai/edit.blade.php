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
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3" 
                 x-data="{ 
                     selectedGolongan: '{{ old('pangkat_golongan', $pegawai->pangkat_golongan) }}',
                     customGolongan: '',
                     isCustomGolongan: false,
                     selectedJabatan: '{{ old('jabatan', $pegawai->jabatan) }}',
                     customJabatan: '',
                     isCustomJabatan: false,
                     init() {
                         const golonganList = [
                             'IV/e - Pembina Utama', 'IV/d - Pembina Utama Madya', 'IV/c - Pembina Utama Muda', 
                             'IV/b - Pembina Tingkat I', 'IV/a - Pembina',
                             'III/d - Penata Tingkat I', 'III/c - Penata', 'III/b - Penata Muda Tingkat I', 'III/a - Penata Muda',
                             'II/d - Pengatur Tingkat I', 'II/c - Pengatur', 'II/b - Pengatur Muda Tingkat I', 'II/a - Pengatur Muda',
                             'I/d - Juru Tingkat I', 'I/c - Juru', 'I/b - Juru Muda Tingkat I', 'I/a - Juru Muda',
                             'PPPK - Golongan X', 'PPPK - Golongan IX', 'PPPK - Golongan VII', 'PPPK - Golongan V'
                         ];
                         const cleanGol = this.selectedGolongan.trim();
                         const matchGol = golonganList.find(g => g.toLowerCase().startsWith(cleanGol.toLowerCase()) || cleanGol.toLowerCase().startsWith(g.split(' ')[0].toLowerCase()));
                         if (!matchGol && cleanGol !== '') {
                             this.isCustomGolongan = true;
                             this.customGolongan = cleanGol;
                         }

                         const jabatanList = [
                             'Inspektur', 'Plt. Inspektur', 'Sekretaris', 'Sekretaris (Plt. Inspektur)',
                             'Inspektur Pembantu Wilayah I', 'Inspektur Pembantu Wilayah II', 'Inspektur Pembantu Wilayah III', 'Inspektur Pembantu Wilayah IV',
                             'Kepala Sub Bagian Umum dan Kepegawaian', 'Kepala Sub Bagian Perencanaan dan Evaluasi',
                             'Auditor Ahli Utama', 'Auditor Ahli Madya', 'Auditor Ahli Muda', 'Auditor Ahli Pertama', 'Auditor Terampil',
                             'Pengawas Penyelenggaraan Urusan Pemerintahan Daerah (PPUPD) Ahli Madya',
                             'Pengawas Penyelenggaraan Urusan Pemerintahan Daerah (PPUPD) Ahli Muda',
                             'Pengawas Penyelenggaraan Urusan Pemerintahan Daerah (PPUPD) Ahli Pertama',
                             'Perencana Ahli Muda', 'Perencana Ahli Pertama', 'Analis Kebijakan', 'Penelaah Teknis Kebijakan',
                             'Pranata Komputer Ahli Pertama', 'Pranata Komputer Terampil', 'Pengadministrasi Perkantoran',
                             'Pengemudi', 'Petugas Keamanan', 'Pramubakti'
                         ];
                         const cleanJab = this.selectedJabatan.trim();
                         const matchJab = jabatanList.find(j => j.toLowerCase() === cleanJab.toLowerCase());
                         if (!matchJab && cleanJab !== '') {
                             this.isCustomJabatan = true;
                             this.customJabatan = cleanJab;
                         }
                     }
                 }">
                <div>
                    <label for="pangkat_golongan" class="block text-sm font-semibold text-slate-700">Pangkat / Golongan</label>
                    <select id="pangkat_golongan_select" 
                            @change="if($event.target.value === '__custom__') { isCustomGolongan = true; selectedGolongan = customGolongan; } else { isCustomGolongan = false; selectedGolongan = $event.target.value; }"
                            class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">-- Pilih Golongan --</option>
                        <optgroup label="PNS Golongan IV (Pembina)">
                            <option value="IV/e - Pembina Utama" :selected="selectedGolongan.startsWith('IV/e')">IV/e - Pembina Utama</option>
                            <option value="IV/d - Pembina Utama Madya" :selected="selectedGolongan.startsWith('IV/d')">IV/d - Pembina Utama Madya</option>
                            <option value="IV/c - Pembina Utama Muda" :selected="selectedGolongan.startsWith('IV/c')">IV/c - Pembina Utama Muda</option>
                            <option value="IV/b - Pembina Tingkat I" :selected="selectedGolongan.startsWith('IV/b')">IV/b - Pembina Tingkat I</option>
                            <option value="IV/a - Pembina" :selected="selectedGolongan.startsWith('IV/a')">IV/a - Pembina</option>
                        </optgroup>
                        <optgroup label="PNS Golongan III (Penata)">
                            <option value="III/d - Penata Tingkat I" :selected="selectedGolongan.startsWith('III/d')">III/d - Penata Tingkat I</option>
                            <option value="III/c - Penata" :selected="selectedGolongan.startsWith('III/c')">III/c - Penata</option>
                            <option value="III/b - Penata Muda Tingkat I" :selected="selectedGolongan.startsWith('III/b')">III/b - Penata Muda Tingkat I</option>
                            <option value="III/a - Penata Muda" :selected="selectedGolongan.startsWith('III/a')">III/a - Penata Muda</option>
                        </optgroup>
                        <optgroup label="PNS Golongan II (Pengatur)">
                            <option value="II/d - Pengatur Tingkat I" :selected="selectedGolongan.startsWith('II/d')">II/d - Pengatur Tingkat I</option>
                            <option value="II/c - Pengatur" :selected="selectedGolongan.startsWith('II/c')">II/c - Pengatur</option>
                            <option value="II/b - Pengatur Muda Tingkat I" :selected="selectedGolongan.startsWith('II/b')">II/b - Pengatur Muda Tingkat I</option>
                            <option value="II/a - Pengatur Muda" :selected="selectedGolongan.startsWith('II/a')">II/a - Pengatur Muda</option>
                        </optgroup>
                        <optgroup label="PNS Golongan I (Juru)">
                            <option value="I/d - Juru Tingkat I" :selected="selectedGolongan.startsWith('I/d')">I/d - Juru Tingkat I</option>
                            <option value="I/c - Juru" :selected="selectedGolongan.startsWith('I/c')">I/c - Juru</option>
                            <option value="I/b - Juru Muda Tingkat I" :selected="selectedGolongan.startsWith('I/b')">I/b - Juru Muda Tingkat I</option>
                            <option value="I/a - Juru Muda" :selected="selectedGolongan.startsWith('I/a')">I/a - Juru Muda</option>
                        </optgroup>
                        <optgroup label="PPPK (Pegawai Perjanjian Kerja)">
                            <option value="PPPK - Golongan X" :selected="selectedGolongan.includes('X') && selectedGolongan.includes('PPPK')">PPPK - Golongan X (S1 / Profesi)</option>
                            <option value="PPPK - Golongan IX" :selected="selectedGolongan.includes('IX') && selectedGolongan.includes('PPPK')">PPPK - Golongan IX (S1 / D4)</option>
                            <option value="PPPK - Golongan VII" :selected="selectedGolongan.includes('VII') && selectedGolongan.includes('PPPK')">PPPK - Golongan VII (D3)</option>
                            <option value="PPPK - Golongan V" :selected="selectedGolongan.includes('V') && selectedGolongan.includes('PPPK')">PPPK - Golongan V (SMA/SMK)</option>
                        </optgroup>
                        <option value="__custom__" :selected="isCustomGolongan">+ Lainnya (Input Manual)</option>
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
                            <option value="Inspektur" :selected="selectedJabatan === 'Inspektur'">Inspektur</option>
                            <option value="Plt. Inspektur" :selected="selectedJabatan === 'Plt. Inspektur'">Plt. Inspektur</option>
                            <option value="Sekretaris" :selected="selectedJabatan === 'Sekretaris'">Sekretaris</option>
                            <option value="Sekretaris (Plt. Inspektur)" :selected="selectedJabatan.includes('Sekretaris') && selectedJabatan.includes('Plt')">Sekretaris (Plt. Inspektur)</option>
                            <option value="Inspektur Pembantu Wilayah I" :selected="selectedJabatan.includes('Wilayah I') || selectedJabatan === 'IRBAN I'">Inspektur Pembantu Wilayah I</option>
                            <option value="Inspektur Pembantu Wilayah II" :selected="selectedJabatan.includes('Wilayah II') || selectedJabatan === 'IRBAN II'">Inspektur Pembantu Wilayah II</option>
                            <option value="Inspektur Pembantu Wilayah III" :selected="selectedJabatan.includes('Wilayah III') || selectedJabatan === 'IRBAN III'">Inspektur Pembantu Wilayah III</option>
                            <option value="Inspektur Pembantu Wilayah IV" :selected="selectedJabatan.includes('Wilayah IV') || selectedJabatan === 'IRBAN IV'">Inspektur Pembantu Wilayah IV</option>
                            <option value="Kepala Sub Bagian Umum dan Kepegawaian" :selected="selectedJabatan.includes('Kasubbag') || selectedJabatan.includes('SUB BAGIAN')">Kepala Sub Bagian Umum dan Kepegawaian</option>
                        </optgroup>
                        <optgroup label="Jabatan Fungsional Auditor">
                            <option value="Auditor Ahli Utama" :selected="selectedJabatan.includes('Auditor') && selectedJabatan.includes('Utama')">Auditor Ahli Utama</option>
                            <option value="Auditor Ahli Madya" :selected="selectedJabatan.includes('Auditor') && selectedJabatan.includes('Madya')">Auditor Ahli Madya</option>
                            <option value="Auditor Ahli Muda" :selected="selectedJabatan.includes('Auditor') && selectedJabatan.includes('Muda')">Auditor Ahli Muda</option>
                            <option value="Auditor Ahli Pertama" :selected="selectedJabatan.includes('Auditor') && selectedJabatan.includes('Pertama')">Auditor Ahli Pertama</option>
                            <option value="Auditor Terampil" :selected="selectedJabatan.includes('Auditor') && selectedJabatan.includes('Terampil')">Auditor Terampil</option>
                        </optgroup>
                        <optgroup label="Jabatan Fungsional PPUPD">
                            <option value="Pengawas Penyelenggaraan Urusan Pemerintahan Daerah (PPUPD) Ahli Madya" :selected="selectedJabatan.includes('PPUPD') && selectedJabatan.includes('Madya')">PPUPD Ahli Madya</option>
                            <option value="Pengawas Penyelenggaraan Urusan Pemerintahan Daerah (PPUPD) Ahli Muda" :selected="selectedJabatan.includes('PPUPD') && selectedJabatan.includes('Muda')">PPUPD Ahli Muda</option>
                            <option value="Pengawas Penyelenggaraan Urusan Pemerintahan Daerah (PPUPD) Ahli Pertama" :selected="selectedJabatan.includes('PPUPD') && selectedJabatan.includes('Pertama')">PPUPD Ahli Pertama</option>
                        </optgroup>
                        <optgroup label="Fungsional &amp; Pelaksana Lainnya">
                            <option value="Perencana Ahli Muda" :selected="selectedJabatan.includes('Perencana')">Perencana Ahli Muda</option>
                            <option value="Analis Kebijakan" :selected="selectedJabatan.includes('Analis Kebijakan')">Analis Kebijakan</option>
                            <option value="Penelaah Teknis Kebijakan" :selected="selectedJabatan.includes('Penelaah Teknis')">Penelaah Teknis Kebijakan</option>
                            <option value="Pranata Komputer Ahli Pertama" :selected="selectedJabatan.includes('Pranata Komputer') && selectedJabatan.includes('Pertama')">Pranata Komputer Ahli Pertama</option>
                            <option value="Pranata Komputer Terampil" :selected="selectedJabatan.includes('Pranata Komputer') && selectedJabatan.includes('Terampil')">Pranata Komputer Terampil</option>
                            <option value="Pengadministrasi Perkantoran" :selected="selectedJabatan.includes('Pengadministrasi')">Pengadministrasi Perkantoran</option>
                            <option value="Pengemudi" :selected="selectedJabatan === 'Pengemudi'">Pengemudi</option>
                            <option value="Petugas Keamanan" :selected="selectedJabatan.includes('Keamanan')">Petugas Keamanan</option>
                            <option value="Pramubakti" :selected="selectedJabatan === 'Pramubakti'">Pramubakti</option>
                        </optgroup>
                        <option value="__custom__" :selected="isCustomJabatan">+ Lainnya (Input Manual)</option>
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
