<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Noodlehaus\ConfigInterface;
use RuntimeException;
use Stu\Module\Template\StatusBarColorEnum;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Entity\Research;
use Stu\Orm\Entity\ResearchDependency;
use Stu\Orm\Entity\Researched;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\ResearchDependencyRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ResearchRepositoryInterface;

final class SelectedTech implements SelectedTechInterface
{
    private ?Researched $state = null;

    /** @var null|array<string, TechDependency> */
    private ?array $excludes = null;

    /** @var null|array<string, TechDependency> */
    private ?array $dependencies = null;

    public function __construct(
        private ResearchRepositoryInterface $researchRepository,
        private ResearchedRepositoryInterface $researchedRepository,
        private ResearchDependencyRepositoryInterface $researchDependencyRepository,
        private BuildingRepositoryInterface $buildingRepository,
        private StatusBarFactoryInterface $statusBarFactory,
        private Research $research,
        private User $currentUser,
        private ConfigInterface $config
    ) {}

    #[\Override]
    public function getResearch(): Research
    {
        return $this->research;
    }

    #[\Override]
    public function getResearchState(): ?Researched
    {
        if ($this->state === null) {
            $this->state = $this->researchedRepository->getFor(
                $this->research->getId(),
                $this->currentUser->getId()
            );
        }
        return $this->state;
    }

    #[\Override]
    public function getDistinctExcludeNames(): array
    {
        if ($this->excludes === null) {
            $result = [];

            $techList = $this->researchDependencyRepository->getExcludesByResearch($this->research->getId());

            array_walk(
                $techList,
                function (ResearchDependency $dependecy) use (&$result): void {
                    $name = $dependecy->getResearchDependOn()->getName();

                    if (!array_key_exists($name, $result) && $name !== $this->research->getName()) {
                        $result[$name] = new TechDependency($name, $dependecy->getResearchDependOn()->getCommodity());
                    }
                }
            );

            $this->excludes = $result;
        }
        return $this->excludes;
    }

    #[\Override]
    public function hasExcludes(): bool
    {
        return $this->getDistinctExcludeNames() !== [];
    }

    #[\Override]
    public function getDistinctPositiveDependencyNames(): array
    {
        if ($this->dependencies === null) {
            $result = [];

            $techList = $this->researchRepository->getPossibleResearchByParent(
                $this->research->getId()
            );

            array_walk(
                $techList,
                function (Research $research) use (&$result): void {
                    $name = $research->getName();

                    if (!array_key_exists($name, $result) && $name !== $this->research->getName()) {
                        $result[$name] = new TechDependency($name, $research->getCommodity());
                    }
                }
            );

            $this->dependencies = $result;
        }
        return $this->dependencies;
    }

    #[\Override]
    public function hasPositiveDependencies(): bool
    {
        return $this->getDistinctPositiveDependencyNames() !== [];
    }

    #[\Override]
    public function getDonePoints(): int
    {
        $researchState = $this->getResearchState();

        return $researchState === null
            ? $this->getPoints()
            : $this->getPoints() - $researchState->getActive();
    }

    private function getPoints(): int
    {
        return $this->research->getPoints();
    }

    #[\Override]
    public function isResearchFinished(): bool
    {
        $researchState = $this->getResearchState();

        return $researchState === null
            ? false
            : $researchState->getFinished() > 0;
    }

    #[\Override]
    public function getBuildings(): array
    {
        return $this->buildingRepository->getByResearch($this->research);
    }

    #[\Override]
    public function getStatusBar(): string
    {
        $researchState = $this->getResearchState();
        if ($researchState === null) {
            throw new RuntimeException('can not call when no researchState present');
        }

        return $this->statusBarFactory
            ->createStatusBar()
            ->setColor(StatusBarColorEnum::BLUE)
            ->setLabel(_('Forschung'))
            ->setMaxValue($this->research->getPoints())
            ->setValue($this->research->getPoints() - $researchState->getActive())
            ->setSizeModifier(2)
            ->render();
    }

    #[\Override]
    public function getWikiLink(): string
    {
        return sprintf(
            '%s/index.php?title=Forschung:%s',
            $this->config->get('wiki.base_url'),
            str_replace(' ', '_', $this->research->getName())
        );
    }
}
