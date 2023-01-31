<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     */
    protected $fillable = [
        'name',
        'analytics_id',
        'place_id',
        'logo',
        'url',
        'client_name',
        'client_email',
        'batch_email'
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'batch_email' => false,
        'logo_dark_background' => false,
    ];

    /**
     * Has many function for the associated reports
     * 
     */
    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    /**
     * Returns an array for the client emails
     * 
     * @return Array
     */
    public function getClientEmailArray()
    {
        if ($this->client_email == null) return '[]';
        return unserialize($this->client_email);
    }

    /**
     * Returns the client email array as JSON
     * 
     * @return JSONObject
     */
    public function getJSONClientEmailArray()
    {
        if ($this->client_email == null) return '{}';
        return json_encode(unserialize($this->client_email));
    }

    /**
     * Returns the client email array for display
     * 
     * @return String
     */
    public function getDisplayClientEmailArray()
    {
        if ($this->client_email == null) return '(Not Set)';
        return implode(', ', unserialize($this->client_email));
    }

    /**
     * Returns a Hello X, line for direct display
     * 
     * @return String
     */
    public function getHelloLine()
    {
        return ($this->client_name == null) ? 'Hello,' : 'Hello ' . $this->client_name . ',';
    }

}
