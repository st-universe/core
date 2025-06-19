<?php

declare(strict_types=1);

namespace Stu\Module\Game\View\ShowComponent;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use request;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Lib\Colony\PlanetFieldHostTypeEnum;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Module\Colony\Component\ColonyComponentEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Game\Component\GameComponentEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class ShowComponentTest extends StuTestCase
{
    private MockInterface&ComponentRegistrationInterface  $componentRegistration;
    private MockInterface&PlanetFieldHostProviderInterface  $planetFieldHostProvider;

    private MockInterface&GameControllerInterface  $game;

    private ViewControllerInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->componentRegistration = $this->mock(ComponentRegistrationInterface::class);
        $this->planetFieldHostProvider = $this->mock(PlanetFieldHostProviderInterface::class);

        $this->game = $this->mock(GameControllerInterface::class);

        $this->subject = new ShowComponent(
            $this->componentRegistration,
            $this->planetFieldHostProvider
        );
    }

    public static function getOutdatedProvider(): array
    {
        return [
            ['pm'],
            ['servertime'],
            ['foo'],
            ['BAR'],
            ['HUX_PM_NAVLET'],
            ['GAME_UNKNOWN']
        ];
    }

    #[DataProvider('getOutdatedProvider')]
    public function testHandleExpectOutdatedWhenComponentUnknown(string $component): void
    {
        request::setMockVars(['component' => $component]);

        $this->componentRegistration->shouldReceive('registerComponent')
            ->with(GameComponentEnum::OUTDATED, null)
            ->once();

        $this->game->shouldReceive('showMacro')
            ->with(GameComponentEnum::OUTDATED->getTemplate())
            ->once();

        $this->subject->handle($this->game);
    }

    public function testHandleExpectCorrectComponentWithoutEntity(): void
    {
        request::setMockVars(['component' => 'GAME_NAGUS_POPUP']);

        $this->componentRegistration->shouldReceive('registerComponent')
            ->with(GameComponentEnum::NAGUS, null)
            ->once();

        $this->game->shouldReceive('showMacro')
            ->with(GameComponentEnum::NAGUS->getTemplate())
            ->once();

        $this->subject->handle($this->game);
    }

    public function testHandleExpectCorrectComponentWithEntity(): void
    {
        $user = $this->mock(UserInterface::class);
        $entity = $this->mock(ColonyInterface::class);

        request::setMockVars([
            'component' => 'COLONY_SHIELDING',
            'id' => 42
        ]);

        $this->planetFieldHostProvider->shouldReceive('loadHostViaRequestParameters')
            ->with($user, false)
            ->once()
            ->andReturn($entity);

        $this->componentRegistration->shouldReceive('registerComponent')
            ->with(ColonyComponentEnum::SHIELDING, $entity)
            ->once();

        $this->game->shouldReceive('showMacro')
            ->with(ColonyComponentEnum::SHIELDING->getTemplate())
            ->once();
        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->subject->handle($this->game);
    }
}
