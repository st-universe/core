<?php

declare(strict_types=1);

namespace Stu\Lib\Component;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Module\Colony\Component\ColonyComponentEnum;
use Stu\Module\Control\JavascriptExecutionInterface;
use Stu\Module\Game\Component\GameComponentEnum;
use Stu\Module\Template\TemplateInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\User;
use Stu\StuMocks;
use Stu\StuTestCase;

class ComponentLoaderTest extends StuTestCase
{
    private MockInterface&ComponentRegistrationInterface  $componentRegistration;
    private MockInterface&JavascriptExecutionInterface  $javascriptExecution;

    private MockInterface&TemplateInterface $template;
    private MockInterface&User $user;

    private ComponentLoaderInterface $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->componentRegistration = $this->mock(ComponentRegistrationInterface::class);
        $this->javascriptExecution = $this->mock(JavascriptExecutionInterface::class);

        $this->template = $this->mock(TemplateInterface::class);
        $this->user = $this->mock(User::class);

        $this->subject = new ComponentLoader(
            $this->componentRegistration,
            $this->javascriptExecution
        );
    }

    #[\Override]
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

        $this->javascriptExecution->shouldReceive('addExecuteJS')
            ->with(
                "updateComponent('ID', '/game.php?SHOW_COMPONENT=1&component=ID');",
                JavascriptExecutionTypeEnum::AFTER_RENDER
            )
            ->once();

        $this->subject->loadComponentUpdates();
    }

    public function testLoadComponentUpdatesExpectNoUpdateWhenNoInstantUpdateAndWithoutRefreshInterval(): void
    {
        $this->componentRegistration->shouldReceive('getComponentUpdates')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection(['ID' => new ComponentUpdate(GameComponentEnum::USER, null, false)]));

        $this->subject->loadComponentUpdates();
    }

    public function testLoadComponentUpdatesWithRefreshInterval(): void
    {
        $this->componentRegistration->shouldReceive('getComponentUpdates')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection(['ID' => new ComponentUpdate(GameComponentEnum::PM, null, false)]));

        $this->javascriptExecution->shouldReceive('addExecuteJS')
            ->with(
                "updateComponent('ID', '/game.php?SHOW_COMPONENT=1&component=ID', 60000);",
                JavascriptExecutionTypeEnum::AFTER_RENDER
            )
            ->once();

        $this->subject->loadComponentUpdates();
    }

    public function testLoadComponentUpdatesWithInstantAndRefreshInterval(): void
    {
        $this->componentRegistration->shouldReceive('getComponentUpdates')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection(['ID' => new ComponentUpdate(GameComponentEnum::PM, null, true)]));

        $this->javascriptExecution->shouldReceive('addExecuteJS')
            ->with(
                "updateComponent('ID', '/game.php?SHOW_COMPONENT=1&component=ID');",
                JavascriptExecutionTypeEnum::AFTER_RENDER
            )
            ->once();

        $this->subject->loadComponentUpdates();
    }

    public function testLoadComponentUpdatesWithParameters(): void
    {
        $entity = $this->mock(EntityWithComponentsInterface::class);

        $this->componentRegistration->shouldReceive('getComponentUpdates')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection(['ID' => new ComponentUpdate(ColonyComponentEnum::SHIELDING, $entity, true)]));

        $entity->shouldReceive('getComponentParameters')
            ->withNoArgs()
            ->once()
            ->andReturn('&hosttype=1&id=42');

        $this->javascriptExecution->shouldReceive('addExecuteJS')
            ->with(
                "updateComponent('ID', '/game.php?SHOW_COMPONENT=1&component=ID&hosttype=1&id=42');",
                JavascriptExecutionTypeEnum::AFTER_RENDER
            )
            ->once();

        $this->subject->loadComponentUpdates();
    }

    public function testLoadRegisteredComponents(): void
    {
        $componentEnumWithVars = $this->mock(ComponentEnumInterface::class);
        $componentEnumNoVars = $this->mock(ComponentEnumInterface::class);
        $componentEnumWithEntity = $this->mock(ComponentEnumInterface::class);
        $componentWithVars = $this->mock(ComponentInterface::class);
        $componentWithEntity = $this->mock(EntityComponentInterface::class);
        $entity = $this->mock(Colony::class);

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
            ->with($this->user, $this->template)
            ->once();
        $componentWithEntity->shouldReceive('setTemplateVariables')
            ->with($entity, $this->template, $this->user)
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

        $this->template->shouldReceive('setTemplateVar')
            ->with('GAME_WITH_VARS', [
                'id' => 'GAME_WITH_VARS',
                'template' => 'with/vars/template'
            ])
            ->once();
        $this->template->shouldReceive('setTemplateVar')
            ->with('GAME_NO_VARS', [
                'id' => 'GAME_NO_VARS',
                'template' => 'no/vars/template'
            ])
            ->once();
        $this->template->shouldReceive('setTemplateVar')
            ->with('COLONY_WITH_ENTITY', [
                'id' => 'COLONY_WITH_ENTITY',
                'template' => 'with/entity/template'
            ])
            ->once();

        $this->subject->loadRegisteredComponents($this->user, $this->template);
    }

    public function testLoadRegisteredComponentsWhenStubbed(): void
    {
        $componentEnumWithVars = $this->mock(ComponentEnumInterface::class);
        $componentEnumNoVars = $this->mock(ComponentEnumInterface::class);
        $componentEnumWithEntity = $this->mock(ComponentEnumInterface::class);
        $entity = $this->mock(Colony::class);

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

        $this->template->shouldReceive('setTemplateVar')
            ->with('GAME_WITH_VARS', [
                'id' => 'GAME_WITH_VARS',
                'template' => null
            ])
            ->once();
        $this->template->shouldReceive('setTemplateVar')
            ->with('GAME_NO_VARS', [
                'id' => 'GAME_NO_VARS',
                'template' => null
            ])
            ->once();
        $this->template->shouldReceive('setTemplateVar')
            ->with('COLONY_WITH_ENTITY', [
                'id' => 'COLONY_WITH_ENTITY',
                'template' => null
            ])
            ->once();

        $this->subject->loadRegisteredComponents($this->user, $this->template);
    }
}
