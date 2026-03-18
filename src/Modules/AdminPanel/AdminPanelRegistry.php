<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel;

use App\Modules\AdminPanel\Contract\AdminPanelInterface;
use Symfony\Component\HttpFoundation\Request;

final class AdminPanelRegistry
{
    /** @var array<string, AdminPanelInterface> */
    private array $byName = [];

    /** @var array<string, AdminPanelInterface> */
    private array $byPrefix = [];

    /**
     * @param iterable<AdminPanelInterface> $panels
     */
    public function __construct(iterable $panels)
    {
        foreach ($panels as $panel) {
            $name = $panel::name();
            $prefix = $panel::routePrefix();

            if (isset($this->byName[$name])) {
                throw new \InvalidArgumentException(\sprintf(
                    'Duplicate admin panel name "%s". Panels must have unique names.',
                    $name,
                ));
            }

            if (isset($this->byPrefix[$prefix])) {
                throw new \InvalidArgumentException(\sprintf(
                    'Duplicate admin panel route prefix "%s". Panels must have unique route prefixes.',
                    $prefix,
                ));
            }

            $this->byName[$name] = $panel;
            $this->byPrefix[$prefix] = $panel;
        }
    }

    public function resolveByRequest(Request $request): ?AdminPanelInterface
    {
        $path = $request->getPathInfo();

        foreach ($this->byPrefix as $prefix => $panel) {
            if (str_starts_with($path, $prefix)) {
                return $panel;
            }
        }

        return null;
    }

    public function get(string $name): AdminPanelInterface
    {
        return $this->byName[$name]
            ?? throw new \InvalidArgumentException(\sprintf(
                'Admin panel "%s" not found. Available panels: %s.',
                $name,
                implode(', ', array_keys($this->byName)) ?: 'none',
            ));
    }
}
