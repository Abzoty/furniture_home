<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    use HasFactory;

    protected $fillable = ['content', 'customer_id'];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}