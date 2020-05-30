<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SearchHistory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'searched_word'
    ];

    protected $visible = [
        'id', 'user_id', 'searched_word'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasManyThrough(
            Item::class,
            SearchHistoryResult::class,
            'serch_history_id',
            'item_id'
        );
    }
}
