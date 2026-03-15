<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\Organization;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all organizations
        $organizations = Organization::all();

        if ($organizations->isEmpty()) {
            $this->command->warn('No organizations found. Please run OrganizationSeeder first.');
            return;
        }

        // Sample teams for each organization
        $teamNames = [
            'Human Resources',
            'Information Technology',
            'Finance',
            'Marketing',
            'Sales',
            'Operations',
            'Customer Service',
            'Research & Development',
            'Legal',
            'Administration'
        ];

        foreach ($organizations as $organization) {
            // Create 3-5 random teams for each organization
            $randomTeams = collect($teamNames)->random(rand(3, 5));
            
            foreach ($randomTeams as $teamName) {
                Team::create([
                    'name' => $teamName,
                    'organization_id' => $organization->id,
                ]);
            }
        }

        $this->command->info('Teams seeded successfully!');
    }
}
