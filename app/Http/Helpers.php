<?php

use App\Models\Period;
use App\Models\TypeOtherMaintenance;
use App\Models\Replacement;
use App\Models\Company;
use App\Models\Location;
use App\Models\Dependence;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

    function periodIsActive ($date_of_realization_ending) {
        return Period::where('start', '<=', $date_of_realization_ending)
                        //->where('end', '>=', $date_of_realization_ending)
                        ->where('end', '>=', Carbon::parse($date_of_realization_ending)->toDateString())
                        ->where('active', true)
                        ->count();

    }

    function replacementsHasErrors ($replacements) {
        $rules = [
            '*.price' => 'required|numeric|min:0|max:1000000000',
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
            'date_of_realization_ending' => $request->date_of_realization_ending,
            'typeMaintenance' => $typeMaintenance,
            'reason' => $request->reason,
            'support_description' => $request->support_description,
            'replacementsFromReport' => $replacementsFromReport,
            'device' => $device,
            'photographicAnnexes' => $request->photographicAnnexes ? array_chunk($request->photographicAnnexes, 2) : [],
            'signature' => $request->signature,
            'official_names' => $request->official_names,
            'official_position' => $request->official_position,
            'location' => $request->location_id ? $location : '',
            'dependence' => $request->dependence_id ? $dependence : ''
        ];
    }

    function getDataForReportDigitalToPreventiveMaintenance($request, $replacements, $checklists, $device = null) {
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
            'date_of_realization_ending' => $request->date_of_realization_ending,
            'typeMaintenance' => null, //Se envía null para validar en blade y como no tiene clasificación se pone Mantenimiento preventivo
            'replacementsFromReport' => $replacementsFromReport,
            'checklists' => $checklists,
            'reason' => $reason,
            'support_description' => $request->support_description,
            'photographicAnnexes' => $request->photographicAnnexes ? array_chunk($request->photographicAnnexes, 2) : [],
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
