<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UploadHistory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'branch_id'
    ];

    protected $visible = [
        'id', 'user_id', 'branch_id', 'created_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'upload_history_id');
    }
}
