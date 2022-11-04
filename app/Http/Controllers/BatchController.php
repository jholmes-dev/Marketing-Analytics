<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\Report;
use App\Mail\Report\MailReport;
use App\Services\BatchService;

class BatchController extends Controller
{

    /**
     * The service helper
     * 
     */
    protected $batchService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(BatchService $service)
    {
        $this->middleware('auth');
        $this->batchService = $service;
    }

    /**
     * Displays view for generating an email list
     * 
     */
    public function generateEmailListView()
    {
        return view('batch.generate-email-list');
    }

    /**
     * Redirects user to their chosen year/month
     * 
     * @param Illuminate\Http\Request
     */
    public function generateEmailListRedirect(Request $request)
    {
        $input = $request->validate([
            'month' => 'required|string|in:01,02,03,04,05,06,07,08,09,10,11,12',
            'year' => 'required|integer'
        ]);
        return redirect()->route('batch.email.view', [ $input['year'], $input['month'] ]);
    }

    /**
     * Returns a list of emails eligible for the given date
     * 
     * @param String $year
     * @param String $month
     */
    public function showBatchEmailList($year, $month) 
    {        
        $reportData = $this->batchService->getEligibleReports($year, $month);

        if ($reportData['status'] === false)
        {
            return redirect()->route('batch.email.generate')->with('error', $reportData['message']);
        }

        return view('batch.view-email-list', [ 
            'reports' => $reportData['reports'],
            'month' => $month,
            'year' => $year
        ]);
    }

    /**
     * Sends batch emails for the given month and year
     * 
     * @param Illuminate\Http\Request
     */
    public function sendBatchEmails(Request $request)
    {
        $input = $request->validate([
            'month' => 'required|string|in:01,02,03,04,05,06,07,08,09,10,11,12',
            'year' => 'required|integer'
        ]);

        // Get reports
        $reportData = $this->batchService->getEligibleReports($input['year'], $input['month']);
        if ($reportData['status'] === false)
        {
            return redirect()->route('batch.email.generate')->with('error', $reportData['message']);
        }

        // Send reports
        $this->batchService->sendBatchEmails($reportData['reports']);

        // Return user to home with message
        return redirect()->route('home')->with('success', 'Batch reports sent!');

    }

}
