<?php

declare(strict_types=1);

namespace Stu\Module\Game\Action\SwitchView;

use Mockery\MockInterface;
use request;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\InvalidParamException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\ViewContextMetadataTypeEnum;
use Stu\Module\Game\View\ShowInnerContent\ShowInnerContent;
use Stu\StuTestCase;
use ValueError;

class SwitchViewTest extends StuTestCase
{
    private MockInterface&ActionControllerContext  $context;

    private ActionControllerInterface $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->context = $this->mock(ActionControllerContext::class);

        $this->subject = new SwitchView();
    }

    public function testHandleExpectExceptionWhenNoViewRequestparam(): void
    {
        static::expectExceptionMessage('request parameter "view" does not exist');
        static::expectException(InvalidParamException::class);

        $this->subject->handle($this->context);
    }

    public function testHandleExpectExceptionWhenViewUnknown(): void
    {
        static::expectExceptionMessage('"foobar" is not a valid backing value for enum Stu\Component\Game\ModuleEnum');
        static::expectException(ValueError::class);

        request::setMockVars(['view' => 'foobar']);

        $this->subject->handle($this->context);
    }

    public function testHandleExpectCorrectViewAndContext(): void
    {
        request::setMockVars(['view' => ModuleEnum::MAINDESK->value]);

        $this->context->shouldReceive('setView')
            ->with(ShowInnerContent::VIEW_IDENTIFIER)
            ->once();
        $this->context->shouldReceive('setViewContext')
            ->with(ViewContextMetadataTypeEnum::MODULE_VIEW, ModuleEnum::MAINDESK)
            ->once();

        $this->subject->handle($this->context);
    }
}
