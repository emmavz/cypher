<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleShare extends Model
{

    public $table = 'article_share';

    // public function referee()
    // {
    //     return $this->belongsTo(ArticleShare::class, 'referee_id', 'referrer_id');
    // }

    // public function getRefereeIdsAttribute()
    // {
    //     $referees = [];

    //     $referee = $this->referee;

    //     while (!is_null($referee)) {
    //         array_push($referees, $referee->referee_id);
    //         $referee = $referee->referee;
    //     }

    //     return $referees;
    // }
}
