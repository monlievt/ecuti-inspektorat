<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PanduanController extends Controller
{
    /**
     * Tampilkan halaman Buku Panduan Pengguna Interaktif.
     */
    public function index()
    {
        return view('panduan.index');
    }
}
