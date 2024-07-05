<?php

namespace CodeBuds\EasyAdminLogViewerBundle\Entity\Dto;

use CodeBuds\EasyAdminLogViewerBundle\Service\LogFileService;

class FileDto
{
    private string $name;

    private string $path;

    private ?int $size = null;

    private ?\DateTime $lastUpdatedAt = null;

    public function getHumanSize(): string
    {
        return LogFileService::humanFilesize($this->size);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getLastUpdatedAt(): ?\DateTime
    {
        return $this->lastUpdatedAt;
    }

    public function setLastUpdatedAt(?\DateTime $lastUpdatedAt): self
    {
        $this->lastUpdatedAt = $lastUpdatedAt;

        return $this;
    }
}
