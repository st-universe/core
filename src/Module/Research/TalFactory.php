<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Noodlehaus\ConfigInterface;
use Stu\Orm\Entity\ResearchInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\ResearchDependencyRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ResearchRepositoryInterface;

final class TalFactory implements TalFactoryInterface
{
    private ResearchRepositoryInterface $researchRepository;

    private ResearchDependencyRepositoryInterface $researchDependencyRepository;

    private ResearchedRepositoryInterface $researchedRepository;

    private BuildingRepositoryInterface $buildingRepository;

    private ConfigInterface $config;

    public function __construct(
        ResearchRepositoryInterface $researchRepository,
        ResearchDependencyRepositoryInterface $researchDependencyRepository,
        ResearchedRepositoryInterface $researchedRepository,
        BuildingRepositoryInterface $buildingRepository,
        ConfigInterface $config
    ) {
        $this->researchRepository = $researchRepository;
        $this->researchDependencyRepository = $researchDependencyRepository;
        $this->researchedRepository = $researchedRepository;
        $this->buildingRepository = $buildingRepository;
        $this->config = $config;
    }

    public function createTalSelectedTech(
        ResearchInterface $research,
        UserInterface $currentUser
    ): TalSelectedTechInterface {
        return new TalSelectedTech(
            $this->researchRepository,
            $this->researchedRepository,
            $this->researchDependencyRepository,
            $this->buildingRepository,
            $research,
            $currentUser,
            $this->config
        );
    }
}
