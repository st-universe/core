<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use Mockery;
use Mockery\MockInterface;
use Stu\Component\Map\MapEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\PrivateMessageFolder;
use Stu\Orm\Entity\Research;
use Stu\Orm\Entity\Researched;
use Stu\Orm\Entity\TutorialStep;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserLayer;
use Stu\Orm\Entity\UserTutorial;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\TutorialStepRepositoryInterface;
use Stu\Orm\Repository\UserLayerRepositoryInterface;
use Stu\Orm\Repository\UserTutorialRepositoryInterface;
use Stu\StuTestCase;

class PlayerDefaultsCreatorTest extends StuTestCase
{
    private MockInterface&PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository;

    private MockInterface&ResearchedRepositoryInterface $researchedRepository;

    private MockInterface&LayerRepositoryInterface $layerRepository;

    private MockInterface&UserLayerRepositoryInterface $userLayerRepository;

    private MockInterface&TutorialStepRepositoryInterface $tutorialStepRepository;

    private MockInterface&UserTutorialRepositoryInterface $userTutorialRepository;

    private PlayerDefaultsCreatorInterface $defaultsCreator;

    #[\Override]
    public function setUp(): void
    {
        $this->privateMessageFolderRepository = $this->mock(PrivateMessageFolderRepositoryInterface::class);
        $this->researchedRepository = $this->mock(ResearchedRepositoryInterface::class);
        $this->layerRepository = $this->mock(LayerRepositoryInterface::class);
        $this->userLayerRepository = $this->mock(UserLayerRepositoryInterface::class);
        $this->tutorialStepRepository = $this->mock(TutorialStepRepositoryInterface::class);
        $this->userTutorialRepository = $this->mock(UserTutorialRepositoryInterface::class);

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
        $user = $this->mock(User::class)->shouldAllowMockingProtectedMethods();
        /** @var User&MockInterface $user */
        $pmFolder = $this->mock(PrivateMessageFolder::class);
        $startResearch = $this->mock(Research::class);
        $researchEntry = $this->mock(Researched::class);
        $layer = $this->mock(Layer::class);
        $userLayer = $this->mock(UserLayer::class);
        $tutorialStep = $this->mock(TutorialStep::class);
        $userTutorial = $this->mock(UserTutorial::class);

        $defaultCategoryCount = count(array_filter(
            PrivateMessageFolderTypeEnum::cases(),
            fn (PrivateMessageFolderTypeEnum $case): bool => $case->isDefault()
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

        $userTutorial->shouldReceive('setUser')
            ->with($user)
            ->once();
        $userTutorial->shouldReceive('setTutorialStep')
            ->with($tutorialStep)
            ->once();

        $this->userTutorialRepository->shouldReceive('save')
            ->with($userTutorial)
            ->once();

        $this->defaultsCreator->createDefault($user);
    }
}
