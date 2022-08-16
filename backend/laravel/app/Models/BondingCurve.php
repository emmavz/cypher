<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BondingCurve extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'author_id', 'user_id', 'total_investments',
    ];
}
