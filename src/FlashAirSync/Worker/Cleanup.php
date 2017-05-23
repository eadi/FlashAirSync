<?php
namespace FlashAirSync\Worker;

class Cleanup
{
    public function __invoke(string $filePath): bool
    {
        return @unlink($filePath);
    }
}