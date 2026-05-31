<?php

namespace Database\Factories;

use Phare\Database\BaseFactory;

class PostFactory extends BaseFactory
{
    public function definition(): array
    {
        $faker = $this->faker();

        return [
            'title' => $faker->sentence(4),
            'body' => $faker->paragraph(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
    }
}
