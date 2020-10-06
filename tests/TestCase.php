<?php

namespace Tkeer\Flattable\Test;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Tkeer\Flattable\FlattableServiceProvider;
use Tkeer\Flattable\Test\Models\Author;
use Tkeer\Flattable\Test\Models\Book;
use Tkeer\Flattable\Test\Models\BookFlattable;
use Tkeer\Flattable\Test\Models\Country;
use Tkeer\Flattable\Test\Models\Publisher;

class TestCase extends OrchestraTestCase
{
    use DatabaseMigrations, WithFaker;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->withFactories(__DIR__ . '/database/factories');
    }

    protected function getPackageProviders($app)
    {
        return [FlattableServiceProvider::class];
    }
}