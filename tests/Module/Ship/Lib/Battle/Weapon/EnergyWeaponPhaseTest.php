<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Weapon;

use Mockery;
use Mockery\MockInterface;
use Stu\Component\Building\BuildingManagerInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Pirate\Component\PirateWrathManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Ship\Lib\Battle\Provider\EnergyAttackerInterface;
use Stu\Module\Ship\Lib\Battle\Weapon\EnergyWeaponPhase;
use Stu\Module\Ship\Lib\Damage\ApplyDamageInterface;
use Stu\Module\Ship\Lib\ModuleValueCalculatorInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\WeaponInterface;
use Stu\Orm\Repository\WeaponRepositoryInterface;
use Stu\StuTestCase;

class EnergyWeaponPhaseTest extends StuTestCase
{
    /** @var MockInterface|ShipSystemManagerInterface */
    protected MockInterface $shipSystemManager;

    /** @var MockInterface|WeaponRepositoryInterface */
    protected MockInterface $weaponRepository;

    /** @var MockInterface|EntryCreatorInterface */
    protected MockInterface $entryCreator;

    /** @var MockInterface|ShipRemoverInterface */
    protected MockInterface $shipRemover;

    /** @var MockInterface|ApplyDamageInterface */
    protected MockInterface $applyDamage;

    /** @var MockInterface|ModuleValueCalculatorInterface */
    protected MockInterface $moduleValueCalculator;

    /** @var MockInterface|BuildingManagerInterface */
    protected MockInterface $buildingManager;

    /** @var MockInterface|CreatePrestigeLogInterface */
    private MockInterface $createPrestigeLog;

    /** @var MockInterface|PrivateMessageSenderInterface */
    private MockInterface $privateMessageSender;

    /** @var MockInterface|PirateWrathManagerInterface */
    private MockInterface $pirateWrathManager;

    /** @var MockInterface|StuRandom */
    private MockInterface $stuRandom;

    private EnergyWeaponPhaseInterface $subject;

    public function setUp(): void
    {
        $this->shipSystemManager = $this->mock(ShipSystemManagerInterface::class);
        $this->weaponRepository = $this->mock(WeaponRepositoryInterface::class);
        $this->entryCreator = $this->mock(EntryCreatorInterface::class);
        $this->shipRemover = $this->mock(ShipRemoverInterface::class);
        $this->applyDamage = $this->mock(ApplyDamageInterface::class);
        $this->moduleValueCalculator = $this->mock(ModuleValueCalculatorInterface::class);
        $this->buildingManager = $this->mock(BuildingManagerInterface::class);
        $this->createPrestigeLog = $this->mock(CreatePrestigeLogInterface::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);
        $this->stuRandom = $this->mock(StuRandom::class);
        $this->pirateWrathManager = $this->mock(PirateWrathManagerInterface::class);

        $this->subject = new EnergyWeaponPhase(
            $this->shipSystemManager,
            $this->weaponRepository,
            $this->entryCreator,
            $this->shipRemover,
            $this->applyDamage,
            $this->moduleValueCalculator,
            $this->buildingManager,
            $this->createPrestigeLog,
            $this->privateMessageSender,
            $this->stuRandom,
            $this->pirateWrathManager,
            $this->initLoggerUtil()
        );
    }

    public function testFireExpectNoSecondShotIfTargetDestoryedOnFirst(): void
    {
        $attacker = $this->mock(EnergyAttackerInterface::class);
        $target = $this->mock(ShipInterface::class);
        $targetWrapper = $this->mock(ShipWrapperInterface::class);
        $weapon = $this->mock(WeaponInterface::class);
        $user = $this->mock(UserInterface::class);
        $targetUser = $this->mock(UserInterface::class);
        $targetRump = $this->mock(ShipRumpInterface::class);

        $targetId = 42;
        $targetPool = [$targetId => $targetWrapper];

        $informations = new InformationWrapper();

        $attacker->shouldReceive('getPhaserVolleys')
            ->withNoArgs()
            ->once()
            ->andReturn(2);
        $attacker->shouldReceive('getPhaserState')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $attacker->shouldReceive('hasSufficientEnergy')
            ->with(1)
            ->andReturn(true);
        $attacker->shouldReceive('getWeapon')
            ->withNoArgs()
            ->andReturn($weapon);
        $attacker->shouldReceive('getFiringMode')
            ->withNoArgs()
            ->andReturn(1);
        $attacker->shouldReceive('reduceEps')
            ->with(1)
            ->once();
        $attacker->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $attacker->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn("ATTACKER");
        $attacker->shouldReceive('getHitChance')
            ->withNoArgs()
            ->andReturn(100);
        $attacker->shouldReceive('getWeaponDamage')
            ->with(true)
            ->andReturn(100);
        $attacker->shouldReceive('getPhaserShieldDamageFactor')
            ->withNoArgs()
            ->andReturn(100);
        $attacker->shouldReceive('getPhaserHullDamageFactor')
            ->withNoArgs()
            ->andReturn(100);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(888);

        $targetWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($target);

        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($targetId);
        $target->shouldReceive('getCloakState')
            ->withNoArgs()
            ->andReturn(false);
        $target->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn("TARGET");
        $target->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($targetUser);
        $target->shouldReceive('getEvadeChance')
            ->withNoArgs()
            ->andReturn(0);
        $target->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->andReturn(true);
        $target->shouldReceive('isBase')
            ->withNoArgs()
            ->andReturn(false);
        $target->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->andReturn(null);
        $target->shouldReceive('getRump')
            ->withNoArgs()
            ->andReturn($targetRump);
        $target->shouldReceive('getSectorString')
            ->withNoArgs()
            ->andReturn("SECTOR");

        $targetRump->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn("RUMP");
        $targetRump->shouldReceive('getPrestige')
            ->withNoArgs()
            ->andReturn(0);

        $targetUser->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(999);

        $weapon->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn("WEAPON");
        $weapon->shouldReceive('getCriticalChance')
            ->withNoArgs()
            ->andReturn(0);
        $weapon->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $weapon->shouldReceive('getFiringMode')
            ->withNoArgs()
            ->andReturn(1);

        $this->stuRandom->shouldReceive('rand')
            ->with(1, 100)
            ->andReturn(0);
        $this->stuRandom->shouldReceive('rand')
            ->with(1, 10000)
            ->andReturn(0);

        $this->applyDamage->shouldReceive('damage')
            ->with(Mockery::any(), $targetWrapper)
            ->andReturn($informations);

        $this->entryCreator->shouldReceive('addEntry')
            ->with(Mockery::any(), 888, $target)
            ->once();

        $this->shipRemover->shouldReceive('destroy')
            ->with($targetWrapper)
            ->once();

        $this->subject->fire($attacker, $targetPool);
    }
}
