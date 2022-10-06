<?php

namespace App\Services;
use App\Models\Property;
use App\Services\AnalyticsService;

class PropertyService {
    
    /**
     * Constructs a new service
     *
     */
    public function __construct() {
        //
    } 

    /**
     * Creates a Property model and stores it in the database
     * 
     * @var Array $request - Validated array of request data
     * @return bool - true on success, false on failure
     */
    public function create($request) {

        // Create and add the property
        Property::create([
            'name' => $request['property-name'],
            'analytics_id' => $request['property-id'],
            'logo' => $request['property-logo'],
            'url' => $request['property-url'],
        ]);

        return true;

    }
    
}