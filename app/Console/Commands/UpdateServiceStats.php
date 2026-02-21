<?php

namespace App\Console\Commands;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\ServiceResource;
use App\Models\Category;
use App\Models\Country;
use App\Models\Scopes\TenantScope;
use App\Models\Service;
use App\Settings\HeroesSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class UpdateServiceStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:update-stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pre-calculate and cache hot/most-requested services for all countries';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating service stats cache...');

        $countries = Country::all();

        foreach ($countries as $country) {
            $this->updateCountryStats($country);
        }

        // Cache categories (not country-specific)
        $this->cacheCategories();

        // Cache hero data
        $this->cacheHeroData();

        $this->info('Service stats cache updated successfully!');

        return Command::SUCCESS;
    }

    private function updateCountryStats(Country $country)
    {
        $this->info("Updating stats for country: {$country->name}");

        // Cache most requested services (15 minutes)
        $mostRequested = Service::query()->withoutGlobalScopes([TenantScope::class])
            ->select('id', 'name', 'image', 'category_id', 'country_id')
            ->where('country_id', $country->id)
            ->mostRequested(8)
            ->get();

        Cache::put("most_requested_{$country->id}", $mostRequested, now()->addMinutes(15));

        // Cache hot services (15 minutes)
        $hotServices = Service::query()->withoutGlobalScopes([TenantScope::class])
            ->select('id', 'name', 'image', 'category_id', 'country_id')
            ->where('country_id', $country->id)
            ->hotNow(8)
            ->get();

        Cache::put("hot_services_{$country->id}", $hotServices, now()->addMinutes(15));

        // Cache latest services (15 minutes)
        $latestServices = Service::query()->withoutGlobalScopes([TenantScope::class])
            ->select('id', 'name', 'image', 'category_id', 'country_id', 'created_at')
            ->where('country_id', $country->id)
            ->latest()
            ->limit(8)
            ->get();

        Cache::put("latest_services_{$country->id}", $latestServices, now()->addMinutes(15));
    }

    private function cacheCategories()
    {
        $this->info("Updating categories cache...");

        $categories = Category::active()
            ->whereNull('parent_id')
            ->withCount('services')
            ->orderBy('services_count', 'desc')
            ->get();

        Cache::put("home_categories", $categories, now()->addMinutes(15));
    }

    private function cacheHeroData()
    {
        $this->info("Updating hero data cache...");

        $herosSettings = app(HeroesSettings::class);

        $heroData = [
            'hero_path' => $herosSettings->getMainHero(),
            'title' => [
                'en' => $herosSettings->text_en,
                'ar' => $herosSettings->text_ar,
            ],
        ];

        Cache::put("home_hero", $heroData, now()->addMinutes(15));
    }
}
