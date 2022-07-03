<?php

use App\Document;
use Illuminate\Database\Seeder;

class DocumentsTableSeeder extends Seeder
{
    
    public function run()
    {
        Document::truncate();

        $faker = Faker\Factory::create();

        for ( $i = 0; $i < 50; $i++) { 
            Document::create([
                'doc_date' => $faker->dateTime,
                'doc_number' => $faker->numberBetween($min = 1, $max = 100),
                'doc_type' => $faker->numberBetween($min = 0, $max = 1),
                'file_path' => $faker->text,
                'subject' => $faker->text,
                'to' => $faker->name,
                'from' => $faker->name,
            ]);
        }
    }
}
