<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class GeneralMaintenanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'group_id' => 'required|exists:groups,id',
            'type_report' => ['required', Rule::in([1,2])],
            'date_of_realization' => 'required|date',
            'date_of_realization_ending' => 'required|date|after_or_equal:date_of_realization',
            'type_maintenance_id' => 'required|exists:type_other_maintenances,id',
            'reason' => 'required_if:type_report,1|nullable|max:600',
            'support_description' => 'required_if:type_report,1|nullable|max:10000',
            'failure_mode_id' => 'required|exists:failure_modes,id',
            'location_id' => [ Rule::requiredIf($this->route()->named('maintenances.store')), 'exists:locations,id'],
            'dependence_id' => [ Rule::requiredIf($this->route()->named('maintenances.store')), 'exists:dependences,id'],
            'evidence' => 'required_if:type_report,2|nullable|mimes:pdf|max:25000',
            'signature' => 'required_if:type_report,1|base64image',
            'official_names' => 'required_if:type_report,1|nullable|max:50',
            'official_position' => 'required_if:type_report,1|nullable|max:30'
        ];
    }

    public function messages () {
        return [
            'reason.required_if' => 'El campo Motivo es obligatorio cuando el campo Tipo es Digital.',
            'support_description.required_if' => 'El campo Descripción del soporte es obligatorio cuando el campo Tipo es Digital.',
            'evidence.required_if' => 'El campo Evidencia es obligatorio',
            'official_names.required_if' => 'El campo Funcionario es obligatorio',
            'official_position.required_if' => 'El campo Cargo es obligatorio',
            'date_of_realization_ending.after_or_equal' => 'La Fecha y hora fin de realización debe ser superior a la fecha y hora inicio de realización.'
        ];
    }

    public function attributes () {
        return [
            'group_id' => 'Grupo',
            'type_report' => 'Tipo',
            'date_of_realization' => 'Fecha y hora inicio de realización',
            'date_of_realization_ending' => 'Fecha y hora fin de realización',
            'type_maintenance_id' => 'Clasificación del servicio',
            'evidence' => 'Evidencia',
            'official_names' => 'Funcionario',
            'official_position' => 'Cargo',
            'support_description' => 'Descripción del soporte',
            'failure_mode_id' => 'Modo de falla',
            'location_id' => 'Ubicación',
            'dependence_id' => 'Dependencia',
        ];
    }
}
