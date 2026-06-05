<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop FK pointing to the old `grade` (singular) table
        Schema::table('enseignants', function (Blueprint $table) {
            $table->dropForeign(['id_grade']);
        });

        // Re-add FK pointing to the correct `grades` (plural) table
        Schema::table('enseignants', function (Blueprint $table) {
            $table->foreign('id_grade')
                  ->references('id_grade')
                  ->on('grades')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('enseignants', function (Blueprint $table) {
            $table->dropForeign(['id_grade']);
        });

        Schema::table('enseignants', function (Blueprint $table) {
            $table->foreign('id_grade')
                  ->references('id_grade')
                  ->on('grade')
                  ->onDelete('restrict');
        });
    }
};
