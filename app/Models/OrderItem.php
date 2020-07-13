<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Exceptions\HttpResponseException;

class OrderItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_id', 'item_id', 'quantity'
    ];

    protected $visible = [
        'id', 'order_id', 'item_id', 'quantity', 'store_id'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function getStoreIdAttribute()
    {
        if(empty($this->item()->withTrashed()->first()))
            throw new HttpResponseException(response()->json(['success'=> 0, 'data' => ['message' => 'order_item of '.$this->id.' not found.']], 401));
        if(empty($this->item()->withTrashed()->first()->branch->store))
            dd($this->item()->withTrashed()->first()->branch);
        return $this->item()->withTrashed()->first()->branch->store->id;
    }
}
