<?php
namespace Jamesst20\KahlanLaravel\Console;

use Illuminate\Console\Command;

use Kahlan\Box\Box;
use Kahlan\Suite;
use Kahlan\Cli\Kahlan;

use Jamesst20\KahlanLaravel\Testing\KahlanWrapper;

class KahlanRunCommand extends Command
{
    protected $signature = 'kahlan:run';

    protected $description = 'Run all specs';

    public function handle()
    {
        /**
         * While we could load manually .env.testing or .env.kahlan here, we won't because
         * it basically mean that .env might have been loaded and we don't want production environment
         * variables in our test environment. 'php artisan kahlan:run' will load .env file unless
         * we do 'APP_ENV=testing php artisan kahlan:run' where .env.testing is loaded. It is the default
         * behavior provided by Laravel.
         */
        if (env('APP_ENV') != 'testing') {
            $this->error("Error: APP_ENV must be equal to testing");
            $this->error("Either prefix APP_ENV=testing to your command or use an environment file : ");
            $this->error("--> .env.testing or .env.kahlan to set APP_ENV=testing");
            return 0;
        }
        $autoloaders = $this->registerAutoloaders();
        $specs       = $this->createKahlan($autoloaders);
        (new KahlanWrapper())->registerLaravelToKahlan($specs);
        $specs->run();
        return $specs->status();
    }

    public function createKahlan(array $autoloaders)
    {
        // Kahlan initialization extracted from /vendor/bin/kahlan
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }

        $box = \Kahlan\box('kahlan', new Box());
        $box->service('suite.global', function () {
            return new Suite();
        });
        $specs = new Kahlan([
            'autoloader' => reset($autoloaders),
            'suite'      => $box->get('suite.global')
        ]);
        initKahlanGlobalFunctions();
        return $specs;
    }

    public function registerAutoloaders()
    {
        $autoloaders = [];
        $vendorAutoloadPath = base_path('vendor/autoload.php');

        $autoloaders[] = include $vendorAutoloadPath;

        if (!$autoloaders) {
            $this->error("\033[1;31mYou need to set up the project dependencies using the following commands: \033[0m");
            $this->error("curl -s http://getcomposer.org/installer | php");
            $this->error("php composer.phar install");
            exit(1);
        }

        return $autoloaders;
    }
}
