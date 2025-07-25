<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'needs',
        'detail',
        'date',
        'price',
        'proof',
    ];
}
