<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Stu\Orm\Entity\ResearchedInterface;
use Noodlehaus\ConfigInterface;
use Stu\Module\Tal\StatusBarColorEnum;
use Stu\Module\Tal\TalStatusBar;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\ResearchDependencyInterface;
use Stu\Orm\Entity\ResearchInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ResearchDependencyRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ResearchRepositoryInterface;

final class TalSelectedTech implements TalSelectedTechInterface
{
    private ResearchInterface $research;

    private UserInterface $currentUser;

    private ResearchRepositoryInterface $researchRepository;

    private ResearchedRepositoryInterface $researchedRepository;

    private ResearchDependencyRepositoryInterface $researchDependencyRepository;

    private ConfigInterface $config;

    private ?ResearchedInterface $state = null;

    private ?array $excludes = null;

    private ?array $dependencies = null;

    public function __construct(
        ResearchRepositoryInterface $researchRepository,
        ResearchedRepositoryInterface $researchedRepository,
        ResearchDependencyRepositoryInterface $researchDependencyRepository,
        ResearchInterface $research,
        UserInterface $currentUser,
        ConfigInterface $config
    ) {
        $this->research = $research;
        $this->currentUser = $currentUser;
        $this->researchRepository = $researchRepository;
        $this->researchedRepository = $researchedRepository;
        $this->researchDependencyRepository = $researchDependencyRepository;
        $this->config = $config;
    }

    public function getId(): int
    {
        return $this->research->getId();
    }

    public function getName(): string
    {
        return $this->research->getName();
    }

    public function getDescription(): string
    {
        return $this->research->getDescription();
    }

    public function getPoints(): int
    {
        return $this->research->getPoints();
    }

    public function getCommodityId(): int
    {
        return $this->research->getCommodityId();
    }

    public function getUpperPlanetLimit(): int
    {
        return $this->research->getUpperPlanetLimit();
    }

    public function getUpperMoonLimit(): int
    {
        return $this->research->getUpperMoonLimit();
    }

    public function getUpperAsteroidLimit(): int
    {
        return $this->research->getUpperAsteroidLimit();
    }

    public function getCommodity(): CommodityInterface
    {
        return $this->research->getCommodity();
    }

    public function getResearchState()
    {
        if ($this->state === null) {
            $this->state = $this->researchedRepository->getFor(
                $this->research->getId(),
                $this->currentUser->getId()
            );
        }
        return $this->state;
    }

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

    public function hasExcludes(): bool
    {
        return $this->getDistinctExcludeNames() !== [];
    }

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

    public function hasPositiveDependencies(): bool
    {
        return $this->getDistinctPositiveDependencyNames() !== [];
    }

    public function getDonePoints(): int
    {
        return $this->getPoints() - $this->getResearchState()->getActive();
    }

    public function isResearchFinished(): bool
    {
        return $this->getResearchState()->getFinished() > 0;
    }

    public function getStatusBar(): string
    {
        return (new TalStatusBar())
            ->setColor(StatusBarColorEnum::STATUSBAR_BLUE)
            ->setLabel(_('Forschung'))
            ->setMaxValue($this->research->getPoints())
            ->setValue($this->research->getPoints() - $this->getResearchState()->getActive())
            ->setSizeModifier(2)
            ->render();
    }

    public function getWikiLink(): string
    {
        return sprintf(
            '%s/index.php?title=Forschung:%s',
            $this->config->get('wiki.base_url'),
            str_replace(' ', '_', $this->research->getName())
        );
    }
}
