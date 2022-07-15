<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Storage;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Str;

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

        $user->wallet_address = "abcd1234";
        $user->balance = 123;
        $user->bio = "Just another baker blogger";
        $user->bg = Storage::url("dynamic/post-8.png");
        // $user->referral_token = (string)Uuid::uuid1();
        $user->referral_token = $this->generateReferral();
        $user->save();

        $user = new User();
        $user->name = "Eliza Mae";
        $user->email = "user2@site.com";
        $user->password = bcrypt('admin123');
        $user->pfp =   Storage::url("dynamic/profile-2.png");
        $user->wallet_address = "abcd1234";
        $user->balance = 222;
        $user->bio = "Just another baker blogger";
        $user->bg = Storage::url("dynamic/post-8.png");
        $user->referral_token = $this->generateReferral();
        $user->save();

        $user = new User();
        $user->name = "Cecelia Hong";
        $user->email = "user3@site.com";
        $user->password = bcrypt('admin123');
        $user->pfp =   Storage::url("dynamic/profile-3.png");
        $user->wallet_address = "abcd1234";
        $user->balance = 111;
        $user->bio = "Just another baker blogger";
        $user->bg = Storage::url("dynamic/post-8.png");
        $user->referral_token = $this->generateReferral();
        $user->save();

        $user = new User();
        $user->name = "Melissa Shen";
        $user->email = "user4@site.com";
        $user->password = bcrypt('admin123');
        $user->pfp =   Storage::url("dynamic/profile-4.png");
        $user->wallet_address = "abcd1234";
        $user->balance = 333;
        $user->bio = "Just another baker blogger";
        $user->bg = Storage::url("dynamic/post-8.png");
        $user->referral_token = $this->generateReferral();
        $user->save();

        $user = new User();
        $user->name = "Darren Jones";
        $user->email = "user5@site.com";
        $user->password = bcrypt('admin123');
        $user->pfp =   Storage::url("dynamic/profile-5.png");
        $user->wallet_address = "abcd1234";
        $user->balance = 444;
        $user->bio = "Just another baker blogger";
        $user->bg = Storage::url("dynamic/post-8.png");
        $user->referral_token = $this->generateReferral();
        $user->save();

        $user = new User();
        $user->name = "Jessica Covington";
        $user->email = "user6@site.com";
        $user->password = bcrypt('admin123');
        $user->pfp =   Storage::url("dynamic/userprofile-1.png");
        $user->wallet_address = "abcd1234";
        $user->balance = 1256;
        $user->bio = "She turned her can't into can and her dreams into plans.";
        $user->bg = Storage::url("dynamic/post-8.png");
        $user->referral_token = $this->generateReferral();
        $user->save();
    }

    protected function generateReferral()
    {
        $length = 5;

        do {
            $referral = Str::random($length);
        } while (User::where('referral_token', $referral)->exists());

        return $referral;
    }
}
