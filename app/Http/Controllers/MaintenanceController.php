<?php

namespace App\Http\Controllers;

use App\Models\Period;
use App\Models\Location;
use App\Models\Dependence;
use App\Models\OtherMaintenance;
use App\Models\TypeOtherMaintenance;
use App\Models\Replacement;
use App\Models\FailureMode;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\OtherMaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MaintenanceController extends Controller
{
    public function __construct () {
        $this->middleware('has.permission:maintenances.create')->only(['getDataToCreateOtherMaintenance', 'store']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function getDataToCreateOtherMaintenance () {
        $typeOtherMaintenances =  TypeOtherMaintenance::orderBy('name')->get(['id', 'name']);
        $replacements =  Replacement::orderBy('name')->get(['id', 'name']);
        $locations = Location::orderBy('name')->get();
        $dependences = Dependence::orderBy('name')->get();
        $failureModes = FailureMode::orderBy('name')->get(['id', 'name']);
        
        return [
            'typeOtherMaintenances' => $typeOtherMaintenances,
            'replacements' => $replacements,
            'locations' => $locations,
            'dependences' => $dependences,
            'failureModes' => $failureModes
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(OtherMaintenanceRequest $request)
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
        
        //Verificando si el reporte es digital=1 o físico=2 para generar o guardar el pdf
        if ($request->type_report == 1) {
            //Verificando si el usuario tiene firma
            if (!auth()->user()->hasSignature()) {
                return response()->json(['errors' => ['message' => ['Debe configurar la firma del usuario']]], 422);
            }

            //Obteniendo la data para generar el reporte pdf
            $data = getDataForReportDigitalToGeneralMaintenancesAndOthers($request, $replacements);

            //Creando el reporte digital en base a una vista
            $pdf = \PDF::loadView('reports-digital.general-maintenances-preventives-and-others', $data)->setPaper('letter');
            //return $pdf->download('asd.pdf');
            $pathEvidence = 'other-maintenances/'.Str::random(45).'.pdf';
            Storage::put($pathEvidence, $pdf->download()->getOriginalContent());
        } else {
            //Guardando evidencia
            $pathEvidence = Storage::putFile('other-maintenances', $request->evidence);
        }


        \DB::transaction(function () use ($request, $pathEvidence, $replacements) {
            $otherMaintenance = OtherMaintenance::create([
                'date_of_realization' => $request->date_of_realization,
                'date_of_realization_ending' => $request->date_of_realization_ending,
                'type_other_maintenance_id' => $request->type_maintenance_id,
                'reason' => $request->reason,
                'conclusion' => $request->conclusion,
                'failure_mode_id' => $request->failure_mode_id,
                'location_id' => $request->location_id,
                'dependence_id' => $request->dependence_id,
                'evidence' => $pathEvidence,
                'user_id' => auth()->user()->id
            ]);

            $otherMaintenance->replacementMaintenancables()->createMany($replacements);
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
