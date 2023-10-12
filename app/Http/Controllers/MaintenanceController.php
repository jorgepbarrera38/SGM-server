<?php

namespace App\Http\Controllers;

use App\Models\Period;
use App\Models\Location;
use App\Models\Dependence;
use App\Models\OtherMaintenance;
use App\Models\TypeOtherMaintenance;
use App\Models\Group;
use App\Models\Replacement;
use App\Models\FailureMode;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\GeneralMaintenanceRequest;
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
        $groups = Group::orderBy('name')->get(['id', 'name']);
        $typeOtherMaintenances =  TypeOtherMaintenance::orderBy('name')->get(['id', 'name']);
        $replacements =  Replacement::orderBy('name')->get(['id', 'name']);
        $locations = Location::orderBy('name')->get();
        $dependences = Dependence::orderBy('name')->get();
        $failureModes = FailureMode::orderBy('name')->get(['id', 'name']);
        
        return [
            'groups' => $groups,
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
    public function store(GeneralMaintenanceRequest $request)
    {
        $validateData = $this->validateData($request);

        if ($validateData->failed) {
            return response()->json(['errors' => ['message' => [$validateData->message]]], 422);
        }

        //Verificando que el usuario esté autorizado en el grupo selecccionado
        $hasPermissionInGroup = auth()->user()->groups()->where('groups.id', $request->group_id)->first();

        if (!$hasPermissionInGroup) {
            return response()->json(['errors' => ['message' => ['El usuario no está autorizado en el grupo seleccionado']]], 422);
        }
        
        //Verificando si el reporte es digital=1 o físico=2 para generar o guardar el pdf
        if ($request->type_report == 1) {

            \DB::beginTransaction();
            $pathsFromImagesSaved = [];
            try {

                $otherMaintenance = OtherMaintenance::create([
                    'type' => 'Digital',
                    'group_id' => $request->group_id,
                    'date_of_realization' => $request->date_of_realization,
                    'date_of_realization_ending' => $request->date_of_realization_ending,
                    'type_other_maintenance_id' => $request->type_maintenance_id,
                    'reason' => $request->reason,
                    'support_description' => $request->support_description,
                    'failure_mode_id' => $request->failure_mode_id,
                    'location_id' => $request->location_id,
                    'dependence_id' => $request->dependence_id,
                    'evidence' => null,
                    'official_names' => $request->official_names,
                    'official_position' => $request->official_position,
                    'signature' => $request->signature,
                    'user_id' => auth()->user()->id,
                ]);
        
                //Registrando los repuestos
                $replacements = json_decode($request->replacementsInJson, true);
                $otherMaintenance->replacements()->createMany($replacements);

                //Registrando registros fotográficos - Solo se guardan en mantenimientos en digitales
                if ($request->photographicAnnexes) {
                    foreach ($request->photographicAnnexes as $photographicAnnexe) {
                        $imagePath = Storage::putFile('photographic-annexes', $photographicAnnexe['image']);
                        $otherMaintenance->photographicAnnexes()->create([
                            'description' => $photographicAnnexe['description'],
                            'image' => $imagePath,
                        ]);
                        $pathsFromImagesSaved[] = $imagePath;
                    }
                }

                \DB::commit();
            
            } catch (\Exception $e) {
                \DB::rollback();
                foreach ($pathsFromImagesSaved as $pathFromImagesSaved) {
                    if (Storage::exists($pathFromImagesSaved)) {
                        Storage::delete($pathFromImagesSaved);
                    }
                }
                return response()->json(['errors' => ['message' => ['El mantenimiento no se pudo registrar:' . $e->getMessage()]]], 422);
            }

        } else {

            \DB::beginTransaction();

            try {
                //Almacenando evidencia PDF
                $pathEvidence = Storage::putFile('other-maintenances', $request->evidence);

                //Registrando el mantenimiento físico
                $otherMaintenance = OtherMaintenance::create([
                    'type' => 'Físico',
                    'date_of_realization' => $request->date_of_realization,
                    'date_of_realization_ending' => $request->date_of_realization_ending,
                    'type_other_maintenance_id' => $request->type_maintenance_id,
                    'failure_mode_id' => $request->failure_mode_id,
                    'location_id' => $request->location_id,
                    'dependence_id' => $request->dependence_id,
                    'evidence' => $pathEvidence,
                    'official_names' => null,
                    'official_position' => null,
                    'signature' => null,
                    'user_id' => auth()->user()->id,
                ]);

                //Registrando los repuestos
                $replacements = json_decode($request->replacementsInJson, true);
                $otherMaintenance->replacements()->createMany($replacements);


                \DB::commit();

            } catch (\Exception $e) {
                \DB::rollback();
                if (Storage::exists($pathEvidence)) {
                    Storage::delete($pathEvidence);
                }
                return response()->json(['errors' => ['message' => ['El mantenimiento no se pudo registrar:' . $e->getMessage()]]], 422);
            }
        }
    }

    public function validateData ($request) {
        $response = new \stdClass();
        $response->failed = false;
        $response->message = null;
        //Verificando que la empresa tenga logo
        /*if (!companyHasLogo()) {
            $response->failed = true;
            $response->message = 'Debe configurar el logo en los datos de empresa';
        }*/

        //Verificando que el usuario sea técnico
        if (!auth()->user()->technical_support) {
            $response->failed = true;
            $response->message = 'El usuario no está definido como soporte técnico';
        }

        //Verificando que el periodo esté activo
        if (!periodIsActive($request->date_of_realization_ending)) {
            $response->failed = true;
            $response->message = 'La fecha y hora fin de realización no se encuentra en un periodo vigente';
        }

        //Validando que los repuestos existan y tengan cantidades
        $replacements = json_decode($request->replacementsInJson, true);
        if ($errors = replacementsHasErrors($replacements)) {
            $response->failed = true;
            $response->message = $errors;
        }
        
        return $response;

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
