<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Article;
use App\Models\User;
use App\Models\Follow;

class ArticleUserPaid extends Model
{

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }

    function author_followers()
    {
        return $this->hasManyThrough(User::class, Follow::class, 'followed_id', 'id', 'author_id');
    }

    function author_followed()
    {
        return $this->hasManyThrough(User::class, Follow::class, 'follower_id', 'id', 'author_id');
    }
}
