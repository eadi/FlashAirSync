<?php
namespace FlashAirSync\App;

use FlashAirSync\Entity\Repository\File as FileRepository;
use FlashAirSync\Service\Service;
use FlashAirSync\Worker\Cleanup;
use FlashAirSync\Worker\Compare;
use FlashAirSync\Worker\Discover;
use FlashAirSync\Worker\Download;
use FlashAirSync\Worker\Store;
use Zend\Console\Adapter\AdapterInterface;

class App
{
    protected $remoteHost;
    protected $remoteDir;
    protected $localWorkingDir;
    protected $localRepositoryDir;
    protected $targetDirectory;

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var Service
     */
    protected $service;

    /**
     * @var FileRepository
     */
    protected $fileRepository;

    public function __construct(AdapterInterface $adapter, string $remoteHost, string $remoteDir, string $localWorkingDir, string $targetDirectory)
    {
        $this->remoteHost = $remoteHost;
        $this->remoteDir = $remoteDir;
        $this->localWorkingDir = $localWorkingDir;
        $this->targetDirectory  = $targetDirectory;
        $this->localRepositoryDir = $this->localWorkingDir . DIRECTORY_SEPARATOR . 'repository';
        $this->adapter = $adapter;

        $this->service = new Service($this->remoteHost, $this->remoteDir, $this->localWorkingDir);
        $this->fileRepository = new FileRepository($this->localRepositoryDir, $this->remoteDir);
    }

    public function discover()
    {
        $discoverWorker = new Discover();
        $discoverWorker($this->service, $this->fileRepository);
    }

    public function download(int $version)
    {
        $downloadWorker = new Download($this->service);
        foreach ($this->fileRepository->getAll() as $localFileEntity) {
            if (!$localFileEntity->getDownloadedAtByIndex($version)) {
                $downloadWorker($localFileEntity, $this->localWorkingDir . DIRECTORY_SEPARATOR . $version);
                $localFileEntity->setDownloadedAtByIndex($version, time());
                $this->fileRepository->save($localFileEntity);
                $this->adapter->writeLine('Downloaded ' . $localFileEntity->getName() . ' for the ' . $version . '. time.');
            }
        }
    }

    public function compare(array $versions)
    {
        $compareWorker = new Compare();
        foreach ($this->fileRepository->getAll() as $localFileEntity) {
            if (!$localFileEntity->getComparedAt()) {
                $leftVersion = array_shift($versions);
                foreach ($versions as $rightVersion) {
                    if (!$compareWorker(
                        $this->localWorkingDir . DIRECTORY_SEPARATOR . $leftVersion . DIRECTORY_SEPARATOR . $localFileEntity->getDirectory() . DIRECTORY_SEPARATOR . $localFileEntity->getName(),
                        $this->localWorkingDir . DIRECTORY_SEPARATOR . $rightVersion . DIRECTORY_SEPARATOR . $localFileEntity->getDirectory() . DIRECTORY_SEPARATOR . $localFileEntity->getName()
                    )) {
                        $localFileEntity->setDownloadedAt([]);
                        $this->fileRepository->save($localFileEntity);
                        $this->adapter->writeLine('Error comparing ' . $localFileEntity->getName() . '.');
                        continue 2;
                    }
                }
                $localFileEntity->setComparedAt(time());
                $this->fileRepository->save($localFileEntity);
            }
        }
    }

    public function store(int $fromVersion)
    {
        $storeWorker = new Store();
        foreach ($this->fileRepository->getAll() as $localFileEntity) {
            if ($localFileEntity->getComparedAt() && !$localFileEntity->getStoredAt()) {
                if ($storeWorker(
                    $this->localWorkingDir . DIRECTORY_SEPARATOR . $fromVersion . DIRECTORY_SEPARATOR . $localFileEntity->getDirectory() . DIRECTORY_SEPARATOR . $localFileEntity->getName(),
                    $this->targetDirectory . DIRECTORY_SEPARATOR . $localFileEntity->getName()
                )) {
                    $localFileEntity->setStoredAt(time());
                    $this->adapter->writeLine('Stored file ' . $localFileEntity->getName() . '.');
                } else {
                    $localFileEntity->setDownloadedAt([]);
                    $localFileEntity->setComparedAt(null);
                    $this->adapter->writeLine('Error storing file ' . $localFileEntity->getName() . ' to ' . $this->targetDirectory . '.');
                }
                $this->fileRepository->save($localFileEntity);
            }
        }
    }

    public function cleanup(array $versions)
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