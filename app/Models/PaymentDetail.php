<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentDetail extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'amount', 'payment_method', 'payment_status'];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}