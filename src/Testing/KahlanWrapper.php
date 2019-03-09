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
            $specs->suite()->root()->beforeEach($instance->refreshTestCaseInstance($specs));
            // TODO: Make this run on the last afterEach !
            $specs->suite()->root()->afterEach(function () {
                $this->laravel->tearDown();
            });

            return $next();
        });
    }

    /**
     * Provide fresh application instance for each single spec.
     * Create and add Laravel TestCase to Kahlan scope
     * @return \Closure
     */
    public function refreshTestCaseInstance($specs)
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

    private static function tryRead($instance, $variable, $default = null)
    {
        try {
            return $instance->$variable ?: $default;
        } catch (Throwable $e) { }
        return $default;
    }
}
