<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Order;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'transaction_uuid',
        'transaction_code',
        'amount',
        'method',
        'status',
        'callback_response'
    ];
    protected $casts = [
        'callback_respose' => 'array'
    ];
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
