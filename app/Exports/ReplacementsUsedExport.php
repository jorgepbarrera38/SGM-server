<?php

namespace App\Exports;

use App\Models\ReplacementMaintenance;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;

class ReplacementsUsedExport implements FromView
{
    public function __construct($initial_date, $final_date, $group)
    {
        $this->initial_date = $initial_date;
        $this->final_date = $final_date;
        $this->group = $group;
    }

    public function view(): View
    {
        $preventiveMaintenances = \DB::table('preventive_maintenance_replacements')
                            ->join('preventive_maintenances', 'preventive_maintenance_replacements.prev_maintenance_id', '=', 'preventive_maintenances.id')
                            ->join('devices', 'preventive_maintenances.device_id', '=', 'devices.id')
                            ->join('elements', 'devices.element_id', '=', 'elements.id')
                            ->join('replacements', 'preventive_maintenance_replacements.replacement_id', '=', 'replacements.id')
                            ->where('devices.group_id', $this->group)
                            ->whereBetween('preventive_maintenances.date_of_realization_ending', [$this->initial_date, $this->final_date])
                            ->select('preventive_maintenance_replacements.quantity as replacement_quantity', 
                                    'replacements.name as replacement_name',
                                    'preventive_maintenances.id as maintenance_id',                                    
                                    'preventive_maintenances.date_of_realization as maintenance_date_of_realization',                                    
                                    'preventive_maintenances.date_of_realization_ending as maintenance_date_of_realization_ending',
                                    'elements.name as element_name',
                                    'devices.internal_code as device_internal_code',
                                    'devices.serial_number as device_serial_number',
                                    'preventive_maintenances.reason as preventive_maintenance_reason',
                                    'preventive_maintenances.support_description as preventive_maintenance_support_description',
                                    \DB::raw('"preventive_maintenance" as type')
                            );                                

        $otherMaintenances = \DB::table('other_maintenance_replacements')
                                ->join('other_maintenances', 'other_maintenance_replacements.other_maintenance_id', '=', 'other_maintenances.id')
                                ->join('replacements', 'other_maintenance_replacements.replacement_id', '=', 'replacements.id')
                                ->join('devices', 'other_maintenances.device_id', '=', 'devices.id')
                                ->join('elements', 'devices.element_id', '=', 'elements.id')
                                ->where('other_maintenances.device_id', '<>', null)
                                ->where('devices.group_id', $this->group)
                                ->whereBetween('other_maintenances.date_of_realization_ending', [$this->initial_date, $this->final_date])
                                ->select('other_maintenance_replacements.quantity as replacement_quantity', 
                                        'replacements.name as replacement_name',
                                        'other_maintenances.id as maintenance_id',                                    
                                        'other_maintenances.date_of_realization as maintenance_date_of_realization',                                    
                                        'other_maintenances.date_of_realization_ending as maintenance_date_of_realization_ending',
                                        'elements.name as element_name',
                                        'devices.internal_code as device_internal_code',
                                        'devices.serial_number as device_serial_number',
                                        'other_maintenances.reason as preventive_maintenance_reason',
                                        'other_maintenances.support_description as preventive_maintenance_support_description',
                                        \DB::raw('"other_maintenance" as type')
                            );

        $replacementsUsed = \DB::table('other_maintenance_replacements')
                            ->join('other_maintenances', 'other_maintenance_replacements.other_maintenance_id', '=', 'other_maintenances.id')
                            ->join('replacements', 'other_maintenance_replacements.replacement_id', '=', 'replacements.id')
                            ->where('other_maintenances.device_id', '=', null)
                            ->where('other_maintenances.group_id', $this->group)
                            ->whereBetween('other_maintenances.date_of_realization_ending', [$this->initial_date, $this->final_date])
                            ->select('other_maintenance_replacements.quantity as replacement_quantity', 
                                        'replacements.name as replacement_name',
                                        'other_maintenances.id as maintenance_id',                                    
                                        'other_maintenances.date_of_realization as maintenance_date_of_realization',                                    
                                        'other_maintenances.date_of_realization_ending as maintenance_date_of_realization_ending',
                                        \DB::raw('"" as element_name'),
                                        \DB::raw('"" as device_internal_code'),
                                        \DB::raw('"" as device_serial_number'),
                                        \DB::raw('"" as preventive_maintenance_reason'),
                                        \DB::raw('"" as preventive_maintenance_support_description'),
                                        \DB::raw('"general_maintenance" as type')
                                    )
                            ->unionAll($preventiveMaintenances)
                            ->unionAll($otherMaintenances)
                            ->orderBy('maintenance_date_of_realization_ending', 'desc')
                            ->get();

                //\Log::info($replacementsUsed);

            return view('reports.replacements-used-excel', [ 'replacementsUsed' => $replacementsUsed ]);                                

    }
}