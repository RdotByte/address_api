<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;

class RevokeApiToken extends Command
{
    protected $signature = 'sanctum:revoke-token
                          {--user= : The email of the user}
                          {--token= : The ID of the token to revoke}
                          {--all : Revoke all tokens for the user}';

    protected $description = 'Revoke API token(s)';

    public function handle()
    {
        $email = $this->option('user');
        $tokenId = $this->option('token');
        $all = $this->option('all');

        if (!$email && !$tokenId) {
            $this->error('Please provide either a user email or token ID');
            return 1;
        }

        if ($email) {
            $user = User::where('email', $email)->first();

            if (!$user) {
                $this->error("User not found: {$email}");
                return 1;
            }

            if ($all) {
                $count = $user->tokens()->delete();
                $this->info("Revoked {$count} tokens for user {$email}");
                return 0;
            }
        }

        if ($tokenId) {
            $token = PersonalAccessToken::find($tokenId);
            if (!$token) {
                $this->error("Token not found: {$tokenId}");
                return 1;
            }

            $token->delete();
            $this->info("Token {$tokenId} revoked successfully");
        }

        return 0;
    }
}
