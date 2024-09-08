<?php

namespace Database\Factories;

use App\Models\History;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class HistoryFactory extends Factory
{
    protected $model = History::class;

    public function definition()
    {
        return [
            'id' => (string) Str::uuid(),
            'model_id' => (string) Str::uuid(),
            'model_name' => $this->faker->word,
            'before' => $this->faker->optional()->json(),
            'after' => $this->faker->optional()->json(),
            'action' => $this->faker->randomElement(['created', 'updated', 'deleted']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
