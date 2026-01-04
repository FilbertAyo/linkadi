<?php

namespace App\Console\Commands;

use App\Models\Profile;
use App\Services\ProfilePublishingService;
use Illuminate\Console\Command;

class ExpireProfiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'profiles:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire published profiles that have passed their expiry date';

    protected ProfilePublishingService $publishingService;

    /**
     * Create a new command instance.
     */
    public function __construct(ProfilePublishingService $publishingService)
    {
        parent::__construct();
        $this->publishingService = $publishingService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired profiles...');

        // Find all profiles that are published but have passed their expiry date
        $expiredProfiles = Profile::where('status', 'published')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        if ($expiredProfiles->isEmpty()) {
            $this->info('No expired profiles found.');
            return 0;
        }

        $this->info("Found {$expiredProfiles->count()} expired profile(s). Processing...");

        $successCount = 0;
        $failureCount = 0;

        foreach ($expiredProfiles as $profile) {
            try {
                $this->publishingService->expire($profile);
                $this->line("✓ Expired profile ID: {$profile->id} (User: {$profile->user->name})");
                $successCount++;
            } catch (\Exception $e) {
                $this->error("✗ Failed to expire profile ID: {$profile->id} - {$e->getMessage()}");
                $failureCount++;
            }
        }

        $this->newLine();
        $this->info("Profile expiry complete!");
        $this->info("Successfully expired: {$successCount}");
        
        if ($failureCount > 0) {
            $this->warn("Failed: {$failureCount}");
        }

        return $successCount > 0 ? 0 : 1;
    }
}
