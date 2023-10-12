<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\ChecklistGroup;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    public function __construct () {
        $this->middleware('has.permission:checklists.index')->only('index');
        $this->middleware('has.permission:checklists.create')->only('store');
        $this->middleware('has.permission:checklists.edit')->only(['update', 'addGroup', 'addItem', 'deleteItem', 'deleteGroup', 'updateChecklist']);
        $this->middleware('has.permission:checklists.destroy')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Checklist::name(request('name'))->orderBy('name')->paginate(10);
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
            'name' => 'required|max:60|unique:checklists,name'
        ], [], [
            'name' => 'Nombre',
        ]);

        Checklist::create($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Checklist  $checklist
     * @return \Illuminate\Http\Response
     */
    public function show(Checklist $checklist)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Checklist  $checklist
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Checklist $checklist)
    {
        return $checklist->load(['checklistGroups', 'checklistItems.checklistGroup']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Checklist  $checklist
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Checklist $checklist)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Checklist  $checklist
     * @return \Illuminate\Http\Response
     */
    public function destroy(Checklist $checklist)
    {
        $checklist->checklistItems()->delete();
        $checklist->checklistGroups()->delete();
        $checklist->delete();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Checklist  $checklist
     * @return \Illuminate\Http\Response
     */
    public function addGroup(Request $request, Checklist $checklist)
    {
        $data = $request->validate([
            'name' => 'required|max:50'
        ], [], [
            'name' => 'Nombre',
        ]);

        $checklist->checklistGroups()->create($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Checklist  $checklist
     * @return \Illuminate\Http\Response
     */
    public function addItem(Request $request, Checklist $checklist)
    {

        $data = $request->validate([
            'description' => 'required|max:300',
            'checklist_group_id' => 'required|exists:checklist_groups,id',
        ], [], [
            'description' => 'Descripción',
            'checklist_group_id' => 'Grupo',
        ]);

        $checklist->checklistItems()->create($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Checklist  $checklist
     * @return \Illuminate\Http\Response
     */
    public function deleteItem(ChecklistItem $checklistItem)
    {
        $checklistItem->delete();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Checklist  $checklist
     * @return \Illuminate\Http\Response
     */
    public function deleteGroup(ChecklistGroup $checklistGroup)
    {
        if (count($checklistGroup->checklistItems)) {
            return response()->json(['errors' => ['message' => ['El grupo tiene ítems relacionados']]], 422);
        }
        $checklistGroup->delete();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Checklist  $checklist
     * @return \Illuminate\Http\Response
     */
    public function updateChecklist(Request $request, Checklist $checklist)
    {
        $data = $request->validate([
            'name' => 'required|max:60|unique:checklists,name,' . $checklist->id,
        ], [], [
            'name' => 'Nombre',
        ]);

        $checklist->update($data);
    }
}
