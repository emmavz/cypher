<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Tag;
use App\Models\ArticleUserPaid;
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
        $relation = $this->belongsToMany(User::class, 'article_read', 'article_id', 'user_id');
        if ($userId) $relation->where('user_id', $userId);
        return $relation;
    }

    public function total_shares($userId = null)
    {
        $relation = $this->belongsToMany(User::class, 'article_share', 'article_id', 'referee_id');
        if ($userId) $relation->where('referee_id', $userId);
        return $relation;
    }

    public function total_invested($userId = null)
    {
        $relation = $this->hasMany(ArticleUserPaid::class);
        if ($userId) $relation->where('user_id', $userId);
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
            $img = storeImage(['image' => $request->image_url, 'path' => self::$path, 'webp' => false]);
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
            summernote(null, self::$path, $oldmodel->content);
        }
    }
}
