<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Article;
use  Carbon\Carbon;
use Storage;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Article::getQuery()->delete();

        $article = new Article();
        $article->user_id = 1;
        $article->title = "Why Python is The Future";
        $article->date_posted = Carbon::now();
        $article->description = "description";
        $article->content = "content";
        $article->liquidation_days = 7;
        $article->price = 15;
        $article->category = 3;
        $article->image_url = Storage::url("dynamic/post-1.png");
        $article->article_total_reads = 294;
        $article->article_total_shares = 1245;
        $article->is_published = 1;
        $article->save();

        $article->tags()->sync([1, 2]);

        $article = new Article();
        $article->user_id = 2;
        $article->title = "Super Chewy Cookies Recipe";
        $article->date_posted = Carbon::now();
        $article->description = "description";
        $article->content = "content";
        $article->liquidation_days = 7;
        $article->price = 15;
        $article->category = 3;
        $article->image_url =  Storage::url("dynamic/post-2.jpg");
        $article->article_total_reads = 29;
        $article->article_total_shares = 32;
        $article->is_published = 1;
        $article->save();

        $article->tags()->sync([3, 1]);

        $article = new Article();
        $article->user_id = 3;
        $article->title = "The Go-To-Market Guide";
        $article->date_posted = Carbon::now();
        $article->description = "description";
        $article->content = "content";
        $article->liquidation_days = 7;
        $article->price = 15;
        $article->category = 3;
        $article->image_url =  Storage::url("dynamic/post-3.jpg");
        $article->article_total_reads = 299;
        $article->article_total_shares = 2386;
        $article->is_published = 1;
        $article->save();

        $article->tags()->sync([1, 4]);

        $article = new Article();
        $article->user_id = 4;
        $article->title = "The Rules of Digital Marketing";
        $article->date_posted = Carbon::now();
        $article->description = "description";
        $article->content = "content";
        $article->liquidation_days = 7;
        $article->price = 15;
        $article->category = 3;
        $article->image_url =  Storage::url("dynamic/post-4.jpg");
        $article->article_total_reads = 89;
        $article->article_total_shares = 298;
        $article->is_published = 1;
        $article->save();

        $article->tags()->sync([6, 7]);

        $article = new Article();
        $article->user_id = 5;
        $article->title = "Building muscle the right way";
        $article->date_posted = Carbon::now();
        $article->description = "description";
        $article->content = "content";
        $article->liquidation_days = 7;
        $article->price = 15;
        $article->category = 3;
        $article->image_url =  Storage::url("dynamic/post-5.jpg");
        $article->article_total_reads = 2;
        $article->article_total_shares = 29;
        $article->is_published = 1;
        $article->save();

        $article->tags()->sync([8, 7]);
    }
}
