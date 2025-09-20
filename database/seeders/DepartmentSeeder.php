<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Organization;

class DepartmentSeeder extends Seeder
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

        // Sample departments for each organization
        $departmentNames = [
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
            // Create 3-5 random departments for each organization
            $randomDepartments = collect($departmentNames)->random(rand(3, 5));
            
            foreach ($randomDepartments as $departmentName) {
                Department::create([
                    'name' => $departmentName,
                    'organization_id' => $organization->id,
                ]);
            }
        }

        $this->command->info('Departments seeded successfully!');
    }
}
