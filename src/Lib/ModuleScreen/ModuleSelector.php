<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen;

use InvalidArgumentException;
use Override;
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
    private const string MACRO = 'html/ship/construction/moduleSelector/selector.twig';
    private const string TEMPLATE = 'html/ajaxempty.twig';

    /** @var ModuleSelectorEntryInterface[]|null */
    private ?array $moduleSelectorEntries = null;
    private ?ShipRumpModuleLevelInterface $shipRumpModuleLevel = null;

    public function __construct(private ModuleRepositoryInterface $moduleRepository, private ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository, private TwigPageInterface $twigPage, private ShipModuleTypeEnum $moduleType, private ColonyInterface|ShipInterface $host, private ShipRumpInterface $rump, private UserInterface $user, private ?ModuleSelectorAddonInterface $addon, private ?ShipBuildplanInterface $buildplan = null) {}

    #[Override]
    public function isMandatory(): bool
    {
        if ($this->isSpecial()) {
            return false;
        }
        $moduleLevels = $this->shipRumpModuleLevelRepository->getByShipRump($this->rump->getId());

        return $moduleLevels->{'getModuleMandatory' . $this->getModuleType()->value}() > 0;
    }

    #[Override]
    public function isSpecial(): bool
    {
        return $this->getModuleType() === ShipModuleTypeEnum::SPECIAL;
    }

    public function getScreenTab(): ModuleScreenTab
    {
        return new ModuleScreenTab($this);
    }

    #[Override]
    public function allowMultiple(): bool
    {
        return $this->isSpecial();
    }

    #[Override]
    public function render(): string
    {
        $this->twigPage->setTemplate(self::TEMPLATE);
        $this->twigPage->setVar('THIS', $this);
        $this->twigPage->setVar('MACRO', self::MACRO);
        return $this->twigPage->render();
    }

    #[Override]
    public function getModuleType(): ShipModuleTypeEnum
    {
        return $this->moduleType;
    }

    #[Override]
    public function allowEmptySlot(): bool
    {
        return !$this->isSpecial() && !$this->isMandatory();
    }

    #[Override]
    public function getModuleDescription(): string
    {
        return $this->getModuleType()->getDescription();
    }

    #[Override]
    public function getUserId(): int
    {
        return $this->user->getId();
    }

    #[Override]
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

    #[Override]
    public function getAvailableModules(): array
    {
        if ($this->moduleSelectorEntries === null) {
            $this->moduleSelectorEntries = [];
            $modules = [];

            $host = $this->getHost();
            if ($this->getModuleType() === ShipModuleTypeEnum::SPECIAL) {
                $modules = $this->moduleRepository->getBySpecialTypeAndRumpWithoutRole(
                    $host,
                    $this->getModuleType(),
                    $this->getRump()->getId()
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
                    $this->moduleRepository,
                    $this->getBuildplan()
                );
            }
        }
        return $this->moduleSelectorEntries;
    }

    #[Override]
    public function hasSelectedModule(): bool
    {
        return $this->getSelectedModules() !== [];
    }

    #[Override]
    public function getSelectedModuleCount(): int
    {
        return count($this->getSelectedModules());
    }

    #[Override]
    public function getSelectedModules(): array
    {
        return array_filter(
            $this->getAvailableModules(),
            fn(ModuleSelectorEntryInterface $entry): bool => $entry->isChosen()
        );
    }

    #[Override]
    public function getHost(): ColonyInterface|ShipInterface
    {
        return $this->host;
    }

    #[Override]
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

    #[Override]
    public function getModuleTypeLevel(): int
    {
        if ($this->isSpecial()) {
            return 0;
        }

        return $this->getShipRumpModuleLevel()
            ->{'getModuleLevel' . $this->getModuleType()->value}();
    }

    #[Override]
    public function getAddon(): ?ModuleSelectorAddonInterface
    {
        return $this->addon;
    }
}