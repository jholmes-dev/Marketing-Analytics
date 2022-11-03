<?php

namespace App\Services;

use App\Models\Report;
use App\Models\Property;
use App\Services\GoogleOAuthService;

use Google\Client;

use Google\Service\AnalyticsData;
use Google\Service\AnalyticsData\DateRange;
use Google\Service\AnalyticsData\Dimension;
use Google\Service\AnalyticsData\Metric;
use Google\Service\AnalyticsData\Filter;
use Google\Service\AnalyticsData\FilterExpression;
use Google\Service\AnalyticsData\StringFilter;
use Google\Service\AnalyticsData\RunReportRequest;
use Google\Service\AnalyticsData\RunReportResponse;
use Google\Service\AnalyticsData\OrderBy;
use Google\Service\AnalyticsData\MetricOrderBy;
use Google\Service\AnalyticsData\DimensionOrderBy;

use Google\Service\SearchConsole;
use Google\Service\SearchConsole\SearchAnalyticsQueryRequest;

class ReportService {
    
    /**
     * @var String the Google API access token
     * 
     */
    public $token;

    /**
     * @var Google\Client
     * 
     */
    public $client;

    /**
     * @var Google\Service\AnalyticsData
     * 
     */
    public $analyticsService;

    /**
     * @var Google\Service\AnalyticsData
     * 
     */
    public $searchConsoleService;

    /**
     * Report variables
     * 
     * @var Int
     * @var String YYYY-mm-dd
     * @var String YYYY-mm-dd
     */
    public $analyticsId;
    public $websiteUrl;
    public $reportStartDate;
    public $reportEndDate;
    public $reportExpDate;

    /**
     * The property object the report will be associated with
     * 
     * @var App\Models\Property
     */
    public $parentProperty;


    /**
     * Constructs a new service
     *
     */
    public function __construct($token) 
    {
        $this->token = $token;
    } 
    
    public function prepServices()
    {
        // Generate Client
        $oauthService = new GoogleOAuthService();
        $this->client = $oauthService->generateOAuthClient();
        $this->client->setAccessToken($this->token);

        // Generate Analytics Service
        $this->analyticsService = new AnalyticsData($this->client);

        // Generate Search Console Service
        $this->searchConsoleService = new SearchConsole($this->client);
    }

    /**
     * Generates a report from the passed data array
     * 
     * @var Array $data - Data array from App\Service\ReportService::getReportData
     * @return App\Models\Report
     */
    public function createReport($data)
    {

        $data = $this->applyDataFilters($data);

        $report = Report::create([
            'property_id' => $this->parentProperty->id,
            'start_date' => $this->reportStartDate,
            'end_date' => $this->reportEndDate,
            'exp_date' => $this->reportExpDate,
            'total_users' => $data['totalUsers'],
            'sessions' => $data['sessions'],
            'page_views' => $data['screenPageViews'],
            'engagement_rate' => $data['engagementRate'],
            'events_per_session' => $data['eventsPerSession'],
            'sessions_per_user' => $data['sessionsPerUser'],
            'date_session' => serialize( $data['dateSessionData'] ),
            'browsers' => serialize( $data['browserData'] ),
            'devices' => serialize( $data['deviceData'] ),
            'channels' => serialize( $data['channelData'] ),
            'pages' => serialize( $data['pageData'] ),
            'cities' => serialize( $data['cityData'] ),
            'queries' => serialize($data['queryData'])
        ]);

        return $report;

    }

    /**
     * Applies any data filters to the dataset and returns the adjusted data
     * 
     * @var Array $data - Data array from App\Service\ReportService::getReportData
     * @return Array
     */
    private function applyDataFilters($data) 
    {

        // Filter out `(not set)` from city data
        foreach ($data['cityData'] as $city => $imp) {
            if ($city == '(not set)') {
                unset($data['cityData'][$city]);
            }
        }

        // Return adjusted data
        return $data;

    }

    /**
     * Compiles an array of all necessary report data
     * 
     * @var App\Models\Property $parentProperty
     * @var String $startDate   - Report start date
     * @var String $endDate     - Report end date
     * @return Array
     */
    public function getReportData($parentProperty, $startDate, $endDate, $expDate = null) 
    {

        // Store input data in object
        $this->parentProperty = $parentProperty;
        $this->analyticsId = $this->parentProperty->analytics_id;
        $this->websiteUrl = $this->parentProperty->url;
        $this->reportStartDate = $startDate;
        $this->reportEndDate = $endDate;
        $this->reportExpDate = $expDate;

        // The final data holder
        $resData = [
            'status' => true
        ];

        // Call and confirm simple metrics
        $simpleMetrics = $this->getSimpleMetrics();
        if (!$simpleMetrics['status']) {
            return $simpleMetrics;
        } else {
            unset($simpleMetrics['status']);
            $resData = array_merge($resData, $simpleMetrics);
        }

        // Call and confirm sessions graph data
        $sessionsGraphData = $this->getSessionsGraphData();
        if (!$sessionsGraphData['status']) {
            return $sessionsGraphData;
        } else {
            unset($sessionsGraphData['status']);
            $resData = array_merge($resData, $sessionsGraphData);
        }

        // Call and confirm browser data
        $browserData = $this->getBrowserData();
        if (!$browserData['status']) {
            return $browserData;
        } else {
            unset($browserData['status']);
            $resData = array_merge($resData, $browserData);
        }

        // Call and confirm device data
        $deviceData = $this->getDeviceData();
        if (!$deviceData['status']) {
            return $deviceData;
        } else {
            unset($deviceData['status']);
            $resData = array_merge($resData, $deviceData);
        }

        // Call and confirm visitor channels data
        $channelData = $this->getVisitorChannels();
        if (!$channelData['status']) {
            return $channelData;
        } else {
            unset($channelData['status']);
            $resData = array_merge($resData, $channelData);
        }

        // Call and confirm page visitor data
        $pageVisitData = $this->getPageVisitData();
        if (!$pageVisitData['status']) {
            return $pageVisitData;
        } else {
            unset($pageVisitData['status']);
            $resData = array_merge($resData, $pageVisitData);
        }

        // Call and confirm visitor city data
        $visitorCityData = $this->getVisitorCityData();
        if (!$visitorCityData['status']) {
            return $visitorCityData;
        } else {
            unset($visitorCityData['status']);
            $resData = array_merge($resData, $visitorCityData);
        }

        // Call and confirm query data
        $queryData = $this->getQueryData();
        if (!$queryData['status']) {
            return $queryData;
        } else {
            unset($queryData['status']);
            $resData = array_merge($resData, $queryData);
        }

        return $resData;

    }

    /**
     * Calls for all simple metrics
     * 
     * @return Array
     */
    public function getSimpleMetrics() 
    {

        try {

            $response = $this->analyticsService->properties->runReport(
                'properties/' . $this->analyticsId,
                new RunReportRequest(
                [
                    'dateRanges' => [
                        new DateRange([
                            'start_date' => $this->reportStartDate,
                            'end_date' => $this->reportEndDate,
                        ]),
                    ],
                    'dimensions' => [
                        new Dimension([
                            'name' => 'country'
                        ])
                    ],
                    'metrics' => [
                        new Metric([
                            'name' => 'totalUsers',
                        ]),
                        new Metric([
                            'name' => 'sessions',
                        ]),
                        new Metric([
                            'name' => 'screenPageViews',
                        ]),
                        new Metric([
                            'name' => 'engagementRate',
                        ]),
                        new Metric([
                            'name' => 'eventsPerSession',
                        ]),
                        new Metric([
                            'name' => 'sessionsPerUser',
                        ])
                    ],
                    'dimensionFilter' => [
                        'filter' => new Filter([
                            'fieldName' => 'country',
                            'stringFilter' => new StringFilter([
                                'value' => 'United States'
                            ])
                        ])
                    ]
                ])
            );

        } catch (\Exception $e) {
            
            return Array(
                'status' => false,
                'error' => $e
            );

        }

        if ($response->rowCount !== null) {
            $responseRow = $response->getRows()[0];

            return Array(
                'status' => true,
                'totalUsers' => $responseRow->getMetricValues()[0]->getValue(),
                'sessions' => $responseRow->getMetricValues()[1]->getValue(),
                'screenPageViews' => $responseRow->getMetricValues()[2]->getValue(),
                'engagementRate' => $responseRow->getMetricValues()[3]->getValue(),
                'eventsPerSession' => $responseRow->getMetricValues()[4]->getValue(),
                'sessionsPerUser' => $responseRow->getMetricValues()[5]->getValue(),
            );

        } else {

            return Array(
                'status' => false,
                'error' => 'No data found for selected date range'
            );

        }

    }
    
    /**
     * Gets all data for sessions graph
     * 
     * @return Array
     */
    private function getSessionsGraphData() 
    {

        try {

            $response = $this->analyticsService->properties->runReport(
                'properties/' . $this->analyticsId,
                new RunReportRequest(
                [
                    'dateRanges' => [
                        new DateRange([
                            'start_date' => $this->reportStartDate,
                            'end_date' => $this->reportEndDate,
                        ]),
                    ],
                    'dimensions' => [
                        new Dimension([
                            'name' => 'date'
                        ]),
                        new Dimension([
                            'name' => 'country'
                        ])
                    ],
                    'metrics' => [
                        new Metric([
                            'name' => 'sessions',
                        ])
                    ],
                    'dimensionFilter' => [
                        'filter' => new Filter([
                            'fieldName' => 'country',
                            'stringFilter' => new StringFilter([
                                'value' => 'United States'
                            ])
                        ])
                    ],
                    'orderBys' => [
                        new OrderBy([
                            'dimension' => new DimensionOrderBy([
                                'dimensionName' => 'date'
                            ])
                        ])
                    ]
                ])
            );

        } catch (\Exception $e) {
            
            return Array(
                'status' => false,
                'error' => $e
            );

        }

        // Return data
        return Array(
            'status' => true,
            'dateSessionData' => $this->parseReportResponse($response, false)
        );

    }

    /**
     * Gets all user browser data
     * 
     * @return Array
     */
    private function getBrowserData() 
    {

        try {

            $response = $this->analyticsService->properties->runReport(
                'properties/' . $this->analyticsId,
                new RunReportRequest(
                [
                    'dateRanges' => [
                        new DateRange([
                            'start_date' => $this->reportStartDate,
                            'end_date' => $this->reportEndDate,
                        ]),
                    ],
                    'dimensions' => [
                        new Dimension([
                            'name' => 'browser'
                        ]),
                        new Dimension([
                            'name' => 'country'
                        ])
                    ],
                    'metrics' => [
                        new Metric([
                            'name' => 'totalUsers',
                        ])
                    ],
                    'dimensionFilter' => [
                        'filter' => new Filter([
                            'fieldName' => 'country',
                            'stringFilter' => new StringFilter([
                                'value' => 'United States'
                            ])
                        ])
                    ],
                    'orderBys' => [
                        new OrderBy([
                            'metric' => new MetricOrderBy([
                                'metricName' => 'totalUsers'
                            ])
                        ])
                    ]
                ])
            );

        } catch (\Exception $e) {
            
            return Array(
                'status' => false,
                'error' => $e
            );

        }

        // Return data
        return Array(
            'status' => true,
            'browserData' => $this->parseReportResponse($response)
        );

    }

    /**
     * Gets all visitor device data
     * 
     * @return Array
     */
    private function getDeviceData() 
    {

        try {

            $response = $this->analyticsService->properties->runReport(
                'properties/' . $this->analyticsId,
                new RunReportRequest(
                [
                    'dateRanges' => [
                        new DateRange([
                            'start_date' => $this->reportStartDate,
                            'end_date' => $this->reportEndDate,
                        ]),
                    ],
                    'dimensions' => [
                        new Dimension([
                            'name' => 'deviceCategory'
                        ]),
                        new Dimension([
                            'name' => 'country'
                        ])
                    ],
                    'metrics' => [
                        new Metric([
                            'name' => 'totalUsers',
                        ])
                    ],
                    'dimensionFilter' => [
                        'filter' => new Filter([
                            'fieldName' => 'country',
                            'stringFilter' => new StringFilter([
                                'value' => 'United States'
                            ])
                        ])
                    ],
                    'orderBys' => [
                        new OrderBy([
                            'metric' => new MetricOrderBy([
                                'metricName' => 'totalUsers'
                            ])
                        ])
                    ]
                ])
            );

        } catch (\Exception $e) {
            
            return Array(
                'status' => false,
                'error' => $e
            );

        }

        // Return data
        return Array(
            'status' => true,
            'deviceData' => $this->parseReportResponse($response)
        );

    }

    /**
     * Gets all visitor acquisition channel data
     * 
     * @return Array
     */
    private function getVisitorChannels() 
    {

        try {

            $response = $this->analyticsService->properties->runReport(
                'properties/' . $this->analyticsId,
                new RunReportRequest(
                [
                    'dateRanges' => [
                        new DateRange([
                            'start_date' => $this->reportStartDate,
                            'end_date' => $this->reportEndDate,
                        ]),
                    ],
                    'dimensions' => [
                        new Dimension([
                            'name' => 'sessionDefaultChannelGrouping'
                        ]),
                        new Dimension([
                            'name' => 'country'
                        ])
                    ],
                    'metrics' => [
                        new Metric([
                            'name' => 'sessions',
                        ])
                    ],
                    'dimensionFilter' => [
                        'filter' => new Filter([
                            'fieldName' => 'country',
                            'stringFilter' => new StringFilter([
                                'value' => 'United States'
                            ])
                        ])
                    ],
                    'orderBys' => [
                        new OrderBy([
                            'metric' => new MetricOrderBy([
                                'metricName' => 'sessions'
                            ])
                        ])
                    ]
                ])
            );

        } catch (\Exception $e) {
            
            return Array(
                'status' => false,
                'error' => $e
            );

        }

        // Return data
        return Array(
            'status' => true,
            'channelData' => $this->parseReportResponse($response)
        );

    }

    /**
     * Gets all most visited page data
     * 
     * @return Array
     */
    private function getPageVisitData() 
    {

        try {

            $response = $this->analyticsService->properties->runReport(
                'properties/' . $this->analyticsId,
                new RunReportRequest(
                [
                    'dateRanges' => [
                        new DateRange([
                            'start_date' => $this->reportStartDate,
                            'end_date' => $this->reportEndDate,
                        ]),
                    ],
                    'dimensions' => [
                        new Dimension([
                            'name' => 'pageTitle'
                        ]),
                        new Dimension([
                            'name' => 'country'
                        ])
                    ],
                    'metrics' => [
                        new Metric([
                            'name' => 'sessions',
                        ])
                    ],
                    'dimensionFilter' => [
                        'filter' => new Filter([
                            'fieldName' => 'country',
                            'stringFilter' => new StringFilter([
                                'value' => 'United States'
                            ])
                        ])
                    ],
                    'orderBys' => [
                        new OrderBy([
                            'metric' => new MetricOrderBy([
                                'metricName' => 'sessions'
                            ])
                        ])
                    ]
                ])
            );

        } catch (\Exception $e) {
            
            return Array(
                'status' => false,
                'error' => $e
            );

        }

        // Return data
        return Array(
            'status' => true,
            'pageData' => array_slice($this->parseReportResponse($response), 0, 15)
        );

    }

    /**
     * Gets page visitor city data
     * 
     * @return Array
     */
    private function getVisitorCityData() 
    {

        try {

            $response = $this->analyticsService->properties->runReport(
                'properties/' . $this->analyticsId,
                new RunReportRequest(
                [
                    'dateRanges' => [
                        new DateRange([
                            'start_date' => $this->reportStartDate,
                            'end_date' => $this->reportEndDate,
                        ]),
                    ],
                    'dimensions' => [
                        new Dimension([
                            'name' => 'city'
                        ]),
                        new Dimension([
                            'name' => 'country'
                        ])
                    ],
                    'metrics' => [
                        new Metric([
                            'name' => 'sessions',
                        ])
                    ],
                    'dimensionFilter' => [
                        'filter' => new Filter([
                            'fieldName' => 'country',
                            'stringFilter' => new StringFilter([
                                'value' => 'United States'
                            ])
                        ]),
                    ],
                    'orderBys' => [
                        new OrderBy([
                            'metric' => new MetricOrderBy([
                                'metricName' => 'sessions'
                            ])
                        ])
                    ]
                ])
            );

        } catch (\Exception $e) {
            
            return Array(
                'status' => false,
                'error' => $e
            );

        }

        // Return data
        return Array(
            'status' => true,
            'cityData' => array_slice($this->parseReportResponse($response), 0, 15)
        );

    }

    /**
     * Gets search console query data
     * 
     * @return Array
     */
    public function getQueryData() 
    {

        try {

            $response = $this->searchConsoleService->searchanalytics->query(
                'sc-domain:' . $this->websiteUrl,
                new SearchAnalyticsQueryRequest([
                    'startDate' => $this->reportStartDate,
                    'endDate' => $this->reportEndDate,
                    'dimensions' => [ 'query' ],
                    'type' => 'web'
                    
                ])
            );

        } catch (\Exception $e) {
            
            return Array(
                'status' => false,
                'error' => $e
            );

        }

        // Adjust data
        $queryData = [];
        foreach ($response->getRows() as $row) {
            $queryData[$row->getKeys()[0]] = $row->getImpressions();
        }

        // Sort array by values
        arsort($queryData);

        // Return data
        return Array(
            'status' => true,
            'queryData' => array_slice($queryData, 0, 15)
        );

    }

    /**
     * Processes Google API response array into a results array
     * 
     * @var Google\Service\AnalyticsData\RunReportResponse
     * @var Boolean
     * @return Array
     */
    private function parseReportResponse($data, $reverse = true) 
    {

        $dataArray = [];
        foreach ($data->getRows() as $row) {
            $dataArray[$row->getDimensionValues()[0]->getValue()] = $row->getMetricValues()[0]->getValue();
        }

        if ($reverse) {
            $dataArray = array_reverse($dataArray);
        }

        return $dataArray;

    }

}