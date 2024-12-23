<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\ActivateSystem;

use Mockery\MockInterface;
use Override;
use request;
use Stu\ActionControllerTestCase;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Spacecraft\Action\ActivateSystem\ActivateSystem;
use Stu\Module\Spacecraft\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

class ActivateSystemTest extends ActionControllerTestCase
{
    /** @var MockInterface&ActivatorDeactivatorHelperInterface */
    private MockInterface $helper;

    private ActionControllerInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->helper = $this->mock(ActivatorDeactivatorHelperInterface::class);

        $this->subject = new ActivateSystem($this->helper);
    }

    public function testHandle(): void
    {
        request::setMockVars([
            'id' => 42,
            'type' => SpacecraftSystemTypeEnum::SHIELDS->name
        ]);

        $this->game->shouldReceive('setView')
            ->with(ShowSpacecraft::VIEW_IDENTIFIER)
            ->once();

        $this->helper->shouldReceive('activate')
            ->with(42, SpacecraftSystemTypeEnum::SHIELDS, $this->game)
            ->once();

        $this->subject->handle($this->game);
    }
}
