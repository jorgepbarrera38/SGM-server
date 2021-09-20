<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use App\Models\Device;
use App\Models\Location;
use App\Models\Dependence;
use App\Models\Responsible;
use Illuminate\Http\Request;

class MovementController extends Controller
{
    public function __construct () {
        $this->middleware('has.permission:devices.movements.create');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function getDataToCreateMovement ($device) {

        $currentMovement = Device::where('id', $device)->firstOrFail([
                'location_id',
                'dependence_id',
                'responsible_id'
            ]);

        $location = Location::orderBy('name')->get(['id', 'name']);
        $dependence = Dependence::orderBy('name')->get(['id', 'name']);
        $responsible = Responsible::orderBy('fullnames')->get(['id', 'fullnames']);

        return [
            'currentMovement' => $currentMovement,
            'locations' => $location,
            'dependences' => $dependence,
            'responsibles' => $responsible
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Device $device)
    {
        $data = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'dependence_id' => 'required|exists:dependences,id',
            'responsible_id' => 'required|exists:responsibles,id',
            'observations' => 'required|max:150',
        ], [], [
            'location_id' => 'Ubicación',
            'dependence_id' => 'Dependencia',
            'responsible_id' => 'Responsable',
            'observations' => 'Observación'
        ]);

        //Verificando que el usuario esté autorizado en el grupo del dispositivo
        $hasPermissionInGroup = auth()->user()->groups()->where('groups.id', $device['group_id'])->first();

        if (!$hasPermissionInGroup) {
            return response()->json(['errors' => ['message' => ['El usuario no está autorizado en el grupo del dispositivo']]], 422);
        } 

        \DB::transaction(function () use ($device, $data) {
            $device->update([
                'location_id' => $data['location_id'],
                'dependence_id' => $data['dependence_id'],
                'responsible_id' => $data['responsible_id']
            ]);
            $device->movements()->create([
                'location_id' => $data['location_id'],
                'dependence_id' => $data['dependence_id'],
                'responsible_id' => $data['responsible_id'],
                'observations' => $data['observations'],
                'user_id' => auth()->user()->id
            ]);
        });
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Movement  $movement
     * @return \Illuminate\Http\Response
     */
    public function show(Movement $movement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Movement  $movement
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Movement $movement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Movement  $movement
     * @return \Illuminate\Http\Response
     */
    public function destroy(Movement $movement)
    {
        //
    }
}
