<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Batchable;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use App\Services\ReportService;
use App\Models\Property;

class GenerateReport implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var App\Models\Property the parent property for the report
     * 
     */
    public $parentProperty;

    /**
     * @var String the date of the report we're generating
     * 
     */
    public $reportDate;
    public $reportEndDate;

    /**
     * @var App\Services\ReportService the report service for report generation
     * 
     */
    public $reportService;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Property $property, $date, $service)
    {
        $this->parentProperty = $property;
        $this->reportDate = $date;
        $this->reportService = $service;
        $this->reportEndDate = date('Y-m-d', strtotime('last day of ' . $date));
        $this->onQueue('reports');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Check if batch is cancelled
        if ($this->batch()->cancelled()) {
            return;
        }

        // Check if the property already has a report for the current timerange
        $currentPeriodReports = $this->parentProperty->reports()
            ->where('start_date', $this->reportDate)
            ->where('end_date', $this->reportEndDate)
            ->get();
        
        // Bail if we do not need to generate a new report
        if ($currentPeriodReports->count() > 0) {
            return;
        }

        // Prep the API services
        $this->reportService->prepServices();

        // Grab the input data and send off the request
        $data = $this->reportService->getReportData(
            $this->parentProperty, 
            $this->reportDate, 
            $this->reportEndDate
        );

        // Generate parent report
        if ($data['status']) {
            $parentReport = $this->reportService->createReport($data);
        } else {
            return;
        }

        // Calculate time and get data for comparison report
        $startTime = strtotime($this->reportDate);
        $endTime = strtotime($this->reportEndDate);
        $timeDifference = $endTime - $startTime;
        $comparisonEnd = $startTime - 86400; // 1 day before report start time
        $comparisonStart = $comparisonEnd - $timeDifference;

        $comparisonData = $this->reportService->getReportData(
            $this->parentProperty, 
            date('Y-m-d', $comparisonStart), 
            date('Y-m-d', $comparisonEnd), 
        );

        // Generate comparison report
        if ($comparisonData['status']) {
            $comparisonReport = $this->reportService->createReport($comparisonData);
            $parentReport->comparisonReport()->save($comparisonReport);
        }

        // Redirect user back to the profile page
        return;

    }
}
