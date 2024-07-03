<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen;

use InvalidArgumentException;
use RuntimeException;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Lib\ModuleScreen\Addon\ModuleSelectorAddonInterface;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Entity\ShipRumpModuleLevelInterface;
use Stu\Orm\Entity\ShipRumpRoleInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;

class ModuleSelector implements ModuleSelectorInterface
{
    private const MACRO = 'html/ship/construction/moduleSelector/selector.twig';
    private const TEMPLATE = 'html/ajaxempty.twig';

    /** @var ModuleSelectorEntryInterface[]|null */
    private ?array $moduleSelectorEntries = null;
    private ShipModuleTypeEnum $moduleType;
    private ShipRumpInterface $rump;
    private UserInterface $user;
    private ColonyInterface|ShipInterface $host;
    private ?ShipBuildplanInterface $buildplan;
    private ?ModuleSelectorAddonInterface $addon;
    private ?ShipRumpModuleLevelInterface $shipRumpModuleLevel = null;

    private ModuleRepositoryInterface $moduleRepository;
    private ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository;

    private TwigPageInterface $twigPage;

    public function __construct(
        ModuleRepositoryInterface $moduleRepository,
        ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository,
        TwigPageInterface $twigPage,
        ShipModuleTypeEnum $moduleType,
        ColonyInterface|ShipInterface $host,
        ShipRumpInterface $rump,
        UserInterface $user,
        ?ModuleSelectorAddonInterface $addon,
        ?ShipBuildplanInterface $buildplan = null
    ) {
        $this->moduleType = $moduleType;
        $this->rump = $rump;
        $this->user = $user;
        $this->host = $host;
        $this->buildplan = $buildplan;
        $this->moduleRepository = $moduleRepository;
        $this->addon = $addon;
        $this->shipRumpModuleLevelRepository = $shipRumpModuleLevelRepository;
        $this->twigPage = $twigPage;
    }

    public function isMandatory(): bool
    {
        if ($this->isSpecial()) {
            return false;
        }
        $moduleLevels = $this->shipRumpModuleLevelRepository->getByShipRump($this->rump->getId());

        return $moduleLevels->{'getModuleMandatory' . $this->getModuleType()->value}() > 0;
    }

    public function isSpecial(): bool
    {
        return $this->getModuleType() === ShipModuleTypeEnum::SPECIAL;
    }

    public function getScreenTab(): ModuleScreenTab
    {
        return new ModuleScreenTab($this);
    }

    public function allowMultiple(): bool
    {
        return $this->isSpecial();
    }

    public function render(): string
    {
        $this->twigPage->setTemplate(self::TEMPLATE);
        $this->twigPage->setVar('THIS', $this);
        $this->twigPage->setVar('MACRO', self::MACRO);
        return $this->twigPage->render();
    }

    public function getModuleType(): ShipModuleTypeEnum
    {
        return $this->moduleType;
    }

    public function allowEmptySlot(): bool
    {
        return !$this->isSpecial() && !$this->isMandatory();
    }

    public function getModuleDescription(): string
    {
        return $this->getModuleType()->getDescription();
    }

    public function getUserId(): int
    {
        return $this->user->getId();
    }

    public function getRump(): ShipRumpInterface
    {
        return $this->rump;
    }

    private function getShipRumpRole(): ShipRumpRoleInterface
    {
        $shipRumpRole = $this->getRump()->getShipRumpRole();

        if ($shipRumpRole === null) {
            throw new InvalidArgumentException('invalid rump without rump role');
        }

        return $shipRumpRole;
    }

    public function getAvailableModules(): array
    {
        if ($this->moduleSelectorEntries === null) {
            $this->moduleSelectorEntries = [];
            $modules = [];

            $host = $this->getHost();
            if ($this->getModuleType() === ShipModuleTypeEnum::SPECIAL) {
                $modules = $this->moduleRepository->getBySpecialTypeAndRump(
                    $host,
                    $this->getModuleType(),
                    $this->getRump()->getId(),
                    $this->getShipRumpRole()->getId()
                );
            } elseif ($this->getHost() instanceof ColonyInterface) {
                $mod_level = $this->shipRumpModuleLevelRepository->getByShipRump(
                    $this->getRump()->getId()
                );
                $min_level = $mod_level->{'getModuleLevel' . $this->getModuleType()->value . 'Min'}();
                $max_level = $mod_level->{'getModuleLevel' . $this->getModuleType()->value . 'Max'}();
                $modules = $this->moduleRepository->getByTypeColonyAndLevel(
                    $host->getId(),
                    $this->getModuleType(),
                    $this->getShipRumpRole()->getId(),
                    range($min_level, $max_level)
                );
            }
            foreach ($modules as $obj) {
                $this->moduleSelectorEntries[$obj->getId()] = new ModuleSelectorEntry(
                    $this,
                    $obj,
                    $this->getRump(),
                    $this->getShipRumpModuleLevel(),
                    $this->getHost(),
                    $this->user,
                    $this->getBuildplan()
                );
            }
        }
        return $this->moduleSelectorEntries;
    }

    public function hasSelectedModule(): bool
    {
        return $this->getSelectedModules() !== [];
    }

    public function getSelectedModuleCount(): int
    {
        return count($this->getSelectedModules());
    }

    public function getSelectedModules(): array
    {
        return array_filter(
            $this->getAvailableModules(),
            fn (ModuleSelectorEntryInterface $entry): bool => $entry->isChosen()
        );
    }

    public function getHost(): ColonyInterface|ShipInterface
    {
        return $this->host;
    }

    public function getBuildplan(): ?ShipBuildplanInterface
    {
        return $this->buildplan;
    }

    private function getShipRumpModuleLevel(): ShipRumpModuleLevelInterface
    {
        if ($this->shipRumpModuleLevel === null) {
            $this->shipRumpModuleLevel = $this->shipRumpModuleLevelRepository->getByShipRump($this->rump->getId());
        }

        if ($this->shipRumpModuleLevel === null) {
            throw new RuntimeException(sprintf('no shipRumpModuleLevel found for rumpId %d', $this->rump->getId()));
        }

        return $this->shipRumpModuleLevel;
    }

    public function getModuleTypeLevel(): int
    {
        if ($this->isSpecial()) {
            return 0;
        }

        return $this->getShipRumpModuleLevel()
            ->{'getModuleLevel' . $this->getModuleType()->value}();
    }

    public function getAddon(): ?ModuleSelectorAddonInterface
    {
        return $this->addon;
    }
}
