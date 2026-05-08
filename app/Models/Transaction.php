<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'code',
        'customer_id',
        'subtotal',
        'tax',
        'total',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class, 'transaction_id');
    }

    public function scopeSearch($query, $search)
    {

        return $query->when($search, function ($query, $search) {
            $query->where('code', 'like', "%{$search}%");
        });
    }
}
