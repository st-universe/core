<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace;

use Crunz\Exception\NotImplementedException;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;

final class GeneralSubspaceDataProvider extends AbstractSubspaceDataProvider
{
    protected function provideDataForMap(PanelBoundaries $boundaries): array
    {
        return $this->mapRepository->getSignaturesOuterSystem($boundaries, $this->createResultSetMapping());
    }

    protected function provideDataForSystemMap(PanelBoundaries $boundaries): array
    {
        throw new NotImplementedException('this is not possible');
    }
}
