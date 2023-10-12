<?php

namespace App\Exports;

use App\Models\OtherMaintenance;
use App\Models\PreventiveMaintenance;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;

class MaintenancesExport implements FromView
{
    public function __construct($initial_date, $final_date, $user_selected, $type_maintenance, $group)
    {
        $this->initial_date = $initial_date;
        $this->final_date = $final_date;
        $this->user_selected = $user_selected;
        $this->type_maintenance = $type_maintenance;
        $this->group = $group;
    }

    public function view(): View
    {
        if ($this->type_maintenance == 1) {
           $preventiveMaintenances = PreventiveMaintenance::whereBetween('date_of_realization_ending', [ 
                                        $this->initial_date, $this->final_date
                                ])
                                ->when($this->user_selected, function ($query, $user_selected) {
                                    return $query->where('user_id', $user_selected);
                                })
                                ->whereHas('device.group', function (Builder $query) {
                                    $query->where('id', $this->group);
                                })
                                ->with('user:id,names')
                                ->with('device.element')
                                ->with('device.group')
                                ->with('location')
                                ->with('dependence')
                                ->with('replacements.replacement')
                                ->orderBy('date_of_realization_ending', 'desc')
                                ->get();

            return view('reports.preventive-maintenances', [ 'preventiveMaintenances' => $preventiveMaintenances ]);                                

        } else if ($this->type_maintenance == 2) {
            $otherMaintenances = OtherMaintenance::whereBetween('date_of_realization_ending', [
                                        $this->initial_date, $this->final_date
                                ])
                                ->when($this->user_selected, function ($query, $user_selected) {
                                    return $query->where('user_id', $user_selected);
                                })
                                ->whereHas('device.group', function (Builder $query) {
                                    $query->where('id', $this->group);
                                })
                                ->with('user:id,names')
                                ->whereHas('device')
                                ->with('device.group')
                                ->with('device')
                                ->with('location')
                                ->with('dependence')
                                ->with('replacements.replacement')
                                ->with('typeOtherMaintenance')
                                ->with('failureMode')
                                ->orderBy('date_of_realization_ending', 'desc')
                                ->get();
                                
            return view('reports.other-maintenances', [ 'otherMaintenances' => $otherMaintenances ]);
        } else {
            $maintenances = OtherMaintenance::whereBetween('date_of_realization_ending', [
                                $this->initial_date, $this->final_date
                        ])
                        ->when($this->user_selected, function ($query, $user_selected) {
                            return $query->where('user_id', $user_selected);
                        })
                        ->with('user:id,names')
                        ->whereDoesntHave('device')
                        ->where('group_id', $this->group)
                        ->with('location')
                        ->with('dependence')
                        ->with('replacements.replacement')
                        ->with('typeOtherMaintenance')
                        ->orderBy('date_of_realization_ending', 'desc')
                        ->get();
                        
                return view('reports.maintenances', [ 'maintenances' => $maintenances ]);
        }
    }
}