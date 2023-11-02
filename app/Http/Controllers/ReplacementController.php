<?php

namespace App\Http\Controllers;

use App\Models\Replacement;
use Illuminate\Http\Request;

class ReplacementController extends Controller
{
    public function __construct () {
        $this->middleware('has.permission:replacements.index')->only('index');
        $this->middleware('has.permission:replacements.create')->only('store');
        $this->middleware('has.permission:replacements.edit')->only('update');
        $this->middleware('has.permission:replacements.destroy')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Replacement::name(request('name'))->orderBy('name')->paginate(10);
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
            'name' => 'required|max:30|unique:replacements,name',
            'price' => 'required|numeric|min:0|max:1000000000',
        ], [], [
            'name' => 'Nombre',
            'price' => 'Precio',
        ]);

        Replacement::create($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Replacement  $replacement
     * @return \Illuminate\Http\Response
     */
    public function show(Replacement $replacement)
    {
        return $replacement;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Replacement  $replacement
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Replacement $replacement)
    {
        $data = $request->validate([
            'name' => 'required|max:30|unique:replacements,name,' . $replacement->id,
            'price' => 'required|numeric|min:0|max:1000000000',
        ], [], [
            'name' => 'Nombre',
            'price' => 'Precio',
        ]);

        $replacement->update($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Replacement  $replacement
     * @return \Illuminate\Http\Response
     */
    public function destroy(Replacement $replacement)
    {
        $replacement->delete();
    }
}
