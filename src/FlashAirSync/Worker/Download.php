<?php
namespace FlashAirSync\Worker;

use FlashAirSync\Entity\File;
use FlashAirSync\Service\Service;

class Download
{
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function __invoke(File $file, string $localDir): void
    {
        $this->service->get($file->getName(), $file->getCreatedAt(), $localDir);
    }
}