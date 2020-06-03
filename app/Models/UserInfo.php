<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserInfo extends Model
{
    use SoftDeletes;
    protected $table = 'user_info';

    protected $fillable = [
        'user_id','lng', 'lat', 'bio', 'delivery_details', 'mobile1', 'mobile2'
    ];
}
