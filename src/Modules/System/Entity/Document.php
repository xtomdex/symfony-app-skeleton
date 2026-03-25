<?php

declare(strict_types=1);

namespace App\Modules\System\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'system_documents')]
class Document extends BaseDocument
{

}
