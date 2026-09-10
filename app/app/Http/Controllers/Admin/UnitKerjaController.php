<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnitKerja;
use Illuminate\Http\Request;

class UnitKerjaController extends Controller
{
    public function index()
    {
        $unitKerja = UnitKerja::with('parent')->orderBy('kode')->get();
        return view('admin.unit-kerja.index', compact('unitKerja'));
    }

    public function create()
    {
        $parentUnits = UnitKerja::where('aktif', true)->get();
        return view('admin.unit-kerja.create', compact('parentUnits'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|max:30|unique:unit_kerja,kode',
            'nama' => 'required|string|max:150',
            'parent_id' => 'nullable|exists:unit_kerja,id',
        ]);

        UnitKerja::create([
            'kode' => strtoupper($request->kode),
            'nama' => $request->nama,
            'parent_id' => $request->parent_id,
            'aktif' => true,
        ]);

        return redirect()->route('admin.unit-kerja.index')->with('success', 'Unit Kerja berhasil ditambahkan.');
    }

    public function edit(UnitKerja $unitKerja)
    {
        $parentUnits = UnitKerja::where('aktif', true)->where('id', '!=', $unitKerja->id)->get();
        return view('admin.unit-kerja.edit', compact('unitKerja', 'parentUnits'));
    }

    public function update(Request $request, UnitKerja $unitKerja)
    {
        $request->validate([
            'kode' => 'required|string|max:30|unique:unit_kerja,kode,' . $unitKerja->id,
            'nama' => 'required|string|max:150',
            'parent_id' => 'nullable|exists:unit_kerja,id|different:id',
            'aktif' => 'required|boolean',
        ]);

        $unitKerja->update([
            'kode' => strtoupper($request->kode),
            'nama' => $request->nama,
            'parent_id' => $request->parent_id,
            'aktif' => $request->aktif,
        ]);

        return redirect()->route('admin.unit-kerja.index')->with('success', 'Unit Kerja berhasil diperbarui.');
    }

    public function destroy(UnitKerja $unitKerja)
    {
        if ($unitKerja->pegawai()->exists()) {
            $total = $unitKerja->pegawai()->count();
            return redirect()->route('admin.unit-kerja.index')
                ->with('error', "Unit Kerja '{$unitKerja->nama}' tidak dapat dihapus karena masih memiliki {$total} data pegawai aktif.");
        }

        if ($unitKerja->children()->exists()) {
            $totalSub = $unitKerja->children()->count();
            return redirect()->route('admin.unit-kerja.index')
                ->with('error', "Unit Kerja '{$unitKerja->nama}' tidak dapat dihapus karena masih memiliki {$totalSub} sub-unit bawahan.");
        }

        $nama = $unitKerja->nama;
        $unitKerja->delete();

        return redirect()->route('admin.unit-kerja.index')->with('success', "Unit Kerja '{$nama}' berhasil dihapus.");
    }
}
