<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_variantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')
                  ->constrained('productos')
                  ->cascadeOnDelete();
            
            $table->string('nombre'); // Ej: "Bistec", "Cecina", "Pechuga"
            $table->decimal('precio', 10, 2); // Ej: 89.00, 95.00
            $table->boolean('esta_disponible')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_variantes');
    }
};