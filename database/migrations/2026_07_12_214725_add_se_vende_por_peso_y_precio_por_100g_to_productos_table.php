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
        Schema::table('productos', function (Blueprint $table) {
            // Solo agrega la columna si no existe previamente
            if (!Schema::hasColumn('productos', 'se_vende_por_peso')) {
                $table->boolean('se_vende_por_peso')->default(false)->after('precio');
            }

            if (!Schema::hasColumn('productos', 'precio_por_100g')) {
                $table->decimal('precio_por_100g', 10, 2)->nullable()->after('se_vende_por_peso');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $columnasABorrar = [];

            if (Schema::hasColumn('productos', 'se_vende_por_peso')) {
                $columnasABorrar[] = 'se_vende_por_peso';
            }

            if (Schema::hasColumn('productos', 'precio_por_100g')) {
                $columnasABorrar[] = 'precio_por_100g';
            }

            if (!empty($columnasABorrar)) {
                $table->dropColumn($columnasABorrar);
            }
        });
    }
};