<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\ActivateShields;

use Mockery\MockInterface;
use Override;
use request;
use Stu\ActionControllerTestCase;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Spacecraft\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

class ActivateShieldsTest extends ActionControllerTestCase
{
    /** @var MockInterface&ActivatorDeactivatorHelperInterface */
    private MockInterface $helper;

    private ActivateShields $subject;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->helper = $this->mock(ActivatorDeactivatorHelperInterface::class);

        $this->subject = new ActivateShields($this->helper);
    }

    public function testHandle(): void
    {
        request::setMockVars(['id' => 42]);

        $this->game->shouldReceive('setView')
            ->with(ShowSpacecraft::VIEW_IDENTIFIER)
            ->once();

        $this->helper->shouldReceive('activate')
            ->with(42, SpacecraftSystemTypeEnum::SYSTEM_SHIELDS, $this->game)
            ->once();

        $this->subject->handle($this->game);
    }
}
