<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Stu\Component\Map\MapEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\PrivateMessageFolderInterface;
use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Entity\ResearchInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserLayerInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ResearchRepositoryInterface;
use Stu\Orm\Repository\UserLayerRepositoryInterface;

class PlayerDefaultsCreatorTest extends MockeryTestCase
{
    /**
     * @var null|MockInterface|PrivateMessageFolderRepositoryInterface
     */
    private $privateMessageFolderRepository;

    /**
     * @var null|MockInterface|ResearchRepositoryInterface
     */
    private $researchedRepository;

    /**
     * @var null|MockInterface|LayerRepositoryInterface
     */
    private $layerRepository;

    /**
     * @var null|MockInterface|UserLayerRepositoryInterface
     */
    private $userLayerRepository;

    /**
     * @var null|PlayerDefaultsCreator
     */
    private $defaultsCreator;

    public function setUp(): void
    {
        $this->privateMessageFolderRepository = Mockery::mock(PrivateMessageFolderRepositoryInterface::class);
        $this->researchedRepository = Mockery::mock(ResearchedRepositoryInterface::class);
        $this->layerRepository = Mockery::mock(LayerRepositoryInterface::class);
        $this->userLayerRepository = Mockery::mock(UserLayerRepositoryInterface::class);

        $this->defaultsCreator = new PlayerDefaultsCreator(
            $this->privateMessageFolderRepository,
            $this->researchedRepository,
            $this->layerRepository,
            $this->userLayerRepository
        );
    }

    public function testCreateDefaultCreatesDefaults(): void
    {
        $user = Mockery::mock(UserInterface::class);
        $pmFolder = Mockery::mock(PrivateMessageFolderInterface::class);
        $startResearch = Mockery::mock(ResearchInterface::class);
        $researchEntry = Mockery::mock(ResearchedInterface::class);
        $layer = Mockery::mock(LayerInterface::class);
        $userLayer = Mockery::mock(UserLayerInterface::class);


        $defaultCategoryCount = count(array_filter(
            PrivateMessageFolderTypeEnum::cases(),
            fn (PrivateMessageFolderTypeEnum $case) => $case->isDefault()
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

        $this->defaultsCreator->createDefault($user);
    }
}
