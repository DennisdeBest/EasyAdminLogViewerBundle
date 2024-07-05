<?php

namespace CodeBuds\EasyAdminLogViewerBundle\Entity\Dto;

class LogFileLine
{
    private string $type;

    private string $level;

    private string $badgeLevel;

    private ?\DateTime $date;

    private ?string $message;

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function setLevel(string $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getBadgeLevel(): string
    {
        return $this->badgeLevel;
    }

    public function setBadgeLevel(string $badgeLevel): self
    {
        $this->badgeLevel = $badgeLevel;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(?\DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
