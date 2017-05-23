<?php
namespace FlashAirSync\Worker;

use FlashAirSync\Entity\Repository\File;
use FlashAirSync\Service\Service;

class Discover
{
    public function __invoke(Service $service, File $fileRepository)
    {
        $files = $service->ls();

        foreach ($files as $discoveredFileName => $discoveredFileTimestamp) {
            $file = $fileRepository->getByFilename($discoveredFileName);
            if (!$file->getCreatedAt()) {
                $file->setCreatedAt($discoveredFileTimestamp);
                $fileRepository->save($file);
            }
        }
    }
}