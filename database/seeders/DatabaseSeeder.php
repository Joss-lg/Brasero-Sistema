<?php
namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder

{
use WithoutModelEvents;
public function run(): void
{
// 1. Primero creamos todos los Modulos
$this->call(ModuloSeeder::class);
$this->call(RoleSeeder::class);
// 2. Creamos el rol Administrador y al SuperAdmin (ID 1) con acceso total por código.
$this->call(UsuarioAdminSeeder::class);
}
} 

