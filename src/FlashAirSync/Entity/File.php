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
     * @var int
     */
    protected $firstDownloadedAt;

    /**
     * @var int
     */
    protected $secondDownloadedAt;

    /**
     * @var int
     */
    protected $comparedAt;

    /**
     * @var int
     */
    protected $storedAt;

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

    public function getCreatedAt(): ?int
    {
        return $this->createdAt;
    }

    public function setCreatedAt(int $createdAt): File
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getDiscoveredAt(): ?int
    {
        return $this->discoveredAt;
    }

    public function setDiscoveredAt(int $discoveredAt): File
    {
        $this->discoveredAt = $discoveredAt;
        return $this;
    }

    public function getFirstDownloadedAt(): ?int
    {
        return $this->firstDownloadedAt;
    }

    public function setFirstDownloadedAt(int $firstDownloadedAt): File
    {
        $this->firstDownloadedAt = $firstDownloadedAt;
        return $this;
    }

    public function getSecondDownloadedAt(): ?int
    {
        return $this->secondDownloadedAt;
    }

    public function setSecondDownloadedAt(int $secondDownloadedAt): File
    {
        $this->secondDownloadedAt = $secondDownloadedAt;
        return $this;
    }

    public function getComparedAt(): ?int
    {
        return $this->comparedAt;
    }

    public function setComparedAt(int $comparedAt): File
    {
        $this->comparedAt = $comparedAt;
        return $this;
    }

    public function getStoredAt(): ?int
    {
        return $this->storedAt;
    }

    public function setStoredAt(int $storedAt): File
    {
        $this->storedAt = $storedAt;
        return $this;
    }
}