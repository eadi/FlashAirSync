<?php
namespace FlashAirSync\Worker;

class Store
{
    public function __invoke(string $sourcePath, string $targetPath): bool
    {
        return copy($sourcePath, $targetPath);
    }
}