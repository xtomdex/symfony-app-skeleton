<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Twig\Component;

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

    /**
     * Breadcrumb items: list of ['label' => string, 'url' => string|null].
     * Last item is rendered without a link (current page).
     *
     * @var list<array{label: string, url: string|null}>
     */
    public array $breadcrumbs = [];
}
