<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateApiToken extends Command
{
    protected $signature = 'sanctum:create-token
                          {name : The name of the user/company}
                          {email : The email of the user/company}
                          {--abilities=* : The abilities of the token}';

    protected $description = 'Create a new API token using Sanctum';

    public function handle()
    {
        $name = $this->argument('name');
        $email = $this->argument('email');
        $abilities = $this->option('abilities') ?: ['postcode:lookup'];

        // Find or create user
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make(Str::random(32)),
            ]
        );

        // Create token with specified abilities
        $token = $user->createToken(
            'api-token-' . now()->format('Y-m-d-H-i-s'),
            $abilities
        );

        $this->info('API Token created successfully!');
        $this->info('User Details:');
        $this->table(
            ['Name', 'Email'],
            [[$user->name, $user->email]]
        );

        $this->info('Token Details:');
        $this->table(
            ['Token', 'Abilities'],
            [[
                $token->plainTextToken,
                implode(', ', $abilities)
            ]]
        );

        $this->warn('Please store this token securely - it won\'t be shown again!');
    }
}
