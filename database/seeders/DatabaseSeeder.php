<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

//        Artisan::call('migrate:fresh');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->call(CountriesTableSeeder::class);
        $this->call(BlockReasonsSeeder::class);
        $this->call(CitiesTableSeeder::class);
        $this->call(CategoriesTableSeeder::class);
        $this->call(ServicesTableSeeder::class);
        $this->call(CategoryServiceTableSeeder::class);
        $this->call(QuestionsTableSeeder::class);
        $this->call(AnswersTableSeeder::class);
        $this->call(QASTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(CustomersTableSeeder::class);
        $this->call(SellersTableSeeder::class);
        $this->call(SellerProfileServicesTableSeeder::class);
        $this->call(SellerQASTableSeeder::class);
        $this->call(SellerSocialMediaTableSeeder::class);
        $this->call(SettingsTableSeeder::class);
        $this->call(UserCountriesTableSeeder::class);
        $this->call(CurrencySeeder::class);
        $this->call(EstimateBasesTableSeeder::class);
        $this->call(PartnersTableSeeder::class);
        $this->call(CompanySizesTableSeeder::class);


        DB::statement('SET FOREIGN_KEY_CHECKS=1;');


        $this->call(UpdateCountriesTableSeeder::class);
        $this->call(PaymentMethodsTableSeeder::class);
    }
}
