<?php

namespace App\Exports;

use App\Models\ReplacementMaintenance;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;

class ReplacementsUsedExport implements FromView
{
    public function __construct($initial_date, $final_date)
    {
        $this->initial_date = $initial_date;
        $this->final_date = $final_date;
    }

    public function view(): View
    {
           $replacementsUsed = ReplacementMaintenance::whereHas('replacementMaintenancable', function (Builder $query) {
                                            $query->whereBetween('date_of_realization_ending', [$this->initial_date, $this->final_date]);
                                        })
                                        ->orderBy('created_at', 'desc')
                                        ->with('replacementMaintenancable.user:id,names')
                                        ->with('replacementMaintenancable.device')
                                        //->with('replacementMaintenancable.typeOtherMaintenance')
                                        ->with('replacement:id,name')
                                        ->select('id', 'replacement_id', 'quantity', 'replacement_maintenancable_id', 'replacement_maintenancable_type', 'created_at')
                                        ->get();

            return view('reports.replacements-used-excel', [ 'replacementsUsed' => $replacementsUsed ]);                                

    }
}