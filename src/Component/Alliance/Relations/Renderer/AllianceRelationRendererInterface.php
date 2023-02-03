<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Relations\Renderer;

use Stu\Orm\Entity\AllianceRelationInterface;

interface AllianceRelationRendererInterface
{
    /**
     * Renders the relations between alliances as graph
     *
     * @param iterable<AllianceRelationInterface> $relationList
     */
    public function render(
        iterable $relationList,
        int $penWidth = 2,
        string $renderFormat = 'svg'
    ): string;
}