<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FormHapusMatkulController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nim' => 'required|string|max:20',
            'nama' => 'required|string|max:255',
            'email' => 'required|email',
            'program_studi' => 'required|string',
            'jumlah_sks' => 'nullable|numeric',
            'sks_wajib' => 'nullable|numeric',
            'sks_pilihan' => 'nullable|numeric',
            'perihal' => 'required|in:Semhas,Ujian Khusus',
            'matkul' => 'array',
            'file' => 'nullable|file|mimes:pdf|max:1024',
        ]);

        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('transkrip', 'public');
        }

        return redirect()->back()->with('success', 'Form berhasil dikirim!');
    }
}
