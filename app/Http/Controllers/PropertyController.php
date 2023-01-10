<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\Report;
use App\Http\Requests\Property\StorePropertyRequest;
use App\Http\Requests\Property\UpdatePropertyRequest;
use App\Http\Requests\Property\UpdateEmailFieldsRequest;
use App\Services\PropertyService;
use App\Mail\Report\MailReport;

class PropertyController extends Controller
{
    protected $propertyService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PropertyService $propertyService)
    {
        $this->middleware('auth');
        $this->propertyService = $propertyService;
    }

    /**
     * Individual property index page
     * 
     * @var int $id - Property ID
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index($id) 
    {
        $property = Property::findOrFail($id);
        return view('properties.property', [ 
            'property' => $property,
            'reports' => $property->reports()->orderByDesc('created_at')->where('report_id', NULL)->get() 
        ]);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function newIndex()
    {
        return view('properties.add-property');
    }

    /**
     * Store a property in the database
     * 
     * @param App\Http\Requests\Property\StorePropertyRequest
     */
    public function newStore(StorePropertyRequest $request) 
    {
        $validated = $request->validated();
        $response = $this->propertyService->create($validated);

        // Check response
        if ($response) {
            return back()->with('success', 'Property has been created!');
        } else {
            return back()->with('error', 'Access could not be verified. Please confirm you entered the Analytics Property ID correctly, and that the Analytics Service User has been added to the property with admin permissions.');
        }

    }

    /**
     * Handles update request for properties
     * 
     * @param Integer : Property ID
     * @param App\Http\Requests\Property\UpdatePropertyRequest
     */
    public function update($id, UpdatePropertyRequest $request)
    {
        $validated = $request->validated();
        $this->propertyService->update($id, $validated);
        return back()->with('success', 'Property has been updated!');
    }

    /**
     * Handles a property deletion request
     * 
     * @param Integer : Property ID
     */
    public function delete($id)
    {
        $this->propertyService->delete($id);
        return redirect()->route('home')->with('success', 'Property has been deleted');
    }

    /**
     * Enables batch email setting
     * 
     * @param Integer $id : The target property's ID
     */
    public function enableBatchEmail($id) 
    {
        $this->propertyService->enableBatchEmail($id);
        return back()->with('success', 'Batch email has been enabled');
    }

    /**
     * Disables batch email setting
     * 
     * @param Integer $id : The target property's ID
     */
    public function disableBatchEmail($id) 
    {
        $this->propertyService->disableBatchEmail($id);
        return back()->with('success', 'Batch email has been disabled');
    }

    /**
     * Updates fields related to batch email settings
     * 
     * @param App\Http\Requests\Property\UpdateEmailFieldsRequest $request
     */
    public function updateBatchEmailSettings(UpdateEmailFieldsRequest $request, $id) 
    {
        $input = $request->validated();
        $this->propertyService->updateBatchEmailSettings($id, $input['client_name'], $input['client_email']);
        return back()->with('success', 'Batch email settings have been updated');
    }

    /**
     * Returns a preview of the property's mailable report
     * 
     * @param Integer $id : The property's ID
     */
    public function previewReportEmail($id, $reportId = NULL)
    {
        $property = Property::findOrFail($id);

        if ($reportId !== NULL) 
        {
            $report = Report::findOrFail($reportId);
            return new MailReport($report);
        }
        return new MailReport();
    }

    /**
     * Toggles a property's dark logo background option
     * 
     * @param Integer $id : The target property's ID
     */
    public function toggleDarkLogoBackground($id) 
    {
        $this->propertyService->toggleDarkLogoBackground($id);
        return back()->with('success', 'Background option has been toggled.');
    }

}
