<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void{
//        $permission = Permission::create(['name' => 'Produccion primaria']);
//        $permission = Permission::create(['name' => 'Recoleccion']);

//        $table->string('name');
//        $table->string('username')->unique();
//        $table->string('role')->default('Usuario');
//        $table->string('avatar')->default('default.png');
//        $table->string('email')->nullable();
//        $table->timestamp('email_verified_at')->nullable();
//        $table->string('password');
        $user = User::create([
            'name' => 'Adimer Paul Chambi Ajata',
            'username' => 'admin',
            'role' => 'Administrador',
            'avatar' => 'default.png',
            'email' => 'adimer101@gmail.com',
            'email_verified_at' => now(),
            'password' => bcrypt('admin123Admin'),
        ]);
        $permisos = [
            'Dashboard',
            'Produccion primaria',
            'Recoleccion',
            'Procesamiento',
            'Almacenamiento',
            'Despacho',
            'Usuarios',
            'Reportes',
            'Configuracion',
            'Soporte',
        ];
        foreach ($permisos as $permiso) {
            Permission::create(['name' => $permiso]);
        }
        $user->givePermissionTo(Permission::all());
    }
}
