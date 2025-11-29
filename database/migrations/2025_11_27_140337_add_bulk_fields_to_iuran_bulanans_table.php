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
        Schema::table('iuran_bulanans', function (Blueprint $table) {
            //
            $table->string('request_batch_id')->nullable()->index();
            $table->string('request_type')->default('regular')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('iuran_bulanans', function (Blueprint $table) {
            //
        });
    }
};
