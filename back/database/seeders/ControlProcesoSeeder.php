<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ControlProcesoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar que existan registros necesarios
        $acopio = DB::table('traza.acopio_cosechas')
            ->where('estado', 'BUENO')
            ->first();
            
        if (!$acopio) {
            $this->command->warn('No hay acopios con estado BUENO. Seeder omitido.');
            return;
        }

        $tanque = DB::table('traza.tanques')->first();
        $producto = DB::table('traza.productos')
            ->where('tipo_id', 3)
            ->first();

        if (!$tanque || !$producto) {
            $this->command->warn('Faltan datos de tanques o productos. Seeder omitido.');
            return;
        }

        // Crear proceso de ejemplo EN_PROCESO
        $procesoId1 = DB::table('traza.control_procesos')->insertGetId([
            'tanque_id' => $tanque->id,
            'producto_id' => $producto->id,
            'fecha_inicio' => Carbon::now()->subDays(2),
            'cantidad_entrada_kg' => 50.00,
            'estado' => 'EN_PROCESO',
            'temperatura_proceso' => 35.5,
            'tiempo_proceso_horas' => 12.0,
            'metodo_proceso' => 'Decantación natural',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Relacionar con acopio
        DB::table('traza.control_proceso_acopios')->insert([
            'control_proceso_id' => $procesoId1,
            'acopio_cosecha_id' => $acopio->id,
            'cantidad_kg' => 50.00,
            'created_at' => Carbon::now(),
        ]);

        // Crear proceso FINALIZADO
        $procesoId2 = DB::table('traza.control_procesos')->insertGetId([
            'tanque_id' => $tanque->id,
            'producto_id' => $producto->id,
            'fecha_inicio' => Carbon::now()->subDays(5),
            'fecha_fin' => Carbon::now()->subDays(3),
            'cantidad_entrada_kg' => 100.00,
            'cantidad_salida_kg' => 95.00,
            'merma_kg' => 5.00,
            'merma_porcentaje' => 5.00,
            'estado' => 'FINALIZADO',
            'temperatura_proceso' => 37.0,
            'tiempo_proceso_horas' => 24.0,
            'metodo_proceso' => 'Filtrado y decantación',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Relacionar con acopio
        DB::table('traza.control_proceso_acopios')->insert([
            'control_proceso_id' => $procesoId2,
            'acopio_cosecha_id' => $acopio->id,
            'cantidad_kg' => 100.00,
            'created_at' => Carbon::now(),
        ]);

        // Crear lote relacionado con el proceso finalizado
        $codigoLote = 'LOTE-' . $tanque->id . '-' . $producto->id . '-' . time();
        
        DB::table('traza.lotes')->insert([
            'cosecha_id' => $acopio->id,
            'tanque_id' => $tanque->id,
            'producto_id' => $producto->id,
            'control_proceso_id' => $procesoId2,
            'codigo_lote' => $codigoLote,
            'cantidad_kg' => 90.00,
            'fecha_envasado' => Carbon::now()->subDays(2),
            'fecha_caducidad' => Carbon::now()->addYears(2),
            'tipo_envase' => 'Frasco de vidrio 500g',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $this->command->info('✅ Seeders de Control de Proceso creados exitosamente');
        $this->command->info("   - Proceso EN_PROCESO: ID {$procesoId1}");
        $this->command->info("   - Proceso FINALIZADO: ID {$procesoId2}");
        $this->command->info("   - Lote creado: {$codigoLote}");
    }
}
