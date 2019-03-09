<?php

namespace Jamesst20\KahlanLaravel\Testing;

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\WithoutEvents;
use Illuminate\Foundation\Testing\WithFaker;

/**
 * Wrapper for the Laravel's built-in testing features.
 */
class LaravelTestCase extends TestCase
{
    use CreatesApplication;
    use RefreshDatabase;
    use WithoutMiddleware;
    use WithoutEvents;
    use WithFaker;

    public function __construct($enabledTraits)
    {
        $this->enabledTraits = $enabledTraits;
    }

    /**
     * Override TestCase#setUpTraits because we can't add Trait at runtime, so
     * we must include all of them by default but we don't necessary want to use all
     * of them.
     */
    protected function setUpTraits()
    {
        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[RefreshDatabase::class]) && in_array(RefreshDatabase::class, $this->enabledTraits)) {
            $this->refreshDatabase();
        }

        if (isset($uses[WithoutMiddleware::class]) && in_array(WithoutMiddleware::class, $this->enabledTraits)) {
            $this->disableMiddlewareForAllTests();
        }

        if (isset($uses[WithoutEvents::class]) && in_array(WithoutEvents::class, $this->enabledTraits)) {
            $this->disableEventsForAllTests();
        }

        if (isset($uses[WithFaker::class]) && in_array(WithFaker::class, $this->enabledTraits)) {
            $this->setUpFaker();
        }
    }

    /**
     * Make everything public because we access this class from the outside.
     */
    public function __call($method, $params)
    {
        return method_exists($this, $method)
            ? call_user_func_array([$this, $method], $params)
            : call_user_func_array([$this->app, $method], $params);
    }

    /**
     * Make everything public because we access this class from the outside.
     *
     * @param string $property
     * @param mixed  $value
     */
    public function __set($property, $value)
    {
        $this->{$property} = $value;
    }

    /**
     * Make everything public because we access this class from the outside.
     *
     * @param  string $property
     * @return mixed
     */
    public function __get($property)
    {
        return property_exists($this, $property) ? $this->{$property} : null;
    }
}
