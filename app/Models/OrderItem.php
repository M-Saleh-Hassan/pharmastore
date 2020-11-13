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
        $item = $this->item()->withTrashed()->first();
        if(empty($item))
            throw new HttpResponseException(response()->json(['success'=> 0, 'data' => ['message' => 'order_item of '.$this->id.' not found.']], 401));
        $branch = $item->branch()->withTrashed()->first();
        if(empty($branch))
            throw new HttpResponseException(response()->json(['success'=> 0, 'data' => ['message' => 'branch id not found.']], 401));
        return $branch->store_id;
    }
}
