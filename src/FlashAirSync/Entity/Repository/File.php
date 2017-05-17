<?php
namespace FlashAirSync\Entity\Repository;

use FlashAirSync\Entity\File as FileEntity;

class File
{
    public function getByDirAndFilename(string $dir, string $filename): FileEntity
    {
        $path = $this->getPath($dir, $filename);
        $entity = new FileEntity($dir, $filename);
        $entity->setDiscoveredAt(time());

        if (file_exists($path)) {
            $fh = fopen($path, 'r');
            $data = fgetcsv($fh, null, ';');
            fclose($fh);

            $entity
                ->setCreatedAt((int)$data[0])
                ->setDiscoveredAt((int)$data[1])
                ->setFirstDownloadedAt((int)$data[2])
                ->setSecondDownloadedAt((int)$data[3])
                ->setComparedAt((int)$data[4])
                ->setStoredAt((int)$data[5]);
        }

        return $entity;
    }

    public function save(FileEntity $entity): void
    {
        $path = $this->getPath($entity->getDirectory(), $entity->getName());
        $data = array(
            $entity->getCreatedAt(),
            $entity->getDiscoveredAt(),
            $entity->getFirstDownloadedAt(),
            $entity->getSecondDownloadedAt(),
            $entity->getComparedAt(),
            $entity->getStoredAt(),
        );

        $fh = fopen($path, 'w');
        fputcsv($fh, $data, ';');
        fclose($fh);
    }

    protected function getPath(string $dir, string $filename): string
    {
        $dir =
            __DIR__ .
            DIRECTORY_SEPARATOR .
            DIRECTORY_SEPARATOR . $dir;
        if (!file_exists($dir)) {
            // @TODO Sanity check
            mkdir($dir, 0777, true);
        }
        // @TODO Sanity check
        return $dir . DIRECTORY_SEPARATOR . $filename . '.csv';
    }
}