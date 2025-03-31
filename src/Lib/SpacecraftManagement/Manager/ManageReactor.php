<?php

declare(strict_types=1);

namespace Stu\Lib\SpacecraftManagement\Manager;

use Override;
use RuntimeException;
use Stu\Lib\SpacecraftManagement\Provider\ManagerProviderInterface;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Module\Commodity\Lib\CommodityCacheInterface;
use Stu\Module\Spacecraft\Lib\ReactorUtilInterface;
use Stu\Module\Spacecraft\Lib\ReactorWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;

class ManageReactor implements ManagerInterface
{
    public function __construct(private ReactorUtilInterface $reactorUtil, private CommodityCacheInterface $commodityCache, private PlayerRelationDeterminatorInterface $playerRelationDeterminator) {}

    #[Override]
    public function manage(SpacecraftWrapperInterface $wrapper, array $values, ManagerProviderInterface $managerProvider): array
    {
        $values = $values['reactor'] ?? null;
        if ($values === null) {
            throw new RuntimeException('value array not existent');
        }

        $ship = $wrapper->get();

        if (!array_key_exists($ship->getId(), $values)) {
            return [];
        }

        if ($values[$ship->getId()] === '' || $values[$ship->getId()] < 1) {
            return [];
        }

        $reactor = $wrapper->getReactorWrapper();
        if ($reactor === null) {
            return [];
        }

        if ($ship->isShielded() && !$this->playerRelationDeterminator->isFriend($ship->getUser(), $managerProvider->getUser())) {
            $msg[] = sprintf(
                _('%s: Warpkern konnte wegen aktivierter Schilde nicht aufgeladen werden.'),
                $ship->getName()
            );
            return $msg;
        }

        $storage = $managerProvider->getStorage();

        if ($this->reactorUtil->storageContainsNeededCommodities($storage, $reactor)) {
            $load = $values[$ship->getId()] == 'm' ? PHP_INT_MAX : (int)$values[$ship->getId()];
            $loadMessage = $this->reactorUtil->loadReactor($ship, $load, $managerProvider, $reactor);

            if ($loadMessage !== null) {
                return [$loadMessage];
            }
        } else {
            return $this->createMissingCommoditiesMessage($ship, $reactor);
        }

        return [];
    }

    /**
     * @return array<string>
     */
    private function createMissingCommoditiesMessage(SpacecraftInterface $spacecraft, ReactorWrapperInterface $reactor): array
    {
        $msg = [];

        $msg[] = sprintf(
            _('%s: Es werden mindestens folgende Waren zum Aufladen des %ss benötigt:'),
            $spacecraft->getName(),
            $reactor->get()->getSystemType()->getDescription()
        );

        foreach ($reactor->get()->getLoadCost() as $commodityId => $loadCost) {
            $commodity = $this->commodityCache->get($commodityId);
            $msg[] = sprintf(_('%d %s'), $loadCost, $commodity->getName());
        }

        return $msg;
    }
}
