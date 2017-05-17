<?php

namespace FlashAirSync\Command;

use FlashAirSync\Entity\Repository\File as FileRepository;
use FlashAirSync\Service;
use Zend\Console\Adapter\AdapterInterface;
use ZF\Console\Route;

class Command
{
    public function __invoke(Route $route, AdapterInterface $console)
    {
        $host = $route->getMatchedParam('host');
        $path = $route->getMatchedParam('path');

//        $sync = new FlashAirSyncService('10.3.3.130', 'C:\\Users\\eadi\\Desktop\\sync', 'DCIM/100__TSB');

        $fileRepository = new FileRepository();
        $file = $fileRepository->getByDirAndFilename('IMG', 'test.jpg');
        $file->setFirstDownloadedAt(time());
        $fileRepository->save($file);
    }
}
