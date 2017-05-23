<?php
namespace FlashAirSync\Worker;

class Compare
{
    public function __invoke(string $firstFilePath, string $secondFilePath): bool
    {
        return is_readable($firstFilePath) && is_readable($secondFilePath) && sha1_file($firstFilePath) === sha1_file($secondFilePath);
    }
}