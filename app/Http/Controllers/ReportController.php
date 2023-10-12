<?php

namespace App\Http\Controllers;

use LynX39\LaraPdfMerger\Facades\PdfMerger;
use App\Models\ReplacementMaintenance;
use App\Models\OtherMaintenance;
use App\Models\User;
use App\Models\Group;
use App\Models\Company;
use App\Models\PreventiveMaintenance;
use App\Models\PreventiveMaintenanceReplacement;
use App\Models\OtherMaintenanceReplacement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;

class ReportController extends Controller
{
    public function __construct () {
        $this->middleware('has.permission:reports.maintenances')->only(['maintenances', 'maintenancesExportPdf', 'maintenancesExportExcel']);
    }

    public function getUsersTechnicalSupportAndGroups () {
        $users = User::where('technical_support', true)->orderBy('names')->get(['id', 'names']);
        $groups = Group::orderBy('name', 'asc')->get(['id', 'name']);

        return [
            'users' => $users,
            'groups'  => $groups,
        ];
    }

    public function replacementsUsed () {
        
        //request()->group

        $preventiveMaintenances = \DB::table('preventive_maintenance_replacements')
                                ->join('preventive_maintenances', 'preventive_maintenance_replacements.prev_maintenance_id', '=', 'preventive_maintenances.id')
                                ->join('devices', 'preventive_maintenances.device_id', '=', 'devices.id')
                                ->join('replacements', 'preventive_maintenance_replacements.replacement_id', '=', 'replacements.id')
                                ->where('devices.group_id', request()->group)
                                ->whereBetween('preventive_maintenances.date_of_realization_ending', [request()->initial_date, request()->final_date])
                                ->select('preventive_maintenance_replacements.quantity as replacement_quantity', 
                                         'replacements.name as replacement_name',
                                         'preventive_maintenances.id as maintenance_id',                                    
                                         'preventive_maintenances.date_of_realization as maintenance_date_of_realization',                                    
                                         'preventive_maintenances.date_of_realization_ending as maintenance_date_of_realization_ending',
                                         \DB::raw('"preventive_maintenance" as type')
                                );                                

        $otherMaintenances = \DB::table('other_maintenance_replacements')
                                ->join('other_maintenances', 'other_maintenance_replacements.other_maintenance_id', '=', 'other_maintenances.id')
                                ->join('replacements', 'other_maintenance_replacements.replacement_id', '=', 'replacements.id')
                                ->join('devices', 'other_maintenances.device_id', '=', 'devices.id')
                                ->where('other_maintenances.device_id', '<>', null)
                                ->where('devices.group_id', request()->group)
                                ->whereBetween('other_maintenances.date_of_realization_ending', [request()->initial_date, request()->final_date])
                                ->select('other_maintenance_replacements.quantity as replacement_quantity', 
                                         'replacements.name as replacement_name',
                                         'other_maintenances.id as maintenance_id',                                    
                                         'other_maintenances.date_of_realization as maintenance_date_of_realization',                                    
                                         'other_maintenances.date_of_realization_ending as maintenance_date_of_realization_ending',
                                         \DB::raw('"other_maintenance" as type')
                            );

        return \DB::table('other_maintenance_replacements')
                                ->join('other_maintenances', 'other_maintenance_replacements.other_maintenance_id', '=', 'other_maintenances.id')
                                ->join('replacements', 'other_maintenance_replacements.replacement_id', '=', 'replacements.id')
                                ->where('other_maintenances.device_id', '=', null)
                                ->where('other_maintenances.group_id', request()->group)
                                ->whereBetween('other_maintenances.date_of_realization_ending', [request()->initial_date, request()->final_date])
                                ->select('other_maintenance_replacements.quantity as replacement_quantity', 
                                            'replacements.name as replacement_name',
                                            'other_maintenances.id as maintenance_id',                                    
                                            'other_maintenances.date_of_realization as maintenance_date_of_realization',                                    
                                            'other_maintenances.date_of_realization_ending as maintenance_date_of_realization_ending',
                                            \DB::raw('"general_maintenance" as type')
                                         )
                                ->unionAll($preventiveMaintenances)
                                ->unionAll($otherMaintenances)
                                ->orderBy('maintenance_date_of_realization_ending', 'desc')
                                ->paginate(10);

        /*return PreventiveMaintenanceReplacement::with('replacement:id,name', 'preventiveMaintenance:id,date_of_realization,date_of_realization_ending')
                                                ->get(['id', 'prev_maintenance_id', 'replacement_id', 'quantity']);


        return ReplacementMaintenance::whereHas('replacementMaintenancable', function (Builder $query) {
                                        $query->whereBetween('date_of_realization_ending', [request()->initial_date, request()->final_date]);
                                    })
                                    ->orderBy('created_at', 'desc')
                                    ->with('replacementMaintenancable.user:id,names')
                                    ->with('replacementMaintenancable.device')
                                    ->with('replacement:id,name')
                                    ->select('id', 'replacement_id', 'quantity', 'replacement_maintenancable_id', 'replacement_maintenancable_type', 'created_at')
                                    ->paginate(10);*/
    }

    public function replacementsUsedExportExcel (Request $request) {
        
        return \Excel::download(new \App\Exports\ReplacementsUsedExport($request->initial_date, $request->final_date, $request->group), 'invoices.xlsx');

    }

    public function maintenances (Request $request) {
        /*if ($request->type_maintenance != 3) {
            //Verificando que el usuario esté autorizado en el grupo seleccionado
            $hasPermissionInGroup = auth()->user()->groups()->where('groups.id', request('group'))->first();
    
            if (!$hasPermissionInGroup) {
                return response()->json(['errors' => ['message' => ['El usuario no está autorizado en el grupo seleccionado']]], 422);
            } 
        }*/

        if ($request->type_maintenance == 1) {
            return PreventiveMaintenance::whereBetween('date_of_realization_ending', [ 
                                                        $request->initial_date, $request->final_date
                                            ])
                                            ->when($request->user_selected, function ($query, $userSelected) {
                                                return $query->where('user_id', $userSelected);
                                            })
                                            ->whereHas('device.group', function (Builder $query) use ($request) {
                                                $query->where('id', $request->group);
                                            })
                                            ->with('user:id,names')
                                            ->with('device.element')
                                            ->with('device.group')
                                            ->with('location')
                                            ->with('dependence')
                                            ->with('replacements.replacement')
                                            ->orderBy('date_of_realization_ending', 'desc')
                                            ->paginate(10);

        } else if ($request->type_maintenance == 2) {
            return OtherMaintenance::whereBetween('date_of_realization_ending', [ 
                                                        $request->initial_date, $request->final_date
                                                  ])
                                                  ->when($request->user_selected, function ($query, $userSelected) {
                                                    return $query->where('user_id', $userSelected);
                                                   })
                                                   ->whereHas('device.group', function (Builder $query) use ($request) {
                                                        $query->where('id', $request->group);
                                                    })
                                                  ->with('device.group')
                                                  ->whereHas('device')
                                                  ->with('user:id,names')
                                                  ->with('device.element')
                                                  ->with('location')
                                                  ->with('dependence')
                                                  ->with('typeOtherMaintenance')
                                                  ->with('failureMode')
                                                  ->with('replacements.replacement')
                                                  ->orderBy('date_of_realization_ending', 'desc')
                                                  ->paginate(10);
        } else {
            return OtherMaintenance::whereBetween('date_of_realization_ending', [ 
                                                $request->initial_date, $request->final_date
                                            ])
                                            ->when($request->user_selected, function ($query, $userSelected) {
                                                return $query->where('user_id', $userSelected);
                                            })
                                            ->whereDoesntHave('device')
                                            ->where('group_id', $request->group)
                                            ->with('user:id,names')
                                            ->with('location')
                                            ->with('dependence')
                                            ->with('typeOtherMaintenance')
                                            ->with('failureMode')
                                            ->with('replacements.replacement')
                                            ->orderBy('date_of_realization_ending', 'desc')
                                            ->paginate(10);
        } 

    }
    public function maintenancesExportPdf (Request $request) {

        /*if ($request->type_maintenance != 3) {
            //Verificando que el usuario esté autorizado en el grupo seleccionado
            $hasPermissionInGroup = auth()->user()->groups()->where('groups.id', request('group'))->first();
    
            if (!$hasPermissionInGroup) {
                return response()->json(['errors' => ['message' => ['El usuario no está autorizado en el grupo seleccionado']]], 422);
            } 
        }*/

        $pdfMerger = PdfMerger::init(); //Initialize the merger
        $maintenances;
        $tempsFilesToDelete = [];
        $pathFileTemp;

        if ($request->type_maintenance == 1) {
            $pathFileTemp = '/temps/preventive-temp';
            $maintenances = PreventiveMaintenance::whereBetween('date_of_realization_ending', [ 
                                        $request->initial_date, $request->final_date
                                ])->orderBy('date_of_realization_ending', 'desc')
                                ->when($request->user_selected, function ($query, $userSelected) {
                                    return $query->where('user_id', $userSelected);
                                })
                                ->whereHas('device.group', function (Builder $query) use ($request) {
                                    $query->where('id', $request->group);
                                })
                                ->get();

        } else if ($request->type_maintenance == 2) {
            $pathFileTemp = '/temps/preventive-temp';
            $maintenances = OtherMaintenance::whereBetween('date_of_realization_ending', [ 
                                        $request->initial_date, $request->final_date
                                ])
                                ->when($request->user_selected, function ($query, $userSelected) {
                                    return $query->where('user_id', $userSelected);
                                })
                                ->whereHas('device.group', function (Builder $query) use ($request) {
                                    $query->where('id', $request->group);
                                })
                                ->orderBy('date_of_realization_ending', 'desc')
                                ->get();
        } else {
            $pathFileTemp = '/temps/general-maintenace-temp';
            $maintenances = OtherMaintenance::whereBetween('date_of_realization_ending', [ 
                                        $request->initial_date, $request->final_date
                                ])
                                ->whereDoesntHave('device')
                                ->when($request->user_selected, function ($query, $userSelected) {
                                    return $query->where('user_id', $userSelected);
                                })
                                ->where('group_id', $request->group)
                                ->orderBy('date_of_realization_ending', 'desc')
                                ->get();
        }

        $company = Company::first();

        
        foreach ($maintenances as $key => $maintenance) {
            
            //Si es digital arma el pdf con dompdf
            if ($maintenance->type == 'Digital') {

                if ($maintenance->device_id == null) {
                    $pdf = \PDF::loadView('reports.maintenances-in-pdf', ['maintenance' => $maintenance, 'company' => $company])->setPaper('letter');
                } else {
                    $pdf = \PDF::loadView('resume.general-maintenances-preventives-and-others', ['device' => $maintenance->device, 'maintenance' => $maintenance, 'company' => $company])->setPaper('letter');
                }
                

                //Guardando el temporal del preventivo
                $pathPreventiveTemp = $pathFileTemp . $key . '.pdf';
                Storage::put($pathPreventiveTemp, $pdf->output());

                //Anexándolo al pdf final
                $pdfMerger->addPDF(public_path() . '/files/' . $pathPreventiveTemp, 'all');
                
                $tempsFilesToDelete[] = $pathPreventiveTemp;

            } else {
                //Si no es digital llama el documento que se adjuntó como evidencia del preventivo
                $pdfMerger->addPDF(public_path() . '/files/' . $preventiveMaintenance->evidence, 'all');

            }
        }

        //Uniendo todas las hojas
        $pdfMerger->merge();

        //Guardando el archivo final temporal
        $pdfMerger->save(public_path() . '/files/temps/maintenances-temp.pdf', 'file');

        //Elminando archivos temporales generados
        foreach ($tempsFilesToDelete as $tempFileToDelete) {
            Storage::delete($tempFileToDelete);
        }

        return response()->file(public_path() . '/files/temps/maintenances-temp.pdf');
    }

    public function maintenancesExportExcel (Request $request) {

        /*if ($request->type_maintenance != 3) {
            //Verificando que el usuario esté autorizado en el grupo seleccionado
            $hasPermissionInGroup = auth()->user()->groups()->where('groups.id', request('group'))->first();
    
            if (!$hasPermissionInGroup) {
                return response()->json(['errors' => ['message' => ['El usuario no está autorizado en el grupo seleccionado']]], 422);
            }
        }*/
        
        return \Excel::download(new \App\Exports\MaintenancesExport($request->initial_date, $request->final_date, $request->user_selected, $request->type_maintenance, $request->group), 'invoices.xlsx');

    }
}
