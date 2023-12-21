<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace;

use RuntimeException;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\AbstractPanelLayerDataProvider;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class SubspaceDataProviderFactory implements SubspaceDataProviderFactoryInterface
{
    private MapRepositoryInterface $mapRepository;

    private StarSystemMapRepositoryInterface $starSystemMapRepository;

    public function __construct(
        MapRepositoryInterface $mapRepository,
        StarSystemMapRepositoryInterface $starSystemMapRepository
    ) {
        $this->mapRepository = $mapRepository;
        $this->starSystemMapRepository = $starSystemMapRepository;
    }

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
                return new IgnoringSubspaceDataProvider($id, $this->mapRepository, $this->starSystemMapRepository);
        }

        throw new RuntimeException(sprintf('subspace layer type %d is not supported', $type->value));
    }
}
