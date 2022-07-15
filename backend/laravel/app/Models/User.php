<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Follow;
use App\Models\Article;
use App\Models\ArticleUserPaid;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function followers()
    {
        return $this->hasMany(Follow::class, 'followed_id');
    }

    public function followed()
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function paid_articles()
    {
        return $this->belongToMany(Article::class, 'article_user_paids', 'user_id', 'article_id');
    }

    public function total_invested($userId = null)
    {
        $relation = $this->hasMany(ArticleUserPaid::class);
        if ($userId) $relation->where('user_id', $userId);
        return $relation;
    }

    public function author_total_invested($userId = null)
    {
        $relation = $this->hasMany(ArticleUserPaid::class, 'author_id', 'id');
        if ($userId) $relation->where('author_id', $userId);
        return $relation;
    }

    public static function rules()
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', 'min:' . config('website.user_min_pass')],
        ];

        return $rules;
    }
}
