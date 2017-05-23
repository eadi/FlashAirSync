<?php

namespace FlashAirSync\Command;

use FlashAirSync\Entity\Repository\File as FileRepository;
use FlashAirSync\Service\Service;
use FlashAirSync\Worker\Cleanup;
use FlashAirSync\Worker\Compare;
use FlashAirSync\Worker\Discover;
use FlashAirSync\Worker\Download;
use FlashAirSync\Worker\Store;
use Zend\Console\Adapter\AdapterInterface;
use ZF\Console\Route;

class Command
{
    protected $remoteHost;
    protected $remoteDir;
    protected $localWorkingDir;
    protected $localRepositoryDir;
    protected $targetDirectory;

    /**
     * @var AdapterInterface
     */
    protected $console;

    /**
     * @var Service
     */
    protected $service;

    /**
     * @var FileRepository
     */
    protected $fileRepository;

    public function __invoke(Route $route, AdapterInterface $console)
    {
        $this->remoteHost = $route->getMatchedParam('remoteHost');
        $this->remoteDir = $route->getMatchedParam('remoteDir');
        $this->localWorkingDir = realpath(getcwd() . DIRECTORY_SEPARATOR . 'data');
        $this->localRepositoryDir = $this->localWorkingDir . DIRECTORY_SEPARATOR . 'repository';
        $this->targetDirectory = getcwd();

        $this->console = $console;
        $this->service = new Service($this->remoteHost, $this->remoteDir, $this->localWorkingDir);
        $this->fileRepository = new FileRepository($this->localRepositoryDir, $this->remoteDir);

        $versions = array(1, 2);

        $this->discover();
        foreach ($versions as $version) {
            $this->download($version);
        }
        $this->compare($versions);
        $this->store(reset($versions));
        $this->cleanup($versions);

        $this->console->writeLine('Finished. Nothing more to sync.');
    }

    protected function discover(): void
    {
        $discoverWorker = new Discover();
        $this->console->writeLine('Discovered ' . $discoverWorker($this->service, $this->fileRepository) . ' additional files.');
    }

    protected function download(int $version): void
    {
        $downloadWorker = new Download($this->service);
        $downloadCounter = 0;
        foreach ($this->fileRepository->getAll() as $localFileEntity) {
            if (!$localFileEntity->getDownloadedAtByIndex($version)) {
                $downloadWorker($localFileEntity, $this->localWorkingDir . DIRECTORY_SEPARATOR . $version);
                $localFileEntity->setDownloadedAtByIndex($version, time());
                $this->fileRepository->save($localFileEntity);
                $downloadCounter++;
            }
        }
        $this->console->writeLine('Downloaded ' . $downloadCounter . ' files for the ' . $version . '. time.');
    }

    protected function compare(array $versions): void
    {
        $compareWorker = new Compare();
        $compareCounterSuccess = 0;
        $compareCounterError = 0;
        foreach ($this->fileRepository->getAll() as $localFileEntity) {
            if (!$localFileEntity->getComparedAt()) {
                $leftVersion = array_shift($versions);
                foreach ($versions as $rightVersion) {
                    if (!$compareWorker(
                        $this->localWorkingDir . DIRECTORY_SEPARATOR . $leftVersion . DIRECTORY_SEPARATOR . $localFileEntity->getDirectory() . DIRECTORY_SEPARATOR . $localFileEntity->getName(),
                        $this->localWorkingDir . DIRECTORY_SEPARATOR . $rightVersion . DIRECTORY_SEPARATOR . $localFileEntity->getDirectory() . DIRECTORY_SEPARATOR . $localFileEntity->getName()
                    )) {
                        $localFileEntity->setDownloadedAt(array());
                        $compareCounterError++;
                        continue 2;
                    }
                }
                $localFileEntity->setComparedAt(time());
                $compareCounterSuccess++;
                $this->fileRepository->save($localFileEntity);
            }
        }
        $this->console->writeLine('Compared ' . $compareCounterSuccess . ' files successfully and ' . $compareCounterError . ' files with errors.');
    }

    protected function store(int $fromVersion): void
    {
        $storeWorker = new Store();
        foreach ($this->fileRepository->getAll() as $localFileEntity) {
            if ($localFileEntity->getComparedAt() && !$localFileEntity->getStoredAt()) {
                if ($storeWorker(
                    $this->localWorkingDir . DIRECTORY_SEPARATOR . $fromVersion . DIRECTORY_SEPARATOR . $localFileEntity->getDirectory() . DIRECTORY_SEPARATOR . $localFileEntity->getName(),
                    $this->targetDirectory . DIRECTORY_SEPARATOR . $localFileEntity->getName()
                )) {
                    $localFileEntity->setStoredAt(time());
                    $this->console->writeLine('Stored file ' . $localFileEntity->getName() . ' successfully.');
                    $this->fileRepository->save($localFileEntity);
                } else {
                    $this->console->writeLine('Error storing file ' . $localFileEntity->getName() . ' to ' . $this->targetDirectory . ' successfully.');
                }
            }
        }
    }

    protected function cleanup(array $versions): void
    {
        $cleanupWorker = new Cleanup();
        foreach ($this->fileRepository->getAll() as $localFileEntity) {
            if ($localFileEntity->getStoredAt() && !$localFileEntity->getCleanedUpAt()) {
                foreach ($versions as $version) {
                    $cleanupWorker($this->localWorkingDir . DIRECTORY_SEPARATOR . $version . DIRECTORY_SEPARATOR . $localFileEntity->getDirectory() . DIRECTORY_SEPARATOR . $localFileEntity->getName());
                }
                $localFileEntity->setCleanedUpAt(time());
                $this->fileRepository->save($localFileEntity);
            }
        }
    }
}
