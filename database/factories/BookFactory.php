<?php

$factory->define(App\Book::class, function (Faker\Generator $faker) {
    return [
        "name" => $faker->name,
    ];
});
