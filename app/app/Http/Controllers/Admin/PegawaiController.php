<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\User;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Carbon\Carbon;

class PegawaiController extends Controller
{
    public function index()
    {
        $pegawai = Pegawai::with('unitKerja', 'user')->get();
        return view('admin.pegawai.index', compact('pegawai'));
    }

    public function create()
    {
        $unitKerja = UnitKerja::where('aktif', true)->get();
        return view('admin.pegawai.create', compact('unitKerja'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nip' => 'required|string|size:18|unique:pegawai,nip',
            'nip_lama' => 'nullable|string|max:9',
            'nama_lengkap' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email',
            'jenis_kelamin' => 'required|in:L,P',
            'tmt_cpns' => 'required|date',
            'tmt_pns' => 'nullable|date|after_or_equal:tmt_cpns',
            'pangkat_golongan' => 'required|string|max:30',
            'jabatan' => 'required|string|max:150',
            'unit_kerja_id' => 'required|exists:unit_kerja,id',
            'jenis_pegawai' => 'required|in:PNS,CPNS,PPPK',
            'nomor_hp' => 'nullable|string|max:20',
            'role' => 'required|in:pegawai,admin_cuti,super_admin',
            'bisa_beri_izin_sementara' => 'required|boolean',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // 1. Buat User Account
                $user = User::create([
                    'name' => $request->nama_lengkap,
                    'email' => $request->email,
                    'password' => bcrypt('password'), // password default
                    'role' => $request->role,
                    'bisa_beri_izin_sementara' => $request->bisa_beri_izin_sementara,
                ]);

                // 2. Buat Profil Pegawai
                Pegawai::create([
                    'user_id' => $user->id,
                    'nip' => $request->nip,
                    'nip_lama' => $request->nip_lama,
                    'nama_lengkap' => $request->nama_lengkap,
                    'jenis_kelamin' => $request->jenis_kelamin,
                    'tmt_cpns' => $request->tmt_cpns,
                    'tmt_pns' => $request->tmt_pns,
                    'pangkat_golongan' => $request->pangkat_golongan,
                    'jabatan' => $request->jabatan,
                    'unit_kerja_id' => $request->unit_kerja_id,
                    'jenis_pegawai' => $request->jenis_pegawai,
                    'nomor_hp' => $request->nomor_hp,
                    'aktif' => true,
                ]);
            });

            return redirect()->route('admin.pegawai.index')->with('success', 'Pegawai & Akun berhasil ditambahkan. Password default adalah "password".');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan pegawai: ' . $e->getMessage());
        }
    }

    public function edit(Pegawai $pegawai)
    {
        $unitKerja = UnitKerja::where('aktif', true)->get();
        $pegawai->load('user');
        
        $tahun = Carbon::now()->year;
        $saldo = $pegawai->saldoTahunan()->where('tahun', $tahun)->first();

        return view('admin.pegawai.edit', compact('pegawai', 'unitKerja', 'saldo', 'tahun'));
    }

    public function update(Request $request, Pegawai $pegawai)
    {
        $request->validate([
            'nip' => 'required|string|size:18|unique:pegawai,nip,' . $pegawai->id,
            'nip_lama' => 'nullable|string|max:9',
            'nama_lengkap' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email,' . $pegawai->user_id,
            'jenis_kelamin' => 'required|in:L,P',
            'tmt_cpns' => 'required|date',
            'tmt_pns' => 'nullable|date|after_or_equal:tmt_cpns',
            'pangkat_golongan' => 'required|string|max:30',
            'jabatan' => 'required|string|max:150',
            'unit_kerja_id' => 'required|exists:unit_kerja,id',
            'jenis_pegawai' => 'required|in:PNS,CPNS,PPPK',
            'nomor_hp' => 'nullable|string|max:20',
            'role' => 'required|in:pegawai,admin_cuti,super_admin',
            'bisa_beri_izin_sementara' => 'required|boolean',
            'aktif' => 'required|boolean',
            
            // Validasi Saldo Cuti
            'jatah_tahun_berjalan' => 'required|integer|min:0',
            'carry_over_n1' => 'required|integer|min:0',
            'carry_over_n2' => 'required|integer|min:0',
            'tambahan_cuti_bersama' => 'required|integer|min:0',
            'terpakai' => 'required|integer|min:0',
            
            // Password baru opsional
            'password' => 'nullable|string|min:6',
        ]);

        try {
            DB::transaction(function () use ($request, $pegawai) {
                // 1. Update User Account
                $userData = [
                    'name' => $request->nama_lengkap,
                    'email' => $request->email,
                    'role' => $request->role,
                    'bisa_beri_izin_sementara' => $request->bisa_beri_izin_sementara,
                ];

                if ($request->filled('password')) {
                    $userData['password'] = bcrypt($request->password);
                }

                $pegawai->user->update($userData);

                // 2. Update Profil Pegawai
                $pegawai->update([
                    'nip' => $request->nip,
                    'nip_lama' => $request->nip_lama,
                    'nama_lengkap' => $request->nama_lengkap,
                    'jenis_kelamin' => $request->jenis_kelamin,
                    'tmt_cpns' => $request->tmt_cpns,
                    'tmt_pns' => $request->tmt_pns,
                    'pangkat_golongan' => $request->pangkat_golongan,
                    'jabatan' => $request->jabatan,
                    'unit_kerja_id' => $request->unit_kerja_id,
                    'jenis_pegawai' => $request->jenis_pegawai,
                    'nomor_hp' => $request->nomor_hp,
                    'aktif' => $request->aktif,
                ]);

                // 3. Update / Create Saldo Cuti Tahunan untuk tahun berjalan
                $tahun = Carbon::now()->year;
                $pegawai->saldoTahunan()->updateOrCreate(
                    ['tahun' => $tahun],
                    [
                        'jatah_tahun_berjalan' => $request->jatah_tahun_berjalan,
                        'carry_over_n1' => $request->carry_over_n1,
                        'carry_over_n2' => $request->carry_over_n2,
                        'tambahan_cuti_bersama' => $request->tambahan_cuti_bersama,
                        'terpakai' => $request->terpakai,
                    ]
                );
            });

            return redirect()->route('admin.pegawai.index')->with('success', 'Pegawai, akun, dan saldo cuti berhasil diperbarui.');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }
}
