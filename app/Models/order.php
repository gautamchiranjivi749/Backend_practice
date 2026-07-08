<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Payment;
use App\Models\User;
use App\Models\OrderItem;


class order extends Model
{
  protected $fillable = [
        'user_id',
        'total_amount',
        'status',
        'payment_method',
        'payment_status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function payment()
{
    return $this->hasOne(Payment::class);
}
}
