# Jamesst20/kahlan-laravel

[Kahlan](https://kahlan.github.io/docs) is a full-featured Unit & BDD test framework a la RSpec/JSpec which uses a describe-it syntax and moves testing in PHP one step forward.

## Why do I need this package

While it's true that you can use the vanilla Kahlan library, you cannot access to any of the Laravel TestCase helpers method.

## Compatibility

Only **Laravel 5.8** is currently supported because this library relies on phpdotenv v3.0 shipped with Laravel. It also has been only tested with the latest **Kahlan** version (v4.5 as of now).

## Usage

Getting the application instance : `$this->app`, `$this->laravel->app`, `app()` are all equivalent ways and will return the same instance.

In a vanilla Laravel TestCase, we can access helpers by doing `$this->insertHelperMethod` such as `$this->assertDatabaseHas('users', ['email' => 'test@email.com']);`

In a kahlan environment, it is as simple as prefixing the method with `$this->laravel->insertHelperMethod`.

Please note that `$this->laravel`and `$this->app` is not accessible in `beforeAll`, `afterAll` callback but is as soon as in `beforeEach`.

## Example

Vanilla Laravel TestCase (PHPUnit)
```
<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function testBasicTest()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function testUserCreation() {
        factory(User::class)->create(['email' => 'test@email.com']);
        $this->laravel->assertDatabaseHas('users', ['email' => 'test@email.com']);
    }
}
```

Kahlan
```
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

describe("Homepage", function() {
    it("loads successfully", function() {
        $response = $this->laravel->get('/');
        expect($response->getStatusCode())->toEqual(200);
    });
});

describe("User", function() {
    beforeAll(function() {
        $this->enabledTraits = array(RefreshDatabase::class);
    });

    it("should save user to database", function() {
        factory(User::class)->create(['email' => 'test@email.com']);
        // There are cleaner way than using assertDatabaseHas though.
        expect(function() {
            $this->laravel->assertDatabaseHas('users', ['email' => 'test@email.com']);
        })->not->toThrow();
    });
});
```

## Laravel Traits

The recommended way of enabling Laravel traits in Kahlan-Laravel is by passing an array of traits in the beforeAll() closure.

```
describe("Something that needs database", function() {
    beforeAll(function() {
        $this->enabledTraits = array(RefreshDatabase::class);
    });
});
```

**Currently supported traits**
`RefreshDatabase`, `WithoutMiddleware`, `WithoutEvents`, `WithFaker`


## Environment

Environment variables are read in `.env.testing` and are also overriden by `.env.kahlan`

During initialization, before Kahlan is even loaded, if `.env.testing` exists, only this file is loaded. `.env.kahlan` is loaded a little later and will override `.env.testing` variables in case of collisions.

Please note that kahlan will raise an error if it is not executed in the testing environment.

## Executing

There is currently 3 ways to execute kahlan but I am currently only supporting 2 officially.

Using artisan :
```
APP_ENV=testing php artisan kahlan:run
```

Using the binary

```
vendor/bin/kahlan-laravel
```

The difference between the 2 is that the binary will automatically read the `APP_ENV` from `.env.testing` or `.env.kahlan` before bootstraping.

Due to the current implementation of Laravel, there is no clean way to avoid the need to specify the APP_ENV=testing before the Artisan command. Laravel automatically loads `.env` if `APP_ENV` is undefined, otherwise `.env.{environment}`. While it would be possible to override `.env` with the testing ones, there could be some production variables that have no override candidate in the testing environment files.

## What is missing

You tell me :) !

## Credits

To all contributors of Laravel, Symfony and Kahlan.

This library was inspired by Sofa/laravel-kohlan and also elephantly/kahlan-bundle.