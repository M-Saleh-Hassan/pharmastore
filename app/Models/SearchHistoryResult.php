<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SearchHistoryResult extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'search_history_id', 'item_id'
    ];

    protected $visible = [
        'id', 'search_history_id', 'item_id'
    ];
}
