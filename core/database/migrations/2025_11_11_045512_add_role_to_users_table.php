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
        Schema::table('users', function (Blueprint $table) {
            // pakai enum supaya validasi DB, ubah jadi string jika tidak ingin enum
            $table->enum('role', [
                'super_admin',
                'admin',
                'manajer_tim',
                'pelatih',
                'asisten_pelatih',
                'orang_tua',
                'siswa'
            ])->default('orang_tua')->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
