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
        $article->price = 15;
        $article->theta = 2;
        $article->image_url = Storage::url("dynamic/post-1.png");
        $article->is_published = 1;
        $article->save();

        $article->tags()->sync([1, 2]);

        $article = new Article();
        $article->user_id = 2;
        $article->title = "Super Chewy Cookies Recipe";
        $article->date_posted = Carbon::now();
        $article->description = "This recipe will teach you the most awesome way to make amazingly chewy cookies that will make your grandma proud.";
        $article->content = '<?xml encoding="utf-8" ?><p>Hiya there!</p><p><br></p><p><br></p><p>If you asked me how many recipes I&rsquo;ve tried to get super chewy, PERFECT cookies, I couldn&rsquo;t even tell you. But what I can tell you, is the recipe that FINALLY worked for me.</p><p><br></p><p><br></p><h3>IT&rsquo;S ALL ABOUT THE BROWN SUGAR!</h3><p><br></p><p><br></p><p>Brown sugar is where a lot of people get it wrong. Before you buy brown sugar from the store, make sure to look at the consistency. It&rsquo;s suuuuper important that you get the right kind or else your cookies are going to be suuuuper NOT chewy. Trust me. Been there, done that. Below, you&rsquo;ll find a list of things to look out for when buying brown sugar during your next trip to the grocery.</p>';
        $article->price = 15;
        $article->theta = 2;
        $article->image_url =  Storage::url("dynamic/post-2.jpg");
        $article->is_published = 1;
        $article->save();

        $article->tags()->sync([3, 1]);

        $article = new Article();
        $article->user_id = 3;
        $article->title = "The Go-To-Market Guide";
        $article->date_posted = Carbon::now();
        $article->description = "This recipe will teach you the most awesome way to make amazingly chewy cookies that will make your grandma proud.";
        $article->content = "content";
        $article->price = 15;
        $article->theta = 2;
        $article->image_url =  Storage::url("dynamic/post-3.jpg");
        $article->is_published = 1;
        $article->save();

        $article->tags()->sync([1, 4]);

        $article = new Article();
        $article->user_id = 4;
        $article->title = "The Rules of Digital Marketing";
        $article->date_posted = Carbon::now();
        $article->description = "This recipe will teach you the most awesome way to make amazingly chewy cookies that will make your grandma proud.";
        $article->content = "content";
        $article->price = 15;
        $article->theta = 2;
        $article->image_url =  Storage::url("dynamic/post-4.jpg");
        $article->is_published = 1;
        $article->save();

        $article->tags()->sync([6, 7]);

        $article = new Article();
        $article->user_id = 5;
        $article->title = "Building muscle the right way";
        $article->date_posted = Carbon::now();
        $article->description = "This recipe will teach you the most awesome way to make amazingly chewy cookies that will make your grandma proud.";
        $article->content = "content";
        $article->price = 15;
        $article->theta = 2;
        $article->image_url =  Storage::url("dynamic/post-5.jpg");
        $article->is_published = 1;
        $article->save();

        $article->tags()->sync([8, 7]);

        $article = new Article();
        $article->user_id = 6;
        $article->title = "The History of Fashion";
        $article->date_posted = Carbon::now();
        $article->description = "Fashion has always been my passion, and that’s why I’ve dedicated all my free time to researching its history.";
        $article->content = '<?xml encoding="utf-8" ?><p>Fashion has always been my passion, and that&rsquo;s why I&rsquo;ve dedicated all my free time to researching its history.</p><p><br></p><p><br></p><p>For me, fashion began in the summer of 2010, when my mother took me to the most beautiful antique dress shop. But fashion started way before that.</p><p><br></p><p><br></p><p><img src="' . Storage::url("dynamic/draft_article.png") . '" data-align="center" style="display: block; margin: auto;"></p>';
        $article->price = 20;
        $article->theta = 10;
        $article->image_url =  Storage::url("dynamic/create_article.png");
        $article->is_published = 1;
        $article->save();

        $article->tags()->sync([1]);
    }
}
