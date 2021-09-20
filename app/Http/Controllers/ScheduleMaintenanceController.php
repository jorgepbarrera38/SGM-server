<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Group;
use App\Models\Element;
use App\Models\Location;
use App\Models\ScheduleMaintenance;
use App\Models\ScheduleMaintenanceDetail;
use App\Models\RecordScheduleMaintenance;
use App\Models\RecordDelScheduleMaintenance;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use App\Http\Requests\ScheduleRequest;

class ScheduleMaintenanceController extends Controller
{
    public function __construct () {
        $this->middleware('has.permission:schedule-maintenance.index')->only('index');
        $this->middleware('has.permission:schedule-maintenance.create')->only('store');
        $this->middleware('has.permission:schedule-maintenance.show')->only('show');
        $this->middleware('has.permission:schedule-maintenance.destroy')->only('destroy');
        $this->middleware('has.permission:schedule-maintenance.finalize')->only('finish');
        $this->middleware('has.permission:schedule-maintenance.reschedule-devices')->only('rescheduleDetail');
        $this->middleware('has.permission:schedule-maintenance.delete-devices')->only('deleteDetail');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return ScheduleMaintenance::name(request('name'))
                                                    ->orderBy('name', 'asc')
                                                    ->with('user')
                                                    ->with('scheduleMaintenanceDetails:id,schedule_maintenance_id,made')
                                                    ->paginate(10);
    }

    public function getDataToCreateScheduleMaintenance () {
        $groups = Group::orderBy('name')->get(['id', 'name']);
        $elements = Element::orderBy('name')->get();
        $locationsWithDevices = Location::orderBy('name')
                        ->with('devices.group:id,name')
                        ->with('devices.dependence')
                        ->with('devices.location')
                        ->with('devices.element')
                        ->with(['devices' => function ($query) {
                            $query->where('active', 1)->orderBy('dependence_id', 'asc')->get();
                        }])
                        ->get(['id', 'name']);

        return [
            $groups, $elements, $locationsWithDevices
        ];

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ScheduleRequest $request)
    {    
        //Verificando que la fecha de mantenimiento 
        //de cada dispositivo corresponda con el año que digitó el usuario
        foreach ($request->devices as $key => $value) {
            $yearRequestDevice = Carbon::createFromDate($value['date'])->year;

            if ($yearRequestDevice != $request->yearOfSchedule) {
                return response()->json(['errors' => ['message' => ['Hay fechas no válidas']]], 422);
            }
        }

        //Validando que el usuario esté autorizado en los grupos de los dispositivos
        foreach ($request->devices as $device) {
            $deviceDB = Device::find($device['device_id']);

            $hasPermissionInGroup = auth()->user()->groups()->where('groups.id', $deviceDB->group_id)->first();

            if (!$hasPermissionInGroup) {
                $group = Group::find($deviceDB->group_id);
                return response()->json(['errors' => ['message' => ["El usuario no está autorizado en el grupo: $group->name"]]], 422);
            }  
        }

        //Validando que el dispositivo no esté programado para el mismo mes y año
        $keysFromDeviceThatAreScheduled = [];

        foreach ($request->devices as $key => $value) {
            $yearRequest = Carbon::createFromDate($value['date'])->year;
            $monthRequest = Carbon::createFromDate($value['date'])->month;

            $deviceIsScheduled = ScheduleMaintenanceDetail::where('device_id', $value['device_id'])
                                            ->whereYear('date', $yearRequest)
                                            ->whereMonth('date', $monthRequest)
                                            ->whereHas('scheduleMaintenance', function (Builder $query) {
                                                $query->where('finished', false);
                                            })
                                            ->first();
            
            if ($deviceIsScheduled) {
                $keysFromDeviceThatAreScheduled[] = $value['device_id'];
            }
        }

        if (count($keysFromDeviceThatAreScheduled)) {
            return response()->json(['errors' => ['message' => ['Hay dispositivos que ya se encuentran programados para la fecha dada'], 'keysFromDeviceThatAreScheduled' => $keysFromDeviceThatAreScheduled]], 422);
        }
        //Fin Validando que el dispositivo no esté programado para el mismo mes y año
        
        \DB::transaction(function () use ($request) {
            $scheduleMaintenance = ScheduleMaintenance::create([
                                                            'name' => $request->nameOfSchedule, 
                                                            'year' => $request->yearOfSchedule, 
                                                            'user_id' => auth()->user()->id
                                                        ]);
            $scheduleMaintenance->scheduleMaintenanceDetails()->createMany($request->get('devices'));
        });    
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($scheduleMaintenance)
    {
        return ScheduleMaintenance::where('id', $scheduleMaintenance)
                                    ->with('recordDelScheduleMaintenances.user:id,names')
                                    ->with('recordDelScheduleMaintenances:id,user_id,schedule_maintenance_id,created_at,observation,device_id')
                                    ->with('scheduleMaintenanceDetails.preventiveMaintenance:id,evidence')
                                    ->with('scheduleMaintenanceDetails.device.group:id,name')
                                    ->with('scheduleMaintenanceDetails.device.location:id,name')
                                    ->with('scheduleMaintenanceDetails.device.element:id,name')
                                    ->with('scheduleMaintenanceDetails.device.dependence:id,name')
                                    ->with('scheduleMaintenanceDetails.device:id,internal_code,serial_number,group_id,location_id,element_id,dependence_id')
                                    ->with('scheduleMaintenanceDetails:id,made,date,preventive_maintenance_id,schedule_maintenance_id,device_id')
                                    ->with('scheduleMaintenanceDetails.recordScheduleMaintenances.user:id,names')
                                    ->with(['scheduleMaintenanceDetails' => function ($query) {
                                        $query->orderBy('date');
                                    }])
                                    ->firstOrFail(['id', 'name', 'year']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ScheduleMaintenance $scheduleMaintenance)
    {   
        //Solo el que creó el cronograma puede eliminarlo
        if ($scheduleMaintenance->user_id != auth()->user()->id) {
            return response()->json(['errors' => ['message' => ['Solo el que registra el cronograma puede eliminarlo']]], 422);
        }

        //Verificando que el mantenimiento no esté finalizado
        if ($scheduleMaintenance->finished) {
            return response()->json(['errors' => ['message' => ['Un cronograma finalizado no se puede eliminar']]], 422);
        }

        $scheduleMaintenanceThatHasPreventivesMade = ScheduleMaintenance::where('id', $scheduleMaintenance->id)
                                                    ->whereHas('scheduleMaintenanceDetails', function (Builder $query) {
                                                        $query->where('made', true);
                                                    })->first();
                        
        
        //Verificando que la programación no tenga preventivos que ya se hayan ejecutado
        if ($scheduleMaintenanceThatHasPreventivesMade) {
            return response()->json(['errors' => ['message' => ['El cronograma no se puede eliminar ya que tiene mantenimientos ejecutados']]], 422);
        }

        
        \DB::transaction(function () use ($scheduleMaintenance) {
            $scheduleMaintenanceDetailIds = $scheduleMaintenance->scheduleMaintenanceDetails()->get(['id']);
            RecordScheduleMaintenance::whereIn('schedule_maint_detail_id', $scheduleMaintenanceDetailIds)->delete();
            $scheduleMaintenance->recordDelScheduleMaintenances()->delete();
            $scheduleMaintenance->scheduleMaintenanceDetails()->delete();
            $scheduleMaintenance->delete();
        });
    }

    public function finish(ScheduleMaintenance $scheduleMaintenance) {
        //Solo el que creó el cronograma puede finalizarlo
        if ($scheduleMaintenance->user_id != auth()->user()->id) {
            return response()->json(['errors' => ['message' => ['Solo el que registra el cronograma puede finalizarlo']]], 422);
        }
        $scheduleMaintenance->finished = true;
        $scheduleMaintenance->save();
    }

    public function exportExcel($scheduleMaintenance) {
        return \Excel::download(new \App\Exports\ScheduleExport($scheduleMaintenance), 'invoices.xlsx');
        return $scheduleMaintenance;
    }

    public function  rescheduleDetail (Request $request, ScheduleMaintenanceDetail $scheduleMaintenanceDetail) {
        $request->validate([
            'newDateScheduleMaintenanceDetail' => 'required|date'
        ], [
            'newDateScheduleMaintenanceDetail.required' => 'Debe diligenciar el mes'
        ]);

        //Solo el que creó el cronograma puede reprogramarlo
        if ($scheduleMaintenanceDetail->scheduleMaintenance->user_id != auth()->user()->id) {
            return response()->json(['errors' => ['message' => ['Solo el que registra el cronograma puede reprogramar los elementos']]], 422);
        }

        //Verificando si el cronograma se encuentra finalizado
        if ($scheduleMaintenanceDetail->scheduleMaintenance->finished) {
            return response()->json(['errors' => ['message' => ['El dispositivo no se puede reprogramar ya que el cronograma se encuentra finalizado']]], 422);
        }
        //Verificando que la fecha de la programación 
        //del dispositivo corresponda con el año que digitó en la petición
        $yearRequest = Carbon::createFromDate($request->newDateScheduleMaintenanceDetail)->year;
        $yearSchedule = $scheduleMaintenanceDetail->scheduleMaintenance->year;

        if ($yearRequest != $yearSchedule) {
            return response()->json(['errors' => ['message' => ['La fecha suministrada no corresponde a un año válido']]], 422);
        }

        //Verificando que no esté ejecutado el mantenimiento
        if ($scheduleMaintenanceDetail->made) {
            return response()->json(['errors' => ['message' => ['No se puede reprogramar ya que el mantenimiento fué ejectuado']]], 422);
        }

        //Verificando que el dispositivo no haya sido programdo 
        //con anterioridad para la fecha dada
        //$yearRequest = Carbon::createFromDate($request->newDateScheduleMaintenanceDetail)->year;
        $monthRequest = Carbon::createFromDate($request->newDateScheduleMaintenanceDetail)->month;

        $deviceIsScheduled = ScheduleMaintenanceDetail::where('device_id', $scheduleMaintenanceDetail->device->id)
                                        ->whereYear('date', $yearRequest)
                                        ->whereMonth('date', $monthRequest)->first();
        
        if ($deviceIsScheduled) {
            return response()->json(['errors' => ['message' => ['El dispositivo ya se encuentra programado para la fecha dada']]], 422);
        }

        \DB::transaction(function () use ($scheduleMaintenanceDetail, $request) {
            //Registrando el histórico del cambio de fecha
            RecordScheduleMaintenance::create([
                'old_date' => $scheduleMaintenanceDetail->date,
                'new_date' => $request->newDateScheduleMaintenanceDetail,
                'schedule_maint_detail_id' => $scheduleMaintenanceDetail->id,
                'user_id' => auth()->user()->id,
            ]);
    
            //Actualizando fecha de programación
            $scheduleMaintenanceDetail->date = $request->newDateScheduleMaintenanceDetail;
            $scheduleMaintenanceDetail->save();
        });
    }

    public function deleteDetail (Request $request, ScheduleMaintenanceDetail $scheduleMaintenanceDetail) {
        $request->validate([
            'observation' => 'required|max:200'
        ], [], [
            'observation' => 'Observación'
        ]);

        //Solo el que creó el cronograma puede eliminar elementos programados
        if ($scheduleMaintenanceDetail->scheduleMaintenance->user_id != auth()->user()->id) {
            return response()->json(['errors' => ['message' => ['Solo el que registra el cronograma puede eliminar elementos']]], 422);
        }

        //Verificando si el cronograma se encuentra finalizado
        if ($scheduleMaintenanceDetail->scheduleMaintenance->finished) {
            return response()->json(['errors' => ['message' => ['El registro no se puede eliminar ya que el cronograma se encuentra finalizado']]], 422);
        }

        //Verificando que no esté ejecutado el mantenimiento
        if ($scheduleMaintenanceDetail->made) {
            return response()->json(['errors' => ['message' => ['El registro no se puede eliminar ya que el mantenimiento fué ejectuado']]], 422);
        }

        //Verificando que no quede vacío el cronograma
        $quantityElements = ScheduleMaintenanceDetail::where('schedule_maintenance_id', $scheduleMaintenanceDetail->scheduleMaintenance->id)->count();

        if ($quantityElements == 1) {
            return response()->json(['errors' => ['message' => ['Debe haber por lo menos 1 elemento en el cronograma']]], 422);
        }
        
        \DB::transaction(function () use($scheduleMaintenanceDetail, $request) {
            RecordDelScheduleMaintenance::create([
                'schedule_maintenance_id' => $scheduleMaintenanceDetail->schedule_maintenance_id,
                'device_id' => $scheduleMaintenanceDetail->device_id,
                'observation' => $request->observation,
                'user_id' => auth()->user()->id
            ]);
            $scheduleMaintenanceDetail->recordScheduleMaintenances()->delete();
            $scheduleMaintenanceDetail->delete();
        });
    }
}
