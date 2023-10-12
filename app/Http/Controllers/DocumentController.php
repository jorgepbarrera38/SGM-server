<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\TypeDocument;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function __construct () {
        $this->middleware('has.permission:document.create')->only('store');
        $this->middleware('has.permission:document.destroy')->only('destroy');
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

    public function getDataToCreateDocument () {
        return TypeDocument::orderBy('name')->get(['id', 'name']);
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
            'type_document_id' => 'required|exists:type_documents,id',
            'file' => 'required|mimes:pdf|max:25000',
        ], [], [
            'type_document_id' => 'Tipo de documento',
            'file' => 'Documento'
        ]);

        //Verificando que el usuario esté autorizado en el grupo del dispositivo
        $hasPermissionInGroup = auth()->user()->groups()->where('groups.id', $device['group_id'])->first();

        if (!$hasPermissionInGroup) {
            return response()->json(['errors' => ['message' => ['El usuario no está autorizado en el grupo del dispositivo']]], 422);
        }

        $pathFile = Storage::putFile('documents', $request->file);

        $device->documents()->create([
            'type_document_id' => $request->type_document_id,
            'file' => $pathFile,
            'user_id' => auth()->user()->id
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function show(Document $document)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Document $document)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function destroy(Document $document)
    {
        if (auth()->user()->id == $document->user_id) {

            //Verificando que el usuario esté autorizado en el grupo del dispositivo
            $hasPermissionInGroup = auth()->user()->groups()->where('groups.id', $document->device['group_id'])->first();

            if (!$hasPermissionInGroup) {
                return response()->json(['errors' => ['message' => ['El usuario no está autorizado en el grupo del dispositivo']]], 422);
            }

            if (Storage::exists($document->file)) {
                Storage::delete($document->file);
            }

            $document->delete();
            return;
        }

        return abort(403);
    }
}
