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
        Schema::create('pembayaran_siswa_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembayaran_id')->constrained('pembayaran_siswa')->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_siswa_detail');
    }
};
