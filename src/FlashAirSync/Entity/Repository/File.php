<?php
namespace FlashAirSync\Entity\Repository;

use FlashAirSync\Entity\File as FileEntity;

class File
{
    const FILE_SUFFIX = '.dat';

    protected $pathWorkingDir;
    protected $remoteDir;

    protected $entityCache = [];

    public function __construct(string $localWorkingDir, string $remoteDir)
    {
        $this->pathWorkingDir = $localWorkingDir;
        $this->remoteDir = $remoteDir;
    }

    public function getByFilename(string $filename): FileEntity
    {
        $key = $filename;
        if (array_key_exists($key, $this->entityCache)) {
            return $this->entityCache[$key];
        }

        $path = $this->getLocalPathOfFile($filename);

        $entity = new FileEntity($this->remoteDir, $filename);
        $entity->setDiscoveredAt(time());

        if (file_exists($path)) {
            $data = file_get_contents($path);
            $entity = unserialize($data);
        }
        return $this->entityCache[$entity->getName()] = $entity;
    }

    public function save(FileEntity $entity)
    {
        $path = $this->getLocalPathOfFile($entity->getName());
        $data = serialize($entity);
        file_put_contents($path, $data);

        $this->entityCache[$entity->getName()] = $entity;
    }

    /**
     * @return FileEntity[]
     */
    public function getAll(): array
    {
        $localPathPattern = $this->getLocalPathOfFile('*');
        $result = [];

        foreach (glob($localPathPattern) as $localDataFile) {
            $filename = preg_replace('/' . self::FILE_SUFFIX . '$/', '', $localDataFile);
            $result[] = $this->getByFilename(basename($filename));
        }

        return $result;
    }

    protected function getLocalPathOfFile(string $filename): string
    {
        $localDir = $this->pathWorkingDir . DIRECTORY_SEPARATOR . $this->remoteDir;
        if (!file_exists($localDir)) {
            // @TODO Sanity check
            mkdir($localDir, 0777, true);
        }
        // @TODO Sanity check
        return $localDir . DIRECTORY_SEPARATOR . $filename . self::FILE_SUFFIX;
    }
}