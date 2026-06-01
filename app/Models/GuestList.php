<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestList extends Model
{
    protected $fillable = [
        'date',
        'name',
        'mobile',
        'address',
        'profession',
        'status',
        'reference',
        'note',
    ];
}
