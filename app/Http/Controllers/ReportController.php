<?php

namespace App\Http\Controllers;

use App\Models\ReplacementMaintenance;
use App\Models\OtherMaintenance;
use App\Models\User;
use App\Models\Group;
use App\Models\PreventiveMaintenance;
use Illuminate\Http\Request;
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
        return ReplacementMaintenance::whereHas('replacementMaintenancable', function (Builder $query) {
                                        $query->whereBetween('date_of_realization', [request()->initial_date, request()->final_date]);
                                    })
                                    ->orderBy('created_at', 'desc')
                                    ->with('replacementMaintenancable.user:id,names')
                                    ->with('replacementMaintenancable.device')
                                    ->with('replacement:id,name')
                                    ->select('id', 'replacement_id', 'quantity', 'replacement_maintenancable_id', 'replacement_maintenancable_type', 'created_at')
                                    ->paginate(10);
    }

    public function replacementsUsedExportExcel (Request $request) {
        
        return \Excel::download(new \App\Exports\ReplacementsUsedExport($request->initial_date, $request->final_date), 'invoices.xlsx');

    }

    public function maintenances (Request $request) {

        if ($request->type_maintenance != 3) {
            //Verificando que el usuario esté autorizado en el grupo seleccionado
            $hasPermissionInGroup = auth()->user()->groups()->where('groups.id', request('group'))->first();
    
            if (!$hasPermissionInGroup) {
                return response()->json(['errors' => ['message' => ['El usuario no está autorizado en el grupo seleccionado']]], 422);
            } 
        }

        if ($request->type_maintenance == 1) {
            return PreventiveMaintenance::whereBetween('date_of_realization', [ 
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
                                            ->with('replacementMaintenancables.replacement')
                                            ->orderBy('date_of_realization', 'desc')
                                            ->paginate(10);

        } else if ($request->type_maintenance == 2) {
            return OtherMaintenance::whereBetween('date_of_realization', [ 
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
                                                  ->with('replacementMaintenancables.replacement')
                                                  ->orderBy('date_of_realization', 'desc')
                                                  ->paginate(10);
        } else {
            return OtherMaintenance::whereBetween('date_of_realization', [ 
                                                $request->initial_date, $request->final_date
                                            ])
                                            ->when($request->user_selected, function ($query, $userSelected) {
                                                return $query->where('user_id', $userSelected);
                                            })
                                            ->whereDoesntHave('device')
                                            ->with('user:id,names')
                                            ->with('location')
                                            ->with('dependence')
                                            ->with('typeOtherMaintenance')
                                            ->with('replacementMaintenancables.replacement')
                                            ->orderBy('date_of_realization', 'desc')
                                            ->paginate(10);
        } 

    }
    public function maintenancesExportPdf (Request $request) {

        ini_set('magic_quotes_runtime', 0);

        if ($request->type_maintenance != 3) {
            //Verificando que el usuario esté autorizado en el grupo seleccionado
            $hasPermissionInGroup = auth()->user()->groups()->where('groups.id', request('group'))->first();
    
            if (!$hasPermissionInGroup) {
                return response()->json(['errors' => ['message' => ['El usuario no está autorizado en el grupo seleccionado']]], 422);
            } 
        }

        $pdf = new \PDFMerger();
        $maintenances;

        if ($request->type_maintenance == 1) {
            $maintenances = PreventiveMaintenance::whereBetween('date_of_realization', [ 
                                        $request->initial_date, $request->final_date
                                ])->orderBy('date_of_realization', 'desc')
                                ->when($request->user_selected, function ($query, $userSelected) {
                                    return $query->where('user_id', $userSelected);
                                })
                                ->whereHas('device.group', function (Builder $query) use ($request) {
                                    $query->where('id', $request->group);
                                })
                                ->get();

        } else if ($request->type_maintenance == 2) {
            $maintenances = OtherMaintenance::whereBetween('date_of_realization', [ 
                                        $request->initial_date, $request->final_date
                                ])
                                ->when($request->user_selected, function ($query, $userSelected) {
                                    return $query->where('user_id', $userSelected);
                                })
                                ->whereHas('device.group', function (Builder $query) use ($request) {
                                    $query->where('id', $request->group);
                                })
                                ->orderBy('date_of_realization', 'desc')
                                ->get();
        } else {
            $maintenances = OtherMaintenance::whereBetween('date_of_realization', [ 
                                        $request->initial_date, $request->final_date
                                ])
                                ->whereDoesntHave('device')
                                ->when($request->user_selected, function ($query, $userSelected) {
                                    return $query->where('user_id', $userSelected);
                                })
                                ->orderBy('date_of_realization', 'desc')
                                ->get();
        }

        foreach ($maintenances as $key => $value) {
            $pdf->addPDF(public_path() . '\\' . $value->evidence, 'all');
        }

        $binaryContent = $pdf->merge('string', "mergedpdf.pdf");

        return response($binaryContent)->header('Content-type' , 'application/pdf');

    }

    public function maintenancesExportExcel (Request $request) {

        if ($request->type_maintenance != 3) {
            //Verificando que el usuario esté autorizado en el grupo seleccionado
            $hasPermissionInGroup = auth()->user()->groups()->where('groups.id', request('group'))->first();
    
            if (!$hasPermissionInGroup) {
                return response()->json(['errors' => ['message' => ['El usuario no está autorizado en el grupo seleccionado']]], 422);
            }
        }
        
        return \Excel::download(new \App\Exports\MaintenancesExport($request->initial_date, $request->final_date, $request->user_selected, $request->type_maintenance, $request->group), 'invoices.xlsx');

    }
}
