<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Property;
use App\Http\Requests\Property\StorePropertyRequest;
use App\Services\PropertyService;

class PropertyController extends Controller
{
    protected $propertyService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PropertyService $propertyService)
    {
        $this->middleware('auth');
        $this->propertyService = $propertyService;
    }

    /**
     * Individual property index page
     * 
     * @var int $id - Property ID
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index($id) 
    {
        return view('properties.property', [ 
            'property' => Property::findOrFail($id)
        ]);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function newIndex()
    {
        return view('properties.add-property');
    }

    /**
     * Store a property in the database
     * 
     */
    public function newStore(StorePropertyRequest $request) 
    {

        // Get validated request data
        $validated = $request->validated();

        // Store in db
        $response = $this->propertyService->create($validated);

        // Check response
        if ($response) {
            return back()->with('success', 'Property has been created!');
        } else {
            return back()->with('error', 'Access could not be verified. Please confirm you entered the Analytics Property ID correctly, and that the Analytics Service User has been added to the property with admin permissions.');
        }

    }

}
