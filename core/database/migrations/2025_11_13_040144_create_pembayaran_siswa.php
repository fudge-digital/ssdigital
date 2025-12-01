<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pembayaran_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // orang tua yang bayar (pencatat)
            $table->foreignId('siswa_id')->constrained('users')->cascadeOnDelete(); // siswa terkait
            $table->string('jenis')->default('pendaftaran'); // contoh: 'pendaftaran', 'iuran_bulanan', 'jersey', ...
            $table->decimal('jumlah', 14, 2)->nullable(); // jumlah yang dibayarkan
            $table->string('status')->default('pending'); // 'pending','verified','rejected'
            $table->string('bukti')->nullable(); // path file bukti
            $table->text('catatan')->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('tanggal_verifikasi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_siswa');
    }
};
