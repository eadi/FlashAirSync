<?php

namespace FlashAirSync\Service;

class Service
{
    protected $remoteHost;
    protected $remotePath;

    public function __construct(string $remoteHost, string $remotePath)
    {
        $this->remoteHost = $remoteHost;
        $this->remotePath = $remotePath;
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
        $url = sprintf('http://%s/command.cgi?op=100&DIR=%s', $this->remoteHost, rawurlencode($this->remotePath));
        $response = @file_get_contents($url);
        if (!$response) {
            throw new Exception('Cannot read directory list.');
        }

        foreach(preg_split('/\r?\n/', $response) as $entry)
        {
            $entryProperties = str_getcsv($entry);
            if(count($entryProperties) < 3) {
                //Invalid entry
                continue;
            }

            if($entryProperties[3] & 16) {
                //Directory
                continue;
            }

            $year    = (($entryProperties[4] & 0b1111111000000000) >> 9) + 1980;
            $month   = ($entryProperties[4] & 0b0000000111100000) >> 5;
            $day     = ($entryProperties[4] & 0b0000000000011111);
            $hours   = ($entryProperties[5] & 0b1111100000000000) >> 11;
            $minutes = ($entryProperties[5] & 0b0000011111100000) >> 5;
            $seconds = ($entryProperties[5] & 0b0000000000011111) * 2;
            $isoTime = sprintf('%d-%d-%d %d:%d:%d', $year, $month, $day, $hours, $minutes, $seconds);
            $contents[$entryProperties[1]] = strtotime($isoTime);
        }

        return $contents;
    }

    public function get(string $filename, int $createdAt, string $localPathWorkingDir): void
    {
        $remoteUrl = sprintf('http://%s/%s/%s', $this->remoteHost, $this->remotePath, $filename);
        $localSaveDir = $localPathWorkingDir . DIRECTORY_SEPARATOR . $this->remotePath;
        @mkdir($localSaveDir, 0777, true);
        $localSavePath = $localSaveDir . DIRECTORY_SEPARATOR . $filename;
        if(copy($remoteUrl, $localSavePath)) {
            touch($localSavePath, $createdAt);
        } else {
            throw new Exception(sprintf('Cannot copy file from %s to %s', $remoteUrl, $localSavePath));
        }
    }
}