<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Storage;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::getQuery()->delete();

        $user = new User();
        $user->name = "Ephraim Jones";
        $user->email = "user@site.com";
        $user->password = bcrypt('admin123');
        $user->pfp =  Storage::url("dynamic/profile-1.png");
        $user->total_invested = 10025;
        $user->wallet_address = "abcd1234";
        $user->balance = 123;
        $user->bio = "bio";
        $user->save();

        $user = new User();
        $user->name = "Eliza Mae";
        $user->email = "user2@site.com";
        $user->password = bcrypt('admin123');
        $user->pfp =   Storage::url("dynamic/profile-2.png");
        $user->total_invested = 7342;
        $user->wallet_address = "abcd1234";
        $user->balance = 222;
        $user->bio = "bio";
        $user->save();

        $user = new User();
        $user->name = "Cecelia Hong";
        $user->email = "user3@site.com";
        $user->password = bcrypt('admin123');
        $user->pfp =   Storage::url("dynamic/profile-3.png");
        $user->total_invested = 8961;
        $user->wallet_address = "abcd1234";
        $user->balance = 111;
        $user->bio = "bio";
        $user->save();

        $user = new User();
        $user->name = "Melissa Shen";
        $user->email = "user4@site.com";
        $user->password = bcrypt('admin123');
        $user->pfp =   Storage::url("dynamic/profile-4.png");
        $user->total_invested = 9456;
        $user->wallet_address = "abcd1234";
        $user->balance = 333;
        $user->bio = "bio";
        $user->save();

        $user = new User();
        $user->name = "Darren Jones";
        $user->email = "user5@site.com";
        $user->password = bcrypt('admin123');
        $user->pfp =   Storage::url("dynamic/profile-5.png");
        $user->total_invested = 11275;
        $user->wallet_address = "abcd1234";
        $user->balance = 444;
        $user->bio = "bio";
        $user->save();
    }
}
