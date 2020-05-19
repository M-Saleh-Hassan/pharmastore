<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'is_cart'
    ];

    protected $visible = [
        'id', 'user_id'
    ];

    protected $appends = ['items'];

    public function scopeCart($query)
    {
        return $query->where('is_cart', 1);
    }

    public function scopeOrder($query)
    {
        return $query->where('is_cart', 0);
    }

    public function scopeUser($query, $user_id)
    {
        return $query->where('user_id', $user_id);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function getitemsAttribute()
    {
        return $this->items()->get();
    }
}
