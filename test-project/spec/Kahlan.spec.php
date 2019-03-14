<?php

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Contracts\Foundation\Application;

use Jamesst20\KahlanLaravel\Testing\LaravelTestCase;

describe('Laravel context', function () {
    it('creates laravel application', function () {
        expect(app())->toBeAnInstanceOf(Application::class);
    });

    it('provides laravel test case instance', function() {
        expect($this->laravel)->toBeAnInstanceOf(TestCase::class);
        expect($this->laravel)->toBeAnInstanceOf(LaravelTestCase::class);
    });

    it('makes application accessible in scope and in test case scope', function () {
        expect($this->app)->toBe(app());
        expect($this->laravel->app)->toBe(app());
    });

    it('binds to the container', function () {
        $stub = ['name' => 'stub'];
        $this->app->bind('some_service', function () use ($stub) {
            return $stub;
        });
        expect($this->app->make('some_service'))->toEqual($stub);
    });

    context('TestCase instance should be recreated every single test', function() {
        beforeEach(function() {
            $this->laravel->test1 = true;
        });

        it('edits laravel instance scope', function() {
            $this->laravel->test2 = true;
            expect($this->laravel->test1)->toEqual(true);
            expect($this->laravel->test2)->toEqual(true);
        });

        it('recreates application for each single test', function () {
            expect($this->laravel->test1)->toEqual(true);
            expect($this->laravel->test2)->toEqual(null);
        });
    });

    context('Laravel TestCase methods are accessible from the outside', function () {
        it('crawls & asserts', function () {
            expect(function() {
                $this->laravel->get('/')
                              ->assertSee('Laravel')
                              ->assertSuccessful();
            })->not->toThrow();
        });

        it('interacts with session', function () {
            expect(function() {
                $this->laravel->withSession(['session_test' => 'working'])
                              ->get('/session-test')
                              ->assertSee('working')
                              ->assertSessionHas('session_test','working');
            })->not->toThrow();
        });

        it('interacts with app services', function () {
            expect(function() {
                $this->laravel
                    ->expectsEvents('event_one')
                    ->doesntExpectEvents('event_something');
                event('event_one');
            })->not->toThrow();
        });
    });

    context('Testing traits', function() {
        context('with database trait', function() {
            beforeAll(function() {
                $this->enabledTraits = array(RefreshDatabase::Class);
            });

            it('interacts with database', function () {
                factory(App\User::class)->create(['email' => 'test@email.com']);
                expect(function() {
                    $this->laravel->assertDatabaseHas('users', ['email' => 'test@email.com']);
                })->not->toThrow();
            });
        });

        context('without database trait', function() {
            it('interacts with database', function () {
                expect(function() {
                    factory(App\User::class)->create(['email' => 'test@email.com']);
                })->toThrow();
            });
        });
    });
});

describe("Environment variables", function() {
    it('overrides testing environments variable', function() {
        // Should be defined in both .env.testing and .env.kahlan
        expect(env("APP_NAME"))->toEqual("kahlan");
    });

    it('reads .env.testing', function() {
        // Should only be defined in .env.testing
        expect(env("TESTING_SPECIFIC"))->toEqual("yes");
    });

    it('reads .env.kahlan', function() {
        // Should only be defined in .env.kahlan
        expect(env("KAHLAN_SPECIFIC"))->toEqual("yes");
    });
});

describe("afterEach", function() {
    afterEach(function() {
        if ($this->laravel == null) {
            throw new Exception("Failed asserting that afterEach callbacks are executed in right order");
        }
    });

    it("assert anything to trigger afterEach", function() {
        expect(true)->toEqual(true);
    });
});
