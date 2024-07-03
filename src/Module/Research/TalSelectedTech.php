<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Noodlehaus\ConfigInterface;
use Override;
use RuntimeException;
use Stu\Module\Tal\StatusBarColorEnum;
use Stu\Module\Tal\TalStatusBar;
use Stu\Orm\Entity\ResearchDependencyInterface;
use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Entity\ResearchInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\ResearchDependencyRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ResearchRepositoryInterface;

final class TalSelectedTech implements TalSelectedTechInterface
{
    private ?ResearchedInterface $state = null;

    /** @var null|array<string, TechDependency> */
    private ?array $excludes = null;

    /** @var null|array<string, TechDependency> */
    private ?array $dependencies = null;

    public function __construct(private ResearchRepositoryInterface $researchRepository, private ResearchedRepositoryInterface $researchedRepository, private ResearchDependencyRepositoryInterface $researchDependencyRepository, private BuildingRepositoryInterface $buildingRepository, private ResearchInterface $research, private UserInterface $currentUser, private ConfigInterface $config)
    {
    }

    #[Override]
    public function getResearch(): ResearchInterface
    {
        return $this->research;
    }

    #[Override]
    public function getResearchState(): ?ResearchedInterface
    {
        if ($this->state === null) {
            $this->state = $this->researchedRepository->getFor(
                $this->research->getId(),
                $this->currentUser->getId()
            );
        }
        return $this->state;
    }

    #[Override]
    public function getDistinctExcludeNames(): array
    {
        if ($this->excludes === null) {
            $result = [];

            $techList = $this->researchDependencyRepository->getExcludesByResearch($this->research->getId());

            array_walk(
                $techList,
                function (ResearchDependencyInterface $dependecy) use (&$result): void {
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

    #[Override]
    public function hasExcludes(): bool
    {
        return $this->getDistinctExcludeNames() !== [];
    }

    #[Override]
    public function getDistinctPositiveDependencyNames(): array
    {
        if ($this->dependencies === null) {
            $result = [];

            $techList = $this->researchRepository->getPossibleResearchByParent(
                $this->research->getId()
            );

            array_walk(
                $techList,
                function (ResearchInterface $research) use (&$result): void {
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

    #[Override]
    public function hasPositiveDependencies(): bool
    {
        return $this->getDistinctPositiveDependencyNames() !== [];
    }

    #[Override]
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

    #[Override]
    public function isResearchFinished(): bool
    {
        $researchState = $this->getResearchState();

        return $researchState === null
            ? false
            : $researchState->getFinished() > 0;
    }

    #[Override]
    public function getBuildings(): array
    {
        return $this->buildingRepository->getByResearch($this->research);
    }

    #[Override]
    public function getStatusBar(): string
    {
        $researchState = $this->getResearchState();
        if ($researchState === null) {
            throw new RuntimeException('can not call when no researchState present');
        }

        return (new TalStatusBar())
            ->setColor(StatusBarColorEnum::STATUSBAR_BLUE)
            ->setLabel(_('Forschung'))
            ->setMaxValue($this->research->getPoints())
            ->setValue($this->research->getPoints() - $researchState->getActive())
            ->setSizeModifier(2)
            ->render();
    }

    #[Override]
    public function getWikiLink(): string
    {
        return sprintf(
            '%s/index.php?title=Forschung:%s',
            $this->config->get('wiki.base_url'),
            str_replace(' ', '_', $this->research->getName())
        );
    }
}
