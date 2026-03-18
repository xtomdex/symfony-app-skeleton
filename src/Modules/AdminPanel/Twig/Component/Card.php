<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Twig\Component;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * Content card component.
 *
 * Usage:
 *     <twig:Admin:Card title="Profile" subtitle="Basic info">
 *         <twig:block name="actions">
 *             <button class="btn btn-sm btn-outline-primary">Edit</button>
 *         </twig:block>
 *
 *         Card body content
 *
 *         <twig:block name="footer">
 *             <small class="text-muted">Last updated: ...</small>
 *         </twig:block>
 *     </twig:Admin:Card>
 */
#[AsTwigComponent]
final class Card
{
    public ?string $title = null;
    public ?string $subtitle = null;
}
