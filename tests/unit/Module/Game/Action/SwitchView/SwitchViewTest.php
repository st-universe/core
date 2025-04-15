<?php

declare(strict_types=1);

namespace Stu\Module\Game\Action\SwitchView;

use Mockery\MockInterface;
use Override;
use request;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\InvalidParamException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Game\View\ShowInnerContent\ShowInnerContent;
use Stu\StuTestCase;
use ValueError;

class SwitchViewTest extends StuTestCase
{
    /** @var MockInterface&GameControllerInterface  */
    private MockInterface $game;

    private ActionControllerInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->game = $this->mock(GameControllerInterface::class);

        $this->subject = new SwitchView();
    }

    public function testHandleExpectExceptionWhenNoViewRequestparam(): void
    {
        static::expectExceptionMessage('request parameter "view" does not exist');
        static::expectException(InvalidParamException::class);

        $this->subject->handle($this->game);
    }

    public function testHandleExpectExceptionWhenViewUnknown(): void
    {
        static::expectExceptionMessage('"foobar" is not a valid backing value for enum Stu\Component\Game\ModuleEnum');
        static::expectException(ValueError::class);

        request::setMockVars(['view' => 'foobar']);

        $this->subject->handle($this->game);
    }

    public function testHandleExpectCorrectViewAndContext(): void
    {
        request::setMockVars(['view' => ModuleEnum::MAINDESK->value]);

        $this->game->shouldReceive('setView')
            ->with(ShowInnerContent::VIEW_IDENTIFIER)
            ->once();
        $this->game->shouldReceive('setViewContext')
            ->with(ViewContextTypeEnum::MODULE_VIEW, ModuleEnum::MAINDESK)
            ->once();

        $this->subject->handle($this->game);
    }
}
