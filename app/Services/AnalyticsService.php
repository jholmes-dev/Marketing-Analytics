<?php

namespace App\Services;
use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\Filter;
use Google\Analytics\Data\V1beta\FilterExpression;
use Google\Analytics\Data\V1beta\Filter\StringFilter;

class AnalyticsService {
    
    /**
     * Constructs a new service
     *
     */
    public function __construct() {
        //
    } 


    /**
     * Tests an Analytics ID to confirm access
     * 
     * @var String $id
     * @return Boolean
     */
    public static function testAnalyticsId($id) {

        $client = new BetaAnalyticsDataClient();

        try {

            $response = $client->runReport([
                'property' => 'properties/' . $id,
                'dateRanges' => [
                    new DateRange([
                        'start_date' => 'yesterday',
                        'end_date' => 'today',
                    ]),
                ],
                'metrics' => [
                    new Metric(
                    [
                        'name' => 'totalUsers',
                    ])
                ]
            ]);

            return true;

        } catch (\Exception $e) {

            return false;

        }

    }


    /**
     * Converts an Analytics API response error into a usable Array
     * 
     * @var String $res
     * @return Array
     */
    private static function convertResponse($res) {

        $index = strpos($res, '{');
        $subMessage = substr($res, $index);
        return json_decode($subMessage);

    }

}