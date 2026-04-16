<?php

namespace App\Models;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;

class AccountCategory extends Model
{
    protected $fillable = ['name', 'status'];

    public function accounts()
    {
        return $this->hasMany(Account::class, 'category_id');
    }
}
