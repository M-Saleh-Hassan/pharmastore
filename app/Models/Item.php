<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'branch_id', 'name_en', 'name_ar', 'quantity', 'basic_price', 'discount', 'upload_history_id'
    ];

    protected $visible = [
        'id', 'branch_id', 'name_en', 'name_ar', 'quantity', 'basic_price', 'discount', 'created_at'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function uploadHistory()
    {
        return $this->belongsTo(UploadHistory::class, 'upload_history_id');
    }
}
