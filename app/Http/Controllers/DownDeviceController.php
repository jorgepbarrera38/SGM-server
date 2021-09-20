<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;

class DownDeviceController extends Controller
{
    public function __construct () {
        $this->middleware('has.permission:devices.down.index')->only('index');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Device::internalCodeOrSerial(request('code-or-serial'))
                    ->where('active', false)
                    ->with('element')
                    ->with('dependence')
                    ->with('responsible')
                    ->with('downUser')
                    ->select('id', 'element_id', 'internal_code', 'serial_number', 'dependence_id', 'responsible_id', 'description', 'observations', 'down_date', 'down_evidence', 'down_user_id')
                    ->orderBy('id')
                    ->paginate(15);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
