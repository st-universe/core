<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib;

use Mockery\MockInterface;
use RuntimeException;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\Lib\Component\ViewComponentProviderInterface;
use Stu\StuTestCase;

class ViewComponentLoaderTest extends StuTestCase
{
    /** @var MockInterface&ViewComponentProviderInterface  */
    private MockInterface $componentProvider;

    /** @var MockInterface&GameControllerInterface  */
    private MockInterface $game;

    private ViewComponentLoaderInterface $subject;

    protected function setUp(): void
    {
        $this->componentProvider = $this->mock(ViewComponentProviderInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);

        $this->subject = new ViewComponentLoader([ModuleViewEnum::MAINDESK->value => $this->componentProvider]);
    }

    public function testRegisterViewComponentsExpectExceptionIfProviderMissing(): void
    {
        static::expectExceptionMessage('viewComponentProvider with follwing id does not exist: colony');
        static::expectException(RuntimeException::class);

        $this->subject->registerViewComponents(ModuleViewEnum::COLONY, $this->game);
    }

    public function testRegisterViewComponentsExpectSettingOfTemplateVariables(): void
    {
        $this->componentProvider->shouldReceive('setTemplateVariables')
            ->with($this->game)
            ->once();

        $this->game->shouldReceive('setTemplateVar')
            ->with('CURRENT_VIEW', ModuleViewEnum::MAINDESK)
            ->once();

        $this->subject->registerViewComponents(ModuleViewEnum::MAINDESK, $this->game);
    }
}
