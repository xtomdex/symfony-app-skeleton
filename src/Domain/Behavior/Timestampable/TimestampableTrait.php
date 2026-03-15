<?php

declare(strict_types=1);

namespace App\Domain\Behavior\Timestampable;

use Doctrine\ORM\Mapping as ORM;

trait TimestampableTrait
{
    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setTimestamps(): void
    {
        $datetime = new \DateTimeImmutable();

        if (null === $this->createdAt) {
            $this->createdAt = $datetime;
        }

        $this->updatedAt = $datetime;
    }
}
