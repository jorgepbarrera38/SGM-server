<?php

namespace App\Http\Controllers;

use App\Models\FailureMode;
use Illuminate\Http\Request;

class FailureModeController extends Controller
{
    public function __construct () {
        $this->middleware('has.permission:failure-modes.index')->only('index');
        $this->middleware('has.permission:failure-modes.create')->only('store');
        $this->middleware('has.permission:failure-modes.edit')->only('update');
        $this->middleware('has.permission:failure-modes.destroy')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return FailureMode::name(request('name'))->orderBy('name')->paginate(10);
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
            'name' => 'required|max:60|unique:failure_modes,name'
        ], [], [
            'name' => 'Nombre'
        ]);

        FailureMode::create($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\FailureMode  $failureMode
     * @return \Illuminate\Http\Response
     */
    public function show(FailureMode $failureMode)
    {
        return $failureMode;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FailureMode  $failureMode
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FailureMode $failureMode)
    {
        $data = $request->validate([
            'name' => 'required|max:60|unique:failure_modes,name,' . $failureMode->id
        ], [], [
            'name' => 'Nombre'
        ]);

        $failureMode->update($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FailureMode  $failureMode
     * @return \Illuminate\Http\Response
     */
    public function destroy(FailureMode $failureMode)
    {
        $failureMode->delete();
    }
}
