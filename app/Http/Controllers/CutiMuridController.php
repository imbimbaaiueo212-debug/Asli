<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CutiMurid;

class CutiMuridController extends Controller
{
    public function index()
    {
        $cuti = CutiMurid::with('bukuInduk')
            ->latest()
            ->paginate(20);

        return view('cuti.index', compact('cuti'));
    }
}