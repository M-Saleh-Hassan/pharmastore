<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInfo extends Model
{
    protected $table = 'user_info';
    protected $softDelete = true;

    protected $fillable = [
        'user_id','lng', 'lat', 'bio', 'delivery_details'
    ];
}
