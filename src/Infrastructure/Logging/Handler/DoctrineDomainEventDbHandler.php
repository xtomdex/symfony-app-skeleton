<?php

declare(strict_types=1);

namespace App\Infrastructure\Logging\Handler;

use Doctrine\DBAL\Connection;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

final class DoctrineDomainEventDbHandler extends AbstractProcessingHandler
{
    public function __construct(
        private readonly Connection $connection,
        private readonly LoggerInterface $fallbackLogger,
        private readonly bool $enabled = false,
        int|string|Level $level = Level::Info,
        bool $bubble = true,
    ) {
        parent::__construct($level, $bubble);
    }

    protected function write(LogRecord $record): void
    {
        if (!$this->enabled) {
            return;
        }

        $eventClass = $record->context['event_class'] ?? null;
        if (!is_string($eventClass) || $eventClass === '') {
            return; // Not a domain event record.
        }

        $payload = $record->context['payload'] ?? [];
        if (!is_array($payload)) {
            $payload = ['_raw' => $payload];
        }

        $eventId = $record->context['event_id'] ?? null;
        if (!is_string($eventId) || $eventId === '') {
            $eventId = null;
        }

        try {
            $this->connection->insert('system_event_log', [
                'id'          => Uuid::uuid7()->toString(),
                'event_id'    => $eventId ?? '',
                'event_name'  => $eventClass,
                'payload'     => json_encode($payload, JSON_THROW_ON_ERROR),
                'context'     => json_encode($this->normalizeContext($record->context), JSON_THROW_ON_ERROR),
                'recorded_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // Never break the main flow because of DB logging.
            $this->fallbackLogger->error('Failed to write domain event log to DB', [
                'exception' => $e->getMessage(),
                'event_class' => $eventClass,
            ]);
        }
    }

    private function normalizeContext(array $context): array
    {
        // Keep only a safe subset; avoid duplicating large payloads.
        return [
            'event_class' => $context['event_class'] ?? null,
            'event_id'    => $context['event_id'] ?? null,
        ];
    }
}
