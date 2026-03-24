<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Twig\Component;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * Modal dialog component.
 *
 * Usage:
 *     <twig:Admin:Modal id="confirmModal" title="Confirm Action">
 *         Are you sure?
 *
 *         <twig:block name="footer">
 *             <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
 *             <button type="button" class="btn btn-primary">Confirm</button>
 *         </twig:block>
 *     </twig:Admin:Modal>
 */
#[AsTwigComponent]
final class Modal
{
    /** Unique modal ID, used for data-bs-target trigger */
    public string $id;

    /** Header title text */
    public string $title = '';

    /** Size variant: null (default), 'sm', 'lg', 'xl' */
    public ?string $size = null;

    /** Enable vertical scrolling for long content */
    public bool $scrollable = false;

    /** Prevent closing by clicking backdrop */
    public bool $staticBackdrop = false;

    /** Center modal vertically */
    public bool $centered = false;

    /** Show close (X) button in header */
    public bool $closable = true;
}
