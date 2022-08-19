<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Tag;
// use App\Models\ArticleUserPaid;
use App\Models\UserInvestment;
use App\Models\ArticleShare;
use App\Models\BlockUser;
use Storage;

class Article extends Model
{

    public static $path = 'articles/';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [];

    public function getContentAttribute($value)
    {
        return str_replace(config('website.editor_utf'), '', $value);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function total_reads($userId = null)
    {
        $relation = $this->belongsToMany(User::class, 'article_read', 'article_id', 'user_id')->withTimestamps();
        if ($userId) $relation->where('user_id', $userId);
        return $relation;
    }

    public function total_shares($userId = null)
    {
        $relation = $this->belongsToMany(User::class, 'article_share', 'article_id', 'referee_id')->withTimestamps();
        if ($userId) $relation->where('referee_id', $userId);
        return $relation;
    }

    public function total_invested($userId = null)
    {
        $relation = $this->user_investments();
        if ($userId) $relation->where('user_id', $userId);
        return $relation;
    }

    public function user_investments()
    {
        return $this->morphMany(UserInvestment::class, 'user_investmentable');
    }

    public function is_paid_by_user()
    {
        $authId = getAuthId();
        $relation = $this->morphOne(UserInvestment::class, 'user_investmentable');
        $relation->where('user_id', $authId);
        return $relation;
    }

    public function is_paid_by_referrals()
    {
        $authId = getAuthId();
        $relation = $this->hasOne(ArticleShare::class);
        $relation->where('is_paid', true)->where(function ($query) use ($authId) {
            return $query->where('referrer_id', $authId)->orWhere('referee_id', $authId);
        });
        return $relation;
    }

    public function block_user()
    {
        $authId = getAuthId();
        $relation = $this->hasOne(BlockUser::class, 'user_2', 'user_id')->where('user_1', $authId);
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
        if ($request->hasFile('image_url')) {
            list($width, $height, $type, $attr) = getimagesize($request->image_url);
            $img = storeImage(['image' => $request->image_url, 'path' => self::$path, 'webp' => false, 'fit' => [$width, 262]]);
            $data['image_url'] = Storage::url(self::$path . $img);
        }

        if (strip_tags($request->content)) {
            $data['content'] = summernote($request->content, self::$path, $oldmodel ? $oldmodel->content : null);
        }

        return $data;
    }

    public static function deleteFiles($model, $request = null, $oldmodel = null)
    {
        if ($request == null || $request->hasFile('image_url')) {
            Storage::delete(str_replace('storage/', '', parse_url($model->image_url)['path']));
        }

        if ($request == null) {
            summernote(null, self::$path, $model->content);
        }
    }
}
