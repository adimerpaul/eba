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
    private function fixSequences(): void
    {
        foreach ([
            'departamentos',
            'provincias',
            'municipios',
            'organizaciones',
            'productores',
            'tipo_manejos',
            'tipo_miel',
            'apiarios',
            'acopio_cosechas'
                 ] as $table) {
            DB::statement("
            SELECT setval(
                pg_get_serial_sequence('{$table}', 'id'),
                COALESCE((SELECT MAX(id) FROM {$table}), 0)
            );
        ");
        }
    }

    public function run(): void
    {
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
            'Geografia',
            'Organizaciones',
            'Productores Crear',
            'Productores / Agricultores',
            'Acopios',
            'Usuarios',
            'Configuracion',
            'Soporte',
//            'Soporte',
        ];
        foreach ($permisos as $permiso) {
            Permission::create(['name' => $permiso]);
        }
        $user->givePermissionTo(Permission::all());

//        $sql = File::get(database_path('seeders/apicultores_202508150628.sql'));
//        DB::unprepared($sql);
        $sql = File::get(database_path('seeders/departamentos_202510050354.sql'));
        DB::unprepared($sql);
        $sql = File::get(database_path('seeders/provincias_202510050357.sql'));
        DB::unprepared($sql);
        $sql = File::get(database_path('seeders/municipios_202510050357.sql'));
        DB::unprepared($sql);
        $sql = File::get(database_path('seeders/organizaciones_202510050431.sql'));
        DB::unprepared($sql);
        $sql = File::get(database_path('seeders/productores_202510050513.sql'));
        DB::unprepared($sql);

//        tipo_manejos_202510060401
//        tipo_miel_202510060401
//        apiarios_202510060401
        $sql = File::get(database_path('seeders/tipo_manejos_202510060401.sql'));
        DB::unprepared($sql);
        $sql = File::get(database_path('seeders/tipo_miel_202510060401.sql'));
        DB::unprepared($sql);
        $sql = File::get(database_path('seeders/apiarios_202510060401.sql'));
        DB::unprepared($sql);
        $sql = File::get(database_path('seeders/acopio_cosechas_202510170333.sql'));;
        DB::unprepared($sql);


        $this->fixSequences();

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
