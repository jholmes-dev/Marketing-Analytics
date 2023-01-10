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

}
