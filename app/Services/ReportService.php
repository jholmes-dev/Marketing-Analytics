<?php

namespace App\Services;

use App\Models\Report;
use App\Models\Property;
use App\Services\GoogleOAuthService;
use App\Services\WordPressPostService;
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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

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
     * @var App\Services\WordPressPostService
     */
    public $wpPostService;

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

        // Generate WP Post Service
        $this->wpPostService = new WordPressPostService();

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
            'new_users' => $data['newUsers'],
            'average_session_duration' => $data['averageSessionDuration'],
            'date_session' => serialize( $data['dateSessionData'] ),
            'browsers' => serialize( $data['browserData'] ),
            'devices' => serialize( $data['deviceData'] ),
            'channels' => serialize( $data['channelData'] ),
            'pages' => serialize( $data['pageData'] ),
            'cities' => serialize( $data['cityData'] ),
            'queries' => serialize($data['queryData']),
            'reviews' => ($data['reviews'] == NULL) ? NULL : serialize($data['reviews']),
            'post_data' => ($data['postSessionData'] == NULL) ? NULL : serialize($data['postSessionData']),
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

        // Filter our 'Page not found' from page data
        foreach ($data['pageData'] as $page => $title) {
            if (str_contains($page, 'Page not found')) {
                unset($data['pageData'][$page]);
            }
        }

        // Filter out `(not set)` from city data
        foreach ($data['cityData'] as $city => $imp) {
            if ($city == '(not set)') {
                unset($data['cityData'][$city]);
            }
        }

        // Filter out `(not set)` from page data
        foreach ($data['pageData'] as $page => $imp) {
            if ($page == '(not set)') {
                unset($data['pageData'][$page]);
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

        // Call and confirm query data for WMT
        $queryData = $this->getQueryData();
        if (!$queryData['status']) {
            return $queryData;
        } else {
            unset($queryData['status']);
            $resData = array_merge($resData, $queryData);
        }

        // Call and add review data
        $resData['reviews'] = $this->getReviewData();

        // Retrieve website posts within date range and grab their analytics data
        $this->wpPostService->setBlogUrl($this->parentProperty->url);
        $this->wpPostService->loadPosts($this->reportStartDate, $this->reportEndDate);
        $reportPosts = $this->wpPostService->getPostData();

        $postSessionData = [];
        foreach ($reportPosts as $post)
        {
            $urlStartPos = strpos($post['link'], '://');
            $subUrl = substr($post['link'], $urlStartPos + 3);
            $postData = $this->getPageMetrics($subUrl);

            if (!$postData['status']) {
                return $postData;
            }
            unset($postData['status']);

            $postData['pageSessionData']['post_title'] = $post['title']['rendered'];

            array_push($postSessionData, $postData['pageSessionData']);
        }

        // Append post data
        $resData['postSessionData'] = $postSessionData;

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
                        ]),
                        new Metric([
                            'name' => 'newUsers',
                        ]),
                        new Metric([
                            'name' => 'averageSessionDuration',
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
                'newUsers' => $responseRow->getMetricValues()[6]->getValue(),
                'averageSessionDuration' => $responseRow->getMetricValues()[7]->getValue(),
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
                    'type' => 'web',
                    'rowLimit' => '100',
                ])
            );

        } catch (\Exception $e) {
            
            return Array(
                'status' => false,
                'error' => $e
            );

        }

        // Test data
        $queryData = [];
        for ($i = 0; $i < count($response['rows']); $i++)
        {
            array_push($queryData, [
                'query' => $response['rows'][$i]['keys'][0],
                'clicks' => $response['rows'][$i]['clicks'],
                'impressions' => $response['rows'][$i]['impressions'],
                'position' => $response['rows'][$i]['position'],
                'ctr' => $response['rows'][$i]['ctr'],
            ]);
        }

        // Return data
        return Array(
            'status' => true,
            'queryData' => $queryData,
        );

    }

    /**
     * Gets place's recent reviews
     * 
     * @return Array||NULL
     */
    public function getReviewData()
    {
        // All variables we need for the call
        $apiVars = [
            'place_id' => $this->parentProperty->place_id,
            'api_key' => config('app.google_places_api_key'),
            'token' => Str::uuid()

        ];

        // Check for parent property's place ID
        // Check for Google Places API key
        if ($apiVars['place_id'] == NULL || $apiVars['api_key'] == NULL) {
            return NULL;
        }

        // Make and check request, then return the data
        $reqUrl = "https://maps.googleapis.com/maps/api/place/details/json?place_id={$apiVars['place_id']}&fields=reviews&reviews_sort=newest&sessiontoken={$apiVars['token']}&key={$apiVars['api_key']}";
        $apiRes = Http::get($reqUrl);
        $apiResData = $apiRes->object();

        if ($apiRes->failed() || !isset($apiResData->status) || !isset($apiResData->result->reviews) || count($apiResData->result->reviews) == 0) {
            return NULL;
        }

        return $apiResData->result->reviews;
        
    }

    /**
     * Retrieve's a specific page's metrics
     * 
     * @param String $pageUrl The full page url to filter by. Excluding http/https. Eg: example.com/path
     * @return Array
     */
    public function getPageMetrics($pageUrl) 
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
                            'name' => 'fullPageUrl',
                        ]),
                    ],
                    'metrics' => [
                        new Metric([
                            'name' => 'sessions',
                        ]),
                    ],
                    'dimensionFilter' => [
                        'filter' => new Filter([
                            'fieldName' => 'fullPageUrl',
                            'stringFilter' => new StringFilter([
                                'value' => $pageUrl,
                            ])
                        ]),
                    ]
                ])
            );

        } catch (\Exception $e) {
            
            return Array(
                'status' => false,
                'error' => $e
            );

        }

        $pageData = [
            "url" => $response['rows'][0]['dimensionValues'][0]['value'],
            "sessions" => $response['rows'][0]['metricValues'][0]['value'],
        ];

        // Return data
        return Array(
            'status' => true,
            'pageSessionData' => $pageData,
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