<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\MatchmakingService;
use Illuminate\Console\Command;

class RunMatchmaking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sets:run-matchmaking {--dry-run : Show what would be created without actually creating sessions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the SETS matchmaking algorithm to create padel sessions';

    /**
     * Execute the console command.
     */
    public function handle(MatchmakingService $matchmakingService): int
    {
        $this->info('🏓 Starting SETS Matchmaking Algorithm...');
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE - No sessions will be created');
            $this->newLine();
        }

        // Run the matchmaking algorithm
        $results = $matchmakingService->runMatchmaking();

        // Display results
        $this->displayResults($results);

        return Command::SUCCESS;
    }

    /**
     * Display the matchmaking results
     */
    private function displayResults(array $results): void
    {
        $this->info('📊 Matchmaking Results:');
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->line("• Sessions that would be created: {$results['sessions_created']}");
            $this->line("• Invitations that would be sent: {$results['invitations_sent']}");
        } else {
            $this->line("• Sessions created: {$results['sessions_created']}");
            $this->line("• Invitations sent: {$results['invitations_sent']}");
        }

        if (!empty($results['errors'])) {
            $this->newLine();
            $this->error('❌ Errors encountered:');
            foreach ($results['errors'] as $error) {
                $this->line("  • {$error}");
            }
        }

        $this->newLine();
        
        if ($results['sessions_created'] > 0) {
            $this->info('✅ Matchmaking completed successfully!');
        } else {
            $this->warn('⚠️  No sessions were created. Check the errors above.');
        }
    }
}
