<?php

namespace CodeBuds\EasyAdminLogViewerBundle\Entity\Dto;

readonly class LogFileLine
{
    public function __construct(
        public string $type,
        public string $level,
        public string $badgeLevel,
        public ?\DateTimeImmutable $date = null,
        public ?string $message = null,
    ) {
    }
}
