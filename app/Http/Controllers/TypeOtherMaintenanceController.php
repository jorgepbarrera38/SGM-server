<?php

namespace App\Http\Controllers;

use App\Models\TypeOtherMaintenance;
use Illuminate\Http\Request;

class TypeOtherMaintenanceController extends Controller
{
    public function __construct () {
        $this->middleware('has.permission:type-other-maintenances.index')->only('index');
        $this->middleware('has.permission:type-other-maintenances.create')->only('store');
        $this->middleware('has.permission:type-other-maintenances.edit')->only('update');
        $this->middleware('has.permission:type-other-maintenances.destroy')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return TypeOtherMaintenance::name(request('name'))->orderBy('name')->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|max:30|unique:type_other_maintenances,name'
        ], [], [
            'name' => 'Nombre'
        ]);

        TypeOtherMaintenance::create($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TypeOtherMaintenance  $typeOtherMaintenance
     * @return \Illuminate\Http\Response
     */
    public function show(TypeOtherMaintenance $typeOtherMaintenance)
    {
        return $typeOtherMaintenance;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TypeOtherMaintenance  $typeOtherMaintenance
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TypeOtherMaintenance $typeOtherMaintenance)
    {
        $data = $request->validate([
            'name' => 'required|max:30|unique:type_other_maintenances,name,' . $typeOtherMaintenance->id
        ], [], [
            'name' => 'Nombre'
        ]);

        $typeOtherMaintenance->update($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TypeOtherMaintenance  $typeOtherMaintenance
     * @return \Illuminate\Http\Response
     */
    public function destroy(TypeOtherMaintenance $typeOtherMaintenance)
    {
        $typeOtherMaintenance->delete();
    }
}