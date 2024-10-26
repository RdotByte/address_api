<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ListApiTokens extends Command
{
    protected $signature = 'sanctum:list-tokens {email? : The email of the user}';
    protected $description = 'List all API tokens';

    public function handle()
    {
        $email = $this->argument('email');

        $query = User::query();
        if ($email) {
            $query->where('email', $email);
        }

        $users = $query->get();

        foreach ($users as $user) {
            $this->info("\nTokens for {$user->name} ({$user->email}):");

            $tokens = $user->tokens->map(function ($token) {
                return [
                    $token->id,
                    $token->name,
                    implode(', ', $token->abilities),
                    $token->created_at->format('Y-m-d H:i:s'),
                    $token->last_used_at ? $token->last_used_at->format('Y-m-d H:i:s') : 'Never'
                ];
            });

            if ($tokens->isEmpty()) {
                $this->warn('No tokens found');
                continue;
            }

            $this->table(
                ['ID', 'Name', 'Abilities', 'Created At', 'Last Used'],
                $tokens
            );
        }
    }
}
