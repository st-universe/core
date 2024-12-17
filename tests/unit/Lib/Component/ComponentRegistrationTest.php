<?php

declare(strict_types=1);

namespace Stu\Lib\Component;

use Override;
use Stu\Module\Game\Component\GameComponentEnum;
use Stu\StuTestCase;

class ComponentRegistrationTest extends StuTestCase
{
    private ComponentRegistrationInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->subject = new ComponentRegistration();
    }

    public function testGetComponentUpdatesExpectEmptyCollection(): void
    {
        $result = $this->subject->getComponentUpdates();
        $this->assertNotNull($result);
        $this->assertTrue($result->isEmpty());
    }

    public function testRegisteredComponentsExpectEmptyCollection(): void
    {
        $result = $this->subject->getRegisteredComponents();
        $this->assertNotNull($result);
        $this->assertTrue($result->isEmpty());
    }

    public function testAddComponentUpdate(): void
    {
        $this->subject->addComponentUpdate(GameComponentEnum::COLONIES, false);
        $this->subject->addComponentUpdate(GameComponentEnum::COLONIES, true);
        $this->subject->addComponentUpdate(GameComponentEnum::NAVIGATION, true);

        $result = $this->subject->getComponentUpdates();

        $this->assertEquals(2, $result->count());
        $componentUpdateColonies = $result->get('GAME_COLONIES_NAVLET');
        $componentUpdateNavigation = $result->get('GAME_NAVIGATION');
        $this->assertFalse($componentUpdateColonies->isInstantUpdate());
        $this->assertEquals(GameComponentEnum::COLONIES, $componentUpdateColonies->getComponentEnum());
        $this->assertTrue($componentUpdateNavigation->isInstantUpdate());
        $this->assertEquals(GameComponentEnum::NAVIGATION, $componentUpdateNavigation->getComponentEnum());
    }

    public function testRegisterComponent(): void
    {
        $this->subject->registerComponent(GameComponentEnum::PM);
        $this->subject->registerComponent(GameComponentEnum::NAVIGATION);
        $this->subject->registerComponent(GameComponentEnum::PM);

        $result = $this->subject->getRegisteredComponents();

        $this->assertEquals(2, $result->count());
        $this->assertEquals([
            'GAME_PM_NAVLET' => GameComponentEnum::PM,
            'GAME_NAVIGATION' => GameComponentEnum::NAVIGATION
        ], $result->toArray());
    }

    public function testResetComponents(): void
    {
        $this->subject->addComponentUpdate(GameComponentEnum::COLONIES, false);
        $this->subject->registerComponent(GameComponentEnum::PM);
        $this->subject->resetComponents();

        $this->assertTrue($this->subject->getComponentUpdates()->isEmpty());
        $this->assertTrue($this->subject->getRegisteredComponents()->isEmpty());
    }
}
