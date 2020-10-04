<?php

use Tkeer\Flattable\Test\Database\Factories\BookFactory;
use Tkeer\Flattable\Test\Models\Author;
use Tkeer\Flattable\Test\Models\Book;
use Tkeer\Flattable\Test\Models\Publisher;

$factory->define(Author::class, function (Faker\Generator $faker) {
    return [
        'first_name' => $this->faker->name,
        'last_name' => $this->faker->unique()->safeEmail,
        'dob' => now(),
    ];
});

$factory->define(Book::class, function (Faker\Generator $faker) {
    return [
        'name' => $this->faker->name,
        'published_at' => $this->faker->dateTimeBetween(),
    ];
});

$factory->define(Publisher::class, function (Faker\Generator $faker) {
    return [
        'first_name' => $this->faker->firstName,
        'last_name' => $this->faker->lastName,
    ];
});


