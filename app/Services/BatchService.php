<?php

namespace App\Services;
use App\Models\Report;
use Illuminate\Support\Facades\Mail;
use App\Mail\Report\MailReport;

class BatchService {
    
    /**
     * Constructs a new service
     *
     */
    public function __construct() 
    {
        //
    } 

    /**
     * Returns an array of eligible reports based on the date given
     * 
     * @param String $year : YYYY formatted year
     * @param String $month : MM formatted month
     * @return Array containing status and message and result set
     */
    public function getEligibleReports($year, $month)
    {
        // Create and verify the date
        $reportDate = date('Y-m-d', strtotime($year . '/' . $month . '/01'));

        if ($reportDate == false) 
        {
            return Array(
                'status' => false,
                'message' => 'Input date is incorrect'
            );
        }

        // Create end date
        $reportEndDate = date('Y-m-d', strtotime('last day of ' . $reportDate));

        // Get all reports that fall within the date range
        $reports = Report::where('start_date', $reportDate) // Within start date
            ->where('end_date', $reportEndDate)             // Within end date
            ->where('report_id', null)                      // Is not a comparison report
            ->get();

        // Filter out any reports that are not eligible for batch emailing
        $eligibleReports = $reports->filter(function($report) {
            return $report->isEligibleForBatchEmail();
        });

        // Check if we have any reports left
        if ($eligibleReports->count() === 0)
        {
            return Array(
                'status' => false,
                'message' => 'No eligible reports for given month/year'
            );
        }

        // Return reports
        return Array(
            'status' => true,
            'reports' => $eligibleReports->sortBy('property.name')
        );

    }

    /**
     * Sends batch emails for given reports
     * 
     * @param Collection[Reports] : Collection containing eligible reports
     */
    public function sendBatchEmails($reports) 
    {
        
        // Loop through and send the reports
        foreach ($reports as $report)
        {
            // Mail the report
            Mail::to($report->property->getClientEmailArray())
                ->bcc(config('mail.report_bcc'))
                ->queue( new MailReport($report) );

            // Set the report as having been mailed, so it's not re-mailed later
            $report->email_sent = true;
            $report->save();
        }

    }
    
}