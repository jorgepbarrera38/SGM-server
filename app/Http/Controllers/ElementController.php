<?php

namespace App\Http\Controllers;

use App\Models\Element;
use Illuminate\Http\Request;

class ElementController extends Controller
{
    public function __construct () {
        $this->middleware('has.permission:elements.index')->only('index');
        $this->middleware('has.permission:elements.create')->only('store');
        $this->middleware('has.permission:elements.edit')->only('update');
        $this->middleware('has.permission:elements.destroy')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Element::name(request('name'))->orderBy('name')->paginate(10);
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
            'name' => 'required|max:30|unique:elements,name'
        ], [], [
            'name' => 'Nombre'
        ]);

        Element::create($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Element  $element
     * @return \Illuminate\Http\Response
     */
    public function show(Element $element)
    {
        return $element;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Element  $element
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Element $element)
    {
        $data = $request->validate([
            'name' => 'required|max:30|unique:elements,name,' . $element->id
        ], [], [
            'name' => 'Nombre'
        ]);

        $element->update($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Element  $element
     * @return \Illuminate\Http\Response
     */
    public function destroy(Element $element)
    {
        $element->delete();
    }
}
