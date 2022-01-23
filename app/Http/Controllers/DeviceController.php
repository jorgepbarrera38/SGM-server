<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Group;
use App\Models\Element;
use App\Models\Location;
use App\Models\Dependence;
use App\Models\Responsible;
use App\Models\Brand;
use App\Models\Company;
use App\Models\PhotographicRecord;
use App\Models\RecordInternalCodeDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;

class DeviceController extends Controller
{
    public function __construct () {
        $this->middleware('has.permission:devices.index')->only('index');
        $this->middleware('has.permission:devices.create')->only(['store']);
        $this->middleware('has.permission:devices.show')->only('show');
        $this->middleware('has.permission:devices.edit')->only(['selectAsPrincipal', 'getPhotographicRecord', 'uploadPhotographicRecord', 'deletePhotographicRecord', 'edit', 'update']);
        $this->middleware('has.permission:devices.down')->only('down');
        $this->middleware('has.permission:devices.update-internal-code')->only('updateInternalCode');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $groupsFromUser = auth()->user()->groups;

        if (request('group')) {
            $group = Group::where('id', request('group'))->whereHas('users', function ($query) {
                $query->where('users.id', auth()->user()->id);
            })->first();

            if (!$group) {
                return response()->json(['message' => 'El usuario no se encuentra autorizado en el grupo seleccionado'], 422);
            }
        }

        if (count($groupsFromUser)) {
            $firstGroupId = $groupsFromUser[0]->id;
            $devices = Device::internalCodeOrSerial(request('internal-code-or-serial'))
                    //->group(request('group'))
                    ->when(request('group'), function ($query, $group) {
                        $query->whereHas('group', function ($query) use ($group) {
                            $query->where('id', $group);
                        });
                    }, function ($query) use ($firstGroupId) {
                        $query->whereHas('group', function ($query) use ($firstGroupId) {
                            $query->where('id', $firstGroupId);
                        });
                    })
                    ->element(request('element'))
                    ->location(request('location'))
                    ->dependence(request('dependence'))
                    ->responsible(request('responsible'))
                    ->where('active', true)
                    ->with('group')
                    ->with('element')
                    ->with('dependence')
                    ->with('responsible')
                    ->with(['photographicRecords' => function ($query) {
                        $query->where('principal', true);
                    }])
                    ->with('location')
                    ->select('id', 'resume_number', 'group_id', 'element_id', 'internal_code', 'serial_number', 'location_id', 'dependence_id', 'responsible_id', 'description', 'observations', 'updated_at')
                    ->orderBy('resume_number')
                    ->paginate(8);

            if (json_decode(request()->get('get-filters'))) { //Devuelve true o false
                $elements = Element::orderBy('name')->get();
                $locations = Location::orderBy('name')->get();
                $dependences = Dependence::orderBy('name')->get();
                $responsibles = Responsible::orderBy('names')->get();
    
                return [
                    'groups' => $groupsFromUser,
                    'elements' => $elements,
                    'locations' => $locations,
                    'dependences' => $dependences,
                    'responsibles' => $responsibles,
                    'devices' => $devices,
                ];
            } else {
                return $devices;
            }            
        }

        return response()->json(['message' => 'El usuario no se encuentra autorizado en ningún grupo'], 422);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getDataToCreateDevice () {
        $groups = Group::orderBy('name')->get(['id', 'name']);
        $elements = Element::orderBy('name')->get(['id', 'name']);
        $locations = Location::orderBy('name')->get(['id', 'name']);
        $dependences = Dependence::orderBy('name')->get(['id', 'name']);
        $responsibles = Responsible::orderBy('fullnames')->get(['id', 'fullnames']);
        $brands = Brand::orderBy('name')->get(['id', 'name']);

        return [
            'groups' => $groups,
            'elements' => $elements,
            'locations' => $locations,
            'dependences' => $dependences,
            'responsibles' => $responsibles,
            'brands' => $brands
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'group_id' => 'required|exists:groups,id',
            'element_id' => 'required|exists:elements,id',
            'location_id' => 'required|exists:locations,id',
            'dependence_id' => 'required|exists:dependences,id',
            'responsible_id' => 'required|exists:responsibles,id',
            'internal_code' => 'required|max:20|unique:devices,internal_code',
            'serial_number' => 'required|max:40|unique:devices,serial_number',
            'brand_id' => 'required|exists:brands,id',
            'model' => 'required|max:40',
            'description' => 'required|max:500',
            'observations' => 'nullable|max:300',
            'start_operation' => 'required|date',
            'date_end_guarantee' => 'required|date',
            'useful_life' => 'required|numeric|min:1|max:20',
            'observations' => 'nullable|max:300',
            'description_support_preventive_maintenance' => 'nullable|max:400',
        ], [], [
            'group_id' => 'Grupo',
            'element_id' => 'Elemento',
            'location_id' => 'Ubicación',
            'dependence_id' => 'Dependencia',
            'responsible_id' => 'Responsable',
            'internal_code' => 'Placa',
            'serial_number' => 'Serial',
            'brand_id' => 'Marca',
            'model' => 'Modelo',
            'description' => 'Descripción',
            'observations' => 'Observaciónes',
            'start_operation' => 'Inicio de operación',
            'date_end_guarantee' => 'Fecha fin de garantía',
            'useful_life' => 'Vida útil',
            'description_support_preventive_maintenance' => 'Descripción de soporte para mantenimientos preventivos'
        ]);

        //Verificando que el usuario esté autorizado en el grupo selecccionado
        $hasPermissionInGroup = auth()->user()->groups()->where('groups.id', $data['group_id'])->first();

        if (!$hasPermissionInGroup) {
            return response()->json(['errors' => ['message' => ['El usuario no está autorizado en el grupo seleccionado']]], 422);
        }

        /*Definiendo número de hoja de vida*/
        $deviceLastResumeNumber = Device::where('group_id', $data['group_id'])->orderBy('resume_number', 'desc')->first();

        if ($deviceLastResumeNumber) {
            $data['resume_number'] = $deviceLastResumeNumber->resume_number + 1;
        } else {
            $data['resume_number'] = 1;
        }


        \DB::transaction(function () use ($data) {
            $device = auth()->user()->devices()->create($data);
            $device->movements()->create([
                'location_id' => $data['location_id'],
                'dependence_id' => $data['dependence_id'],
                'responsible_id' => $data['responsible_id'],
                'observations' => 'Primer movimiento',
                'user_id' => auth()->user()->id
            ]);
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function show($device)
    {
        
        $device = Device::where('id', $device)//->where('active', true)
                                        ->with('group:id,name')
                                        ->with('element:id,name')
                                        ->with('location:id,name')
                                        ->with('dependence:id,name')
                                        ->with('responsible:id,identification,fullnames')
                                        ->with('brand:id,name')

                                        ->with('movements.location:id,name')
                                        ->with('movements.dependence:id,name')
                                        ->with('movements.responsible:id,fullnames')
                                        ->with('movements.user:id,names')
                                        ->with(['movements' => function ($query) {
                                            $query->orderBy('created_at', 'desc');
                                        }])

                                        ->with('preventiveMaintenances.user')
                                        ->with('preventiveMaintenances.replacementMaintenancables.replacement')

                                        ->with(['preventiveMaintenances' => function ($query) {
                                            $query->orderBy('date_of_realization', 'desc');
                                        }])

                                        ->with('otherMaintenances.user')
                                        ->with('otherMaintenances.typeOtherMaintenance:id,name')
                                        ->with('otherMaintenances.replacementMaintenancables.replacement')

                                        ->with(['otherMaintenances' => function ($query) {
                                            $query->orderBy('date_of_realization', 'desc');
                                        }])
                                        ->with('documents.user:id,names')
                                        ->with('documents.typeDocument:id,name')
                                        ->with('documents:id,type_document_id,user_id,file,device_id')

                                        ->with('photographicRecords')
                                        ->with('scheduleMaintenanceDetails.scheduleMaintenance')
                                        ->with(['scheduleMaintenanceDetails' => function ($query) {
                                            $query->orderBy('date');
                                        }])
                                        ->firstOrFail();

        //Verificando que el usuario esté autorizado en el grupo del dispositivo
        $hasPermissionInGroup = auth()->user()->groups()->where('groups.id', $device['group_id'])->first();

        if (!$hasPermissionInGroup) {
            return response()->json(['errors' => ['message' => ['El usuario no está autorizado en el grupo del dispositivo']]], 422);
        }        
        
        return $device;
    }

    public function edit (Device $device) {
        return $device;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Device $device)
    {
        $data = $request->validate([
            'group_id' => 'required|exists:groups,id',
            'element_id' => 'required|exists:elements,id',
            'serial_number' => 'required|max:40|unique:devices,serial_number,' . $device->id,
            'brand_id' => 'required|exists:brands,id',
            'model' => 'required|max:40',
            'description' => 'required|max:500',
            'observations' => 'nullable|max:300',
            'start_operation' => 'required|date',
            'date_end_guarantee' => 'required|date',
            'useful_life' => 'required|numeric|min:1|max:20',
            'description_support_preventive_maintenance' => 'nullable|max:400',
        ], [], [
            'group_id' => 'Grupo',
            'element_id' => 'Elemento',
            'serial_number' => 'Serial',
            'brand_id' => 'Marca',
            'model' => 'Modelo',
            'description' => 'Descripción',
            'observations' => 'Observaciónes',
            'start_operation' => 'Inicio de operación',
            'date_end_guarantee' => 'Fecha fin de garantía',
            'useful_life' => 'Vida útil',
            'description_support_preventive_maintenance' => 'Descripción de soporte para mantenimientos preventivos'
        ]);

        //Verificando que el usuario esté autorizado en el grupo selecccionado
        $hasPermissionInGroup = auth()->user()->groups()->where('groups.id', $data['group_id'])->first();

        if (!$hasPermissionInGroup) {
            return response()->json(['errors' => ['message' => ['El usuario no está autorizado en el grupo seleccionado']]], 422);
        }

        $existsDeviceWithSameResumenNumber = Device::where([
                                                                'group_id' => $data['group_id'],
                                                                'resume_number' => $device->resume_number
                                                            ])->first();
        if ($existsDeviceWithSameResumenNumber && $device->group_id !=  $data['group_id']) {
            return response()->json(['errors' => ['message' => ['Ya existe un dispositivo con el mismo número de hoja de vida en el grupo seleccionado']]], 422);
        }

        $device->update($data);
    }

    public function down(Request $request, $device)
    {
        $deviceHasSchedulePending = Device::where('id', $device)->whereHas('scheduleMaintenanceDetails.scheduleMaintenance', function (Builder $query) {
                                                $query->where('finished', false);
                                            })->first();

        if (!empty($deviceHasSchedulePending)) {
            return response()->json(['errors' => ['' => ['El dispositivo tiene activo un mantenimiento programado']]], 422);
        }

        $device = Device::where([
            'id' => $device,
            'active' => true
        ])->first();

        if (empty($device)) {
            return response()->json(['errors' => ['' => ['El dispositivo ya fué dado de baja o no existe']]], 422);
        }
        
        $request->validate([
            'down_date' => 'required|date',
            'down_reason' => 'required|max:150',
            'down_evidence' => 'required|mimes:pdf|max:25000',
        ], [], [
            'down_date' => 'Fecha',
            'down_reason' => 'Motivo',
            'down_evidence' => 'Evidencia '
        ]);

        //Verificando que el usuario esté autorizado en el grupo del dispositivo
        $hasPermissionInGroup = auth()->user()->groups()->where('groups.id', $device['group_id'])->first();

        if (!$hasPermissionInGroup) {
            return response()->json(['errors' => ['message' => ['El usuario no está autorizado en el grupo del dispositivo']]], 422);
        } 
            
        $pathEvidence = Storage::putFile('down-devices', $request->down_evidence);

        if ($device->down_evidence) {
            if (Storage::exists($device->down_evidence)) {
                Storage::delete($device->down_evidence);
            }
        }
        
        $device->update([
            'active' => 0,
            'down_date' => $request->down_date,
            'down_reason' => $request->down_reason,
            'down_evidence' => $pathEvidence,
            'down_user_id' => auth()->user()->id
        ]);

    }

    public function updateInternalCode (Request $request, Device $device) {
        $data = $request->validate([
            'internal_code' => 'required|max:20|unique:devices,internal_code'
        ], [
            'internal_code.unique' => 'La placa ya está en uso'
        ], [
            'internal_code' => 'Placa'
        ]);

        //Verificando que el usuario esté autorizado en el grupo del dispositivo
        $hasPermissionInGroup = auth()->user()->groups()->where('groups.id', $device['group_id'])->first();

        if (!$hasPermissionInGroup) {
            return response()->json(['errors' => ['message' => ['El usuario no está autorizado en el grupo del dispositivo']]], 422);
        } 

        $oldInternalCode = $device->internal_code;

        \DB::transaction(function () use ($device, $data, $oldInternalCode) {
            $device->update($data);
            $device->recordInternalCodeDevices()->create([
                'old_internal_code' => $oldInternalCode,
                'new_internal_code' => $device->internal_code,
                'user_id' => auth()->user()->id
            ]);
        });
    }

    public function uploadPhotographicRecord (Request $request, Device $device) {
        $request->validate([
            'photo' => 'required|mimes:jpg,jpeg'
        ], [], [
            'photo' => 'Foto'
        ]);

        //Verificando que el usuario esté autorizado en el grupo del dispositivo
        $hasPermissionInGroup = auth()->user()->groups()->where('groups.id', $device['group_id'])->first();

        if (!$hasPermissionInGroup) {
            return response()->json(['errors' => ['message' => ['El usuario no está autorizado en el grupo del dispositivo']]], 422);
        } 

        $pathPhoto = Storage::putFile('photos', $request->photo);
        PhotographicRecord::create([
            'device_id' => $device->id,
            'photo' => $pathPhoto
        ]);
    }

    public function getPhotographicRecord (Device $device) {
        //Verificando que el usuario esté autorizado en el grupo del dispositivo
        $hasPermissionInGroup = auth()->user()->groups()->where('groups.id', $device['group_id'])->first();

        if (!$hasPermissionInGroup) {
            return response()->json(['errors' => ['message' => ['El usuario no está autorizado en el grupo del dispositivo']]], 422);
        } 
        return $device->photographicRecords()->orderBy('id', 'desc')->get(['id', 'photo', 'principal']);
    }

    public function selectAsPrincipal (PhotographicRecord $photographicRecord) {
        //Verificando que el usuario esté autorizado en el grupo del dispositivo
        $hasPermissionInGroup = auth()->user()->groups()->where('groups.id', $photographicRecord->device['group_id'])->first();

        if (!$hasPermissionInGroup) {
            return response()->json(['errors' => ['message' => ['El usuario no está autorizado en el grupo del dispositivo']]], 422);
        } 

        $photographicRecords = $photographicRecord->device->photographicRecords()->get(['id']);
        
        \DB::transaction(function () use ($photographicRecords, $photographicRecord) {
            PhotographicRecord::whereIn('id', $photographicRecords)->update(['principal' => 0]);
            $photographicRecord->principal = true;
            $photographicRecord->save();
        });

    }

    public function deletePhotographicRecord (PhotographicRecord $photographicRecord)
    {
        if (Storage::exists($photographicRecord->photo)) {
            Storage::delete($photographicRecord->photo);
        }

        //Verificando que el usuario esté autorizado en el grupo del dispositivo
        $hasPermissionInGroup = auth()->user()->groups()->where('groups.id', $photographicRecord->device['group_id'])->first();

        if (!$hasPermissionInGroup) {
            return response()->json(['errors' => ['message' => ['El usuario no está autorizado en el grupo del dispositivo']]], 422);
        } 
        
        $photographicRecord->delete();
    }

    public function getResume (Device $device) {
        $company = Company::firstOrFail();
        $pdf = \PDF::loadView('resume.resume', ['device' => $device, 'companyLogo' => $company->logo])->setPaper('letter');
        return $pdf->download();
    }
}
