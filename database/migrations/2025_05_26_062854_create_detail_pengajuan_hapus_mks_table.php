<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_detail_pengajuan_hapus_mks_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_pengajuan_hapus_mks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuans')->cascadeOnDelete();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliahs')->cascadeOnDelete();
            $table->enum('status_persetujuan_kps', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu')->nullable();
            $table->text('alasan_tolak_kps')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pengajuan_hapus_mks');
    }
};