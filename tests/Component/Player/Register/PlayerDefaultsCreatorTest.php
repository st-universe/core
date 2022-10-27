<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Orm\Entity\PrivateMessageFolderInterface;
use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Entity\ResearchInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ResearchRepositoryInterface;

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
     * @var null|PlayerDefaultsCreator
     */
    private $defaultsCreator;

    public function setUp(): void
    {
        $this->privateMessageFolderRepository = Mockery::mock(PrivateMessageFolderRepositoryInterface::class);
        $this->researchedRepository = Mockery::mock(ResearchedRepositoryInterface::class);

        $this->defaultsCreator = new PlayerDefaultsCreator(
            $this->privateMessageFolderRepository,
            $this->researchedRepository
        );
    }

    public function testCreateDefaultCreatesDefaults(): void
    {
        $user = Mockery::mock(UserInterface::class);
        $pmFolder = Mockery::mock(PrivateMessageFolderInterface::class);
        $startResearch = Mockery::mock(ResearchInterface::class);
        $researchEntry = Mockery::mock(ResearchedInterface::class);

        $defaultCategoryCount = count(PrivateMessageFolderSpecialEnum::DEFAULT_CATEGORIES);

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

        foreach (PrivateMessageFolderSpecialEnum::DEFAULT_CATEGORIES as $specialFolderTypeId => $label) {
            $pmFolder->shouldReceive('setDescription')
                ->with($label)
                ->once()
                ->andReturnSelf();
            $pmFolder->shouldReceive('setSpecial')
                ->with($specialFolderTypeId)
                ->once()
                ->andReturnSelf();
            $pmFolder->shouldReceive('setSort')
                ->with($specialFolderTypeId)
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

        $this->defaultsCreator->createDefault($user);
    }
}
