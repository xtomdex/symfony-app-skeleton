<?php

declare(strict_types=1);

namespace App\Modules\System\Exception;

final class DocumentSlugAlreadyExistsException extends \DomainException
{
    public static function forSlug(string $slug): self
    {
        return new self(sprintf('Document with slug "%s" already exists.', $slug));
    }
}
