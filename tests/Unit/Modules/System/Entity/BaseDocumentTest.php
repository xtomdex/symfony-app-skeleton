<?php

declare(strict_types=1);

namespace App\Tests\Unit\Modules\System\Entity;

use App\Modules\System\Entity\Document;
use App\Modules\System\Enum\DocumentStatus;
use App\Modules\System\Enum\DocumentType;
use App\Modules\System\Event\DocumentArchived;
use App\Modules\System\Event\DocumentCreated;
use App\Modules\System\Event\DocumentDeleted;
use App\Modules\System\Event\DocumentPublished;
use App\Modules\System\Event\DocumentUpdated;
use PHPUnit\Framework\TestCase;
final class BaseDocumentTest extends TestCase
{
    private function uuid(): string
    {
        return \Ramsey\Uuid\Uuid::uuid4()->toString();
    }

    public function test_create(): void
    {
        $id = $this->uuid();

        $document = Document::create(
            id: $id,
            title: 'Terms of Service',
            slug: 'terms-of-service',
            content: 'Full content here.',
            type: DocumentType::Legal,
            description: 'Our terms.',
        );

        self::assertSame($id, $document->getId());
        self::assertSame('Terms of Service', $document->getTitle());
        self::assertSame('terms-of-service', $document->getSlug());
        self::assertSame('Full content here.', $document->getContent());
        self::assertSame(DocumentType::Legal, $document->getType());
        self::assertSame('Our terms.', $document->getDescription());
        self::assertSame(DocumentStatus::Draft, $document->getStatus());
        self::assertNotNull($document->getEditedAt());
        self::assertNull($document->getPublishedAt());
    }

    public function test_create_without_description(): void
    {
        $document = Document::create(
            id: $this->uuid(),
            title: 'About',
            slug: 'about',
            content: 'About us.',
            type: DocumentType::Page,
        );

        self::assertNull($document->getDescription());
    }

    public function test_publish(): void
    {
        $document = Document::create(
            id: $this->uuid(),
            title: 'About',
            slug: 'about',
            content: 'About us.',
            type: DocumentType::Page,
        );

        $document->publish();

        self::assertSame(DocumentStatus::Published, $document->getStatus());
        self::assertNotNull($document->getPublishedAt());
    }

    public function test_publish_preserves_existing_published_at(): void
    {
        $document = Document::create(
            id: $this->uuid(),
            title: 'About',
            slug: 'about',
            content: 'About us.',
            type: DocumentType::Page,
        );

        $document->publish();
        $firstPublishedAt = $document->getPublishedAt();

        $document->publish();

        self::assertSame($firstPublishedAt, $document->getPublishedAt());
    }

    public function test_archive(): void
    {
        $document = Document::create(
            id: $this->uuid(),
            title: 'About',
            slug: 'about',
            content: 'About us.',
            type: DocumentType::Page,
        );

        $document->archive();

        self::assertSame(DocumentStatus::Archived, $document->getStatus());
    }

    public function test_update_content(): void
    {
        $document = Document::create(
            id: $this->uuid(),
            title: 'Old Title',
            slug: 'old-slug',
            content: 'Old content.',
            type: DocumentType::Page,
            description: 'Old description.',
        );

        $document->updateContent('New Title', 'New content.', 'New description.');

        self::assertSame('New Title', $document->getTitle());
        self::assertSame('New content.', $document->getContent());
        self::assertSame('New description.', $document->getDescription());
    }

    public function test_update_content_sets_edited_at(): void
    {
        $document = Document::create(
            id: $this->uuid(),
            title: 'Title',
            slug: 'title',
            content: 'Content.',
            type: DocumentType::Page,
        );

        $editedAtBefore = $document->getEditedAt();

        usleep(1000);

        $document->updateContent('Updated Title', 'Updated content.', null);

        self::assertGreaterThan($editedAtBefore, $document->getEditedAt());
    }

    public function test_create_records_document_created_event(): void
    {
        $id = $this->uuid();

        $document = Document::create(
            id: $id,
            title: 'Title',
            slug: 'my-slug',
            content: 'Content.',
            type: DocumentType::Legal,
        );

        $events = $document->releaseEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(DocumentCreated::class, $events[0]);
        self::assertSame($id, $events[0]->documentId);
        self::assertSame('my-slug', $events[0]->slug);
        self::assertSame(DocumentType::Legal, $events[0]->type);
    }

    public function test_update_content_records_document_updated_event(): void
    {
        $id = $this->uuid();

        $document = Document::create(
            id: $id,
            title: 'Title',
            slug: 'my-slug',
            content: 'Content.',
            type: DocumentType::Page,
        );

        $document->releaseEvents(); // clear create event

        $document->updateContent('New Title', 'New content.', null);

        $events = $document->releaseEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(DocumentUpdated::class, $events[0]);
        self::assertSame($id, $events[0]->documentId);
    }

    public function test_publish_records_document_published_event(): void
    {
        $id = $this->uuid();

        $document = Document::create(
            id: $id,
            title: 'Title',
            slug: 'my-slug',
            content: 'Content.',
            type: DocumentType::Page,
        );

        $document->releaseEvents(); // clear create event

        $document->publish();

        $events = $document->releaseEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(DocumentPublished::class, $events[0]);
        self::assertSame($id, $events[0]->documentId);
        self::assertSame('my-slug', $events[0]->slug);
    }

    public function test_archive_records_document_archived_event(): void
    {
        $id = $this->uuid();

        $document = Document::create(
            id: $id,
            title: 'Title',
            slug: 'my-slug',
            content: 'Content.',
            type: DocumentType::Page,
        );

        $document->releaseEvents(); // clear create event

        $document->archive();

        $events = $document->releaseEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(DocumentArchived::class, $events[0]);
        self::assertSame($id, $events[0]->documentId);
        self::assertSame('my-slug', $events[0]->slug);
    }

    public function test_delete_records_document_deleted_event(): void
    {
        $id = $this->uuid();

        $document = Document::create(
            id: $id,
            title: 'Title',
            slug: 'my-slug',
            content: 'Content.',
            type: DocumentType::Legal,
        );

        $document->releaseEvents(); // clear create event

        $document->delete();

        $events = $document->releaseEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(DocumentDeleted::class, $events[0]);
        self::assertSame($id, $events[0]->documentId);
        self::assertSame('my-slug', $events[0]->slug);
        self::assertSame(DocumentType::Legal, $events[0]->type);
    }
}
