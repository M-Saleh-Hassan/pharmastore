<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id', 'area_id', 'lng', 'lat', 'mobile', 'working_hours_from', 'working_hours_to', 'working_days_from', 'working_days_to', 'name'
    ];

    protected $visible = [
        'id', 'store_id', 'area_id', 'lng', 'lat', 'mobile', 'working_hours_from', 'working_hours_to', 'working_days_from', 'working_days_to', 'name'
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function store()
    {
        return $this->belongsTo(User::class, 'store_id');
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function uploadHistories()
    {
        return $this->hasMany(UploadHistory::class);
    }
}
