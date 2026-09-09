<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalles_orden', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')->constrained('ordenes')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos');
            
            // Columna simple sin constraint foránea inmediata para evitar el error 150
            $table->unsignedBigInteger('producto_variante_id')->nullable();

            $table->string('lote_envio')->nullable();
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->string('estado')->default('en cocina');
            $table->string('estado_preparacion')->default('pendiente');
            $table->text('notas')->nullable();
            $table->string('tiempo')->nullable();
            $table->string('gramaje')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalles_orden');
    }
};