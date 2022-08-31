<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Follow;
use App\Models\Article;
use App\Models\UserInvestment;
use App\Models\BondingCurve;
use App\Models\ArticleUserPaid;
use Storage;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public static $path = 'users/';

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

    public function published_articles()
    {
        return $this->articles()->where('is_published', true);
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

    public function user_investments()
    {
        return $this->morphMany(UserInvestment::class, 'user_investmentable');
    }

    public function author_bonding_curve()
    {
        return $this->hasOne(BondingCurve::class, 'author_id');
    }

    public function block_user_func($user_id = null)
    {
        $relation = $this->belongsToMany(User::class, 'block_users', 'user_1', 'user_2')->withTimestamps();
        if ($user_id) {
            $relation->where('user_2', $user_id);
        }
        return $relation;
    }

    public function block_user()
    {
        $authId = getAuthId();
        $relation = $this->hasOne(BlockUser::class, 'user_2', 'id')->where('user_1', $authId);
        return $relation;
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            self::deleteFiles($model);
        });
    }

    public static function storeFiles($request, $data, $oldmodel = null)
    {
        if ($request->hasFile('pfp')) {
            $img = storeImage(['image' => $request->pfp, 'path' => self::$path, 'webp' => false, 'fit' => [142, 142]]);
            $data['pfp'] = Storage::url(self::$path . $img);
        }

        return $data;
    }

    public static function deleteFiles($model, $request = null, $oldmodel = null)
    {
        if ($request == null || $request->hasFile('pfp')) {
            Storage::delete(str_replace('storage/', '', parse_url($model->pfp)['path']));
        }
    }

    public static function rules()
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'min:' . config('website.user_min_pass')],
            'pfp'   => ['nullable',  'mimes:' . config('website.imgformats')],
            'bio'   => ['nullable', 'string'],
        ];

        return $rules;
    }
}
