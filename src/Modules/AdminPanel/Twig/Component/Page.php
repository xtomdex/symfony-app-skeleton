<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Twig\Component;

use App\Modules\AdminPanel\DTO\Breadcrumb;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * Page wrapper component.
 *
 * Usage:
 *     <twig:Admin:Page title="Users" :breadcrumbs="breadcrumbs">
 *         <twig:block name="actions">
 *             <a href="..." class="btn btn-primary">Create</a>
 *         </twig:block>
 *
 *         Page content here
 *     </twig:Admin:Page>
 */
#[AsTwigComponent]
final class Page
{
    public string $title;

    /** @var list<Breadcrumb> */
    public array $breadcrumbs = [];

    /** Whether Breadcrumbs component shows home root */
    public bool $showHome = true;
}
