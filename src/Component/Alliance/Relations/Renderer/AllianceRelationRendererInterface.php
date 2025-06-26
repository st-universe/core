<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Relations\Renderer;

use Stu\Orm\Entity\AllianceRelation;

interface AllianceRelationRendererInterface
{
    /**
     * Renders the relations between alliances as graph
     *
     * @param iterable<AllianceRelation> $relationList
     */
    public function render(
        iterable $relationList,
        int $width,
        int $height,
        int $penWidth = 2,
        string $renderFormat = 'svg'
    ): string;
}
