<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        collect(['Staff One', 'Staff Two', 'Staff Three', 'Staff Four'])
            ->each(fn (string $name, int $i) => User::factory()->create([
                'name' => $name,
                'email' => 'staff'.($i + 1).'@example.com',
            ]));

        $this->call(ProjectSeeder::class);
    }
}
