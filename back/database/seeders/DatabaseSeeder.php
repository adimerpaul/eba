<?php

namespace Database\Seeders;

use App\Models\Apicultor;
use App\Models\Cliente;
use App\Models\Planta;
use App\Models\Producto;
use App\Models\Productor;
use App\Models\Tanque;
use App\Models\TipoProducto;
use App\Models\Transporte;
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
                     'acopio_cosechas',
                 ] as $table) {
            DB::statement("
            SELECT setval(
                pg_get_serial_sequence('{$table}', 'id'),
                COALESCE((SELECT MAX(id) FROM {$table}), 1),
                (SELECT MAX(id) IS NOT NULL FROM {$table})
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

//        INSERT INTO public.tipo_productos (id, codigo_tipo, nombre_tipo, detalles) VALUES(1, '0001', 'Materia Prima', 'Meil, Polen, Propoleo');
//INSERT INTO public.tipo_productos (id, codigo_tipo, nombre_tipo, detalles) VALUES(2, '0002', 'en Proceso', 'En planta rpocesamiento');
//INSERT INTO public.tipo_productos (id, codigo_tipo, nombre_tipo, detalles) VALUES(3, '0003', 'Producto Terminado', 'Almacen Terminados');
        TipoProducto::create([
            'codigo_tipo' => '0001',
            'nombre_tipo' => 'Materia Prima',
            'detalles' => 'Miel, Polen, Propoleo',
        ]);
        TipoProducto::create([
            'codigo_tipo' => '0002',
            'nombre_tipo' => 'En Proceso',
            'detalles' => 'En planta procesamiento',
        ]);
        TipoProducto::create([
            'codigo_tipo' => '0003',
            'nombre_tipo' => 'Producto Terminado',
            'detalles' => 'Almacen Terminados',
        ]);

//        INSERT INTO public.productos (id, tipo_id, codigo_producto, nombre_producto, presentacion, cantidad_kg, costo, precio, fecha_vencimiento, nro_lote, codigo_barra, imagen)
//VALUES(1, 1, '0001', 'Miel de Abeja', 'Kilos', 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL);
//INSERT INTO public.productos (id, tipo_id, codigo_producto, nombre_producto, presentacion, cantidad_kg, costo, precio, fecha_vencimiento, nro_lote, codigo_barra, imagen)
//VALUES(2, 1, '0002', 'Cera', 'Kilos', 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL);
//INSERT INTO public.productos (id, tipo_id, codigo_producto, nombre_producto, presentacion, cantidad_kg, costo, precio, fecha_vencimiento, nro_lote, codigo_barra, imagen)
//VALUES(3, 1, '0003', 'Polen', 'Kilos', 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL);
//INSERT INTO public.productos (id, tipo_id, codigo_producto, nombre_producto, presentacion, cantidad_kg, costo, precio, fecha_vencimiento, nro_lote, codigo_barra, imagen)
//VALUES(4, 1, '0004', 'Propoleo', 'Kilos', 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL);
//INSERT INTO public.productos (id, tipo_id, codigo_producto, nombre_producto, presentacion, cantidad_kg, costo, precio, fecha_vencimiento, nro_lote, codigo_barra, imagen)
//VALUES(5, 2, '001', 'Miel Abeja', 'Kilos', 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL);
//INSERT INTO public.productos (id, tipo_id, codigo_producto, nombre_producto, presentacion, cantidad_kg, costo, precio, fecha_vencimiento, nro_lote, codigo_barra, imagen)
//VALUES(6, 2, '002', 'Cera', 'Kilos', 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL);
//INSERT INTO public.productos (id, tipo_id, codigo_producto, nombre_producto, presentacion, cantidad_kg, costo, precio, fecha_vencimiento, nro_lote, codigo_barra, imagen)
//VALUES(7, 2, '003', 'Polen', 'Kilos', 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL);
//INSERT INTO public.productos (id, tipo_id, codigo_producto, nombre_producto, presentacion, cantidad_kg, costo, precio, fecha_vencimiento, nro_lote, codigo_barra, imagen)
//VALUES(8, 3, '01', 'Miel Natural 400 GR', 'Pieza', 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL);
//INSERT INTO public.productos (id, tipo_id, codigo_producto, nombre_producto, presentacion, cantidad_kg, costo, precio, fecha_vencimiento, nro_lote, codigo_barra, imagen)
//VALUES(9, 3, '02', 'Miel con Propoleo 300 GR', 'Pieza', 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL);
//INSERT INTO public.productos (id, tipo_id, codigo_producto, nombre_producto, presentacion, cantidad_kg, costo, precio, fecha_vencimiento, nro_lote, codigo_barra, imagen)
//VALUES(10, 3, '03', 'Miel Flor Jamaica 400 GR', 'Pieza', 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL);
//INSERT INTO public.productos (id, tipo_id, codigo_producto, nombre_producto, presentacion, cantidad_kg, costo, precio, fecha_vencimiento, nro_lote, codigo_barra, imagen)
//VALUES(11, 3, '04', 'Miel Jengibre 200 GR', 'Pieza', 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL);
//INSERT INTO public.productos (id, tipo_id, codigo_producto, nombre_producto, presentacion, cantidad_kg, costo, precio, fecha_vencimiento, nro_lote, codigo_barra, imagen)
//VALUES(12, 3, '05', 'Miel Polen 200 GR', 'Pieza', 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL);
//INSERT INTO public.productos (id, tipo_id, codigo_producto, nombre_producto, presentacion, cantidad_kg, costo, precio, fecha_vencimiento, nro_lote, codigo_barra, imagen)
//VALUES(13, 3, '06', 'Miel con Quinua 100 GR', 'Pieza', 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL);
//INSERT INTO public.productos (id, tipo_id, codigo_producto, nombre_producto, presentacion, cantidad_kg, costo, precio, fecha_vencimiento, nro_lote, codigo_barra, imagen)
//VALUES(14, 3, '07', 'Super Energetico 200 GR', 'Pieza', 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL);
//INSERT INTO public.productos (id, tipo_id, codigo_producto, nombre_producto, presentacion, cantidad_kg, costo, precio, fecha_vencimiento, nro_lote, codigo_barra, imagen)
//VALUES(15, 3, '08', 'Miel con Maca 200 GR', 'Pieza', 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL);
//INSERT INTO public.productos (id, tipo_id, codigo_producto, nombre_producto, presentacion, cantidad_kg, costo, precio, fecha_vencimiento, nro_lote, codigo_barra, imagen)
//VALUES(16, 3, '09', 'Miel con Chocolate 50 GR', 'Pieza', 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL);
//INSERT INTO public.productos (id, tipo_id, codigo_producto, nombre_producto, presentacion, cantidad_kg, costo, precio, fecha_vencimiento, nro_lote, codigo_barra, imagen)
//VALUES(17, 3, '10', 'Miel Cremosa 100 GR', 'Pieza', 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL);

        Producto::create(['tipo_id' => 1, 'codigo_producto' => '0001', 'nombre_producto' => 'Miel de Abeja', 'presentacion' => 'Kilos', 'cantidad' => 0.00, 'costo' => 0.00, 'precio' => 0.00, 'fecha_vencimiento' => null, 'nro_lote' => null, 'codigo_barra' => null, 'imagen' => null]);
        Producto::create(['tipo_id' => 1, 'codigo_producto' => '0002', 'nombre_producto' => 'Cera', 'presentacion' => 'Kilos', 'cantidad' => 0.00, 'costo' => 0.00, 'precio' => 0.00, 'fecha_vencimiento' => null, 'nro_lote' => null, 'codigo_barra' => null, 'imagen' => null]);
        Producto::create(['tipo_id' => 1, 'codigo_producto' => '0003', 'nombre_producto' => 'Polen', 'presentacion' => 'Kilos', 'cantidad' => 0.00, 'costo' => 0.00, 'precio' => 0.00, 'fecha_vencimiento' => null, 'nro_lote' => null, 'codigo_barra' => null, 'imagen' => null]);
        Producto::create(['tipo_id' => 1, 'codigo_producto' => '0004', 'nombre_producto' => 'Propoleo', 'presentacion' => 'Kilos', 'cantidad' => 0.00, 'costo' => 0.00, 'precio' => 0.00, 'fecha_vencimiento' => null, 'nro_lote' => null, 'codigo_barra' => null, 'imagen' => null]);
        Producto::create(['tipo_id' => 2, 'codigo_producto' => '001', 'nombre_producto' => 'Miel Abeja', 'presentacion' => 'Kilos', 'cantidad' => 0.00, 'costo' => 0.00, 'precio' => 0.00, 'fecha_vencimiento' => null, 'nro_lote' => null, 'codigo_barra' => null, 'imagen' => null]);
        Producto::create(['tipo_id' => 2, 'codigo_producto' => '002', 'nombre_producto' => 'Cera', 'presentacion' => 'Kilos', 'cantidad' => 0.00, 'costo' => 0.00, 'precio' => 0.00, 'fecha_vencimiento' => null, 'nro_lote' => null, 'codigo_barra' => null, 'imagen' => null]);
        Producto::create(['tipo_id' => 2, 'codigo_producto' => '003', 'nombre_producto' => 'Polen', 'presentacion' => 'Kilos', 'cantidad' => 0.00, 'costo' => 0.00, 'precio' => 0.00, 'fecha_vencimiento' => null, 'nro_lote' => null, 'codigo_barra' => null, 'imagen' => null]);
        Producto::create(['tipo_id' => 3, 'codigo_producto' => '01', 'nombre_producto' => 'Miel Natural 400 GR', 'presentacion' => 'Pieza', 'cantidad' => 0.00, 'costo' => 0.00, 'precio' => 0.00, 'fecha_vencimiento' => null, 'nro_lote' => null, 'codigo_barra' => null, 'imagen' => null]);
        Producto::create(['tipo_id' => 3, 'codigo_producto' => '02', 'nombre_producto' => 'Miel con Propoleo 300 GR', 'presentacion' => 'Pieza', 'cantidad' => 0.00, 'costo' => 0.00, 'precio' => 0.00, 'fecha_vencimiento' => null, 'nro_lote' => null, 'codigo_barra' => null, 'imagen' => null]);
        Producto::create(['tipo_id' => 3, 'codigo_producto' => '03', 'nombre_producto' => 'Miel Flor Jamaica 400 GR', 'presentacion' => 'Pieza', 'cantidad' => 0.00, 'costo' => 0.00, 'precio' => 0.00, 'fecha_vencimiento' => null, 'nro_lote' => null, 'codigo_barra' => null, 'imagen' => null]);
        Producto::create(['tipo_id' => 3, 'codigo_producto' => '04', 'nombre_producto' => 'Miel Jengibre 200 GR', 'presentacion' => 'Pieza', 'cantidad' => 0.00, 'costo' => 0.00, 'precio' => 0.00, 'fecha_vencimiento' => null, 'nro_lote' => null, 'codigo_barra' => null, 'imagen' => null]);
        Producto::create(['tipo_id' => 3, 'codigo_producto' => '05', 'nombre_producto' => 'Miel Polen 200 GR', 'presentacion' => 'Pieza', 'cantidad' => 0.00, 'costo' => 0.00, 'precio' => 0.00, 'fecha_vencimiento' => null, 'nro_lote' => null, 'codigo_barra' => null, 'imagen' => null]);
        Producto::create(['tipo_id' => 3, 'codigo_producto' => '06', 'nombre_producto' => 'Miel con Quinua 100 GR', 'presentacion' => 'Pieza', 'cantidad' => 0.00, 'costo' => 0.00, 'precio' => 0.00, 'fecha_vencimiento' => null, 'nro_lote' => null, 'codigo_barra' => null, 'imagen' => null]);
        Producto::create(['tipo_id' => 3, 'codigo_producto' => '07', 'nombre_producto' => 'Super Energetico 200 GR', 'presentacion' => 'Pieza', 'cantidad' => 0.00, 'costo' => 0.00, 'precio' => 0.00, 'fecha_vencimiento' => null, 'nro_lote' => null, 'codigo_barra' => null, 'imagen' => null]);
        Producto::create(['tipo_id' => 3, 'codigo_producto' => '08', 'nombre_producto' => 'Miel con Maca 200 GR', 'presentacion' => 'Pieza', 'cantidad' => 0.00, 'costo' => 0.00, 'precio' => 0.00, 'fecha_vencimiento' => null, 'nro_lote' => null, 'codigo_barra' => null, 'imagen' => null]);
        Producto::create(['tipo_id' => 3, 'codigo_producto' => '09', 'nombre_producto' => 'Miel con Chocolate 50 GR', 'presentacion' => 'Pieza', 'cantidad' => 0.00, 'costo' => 0.00, 'precio' => 0.00, 'fecha_vencimiento' => null, 'nro_lote' => null, 'codigo_barra' => null, 'imagen' => null]);
        Producto::create(['tipo_id' => 3, 'codigo_producto' => '10', 'nombre_producto' => 'Miel Cremosa 100 GR', 'presentacion' => 'Pieza', 'cantidad' => 0.00, 'costo' => 0.00, 'precio' => 0.00, 'fecha_vencimiento' => null, 'nro_lote' => null, 'codigo_barra' => null, 'imagen' => null]);


        $sql = File::get(database_path('seeders/acopio_cosechas_202510170333.sql'));;
        DB::unprepared($sql);


//        INSERT INTO public.plantas (id, codigo_planta, nombre, registro_sanitario, direccion, municipio_id) VALUES(1, '07', 'Planta Samuzabety', '123456', 'Eterazama, Villa Tunari Cochabamba', 31003);
//INSERT INTO public.plantas (id, codigo_planta, nombre, registro_sanitario, direccion, municipio_id) VALUES(2, '10', 'Planta Villa Montes', '123456', 'Villamontes, Tarija', 60303);
//INSERT INTO public.plantas (id, codigo_planta, nombre, registro_sanitario, direccion, municipio_id) VALUES(4, '04', 'Planta Monteagudo', '123456', 'Chaco Chuquisaqueño', 10501);
//INSERT INTO public.plantas (id, codigo_planta, nombre, registro_sanitario, direccion, municipio_id) VALUES(3, '05', 'Planta Irupana', '123456', 'Yungas Irupana', 21102);

        Planta::create(['codigo_planta' => '07', 'nombre_planta' => 'Planta Samuzabety', 'registro_sanitario' => '123456', 'direccion' => 'Eterazama, Villa Tunari Cochabamba', 'municipio_id' => 31003]);
        Planta::create(['codigo_planta' => '10', 'nombre_planta' => 'Planta Villa Montes', 'registro_sanitario' => '123456', 'direccion' => 'Villamontes, Tarija', 'municipio_id' => 60303]);
        Planta::create(['codigo_planta' => '04', 'nombre_planta' => 'Planta Monteagudo', 'registro_sanitario' => '123456', 'direccion' => 'Chaco Chuquisaqueño', 'municipio_id' => 10501]);
        Planta::create(['codigo_planta' => '05', 'nombre_planta' => 'Planta Irupana', 'registro_sanitario' => '123456', 'direccion' => 'Yungas Irupana', 'municipio_id' => 21102]);

//        INSERT INTO public.tanques (id, codigo_tanque, nombre_tanque, planta_id) VALUES(0, '00', 'Tanque 0', 1);
//INSERT INTO public.tanques (id, codigo_tanque, nombre_tanque, planta_id) VALUES(1, '01', 'Tanque 1', 1);
//INSERT INTO public.tanques (id, codigo_tanque, nombre_tanque, planta_id) VALUES(2, '02', 'Tanque 2', 1);
//INSERT INTO public.tanques (id, codigo_tanque, nombre_tanque, planta_id) VALUES(3, '03', 'Tanque 3', 1);
//INSERT INTO public.tanques (id, codigo_tanque, nombre_tanque, planta_id) VALUES(4, '04', 'Tanque 4', 1);

        Tanque::create(['codigo_tanque' => '00', 'nombre_tanque' => 'Tanque 0', 'planta_id' => 1]);
        Tanque::create(['codigo_tanque' => '01', 'nombre_tanque' => 'Tanque 1', 'planta_id' => 1]);
        Tanque::create(['codigo_tanque' => '02', 'nombre_tanque' => 'Tanque 2', 'planta_id' => 1]);
        Tanque::create(['codigo_tanque' => '03', 'nombre_tanque' => 'Tanque 3', 'planta_id' => 1]);
        Tanque::create(['codigo_tanque' => '04', 'nombre_tanque' => 'Tanque 4', 'planta_id' => 1]);







        $this->fixSequences();

//        INSERT INTO public.transportes (id, empresa, placa, responsable) VALUES(0, 'Sin Empresa', '000', 'Sin nombre');
//INSERT INTO public.clientes(id, nit, nombre_cliente, direccion, telefono, email, pais_destino) VALUES(0, '0', 'Sin Nombre', 'sin direccion', '0', '''''', '''''');
        Transporte::create(['empresa' => 'Sin Empresa', 'placa' => '000', 'responsable' => 'Sin nombre']);
        Cliente::create(['nit' => '0', 'nombre_cliente' => 'Sin Nombre', 'direccion' => 'sin direccion', 'telefono' => '0', 'email' => '', 'pais_destino' => '']);


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
