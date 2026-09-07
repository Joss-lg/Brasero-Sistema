<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
{
    $path = database_path('agostadero.sql'); 
    
    $host = config('database.connections.mysql.host');
    $user = config('database.connections.mysql.username');
    $pass = config('database.connections.mysql.password');
    $db   = config('database.connections.mysql.database');

    // Solo incluye la bandera -p si existe contraseña configurada
    $passwordFlag = !empty($pass) ? "-p\"{$pass}\"" : '';

    // Se envuelven las rutas y nombres entre comillas por compatibilidad con Windows
    exec("mysql -h {$host} -u {$user} {$passwordFlag} {$db} < \"{$path}\"");
    }
}