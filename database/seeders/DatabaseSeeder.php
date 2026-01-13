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
            'name' => 'Amilcar Bruno Saavedra',
            'email' => 'amilcar@gmail.com',
            'password' => Hash::make('amilcar123*'),
            'rol' => 'PROPIETARIO',
            'activo' => true,
        ]);

        $propietario2 = User::create([
            'name' => 'Benjamin Galindo Galindo',
            'email' => 'benjamin@gmail.com',
            'password' => Hash::make('benjamin123*'),
            'rol' => 'PROPIETARIO',
            'activo' => true,
        ]);

        $propietario3 = User::create([
            'name' => 'Luis Enrique Justiniano Agreda',
            'email' => 'luis@gmail.com',
            'password' => Hash::make('luis123*'),
            'rol' => 'PROPIETARIO',
            'activo' => true,
        ]);

        $propietario4 = User::create([
            'name' => 'Sergio Eduardo Ramos M.',
            'email' => 'sergio@gmail.com',
            'password' => Hash::make('sergio123*'),
            'rol' => 'PROPIETARIO',
            'activo' => true,
        ]);

        $propietario5 = User::create([
            'name' => 'Evans Balcazar Veizaga',
            'email' => 'evansbv@gmail.com',
            'password' => Hash::make('evans123*'),
            'rol' => 'PROPIETARIO',
            'activo' => true,
        ]);

        $propietario6 = User::create([
            'name' => 'Adalberto Ruiz Alvarez',
            'email' => 'adalberto@gmail.com',
            'password' => Hash::make('adalberto123*'),
            'rol' => 'PROPIETARIO',
            'activo' => true,
        ]);

        // Crear viviendas
        $vivienda1 = Vivienda::create([
            'numero' => 'Casa-101',
            'direccion' => 'Calle #1 No. 101',
            'tipo' => 'CASA',
            'area_m2' => 380.00,
            'latitud' => -17.710633,   
            'longitud' => -63.139183,
            'numero_habitantes' => 4,
            'activo' => true,
        ]);

        $vivienda2 = Vivienda::create([
            'numero' => 'Casa-105',
            'direccion' => 'Calle #1 No. 105',
            'tipo' => 'CASA',
            'area_m2' => 220.00,
            'latitud' => -17.710633,   
            'longitud' => -63.139183,
            'numero_habitantes' => 7,
            'activo' => true,
        ]);

        $vivienda3 = Vivienda::create([
            'numero' => 'Casa-108',
            'direccion' => 'Calle #1 No. 108',
            'tipo' => 'CASA',
            'area_m2' => 120.00,
            'latitud' => -17.710633,   
            'longitud' => -63.139183,
            'numero_habitantes' => 5,
            'activo' => true,
        ]);

        $vivienda4 = Vivienda::create([
            'numero' => 'Casa-201',
            'direccion' => 'Calle #2 No. 201',
            'tipo' => 'CASA',
            'area_m2' => 380.00,
            'latitud' => -17.710887, 
            'longitud' => -63.139005,
            'numero_habitantes' => 5,
            'activo' => true,
        ]);

        $vivienda5 = Vivienda::create([
            'numero' => 'Casa-210',
            'direccion' => 'Calle #2 No. 210',
            'tipo' => 'CASA',
            'area_m2' => 380.00,
            'latitud' => -17.710887, 
            'longitud' => -63.139005,
            'numero_habitantes' => 5,
            'activo' => true,
        ]);

        $vivienda6 = Vivienda::create([
            'numero' => 'Casa-115',
            'direccion' => 'Calle #2 No. 115',
            'tipo' => 'CASA',
            'area_m2' => 220.00,
            'latitud' => -17.710633,   
            'longitud' => -63.139183,
            'numero_habitantes' => 7,
            'activo' => true,
        ]);

        // Crear residentes
        $residente1=Residente::create([
            'user_id' => $propietario1->id,
            'vivienda_id' => $vivienda1->id,
            'nombres' => 'Amilcar',
            'apellido_paterno' => 'Bruno',
            'apellido_materno' => 'Saavedra',
            'ci' => '12345601',
            'fecha_nacimiento' => '1985-05-15',
            'celular' => '70012301',
            'tipo_residente' => 'PROPIETARIO',
            'activo' => true,
        ]);

        $residente2=Residente::create([
            'user_id' => $propietario2->id,
            'vivienda_id' => $vivienda2->id,
            'nombres' => 'Luis Enrique',
            'apellido_paterno' => 'Justiniano',
            'apellido_materno' => 'Agreda',
            'ci' => '12345602',
            'fecha_nacimiento' => '1985-05-15',
            'celular' => '70012302',
            'tipo_residente' => 'PROPIETARIO',
            'activo' => true,
        ]);

        $residente3=Residente::create([
            'user_id' => $propietario3->id,
            'vivienda_id' => $vivienda3->id,
            'nombres' => 'Sergio Eduardo',
            'apellido_paterno' => 'Ramos',
            'apellido_materno' => 'M',
            'ci' => '12345603',
            'fecha_nacimiento' => '1985-05-15',
            'celular' => '70012303',
            'tipo_residente' => 'PROPIETARIO',
            'activo' => true,
        ]);

        $residente4=Residente::create([
            'user_id' => $propietario4->id,
            'vivienda_id' => $vivienda4->id,
            'nombres' => 'Benjamin',
            'apellido_paterno' => 'Galindo',
            'apellido_materno' => 'Galindo',
            'ci' => '12345604',
            'fecha_nacimiento' => '1980-08-20',
            'celular' => '70012304',
            'tipo_residente' => 'PROPIETARIO',
            'activo' => true,
        ]);

        $residente5=Residente::create([
            'user_id' => $propietario5->id,
            'vivienda_id' => $vivienda5->id,
            'nombres' => 'Evans',
            'apellido_paterno' => 'Balcazar',
            'apellido_materno' => 'Veizaga',
            'ci' => '3924689',
            'fecha_nacimiento' => '1975-08-29',
            'celular' => '72107856',
            'tipo_residente' => 'PROPIETARIO',
            'activo' => true,
        ]);
        $residente6=Residente::create([
            'user_id' => $propietario6->id,
            'vivienda_id' => $vivienda6->id,
            'nombres' => 'Adalberto',
            'apellido_paterno' => 'Ruiz',
            'apellido_materno' => 'Alvarez',
            'ci' => '12345606',
            'fecha_nacimiento' => '1975-08-29',
            'celular' => '70012306',
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
            'monto' => 250.00,
            'fecha_vencimiento' => now()->addDays(15),
            'estado' => 'PENDIENTE',
        ]);

        Aporte::create([
            'actividad_id' => $actividad->id,
            'vivienda_id' => $vivienda2->id,
            'monto' => 250.00,
            'fecha_vencimiento' => now()->addDays(15),
            'estado' => 'PENDIENTE',
        ]);

        Aporte::create([
            'actividad_id' => $actividad->id,
            'vivienda_id' => $vivienda3->id,
            'monto' => 250.00,
            'fecha_vencimiento' => now()->addDays(15),
            'estado' => 'PENDIENTE',
        ]);

        Aporte::create([
            'actividad_id' => $actividad->id,
            'vivienda_id' => $vivienda4->id,
            'monto' => 250.00,
            'fecha_vencimiento' => now()->addDays(15),
            'estado' => 'PENDIENTE',
        ]);

        Aporte::create([
            'actividad_id' => $actividad->id,
            'vivienda_id' => $vivienda5->id,
            'monto' => 250.00,
            'fecha_vencimiento' => now()->addDays(15),
            'estado' => 'PENDIENTE',
        ]);
        Aporte::create([
            'actividad_id' => $actividad->id,
            'vivienda_id' => $vivienda6->id,
            'monto' => 250.00,
            'fecha_vencimiento' => now()->addDays(15),
            'estado' => 'PENDIENTE',
        ]);

        // Crear comunicación al Administrador y a los Residenetes
        Comunicacion::create([
            'tipo' => 'COMUNICADO',
            'asunto' => 'Bienvenida al Sistema - Demo',
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
        $this->command->info('Usuario Propietario: amilcar@gmail.com / amilcar123*');
    }
}
