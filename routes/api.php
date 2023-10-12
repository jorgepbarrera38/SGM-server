<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Device;
use App\Models\Dependence;
use App\Models\Responsible;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('parameters', function () {
    $APP_URL = env('APP_URL');
    return [
        'APP_URL' => $APP_URL,
    ];
});

Route::middleware('auth')->group(function () {

        Route::get('dashboard/get-quantities', function () {
            $devicesQuantity = Device::where('active', true)->count();
            $dependencesQuantity = Dependence::count();
            $responsiblesQuantity = Responsible::count();
            return [
                'devicesQuantity' => $devicesQuantity,
                'dependencesQuantity' => $dependencesQuantity,
                'responsiblesQuantity' => $responsiblesQuantity,
            ];
        });

        Route::get('groups/{group}/get-users-to-authorize', 'GroupController@getGroupWithUsersToAuthorize');
        Route::post('groups/{group}/authorize-users-in-group', 'GroupController@authorizeUsersInGroup');
        Route::apiResource('groups', 'GroupController');
        Route::apiResource('elements', 'ElementController');
        Route::apiResource('locations', 'LocationController');
        Route::apiResource('dependences', 'DependenceController');
        Route::apiResource('responsibles', 'ResponsibleController');
        Route::apiResource('brands', 'BrandController');
        
        Route::put('devices/{device}/update-internal-code', 'DeviceController@updateInternalCode');
        Route::get('devices/get-data-to-create-device', 'DeviceController@getDataToCreateDevice');
        Route::post('devices/{device}/down', 'DeviceController@down');
        Route::get('devices/{device}/edit', 'DeviceController@edit');
        Route::get('devices/get-data-to-filter-search', 'DeviceController@getDataToFilterSearch');
        Route::apiResource('devices', 'DeviceController');
        Route::post('devices/{device}/upload-photographic-record', 'DeviceController@uploadPhotographicRecord');
        Route::get('devices/{device}/get-photographic-record', 'DeviceController@getPhotographicRecord');
        Route::post('devices/{photographicRecord}/select-as-principal', 'DeviceController@selectAsPrincipal');
        Route::delete('devices/{photographicRecord}/delete-photographic-record', 'DeviceController@deletePhotographicRecord');
        Route::get('devices/{device}/get-resume', 'DeviceController@getResume');
        
        Route::post('movements/{device}', 'MovementController@store');
        Route::get('movements/get-data-to-create-movement/{device}', 'MovementController@getDataToCreateMovement');
        
        Route::post('preventive-maintenances/get-blob-from-photographic-annexe', function (Request $request) {
            $imageInBlob = file_get_contents(public_path() . '/files/' . $request->image);
            return $imageInBlob;
        });

        Route::get('preventive-maintenances/{preventiveMaintenance}/edit', 'PreventiveMaintenanceController@edit');
        Route::put('preventive-maintenances/{preventiveMaintenance}', 'PreventiveMaintenanceController@update')->name('preventives.update');
        
        Route::get('preventive-maintenances/{device}/get-data-to-create-preventive-maintenance', 'PreventiveMaintenanceController@getDataToCreatePreventiveMaintenance');
        
        Route::post('preventive-maintenances/{device}', 'PreventiveMaintenanceController@store');
        Route::delete('preventive-maintenances/{preventiveMaintenance}', 'PreventiveMaintenanceController@destroy');

        Route::get('preventive-maintenances/{preventiveMaintenance}/get-evidence-from-preventive-maintenance', 'PreventiveMaintenanceController@getEvidenceFromPreventiveMaintenance');
        
        Route::apiResource('type-other-maintenances', 'TypeOtherMaintenanceController');
        
        Route::get('other-maintenances/get-data-to-create-other-maintenance', 'OtherMaintenanceController@getDataToCreateOtherMaintenance');
        Route::get('other-maintenances/{otherMaintenance}', 'OtherMaintenanceController@show');
        Route::post('other-maintenances/{device}', 'OtherMaintenanceController@store');
        Route::put('other-maintenances/{otherMaintenance}', 'OtherMaintenanceController@update')->name('otherMaintenances.update');
        Route::delete('other-maintenances/{otherMaintenance}', 'OtherMaintenanceController@destroy');
        Route::get('other-maintenances/{otherMaintenance}/get-evidence-from-other-maintenance', 'OtherMaintenanceController@getEvidenceFromOtherMaintenance');
        
        Route::get('documents/get-data-to-create-document', 'DocumentController@getDataToCreateDocument');
        Route::post('documents/{device}', 'DocumentController@store');
        Route::delete('documents/{document}', 'DocumentController@destroy');
        
        Route::get('down-devices', 'DownDeviceController@index');
        
        Route::get('schedule-maintenances', 'ScheduleMaintenanceController@index');
        Route::get('schedule-maintenances/get-data-to-create-schedule-maintenance', 'ScheduleMaintenanceController@getDataToCreateScheduleMaintenance');
        Route::get('schedule-maintenances/{scheduleMaintenance}/export-excel', 'ScheduleMaintenanceController@exportExcel');
        Route::get('schedule-maintenances/{scheduleMaintenance}/export-pdf', 'ScheduleMaintenanceController@exportPdf');
        Route::get('schedule-maintenances/{scheduleMaintenance}', 'ScheduleMaintenanceController@show');
        Route::post('schedule-maintenances', 'ScheduleMaintenanceController@store');
        Route::delete('schedule-maintenances/{scheduleMaintenance}', 'ScheduleMaintenanceController@destroy');
        Route::delete('schedule-maintenances/{scheduleMaintenance}/finish', 'ScheduleMaintenanceController@finish');
        Route::put('schedule-maintenances/{scheduleMaintenanceDetail}/reschedule-detail', 'ScheduleMaintenanceController@rescheduleDetail');
        Route::post('schedule-maintenances/{scheduleMaintenanceDetail}/delete-detail', 'ScheduleMaintenanceController@deleteDetail');
        
        Route::apiResource('replacements', 'ReplacementController');
        
        Route::get('reports/maintenances', 'ReportController@maintenances');
        Route::get('reports/get-users-technical-support-and-groups', 'ReportController@getUsersTechnicalSupportAndGroups');
        Route::get('reports/maintenances-export-pdf', 'ReportController@maintenancesExportPdf');
        Route::get('reports/maintenances-export-excel', 'ReportController@maintenancesExportExcel');
        Route::get('reports/replacements-used', 'ReportController@replacementsUsed');
        Route::get('reports/replacements-used-excel', 'ReportController@replacementsUsedExportExcel');
        
        Route::put('periods/{period}/update-state', 'PeriodController@updateState');
        Route::apiResource('periods', 'PeriodController');
        
        Route::get('maintenances/get-data-to-create-other-maintenance', 'MaintenanceController@getDataToCreateOtherMaintenance');
        Route::post('maintenances', 'MaintenanceController@store')->name('maintenances.store');
        
        Route::put('users/{user}/update-permissions', 'UserController@updatePermissions');    
        Route::get('users/{user}/get-permissions', 'UserController@getPermissions');    
        Route::apiResource('users', 'UserController');
        
        Route::apiResource('company', 'CompanyController')->only(['index', 'store']);
        
        Route::apiResource('type-documents', 'TypeDocumentController');

        Route::apiResource('failure-modes', 'FailureModeController');

        Route::get('checklists', 'ChecklistController@index')->name('checklists.index');
        Route::get('checklists/{checklist}/edit', 'ChecklistController@edit')->name('checklists.edit');
        Route::post('checklists', 'ChecklistController@store')->name('checklists.store');
        Route::delete('checklists/{checklist}', 'ChecklistController@destroy')->name('checklists.destroy');
        Route::post('checklists/{checklist}/add-group', 'ChecklistController@addGroup')->name('checklists.addGroup');
        Route::post('checklists/{checklist}/add-item', 'ChecklistController@addItem')->name('checklists.addItem');
        Route::delete('checklists/{checklistItem}/delete-item', 'ChecklistController@deleteItem')->name('checklists.deleteItem');
        Route::delete('checklists/{checklistGroup}/delete-group', 'ChecklistController@deleteGroup')->name('checklists.deleteGroup');
        Route::put('checklists/{checklist}/update-checklist', 'ChecklistController@updateChecklist')->name('checklists.updateChecklist');

});

Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');

});
