<?php

namespace App\Models;

use App\Models\AccountCategory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'title',
        'type',
        'category_id',
        'amount',
        'date',
        'note'
    ];

    public function category()
    {
        return $this->belongsTo(AccountCategory::class, 'category_id');
    }
}
