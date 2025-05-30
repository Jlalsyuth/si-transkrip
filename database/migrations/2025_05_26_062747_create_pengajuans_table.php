<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('jenis_pengajuan', ['transkrip_sementara', 'hapus_mata_kuliah']);
            $table->string('keperluan_transkrip')->nullable(); 
            $table->enum('bahasa_transkrip', ['indonesia', 'inggris'])->nullable();
            
            // TAMBAHKAN KOLOM PERIHAL DI SINI
            $table->string('perihal')->nullable();
            $table->timestamp('tanggal_pengajuan')->useCurrent();
            $table->foreignId('current_status_id')->constrained('status_pengajuan_masters');
            $table->text('catatan_mahasiswa')->nullable(); // Akan dihapus dari form hapus MK
            $table->text('catatan_operator')->nullable();
            $table->text('catatan_kps')->nullable();
            $table->string('file_transkrip_sementara_unggah_mhs')->nullable(); // Akan digunakan untuk file pendukung hapus MK
            $table->string('file_transkrip_final_operator')->nullable();
            $table->string('file_tanda_tangan_kps')->nullable();
            $table->integer('sks_lulus_total')->nullable();
            $table->integer('sks_lulus_wajib')->nullable();
            $table->integer('sks_lulus_pilihan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuans');
    }
};