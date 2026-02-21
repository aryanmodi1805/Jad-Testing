<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Seller;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExportCustomersAndSellers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:customers-sellers {--dir=exports : Directory inside storage/app where files will be saved}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export all customers and sellers details into text files (one JSON line per record).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dir = trim($this->option('dir'), '/');
        $timestamp = now()->format('Ymd_His');

        $customerPath = "{$dir}/customers_{$timestamp}.txt";
        $sellerPath = "{$dir}/sellers_{$timestamp}.txt";

        // Ensure directory exists
        Storage::makeDirectory($dir);

        $this->info('Exporting customers...');
        $this->exportModel(Customer::class, $customerPath);

        $this->info('Exporting sellers...');
        $this->exportModel(Seller::class, $sellerPath);

        $this->newLine();
        $this->info('Export completed successfully.');
        $this->line('Customers file: ' . storage_path("app/{$customerPath}"));
        $this->line('Sellers file:   ' . storage_path("app/{$sellerPath}"));

        return self::SUCCESS;
    }

    /**
     * Export the given model to a text file as JSON lines.
     *
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelClass
     * @param  string  $path
     */
    protected function exportModel(string $modelClass, string $path): void
    {
        // Start with an empty file.
        Storage::put($path, '');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass();

        $hidden = $model->getHidden();

        $modelClass::chunkById(500, function ($records) use ($path, $hidden) {
            $lines = [];

            foreach ($records as $record) {
                $array = $record->toArray();

                // Remove hidden attributes like passwords/tokens.
                foreach ($hidden as $key) {
                    unset($array[$key]);
                }

                $lines[] = json_encode($array, JSON_UNESCAPED_UNICODE);
            }

            if (! empty($lines)) {
                Storage::append($path, implode(PHP_EOL, $lines));
            }
        });
    }
}

