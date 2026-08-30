<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Apunta a tu archivo (cambia 'tu_archivo.sql' por el nombre real)
        $path = database_path('agostadero.sql'); 
        
        // 2. Extraemos las credenciales del entorno de producción
        $host = config('database.connections.mysql.host');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');
        $db   = config('database.connections.mysql.database');

        // 3. Ejecutamos la importación nativa sin saturar PHP
        exec("mysql -h {$host} -u {$user} -p'{$pass}' {$db} < {$path}");
    }
}