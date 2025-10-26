<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\ActivateSystem;

use Mockery\MockInterface;
use request;
use Stu\ActionControllerTestCase;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Spacecraft\Action\ActivateSystem\ActivateSystem;
use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

class ActivateSystemTest extends ActionControllerTestCase
{
    private MockInterface&ActivatorDeactivatorHelperInterface $helper;

    private ActionControllerInterface $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->helper = $this->mock(ActivatorDeactivatorHelperInterface::class);

        $this->subject = new ActivateSystem($this->helper);
    }

    public function testHandle(): void
    {
        $info = $this->mock(InformationWrapper::class);

        request::setMockVars([
            'id' => 42,
            'type' => SpacecraftSystemTypeEnum::SHIELDS->name
        ]);

        $this->game->shouldReceive('setView')
            ->with(ShowSpacecraft::VIEW_IDENTIFIER)
            ->once();
        $this->game->shouldReceive('getInfo')
            ->withNoArgs()
            ->once()
            ->andReturn($info);

        $this->helper->shouldReceive('activate')
            ->with(42, SpacecraftSystemTypeEnum::SHIELDS, $info)
            ->once();

        $this->subject->handle($this->game);
    }
}
