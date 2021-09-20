<?php

namespace App\Http\Controllers;

use App\Models\Period;
use Illuminate\Http\Request;

class PeriodController extends Controller
{
    public function __construct () {
        $this->middleware('has.permission:periods.index')->only('index');
        $this->middleware('has.permission:periods.create')->only('store');
        $this->middleware('has.permission:periods.edit')->only('update');
        $this->middleware('has.permission:periods.destroy')->only('destroy');
        $this->middleware('has.permission:periods.update-state')->only('updateState');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Period::name(request('name'))->orderBy('start', 'desc')->paginate(10);
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
            'name' => 'required|max:30|unique:periods,name',
            'start' => 'required|date',
            'end' => 'required|date|after:start'
        ], [], [
            'name' => 'Nombre',
            'start' => 'Inicio',
            'end' => 'Fin'
        ]);

        Period::create($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Period  $period
     * @return \Illuminate\Http\Response
     */
    public function show(Period $period)
    {
        return $period;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Period  $period
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Period $period)
    {
        $data = $request->validate([
            'name' => 'required|max:30|unique:periods,name,' . $period->id,
            'start' => 'required|date',
            'end' => 'required|date|after:start'
        ], [], [
            'name' => 'Nombre',
            'start' => 'Inicio',
            'end' => 'Fin'
        ]);

        $period->update($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Period  $period
     * @return \Illuminate\Http\Response
     */
    public function destroy(Period $period)
    {
        $period->delete();
    }

    public function updateState (Period $period) {
        $period->update(['active' => !$period->active]);
    }
}
