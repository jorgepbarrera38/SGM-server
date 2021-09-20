<?php

namespace App\Http\Controllers;

use App\Models\Dependence;
use Illuminate\Http\Request;

class DependenceController extends Controller
{
    public function __construct () {
        $this->middleware('has.permission:dependences.index')->only('index');
        $this->middleware('has.permission:dependences.create')->only('store');
        $this->middleware('has.permission:dependences.edit')->only('update');
        $this->middleware('has.permission:dependences.destroy')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Dependence::name(request('name'))->orderBy('name')->paginate(10);
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
            'name' => 'required|max:30|unique:dependences,name'
        ], [], [
            'name' => 'Nombre'
        ]);

        Dependence::create($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Dependence  $dependence
     * @return \Illuminate\Http\Response
     */
    public function show(Dependence $dependence)
    {
        return $dependence;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Dependence  $dependence
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Dependence $dependence)
    {
        $data = $request->validate([
            'name' => 'required|max:30|unique:dependences,name,' . $dependence->id
        ], [], [
            'name' => 'Nombre'
        ]);

        $dependence->update($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Dependence  $dependence
     * @return \Illuminate\Http\Response
     */
    public function destroy(Dependence $dependence)
    {
        $dependence->delete();
    }
}
