<?php

namespace Database\Seeders;

use App\Models\Apicultor;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void{
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
        $sqlPach = database_path('seeders/apicultores_202508150628.sql');
        if (File::exists($sqlPach)) {
            $sql = File::get($sqlPach);
            DB::unprepared($sql);
            $this->command->info('Apicultores imported successfully.');
        } else {
            $this->command->error('SQL file not found: ' . $sqlPach);
        }

//        $faker = Faker::create('es_ES');
//
//        $estados = ['Activo', 'Mantenimiento', 'Inactivo'];
//        $departamentos = ['La Paz', 'Cochabamba', 'Santa Cruz', 'Oruro', 'Potosí', 'Chuquisaca', 'Tarija', 'Beni', 'Pando'];
//        $municipios = ['El Alto','La Paz','Warnes','Montero','Quillacollo','Sacaba','Cochabamba','Yacuiba','Riberalta','Trinidad'];
//        $asociaciones = ['Asoc. Flor de Miel', 'Coop. La Abejita', 'Asoc. Valle Dulce', 'Apis del Sur', 'Miel del Norte'];
//
//        for ($i = 0; $i < 100; $i++) {
//            Apicultor::create([
//                // 'codigo' => auto en el modelo
//                'nombre' => $faker->name(),
//                'ci' => (string)$faker->numberBetween(1000000, 12000000),
//                'telefono' => $faker->optional()->phoneNumber(),
//                'email' => $faker->optional(0.6)->safeEmail(),
//                'departamento' => $faker->randomElement($departamentos),
//                'municipio' => $faker->randomElement($municipios),
//                'asociacion' => $faker->optional()->randomElement($asociaciones),
//                'estado' => $faker->randomElement($estados),
//                'apiarios' => $faker->numberBetween(0, 40),
//                'ultima_inspeccion' => $faker->optional()->dateTimeBetween('-8 months', 'now')?->format('Y-m-d'),
//                // Bolivia aprox. lat -22 a -9, lng -69 a -63 (muy aprox)
//                'lat' => $faker->optional()->randomFloat(7, -22.0, -9.0),
//                'lng' => $faker->optional()->randomFloat(7, -69.0, -63.0),
//                'observaciones' => $faker->optional()->sentence(8),
//            ]);
//        }

    }
}
