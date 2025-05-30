<?php

namespace App\Http\Requests\KPS;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Pengajuan;
use App\Models\StatusPengajuanMaster;
use Illuminate\Validation\Rule;

class KPSProsesHapusMkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $pengajuan = $this->route('pengajuan');
        if (!$pengajuan instanceof Pengajuan) {
            return false;
        }
        $kpsUser = Auth::user();

        return $kpsUser &&
               isset($kpsUser->role) && $kpsUser->role === 'kps' &&
               isset($kpsUser->program_studi) &&
               $pengajuan->jenis_pengajuan === 'hapus_mata_kuliah' && // Pastikan jenisnya benar
               $pengajuan->mahasiswa->program_studi === $kpsUser->program_studi;
        // Tambahkan pengecekan status jika KPS hanya boleh aksi pada status tertentu
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $pengajuan = $this->route('pengajuan');

        return [
            'new_status_id' => [
                'required', 
                'integer', 
                Rule::exists('status_pengajuan_masters', 'id')
            ],
            'catatan_kps' => ['nullable', 'string', 'max:5000'],
            'file_tanda_tangan_kps' => [ // File yang diupload KPS
                Rule::requiredIf(function () use ($pengajuan) {
                    // Contoh: Wajib jika status diubah menjadi yang menandakan persetujuan final dari KPS
                    // dan file TTD KPS belum ada atau ingin diganti.
                    $statusTerpilih = StatusPengajuanMaster::find($this->input('new_status_id'));
                    return $statusTerpilih && $statusTerpilih->kode_status === 'DISETUJUI_KPS'; // Sesuaikan kode_status
                }),
                'nullable',
                'file',
                'mimes:pdf', 
                'max:2048',  // 2MB
            ],
            // Validasi untuk persetujuan_mk per item dihapus
        ];
    }

    public function messages(): array
    {
        return [
            'new_status_id.required' => 'Status pengajuan baru wajib dipilih.',
            'file_tanda_tangan_kps.required' => 'File keputusan/TTD KPS wajib diunggah jika pengajuan disetujui sepenuhnya.',
            // ... pesan lain
        ];
    }
}