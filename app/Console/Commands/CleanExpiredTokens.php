<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserToken;
use Carbon\Carbon;

class CleanExpiredTokens extends Command
{
    // Command signature
    protected $signature = 'tokens:clean';

    // Command description
    protected $description = 'Delete expired user tokens older than 2 days';

    public function handle()
    {
        // 3-day buffer
        $threshold = Carbon::now()->subDays(3);

        // Delete expired tokens older than 2 days
        $count = UserToken::whereNotNull('expires_at')
            ->where('expires_at', '<', $threshold)
            ->delete();

        $this->info("Deleted {$count} expired user tokens older than 2 days.");
    }
}
