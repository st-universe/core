<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\SelectTorpedo;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use request;
use Stu\ActionControllerTestCase;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\TorpedoStorage;
use Stu\Orm\Entity\TorpedoType;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\TorpedoStorageRepositoryInterface;

class SelectTorpedoTest extends ActionControllerTestCase
{
    /** @var MockInterface&SpacecraftLoaderInterface<SpacecraftWrapperInterface> */
    private MockInterface&SpacecraftLoaderInterface $spacecraftLoader;
    private MockInterface&TorpedoStorageRepositoryInterface $torpedoStorageRepository;

    private SelectTorpedo $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->spacecraftLoader = $this->mock(SpacecraftLoaderInterface::class);
        $this->torpedoStorageRepository = $this->mock(TorpedoStorageRepositoryInterface::class);
        $this->subject = new SelectTorpedo($this->spacecraftLoader, $this->torpedoStorageRepository);
    }

    public function testHandleReturnsWhenNoTorpedoTypeWasProvided(): void
    {
        request::setMockVars([]);

        $this->game->shouldReceive('setView')->with(ShowSpacecraft::VIEW_IDENTIFIER)->once();
        $this->spacecraftLoader->shouldReceive('getWrapperByIdAndUser')->never();
        $this->torpedoStorageRepository->shouldReceive('save')->never();

        $this->subject->handle($this->game);
    }

    public function testHandleSelectsRequestedFireableTorpedoType(): void
    {
        request::setMockVars(['id' => 42, 'torpedo_type' => 7]);

        $info = $this->mock(InformationWrapper::class);
        $user = $this->mock(User::class);
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $spacecraft = $this->mock(Spacecraft::class);
        $selectedStorage = $this->mock(TorpedoStorage::class);
        $otherStorage = $this->mock(TorpedoStorage::class);
        $selectedTorpedo = $this->mock(TorpedoType::class);

        $this->game->shouldReceive('setView')->with(ShowSpacecraft::VIEW_IDENTIFIER)->once();
        $this->game->shouldReceive('getUser')->withNoArgs()->once()->andReturn($user);
        $this->game->shouldReceive('getInfo')->withNoArgs()->once()->andReturn($info);
        $user->shouldReceive('getId')->withNoArgs()->once()->andReturn(100);

        $this->spacecraftLoader->shouldReceive('getWrapperByIdAndUser')->with(42, 100)->once()->andReturn($wrapper);
        $wrapper->shouldReceive('get')->withNoArgs()->once()->andReturn($spacecraft);
        $spacecraft->shouldReceive('getFireableTorpedoStorages')
            ->withNoArgs()->once()->andReturn([$selectedStorage, $otherStorage]);
        $spacecraft->shouldReceive('getTorpedoStorages')
            ->withNoArgs()->once()->andReturn(new ArrayCollection([$selectedStorage, $otherStorage]));

        $selectedStorage->shouldReceive('getTorpedo')->withNoArgs()->twice()->andReturn($selectedTorpedo);
        $selectedStorage->shouldReceive('setActive')->with(true)->once();
        $otherStorage->shouldReceive('setActive')->with(false)->once();
        $selectedTorpedo->shouldReceive('getId')->withNoArgs()->once()->andReturn(7);
        $selectedTorpedo->shouldReceive('getName')->withNoArgs()->once()->andReturn('Photonentorpedo');

        $this->torpedoStorageRepository->shouldReceive('save')->with($selectedStorage)->once();
        $this->torpedoStorageRepository->shouldReceive('save')->with($otherStorage)->once();
        $info->shouldReceive('addInformationf')
            ->with('%s ist nun als schussbereiter Torpedotyp ausgewählt', 'Photonentorpedo')
            ->once();

        $this->subject->handle($this->game);
    }
}
