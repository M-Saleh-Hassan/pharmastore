<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name_en', 'name_ar', 'city_id'
    ];

    protected $visible = [
        'id', 'name_en', 'name_ar', 'city_id'
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
