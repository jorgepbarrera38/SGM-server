<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {/*
        //Devices
        Permission::create([
            'name' => 'devices.index',
            'description' => 'Listar dispositivos'
        ]);

        Permission::create([
            'name' => 'devices.create',
            'description' => 'Registrar dispositivos'
        ]);

        Permission::create([
            'name' => 'devices.show',
            'description' => 'Ver información de dispositivos'
        ]);

        Permission::create([
            'name' => 'devices.edit',
            'description' => 'Editar dispositivos'
        ]);

        
        Permission::create([
            'name' => 'devices.down',
            'description' => 'Dar baja a dispositivos'
        ]);
        
        Permission::create([
            'name' => 'devices.update-internal-code',
            'description' => 'Actualizar placa institucional de dispositivos'
        ]);
        
        Permission::create([
            'name' => 'devices.movements.create',
            'description' => 'Registar movimientos de dispositivos'
        ]);

        //Ver dispositivos dados de baja
        Permission::create([
            'name' => 'devices.down.index',
            'description' => 'Ver dispositivos dados de baja'
        ]);

        //Grupos de dispositivos
        Permission::create([
            'name' => 'groups.index',
            'description' => 'Listar grupos de dispositivos'
        ]);

        Permission::create([
            'name' => 'groups.create',
            'description' => 'Registrar grupos de dispositivos'
        ]);

        Permission::create([
            'name' => 'groups.edit',
            'description' => 'Editar grupos de dispositivos'
        ]);

        Permission::create([
            'name' => 'groups.destroy',
            'description' => 'Eliminar grupos de dispositivos'
        ]);

        Permission::create([
            'name' => 'groups.authorization',
            'description' => 'Autorizar usuarios en grupos de dispositivos'
        ]);

        //Elementos
        Permission::create([
            'name' => 'elements.index',
            'description' => 'Listar elementos'
        ]);

        Permission::create([
            'name' => 'elements.create',
            'description' => 'Registrar elementos'
        ]);

        Permission::create([
            'name' => 'elements.edit',
            'description' => 'Editar elementos'
        ]);

        Permission::create([
            'name' => 'elements.destroy',
            'description' => 'Eliminar elementos'
        ]);

        //Ubicaciónes
        Permission::create([
            'name' => 'locations.index',
            'description' => 'Listar ubicaciones'
        ]);

        Permission::create([
            'name' => 'locations.create',
            'description' => 'Registrar ubicaciones'
        ]);

        Permission::create([
            'name' => 'locations.edit',
            'description' => 'Editar ubicaciones'
        ]);

        Permission::create([
            'name' => 'locations.destroy',
            'description' => 'Eliminar ubicaciones'
        ]);

        //Dependencias
        Permission::create([
            'name' => 'dependences.index',
            'description' => 'Listar dependencias'
        ]);

        Permission::create([
            'name' => 'dependences.create',
            'description' => 'Registrar dependencias'
        ]);

        Permission::create([
            'name' => 'dependences.edit',
            'description' => 'Editar dependencias'
        ]);

        Permission::create([
            'name' => 'dependences.destroy',
            'description' => 'Eliminar dependencias'
        ]);

        //Responsables
        Permission::create([
            'name' => 'responsibles.index',
            'description' => 'Listar responsables'
        ]);

        Permission::create([
            'name' => 'responsibles.create',
            'description' => 'Registrar responsables'
        ]);

        Permission::create([
            'name' => 'responsibles.edit',
            'description' => 'Editar responsables'
        ]);

        Permission::create([
            'name' => 'responsibles.destroy',
            'description' => 'Eliminar responsables'
        ]);

        //Marcas
        Permission::create([
            'name' => 'brands.index',
            'description' => 'Listar marcas'
        ]);

        Permission::create([
            'name' => 'brands.create',
            'description' => 'Registrar marcas'
        ]);

        Permission::create([
            'name' => 'brands.edit',
            'description' => 'Editar marcas'
        ]);

        Permission::create([
            'name' => 'brands.destroy',
            'description' => 'Eliminar marcas'
        ]);

        //Registrar mantenimientos generales
        Permission::create([
            'name' => 'maintenances.create',
            'description' => 'Registrar mantenimientos generales'
        ]);

        //Tipos de documentos
        Permission::create([
            'name' => 'type-documents.index',
            'description' => 'Listar tipos de documentos'
        ]);

        Permission::create([
            'name' => 'type-documents.create',
            'description' => 'Registrar tipos de documentos'
        ]);

        Permission::create([
            'name' => 'type-documents.edit',
            'description' => 'Editar tipos de documentos'
        ]);

        Permission::create([
            'name' => 'type-documents.destroy',
            'description' => 'Eliminar tipos de documentos'
        ]);

        //Tipos de mantenimientos - tipos de otros mantenimientos
        Permission::create([
            'name' => 'type-other-maintenances.index',
            'description' => 'Listar clasificaciones de servicios'
        ]);

        Permission::create([
            'name' => 'type-other-maintenances.create',
            'description' => 'Registrar clasificaciones de servicios'
        ]);

        Permission::create([
            'name' => 'type-other-maintenances.edit',
            'description' => 'Editar clasificaciones de servicios'
        ]);

        Permission::create([
            'name' => 'type-other-maintenances.destroy',
            'description' => 'Eliminar clasificaciones de servicios'
        ]);

        //Repuestos y consumibles
        Permission::create([
            'name' => 'replacements.index',
            'description' => 'Listar repuestos y consumibles'
        ]);

        Permission::create([
            'name' => 'replacements.create',
            'description' => 'Registrar repuestos y consumibles'
        ]);

        Permission::create([
            'name' => 'replacements.edit',
            'description' => 'Editar repuestos y consumibles'
        ]);

        Permission::create([
            'name' => 'replacements.destroy',
            'description' => 'Eliminar repuestos y consumibles'
        ]);

        //Repuestos y consumibles
        Permission::create([
            'name' => 'periods.index',
            'description' => 'Listar periodos de mantenimiento'
        ]);

        Permission::create([
            'name' => 'periods.create',
            'description' => 'Registrar periodos de mantenimiento'
        ]);

        Permission::create([
            'name' => 'periods.edit',
            'description' => 'Editar periodos de mantenimiento'
        ]);

        Permission::create([
            'name' => 'periods.destroy',
            'description' => 'Eliminar periodos de mantenimiento'
        ]);

        Permission::create([
            'name' => 'periods.update-state',
            'description' => 'Actualizar estado de periodos de mantenimiento'
        ]);

        //Cronogramas de mantenimiento
        Permission::create([
            'name' => 'schedule-maintenance.index',
            'description' => 'Listar cronogramas de mantenimiento'
        ]);

        Permission::create([
            'name' => 'schedule-maintenance.create',
            'description' => 'Registrar cronogramas de mantenimiento'
        ]);

        Permission::create([
            'name' => 'schedule-maintenance.show',
            'description' => 'Ver info de cronogramas de mantenimiento'
        ]);

        Permission::create([
            'name' => 'schedule-maintenance.destroy',
            'description' => 'Eliminar cronogramas de mantenimiento'
        ]);

        Permission::create([
            'name' => 'schedule-maintenance.finalize',
            'description' => 'Finalizar cronogramas de mantenimiento'
        ]);

        Permission::create([
            'name' => 'schedule-maintenance.reschedule-devices',
            'description' => 'Reprogramar dispositivos de cronogramas de mantenimiento'
        ]);

        Permission::create([
            'name' => 'schedule-maintenance.delete-devices',
            'description' => 'Eliminar dispositivos de cronogramas de mantenimiento'
        ]);

        //Repuestos y consumibles
        Permission::create([
            'name' => 'company.index',
            'description' => 'Listar empresa y parámetros'
        ]);

        Permission::create([
            'name' => 'company.update',
            'description' => 'Actualizar empresa y parámetros'
        ]);
        //Usuarios
        Permission::create([
            'name' => 'users.index',
            'description' => 'Listar usuarios'
        ]);

        Permission::create([
            'name' => 'users.create',
            'description' => 'Registrar usuarios'
        ]);

        Permission::create([
            'name' => 'users.edit',
            'description' => 'Editar usuarios'
        ]);

        Permission::create([
            'name' => 'users.permission',
            'description' => 'Actualizar permisos de usuario'
        ]);

        //Mantenimientos preventivos
        Permission::create([
            'name' => 'preventive-maintenances.create',
            'description' => 'Registrar mantenimientos preventivos'
        ]);

        Permission::create([
            'name' => 'preventive-maintenances.edit',
            'description' => 'Editar mantenimientos preventivos'
        ]);

        Permission::create([
            'name' => 'preventive-maintenances.destroy',
            'description' => 'Eliminar mantenimientos preventivos'
        ]);

        //Mantenimientos preventivos
        Permission::create([
            'name' => 'other-maintenances.create',
            'description' => 'Registrar otros mantenimientos'
        ]);

        Permission::create([
            'name' => 'other-maintenances.edit',
            'description' => 'Editar otros mantenimientos'
        ]);

        Permission::create([
            'name' => 'other-maintenances.destroy',
            'description' => 'Eliminar otros mantenimientos'
        ]);

        Permission::create([
            'name' => 'document.create',
            'description' => 'Anexar documentos de dispositivos'
        ]);

        Permission::create([
            'name' => 'document.destroy',
            'description' => 'Eliminar documentos de dispositivos'
        ]);

        //Reportes - Mantenimientos
        Permission::create([
            'name' => 'reports.maintenances',
            'description' => 'Consultar reporte de mantenimientos'
        ]);

        Permission::create([
            'name' => 'reports.replacements',
            'description' => 'Consultar repuestos usados'
        ]);*/
        //Modos de falla
        Permission::create([
            'name' => 'failure-modes.index',
            'description' => 'Listar modos de falla'
        ]);

        Permission::create([
            'name' => 'failure-modes.create',
            'description' => 'Registrar modos de falla'
        ]);

        Permission::create([
            'name' => 'failure-modes.edit',
            'description' => 'Editar modos de falla'
        ]);

        Permission::create([
            'name' => 'failure-modes.destroy',
            'description' => 'Eliminar modos de falla'
        ]);
    }
}
