<?php

declare(strict_types=1);

namespace Stu\Lib\Component;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Component\Game\GameEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Module\Colony\Component\ColonyComponentEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\Component\GameComponentEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\StuMocks;
use Stu\StuTestCase;

class ComponentLoaderTest extends StuTestCase
{
    /** @var MockInterface&ComponentRegistrationInterface  */
    private $componentRegistration;

    /** @var MockInterface&GameControllerInterface  */
    private $game;

    private ComponentLoaderInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->componentRegistration = $this->mock(ComponentRegistrationInterface::class);

        $this->game = $this->mock(GameControllerInterface::class);

        $this->subject = new ComponentLoader(
            $this->componentRegistration
        );
    }

    #[Override]
    protected function tearDown(): void
    {
        StuMocks::get()->reset();
    }

    public function testLoadComponentUpdatesAsInstantUpdate(): void
    {
        $this->componentRegistration->shouldReceive('getComponentUpdates')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection(['ID' => new ComponentUpdate(GameComponentEnum::USER, null, true)]));

        $this->game->shouldReceive('addExecuteJS')
            ->with(
                "updateComponent('ID', '/game.php?SHOW_COMPONENT=1&component=ID');",
                GameEnum::JS_EXECUTION_AFTER_RENDER
            )
            ->once();

        $this->subject->loadComponentUpdates($this->game);
    }

    public function testLoadComponentUpdatesExpectNoUpdateWhenNoInstantUpdateAndWithoutRefreshInterval(): void
    {
        $this->componentRegistration->shouldReceive('getComponentUpdates')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection(['ID' => new ComponentUpdate(GameComponentEnum::USER, null,  false)]));

        $this->subject->loadComponentUpdates($this->game);
    }

    public function testLoadComponentUpdatesWithRefreshInterval(): void
    {
        $this->componentRegistration->shouldReceive('getComponentUpdates')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection(['ID' => new ComponentUpdate(GameComponentEnum::PM, null, false)]));

        $this->game->shouldReceive('addExecuteJS')
            ->with(
                "updateComponent('ID', '/game.php?SHOW_COMPONENT=1&component=ID', 60000);",
                GameEnum::JS_EXECUTION_AFTER_RENDER
            )
            ->once();

        $this->subject->loadComponentUpdates($this->game);
    }

    public function testLoadComponentUpdatesWithInstantAndRefreshInterval(): void
    {
        $this->componentRegistration->shouldReceive('getComponentUpdates')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection(['ID' => new ComponentUpdate(GameComponentEnum::PM, null,  true)]));

        $this->game->shouldReceive('addExecuteJS')
            ->with(
                "updateComponent('ID', '/game.php?SHOW_COMPONENT=1&component=ID');",
                GameEnum::JS_EXECUTION_AFTER_RENDER
            )
            ->once();

        $this->subject->loadComponentUpdates($this->game);
    }

    public function testLoadComponentUpdatesWithParameters(): void
    {
        $entity = $this->mock(EntityWithComponentsInterface::class);

        $this->componentRegistration->shouldReceive('getComponentUpdates')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection(['ID' => new ComponentUpdate(ColonyComponentEnum::SHIELDING, $entity,  true)]));

        $entity->shouldReceive('getComponentParameters')
            ->withNoArgs()
            ->once()
            ->andReturn('&hosttype=1&id=42');

        $this->game->shouldReceive('addExecuteJS')
            ->with(
                "updateComponent('ID', '/game.php?SHOW_COMPONENT=1&component=ID&hosttype=1&id=42');",
                GameEnum::JS_EXECUTION_AFTER_RENDER
            )
            ->once();

        $this->subject->loadComponentUpdates($this->game);
    }

    public function testLoadRegisteredComponents(): void
    {
        $componentEnumWithVars = $this->mock(ComponentEnumInterface::class);
        $componentEnumNoVars = $this->mock(ComponentEnumInterface::class);
        $componentEnumWithEntity = $this->mock(ComponentEnumInterface::class);
        $componentWithVars = $this->mock(ComponentInterface::class);
        $componentWithEntity = $this->mock(EntityComponentInterface::class);
        $entity = $this->mock(ColonyInterface::class);

        StuMocks::get()->mockService('GAME_COMPONENTS', [
            'WITH_VARS' => $componentWithVars
        ]);
        StuMocks::get()->mockService('COLONY_COMPONENTS', [
            'WITH_ENTITY' => $componentWithEntity
        ]);

        $this->componentRegistration->shouldReceive('getRegisteredComponents')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([
                'GAME_WITH_VARS' => new RegisteredComponent($componentEnumWithVars, null),
                'GAME_NO_VARS' => new RegisteredComponent($componentEnumNoVars, null),
                'COLONY_WITH_ENTITY' => new RegisteredComponent($componentEnumWithEntity, $entity)
            ]));

        $componentEnumWithVars->shouldReceive('hasTemplateVariables')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $componentEnumNoVars->shouldReceive('hasTemplateVariables')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $componentEnumWithEntity->shouldReceive('hasTemplateVariables')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $componentEnumWithVars->shouldReceive('getModuleView')
            ->withNoArgs()
            ->once()
            ->andReturn(ModuleEnum::GAME);
        $componentEnumWithEntity->shouldReceive('getModuleView')
            ->withNoArgs()
            ->once()
            ->andReturn(ModuleEnum::COLONY);

        $componentEnumWithVars->shouldReceive('getValue')
            ->withNoArgs()
            ->andReturn('WITH_VARS');
        $componentEnumWithEntity->shouldReceive('getValue')
            ->withNoArgs()
            ->andReturn('WITH_ENTITY');

        $componentWithVars->shouldReceive('setTemplateVariables')
            ->with($this->game)
            ->once();
        $componentWithEntity->shouldReceive('setTemplateVariables')
            ->with($entity, $this->game)
            ->once();

        $componentEnumWithVars->shouldReceive('getTemplate')
            ->withNoArgs()
            ->once()
            ->andReturn('with/vars/template');
        $componentEnumNoVars->shouldReceive('getTemplate')
            ->withNoArgs()
            ->once()
            ->andReturn('no/vars/template');
        $componentEnumWithEntity->shouldReceive('getTemplate')
            ->withNoArgs()
            ->once()
            ->andReturn('with/entity/template');

        $this->game->shouldReceive('setTemplateVar')
            ->with('GAME_WITH_VARS', [
                'id' => 'GAME_WITH_VARS',
                'template' => 'with/vars/template'
            ])
            ->once();
        $this->game->shouldReceive('setTemplateVar')
            ->with('GAME_NO_VARS', [
                'id' => 'GAME_NO_VARS',
                'template' => 'no/vars/template'
            ])
            ->once();
        $this->game->shouldReceive('setTemplateVar')
            ->with('COLONY_WITH_ENTITY', [
                'id' => 'COLONY_WITH_ENTITY',
                'template' => 'with/entity/template'
            ])
            ->once();

        $this->subject->loadRegisteredComponents($this->game);
    }

    public function testLoadRegisteredComponentsWhenStubbed(): void
    {
        $componentEnumWithVars = $this->mock(ComponentEnumInterface::class);
        $componentEnumNoVars = $this->mock(ComponentEnumInterface::class);
        $componentEnumWithEntity = $this->mock(ComponentEnumInterface::class);
        $entity = $this->mock(ColonyInterface::class);

        $this->subject->registerStubbedComponent($componentEnumWithVars)
            ->registerStubbedComponent($componentEnumNoVars)
            ->registerStubbedComponent($componentEnumWithEntity);

        $this->componentRegistration->shouldReceive('getRegisteredComponents')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([
                'GAME_WITH_VARS' => new RegisteredComponent($componentEnumWithVars, null),
                'GAME_NO_VARS' => new RegisteredComponent($componentEnumNoVars, null),
                'COLONY_WITH_ENTITY' => new RegisteredComponent($componentEnumWithEntity, $entity)
            ]));

        $this->game->shouldReceive('setTemplateVar')
            ->with('GAME_WITH_VARS', [
                'id' => 'GAME_WITH_VARS',
                'template' => null
            ])
            ->once();
        $this->game->shouldReceive('setTemplateVar')
            ->with('GAME_NO_VARS', [
                'id' => 'GAME_NO_VARS',
                'template' => null
            ])
            ->once();
        $this->game->shouldReceive('setTemplateVar')
            ->with('COLONY_WITH_ENTITY', [
                'id' => 'COLONY_WITH_ENTITY',
                'template' => null
            ])
            ->once();

        $this->subject->loadRegisteredComponents($this->game);
    }
}
