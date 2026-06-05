<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enseignants', function (Blueprint $table) {
            // Drop the existing foreign key constraint before modifying the column
            $table->dropForeign(['user_log_sp']);

            // Make the column nullable
            $table->string('user_log_sp', 50)->nullable()->change();

            // Recreate the foreign key with SET NULL on delete
            $table->foreign('user_log_sp')
                ->references('user_log_sp')
                ->on('secretaire_principal')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('enseignants', function (Blueprint $table) {
            // Drop the nullable foreign key constraint before reverting the column
            $table->dropForeign(['user_log_sp']);

            // Revert the column to non-nullable
            $table->string('user_log_sp', 50)->nullable(false)->change();

            // Recreate the original foreign key with CASCADE on delete
            $table->foreign('user_log_sp')
                ->references('user_log_sp')
                ->on('secretaire_principal')
                ->onDelete('cascade');
        });
    }
};
