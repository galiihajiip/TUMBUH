<?php

namespace Database\Seeders;

use App\Services\Demo\DemoScenarioBuilder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([DiagnosticQuestionSeeder::class, CatalogSeeder::class]);

        // Demo scenarios — illustrative data only, not real companies.
        app(DemoScenarioBuilder::class)->buildAll();
    }
}
