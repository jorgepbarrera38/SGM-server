<?php

namespace App\Http\Controllers;

use App\Models\PreventiveMaintenance;
use App\Models\Device;
use App\Models\Period;
use App\Models\Replacement;
use App\Models\ScheduleMaintenanceDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Http\Requests\PreventiveMaintenanceRequest;
use Illuminate\Database\Eloquent\Builder;

class PreventiveMaintenanceController extends Controller
{
    public function __construct () {
        $this->middleware('has.permission:preventive-maintenances.create')->only(['store']);
        $this->middleware('has.permission:preventive-maintenances.edit')->only(['update']);
        $this->middleware('has.permission:preventive-maintenances.destroy')->only(['destroy']);

    }

    public function getDataToCreatePreventiveMaintenance (Device $device) {

        $replacements = Replacement::orderBy('name')->get();
        return [
            'conclusion' => $device->description_support_preventive_maintenance,
            'replacements' => $replacements
        ];
    }

    public function store(PreventiveMaintenanceRequest $request, Device $device)
    {
        //return response()->json(['errors' => ['message' => ]], 422);
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
        if (!periodIsActive($request->date_of_realization_ending)) {
            return response()->json(['errors' => ['message' => ['La fecha y hora fin de realización no se encuentra en un periodo vigente']]], 422);
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

            //Obteniendo la data para generar el reporte digital pdf
            $data = getDataForReportDigitalToPreventiveMaintenance($request, $replacements, $device);
            //Creando el reporte digital en base a una vista
            $pdf = \PDF::loadView('reports-digital.general-maintenances-preventives-and-others', $data)->setPaper('letter');
            //return $pdf->download('asd.pdf');
            $pathEvidence = 'preventive-maintenances/'.Str::random(45).'.pdf';
            Storage::put($pathEvidence, $pdf->download()->getOriginalContent());
        } else {
            //Guardando evidencia
            $pathEvidence = Storage::putFile('preventive-maintenances', $request->evidence);
        }

        //Verificando que esté programado el preventivo para la fecha dada y que la programación esté activa
        $yearFromRequest = Carbon::createFromDate($request->date_of_realization_ending)->year;
        $monthFromRequest = Carbon::createFromDate($request->date_of_realization_ending)->month;

        $scheduleMaintenanceDetail = ScheduleMaintenanceDetail::whereYear('date', $yearFromRequest)
                                                              ->whereMonth('date', $monthFromRequest)
                                                              ->where('device_id', $device->id)
                                                              ->whereHas('scheduleMaintenance', function (Builder $query) {
                                                                $query->where('finished', '!=', true);
                                                              })
                                                              ->first();

        if (!$scheduleMaintenanceDetail) {
            return response()->json(['errors' => ['message' => ['El dispositivo no tiene programación de mantenimiento preventivo para la fecha y hora de finalización dada']]], 422);
        }

        //Verificando que el preventivo no haya sido realizado con anterioridad
        if ($scheduleMaintenanceDetail->made) {
            return response()->json(['errors' => ['message' => ['El dispositivo ya tiene registrado el mantenimiento preventivo para la fecha dada']]], 422);
        }

        \DB::transaction(function () use($device, $request, $pathEvidence, $replacements, $scheduleMaintenanceDetail) {
            //Registrando el preventivo
            $preventiveMaintenance = $device->preventiveMaintenances()->create([
                'date_of_realization' => $request->date_of_realization,
                'date_of_realization_ending' => $request->date_of_realization_ending,
                'evidence' => $pathEvidence,
                'location_id' => $device->location_id,
                'dependence_id' => $device->dependence_id,
                'user_id' => auth()->user()->id
            ]);

            //Registrando los repuestos
            $preventiveMaintenance->replacementMaintenancables()->createMany($replacements);

            //Actualizando en la programación el estado y la relación del mantenimiento preventivo realizado
            $scheduleMaintenanceDetail->made = true;
            $scheduleMaintenanceDetail->preventive_maintenance_id = $preventiveMaintenance->id;
            $scheduleMaintenanceDetail->save();
        });

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PreventiveMaintenance  $preventiveMaintenance
     * @return \Illuminate\Http\Response
     */
    public function show($preventiveMaintenance)
    {
        $preventiveMaintenance = PreventiveMaintenance::with('replacementMaintenancables.replacement')->where('id', $preventiveMaintenance)->firstOrFail();
        $description_support_preventive_maintenance = $preventiveMaintenance->device->description_support_preventive_maintenance;
        $replacements = Replacement::orderBy('name')->get(['id', 'name']);
        return [
            'date_of_realization' => $preventiveMaintenance->date_of_realization,
            'date_of_realization_ending' => $preventiveMaintenance->date_of_realization_ending,
            'description_support_preventive_maintenance' => $description_support_preventive_maintenance,
            'replacementsFromPreventive' => $preventiveMaintenance->replacementMaintenancables,
            'replacements' => $replacements,
        ];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PreventiveMaintenance  $preventiveMaintenance
     * @return \Illuminate\Http\Response
     */
    public function update(PreventiveMaintenanceRequest $request, PreventiveMaintenance $preventiveMaintenance)
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
        if (!periodIsActive($request->date_of_realization_ending)) {
            return response()->json(['errors' => ['message' => ['La fecha y hora fin de realización no se encuentra en un periodo vigente']]], 422);
        }

        //Verificando que el usuario esté autorizado en el grupo del dispositivo
        $hasPermissionInGroup = auth()->user()->groups()->where('groups.id', $preventiveMaintenance->device->group_id)->first();

        if (!$hasPermissionInGroup) {
            return response()->json(['errors' => ['message' => ['El usuario no está autorizado en el grupo del dispositivo']]], 422);
        }

        //Verificando si el reporte es digital=1 o físico=2 para generar o guardar el pdf
        if ($request->type_report == 1) {
            //Verificando si el usuario tiene firma
            if (!auth()->user()->hasSignature()) {
                return response()->json(['errors' => ['message' => ['Debe configurar la firma del usuario']]], 422);
            }

            //Obteniendo la data para generar el reporte digital pdf
            $data = getDataForReportDigitalToPreventiveMaintenance($request, $replacements, $preventiveMaintenance->device);

            //Creando el reporte digital en base a una vista
            $pdf = \PDF::loadView('reports-digital.general-maintenances-preventives-and-others', $data)->setPaper('letter');
            //return $pdf->download('asd.pdf');
            $pathEvidence = 'preventive-maintenances/'.Str::random(45).'.pdf';
            Storage::put($pathEvidence, $pdf->download()->getOriginalContent());
        } else {
            //Guardando evidencia física
            $pathEvidence = Storage::putFile('preventive-maintenances', $request->evidence);
        }

        //Verificando que esté programado el preventivo para la fecha dada
        //$scheduleMaintenanceDetail = ScheduleMaintenanceDetail::->firstOrFail();
        $yearFromRequest = Carbon::createFromDate($request->date_of_realization_ending)->year;
        $monthFromRequest = Carbon::createFromDate($request->date_of_realization_ending)->month;

        $scheduleMaintenanceDetail = ScheduleMaintenanceDetail::whereYear('date', $yearFromRequest)
                                                              ->whereMonth('date', $monthFromRequest)
                                                              ->where('device_id', $preventiveMaintenance->device->id)
                                                              ->whereHas('scheduleMaintenance', function (Builder $query) {
                                                                $query->where('finished', '!=', true);
                                                              })
                                                              ->first();

        if (!$scheduleMaintenanceDetail) {
            return response()->json(['errors' => ['message' => ['El dispositivo no tiene programación de mantenimiento preventivo para la fecha y hora de finalización dada']]], 422);
        }

        //Verificando que el preventivo no haya sido realizado con anterioridad
        /*if ($scheduleMaintenanceDetail->made) {
            return response()->json(['errors' => ['message' => ['El dispositivo ya tiene registrado el mantenimiento preventivo para la fecha dada']]], 422);
        }*/

        $pathEvidencePrevious = $preventiveMaintenance->evidence;


        \DB::transaction(function () use ($preventiveMaintenance, $pathEvidence, $request, $replacements, $scheduleMaintenanceDetail) {
            //Actualizando el preventivo
            $preventiveMaintenance->evidence = $pathEvidence;
            $preventiveMaintenance->date_of_realization = $request->date_of_realization;
            $preventiveMaintenance->date_of_realization_ending = $request->date_of_realization_ending;
            $preventiveMaintenance->location_id = $preventiveMaintenance->device->location_id;
            $preventiveMaintenance->dependence_id = $preventiveMaintenance->device->dependence_id;
            $preventiveMaintenance->update();

            //Actualizando los repuestos los repuestos
            $preventiveMaintenance->replacementMaintenancables()->delete();
            $preventiveMaintenance->replacementMaintenancables()->createMany($replacements);

            //Actualizando en la programación el estado y la relación del mantenimiento preventivo realizado
            $scheduleMaintenanceDetail->made = true;
            $scheduleMaintenanceDetail->preventive_maintenance_id = $preventiveMaintenance->id;
            $scheduleMaintenanceDetail->save();
        });

        //Elminando el reporte anterior
        if (Storage::exists($pathEvidencePrevious)) {
            Storage::delete($pathEvidencePrevious);
        }



    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PreventiveMaintenance  $preventiveMaintenance
     * @return \Illuminate\Http\Response
     */
    public function destroy(PreventiveMaintenance $preventiveMaintenance)
    {
        //Verificnado que solo pueda eliminar el que haya registrado el preventivo
        if (auth()->user()->id == $preventiveMaintenance->user_id) {

            //Eliminando el archivo de evidencia pdf generado
            if (Storage::exists($preventiveMaintenance->evidence)) {
                Storage::delete($preventiveMaintenance->evidence);
            }

            //Eliminando la relación el detalle del cronograma
            if ($preventiveMaintenance->scheduleMaintenanceDetail) {
                $preventiveMaintenance->scheduleMaintenanceDetail()->update([
                    'made' => false,
                    'preventive_maintenance_id' => null
                ]);
            }

            return $preventiveMaintenance->delete();
        }

        return abort(403);
    }
}
