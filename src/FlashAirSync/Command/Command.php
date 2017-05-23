<?php

namespace FlashAirSync\Command;

use FlashAirSync\App\App;
use FlashAirSync\Service\Exception as ServiceException;
use Zend\Console\Adapter\AdapterInterface;
use ZF\Console\Route;

declare(ticks=1);

class Command
{
    protected $interrupted = false;

    public function __invoke(Route $route, AdapterInterface $console)
    {
        $remoteHost = $route->getMatchedParam('remoteHost');
        $remoteDir = $route->getMatchedParam('remoteDir');
        $localWorkingDir = realpath(getcwd() . DIRECTORY_SEPARATOR . 'data');
        $targetDirectory = getcwd();

        $versions = array(1, 2);
        $app = new App($console, $remoteHost, $remoteDir, $localWorkingDir, $targetDirectory);

        while (!$this->interrupted) {
            try {
                $this->run($app, $versions);
            } catch (ServiceException $exception) {
                $console->writeLine('Error: ' . $exception->getMessage());
            }
            $this->sleep(15);
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

    protected function sleep(float $secondsToSleep)
    {
        $interval = 0.1;
        while($secondsToSleep > 0) {
            usleep($interval * 1000000);
            $secondsToSleep -= $interval;
        }
    }

    protected function setUpInterrupts(): void
    {
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, array($this, 'interrupt'));
        }
    }

    public function interrupt()
    {
        if ($this->interrupted) {
            exit;
        } else {
            $this->interrupted = true;
        }
    }
}
