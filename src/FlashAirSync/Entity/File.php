<?php
namespace FlashAirSync\Entity;

class File
{
    /**
     * @var string
     */
    protected $directory;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $createdAt;

    /**
     * @var int
     */
    protected $discoveredAt;

    /**
     * @var int[]
     */
    protected $downloadedAt = [];

    /**
     * @var int
     */
    protected $comparedAt;

    /**
     * @var int
     */
    protected $storedAt;

    /**
     * @var int
     */
    protected $cleanedUpAt;

    public function __construct(string $directory, string $name)
    {
        $this->directory = $directory;
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    /**
     * @return int|null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt(int $createdAt): File
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getDiscoveredAt()
    {
        return $this->discoveredAt;
    }

    public function setDiscoveredAt(int $discoveredAt): File
    {
        $this->discoveredAt = $discoveredAt;
        return $this;
    }

    public function getDownloadedAt(): array
    {
        return $this->downloadedAt;
    }

    public function setDownloadedAt(array $downloadedAt): File
    {
        $this->downloadedAt = $downloadedAt;
        return $this;
    }

    /**
     * @param int $index
     * @return int|null
     */
    public function getDownloadedAtByIndex(int $index)
    {
        return array_key_exists($index, $this->downloadedAt)? $this->downloadedAt[$index] : null;
    }

    public function setDownloadedAtByIndex(int $index, int $downloadedAt): File
    {
        $this->downloadedAt[$index] = $downloadedAt;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getComparedAt()
    {
        return $this->comparedAt;
    }

    public function setComparedAt(int $comparedAt): File
    {
        $this->comparedAt = $comparedAt;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getStoredAt()
    {
        return $this->storedAt;
    }

    public function setStoredAt(int $storedAt): File
    {
        $this->storedAt = $storedAt;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCleanedUpAt()
    {
        return $this->cleanedUpAt;
    }

    public function setCleanedUpAt(int $cleanedUpAt): File
    {
        $this->cleanedUpAt = $cleanedUpAt;
        return $this;
    }
}