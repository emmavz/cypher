<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Article;
use App\Models\Notification;
use App\Models\UserNotification;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Notification::getQuery()->delete();

        $notification = new Notification();
        $notification->text = '<a :to="{ name: \'profile\', params: { userId: 2 } }"><b>Eliza Mae</b></a> just invested <b>20 CPHR</b>.';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 1;
        $userNotification->save();

        $notification = new Notification();
        $notification->text = '<a :to="{ name: \'profile\', params: { userId: 3 } }"><b>Cecelia Hong</b></a> just invested <b>14 CPHR</b>.';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 1;
        $userNotification->read_at = Carbon::now();
        $userNotification->save();

        $notification = new Notification();
        $notification->text = 'Congratulations on your first article: <a :to="{ name: \'article_homepage\', params: { articleId: 1} }"><b>Why Python is The Future</b></a>!';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 1;
        $userNotification->read_at = Carbon::now();
        $userNotification->save();

        $notification = new Notification();
        $notification->text = 'Your article <a :to="{ name: \'article_homepage\', params: { articleId: 1 } }"><b>Why Python is The Future</b></a> has been liquidated. <a :to="{ name: \'full_article_homepage\', params: { articleId: 1 } }"><b>See statistics here</b></a>';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 1;
        $userNotification->save();

        $notification = new Notification();
        $notification->text = 'Welcome to Cypher! <a :to="{ name: \'drafts\' }"><b>Get started here</b></a>.';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 1;
        $userNotification->read_at = Carbon::now();
        $userNotification->save();



        $notification = new Notification();
        $notification->text = '<a :to="{ name: \'profile\', params: { userId: 2 } }"><b>Eliza Mae</b></a> just invested <b>20 CPHR</b>.';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 2;
        $userNotification->save();

        $notification = new Notification();
        $notification->text = '<a :to="{ name: \'profile\', params: { userId: 3 } }"><b>Cecelia Hong</b></a> just invested <b>14 CPHR</b>.';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 2;
        $userNotification->read_at = Carbon::now();
        $userNotification->save();

        $notification = new Notification();
        $notification->text = 'Congratulations on your first article: <a :to="{ name: \'article_homepage\', params: { articleId: 2} }"><b>Super Chewy Cookies Recipe</b></a>!';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 2;
        $userNotification->read_at = Carbon::now();
        $userNotification->save();

        $notification = new Notification();
        $notification->text = 'Your article <a :to="{ name: \'article_homepage\', params: { articleId: 2 } }"><b>Super Chewy Cookies Recipe</b></a> has been liquidated. <a :to="{ name: \'full_article_homepage\', params: { articleId: 2 } }"><b>See statistics here</b></a>';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 2;
        $userNotification->save();

        $notification = new Notification();
        $notification->text = 'Welcome to Cypher! <a :to="{ name: \'drafts\' }"><b>Get started here</b></a>.';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 2;
        $userNotification->read_at = Carbon::now();
        $userNotification->save();




        $notification = new Notification();
        $notification->text = '<a :to="{ name: \'profile\', params: { userId: 2 } }"><b>Eliza Mae</b></a> just invested <b>20 CPHR</b>.';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 3;
        $userNotification->save();

        $notification = new Notification();
        $notification->text = '<a :to="{ name: \'profile\', params: { userId: 3 } }"><b>Cecelia Hong</b></a> just invested <b>14 CPHR</b>.';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 3;
        $userNotification->read_at = Carbon::now();
        $userNotification->save();

        $notification = new Notification();
        $notification->text = 'Congratulations on your first article: <a :to="{ name: \'article_homepage\', params: { articleId: 3} }"><b>The Go-To-Market Guide</b></a>!';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 3;
        $userNotification->read_at = Carbon::now();
        $userNotification->save();

        $notification = new Notification();
        $notification->text = 'Your article <a :to="{ name: \'article_homepage\', params: { articleId: 3 } }"><b>The Go-To-Market Guide</b></a> has been liquidated. <a :to="{ name: \'full_article_homepage\', params: { articleId: 3 } }"><b>See statistics here</b></a>';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 3;
        $userNotification->save();

        $notification = new Notification();
        $notification->text = 'Welcome to Cypher! <a :to="{ name: \'drafts\' }"><b>Get started here</b></a>.';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 3;
        $userNotification->read_at = Carbon::now();
        $userNotification->save();




        $notification = new Notification();
        $notification->text = '<a :to="{ name: \'profile\', params: { userId: 2 } }"><b>Eliza Mae</b></a> just invested <b>20 CPHR</b>.';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 4;
        $userNotification->save();

        $notification = new Notification();
        $notification->text = '<a :to="{ name: \'profile\', params: { userId: 3 } }"><b>Cecelia Hong</b></a> just invested <b>14 CPHR</b>.';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 4;
        $userNotification->read_at = Carbon::now();
        $userNotification->save();

        $notification = new Notification();
        $notification->text = 'Congratulations on your first article: <a :to="{ name: \'article_homepage\', params: { articleId: 4} }"><b>The Rules of Digital Marketing</b></a>!';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 4;
        $userNotification->read_at = Carbon::now();
        $userNotification->save();

        $notification = new Notification();
        $notification->text = 'Your article <a :to="{ name: \'article_homepage\', params: { articleId: 4 } }"><b>The Rules of Digital Marketing</b></a> has been liquidated. <a :to="{ name: \'full_article_homepage\', params: { articleId: 4 } }"><b>See statistics here</b></a>';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 4;
        $userNotification->save();

        $notification = new Notification();
        $notification->text = 'Welcome to Cypher! <a :to="{ name: \'drafts\' }"><b>Get started here</b></a>.';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 4;
        $userNotification->read_at = Carbon::now();
        $userNotification->save();




        $notification = new Notification();
        $notification->text = '<a :to="{ name: \'profile\', params: { userId: 2 } }"><b>Eliza Mae</b></a> just invested <b>20 CPHR</b>.';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 5;
        $userNotification->save();

        $notification = new Notification();
        $notification->text = '<a :to="{ name: \'profile\', params: { userId: 3 } }"><b>Cecelia Hong</b></a> just invested <b>14 CPHR</b>.';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 5;
        $userNotification->read_at = Carbon::now();
        $userNotification->save();

        $notification = new Notification();
        $notification->text = 'Congratulations on your first article: <a :to="{ name: \'article_homepage\', params: { articleId: 5} }"><b>Building muscle the right way</b></a>!';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 5;
        $userNotification->read_at = Carbon::now();
        $userNotification->save();

        $notification = new Notification();
        $notification->text = 'Your article <a :to="{ name: \'article_homepage\', params: { articleId: 5 } }"><b>Building muscle the right way</b></a> has been liquidated. <a :to="{ name: \'full_article_homepage\', params: { articleId: 5 } }"><b>See statistics here</b></a>';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 5;
        $userNotification->save();

        $notification = new Notification();
        $notification->text = 'Welcome to Cypher! <a :to="{ name: \'drafts\' }"><b>Get started here</b></a>.';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 5;
        $userNotification->read_at = Carbon::now();
        $userNotification->save();



        $notification = new Notification();
        $notification->text = '<a :to="{ name: \'profile\', params: { userId: 2 } }"><b>Eliza Mae</b></a> just invested <b>20 CPHR</b>.';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 6;
        $userNotification->save();

        $notification = new Notification();
        $notification->text = '<a :to="{ name: \'profile\', params: { userId: 3 } }"><b>Cecelia Hong</b></a> just invested <b>14 CPHR</b>.';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 6;
        $userNotification->read_at = Carbon::now();
        $userNotification->save();

        $notification = new Notification();
        $notification->text = 'Congratulations on your first article: <a :to="{ name: \'article_homepage\', params: { articleId: 6} }"><b>The History of Fashion</b></a>!';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 6;
        $userNotification->read_at = Carbon::now();
        $userNotification->save();

        $notification = new Notification();
        $notification->text = 'Your article <a :to="{ name: \'article_homepage\', params: { articleId: 6 } }"><b>The History of Fashion</b></a> has been liquidated. <a :to="{ name: \'full_article_homepage\', params: { articleId: 6 } }"><b>See statistics here</b></a>';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 6;
        $userNotification->save();

        $notification = new Notification();
        $notification->text = 'Welcome to Cypher! <a :to="{ name: \'drafts\' }"><b>Get started here</b></a>.';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 6;
        $userNotification->read_at = Carbon::now();
        $userNotification->save();
    }
}
