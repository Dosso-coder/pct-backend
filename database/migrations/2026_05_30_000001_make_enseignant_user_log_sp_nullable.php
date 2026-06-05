<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enseignants', function (Blueprint $table) {
            $table->dropForeign(['user_log_sp']);
            $table->string('user_log_sp')->nullable()->change();
        });

        // Recreate FK with SET NULL on delete
        Schema::table('enseignants', function (Blueprint $table) {
            $table->foreign('user_log_sp')
                ->references('user_log_sp')
                ->on('secretaire_principal')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('enseignants', function (Blueprint $table) {
            $table->dropForeign(['user_log_sp']);
            $table->string('user_log_sp')->nullable(false)->change();
        });

        Schema::table('enseignants', function (Blueprint $table) {
            $table->foreign('user_log_sp')
                ->references('user_log_sp')
                ->on('secretaire_principal')
                ->onDelete('cascade');
        });
    }
};

