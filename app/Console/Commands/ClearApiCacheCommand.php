<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearApiCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-api {--key= : Specific cache key to clear}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear cached API response data (countries, block reasons, estimate bases, etc.)';

    /**
     * Cache keys used for API responses
     */
    protected array $apiCacheKeys = [
        'api_countries',
        'api_block_reasons',
        'api_estimate_bases',
        'qa_list',
        'categories_data',
        'services_data',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $specificKey = $this->option('key');

        if ($specificKey) {
            if (in_array($specificKey, $this->apiCacheKeys)) {
                Cache::forget($specificKey);
                $this->info("Cleared cache key: {$specificKey}");
            } else {
                $this->error("Unknown cache key: {$specificKey}");
                $this->info("Available keys: " . implode(', ', $this->apiCacheKeys));
                return 1;
            }
        } else {
            foreach ($this->apiCacheKeys as $key) {
                Cache::forget($key);
            }
            $this->info('Cleared all API cache keys:');
            $this->table(['Cache Key'], array_map(fn($k) => [$k], $this->apiCacheKeys));
        }

        return 0;
    }
}
