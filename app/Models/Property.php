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
        'logo',
        'url'
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
