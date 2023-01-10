<?php

namespace App\View\Components\property;

use Illuminate\View\Component;

class FindPlaceIdModal extends Component
{

    /**
     * The action tags for the modal
     * 
     * @var String
     */
    public $submitAction = 'dismiss';
    public $dataAttributes;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($submitAction)
    {
        $this->submitAction = $submitAction;
        $this->dataAttributes = $this->dataAttributes();
    }

    /**
     * Returns the data-bs attributes for the modal submit button
     * 
     * @return String
     */
    public function dataAttributes()
    {
        if ($this->submitAction == 'dismiss') {
            return 'data-bs-dismiss="modal"';
        } else {
            return 'data-bs-toggle="modal" data-bs-target="#' . $this->submitAction . '"';
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.property.find-place-id-modal');
    }
}
