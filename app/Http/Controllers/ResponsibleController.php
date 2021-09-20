<?php

namespace App\Http\Controllers;

use App\Models\Responsible;
use Illuminate\Http\Request;

class ResponsibleController extends Controller
{
    public function __construct () {
        $this->middleware('has.permission:responsibles.index')->only('index');
        $this->middleware('has.permission:responsibles.create')->only('store');
        $this->middleware('has.permission:responsibles.edit')->only('update');
        $this->middleware('has.permission:responsibles.destroy')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Responsible::identificationOrFullNames(request('search'))
                            ->orderBy('fullnames')
                            ->paginate(10);
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
            'identification' => 'required|max:15|unique:responsibles,identification',
            'names' => 'required|max:40',
            'lastnames' => 'required|max:40'
        ], [], [
            'identification' => 'Identificación',
            'names' => 'Nombres',
            'lastnames' => 'Apellidos'
        ]);

        $data['fullnames'] = $data['names'] . ' ' . $data['lastnames'];

        Responsible::create($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Responsible  $responsible
     * @return \Illuminate\Http\Response
     */
    public function show(Responsible $responsible)
    {
        return $responsible;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Responsible  $responsible
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Responsible $responsible)
    {
        $data = $request->validate([
            'identification' => 'required|max:15|unique:responsibles,identification,' . $responsible->id,
            'names' => 'required|max:40',
            'lastnames' => 'required|max:40'
        ], [], [
            'identification' => 'Identificación',
            'names' => 'Nombres',
            'lastnames' => 'Apellidos'
        ]);

        $data['fullnames'] = $data['names'] . ' ' . $data['lastnames'];

        $responsible->update($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Responsible  $responsible
     * @return \Illuminate\Http\Response
     */
    public function destroy(Responsible $responsible)
    {
        $responsible->delete();
    }
}
