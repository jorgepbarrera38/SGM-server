<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class PreventiveMaintenanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->route()->named('preventives.update')) {
            if ($this->route('preventiveMaintenance')->user_id != auth()->user()->id) {
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
            'date_of_realization_ending' => 'required|date|after_or_equal:date_of_realization',
            'evidence' => 'required_if:type_report,2|nullable|mimes:pdf|max:25000',
            'signature' => 'required_if:type_report,1|base64image',
            'official_names' => 'required_if:type_report,1|nullable|max:50',
            'official_position' => 'required_if:type_report,1|nullable|max:30',
            'conclusion' => 'required_if:type_report,1|max:400',
        ];
    }

    public function messages () {
        return [
            'evidence.required_if' => 'El campo Evidencia es obligatorio',
            'official_names.required_if' => 'El campo Funcionario es obligatorio',
            'official_position.required_if' => 'El campo Cargo es obligatorio',
            'conclusion.required_if' => 'El campo Descripción del soporte es obligatorio',
            'date_of_realization_ending.after_or_equal' => 'La Fecha y hora fin de realización debe ser superior a la fecha y hora inicio de realización.'
        ];
    }

    public function attributes () {
        return [
            'type_report' => 'Tipo',
            'date_of_realization' => 'Fecha y hora inicio de realización',
            'date_of_realization_ending' => 'Fecha y hora fin de realización',
            'evidence' => 'Evidencia',
            'official_names' => 'Funcionario',
            'official_position' => 'Cargo',
            'conclusion' => 'Descripción del soporte'
        ];
    }
}
