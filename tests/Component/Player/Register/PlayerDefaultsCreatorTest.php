<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Override;
use Stu\Component\Map\MapEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\PrivateMessageFolderInterface;
use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Entity\ResearchInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserLayerInterface;
use Stu\Orm\Entity\TutorialStepInterface;
use Stu\Orm\Entity\UserTutorialInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ResearchRepositoryInterface;
use Stu\Orm\Repository\UserLayerRepositoryInterface;
use Stu\Orm\Repository\UserTutorialRepositoryInterface;
use Stu\Orm\Repository\TutorialStepRepositoryInterface;

class PlayerDefaultsCreatorTest extends MockeryTestCase
{
    private MockInterface&PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository;

    private MockInterface&ResearchedRepositoryInterface $researchedRepository;

    private MockInterface&LayerRepositoryInterface $layerRepository;

    private MockInterface&UserLayerRepositoryInterface $userLayerRepository;

    private MockInterface&TutorialStepRepositoryInterface $tutorialStepRepository;

    private MockInterface&UserTutorialRepositoryInterface $userTutorialRepository;

    private PlayerDefaultsCreatorInterface $defaultsCreator;

    #[Override]
    public function setUp(): void
    {
        $this->privateMessageFolderRepository = Mockery::mock(PrivateMessageFolderRepositoryInterface::class);
        $this->researchedRepository = Mockery::mock(ResearchedRepositoryInterface::class);
        $this->layerRepository = Mockery::mock(LayerRepositoryInterface::class);
        $this->userLayerRepository = Mockery::mock(UserLayerRepositoryInterface::class);
        $this->tutorialStepRepository = Mockery::mock(TutorialStepRepositoryInterface::class);
        $this->userTutorialRepository = Mockery::mock(UserTutorialRepositoryInterface::class);

        $this->defaultsCreator = new PlayerDefaultsCreator(
            $this->privateMessageFolderRepository,
            $this->researchedRepository,
            $this->layerRepository,
            $this->userLayerRepository,
            $this->tutorialStepRepository,
            $this->userTutorialRepository
        );
    }

    public function testCreateDefaultCreatesDefaults(): void
    {
        $user = Mockery::mock(UserInterface::class)->shouldAllowMockingProtectedMethods();
        /** @var UserInterface&MockInterface $user */
        $pmFolder = Mockery::mock(PrivateMessageFolderInterface::class);
        $startResearch = Mockery::mock(ResearchInterface::class);
        $researchEntry = Mockery::mock(ResearchedInterface::class);
        $layer = Mockery::mock(LayerInterface::class);
        $userLayer = Mockery::mock(UserLayerInterface::class);
        $tutorialStep = Mockery::mock(TutorialStepInterface::class);
        $userTutorial = Mockery::mock(UserTutorialInterface::class);

        $defaultCategoryCount = count(array_filter(
            PrivateMessageFolderTypeEnum::cases(),
            fn(PrivateMessageFolderTypeEnum $case): bool => $case->isDefault()
        ));

        $this->privateMessageFolderRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->times($defaultCategoryCount)
            ->andReturn($pmFolder);
        $this->privateMessageFolderRepository->shouldReceive('save')
            ->with($pmFolder)
            ->times($defaultCategoryCount);

        $pmFolder->shouldReceive('setUser')
            ->with($user)
            ->times($defaultCategoryCount)
            ->andReturnSelf();

        foreach (PrivateMessageFolderTypeEnum::cases() as $case) {
            if (!$case->isDefault()) {
                continue;
            }

            $label = $case->getDescription();

            $pmFolder->shouldReceive('setDescription')
                ->with($label)
                ->once()
                ->andReturnSelf();
            $pmFolder->shouldReceive('setSpecial')
                ->with($case)
                ->once()
                ->andReturnSelf();
            $pmFolder->shouldReceive('setSort')
                ->with($case->value)
                ->once()
                ->andReturnSelf();
        }

        $user->shouldReceive('getFaction->getStartResearch')
            ->withNoArgs()
            ->once()
            ->andReturn($startResearch);

        $this->researchedRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($researchEntry);
        $this->researchedRepository->shouldReceive('save')
            ->with($researchEntry)
            ->once();

        $researchEntry->shouldReceive('setResearch')
            ->with($startResearch)
            ->once()
            ->andReturnSelf();
        $researchEntry->shouldReceive('setUser')
            ->with($user)
            ->once()
            ->andReturnSelf();
        $researchEntry->shouldReceive('setFinished')
            ->with(Mockery::type('int'))
            ->once()
            ->andReturnSelf();
        $researchEntry->shouldReceive('setActive')
            ->with(0)
            ->once()
            ->andReturnSelf();

        $this->layerRepository->shouldReceive('find')
            ->with(MapEnum::DEFAULT_LAYER)
            ->once()
            ->andReturn($layer);
        $this->userLayerRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($userLayer);
        $this->userLayerRepository->shouldReceive('save')
            ->with($userLayer)
            ->once();
        $userLayer->shouldReceive('setLayer')
            ->with($layer)
            ->once();
        $userLayer->shouldReceive('setUser')
            ->with($user)
            ->once();
        $user->shouldReceive('getUserLayers->set')
            ->with(MapEnum::DEFAULT_LAYER, $userLayer)
            ->once();

        $this->tutorialStepRepository->shouldReceive('findAllFirstSteps')
            ->withNoArgs()
            ->once()
            ->andReturn([$tutorialStep]);

        $this->userTutorialRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($userTutorial);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $tutorialStep->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $userTutorial->shouldReceive('setUser')
            ->with($user)
            ->once();
        $userTutorial->shouldReceive('setTutorialStep')
            ->with($tutorialStep)
            ->once();
        $userTutorial->shouldReceive('setUserId')
            ->with(42)
            ->once();
        $userTutorial->shouldReceive('setTutorialStepId')
            ->with(1)
            ->once();

        $this->userTutorialRepository->shouldReceive('save')
            ->with($userTutorial)
            ->once();

        $this->defaultsCreator->createDefault($user);
    }
}
