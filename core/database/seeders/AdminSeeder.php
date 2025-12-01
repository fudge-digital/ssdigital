<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'info@satriasiliwangibasketball.id'],
            [
                'name' => 'Admin SS',
                'password' => Hash::make('Bdg_2026##'),
                'role' => 'admin'
            ]
        );
        
    }
}
