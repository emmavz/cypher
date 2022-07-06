<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Tag::getQuery()->delete();

        $tag = new Tag();
        $tag->name = "Fashion";
        $tag->save();

        $tag = new Tag();
        $tag->name = "Beauty";
        $tag->save();

        $tag = new Tag();
        $tag->name = "Writing";
        $tag->save();

        $tag = new Tag();
        $tag->name = "Plants";
        $tag->save();

        $tag = new Tag();
        $tag->name = "Knitting";
        $tag->save();

        $tag = new Tag();
        $tag->name = "Gaming";
        $tag->save();

        $tag = new Tag();
        $tag->name = "Movies";
        $tag->save();

        $tag = new Tag();
        $tag->name = "Books";
        $tag->save();
    }
}
