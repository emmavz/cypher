<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInvestment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'author_id', 'user_id', 'amount', 'tokens', 'investments'
    ];

    public function user_investmentable()
    {
        return $this->morphTo();
    }
}
