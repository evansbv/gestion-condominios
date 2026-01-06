<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Residente;
use App\Models\Vivienda;
use App\Models\Reunion;
use App\Models\Actividad;
use App\Models\Aporte;
use App\Models\Comunicacion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear usuarios con diferentes roles
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@tecnoweb.org.bo',
            'password' => Hash::make('admin123'),
            'rol' => 'ADMINISTRADOR',
            'activo' => true,
        ]);

        $miembroDirectorio = User::create([
            'name' => 'Juan Pérez',
            'email' => 'directorio@tecnoweb.org.bo',
            'password' => Hash::make('directorio123'),
            'rol' => 'MIEMBRO_DIRECTORIO',
            'activo' => true,
        ]);

        $propietario1 = User::create([
            'name' => 'María García',
            'email' => 'maria@example.com',
            'password' => Hash::make('propietario123'),
            'rol' => 'PROPIETARIO',
            'activo' => true,
        ]);

        $propietario2 = User::create([
            'name' => 'Carlos Rodríguez',
            'email' => 'carlos@example.com',
            'password' => Hash::make('propietario123'),
            'rol' => 'PROPIETARIO',
            'activo' => true,
        ]);

        // Crear viviendas
        $vivienda1 = Vivienda::create([
            'numero' => 'A-101',
            'direccion' => 'Calle Principal #123',
            'tipo' => 'DEPARTAMENTO',
            'area_m2' => 85.50,
            'latitud' => -16.5000,
            'longitud' => -68.1500,
            'numero_habitantes' => 4,
            'activo' => true,
        ]);

        $vivienda2 = Vivienda::create([
            'numero' => 'A-102',
            'direccion' => 'Calle Principal #125',
            'tipo' => 'DEPARTAMENTO',
            'area_m2' => 92.00,
            'latitud' => -16.5001,
            'longitud' => -68.1501,
            'numero_habitantes' => 3,
            'activo' => true,
        ]);

        $vivienda3 = Vivienda::create([
            'numero' => 'B-201',
            'direccion' => 'Calle Secundaria #456',
            'tipo' => 'CASA',
            'area_m2' => 120.00,
            'latitud' => -16.5002,
            'longitud' => -68.1502,
            'numero_habitantes' => 5,
            'activo' => true,
        ]);

        // Crear residentes
        Residente::create([
            'user_id' => $propietario1->id,
            'vivienda_id' => $vivienda1->id,
            'nombres' => 'María',
            'apellido_paterno' => 'García',
            'apellido_materno' => 'López',
            'ci' => '1234567',
            'fecha_nacimiento' => '1985-05-15',
            'celular' => '71234567',
            'tipo_residente' => 'PROPIETARIO',
            'activo' => true,
        ]);

        Residente::create([
            'user_id' => $propietario2->id,
            'vivienda_id' => $vivienda2->id,
            'nombres' => 'Carlos',
            'apellido_paterno' => 'Rodríguez',
            'apellido_materno' => 'Mamani',
            'ci' => '7654321',
            'fecha_nacimiento' => '1980-08-20',
            'celular' => '79876543',
            'tipo_residente' => 'PROPIETARIO',
            'activo' => true,
        ]);

        // Crear reunión
        $reunion = Reunion::create([
            'titulo' => 'Reunión Ordinaria - Enero 2026',
            'descripcion' => 'Reunión mensual para aprobar actividades de mantenimiento',
            'fecha_convocatoria' => now()->subDays(5),
            'fecha_reunion' => now()->addDays(3),
            'lugar' => 'Salón comunal del condominio',
            'orden_dia' => "1. Verificación de quórum\n2. Aprobación de actividades\n3. Lectura de cuentas\n4. Varios",
            'estado' => 'CONVOCADA',
            'convocada_por' => $miembroDirectorio->id,
        ]);

        // Crear actividad
        $actividad = Actividad::create([
            'reunion_id' => $reunion->id,
            'titulo' => 'Mantenimiento de Churrasquera',
            'descripcion' => 'Limpieza y reparación de la churrasquera común',
            'tipo' => 'MANTENIMIENTO_CHURRASQUERA',
            'fecha_inicio' => now()->addDays(7),
            'fecha_fin' => now()->addDays(10),
            'presupuesto_aprobado' => 1500.00,
            'presupuesto_ejecutado' => 0,
            'estado' => 'PLANIFICADA',
            'porcentaje_avance' => 0,
            'responsable_id' => $miembroDirectorio->id,
        ]);

        // Crear aportes para cada vivienda
        Aporte::create([
            'actividad_id' => $actividad->id,
            'vivienda_id' => $vivienda1->id,
            'monto' => 500.00,
            'fecha_vencimiento' => now()->addDays(15),
            'estado' => 'PENDIENTE',
        ]);

        Aporte::create([
            'actividad_id' => $actividad->id,
            'vivienda_id' => $vivienda2->id,
            'monto' => 500.00,
            'fecha_vencimiento' => now()->addDays(15),
            'estado' => 'PENDIENTE',
        ]);

        Aporte::create([
            'actividad_id' => $actividad->id,
            'vivienda_id' => $vivienda3->id,
            'monto' => 500.00,
            'fecha_vencimiento' => now()->addDays(15),
            'estado' => 'PENDIENTE',
        ]);

        // Crear comunicación
        Comunicacion::create([
            'tipo' => 'COMUNICADO',
            'asunto' => 'Bienvenida al Sistema',
            'contenido' => 'Estimados residentes, bienvenidos al nuevo sistema de gestión del condominio.',
            'remitente_id' => $admin->id,
            'fecha_envio' => now(),
            'prioridad' => 'MEDIA',
            'estado' => 'ENVIADO',
            'enviado_por_email' => false,
        ]);

        $this->command->info('Datos de prueba creados exitosamente!');
        $this->command->info('Usuario Admin: admin@tecnoweb.org.bo / admin123');
        $this->command->info('Usuario Directorio: directorio@tecnoweb.org.bo / directorio123');
        $this->command->info('Usuario Propietario: maria@example.com / propietario123');
    }
}
