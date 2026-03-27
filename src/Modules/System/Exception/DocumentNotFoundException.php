<?php

declare(strict_types=1);

namespace App\Modules\System\Exception;

final class DocumentNotFoundException extends \DomainException
{
    public static function byId(string $id): self
    {
        return new self(sprintf('Document with id "%s" was not found.', $id));
    }

    public static function bySlug(string $slug): self
    {
        return new self(sprintf('Document with slug "%s" was not found.', $slug));
    }
}
