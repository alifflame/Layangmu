<?php

use App\Documment;
use Faker\Generator as Faker;

$factory->define('App\Document', function ($faker) {
    
    return [
        'doc_date' => $faker->date,
        'doc_number' => $faker->randomDigit,
        'doc_type' => $faker->randomDigit,
        'file_path' => $faker->text,
        'subject' => $faker->text,
        'to' => $faker->name,
        'from' => $faker->name,
    ];
});