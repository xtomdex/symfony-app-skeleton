<?php

declare(strict_types=1);

namespace App\Modules\System\Entity;

use App\Domain\Behavior\Timestampable\TimestampableTrait;
use App\Domain\Eventing\Contract\AggregateRoot;
use App\Domain\Eventing\Trait\EventsTrait;
use App\Domain\Persistence\Contract\EntityInterface;
use App\Modules\System\Enum\DocumentStatus;
use App\Modules\System\Enum\DocumentType;
use App\Modules\System\Event\DocumentArchived;
use App\Modules\System\Event\DocumentCreated;
use App\Modules\System\Event\DocumentDeleted;
use App\Modules\System\Event\DocumentPublished;
use App\Modules\System\Event\DocumentUpdated;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
#[ORM\HasLifecycleCallbacks]
class BaseDocument implements EntityInterface, AggregateRoot
{
    use TimestampableTrait, EventsTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'string', length: 36)]
    protected ?string $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(length: 255, unique: true)]
    private string $slug;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $content;

    #[ORM\Column(type: 'string', enumType: DocumentType::class)]
    private DocumentType $type;

    #[ORM\Column(type: 'string', enumType: DocumentStatus::class)]
    private DocumentStatus $status;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $editedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    protected function __construct()
    {
    }

    public static function create(
        string $id,
        string $title,
        string $slug,
        string $content,
        DocumentType $type,
        ?string $description = null,
    ): static {
        $document = new static();
        $document->id = $id;
        $document->title = $title;
        $document->slug = $slug;
        $document->content = $content;
        $document->type = $type;
        $document->description = $description;
        $document->status = DocumentStatus::Draft;
        $document->editedAt = new \DateTimeImmutable();

        $document->recordEvent(new DocumentCreated($document->id, $document->slug, $document->type));

        return $document;
    }

    public function publish(): void
    {
        $this->status = DocumentStatus::Published;
        if ($this->publishedAt === null) {
            $this->publishedAt = new \DateTimeImmutable();
        }

        $this->recordEvent(new DocumentPublished($this->id, $this->slug));
    }

    public function archive(): void
    {
        $this->status = DocumentStatus::Archived;

        $this->recordEvent(new DocumentArchived($this->id, $this->slug));
    }

    public function updateContent(
        string $title,
        string $content,
        ?string $description,
    ): void {
        $this->title = $title;
        $this->content = $content;
        $this->description = $description;
        $this->editedAt = new \DateTimeImmutable();

        $this->recordEvent(new DocumentUpdated($this->id));
    }

    public function delete(): void
    {
        $this->recordEvent(new DocumentDeleted($this->id, $this->slug, $this->type));
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getType(): DocumentType
    {
        return $this->type;
    }

    public function getStatus(): DocumentStatus
    {
        return $this->status;
    }

    public function getEditedAt(): ?\DateTimeImmutable
    {
        return $this->editedAt;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }
}
