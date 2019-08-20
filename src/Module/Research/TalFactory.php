<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Stu\Orm\Entity\ResearchInterface;
use Stu\Orm\Repository\ResearchDependencyRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use UserData;

final class TalFactory implements TalFactoryInterface
{
    private $researchDependencyRepository;

    private $researchedRepository;

    public function __construct(
        ResearchDependencyRepositoryInterface $researchDependencyRepository,
        ResearchedRepositoryInterface $researchedRepository
    ) {
        $this->researchDependencyRepository = $researchDependencyRepository;
        $this->researchedRepository = $researchedRepository;
    }

    public function createTalSelectedTech(
        ResearchInterface $research,
        UserData $currentUser
    ): TalSelectedTechInterface {
        return new TalSelectedTech(
            $this->researchedRepository,
            $this->researchDependencyRepository,
            $research,
            $currentUser
        );
    }
}