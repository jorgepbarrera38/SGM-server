<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function __construct () {
        $this->middleware('has.permission:company.index')->only('index');
        $this->middleware('has.permission:company.update')->only('store');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if ($company = Company::first()) {
            return $company;
        } else {
            abort(404);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'logo' => 'nullable|mimes:jpg,jpeg',
            'reason_preventive_maintenance_report_digital' => 'required|max:500',
            'complement_title' => 'nullable|max:15',
        ], [], [
            'logo' => 'Logo',
            'reason_preventive_maintenance_report_digital' => 'Motivo de servicio para mantenimientos preventivos',
            'complement_title' => 'Complemento título',
        ]);

        if ($request->hasFile('logo')) {
            $pathLogo = Storage::putFile('company', $request->logo);
        }        

        if ($company = Company::first()) {

            if ($request->hasFile('logo')) {
                if ($company->logo) {
                    if (Storage::exists($company->logo)) {
                        Storage::delete($company->logo);
                    }
                }
                $company->logo = $pathLogo;
            }
    
            $company->reason_preventive_maintenance_report_digital = $request->reason_preventive_maintenance_report_digital;
            $company->complement_title = $request->complement_title;
            $company->save();
            
        } else {
            $company = (new Company)->fill(['reason_preventive_maintenance_report_digital' => $request->reason_preventive_maintenance_report_digital,
                                            'complement_title' => $request->complement_title]);
                                            
            if ($request->hasFile('logo')) {
                $company->logo = $pathLogo;
            }
            $company->save();
        }

        return response()->json(['message', 'Company updated'], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function show(Company $company)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Company $company)
    {
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function destroy(Company $company)
    {
        //
    }
}
