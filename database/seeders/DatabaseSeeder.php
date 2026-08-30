<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // <-- Se agregó esta línea

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Ejecutar directamente tu respaldo SQL
        // Reemplaza 'nombre_de_tu_archivo.sql' con el nombre exacto de tu archivo
        DB::unprepared(file_get_contents(database_path('agostadero.sql')));

        // 2. Comentamos temporalmente los otros seeders para evitar errores de duplicidad
        // $this->call(ModuloSeeder::class);
        // $this->call(RoleSeeder::class);
        // $this->call(UsuarioAdminSeeder::class);
    }
}