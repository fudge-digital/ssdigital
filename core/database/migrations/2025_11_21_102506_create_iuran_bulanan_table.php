<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('iuran_bulanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('users')->onDelete('cascade');
            $table->string('bulan'); // format: 2025-11
            $table->integer('jumlah')->default(325000);
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->string('bukti')->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iuran_bulanans');
    }
};
