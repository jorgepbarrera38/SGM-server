<?php

namespace App\Http\Controllers;

use App\Models\OtherMaintenance;
use App\Models\Device;
use App\Models\Period;
use App\Models\TypeOtherMaintenance;
use App\Models\Replacement;
use App\Models\FailureMode;
use App\Models\Checklist;
use App\Models\Company;
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
        $replacements = Replacement::orderBy('name')->get(['id', 'name']);
        $failureModes = FailureMode::orderBy('name')->get(['id', 'name']);
        $checklists = Checklist::with('checklistGroups.checklistItems')->orderBy('name')->get();
        
        return [
            'typeOtherMaintenances' => $typeOtherMaintenances,
            'replacements' => $replacements,
            'failureModes' => $failureModes,
            'checklists' => $checklists,
        ];
    }

    public function store(OtherMaintenanceRequest $request, Device $device)
    {
        $validateData = $this->validateData($request, $device);

        if ($validateData->failed) {
            return response()->json(['errors' => ['message' => [$validateData->message]]], 422);
        }
        
        //Verificando si el reporte es digital=1 o físico=2 para generar o guardar el pdf
        if ($request->type_report == 1) {

            \DB::beginTransaction();
            $pathsFromImagesSaved = [];
            try {

                $otherMaintenance = $device->otherMaintenances()->create([
                    'type' => 'Digital',
                    'date_of_realization' => $request->date_of_realization,
                    'date_of_realization_ending' => $request->date_of_realization_ending,
                    'type_other_maintenance_id' => $request->type_maintenance_id,
                    'reason' => $request->reason,
                    'support_description' => $request->support_description,
                    'failure_mode_id' => $request->failure_mode_id,
                    'location_id' => $device->location_id,
                    'dependence_id' => $device->dependence_id,
                    'evidence' => null,
                    'official_names' => $request->official_names,
                    'official_position' => $request->official_position,
                    'signature' => $request->signature,
                    'user_id' => auth()->user()->id,
                ]);
        
                //Registrando los repuestos
                $replacements = json_decode($request->replacementsInJson, true);
                $otherMaintenance->replacements()->createMany($replacements);

                //Registrando listas de chequeo
                $checklists = json_decode($request->checklists);

                foreach ($checklists as $checklist) {
                    $otherMaintenanceChecklist = $otherMaintenance->checklists()->create(['name' => $checklist->name]);
                    foreach ($checklist->checklist_groups as $checklist_group) {
                        $otherMaintenanceChecklistGroup = $otherMaintenanceChecklist->checklistGroups()->create(['name' => $checklist_group->name]);
                        foreach ($checklist_group->checklist_items as $checklist_item) {
                            $otherMaintenanceChecklistGroup->checklistItems()->create([
                                'description' => $checklist_item->description,
                                'response' => $checklist_item->response
                            ]);

                        }
                    }
                }

                //Registrando registros fotográficos - Solo se guardan en mantenimientos en digitales
                if ($request->photographicAnnexes) {
                    foreach ($request->photographicAnnexes as $photographicAnnexe) {
                        $imagePath = Storage::putFile('photographic-annexes', $photographicAnnexe['image']);
                        $pathsFromImagesSaved[] = $imagePath;
                        $otherMaintenance->photographicAnnexes()->create([
                            'description' => $photographicAnnexe['description'],
                            'image' => $imagePath,
                        ]);
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
                $otherMaintenance = $device->otherMaintenances()->create([
                    'type' => 'Físico',
                    'date_of_realization' => $request->date_of_realization,
                    'date_of_realization_ending' => $request->date_of_realization_ending,
                    'type_other_maintenance_id' => $request->type_maintenance_id,
                    'failure_mode_id' => $request->failure_mode_id,
                    'location_id' => $device->location_id,
                    'dependence_id' => $device->dependence_id,
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

    public function validateData ($request, $device) {
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

        //Verificando que el usuario esté autorizado en el grupo del dispositivo
        $hasPermissionInGroup = auth()->user()->groups()->where('groups.id', $device['group_id'])->first();

        if (!$hasPermissionInGroup) {
            $response->failed = true;
            $response->message = 'El usuario no está autorizado en el grupo del dispositivo';
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
     * @param  \App\Models\OtherMaintenance  $otherMaintenance
     * @return \Illuminate\Http\Response
     */
    public function show($otherMaintenance)
    {
        return OtherMaintenance::with(['replacements.replacement', 'photographicAnnexes', 'checklists.checklistGroups.checklistItems'])->where('id', $otherMaintenance)->firstOrFail();
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

        $validateData = $this->validateData($request, $otherMaintenance->device);

        if ($validateData->failed) {
            return response()->json(['errors' => ['message' => [$validateData->message]]], 422);
        }

        //Verificando autenticidad del registro
        if (auth()->user()->id != $otherMaintenance->user_id) {
            return abort(403);
        }

        if ($otherMaintenance->type == 'Digital') {
            
            \DB::beginTransaction();
            $pathsFromImagesSaved = [];

            try {

                $otherMaintenance->update([
                    'date_of_realization' => $request->date_of_realization,
                    'date_of_realization_ending' => $request->date_of_realization_ending,
                    'type_other_maintenance_id' => $request->type_maintenance_id,
                    'reason' => $request->reason,
                    'support_description' => $request->support_description,
                    'failure_mode_id' => $request->failure_mode_id,
                    'location_id' => $otherMaintenance->device->location_id,
                    'dependence_id' => $otherMaintenance->device->dependence_id,
                    'evidence' => null,
                    'official_names' => $request->official_names,
                    'official_position' => $request->official_position,
                    'signature' => $request->signature,
                    'user_id' => auth()->user()->id,
                ]);
        
                //Elminando repuestos anteriores y registrando los nuevos
                $otherMaintenance->replacements()->delete();

                $replacements = json_decode($request->replacementsInJson, true);
                $otherMaintenance->replacements()->createMany($replacements);

                //Eliminando listas de chequeo anteriores y registrando las nuevas
                $otherMaintenance->checklists->each(function ($checklist) {
                    $checklist->checklistGroups->each(function ($group) {
                        $group->checklistItems->each(function ($item) {
                            $item->delete();
                        });
                        $group->delete();
                    });
                    $checklist->delete();
                });
    
                $checklists = json_decode($request->checklists);
    
                foreach ($checklists as $checklist) {
                    $otherMaintenanceChecklist = $otherMaintenance->checklists()->create(['name' => $checklist->name]);
                    foreach ($checklist->checklist_groups as $checklist_group) {
                        $otherMaintenanceChecklistGroup = $otherMaintenanceChecklist->checklistGroups()->create(['name' => $checklist_group->name]);
                        foreach ($checklist_group->checklist_items as $checklist_item) {
                            $otherMaintenanceChecklistGroup->checklistItems()->create([
                                'description' => $checklist_item->description,
                                'response' => $checklist_item->response
                            ]);
    
                        }
                    }
                }

                //Elminando registros fotográficos anteriores y registrando los nuevos
                $pathsFromImagesToDelete = [];

                foreach ($otherMaintenance->photographicAnnexes as $photographicAnnexe) {
                    if (Storage::exists($photographicAnnexe->image)) {
                        $pathsFromImagesToDelete[] = $photographicAnnexe->image;
                    }
                }

                $otherMaintenance->photographicAnnexes()->delete();

                if ($request->photographicAnnexes) {
                    foreach ($request->photographicAnnexes as $photographicAnnexe) {
                        $imagePath = Storage::putFile('photographic-annexes', $photographicAnnexe['image']);
                        $pathsFromImagesSaved[] = $imagePath;
                        $otherMaintenance->photographicAnnexes()->create([
                            'description' => $photographicAnnexe['description'],
                            'image' => $imagePath,
                        ]);
                    }
                }

                foreach ($pathsFromImagesToDelete as $photographicAnnexe) {
                    if (Storage::exists($photographicAnnexe)) {
                        Storage::delete($photographicAnnexe);
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

                $oldEvidence = $otherMaintenance->evidence;

                //Actualizando el mantenimiento físico
                $otherMaintenance->update([
                    'date_of_realization' => $request->date_of_realization,
                    'date_of_realization_ending' => $request->date_of_realization_ending,
                    'type_other_maintenance_id' => $request->type_maintenance_id,
                    'failure_mode_id' => $request->failure_mode_id,
                    'location_id' => $otherMaintenance->device->location_id,
                    'dependence_id' => $otherMaintenance->device->dependence_id,
                    'evidence' => $pathEvidence,
                    'official_names' => null,
                    'official_position' => null,
                    'signature' => null,
                    'user_id' => auth()->user()->id,
                ]);

                //Elminando repuestos anteriores y registrando los nuevos
                $otherMaintenance->replacements()->delete();

                $replacements = json_decode($request->replacementsInJson, true);
                $otherMaintenance->replacements()->createMany($replacements);

                //Elminando el reporte físico anterior
                if (Storage::exists($oldEvidence)) {
                    Storage::delete($oldEvidence);
                }

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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OtherMaintenance  $otherMaintenance
     * @return \Illuminate\Http\Response
     */
    public function destroy(OtherMaintenance $otherMaintenance)
    {
        //Verificando que solo pueda eliminar el que haya registrado el mantenimiento
        if (auth()->user()->id == $otherMaintenance->user_id) {

            \DB::beginTransaction();

            try {
                //Eliminando el archivo de evidencia pdf generado
                if (Storage::exists($otherMaintenance->evidence)) {
                    Storage::delete($otherMaintenance->evidence);
                }
    
                //Eliminando repuestos
                $otherMaintenance->replacements()->delete();

		//Eliminando listas de chequeo
                $otherMaintenance->checklists->each(function ($checklist) {
                    $checklist->checklistGroups->each(function ($group) {
                        $group->checklistItems->each(function ($item) {
                            $item->delete();
                        });
                        $group->delete();
                    });
                    $checklist->delete();
                });
    
                //Eliminando anexos fotográficos
                $photographicAnnexes = $otherMaintenance->photographicAnnexes;

                $otherMaintenance->photographicAnnexes()->delete();
    
                //Eliminando mantenimiento   
                $otherMaintenance->delete();

                foreach ($photographicAnnexes as $photographicAnnexe) {
                    if (Storage::exists($photographicAnnexe->image)) {
                        Storage::delete($photographicAnnexe->image);
                    }
                }

                \DB::commit();

            } catch (\Exception $e) {
                \DB::rollback();
                return response()->json(['errors' => ['message' => ['El mantenimiento no se pudo eliminar:' . $e->getMessage()]]], 422);
            }
        } else {
            return abort(403);
        }
    }

    public function getEvidenceFromOtherMaintenance (OtherMaintenance $otherMaintenance) {
        if ($otherMaintenance->type == 'Digital') {
            
            $company = Company::first();
            
            if ($otherMaintenance->device_id == null) {
                $pdf = \PDF::loadView('reports.maintenances-in-pdf', ['maintenance' => $otherMaintenance, 'company' => $company])->setPaper('letter');
            } else {
                $pdf = \PDF::loadView('resume.general-maintenances-preventives-and-others', [
                                                                                                'device' => $otherMaintenance->device, 
                                                                                                'maintenance' => $otherMaintenance, 
                                                                                                'company' => $company])
                                                                                                ->setPaper('letter');
            }

            return $pdf->download();

        } else {
            
            return Storage::download($otherMaintenance->evidence);

        }
    }
}