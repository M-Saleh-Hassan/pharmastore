<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_id', 'item_id', 'quantity'
    ];

    protected $visible = [
        'id', 'order_id', 'item_id', 'quantity'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
