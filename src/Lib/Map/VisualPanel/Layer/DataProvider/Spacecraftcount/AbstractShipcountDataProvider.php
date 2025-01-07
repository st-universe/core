<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Spacecraftcount;

use Doctrine\ORM\Query\ResultSetMapping;
use Override;
use Stu\Lib\Map\VisualPanel\Layer\Data\SpacecraftCountData;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\AbstractPanelLayerDataProvider;

abstract class AbstractShipcountDataProvider extends AbstractPanelLayerDataProvider
{
    #[Override]
    protected function getDataClassString(): string
    {
        return SpacecraftCountData::class;
    }

    #[Override]
    protected function addFieldResults(ResultSetMapping $rsm): void
    {
        $rsm->addFieldResult('d', 'spacecraftcount', 'spacecraftcount');
        $rsm->addFieldResult('d', 'cloakcount', 'cloakcount');
    }
}
