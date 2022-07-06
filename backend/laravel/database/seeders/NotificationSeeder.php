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
        $notification->text = '<a :to="{ name: \'other-profile\', params: { userId: 1 } }"><b>Allison Vega</b></a> just invested <b>20 CPHR</b>.';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 1;
        $userNotification->save();

        $notification = new Notification();
        $notification->text = '<a :to="{ name: \'other-profile\', params: { userId: 1 } }"><b>Debby Anonymous</b></a> just invested <b>14 CPHR</b>.';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 1;
        $userNotification->read_at = Carbon::now();
        $userNotification->save();

        $notification = new Notification();
        $notification->text = 'Congratulations on your first article: <a :to="{ name: \'article_homepage\', params: { articleId: 1, userId: 1 } }"><b>Tokenomics</b></a>!';
        $notification->save();

        $userNotification = new UserNotification();
        $userNotification->notification_id = $notification->id;
        $userNotification->user_id = 1;
        $userNotification->read_at = Carbon::now();
        $userNotification->save();

        $notification = new Notification();
        $notification->text = 'Your article <a :to="{ name: \'article_homepage\', params: { articleId: 1, userId: 1 } }"><b>School’s Out</b></a> has been liquidated. <b>See statistics here</b>';
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
    }
}
