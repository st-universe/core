<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace;

use Override;
use RuntimeException;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\AbstractPanelLayerDataProvider;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class SubspaceDataProviderFactory implements SubspaceDataProviderFactoryInterface
{
    public function __construct(private MapRepositoryInterface $mapRepository, private StarSystemMapRepositoryInterface $starSystemMapRepository)
    {
    }

    #[Override]
    public function getDataProvider(int $id, SubspaceLayerTypeEnum $type): AbstractPanelLayerDataProvider
    {
        switch ($type) {
            case SubspaceLayerTypeEnum::ALL:
                return new GeneralSubspaceDataProvider($this->mapRepository, $this->starSystemMapRepository);
            case SubspaceLayerTypeEnum::IGNORE_USER:
                return new IgnoringSubspaceDataProvider($id, $this->mapRepository, $this->starSystemMapRepository);
            case SubspaceLayerTypeEnum::ALLIANCE_ONLY:
                return new AllianceSubspaceDataProvider($id, $this->mapRepository, $this->starSystemMapRepository);
            case SubspaceLayerTypeEnum::USER_ONLY:
                return new UserSubspaceDataProvider($id, $this->mapRepository, $this->starSystemMapRepository);
            case SubspaceLayerTypeEnum::SHIP_ONLY:
                return new ShipSubspaceDataProvider($id, $this->mapRepository, $this->starSystemMapRepository);
        }

        throw new RuntimeException(sprintf('subspace layer type %d is not supported', $type->value));
    }
}
