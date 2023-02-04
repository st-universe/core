<?php

declare(strict_types=1);

namespace Stu\Component\Alliance;


use Stu\Orm\Entity\AllianceInterface;

interface AllianceDescriptionRendererInterface
{
    /**
     * Renders the alliance description (bbcode, replacement variables, etc..)
     */
    public function render(AllianceInterface $alliance): string;
}