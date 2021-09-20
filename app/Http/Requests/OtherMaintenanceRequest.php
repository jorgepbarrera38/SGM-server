<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class OtherMaintenanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->route()->named('otherMaintenances.update')) {
            if ($this->route('otherMaintenance')->user_id != auth()->user()->id) {
                return false;
            }
            return true;
        }
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
            'type_report' => ['required', Rule::in([1,2])],
            'date_of_realization' => 'required|date',
            'type_maintenance_id' => 'required|exists:type_other_maintenances,id',
            'reason' => 'required|max:400',
            'conclusion' => 'required|max:400',
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
            'evidence.required_if' => 'El campo evidencia es obligatorio',
            'official_names.required_if' => 'El campo funcionario es obligatorio',
            'official_position.required_if' => 'El campo cargo es obligatorio'
        ];
    }

    public function attributes () {
        return [
            'type_report' => 'Tipo',
            'date_of_realization' => 'Fecha de realización',
            'type_maintenance_id' => 'Clasificación del servicio',
            'reason' => 'Motivo',
            'conclusion' => 'Conclusión',
            'evidence' => 'Evidencia',
            'official_names' => 'Funcionario',
            'official_position' => 'Cargo',
            'location_id' => 'Ubicación',
            'dependence_id' => 'Dependencia'
        ];
    }
}
