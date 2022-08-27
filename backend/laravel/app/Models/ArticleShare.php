<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleShare extends Model
{

    public $table = 'article_share';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lucky_sharer_seen_referrer_id', 'lucky_sharer_seen_referee_id',
    ];
}
