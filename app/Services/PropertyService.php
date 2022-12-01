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

    /**
     * Enables a propery's batch email setting
     * 
     * @param Integer $id : The affected property's ID
     */
    public function enableBatchEmail($id)
    {
        $property = Property::findOrFail($id);
        $property->batch_email = true;
        $property->save();
    }

    /**
     * Disables a propery's batch email setting
     * 
     * @param Integer $id : The affected property's ID
     */
    public function disableBatchEmail($id)
    {
        $property = Property::findOrFail($id);
        $property->batch_email = false;
        $property->save();
    }

    /**
     * Updates the fields related to batch emails
     * 
     * @param Integer $id : The affect property's ID
     * @param String $name : The field for insertion into client_name
     * @param String $email : The field for insertion into client_email
     */
    public function updateBatchEmailSettings($id, $name, $email)
    {
        $property = Property::findOrFail($id);
        $property->client_name = $name;
        $property->client_email = $email;
        $property->save();
    }

    /**
     * Toggles a property's dark logo background option
     * 
     * @param Integer $id : The affected property's ID
     */
    public function toggleDarkLogoBackground($id)
    {
        $property = Property::findOrFail($id);
        $property->logo_dark_background = !$property->logo_dark_background;
        $property->save();
    }
    
}