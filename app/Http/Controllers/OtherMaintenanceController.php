<?php

namespace App\Http\Controllers;

use App\Models\OtherMaintenance;
use App\Models\Device;
use App\Models\Period;
use App\Models\TypeOtherMaintenance;
use App\Models\Replacement;
use App\Http\Requests\OtherMaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OtherMaintenanceController extends Controller
{
    public function __construct () {
        $this->middleware('has.permission:other-maintenances.create')->only(['store']);
        $this->middleware('has.permission:other-maintenances.edit')->only(['update']);
        $this->middleware('has.permission:other-maintenances.destroy')->only(['destroy']);
        
    }

    public function getDataToCreateOtherMaintenance () {
        $typeOtherMaintenances =  TypeOtherMaintenance::orderBy('name')->get(['id', 'name']);
        $replacements =  Replacement::orderBy('name')->get(['id', 'name']);
        
        return [
            'typeOtherMaintenances' => $typeOtherMaintenances,
            'replacements' => $replacements
        ];
    }

    public function store(OtherMaintenanceRequest $request, Device $device)
    {
        //Validando que los repuestos existan y tengan cantidades
        $replacements = json_decode($request->replacementsInJson, true);
        if ($errors = replacementsHasErrors($replacements)) {
            return response()->json(['errors' => ['message' => [$errors]]], 422);
        }

        //Verificando que la empresa tenga logo
        if (!companyHasLogo()) {
            return response()->json(['errors' => ['message' => ['Debe configurar el logo en los datos de empresa']]], 422);
        }

        //Verificando que el usuario sea técnico
        if (!auth()->user()->technical_support) {
            return response()->json(['errors' => ['message' => ['El usuario no está definido como soporte técnico']]], 422);
        }

        //Verificando que el periodo esté activo
        if (!periodIsActive($request->date_of_realization)) {
            return response()->json(['errors' => ['message' => ['La fecha de realización no se encuentra en un periodo vigente']]], 422);
        }

        //Verificando que el usuario esté autorizado en el grupo del dispositivo
        $hasPermissionInGroup = auth()->user()->groups()->where('groups.id', $device['group_id'])->first();

        if (!$hasPermissionInGroup) {
            return response()->json(['errors' => ['message' => ['El usuario no está autorizado en el grupo del dispositivo']]], 422);
        }  
        
        //Verificando si el reporte es digital=1 o físico=2 para generar o guardar el pdf
        if ($request->type_report == 1) {
            //Verificando si el usuario tiene firma
            if (!auth()->user()->hasSignature()) {
                return response()->json(['errors' => ['message' => ['Debe configurar la firma del usuario']]], 422);
            }

            //Obteniendo la data para generar el reporte pdf
            $data = getDataForReportDigitalToGeneralMaintenancesAndOthers($request, $replacements, $device);

            //Creando el reporte digital en base a una vista
            $pdf = \PDF::loadView('reports-digital.general-maintenances-preventives-and-others', $data)->setPaper('letter');
            //return $pdf->download('asd.pdf');
            $pathEvidence = 'other-maintenances/'.Str::random(45).'.pdf';
            Storage::put($pathEvidence, $pdf->download()->getOriginalContent());
        } else {
            //Guardando evidencia
            $pathEvidence = Storage::putFile('other-maintenances', $request->evidence);
        }

        //Creando el mantenimiento y registrando los repuestos
        \DB::transaction(function () use ($device, $request, $pathEvidence, $replacements) {
            $otherMaintenance = $device->otherMaintenances()->create([
                'date_of_realization' => $request->date_of_realization,
                'type_other_maintenance_id' => $request->type_maintenance_id,
                'reason' => $request->reason,
                'conclusion' => $request->conclusion,
                'location_id' => $device->location_id,
                'dependence_id' => $device->dependence_id,
                'evidence' => $pathEvidence,
                'user_id' => auth()->user()->id
            ]);
    
            $otherMaintenance->replacementMaintenancables()->createMany($replacements);
        });

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OtherMaintenance  $otherMaintenance
     * @return \Illuminate\Http\Response
     */
    public function show($otherMaintenance)
    {
        return OtherMaintenance::with('replacementMaintenancables.replacement')->where('id', $otherMaintenance)->firstOrFail();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OtherMaintenance  $otherMaintenance
     * @return \Illuminate\Http\Response
     */
    public function update(OtherMaintenanceRequest $request, OtherMaintenance $otherMaintenance)
    {
        //Verificando autenticidad del registro
        if (auth()->user()->id != $otherMaintenance->user_id) {
            return abort(403);
        }

        //Validando que los repuestos existan y tengan cantidades
        $replacements = json_decode($request->replacementsInJson, true);
        if ($errors = replacementsHasErrors($replacements)) {
            return response()->json(['errors' => ['message' => [$errors]]], 422);
        }

        //Verificando que la empresa tenga logo
        if (!companyHasLogo()) {
            return response()->json(['errors' => ['message' => ['Debe configurar el logo en los datos de empresa']]], 422);
        }

        //Verificando que el usuario sea técnico
        if (!auth()->user()->technical_support) {
            return response()->json(['errors' => ['message' => ['El usuario no está definido como soporte técnico']]], 422);
        }

        //Verificando que el periodo esté activo
        if (!periodIsActive($request->date_of_realization)) {
            return response()->json(['errors' => ['message' => ['La fecha de realización no se encuentra en un periodo vigente']]], 422);
        }

        //Verificando que el usuario esté autorizado en el grupo del dispositivo
        $hasPermissionInGroup = auth()->user()->groups()->where('groups.id', $otherMaintenance->device['group_id'])->first();

        if (!$hasPermissionInGroup) {
            return response()->json(['errors' => ['message' => ['El usuario no está autorizado en el grupo del dispositivo']]], 422);
        }  
        
        //Verificando si el reporte es digital=1 o físico=2 para generar o guardar el pdf
        if ($request->type_report == 1) {
            //Verificando si el usuario tiene firma
            if (!auth()->user()->hasSignature()) {
                return response()->json(['errors' => ['message' => ['Debe configurar la firma del usuario']]], 422);
            }

            //Obteniendo la data para generar el reporte pdf
            $data = getDataForReportDigitalToGeneralMaintenancesAndOthers($request, $replacements, $otherMaintenance->device);

            //Creando el reporte digital en base a una vista
            $pdf = \PDF::loadView('reports-digital.general-maintenances-preventives-and-others', $data)->setPaper('letter');
            //return $pdf->download('asd.pdf');
            $pathEvidence = 'other-maintenances/'.Str::random(45).'.pdf';
            Storage::put($pathEvidence, $pdf->download()->getOriginalContent());
        } else {
            //Guardando evidencia
            $pathEvidence = Storage::putFile('other-maintenances', $request->evidence);
        }

        $pathEvidencePrevious = $otherMaintenance->evidence;

        $otherMaintenance->fill($request->only(['date_of_realization', 'type_other_maintenance_id', 'reason', 'conclusion']));
        $otherMaintenance->location_id = $otherMaintenance->device->location_id;
        $otherMaintenance->dependence_id = $otherMaintenance->device->dependence_id;
        $otherMaintenance->evidence = $pathEvidence;

        //Transacción
        \DB::transaction(function () use($otherMaintenance, $request, $replacements) {
            //Actualizando el mantenimiento
            $otherMaintenance->update();

            //Actualizando los repuestos
            $otherMaintenance->replacementMaintenancables()->delete();
            $otherMaintenance->replacementMaintenancables()->createMany($replacements);

        });

        if (Storage::exists($pathEvidencePrevious)) {
            Storage::delete($pathEvidencePrevious);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OtherMaintenance  $otherMaintenance
     * @return \Illuminate\Http\Response
     */
    public function destroy(OtherMaintenance $otherMaintenance)
    {
        if (auth()->user()->id == $otherMaintenance->user_id) {

            if (Storage::exists($otherMaintenance->evidence)) {
                Storage::delete($otherMaintenance->evidence);
            }

            \DB::transaction(function () use ($otherMaintenance) {
                $otherMaintenance->replacementMaintenancables()->delete();
                $otherMaintenance->delete();
            });
            return;
        }

        return abort(403);
    }
}