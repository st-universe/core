<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Shipcount;

use Override;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Lib\Map\VisualPanel\Layer\Data\ShipCountData;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\AbstractPanelLayerDataProvider;

abstract class AbstractShipcountDataProvider extends AbstractPanelLayerDataProvider
{
    #[Override]
    protected function getDataClassString(): string
    {
        return ShipCountData::class;
    }

    #[Override]
    protected function addFieldResults(ResultSetMapping $rsm): void
    {
        $rsm->addFieldResult('d', 'shipcount', 'shipcount');
        $rsm->addFieldResult('d', 'cloakcount', 'cloakcount');
    }
}
