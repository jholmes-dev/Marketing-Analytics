<?php

namespace App\View\Components\report;

use Illuminate\View\Component;

class InfoBox extends Component
{
    /**
     * The box title
     * 
     * @var String
     */
    public $title;
    
    /**
     * The box content
     * 
     * @var String
     */
    public $content;
    
    /**
     * The box footer
     * 
     * @var String
     */
    public $footer;

    /**
     * The box tooltip content
     * 
     * @var String
     */
    public $tooltip;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($title, $content, $footer = '', $tooltip = '')
    {
        $this->title = $title;
        $this->content = $content;
        $this->footer = $footer;
        $this->tooltip = $tooltip;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.report.info-box');
    }
}
