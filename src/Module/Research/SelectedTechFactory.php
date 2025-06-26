<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Noodlehaus\ConfigInterface;
use Override;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Entity\Research;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\ResearchDependencyRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ResearchRepositoryInterface;

final class SelectedTechFactory implements SelectedTechFactoryInterface
{
    public function __construct(
        private ResearchRepositoryInterface $researchRepository,
        private ResearchDependencyRepositoryInterface $researchDependencyRepository,
        private ResearchedRepositoryInterface $researchedRepository,
        private BuildingRepositoryInterface $buildingRepository,
        private StatusBarFactoryInterface $statusBarFactory,
        private ConfigInterface $config
    ) {}

    #[Override]
    public function createSelectedTech(
        Research $research,
        User $currentUser
    ): SelectedTechInterface {
        return new SelectedTech(
            $this->researchRepository,
            $this->researchedRepository,
            $this->researchDependencyRepository,
            $this->buildingRepository,
            $this->statusBarFactory,
            $research,
            $currentUser,
            $this->config
        );
    }
}
