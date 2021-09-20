<?php

namespace App\Http\Controllers;

use App\Models\TypeDocument;
use Illuminate\Http\Request;

class TypeDocumentController extends Controller
{
    public function __construct () {
        $this->middleware('has.permission:type-documents.index')->only('index');
        $this->middleware('has.permission:type-documents.create')->only('store');
        $this->middleware('has.permission:type-documents.edit')->only('update');
        $this->middleware('has.permission:type-documents.destroy')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return TypeDocument::name(request('name'))->orderBy('name')->paginate(10);
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
            'name' => 'required|max:40|unique:type_documents,name'
        ], [], [
            'name' => 'Nombre'
        ]);

        TypeDocument::create($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TypeDocument  $typeDocument
     * @return \Illuminate\Http\Response
     */
    public function show(TypeDocument $typeDocument)
    {
        return $typeDocument;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TypeDocument  $typeDocument
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TypeDocument $typeDocument)
    {
        $data = $request->validate([
            'name' => 'required|max:40|unique:type_documents,name,' . $typeDocument->id
        ], [], [
            'name' => 'Nombre'
        ]);

        $typeDocument->update($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TypeDocument  $typeDocument
     * @return \Illuminate\Http\Response
     */
    public function destroy(TypeDocument $typeDocument)
    {
        $typeDocument->delete();
    }
}
