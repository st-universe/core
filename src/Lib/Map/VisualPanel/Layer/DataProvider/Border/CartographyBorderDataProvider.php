<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Border;

use Doctrine\ORM\Query\ResultSetMapping;
use Override;
use Stu\Lib\Map\VisualPanel\Layer\Data\BorderData;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;

final class CartographyBorderDataProvider extends AbstractBorderDataProvider
{

    public function __construct(
        private SpacecraftInterface $currentSpacecraft,
        protected MapRepositoryInterface $mapRepository,
        protected StarSystemMapRepositoryInterface $starSystemMapRepository,
        private AstroEntryRepositoryInterface $astroEntryRepository
    ) {
        $this->astroEntryRepository = $astroEntryRepository;
    }

    #[Override]
    protected function getDataClassString(): string
    {
        return BorderData::class;
    }

    #[Override]
    protected function addFieldResults(ResultSetMapping $rsm): void
    {
        $rsm->addFieldResult('d', 'cartographing', 'cartographing');
    }

    protected function provideDataForMap(PanelBoundaries $boundaries): array
    {
        return $this->mapRepository->getCartographingData(
            $boundaries,
            $this->createResultSetMapping(),
            $this->createLocationArray()
        );
    }

    protected function provideDataForSystemMap(PanelBoundaries $boundaries): array
    {
        return $this->starSystemMapRepository->getCartographingData(
            $boundaries,
            $this->createResultSetMapping(),
            $this->createLocationArray()
        );
    }

    /**
     * @return array<int>
     */
    private function createLocationArray(): array
    {
        $astroEntries = $this->astroEntryRepository->getByUserAndState(
            $this->currentSpacecraft->getUser(),
            1
        );

        $locations = [];
        foreach ($astroEntries as $entry) {
            $fieldIds = unserialize($entry->getFieldIds());
            if (is_array($fieldIds)) {
                $locations = array_merge($locations, $fieldIds);
            }
        }

        return $locations;
    }
}
