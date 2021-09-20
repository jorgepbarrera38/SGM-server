<?php

use App\Models\Period;
use App\Models\TypeOtherMaintenance;
use App\Models\Replacement;
use App\Models\Company;
use App\Models\Location;
use App\Models\Dependence;
use Illuminate\Support\Facades\Storage;

    function periodIsActive ($date_of_realization) {
        return Period::where('start', '<=', $date_of_realization)->
                        where('end', '>=', $date_of_realization)
                        ->where('active', true)
                        ->count();
        
    }

    function replacementsHasErrors ($replacements) {
        $rules = [ 
            '*.quantity' => 'required|numeric|min:1|max:100',
            '*.replacement_id' => 'required|exists:replacements,id',
        ];
        
        $validator = \Validator::make($replacements, $rules);

        if (!$validator->passes()) {
            return $validator->errors()->all();
        }
        return false;
    }
    
    function getDataForReportDigitalToGeneralMaintenancesAndOthers($request, $replacements, $device = null) {
        $typeMaintenance = TypeOtherMaintenance::findOrFail($request->type_maintenance_id);
        $companyLogo = Company::first()->logo;

        if ($request->location_id) {
            $location = Location::find($request->location_id);
            $dependence = Dependence::find($request->dependence_id);
        }


        $replacementsFromReport = array_map(function ($idFromReplacement) {
            $replacement = Replacement::findOrFail($idFromReplacement['replacement_id']);
            return [
                'name' => $replacement->name,
                'quantity' => $idFromReplacement['quantity']
            ];
        }, $replacements);

        return [
            'companyLogo' => $companyLogo,
            'date_of_realization' => $request->date_of_realization,
            'typeMaintenance' => $typeMaintenance, 
            'reason' => $request->reason,
            'conclusion' => $request->conclusion,
            'replacementsFromReport' => $replacementsFromReport,
            'device' => $device,
            'signature' => $request->signature,
            'official_names' => $request->official_names,
            'official_position' => $request->official_position,
            'location' => $request->location_id ? $location : '',
            'dependence' => $request->dependence_id ? $dependence : ''
        ];
    }

    function getDataForReportDigitalToPreventiveMaintenance($request, $replacements, $device = null) {
        $companyLogo = Company::first()->logo;
        $reason = Company::first()->reason_preventive_maintenance_report_digital;

        $replacementsFromReport = array_map(function ($idFromReplacement) {
            $replacement = Replacement::findOrFail($idFromReplacement['replacement_id']);
            return [
                'name' => $replacement->name,
                'quantity' => $idFromReplacement['quantity']
            ];
        }, $replacements);

        return [
            'companyLogo' => $companyLogo,
            'date_of_realization' => $request->date_of_realization,
            'typeMaintenance' => null, //Se envía null para validar en blade y como no tiene clasificación se pone Mantenimiento preventivo
            'replacementsFromReport' => $replacementsFromReport,
            'reason' => $reason,
            'conclusion' => $request->conclusion,
            'device' => $device,
            'signature' => $request->signature,
            'official_names' => $request->official_names,
            'official_position' => $request->official_position
        ];
    }

    function companyHasLogo() {
        if ($company = Company::first()) {
            return $company->logo && Storage::exists($company->logo);
        } else {
            return false;
        }
    }

    function getMonth ($monthNumber) {
        $months = [
            'Enero',
            'Febrero',
            'Marzo',
            'Abril',
            'Mayo',
            'Junio',
            'Julio',
            'Agosto',
            'Septiembre',
            'Octubre',
            'Noviembre',
            'Diciembre'
        ];

        return $months[$monthNumber-1];
    }