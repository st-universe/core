<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Damage;

use Mockery\MockInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Damage\DamageWrapper;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\Station;
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

    public function testDamageStopsHullProcessingWhenCriticalSystemDamageDestroysSpacecraft(): void
    {
        $station = new Station();
        $condition = new SpacecraftCondition($station);
        $condition->setHull(600);
        $station->setCondition($condition);
        $station->setMaxHull(1000);

        $torpedoStorageSystem = (new SpacecraftSystem())
            ->setSystemType(SpacecraftSystemTypeEnum::TORPEDO_STORAGE)
            ->setStatus(25)
            ->setMode(SpacecraftSystemModeEnum::MODE_ON);
        $station->getSystems()->set(SpacecraftSystemTypeEnum::TORPEDO_STORAGE->value, $torpedoStorageSystem);

        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($station);

        $this->spacecraftSystemManager->shouldReceive('handleDestroyedSystem')
            ->with($wrapper, SpacecraftSystemTypeEnum::TORPEDO_STORAGE)
            ->once()
            ->andReturnUsing(static function () use ($condition): void {
                $condition->setIsDestroyed(true);
            });

        $subject = new ApplyDamage(
            $this->spacecraftSystemManager,
            new SystemDamage($this->spacecraftSystemManager)
        );

        $damageWrapper = new DamageWrapper(78);
        $damageWrapper->setCrit(true);

        $informations = new InformationWrapper();

        $subject->damage($damageWrapper, $wrapper, $informations);

        $information = $informations->getInformations();

        static::assertMatchesRegularExpression(
            '/^- Kritischer Hüllen-Treffer verursacht ([3-5][0-9]|60)% Systemschaden$/',
            $information[0]
        );
        static::assertSame(
            [
                '- Der Schaden zerstört folgendes System: Torpedolager',
                '-- Das Schiff wurde zerstört!'
            ],
            array_slice($information, 1)
        );
        static::assertTrue($condition->isDestroyed());
        static::assertSame(600, $condition->getHull());
        static::assertSame(0, $torpedoStorageSystem->getStatus());
        static::assertSame(SpacecraftSystemModeEnum::MODE_OFF, $torpedoStorageSystem->getMode());
    }
}
