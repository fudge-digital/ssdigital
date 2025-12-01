<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE `student_documents` MODIFY `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP()');
        DB::statement('ALTER TABLE `student_documents` MODIFY `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()');

        Schema::table('student_documents', function (Blueprint $table) {
            $table->dropTimestamps();
        });

        Schema::table('student_documents', function (Blueprint $table) {
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_documents', function (Blueprint $table) {
            //
        });
    }
};
