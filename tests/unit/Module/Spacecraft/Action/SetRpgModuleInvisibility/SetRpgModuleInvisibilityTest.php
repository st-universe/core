<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\SetRpgModuleInvisibility;

use Mockery\MockInterface;
use request;
use Stu\ActionControllerTestCase;
use Stu\Component\Realtime\SpacecraftMovementPublisherInterface;
use Stu\Component\Spacecraft\System\Data\RpgModuleSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;

class SetRpgModuleInvisibilityTest extends ActionControllerTestCase
{
    /** @var MockInterface&SpacecraftLoaderInterface<SpacecraftWrapperInterface> */
    private MockInterface&SpacecraftLoaderInterface $spacecraftLoader;
    private MockInterface&SpacecraftSystemRepositoryInterface $spacecraftSystemRepository;
    private MockInterface&StatusBarFactoryInterface $statusBarFactory;
    private MockInterface&SpacecraftMovementPublisherInterface $spacecraftMovementPublisher;

    private SetRpgModuleInvisibility $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->spacecraftLoader = $this->mock(SpacecraftLoaderInterface::class);
        $this->spacecraftSystemRepository = $this->mock(SpacecraftSystemRepositoryInterface::class);
        $this->statusBarFactory = $this->mock(StatusBarFactoryInterface::class);
        $this->spacecraftMovementPublisher = $this->mock(SpacecraftMovementPublisherInterface::class);

        $this->subject = new SetRpgModuleInvisibility(
            $this->spacecraftLoader,
            $this->spacecraftMovementPublisher
        );
    }

    public function testHandleReturnsWhenUserIsNoAdmin(): void
    {
        $this->game->shouldReceive('setView')
            ->with(ShowSpacecraft::VIEW_IDENTIFIER)
            ->once();
        $this->game->shouldReceive('isAdmin')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->spacecraftLoader->shouldReceive('getWrapperByIdAndUser')
            ->never();
        $this->spacecraftSystemRepository->shouldReceive('save')
            ->never();
        $this->spacecraftMovementPublisher->shouldReceive('publishRemoval')
            ->never();
        $this->spacecraftMovementPublisher->shouldReceive('publishState')
            ->never();

        $this->subject->handle($this->game);
    }

    public function testHandleActivatesInvisibility(): void
    {
        request::setMockVars([
            'id' => 42,
            'state' => 1
        ]);

        $info = $this->mock(InformationWrapper::class);
        $user = $this->mock(User::class);
        $spacecraft = $this->mock(Spacecraft::class);
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $rpgModule = new SpacecraftSystem()->setData('{"foo":"bar"}');
        $rpgModuleData = new RpgModuleSystemData($this->spacecraftSystemRepository, $this->statusBarFactory)
            ->setInvisible(false);
        $rpgModuleData->setSpacecraft($spacecraft);

        $this->game->shouldReceive('setView')
            ->with(ShowSpacecraft::VIEW_IDENTIFIER)
            ->once();
        $this->game->shouldReceive('isAdmin')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $this->game->shouldReceive('getInfo')
            ->withNoArgs()
            ->once()
            ->andReturn($info);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(100);

        $this->spacecraftLoader->shouldReceive('getWrapperByIdAndUser')
            ->with(42, 100)
            ->once()
            ->andReturn($wrapper);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($spacecraft);
        $wrapper->shouldReceive('getRpgModuleSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($rpgModuleData);

        $spacecraft->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::RPG_MODULE)
            ->once()
            ->andReturn($rpgModule);

        $this->spacecraftSystemRepository->shouldReceive('save')
            ->with($rpgModule)
            ->once();
        $this->spacecraftMovementPublisher->shouldReceive('publishRemoval')
            ->with($spacecraft)
            ->once();
        $this->spacecraftMovementPublisher->shouldReceive('publishState')
            ->never();

        $info->shouldReceive('addInformation')
            ->with('RPG-Unsichtbarkeit aktiviert')
            ->once();

        $this->subject->handle($this->game);

        static::assertSame('{"invisible":true}', $rpgModule->getData());
    }

    public function testHandleDeactivatesInvisibility(): void
    {
        request::setMockVars([
            'id' => 42,
            'state' => 0
        ]);

        $info = $this->mock(InformationWrapper::class);
        $user = $this->mock(User::class);
        $spacecraft = $this->mock(Spacecraft::class);
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $rpgModule = new SpacecraftSystem()->setData('{"invisible":true}');
        $rpgModuleData = new RpgModuleSystemData($this->spacecraftSystemRepository, $this->statusBarFactory)
            ->setInvisible(true);
        $rpgModuleData->setSpacecraft($spacecraft);

        $this->game->shouldReceive('setView')
            ->with(ShowSpacecraft::VIEW_IDENTIFIER)
            ->once();
        $this->game->shouldReceive('isAdmin')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $this->game->shouldReceive('getInfo')
            ->withNoArgs()
            ->once()
            ->andReturn($info);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(100);

        $this->spacecraftLoader->shouldReceive('getWrapperByIdAndUser')
            ->with(42, 100)
            ->once()
            ->andReturn($wrapper);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($spacecraft);
        $wrapper->shouldReceive('getRpgModuleSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($rpgModuleData);

        $spacecraft->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::RPG_MODULE)
            ->once()
            ->andReturn($rpgModule);

        $this->spacecraftSystemRepository->shouldReceive('save')
            ->with($rpgModule)
            ->once();
        $this->spacecraftMovementPublisher->shouldReceive('publishRemoval')
            ->never();
        $this->spacecraftMovementPublisher->shouldReceive('publishState')
            ->with($spacecraft)
            ->once();

        $info->shouldReceive('addInformation')
            ->with('RPG-Unsichtbarkeit deaktiviert')
            ->once();

        $this->subject->handle($this->game);

        static::assertSame('{"invisible":false}', $rpgModule->getData());
    }
}
