<?php

namespace App\Exports;

use App\Models\ScheduleMaintenance;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ScheduleExport implements FromView
{
    public function __construct($scheduleMaintenance_id)
    {
        $this->scheduleMaintenance_id = $scheduleMaintenance_id;
    }

    public function view(): View
    {
        $scheduleMaintenance = ScheduleMaintenance::where('id', $this->scheduleMaintenance_id)
                                    ->with('scheduleMaintenanceDetails.preventiveMaintenance:id,evidence')
                                    ->with('scheduleMaintenanceDetails.device.location:id,name')
                                    ->with('scheduleMaintenanceDetails.device.element:id,name')
                                    ->with('scheduleMaintenanceDetails.device.dependence:id,name')
                                    ->with('scheduleMaintenanceDetails.device:id,description,internal_code,serial_number,location_id,element_id,dependence_id,description_support_preventive_maintenance')
                                    ->with('scheduleMaintenanceDetails:id,made,date,preventive_maintenance_id,schedule_maintenance_id,device_id')
                                    ->with(['scheduleMaintenanceDetails' => function ($query) {
                                        $query->orderBy('date');
                                    }])
                                    ->firstOrFail(['id', 'name']);                                    

            return view('schedule-reports.schedule-details', [ 'scheduleMaintenance' => $scheduleMaintenance ]);
    }
}