<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Property;
use Illuminate\Http\Request;
use App\Http\Requests\Report\GenerateReportRequest;
use App\Http\Requests\Report\BatchReportRequest;
use App\Services\ReportService;
use App\Jobs\GenerateReport;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;

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
        if ( $request->session()->has('access_token') ) 
        {
            $this->reportService = new ReportService( $request->session()->get('access_token') );
            $this->reportService->prepServices();
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

        // Generate parent report
        if ($data['status']) {
            $parentReport = $this->reportService->createReport($data);
        } else {
            return back()->with('error', $data['error']);
        }

        // Calculate time and get data for comparison report
        $startTime = strtotime($input['start-date']);
        $endTime = strtotime($input['end-date']);
        $timeDifference = $endTime - $startTime;
        $comparisonEnd = $startTime - 86400; // 1 day before report start time
        $comparisonStart = $comparisonEnd - $timeDifference;

        $comparisonData = $this->reportService->getReportData($parentProperty, date('Y-m-d', $comparisonStart), date('Y-m-d', $comparisonEnd), $input['exp-date']);

        // Generate comparison report
        if ($comparisonData['status']) {
            $comparisonReport = $this->reportService->createReport($comparisonData);
            $parentReport->comparisonReport()->save($comparisonReport);
        }
        
        // Redirect user back to the profile page
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
        // Delete report & comparison report
        $report = Report::findOrFail($id);

        if ($report->comparisonReport !== null) {
            $report->comparisonReport->delete();
        }

        $report->delete();
        return back()->with('success', 'The report has been deleted.');

    }

    /**
     * Return view for batch report generation
     * 
     */
    public function batchGenerateView()
    {
        return view('reports.batch-generate', [ 'properties' => Property::all() ]);
    }

    /**
     * Handles new report generation batch creation
     * 
     * @param $request App\Http\Requests\Report\BatchReportRequest
     */
    public function batchGenerateCreate(BatchReportRequest $request)
    {

        // Test credentials
        if ($this->reportService == null) {
            return back()->with('error', 'Google API access could not be verified. Please reapply for an OAuth2 token.');
        }

        $input = $request->validated();
        $reportDate = $input['year'] . '-' . $input['month'] . '-01';
        $jobs = [];
        $properties = Property::all();

        foreach ($properties as $property)
        {
            $jobs[] = new GenerateReport( 
                $property, 
                $reportDate, 
                new ReportService( $request->session()->get('access_token') ) 
            );
        }
       
        if (empty($jobs)) {
            return back()->with('error', 'No schedules to process.');
        }

        $batch = Bus::batch(
            $jobs
        )->allowFailures()->onQueue('reports')->dispatch();

        return back()->with('success', 'Batch created.');

    }

    /**
     * Views a currently running batch report
     * 
     */
    public function viewBatchReportJob($id)
    {
        return view('reports.view-report-batch', [ 'batch' => Bus::findBatch($id) ]);
    }

}
