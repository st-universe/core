<?php

declare(strict_types=1);

namespace Stu\Component\Alliance;

use Stu\Orm\Entity\Alliance;

interface AllianceDescriptionRendererInterface
{
    /**
     * Renders the alliance description (bbcode, replacement variables, etc..)
     */
    public function render(Alliance $alliance): string;
}
