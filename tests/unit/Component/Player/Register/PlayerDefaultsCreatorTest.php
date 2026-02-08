<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
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

class PlayerDefaultsCreatorTest extends MockeryTestCase
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
        $user = Mockery::mock(User::class)->shouldAllowMockingProtectedMethods();
        /** @var User&MockInterface $user */
        $pmFolder = Mockery::mock(PrivateMessageFolder::class);
        $startResearch = Mockery::mock(Research::class);
        $researchEntry = Mockery::mock(Researched::class);
        $layer = Mockery::mock(Layer::class);
        $userLayer = Mockery::mock(UserLayer::class);
        $tutorialStep = Mockery::mock(TutorialStep::class);
        $userTutorial = Mockery::mock(UserTutorial::class);

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
