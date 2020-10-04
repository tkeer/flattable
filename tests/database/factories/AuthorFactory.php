<?php

namespace Tkeer\Flattable\Test\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tkeer\Flattable\Test\Models\Author;

class AuthorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Author::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'first_name' => $this->faker->name,
            'last_name' => $this->faker->unique()->safeEmail,
            'dob' => now(),
        ];
    }
}
