<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Noodlehaus\ConfigInterface;
use Stu\Orm\Entity\ResearchInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ResearchDependencyRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

final class TalFactory implements TalFactoryInterface
{
    private ResearchDependencyRepositoryInterface $researchDependencyRepository;

    private ResearchedRepositoryInterface $researchedRepository;

    private ConfigInterface $config;

    public function __construct(
        ResearchDependencyRepositoryInterface $researchDependencyRepository,
        ResearchedRepositoryInterface $researchedRepository,
        ConfigInterface $config
    ) {
        $this->researchDependencyRepository = $researchDependencyRepository;
        $this->researchedRepository = $researchedRepository;
        $this->config = $config;
    }

    public function createTalSelectedTech(
        ResearchInterface $research,
        UserInterface $currentUser
    ): TalSelectedTechInterface {
        return new TalSelectedTech(
            $this->researchedRepository,
            $this->researchDependencyRepository,
            $research,
            $currentUser,
            $this->config
        );
    }
}
