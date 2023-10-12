<?php

namespace App\Http\Controllers;

use App\Models\PreventiveMaintenance;
use App\Models\Device;
use App\Models\Period;
use App\Models\Replacement;
use App\Models\ScheduleMaintenanceDetail;
use App\Models\Checklist;
use App\Models\Company;
use App\Models\PreventiveMaintenancePhotographicAnnexe;
use App\Models\PreventiveMaintenanceChecklist;
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
        $company = Company::first();
        $checklists = Checklist::with('checklistGroups.checklistItems')->orderBy('name')->get();
        return [
            'reason' => $company ? $company->reason_preventive_maintenance_report_digital : '',
            'support_description' => $device->description_support_preventive_maintenance,
            'replacements' => $replacements,
            'checklists' => $checklists,
        ];
    }

    public function store(PreventiveMaintenanceRequest $request, Device $device)
    {
        $validateData = $this->validateData($request, $device);

        if ($validateData->failed) {
            return response()->json(['errors' => ['message' => [$validateData->message]]], 422);
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

        //Verificando si el reporte es digital=1 o físico=2 para generar o guardar el pdf
        if ($request->type_report == 1) {

            \DB::beginTransaction();
            try {
                //Registrando el preventivo digital
                $preventiveMaintenance = $device->preventiveMaintenances()->create([
                    'type' => 'Digital',
                    'date_of_realization' => $request->date_of_realization,
                    'date_of_realization_ending' => $request->date_of_realization_ending,
                    'reason' => $request->reason,
                    'support_description' => $request->support_description,
                    'evidence' => null,
                    'location_id' => $device->location_id,
                    'dependence_id' => $device->dependence_id,
                    'official_names' => $request->official_names,
                    'official_position' => $request->official_position,
                    'user_id' => auth()->user()->id,
                    'signature' => $request->signature
                ]);

                //Registrando los repuestos
                $replacements = json_decode($request->replacementsInJson, true);
                $preventiveMaintenance->replacements()->createMany($replacements);

                //Registrando listas de chequeo
                $checklists = json_decode($request->checklists);

                foreach ($checklists as $checklist) {
                    $preventiveMaintenanceChecklist = $preventiveMaintenance->checklists()->create(['name' => $checklist->name]);
                    foreach ($checklist->checklist_groups as $checklist_group) {
                        $preventiveMaintenanceChecklistGroup = $preventiveMaintenanceChecklist->checklistGroups()->create(['name' => $checklist_group->name]);
                        foreach ($checklist_group->checklist_items as $checklist_item) {
                            $preventiveMaintenanceChecklistGroup->checklistItems()->create([
                                'description' => $checklist_item->description,
                                'response' => $checklist_item->response
                            ]);

                        }
                    }
                }

                //Registrando registros fotográficos - Solo se guardan en mantenimientos en digitales
                $pathsFromImagesSaved = [];
                if ($request->photographicAnnexes) {
                    foreach ($request->photographicAnnexes as $photographicAnnexe) {
                        $imagePath = Storage::putFile('photographic-annexes', $photographicAnnexe['image']);
                        $pathsFromImagesSaved[] = $imagePath;
                        $preventiveMaintenance->photographicAnnexes()->create([
                            'description' => $photographicAnnexe['description'],
                            'image' => $imagePath,
                        ]);
                    }
                }

                //Actualizando en la programación el estado y la relación del mantenimiento preventivo realizado
                $scheduleMaintenanceDetail->made = true;
                $scheduleMaintenanceDetail->preventive_maintenance_id = $preventiveMaintenance->id;
                $scheduleMaintenanceDetail->save();

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
                $pathEvidence = Storage::putFile('preventive-maintenances', $request->evidence);
                //Registrando el preventivo físico
                $preventiveMaintenance = $device->preventiveMaintenances()->create([
                    'type' => 'Físico',
                    'date_of_realization' => $request->date_of_realization,
                    'date_of_realization_ending' => $request->date_of_realization_ending,
                    'evidence' => $pathEvidence,
                    'location_id' => $device->location_id,
                    'dependence_id' => $device->dependence_id,
                    'user_id' => auth()->user()->id
                ]);

                //Registrando los repuestos
                $replacements = json_decode($request->replacementsInJson, true);
                $preventiveMaintenance->replacements()->createMany($replacements);

                //Actualizando en la programación el estado y la relación del mantenimiento preventivo realizado
                $scheduleMaintenanceDetail->made = true;
                $scheduleMaintenanceDetail->preventive_maintenance_id = $preventiveMaintenance->id;
                $scheduleMaintenanceDetail->save();

                \DB::commit();

            } catch (\Exception $e) {
                \DB::rollback();
                if (Storage::exists($pathEvidence)) {
                    Storage::delete($pathEvidence);
                }
                return response()->json(['errors' => ['message' => ['El mantenimiento preventivo no se pudo registrar:' . $e->getMessage()]]], 422);
            }
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PreventiveMaintenance  $preventiveMaintenance
     * @return \Illuminate\Http\Response
     */
    public function edit($preventiveMaintenance)
    {
        $preventiveMaintenance = PreventiveMaintenance::with(['replacements.replacement', 'photographicAnnexes'])->where('id', $preventiveMaintenance)->firstOrFail();
        $replacements = Replacement::orderBy('name')->get(['id', 'name']);
        $checklists = Checklist::with('checklistGroups.checklistItems')->orderBy('name')->get();
        $checklistsFromPreventive = $preventiveMaintenance->checklists->load('checklistGroups.checklistItems');

        return [
            'type' => $preventiveMaintenance->type,
            'date_of_realization' => $preventiveMaintenance->date_of_realization,
            'date_of_realization_ending' => $preventiveMaintenance->date_of_realization_ending,
            'reason' => $preventiveMaintenance->reason,
            'support_description' => $preventiveMaintenance->support_description,
            'photographicAnnexes' => $preventiveMaintenance->photographicAnnexes,
            'replacementsFromPreventive' => $preventiveMaintenance->replacements,
            'replacements' => $replacements,
            'checklists' => $checklists,
            'official_names' => $preventiveMaintenance->official_names,
            'official_position' => $preventiveMaintenance->official_position,
            'checklistsFromPreventive' => $checklistsFromPreventive,
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

        $validateData = $this->validateData($request, $preventiveMaintenance->device);

        if ($validateData->failed) {
            return response()->json(['errors' => ['message' => [$validateData->message]]], 422);
        }

        //Verificando que esté programado el preventivo para la fecha dada
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

        if ($scheduleMaintenanceDetail->made && $scheduleMaintenanceDetail->preventive_maintenance_id != $preventiveMaintenance->id) {
            return response()->json(['errors' => ['message' => ['El dispositivo ya tiene registrado el mantenimiento preventivo para la fecha dada']]], 422);
        }

        //Verificando que solo pueda actualizar el que haya registrado el preventivo
        if (auth()->user()->id != $preventiveMaintenance->user_id) {
            abort(403);
        }

        //Verificando si el reporte es digital o físico
        if ($preventiveMaintenance->type == 'Digital') {

            \DB::beginTransaction();
            $pathsFromImagesSaved = [];

            try {
                //Actualizando el preventivo digital
                $preventiveMaintenance->update([
                    'date_of_realization' => $request->date_of_realization,
                    'date_of_realization_ending' => $request->date_of_realization_ending,
                    'reason' => $request->reason,
                    'support_description' => $request->support_description,
                    'evidence' => null,
                    'location_id' => $preventiveMaintenance->device->location_id,
                    'dependence_id' => $preventiveMaintenance->device->dependence_id,
                    'official_names' => $request->official_names,
                    'official_position' => $request->official_position,
                    'user_id' => auth()->user()->id,
                    'signature' => $request->signature
                ]);
    
                //Eliminando los repuestos anteriores y registrando los nuevos
                $preventiveMaintenance->replacements()->delete();
    
                $replacements = json_decode($request->replacementsInJson, true);
                $preventiveMaintenance->replacements()->createMany($replacements);
    
                //Eliminando listas de chequeo anteriores y registrando las nuevas
                $preventiveMaintenance->checklists->each(function ($checklist) {
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
                    $preventiveMaintenanceChecklist = $preventiveMaintenance->checklists()->create(['name' => $checklist->name]);
                    foreach ($checklist->checklist_groups as $checklist_group) {
                        $preventiveMaintenanceChecklistGroup = $preventiveMaintenanceChecklist->checklistGroups()->create(['name' => $checklist_group->name]);
                        foreach ($checklist_group->checklist_items as $checklist_item) {
                            $preventiveMaintenanceChecklistGroup->checklistItems()->create([
                                'description' => $checklist_item->description,
                                'response' => $checklist_item->response
                            ]);
    
                        }
                    }
                }
    
                //Elminando anexos fotográficos anteriores y registrando los nuevos        
                $pathsFromImagesToDelete = [];

                foreach ($preventiveMaintenance->photographicAnnexes as $photographicAnnexe) {
                    if (Storage::exists($photographicAnnexe->image)) {
                        $pathsFromImagesToDelete[] = $photographicAnnexe->image;
                    }
                }

                $preventiveMaintenance->photographicAnnexes()->delete();
    
                if ($request->photographicAnnexes) {
                    foreach ($request->photographicAnnexes as $photographicAnnexe) {
                        $imagePath = Storage::putFile('photographic-annexes', $photographicAnnexe['image']);
                        $pathsFromImagesSaved[] = $imagePath;
                        $preventiveMaintenance->photographicAnnexes()->create([
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

                //Actualizando en la programación el estado y la relación del mantenimiento preventivo realizado
                $preventiveMaintenance->scheduleMaintenanceDetail->update([
                    'made' => false,
                    'preventive_maintenance_id' => null,
                ]);

                $scheduleMaintenanceDetail->made = true;
                $scheduleMaintenanceDetail->preventive_maintenance_id = $preventiveMaintenance->id;
                $scheduleMaintenanceDetail->save();

                \DB::commit();

            } catch (\Exception $e) {
                \DB::rollback();
                foreach ($pathsFromImagesSaved as $pathFromImagesSaved) {
                    if (Storage::exists($pathFromImagesSaved)) {
                        Storage::delete($pathFromImagesSaved);
                    }
                }
                return response()->json(['errors' => ['message' => ['El mantenimiento no se pudo actualizar:' . $e->getMessage()]]], 422);
            }

        } else {
            \DB::beginTransaction();

            try {
                //Almacenando evidencia PDF
                $pathEvidence = Storage::putFile('preventive-maintenances', $request->evidence);

                $pathEvidencePrevious = $preventiveMaintenance->evidence;

                //Actualizando el preventivo físico
                $preventiveMaintenance->update([
                    'date_of_realization' => $request->date_of_realization,
                    'date_of_realization_ending' => $request->date_of_realization_ending,
                    'evidence' => $pathEvidence,
                    'location_id' => $preventiveMaintenance->device->location_id,
                    'dependence_id' => $preventiveMaintenance->device->dependence_id
                ]);

                //Eliminando los repuestos anteriores y registrando los nuevos
                $preventiveMaintenance->replacements()->delete();

                $replacements = json_decode($request->replacementsInJson, true);
                $preventiveMaintenance->replacements()->createMany($replacements);

                //Elminando el reporte físico anterior
                if (Storage::exists($pathEvidencePrevious)) {
                    Storage::delete($pathEvidencePrevious);
                }

                \DB::commit();

            } catch (\Exception $e) {
                \DB::rollback();
                if (Storage::exists($pathEvidence)) {
                    Storage::delete($pathEvidence);
                }
                return response()->json(['errors' => ['message' => ['El mantenimiento preventivo no se pudo actualizar:' . $e->getMessage()]]], 422);
            }
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

            \DB::beginTransaction();
            try {
                //Verificando que el cronograma en el que se subió el preventivo no esté finalizado
                if ($preventiveMaintenance->scheduleMaintenanceDetail->scheduleMaintenance->finished) {
                    return response()->json(['errors' => ['message' => ['El cronograma ya se encuentra finalizado']]], 422);
                }
    
                //Eliminando la relación el detalle del cronograma
                if ($preventiveMaintenance->scheduleMaintenanceDetail) {
                    $preventiveMaintenance->scheduleMaintenanceDetail()->update([
                        'made' => false,
                        'preventive_maintenance_id' => null
                    ]);
                }
    
                //Eliminando repuestos
                $preventiveMaintenance->replacements()->delete();

                //Eliminando listas de chequeo
                $preventiveMaintenance->checklists->each(function ($checklist) {
                    $checklist->checklistGroups->each(function ($group) {
                        $group->checklistItems->each(function ($item) {
                            $item->delete();
                        });
                        $group->delete();
                    });
                    $checklist->delete();
                });
    
                //Eliminando anexos fotográficos
                $photographicAnnexes = $preventiveMaintenance->photographicAnnexes;
                
                $preventiveMaintenance->photographicAnnexes()->delete();

                //Eliminando el archivo de evidencia pdf generado
                if (Storage::exists($preventiveMaintenance->evidence)) {
                    Storage::delete($preventiveMaintenance->evidence);
                }

                //Eliminando mantenimiento preventivo   
                $preventiveMaintenance->delete();
    
                foreach ($photographicAnnexes as $photographicAnnexe) {
                    if (Storage::exists($photographicAnnexe->image)) {
                        Storage::delete($photographicAnnexe->image);
                    }
                }

                \DB::commit();

            } catch (\Exception $e) {
                \DB::rollback();
                return response()->json(['errors' => ['message' => ['El mantenimiento preventivo no se pudo eliminar:' . $e->getMessage()]]], 422);
            }
        } else {
            return abort(403);
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

    public function getEvidenceFromPreventiveMaintenance (PreventiveMaintenance $preventiveMaintenance) {
        if ($preventiveMaintenance->type == 'Digital') {
            
            //Generando archivo temporal de especificaciónes técnicas
            $company = Company::first();
            $pdf = \PDF::loadView('resume.general-maintenances-preventives-and-others', [
                                                                                            'device' => $preventiveMaintenance->device, 
                                                                                            'maintenance' => $preventiveMaintenance, 
                                                                                            'company' => $company])
                                                                                            ->setPaper('letter');
            return $pdf->download();

        } else {
            
            return Storage::download($preventiveMaintenance->evidence);

        }
    }

}
