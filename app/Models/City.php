<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name_en', 'name_ar'
    ];

    protected $visible = [
        'id', 'name_en', 'name_ar', 'areas'
    ];

    protected $appends = ['areas'];

    public function areas()
    {
        return $this->hasMany(Area::class);
    }

    public function getAreasAttribute()
    {
        return $this->areas()->get();
    }
}
