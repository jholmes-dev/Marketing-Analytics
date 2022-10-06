<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Property;
use Illuminate\Http\Request;
use App\Http\Requests\Report\GenerateReportRequest;
use App\Services\ReportService;

class ReportController extends Controller
{
    protected $reportService = null;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {

        // Check if the access token exists
        if ( $request->session()->has('access_token') ) {
            $this->reportService = new ReportService( $request->session()->get('access_token') );
        }
        
    }

    /**
     * Generates a report for the provided property
     * 
     * @var App\Http\Requests\Report\GenerateReportRequest
     * @var int $id - Property ID
     */
    public function generateReport(GenerateReportRequest $request, $id) 
    {

        // Test credentials
        if ($this->reportService == null) {
            return back()->with('error', 'Google API access could not be verified. Please reapply for an OAuth2 token.');
        }

        // Grab the parent property
        $parentProperty = Property::findOrFail($id);

        // Grab the input data and send off the request
        $input = $request->validated();
        $data = $this->reportService->getReportData($parentProperty, $input['start-date'], $input['end-date'], $input['exp-date']);

        // Generate report
        if ($data['status']) {
            $report = $this->reportService->createReport($data);
        } else {
            return back()->with('GoogleAPIErrors', $data['error']->getErrors());
        }

        // Redirect user to the report
        return back()->with('success', 'The report has been successfully generated!');

    }

    /**
     * Returns a report for viewing
     * 
     * @var Int $id - The report ID
     */
    public function getReport($id) 
    {

        $report = Report::findOrFail($id);
        return view('reports.view-report', [ 'report' => $report, 'property' => $report->property ]);

    }

    /**
     * Deletes a report
     * 
     * @var Int $id - The report ID
     */
    public function deleteReport($id) 
    {

        $report = Report::findOrFail($id);
        $report->delete();
        return back()->with('success', 'The report has been deleted.');

    }

}
