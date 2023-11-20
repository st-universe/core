<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use request;
use Stu\Component\Colony\Shields\ColonyShieldingManagerInterface;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Module\Colony\Lib\Gui\GuiComponentEnum;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Commodity\Lib\CommodityCacheInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Tal\StatusBarColorEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonySandboxInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class ColonyGuiHelper implements ColonyGuiHelperInterface
{
    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private CommodityRepositoryInterface $commodityRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private CommodityCacheInterface $commodityCache;

    /** @var array<int, ColonyProduction> */
    private ?array $production = null;

    private ?ColonyShieldingManagerInterface $shieldingManager = null;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository,
        CommodityRepositoryInterface $commodityRepository,
        ColonyLibFactoryInterface $colonyLibFactory,
        CommodityCacheInterface $commodityCache
    ) {
        $this->planetFieldRepository = $planetFieldRepository;
        $this->commodityRepository = $commodityRepository;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->commodityCache = $commodityCache;
    }

    public function registerComponents(
        PlanetFieldHostInterface $host,
        GameControllerInterface $game,
        array $whitelist = null
    ): void {

        $components = $whitelist ?? GuiComponentEnum::cases();
        foreach ($components as $component) {
            $method = $component->value;

            $this->$method($host, $game);
        }

        $game->setTemplateVar('HOST', $host);

        if ($host instanceof ColonyInterface) {
            $game->setTemplateVar('COLONY', $host);
            $game->setTemplateVar('FORM_ACTION', 'colony.php');
        }
        if ($host instanceof ColonySandboxInterface) {
            $game->setTemplateVar('COLONY', $host->getColony());
            $game->setTemplateVar('FORM_ACTION', '/admin/index.php');
        }
    }

    /** @return array<int, ColonyProduction> */
    private function getProduction(PlanetFieldHostInterface $host): array
    {
        if ($this->production === null) {
            $this->production = $this->colonyLibFactory->createColonyCommodityProduction($host)->getProduction();
        }

        return $this->production;
    }

    private function registerSurface(PlanetFieldHostInterface $host, GameControllerInterface $game): void
    {
        $game->setTemplateVar(
            'COLONY_SURFACE',
            $this->colonyLibFactory->createColonySurface($host, request::getInt('bid') !== 0 ? request::getInt('bid') : null)
        );
    }

    private function registerEffects(PlanetFieldHostInterface $host, GameControllerInterface $game): void
    {
        $commodities = $this->commodityCache->getAll(CommodityTypeEnum::COMMODITY_TYPE_EFFECT);
        $depositMinings = $host instanceof ColonyInterface ? $host->getUserDepositMinings() : [];
        $prod = $this->getProduction($host);

        $effects = [];
        foreach ($commodities as $value) {
            $commodityId = $value->getId();

            //skip deposit effects on asteroid
            if (array_key_exists($commodityId, $depositMinings)) {
                continue;
            }

            if (!array_key_exists($commodityId, $prod) || $prod[$commodityId]->getProduction() == 0) {
                continue;
            }
            $effects[$commodityId]['commodity'] = $value;
            $effects[$commodityId]['production'] = $prod[$commodityId];
        }

        $game->setTemplateVar('EFFECTS', $effects);
    }

    private function registerStorage(PlanetFieldHostInterface $host, GameControllerInterface $game): void
    {
        $commodities = $this->commodityCache->getAll(CommodityTypeEnum::COMMODITY_TYPE_STANDARD);

        $prod = $this->getProduction($host);
        $game->setTemplateVar(
            'PRODUCTION_SUM',
            $this->colonyLibFactory->createColonyProductionSumReducer()->reduce($prod)
        );

        if (!$host instanceof ColonyInterface) {
            return;
        }

        $stor = $host->getStorage();
        $storage = [];
        foreach ($commodities as $value) {
            $commodityId = $value->getId();
            if (array_key_exists($commodityId, $prod)) {
                $storage[$commodityId]['commodity'] = $value;
                $storage[$commodityId]['production'] = $prod[$commodityId];
                $storage[$commodityId]['storage'] = $stor->containsKey($commodityId) ? $stor[$commodityId] : false;
            } elseif ($stor->containsKey($commodityId)) {
                $storage[$commodityId]['commodity'] = $value;
                $storage[$commodityId]['storage'] = $stor[$commodityId];
                $storage[$commodityId]['production'] = false;
            }
        }

        $game->setTemplateVar('STORAGE', $storage);
    }

    private function registerShieldingManager(PlanetFieldHostInterface $host, GameControllerInterface $game): void
    {
        $game->setTemplateVar('SHIELDING_MANAGER', $this->getShieldingManager($host));
    }

    private function registerShieldBar(PlanetFieldHostInterface $host, GameControllerInterface $game): void
    {
        $shieldingManager = $this->getShieldingManager($host);

        if ($shieldingManager->hasShielding()) {
            $game->setTemplateVar(
                'SHIELD_STATUS_BAR',
                $this->buildShieldBar($shieldingManager, $host)
            );
        }
    }

    private function registerBuildingManagement(PlanetFieldHostInterface $host, GameControllerInterface $game): void
    {
        $list = $this->planetFieldRepository->getByColonyWithBuilding($host);

        $game->setTemplateVar('PLANET_FIELD_LIST', $list);
        $game->setTemplateVar('USEABLE_COMMODITY_LIST', $this->commodityRepository->getByBuildingsOnColony($host));
    }

    private function getShieldingManager(PlanetFieldHostInterface $host): ColonyShieldingManagerInterface
    {
        if ($this->shieldingManager === null) {
            $this->shieldingManager = $this->colonyLibFactory->createColonyShieldingManager($host);
        }

        return $this->shieldingManager;
    }

    private function registerEpsBar(PlanetFieldHostInterface $host, GameControllerInterface $game): void
    {
        $energyProduction = $this->planetFieldRepository->getEnergyProductionByColony($host);

        $currentEps = $host instanceof ColonyInterface ? $host->getEps() : 0;
        $width = 360;
        $bars = [];
        $epsBar = '';
        if ($energyProduction < 0) {
            $prod = abs($energyProduction);
            if ($currentEps - $prod < 0) {
                $bars[StatusBarColorEnum::STATUSBAR_RED] = $currentEps;
                $bars[StatusBarColorEnum::STATUSBAR_GREY] = $host->getMaxEps() - $currentEps;
            } else {
                $bars[StatusBarColorEnum::STATUSBAR_YELLOW] = $currentEps - $prod;
                $bars[StatusBarColorEnum::STATUSBAR_RED] = $prod;
                $bars[StatusBarColorEnum::STATUSBAR_GREY] = $host->getMaxEps() - $currentEps;
            }
        }
        if ($energyProduction > 0) {
            if ($currentEps + $energyProduction > $host->getMaxEps()) {
                $bars[StatusBarColorEnum::STATUSBAR_YELLOW] = $currentEps;
                if ($currentEps < $host->getMaxEps()) {
                    $bars[StatusBarColorEnum::STATUSBAR_GREEN] = $host->getMaxEps() - $currentEps;
                }
            } else {
                $bars[StatusBarColorEnum::STATUSBAR_YELLOW] = $currentEps;
                $bars[StatusBarColorEnum::STATUSBAR_GREEN] = $energyProduction;
                $bars[StatusBarColorEnum::STATUSBAR_GREY] = $host->getMaxEps() - $currentEps - $energyProduction;
            }
        }
        if ($energyProduction == 0) {
            $bars[StatusBarColorEnum::STATUSBAR_YELLOW] = $currentEps;
            $bars[StatusBarColorEnum::STATUSBAR_GREY] = $host->getMaxEps() - $currentEps;
        }
        foreach ($bars as $color => $value) {
            if ($host->getMaxEps() < $value) {
                $value = $host->getMaxEps();
            }
            if ($value <= 0) {
                continue;
            }
            $epsBar .= sprintf(
                '<img src="/assets/bars/balken.png" style="background-color: #%s;height: 12px; width: %dpx;" title="%s" />',
                $color,
                round($width / 100 * (100 / $host->getMaxEps() * $value)),
                'Energieproduktion'
            );
        }

        $game->setTemplateVar(
            'EPS_STATUS_BAR',
            $epsBar
        );
    }

    private function buildShieldBar(
        ColonyShieldingManagerInterface $colonyShieldingManager,
        PlanetFieldHostInterface $host
    ): string {
        $shieldBar = '';
        $bars = [];
        $width = 360;

        $currentShields = $host instanceof ColonyInterface ? $host->getShields() : 0;
        $maxShields = $colonyShieldingManager->getMaxShielding();

        if ($colonyShieldingManager->isShieldingEnabled()) {
            $bars[StatusBarColorEnum::STATUSBAR_SHIELD_ON] = $currentShields;
        } else {
            $bars[StatusBarColorEnum::STATUSBAR_SHIELD_OFF] = $currentShields;
        }
        $bars[StatusBarColorEnum::STATUSBAR_GREY] = $maxShields - $currentShields;

        foreach ($bars as $color => $value) {
            if ($maxShields < $value) {
                $value = $maxShields;
            }
            if ($value <= 0) {
                continue;
            }
            $shieldBar .= sprintf(
                '<img src="/assets/bars/balken.png" style="background-color: #%s;height: 12px; width: %dpx;" title="%s" />',
                $color,
                round($width / 100 * (100 / $maxShields * $value)),
                'Schildstärke'
            );
        }

        return $shieldBar;
    }
}
