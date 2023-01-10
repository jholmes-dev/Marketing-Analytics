<?php

namespace App\View\Components\property;

use Illuminate\View\Component;
use App\Models\Property;

class EditModal extends Component
{

    /**
     * The parent property
     * 
     * @var App\Models\Property
     */
    public $property;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(Property $property)
    {
        $this->property = $property;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.property.edit-modal');
    }
}
