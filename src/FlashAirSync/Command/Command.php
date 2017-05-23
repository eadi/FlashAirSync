<?php

namespace FlashAirSync\Command;

use FlashAirSync\App\App;
use Zend\Console\Adapter\AdapterInterface;
use ZF\Console\Route;

class Command
{
    public function __invoke(Route $route, AdapterInterface $console)
    {
        $remoteHost = $route->getMatchedParam('remoteHost');
        $remoteDir = $route->getMatchedParam('remoteDir');
        $localWorkingDir = realpath(getcwd() . DIRECTORY_SEPARATOR . 'data');
        $targetDirectory = getcwd();

        $versions = array(1, 2);
        $app = new App($console, $remoteHost, $remoteDir, $localWorkingDir, $targetDirectory);

        while (true) {
            $this->run($app, $versions);
            $console->writeLine('Finished. Nothing more to sync.');
        }
    }

    protected function run(App $app, array $versions): void
    {
        $app->discover();
        foreach ($versions as $version) {
            $app->download($version);
        }
        $app->compare($versions);
        $app->store(reset($versions));
        $app->cleanup($versions);
    }
}
