<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Tag;
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

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'title', 'description', 'content', 'liquidation_days', 'price', 'category', 'hashtag_id',
        'image_url', 'article_total_reads', 'article_total_shares', 'is_published', 'date_posted'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
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

        if (strip_tags($request->description)) {
            $data['description'] = summernote($request->description, self::$path, $oldmodel ? $oldmodel->description : null);
        }

        return $data;
    }

    public static function deleteFiles($model, $request = null, $oldmodel = null)
    {
        if ($request == null || $request->hasFile('image_url')) {
            Storage::delete(str_replace('storage/', '', parse_url($model->image_url)['path']));
        }

        if ($request == null) {
            summernote(null, self::$path, $oldmodel->description);
        }
    }
}
