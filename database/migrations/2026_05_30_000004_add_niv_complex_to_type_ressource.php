<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->repairNiveauxComplexiteSchema();

        if (! Schema::hasColumn('type_ressource', 'id_niv_complex')) {
            Schema::table('type_ressource', function (Blueprint $table) {
                $table->unsignedBigInteger('id_niv_complex')->nullable()->after('typ_res');
            });
        }

        $this->addForeignKeyIfMissing();
    }

    public function down(): void
    {
        if (Schema::hasColumn('type_ressource', 'id_niv_complex')) {
            Schema::table('type_ressource', function (Blueprint $table) {
                $table->dropForeign(['id_niv_complex']);
                $table->dropColumn('id_niv_complex');
            });
        }
    }

    private function repairNiveauxComplexiteSchema(): void
    {
        if (Schema::hasTable('niveau_complexite') && ! Schema::hasTable('niveaux_complexite')) {
            Schema::rename('niveau_complexite', 'niveaux_complexite');
        }

        if (! Schema::hasTable('niveaux_complexite')) {
            return;
        }

        if (Schema::hasColumn('niveaux_complexite', 'lib_niveau')
            && ! Schema::hasColumn('niveaux_complexite', 'lib_niv_complex')) {
            Schema::table('niveaux_complexite', function (Blueprint $table) {
                $table->renameColumn('lib_niveau', 'lib_niv_complex');
            });
        }

        if (Schema::hasColumn('niveaux_complexite', 'coef_hor')
            && ! Schema::hasColumn('niveaux_complexite', 'coeff_niv_complex')) {
            Schema::table('niveaux_complexite', function (Blueprint $table) {
                $table->renameColumn('coef_hor', 'coeff_niv_complex');
            });
        }

        Schema::table('niveaux_complexite', function (Blueprint $table) {
            if (! Schema::hasColumn('niveaux_complexite', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (! Schema::hasColumn('niveaux_complexite', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    private function addForeignKeyIfMissing(): void
    {
        Schema::table('type_ressource', function (Blueprint $table) {
            $table->foreign('id_niv_complex')
                ->references('id_niv_complex')
                ->on('niveaux_complexite')
                ->nullOnDelete();
        });
    }
};
