<?php

declare(strict_types=1);

namespace Stu\Lib\ShipManagement\Manager;

use RuntimeException;
use Stu\Component\Ship\ShipEnum;
use Stu\Lib\ShipManagement\Provider\ManagerProviderInterface;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Ship\Lib\ReactorUtilInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

class ManageReactor implements ManagerInterface
{
    private ReactorUtilInterface $reactorUtil;

    public function __construct(
        ReactorUtilInterface $reactorUtil
    ) {
        $this->reactorUtil = $reactorUtil;
    }

    public function manage(ShipWrapperInterface $wrapper, array $values, ManagerProviderInterface $managerProvider): array
    {
        $reactor = $values['reactor'] ?? null;
        if ($reactor === null) {
            throw new RuntimeException('value array not existent');
        }

        $ship = $wrapper->get();

        if (!array_key_exists($ship->getId(), $reactor)) {
            return [];
        }

        if ($reactor[$ship->getId()] < 1) {
            return [];
        }

        $hasWarpcore = $ship->hasWarpcore();
        $hasFusionReactor = $ship->hasFusionReactor();

        if (!$hasWarpcore && !$hasFusionReactor) {
            return [];
        }

        $storage = $managerProvider->getStorage();

        if ($this->reactorUtil->storageContainsNeededCommodities($storage, $hasWarpcore)) {
            $load = $reactor[$ship->getId()] == 'm' ? PHP_INT_MAX : (int)$reactor[$ship->getId()];
            $loadMessage = $this->reactorUtil->loadReactor($ship, $load, $managerProvider, $hasWarpcore);

            if ($loadMessage !== null) {
                return [$loadMessage];
            }
        } else {
            return $this->createMissingCommoditiesMessage($ship, $hasWarpcore);
        }

        return [];
    }

    /**
     * @return array<string>
     */
    private function createMissingCommoditiesMessage(ShipInterface $ship, bool $hasWarpcore): array
    {
        $msg = [];

        $msg[] = sprintf(
            _('%s: Es werden mindestens folgende Waren zum Aufladen des %s benötigt:'),
            $ship->getName(),
            $hasWarpcore ? 'Warpkerns' : 'Fusionsreaktors'
        );
        $costs = $hasWarpcore ? ShipEnum::WARPCORE_LOAD_COST : ShipEnum::REACTOR_LOAD_COST;
        foreach ($costs as $commodityId => $loadCost) {
            $msg[] = sprintf(_('%d %s'), $loadCost, CommodityTypeEnum::getDescription($commodityId));
        }

        return $msg;
    }
}
