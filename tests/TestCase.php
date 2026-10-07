<?php

namespace Tests;

use App\Models\Business;
use App\Models\User;
use App\Services\Demo\DemoScenarioBuilder;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\DiagnosticQuestionSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function seedCatalog(): void
    {
        $this->seed([DiagnosticQuestionSeeder::class, CatalogSeeder::class]);
    }

    /** Build one demo scenario and return its business. */
    protected function scenario(string $key): Business
    {
        $this->seedCatalog();
        app(DemoScenarioBuilder::class)->build($key);

        return User::where('email', DemoScenarioBuilder::ACCOUNTS[$key]['email'])->firstOrFail()->business;
    }
}
