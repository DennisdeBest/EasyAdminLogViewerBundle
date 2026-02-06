<?php

namespace CodeBuds\EasyAdminLogViewerBundle\Entity\Dto;

use CodeBuds\EasyAdminLogViewerBundle\Service\LogFileService;

readonly class FileDto
{
    public function __construct(
        public string $name,
        public string $path,
        public ?int $size = null,
        public ?\DateTimeImmutable $lastUpdatedAt = null,
    ) {
    }

    public function getHumanSize(): string
    {
        return LogFileService::humanFilesize($this->size);
    }
}
