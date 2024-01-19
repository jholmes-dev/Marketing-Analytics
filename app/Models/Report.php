<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;
    use HasUlids;

    /**
     * The guarded attributes
     * 
     */
    protected $guarded = [];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'email_sent' => false,
    ];
    
    /**
     * Belongs to function for the parent Property
     * 
     */
    public function property() 
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Belongs to function for comparison reports
     * 
     */
    public function comparisonReport()
    {
        return $this->hasOne(Report::class);
    }

    /**
     * Belongs to function for the parent report to a comparison report
     * 
     */
    public function parentReport()
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * Returns if the report is a comparison report
     * 
     * @return Boolean
     */
    public function isComparisonReport()
    {
        return (!$this->report_id === NULL);
    }

    /**
     * Determines if the current report has a valid comparison report
     * 
     * @return Boolean
     */
    public function hasValidComparisonReport()
    {
        return !($this->comparisonReport === null);
    }

    /**
     * Returns if the report is expired
     * 
     * @return Boolean
     */
    public function isExpired()
    {
        // Not expired if expiration date is not set
        if ($this->exp_date === NULL) 
        {
            return false;
        }

        // Compare today to expiration date
        $today = strtotime(date('Y/m/d'));
        $expDate = strtotime($this->exp_date);

        return ($today >= $expDate);
    }

    /**
     * Returns a formatted string for use in the report template
     * 
     * @param String $name : The name of the database table that we're pulling data for
     * @param Boolean $date : If we need to format the return data as a date
     * @return Array - index 1 is the keys, index 2 is the values
     */
    public function getFormattedArray($name, $date = false) 
    {

        // Key data
        $returnKeys = '';

        $arrayKeys = $this->getDatabaseArrayKeys($name);
        foreach ($arrayKeys as $k => $dsk) 
        {
            $returnKeys .= "'";
            if ($date) {
                $returnKeys .= strtotime($dsk);
            } else {
                $returnKeys .= $dsk;
            }
            $returnKeys .= "', ";
        }

        // Value data
        $returnValues = implode(',', $this->getDatabaseArrayValues($name));

        // Return data
        return [ substr_replace($returnKeys, "", -2), $returnValues ];        
    }

    /**
     * Returns the formatted session graph data. This function is separate from
     * getFormattedArray and necessary due to Google Analytics API not returning 
     * any data for days with 0 sessions.
     * 
     * This function fills in any missing days with blank data sets to the comparison
     * report graph will match up correctly.
     * 
     * @return Array - index 1 is the keys, index 2 is the values
     */
    public function getFormattedSessionData() 
    {
        // Return data
        $returnKeys = '';
        $returnValues = [];

        // Retrieve keys and values to loop through
        $arrayKeys = $this->getDatabaseArrayKeys("date_session");
        $arrayValues = $this->getDatabaseArrayValues("date_session");

        // Iterators for start and end times for the report
        $iterDate = strtotime($this->start_date);
        $endDate = strtotime($this->end_date);

        // Iterator for the current index of the values array
        $valIterator = 0;

        // Loop through each key, if there is a date missing fill it in
        foreach ($arrayKeys as $k => $dsk) 
        {
            // While dates are missing, create dates and add to results
            while ($iterDate !== strtotime($dsk) && $iterDate <= $endDate)
            {   
                $returnKeys .= "'";
                $returnKeys .= date("M, d", $iterDate);
                $returnKeys .= "', ";

                array_push($returnValues, 0);
                $iterDate = strtotime("+ 1 day", $iterDate);
            }
            
            // Add current day key
            $returnKeys .= "'";
            $returnKeys .= date("M, d", strtotime($dsk));
            $returnKeys .= "', ";

            // Add current key key
            array_push($returnValues, $arrayValues[$valIterator]);
            $valIterator++;

            // Increment date iterator
            $iterDate = strtotime("+ 1 day", $iterDate);
        }

        // Value data
        $returnValues = implode(',', $returnValues);

        // Return data
        return [ substr_replace($returnKeys, "", -2), $returnValues ];    
    }

    /**
     * Returns keys of a database column
     * 
     * @var String $name - The name of a databse column to retrieve
     */
    public function getDatabaseArrayKeys($name) 
    {

        $dateSessionData = unserialize($this[$name]);
        return array_keys( $dateSessionData );
    }

    /**
     * Returns values of a database column
     * 
     * @var String $name - The name of a databse column to retrieve
     */
    public function getDatabaseArrayValues($name) 
    {
        $dateSessionData = unserialize($this[$name]);
        return array_values( $dateSessionData );
    }

    /**
     * Returns a display-ready string of comparison HTML for direct
     * display in a report. For data point: Total Users
     * 
     * @return String HTML
     */
    public function getUsersComparisonString()
    {
        if (!$this->hasValidComparisonReport()) {
            return '';
        }
        return $this->formatDataToHtml($this->total_users, $this->comparisonReport->total_users);
    }

    /**
     * Returns a display-ready string of comparison HTML for direct
     * display in a report. For data point: Sessions
     * 
     * @return String HTML
     */
    public function getSessionsComparisonString()
    {
        if (!$this->hasValidComparisonReport()) {
            return '';
        }
        return $this->formatDataToHtml($this->sessions, $this->comparisonReport->sessions);
    }

    /**
     * Returns a display-ready string of comparison HTML for direct
     * display in a report. For data point: Page Views
     * 
     * @return String HTML
     */
    public function getViewsComparisonString()
    {
        if (!$this->hasValidComparisonReport()) {
            return '';
        }
        return $this->formatDataToHtml($this->page_views, $this->comparisonReport->page_views);
    }

    /**
     * Returns a display-ready string of comparison HTML for direct
     * display in a report. For data point: Engagement Rate
     * 
     * @return String HTML
     */
    public function getEngagementRateComparisonString()
    {
        if (!$this->hasValidComparisonReport()) {
            return '';
        }
        return $this->formatDataToHtml($this->engagement_rate, $this->comparisonReport->engagement_rate);
    }

    /**
     * Returns a display-ready string of comparison HTML for direct
     * display in a report. For data point: Events Per Session
     * 
     * @return String HTML
     */
    public function getEventsPerSessionComparisonString()
    {
        if (!$this->hasValidComparisonReport()) {
            return '';
        }
        return $this->formatDataToHtml($this->events_per_session, $this->comparisonReport->events_per_session);
    }

    /**
     * Returns a display-ready string of comparison HTML for direct
     * display in a report. For data point: Sessions Per User
     * 
     * @return String HTML
     */
    public function getSessionsPerUserComparisonString()
    {
        if (!$this->hasValidComparisonReport()) {
            return '';
        }
        return $this->formatDataToHtml($this->sessions_per_user, $this->comparisonReport->sessions_per_user);
    }

    /**
     * Helper function for returning formatted HTML from two comparison values
     * 
     * @param Integer $current : The current month's value
     * @param Integer $previous : The previous, or comparison month's value
     * @return String HTML : A formatted string of HTML for direct display
     */
    public function formatDataToHtml($current, $previous)
    {

        if ($current > $previous) {
            $comparisonArrow = '<i class="bi bi-arrow-up-short"></i>';
            $comparisonColor = 'text-success';
        } else {
            $comparisonArrow = '<i class="bi bi-arrow-down-short"></i>';
            $comparisonColor = 'text-danger';
        }

        $comparisonPercent = ($previous > 0) ? round(abs(1 - ($current / $previous)), 4) : $current;

        return '<span class="' . $comparisonColor . '">' . $comparisonArrow . (String)($comparisonPercent * 100) . '%</span>';

    }

    /**
     * Determines if the report is eligible for batch report emails
     * 
     * @return Boolean
     */
    public function isEligibleForBatchEmail()
    {
        // Needs to have batch email enabled
        if (!$this->property->batch_email)
        {
            return false;
        }

        // Needs to have valid batch email fields
        if ($this->property->client_email === NULL || count($this->property->getClientEmailArray()) == 0)
        {
            return false;
        }

        // Needs to not have already had its report sent already
        if ($this->email_sent)
        {
            return false;
        }

        // Needs to not be a comparison report
        if ($this->isComparisonReport())
        {
            return false;
        }

        // Needs to not be expired
        if ($this->isExpired())
        {
            return false;
        }

        // Return true if all checks passed
        return true;

    }

}
