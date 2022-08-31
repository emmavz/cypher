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

        // Admin Account
        $user = new User();
        $user->name = "Super Admin";
        $user->email = "admin@site.com";
        $user->password = bcrypt('admin123');
        $user->balance = 1000000;
        $user->bio = "Super admin!!!";
        $user->referral_token = generateReferral();
        $user->is_admin = 1;
        $user->save();

        // Users Account
        $user = new User();
        $user->name = "Ephraim Jones";
        $user->email = "user@site.com";
        $user->password = bcrypt('admin123');
        $user->pfp =  Storage::url("dynamic/profile-1.png");
        $user->wallet_address = "abcd1234";
        $user->balance = config('website.balance');
        $user->bio = "Just another baker blogger";
        // $user->referral_token = (string)Uuid::uuid1();
        $user->referral_token = generateReferral();
        $user->save();

        $user = new User();
        $user->name = "Eliza Mae";
        $user->email = "user2@site.com";
        $user->password = bcrypt('admin123');
        $user->pfp =   Storage::url("dynamic/profile-2.png");
        $user->wallet_address = "abcd1234";
        $user->balance = config('website.balance');
        $user->bio = "Just another baker blogger";
        $user->referral_token = generateReferral();
        $user->save();

        $user = new User();
        $user->name = "Cecelia Hong";
        $user->email = "user3@site.com";
        $user->password = bcrypt('admin123');
        $user->pfp =   Storage::url("dynamic/profile-3.png");
        $user->wallet_address = "abcd1234";
        $user->balance = config('website.balance');
        $user->bio = "Just another baker blogger";
        $user->referral_token = generateReferral();
        $user->save();

        $user = new User();
        $user->name = "Melissa Shen";
        $user->email = "user4@site.com";
        $user->password = bcrypt('admin123');
        $user->pfp =   Storage::url("dynamic/profile-4.png");
        $user->wallet_address = "abcd1234";
        $user->balance = config('website.balance');
        $user->bio = "Just another baker blogger";
        $user->referral_token = generateReferral();
        $user->save();

        $user = new User();
        $user->name = "Darren Jones";
        $user->email = "user5@site.com";
        $user->password = bcrypt('admin123');
        $user->pfp =   Storage::url("dynamic/profile-5.png");
        $user->wallet_address = "abcd1234";
        $user->balance = config('website.balance');
        $user->bio = "Just another baker blogger";
        $user->referral_token = generateReferral();
        $user->save();

        $user = new User();
        $user->name = "Jessica Covington";
        $user->email = "user6@site.com";
        $user->password = bcrypt('admin123');
        $user->pfp =   Storage::url("dynamic/userprofile-1.png");
        $user->wallet_address = "abcd1234";
        $user->balance = config('website.balance');
        $user->bio = "She turned her can't into can and her dreams into plans.";
        $user->referral_token = generateReferral();
        $user->save();

        $user = new User();
        $user->name = "User 7";
        $user->email = "user7@site.com";
        $user->password = bcrypt('admin123');
        $user->pfp =   '';
        $user->wallet_address = "abcd1234";
        $user->balance = config('website.balance');
        $user->bio = "";
        $user->referral_token = generateReferral();
        $user->save();

        $user = new User();
        $user->name = "User 8";
        $user->email = "user8@site.com";
        $user->password = bcrypt('admin123');
        $user->pfp =   '';
        $user->wallet_address = "abcd1234";
        $user->balance = config('website.balance');
        $user->bio = "";
        $user->referral_token = generateReferral();
        $user->save();

        $user = new User();
        $user->name = "User 9";
        $user->email = "user9@site.com";
        $user->password = bcrypt('admin123');
        $user->pfp =   '';
        $user->wallet_address = "abcd1234";
        $user->balance = config('website.balance');
        $user->bio = "";
        $user->referral_token = generateReferral();
        $user->save();

        $user = new User();
        $user->name = "User 10";
        $user->email = "user10@site.com";
        $user->password = bcrypt('admin123');
        $user->pfp =   '';
        $user->wallet_address = "abcd1234";
        $user->balance = config('website.balance');
        $user->bio = "";
        $user->referral_token = generateReferral();
        $user->save();

        $user = new User();
        $user->name = "User 11";
        $user->email = "user11@site.com";
        $user->password = bcrypt('admin123');
        $user->pfp =   '';
        $user->wallet_address = "abcd1234";
        $user->balance = config('website.balance');
        $user->bio = "";
        $user->referral_token = generateReferral();
        $user->save();

        $user = new User();
        $user->name = "User 12";
        $user->email = "user12@site.com";
        $user->password = bcrypt('admin123');
        $user->pfp =   '';
        $user->wallet_address = "abcd1234";
        $user->balance = config('website.balance');
        $user->bio = "";
        $user->referral_token = generateReferral();
        $user->save();
    }
}
