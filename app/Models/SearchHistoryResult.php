<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function searchhistory()
    {
        return $this->belongsTo(SearchHistory::class, 'search_history_id');
    }

    public function scopeSearch($query, $search)
    {
        return $query->whereHas('item', function (Builder $query) use ($search){
            $query->where('name_en', 'like', '%'.$search.'%' )->orWhere('name_ar', 'like', '%'.$search.'%');
        });
    }
}
