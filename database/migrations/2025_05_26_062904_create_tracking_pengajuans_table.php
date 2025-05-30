<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracking_pengajuans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuans')->cascadeOnDelete();
            $table->foreignId('status_id')->constrained('status_pengajuan_masters');
            $table->foreignId('user_pelaku_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan_tracking')->nullable();
            $table->timestamp('timestamp_perubahan')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_pengajuans');
    }
};