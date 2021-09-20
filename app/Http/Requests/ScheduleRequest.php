<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleRequest extends FormRequest
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
            'nameOfSchedule' => 'required|max:60|unique:schedule_maintenances,name',
            'devices' => 'required|array',
            'devices.*.device_id' => 'required|exists:devices,id',
            'devices.*.date' => 'required|date',
            'yearOfSchedule' => 'required|numeric|between:2021,2100'
        ];
    }

    public function attributes()
    {
        return [
            'nameOfSchedule' => 'Nombre',
            'yearOfSchedule' => 'Año'
        ];
    }

    public function messages()
    {
        return [
            'nameOfSchedule.unique' => 'Ya existe una programación con el mismo nombre',
            'devices.required' => 'No ha agregado ningún dispositivo a la programación'
        ];
    }
}
