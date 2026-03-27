<?php

declare(strict_types=1);

namespace App\Modules\System\Exception;

final class DocumentNotArchivedException extends \DomainException
{
    public static function forDocument(string $id): self
    {
        return new self(sprintf('Document "%s" is not archived.', $id));
    }
}
