<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;
    use HasUlids;

    /**
     * The guarded attributes
     * 
     */
    protected $guarded = [];
    
    /**
     * Belongs to function for the parent Property
     * 
     */
    public function property() 
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Returns a formatted string for use in the report template
     * 
     * @return Array - index 1 is the keys, index 2 is the values
     */
    public function getFormattedArray($name, $date = false) 
    {

        // Key data
        $returnKeys = '';

        $arrayKeys = $this->getDatabaseArrayKeys($name);
        foreach ($arrayKeys as $k => $dsk) 
        {
            $returnKeys .= "'";
            if ($date) {
                $returnKeys .= date('M d', strtotime($dsk));
            } else {
                $returnKeys .= $dsk;
            }
            $returnKeys .= "', ";
        }

        // Value data
        $returnValues = implode(',', $this->getDatabaseArrayValues($name));

        // Return data
        return [ substr_replace($returnKeys, "", -2), $returnValues ];
        
        
    }

    /**
     * Returns keys of a database column
     * 
     * @var String $name - The name of a databse column to retrieve
     */
    public function getDatabaseArrayKeys($name) 
    {

        $dateSessionData = unserialize($this[$name]);
        return array_keys( $dateSessionData );
    }

    /**
     * Returns values of a database column
     * 
     * @var String $name - The name of a databse column to retrieve
     */
    public function getDatabaseArrayValues($name) 
    {
        $dateSessionData = unserialize($this[$name]);
        return array_values( $dateSessionData );
    }

}
