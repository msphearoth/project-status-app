<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_code' => 'PRJ-'.$this->faker->unique()->numerify('#####'),
            'year' => now()->year,
            'work_code' => 'WRK-'.$this->faker->numerify('#####'),
            'on_road' => $this->faker->streetName(),
            'start_road' => $this->faker->streetName(),
            'end_road' => $this->faker->streetName(),
            'pipe_type' => $this->faker->randomElement(['PVC', 'HDPE', 'Concrete', 'Steel', 'Ductile Iron']),
            'pipe_diameter' => $this->faker->randomFloat(2, 50, 600),
            'pipe_length' => $this->faker->randomFloat(2, 10, 5000),
            'received_date' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'status' => ProjectStatus::Pending,
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the project is in progress and assigned to a user.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProjectStatus::InProgress,
            'assignee_id' => User::factory(),
        ]);
    }

    /**
     * Indicate that the project has been completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProjectStatus::Completed,
            'assignee_id' => User::factory(),
            'project_amount' => $this->faker->randomFloat(2, 1000, 100000),
            'request_number' => 'REQ-'.$this->faker->unique()->numerify('#####'),
            'completed_at' => now(),
        ]);
    }
}
