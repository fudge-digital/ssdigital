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
        Schema::create('student_documents', function (Blueprint $table) {
            $table->id();
            // sesuaikan foreign key ke siswa (user_id atau siswa_profile id).
            // Aku asumsikan kamu pakai siswa_profiles table with PK id.
            $table->foreignId('siswa_profile_id')->constrained('siswa_profiles')->cascadeOnDelete();

            $table->enum('type', ['kk', 'akta', 'lain'])->index();
            $table->string('title')->nullable(); // untuk 'lain' berisi judul dokumen
            $table->string('file_path'); // path relatif di storage disk public
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_documents');
    }
};
