<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Crea la tabla de variantes
        Schema::create('producto_variantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->string('nombre');
            $table->decimal('precio', 10, 2);
            $table->boolean('esta_disponible')->default(true);
            $table->timestamps();
        });

        // 2. Conecta la relación foránea hacia detalles_orden de forma segura
        if (Schema::hasTable('detalles_orden') && Schema::hasColumn('detalles_orden', 'producto_variante_id')) {
            Schema::table('detalles_orden', function (Blueprint $table) {
                $table->foreign('producto_variante_id')
                      ->references('id')
                      ->on('producto_variantes')
                      ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('detalles_orden')) {
            Schema::table('detalles_orden', function (Blueprint $table) {
                $table->dropForeign(['producto_variante_id']);
            });
        }

        Schema::dropIfExists('producto_variantes');
    }
};