<?php

namespace App\Mail\Report;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailReport extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The report details
     * 
     * @var App\Models\Report
     */
    protected $clientHelloLine = 'Hello [Client Name or Blank],';
    protected $reportDate = '2022-01-01';
    protected $reportUrl = 'https://ReportURL.com/ReportID';

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Report $report = NULL)
    {
        if ($report !== NULL)
        {
            $this->clientHelloLine = $report->property->getHelloLine();
            $this->reportDate = $report->start_date;
            $this->reportUrl = route('report.view', $report->id);
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $reportMonth = date('F', strtotime($this->reportDate));
        return $this->view('emails.reports.send')
            ->subject($reportMonth . ' SEO Report')
            ->with([
                'clientHelloLine' => $this->clientHelloLine,
                'reportUrl' => $this->reportUrl,
                'reportMonth' => $reportMonth
            ]);
    }
}
