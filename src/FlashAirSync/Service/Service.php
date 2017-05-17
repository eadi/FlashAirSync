<?php

namespace FlashAirSync\Service;

class Service
{
    protected $sourceHost;
    protected $targetDir;
    protected $sourceDir;

    public function __construct(string $sourceHost, string $targetDir, string $sourceDir)
    {
        $this->sourceHost = $sourceHost;
        $this->targetDir = $targetDir;
        $this->sourceDir = $sourceDir;
    }

    /**
     * Scans for files inside the directory without traversing subdirectories. Returns the filename and the file
     * creation date as an associative array.
     *
     * @return array
     */
    public function ls(): array
    {
        $contents = array();
        $url = sprintf('http://%s/command.cgi?op=100&DIR=%s', $this->sourceHost, rawurlencode($this->sourceDir));
        $response = file_get_contents($url);
        if ($response === null) {
            throw new Exception('Cannot read directory list.');
        }

        foreach(preg_split('/\r?\n/', $response) as $entry)
        {
            $entryProperties = str_getcsv($entry);
            if(count($entryProperties) < 3) {
                continue;
            }

            if($entryProperties[3] & 16) // bit 5 = Directory
            {
                continue;
            }

            $day     = ($entryProperties[4] & 0b0000000000011111);
            $month   = ($entryProperties[4] & 0b0000000111100000) >> 5;
            $year    = (($entryProperties[4] & 0b1111111000000000) >> 9) + 1980;
            $seconds = ($entryProperties[5] & 0b0000000000011111) * 2;
            $minutes = ($entryProperties[5] & 0b0000011111100000) >> 5;
            $hours   = ($entryProperties[5] & 0b1111100000000000) >> 11;
            $isoTime = sprintf('%d-%d-%d %d:%d:%d', $year, $month, $day, $hours, $minutes, $seconds);
            $contents[$entryProperties[1]] = strtotime($isoTime);
        }

        return $contents;
    }

    /**
     * @param string $filename
     * @param int $createdAt timestamp
     */
    public function get(string $filename, int $createdAt): void
    {
        $url = sprintf('http://%s/%s/%s', $this->sourceHost, $this->sourceDir, $filename);
        $targetPath = $this->targetDir . DIRECTORY_SEPARATOR . $filename;
        if(copy($url, $targetPath)) {
            touch($targetPath, $createdAt);
        } else {
            throw new Exception(sprintf('Cannot copy file from %s to %s', $url, $targetPath));
        }
    }
}