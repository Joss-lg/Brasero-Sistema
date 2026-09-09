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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('categorias')->cascadeOnDelete();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            
            // Precio base (default 0 si el costo lo fijan variantes o peso)
            $table->decimal('precio', 10, 2)->nullable()->default(0);
            
            // Flags de configuración
            $table->boolean('tiene_variantes')->default(false);
            $table->boolean('se_vende_por_peso')->default(false);
            $table->decimal('precio_por_100g', 10, 2)->nullable()->default(null);
            $table->boolean('esta_disponible')->default(true);

            // Lógica de imágenes desactivada temporalmente.
            // $table->binary('imagen')->nullable()->comment('Los bytes binarios de la imagen');
            // $table->string('imagen_mime_type', 100)->nullable()->comment('Ej. image/jpeg, image/png');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};