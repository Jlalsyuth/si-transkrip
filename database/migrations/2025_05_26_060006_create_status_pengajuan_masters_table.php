<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_status_pengajuan_masters_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_pengajuan_masters', function (Blueprint $table) {
            $table->id();
            $table->string('kode_status')->unique();
            $table->string('nama_status_display');
            $table->integer('urutan_timeline')->default(0);
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_pengajuan_masters');
    }
};