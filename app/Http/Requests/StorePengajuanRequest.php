<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StorePengajuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        $rules = [
            'jenis_pengajuan' => ['required', 'string', Rule::in(['transkrip_sementara', 'hapus_mata_kuliah'])],
            // catatan_mahasiswa tidak lagi divalidasi secara umum di sini
        ];

        if ($this->input('jenis_pengajuan') === 'transkrip_sementara') {
            $rules += [
                'keperluan_transkrip' => ['required', 'string',                     
                Rule::in([ // <-- PERBARUI DAFTAR INI
                        'Beasiswa',
                        'Konseling ke Kemahasiswaan', // Tambahan
                        'PKL', // Tambahan
                        'Pendaftaran Seminar Hasil', // Tambahan
                        'Konsultasi DPA untuk mahasiswa yang memprogram KRS Tugas Akhir/Skripsi', // Tambahan
                        'Melamar Pekerjaan',
                        'Lainnya',
                    ]),],
                'bahasa_transkrip' => ['required', Rule::in(['indonesia', 'inggris'])],
            ];
        }

        if ($this->input('jenis_pengajuan') === 'hapus_mata_kuliah') {
            $rules += [
                'mata_kuliah_ids' => ['required', 'array', 'min:1'],
                'mata_kuliah_ids.*' => ['integer', 'exists:mata_kuliahs,id'],
                'sks_lulus_total' => ['nullable', 'integer', 'min:0'],
                'sks_lulus_wajib' => ['nullable', 'integer', 'min:0'],
                'sks_lulus_pilihan' => ['nullable', 'integer', 'min:0'],
                'perihal' => ['required', 'string', Rule::in(['Pendaftaran Semhas dan Sidang Skripsi', 'Pendaftaran Ujian Khusus'])],
                'file_pendukung' => [ // Ini adalah nama field dari form Vue
                    'nullable', // Atau 'required' jika wajib
                    'file',
                    'mimes:pdf,jpg,jpeg,png,doc,docx',
                    'max:2048', // 2MB
                ],
                // catatan_mahasiswa dihapus dari sini jika tidak ada di form hapus MK
            ];
        }
        return $rules;
    }

    public function messages(): array
    {
        return [
            // ... pesan lain ...
            'perihal.required' => 'Perihal pengajuan wajib dipilih.',
            'perihal.in' => 'Pilihan perihal tidak valid.',
            'file_pendukung.file' => 'File pendukung harus berupa file.',
            'file_pendukung.mimes' => 'Format file pendukung tidak valid. Hanya PDF, JPG, PNG, DOC, DOCX yang diizinkan.',
            'file_pendukung.max' => 'Ukuran file pendukung maksimal 2MB.',
        ];
    }
    
    protected function prepareForValidation(): void
    {
        if ($this->input('jenis_pengajuan') === 'hapus_mata_kuliah' || $this->input('jenis_pengajuan') === 'transkrip_sementara') {
             // Konversi SKS jika ada di request dan jenisnya sesuai (jika field SKS juga ada di form transkrip)
            if ($this->has('sks_lulus_total')) {
                $this->merge(['sks_lulus_total' => $this->sks_lulus_total === '' ? null : $this->sks_lulus_total]);
            }
            if ($this->has('sks_lulus_wajib')) {
                $this->merge(['sks_lulus_wajib' => $this->sks_lulus_wajib === '' ? null : $this->sks_lulus_wajib]);
            }
            if ($this->has('sks_lulus_pilihan')) {
                $this->merge(['sks_lulus_pilihan' => $this->sks_lulus_pilihan === '' ? null : $this->sks_lulus_pilihan]);
            }
        }
    }
}