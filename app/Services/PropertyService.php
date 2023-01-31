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
     * @param Array $input : Validated array of request data
     * @return bool : true on success, false on failure
     */
    public function create($input) {

        // Create and add the property
        Property::create([
            'name' => $input['property-name'],
            'analytics_id' => $input['property-id'],
            'place_id' => $input['place-id'],
            'logo' => $input['property-logo'],
            'url' => $input['property-url'],
        ]);

        return true;

    }

    /**
     * Updates a property given the new fields
     * 
     * @param Integer $id : The property ID
     * @param Array $input : Validated array of request data
     */
    public function update($id, $input)
    {
        Property::findOrFail($id)->update([
            'name' => $input['property-name'],
            'analytics_id' => $input['property-id'],
            'place_id' => $input['place-id'],
            'logo' => $input['property-logo'],
            'url' => $input['property-url'],
        ]);
    }

    /**
     * Deletes a property
     * 
     * @param Integer $id : The property ID
     */
    public function delete($id)
    {
        $property = Property::findOrFail($id);

        // Delete all attached reports
        $property->reports->each(function($report, $key) {
            $report->delete();
        });

        // Delete the property itself
        $property->delete();
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
     * @param Array[String] $emails : An array of emails for insertion
     */
    public function updateBatchEmailSettings($id, $name = null, $emails = [])
    {
        $property = Property::findOrFail($id);
        $property->client_name = $name;
        $property->client_email = serialize($emails);
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