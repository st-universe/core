<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Stu\Orm\Entity\ResearchInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ResearchDependencyRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

final class TalFactory implements TalFactoryInterface
{
    private ResearchDependencyRepositoryInterface $researchDependencyRepository;

    private ResearchedRepositoryInterface $researchedRepository;

    public function __construct(
        ResearchDependencyRepositoryInterface $researchDependencyRepository,
        ResearchedRepositoryInterface $researchedRepository
    ) {
        $this->researchDependencyRepository = $researchDependencyRepository;
        $this->researchedRepository = $researchedRepository;
    }

    public function createTalSelectedTech(
        ResearchInterface $research,
        UserInterface $currentUser
    ): TalSelectedTechInterface {
        return new TalSelectedTech(
            $this->researchedRepository,
            $this->researchDependencyRepository,
            $research,
            $currentUser
        );
    }
}
