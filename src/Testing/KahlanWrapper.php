<?php

namespace Jamesst20\KahlanLaravel\Testing;

use Throwable;

use Dotenv\Dotenv;

use Kahlan\Suite;
use Kahlan\Cli\Kahlan;
use Kahlan\Filter\Filters;

use Jamesst20\KahlanLaravel\Testing\LaravelTestCase;

class KahlanWrapper
{

    public function __construct()
    {
        if (is_file(base_path('.env.kahlan'))) {
            Dotenv::create(base_path(), '.env.kahlan')->overload();
        }
    }

    public function registerLaravelToKahlan(Kahlan $specs)
    {
        $instance = $this;

        // Add stuffs to Kahlan testing scope
        Filters::apply($specs, 'run', function ($next) use ($specs, $instance) {
            $specs->suite()->root()->beforeEach($instance->testSetup($specs));
            $specs->suite()->root()->afterEach($instance->testTearDown($specs));

            return $next();
        });
    }

    /**
     * Provide fresh application instance for each single spec.
     * Create and add Laravel TestCase to Kahlan scope and call setUp() on TestCase
     * @return \Closure
     */
    public function testSetup($specs)
    {
        $instance = $this;
        return function () use ($instance, $specs) {
            $currentScope = Suite::current()->scope();
            $rootScope = $specs->suite()->root()->scope();

            $laravel = new LaravelTestCase($instance->tryRead($currentScope, 'enabledTraits', array()));
            $laravel->baseUrl = env('BASE_URL', 'localhost');
            $laravel->setUp();

            // Add to root scope to prevent multiple definition
            $rootScope->app = $laravel->app;
            $rootScope->laravel = $laravel;
        };
    }

    /**
     * Destroy the TestCase for each single spec and clean up Kahlan scope.
     * Calls tearDown on the TestCase to trigger all events associated.
     * @return \Closure
     */
    public function testTearDown($specs) {
        return function() use($specs) {
            $rootScope = $specs->suite()->root()->scope();
            $rootScope->laravel->tearDown();
            $rootScope->laravel = null;
            $rootScope->app = null;
        };        
    }

    private static function tryRead($instance, $variable, $default = null)
    {
        try {
            return $instance->$variable ?: $default;
        } catch (Throwable $e) { }
        return $default;
    }
}
