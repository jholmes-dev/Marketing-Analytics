<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Property;

class PropertiesList extends Component
{

    /**
     * List of properties to be displayed
     * 
     * @var $properties
     */
    public $properties;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->properties = Property::all();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.properties-list');
    }
}
