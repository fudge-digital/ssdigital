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
        Schema::table('iuran_requests', function (Blueprint $table) {
            //
            $table->integer('student_count')->default(0);
            $table->bigInteger('total_tagihan')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('iuran_requests', function (Blueprint $table) {
            //
        });
    }
};
