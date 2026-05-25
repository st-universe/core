<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Damage;

use Mockery\MockInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Damage\DamageWrapper;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\StuTestCase;

class ApplyDamageTest extends StuTestCase
{
    private MockInterface&SpacecraftSystemManagerInterface $spacecraftSystemManager;

    private MockInterface&SystemDamageInterface $systemDamage;

    private ApplyDamage $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->spacecraftSystemManager = $this->mock(SpacecraftSystemManagerInterface::class);
        $this->systemDamage = $this->mock(SystemDamageInterface::class);

        $this->subject = new ApplyDamage(
            $this->spacecraftSystemManager,
            $this->systemDamage
        );
    }

    public function testDamageAddsCriticalHitMessageWhenSpacecraftGetsDestroyed(): void
    {
        $spacecraft = $this->mock(Spacecraft::class);
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $condition = new SpacecraftCondition($spacecraft);
        $condition->setHull(100);

        $damageWrapper = new DamageWrapper(100);
        $damageWrapper->setCrit(true);

        $informations = new InformationWrapper();

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($spacecraft);

        $spacecraft->shouldReceive('isShielded')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $spacecraft->shouldReceive('getCondition')
            ->withNoArgs()
            ->twice()
            ->andReturn($condition);
        $spacecraft->shouldReceive('getSystemState')
            ->with(SpacecraftSystemTypeEnum::RPG_MODULE)
            ->once()
            ->andReturnFalse();

        $this->subject->damage($damageWrapper, $wrapper, $informations);

        static::assertSame(
            [
                '- Kritischer Hüllen-Treffer',
                '- Hüllenschaden: 100',
                '-- Das Schiff wurde zerstört!'
            ],
            $informations->getInformations()
        );
        static::assertTrue($condition->isDestroyed());
    }
}
